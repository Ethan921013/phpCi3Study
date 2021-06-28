<?php


class AlimtalkApi extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this -> load -> library('curl');

        $this -> load -> model('testmodel', 'testmodel');
        $this -> load -> model('supportModel');


        $this -> load -> helper('array_helper');

        $this -> load -> helper('debug_helper');
        $this -> load -> helper('test_helper');
        $this -> load -> helper('url');
        $this -> load -> database();

        $this -> data = array(
            "bootstrap_version" => "/assets/testlink/bootstrap",
            "bootstrap_theme" => "/assets/bootstrap-3.3.3.6/css/flatly",
            "jquery_version" => "/assets/js/2.2.0",
            "module_name" => $this -> module_name,
            "asset_directory" => "/assets/testlink",
            "version" => "1.0.0"
        );


    }


    function alimTalkApi($templateCode = null)
    {

        $arrGetSmsInfo = $this->supportModel->getSmsSendInfo();

        foreach ($arrGetSmsInfo as $value) {

            unset($shorten_customer_url);
            $encode_no = base64url_encode($value['mall_order_no']);
            $birthday_url = "http://test.test.com/api/birthdayCode?no={$encode_no}";
            $shorten_customer_url = $this->curl->simple_post($this->config->item('basic_public_url') . 'api/short_url', array('query' => $birthday_url));


            $params = array();
            $params['shorten_customer_url'] = $shorten_customer_url;
            $params['item_name'] = $value['item_name'];

            // template 메세지를 가져온다
            $message = $this->getSmsTemplate($params, $templateCode);

            // 문자 발송 기록
            $arr_history = array();
            $arr_history['destination'] = $value['reci_hp'];
            $arr_history['order_seq'] = $value['order_seq'];
            $arr_history['subject'] = "안녕하세요? test 입니다.";
            $arr_history['body'] = $message;
            $arr_history['create_date'] = date("Y-m-d H:i:s");
            $arr_history['cs_type'] = $templateCode;

            $arr_status = array();
            $arr_status['mall_order_no'] = $value['mall_order_no'];
            $arr_status['sms_send_date'] = date("Y-m-d H:i:s");


            $trim_hp = substr($value['reci_hp'],0,3);
            $arr_confirm_hp = array('010');

            if(in_array($trim_hp,$arr_confirm_hp)){

                $appkey = 'test';
                $plusFriendId = 'test';
                $secretKey = 'test';
                $seconds = 60;
                $date_now = date("Y-m-d H:i");
                $added_date = date("Y-m-d H:i", (strtotime(date($date_now)) + $seconds));

                // curl method sending data
                $arr_curl_data = array(
                    'appkey'=>$appkey,
                    'secretKey'=>$secretKey,
                    'plusFriendId'=>$plusFriendId,
                    'templateCode'=>$templateCode,
                    'added_date'=>$added_date,
                    'shorten_customer_url'=>$shorten_customer_url,
                );

                // curl 전송
                $this->sendAlimTalkCurl($arr_curl_data,$value);

                $arr_history['username'] = 'AlimTalk';

                // 기록
                $this->test_sms_history_model->add_test_sms_history($arr_history);

                // 등록 및 업데이트 날짜
                $this->supportModel->test_sms_send_status($arr_status);


            }else{

                // 문자를 발송한다
                $this->send_sms($value,$message);

                $arr_history['username'] = 'SendOkSms';

                // 기록
                $this -> test_sms_history_model -> add_test_sms_history($arr_history);

                // 등록 및 업데이트 날짜
                $this -> supportModel -> test_sms_send_status($arr_status);

            }

            // 0.1 delay
            usleep(100000);

        }

    }


    public function getSmsTemplate($params, $template){

        $template_text = '';

        switch ($template){

            case "taxcode" :
                $template_text = "test님!
주문 상품 <{$params['item_name']}> 입니다!";
                break;

        }

        return $template_text;

    }


    function sendAlimTalkCurl($params,$arr_value){

        $url = "https://api-alimtalk.cloud.toast.com/alimtalk/v1.4/appkeys/{$params['appkey']}/messages";

        $multi_headers = array();
        $multi_headers[0] = array(
            "charset=UTF-8",
            "X-Secret-Key:{$params['secretKey']}",
        );

        $headers = array();
        $headers = $multi_headers[0];

        $body = <<<EOF
                   {
                   "plusFriendId":"{$params['plusFriendId']}",
                   "templateCode":"{$params['templateCode']}",
                   "requestDate":"{$params['added_date']}",
                   "recipientList":
                       [
                         {
                         "recipientNo":"{$arr_value['reci_hp']}",
                         "templateParameter":
                            {
                                "고객명":"{$arr_value['reci_name']}",
                                "상품명":"{$arr_value['item_name']}",
                                "옵션명":"{$arr_value['user_option_item']}",
                                "고유부호입력주소":"{$params['shorten_customer_url']}"
                            }
                         }
                       ]
                   }
EOF;

        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

    }


    function send_sms($sms_info,$message){

        $url = "http://www.okmunja.co.kr/Remote/RemoteMms.html";
        $arr_post = array();
        $arr_post['sms_send'] = $url;
        $arr_post['remote_id'] = "test";
        $arr_post['remote_pass'] = "test";
        $arr_post['remote_num'] = "1";
        $arr_post['remote_phone'] = $sms_info['reci_hp'];
        $arr_post['remote_reserve'] = "0";
        $arr_post['remote_name'] = "test";
        $arr_post['remote_callbatest'] = "1234";
        $arr_post['remote_subject'] = "안녕? {$sms_info['reci_name']}";
        $arr_post['remote_msg'] = $message;
        $arr_post['remote_etc1'] = $sms_info['order_seq'];
        $result_sms_send = $this->curl->simple_post($url, $arr_post);
        iconv("UTF-8","UTF-8", $result_sms_send);
        // 결과 확인
        debug_var("OK sms result : ".$result_sms_send);
        return $result_sms_send;

    }
    
    
    
    
    
    

}