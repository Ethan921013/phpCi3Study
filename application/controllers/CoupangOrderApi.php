<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class CoupangOrderApi extends CI_Controller
{

    var $data;
    var $module_name = "test";
    var $username = "";
    var $mall_id = "";

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->helper('url');
        $this->load->helper('array_helper');
        $this->load->helper('test_helper');
        $this->load->helper('table_helper');
        $this->load->helper('debug_helper');
        $this->load->helper('form');

        $this->load->library('curl');
        $this->load->library('simple_html_dom');
        $this->load->library('ion_auth');
        $this->load->library('chinalink');

        $this->load->model('CoupangModel','coupangModel');
        $this->load->model('testmodel', 'testmodel');

        $this->load->library('grocery_CRUD');
        $this->load->library('simplexml');

        $this->data = array(
            "bootstrap_version" => "/assets/test/bootstrap",
            "bootstrap_theme" => "/assets/bootstrap-3.3.3.6/css/flatly",//없으면 "" 있으면 superhero/
            "jquery_version" => "/assets/js/2.2.0",
            "module_name" => $this->module_name,
            "asset_directory" => "/assets/test", //없으면 "" 있으면 superhero/
            "version" => "1.0.0"
        );

        $arr_status_code = array('1111' => 'test');
        $this->data['arr_status_code'] = $arr_status_code;
        $this->data['active_menu'] = $this->router->fetch_method();
        
    }


//    private function getAPIKEY(){
//        return array("1234");
//    }

    //CHEtest API KEY, IF KEY IS NOT EXIST THEN SEND FAIL MESSAGE
//    private function chetestAPIKEY($headerMap){
//
//        $test_APP_KEY = isset($headerMap["test_APP_KEY"]) ? $headerMap["test_APP_KEY"] : "";
//        if($test_APP_KEY==""){
//            $test_APP_KEY = isset($headerMap["test_app_key"]) ? $headerMap["test_app_key"] : "";
//        }
//
//        if (!in_array($test_APP_KEY, $this -> getAPIKEY())) {
//
//            $responseMap["result"] = "FAIL";
//            $responseMap["message"] = "APP_KEY_ERROR";
//            echo json_encode($responseMap);
//            return false;
//        }
//        else{
//            return true;
//        }
//    }

    function insertOrderData($arr_params){

        //엔트포인트 받아서 CURL

        $url ="";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_REFERER, $url);

        $res = curl_exec($ch);

        curl_close($ch);

        $result = $arr_params['result'];

        if($result == 'Success'){

            foreach ($arr_params['data']['orderList'] as $val){

                // db insert
                $this -> coupangModel -> insertOrderList($val);

            }

        }

    }


    function sendHblToCoupang(){

        //status -> extra_12 없는건을 가져온다.
        $arr_api_list = $this -> coupangModel -> gettestOrderApiList();
        $arr_tmp = array();
        foreach ($arr_api_list as $v){
            array_push($arr_tmp,$v['mall_order_no']);
        }

        $arr_info = array();
        if(!empty($arr_api_list)){
            $arr_info = $this -> coupangModel -> gettestOrderInfo($arr_tmp);
        }

        $arr_response = array();
        $arr_conf = array();

        if(!empty($arr_info)){

            foreach ($arr_info as $val){

                date_default_timezone_set("GMT+0");

                $datetime = date("ymd").'T'.date("His").'Z';

                $mall = $val['mall'];

                $code = "vendorid";
                $arr_mall_info = $this -> coupangModel -> getMallInfo($mall,$code);

                $vendorId = $arr_mall_info[0]['value'];

                $code = 'access_key';
                $access_key = $this -> coupangModel -> getMallInfo($mall,$code);
                $access_key = $access_key[0]['value'];
                $code = 'secret_key';
                $secret_key = $this -> coupangModel -> getMallInfo($mall,$code);
                $secret_key = $secret_key[0]['value'];

                $arr_mall_order_no = explode('_',$val['mall_order_no']);

                $mall_order_no = '';
                $shipmentBoxId = '';
                if(!empty($arr_mall_order_no)){
                    $mall_order_no = $arr_mall_order_no[0];
                }
                if(!empty($arr_mall_order_no)){
                    $shipmentBoxId = $arr_mall_order_no[1];
                }

                $arr_data = array(
                    "vendorId" => $vendorId,
                    "orderSheetInvoiceApplyDtos" =>
                        array(
                            array(
    //                            "invoiceNumber" => "1234",
                                "invoiceNumber" => $val['invoice'],
                                "deliveryCompanyCode" => "CWAY",
                                "shipmentBoxId" => $shipmentBoxId,
    //                            "shipmentBoxId" => "12341234",
                                "orderId"=>$mall_order_no,
                            )

                        ),
                );

                $arr_data = json_encode($arr_data);

    //            echo $arr_data;
    //            exit;

                $method = "POST";
                $path = "/v2/providers/openapi/apis/api/v4/vendors/{$vendorId}/orders/invoices";

                $message = $datetime.$method.$path;

                $algorithm = "HmacSHA256";

                $signature = hash_hmac('sha256', $message, $secret_key);

                $authorization = "CEA algorithm={$algorithm}, access-key={$access_key}, signed-date={$datetime}, signature={$signature}";

                $url = 'https://api-gateway.coupang.com'.$path;


                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl,CURLOPT_POSTFIELDS,$arr_data);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:".$authorization,"X-EXTENDED-TIMEOUT:90000"));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($curl);

                curl_close($curl);

                $arr_result = json_decode($result,true);

                if($arr_result['code'] == "200"){

                    if($arr_result['data']['responseCode'] != "99"){
                        $arr_response['result'] = "Success";
                        $arr_response['result_msg'] = $arr_result['data']['responseMessage'];
                        $arr_response['market'] ="Coupang";
                        $arr_response['test_mall_order_no'] = $val['mall_order_no'];

                            $arr_params = array(
                                'mall_order_no' => $val['mall_order_no'],
                                'invoice' => $val['invoice'],
                                'extra_12' => "Success",
                            );
                            $this -> coupangModel -> updateInfo($arr_params);

                    }else{

                        $arr_response['result'] = "Fail";
                        $arr_response['result_msg'] = $arr_result['data']['responseList'][0]['resultMessage'];
                        $arr_response['market'] ="Coupang";
                        $arr_response['test_mall_order_no'] = $val['mall_order_no'];

                        $arr_params = array(
                            'mall_order_no' => $val['mall_order_no'],
                            'invoice' => $val['invoice'],
                            'status' => "A005",
                            'extra_12' => "Fail",
                            'extra_11' => $arr_response['result_msg'],

                        );
                        $this -> coupangModel -> updateInfo($arr_params);

                        $arr_change_data = array(
                            'mall_order_no' => $val['mall_order_no'],
                            'status' => "A005",
                        );
                        $this -> coupangModel -> updateInfotestOrder($arr_change_data);

                    }

                }else{

                    $arr_response['result'] = "Fail";
                    $arr_response['result_msg'] = $arr_result['data']['responseMessage'];
                    $arr_response['market'] ="Coupang";
                    $arr_response['test_mall_order_no'] = $val['mall_order_no'];

                    $arr_params = array(
                        'mall_order_no' => $val['mall_order_no'],
                        'invoice' => $val['invoice'],
                        'status' => "1234",
                        'extra_12' => "Fail",
                        'extra_11' => $arr_response['result_msg'],

                    );
                    $this -> coupangModel -> updateInfo($arr_params);

                    $arr_change_data = array(
                        'mall_order_no' => $val['mall_order_no'],
                        'status' => "1234",
                    );
                    $this -> coupangModel -> updateInfotestOrder($arr_change_data);

                }

                array_push($arr_conf,$arr_response);

            }

        }


        if(empty($arr_conf)){

            echo "There's no data.";

        }else{

            $str_conf = "";
            foreach ($arr_conf as $v){

                $str_conf .= "Result => {$v['test_mall_order_no']} - {$v['result']}\n";

            }
            echo $str_conf;

        }


    }

    public function find_option($arr_data){

        $arr_option_return = getOrderOptions_v2('쿠팡',$arr_data['item_name']);
        $arr_option_return['itemId']= $arr_data['itemId'];
        $skus = $this->testmodel->getSKUSByOrdeOption($arr_option_return);
        return $skus;

    }

    function changeStatus($data_params){

        date_default_timezone_set("GMT+0");

        $datetime = date("ymd").'T'.date("His").'Z';

        $method = "PUT";
        $path = "/v2/providers/openapi/apis/api/v4/vendors/{$data_params['vendorId']}/ordersheets/atestnowledgement";

        $message = $datetime.$method.$path;

        $algorithm = "HmacSHA256";

        $signature = hash_hmac('sha256', $message, $data_params['secret_key']);

        $authorization = "CEA algorithm={$algorithm}, access-key={$data_params['access_key']}, signed-date={$datetime}, signature={$signature}";

        $url = 'https://api-gateway.coupang.com'.$path;

        $arr_data = array(
            "vendorId" => $data_params['vendorId'],
            "shipmentBoxIds" => array(
                $data_params['shipmentBoxId'],
            ),
        );

        $arr_data = json_encode($arr_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$arr_data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:".$authorization,"X-EXTENDED-TIMEOUT:90000"));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);

        curl_close($curl);

        $arr_result = json_decode($result,true);
//        debug_var($arr_result);

        if(!empty($arr_result['data'])){

            $result = $arr_result['data']['responseList'][0]['resultCode'];

        }else{

            $result = "Fail";

        }

        return $result;

    }




    function getOrderList()
    {

        $c_status = "ACCEPT";

        $nextToken = 1;

        $createdAtTo = date("Y-m-d");

        $createdAtFrom = date("Y-m-d", strtotime("-3 day"));

        $arr_vendorId = array();
        $arr_all_mall_list = $this->coupangModel->getMallIdList();
        foreach ($arr_all_mall_list as $v){
            array_push($arr_vendorId,$v['value']);
        }


        foreach ($arr_vendorId as $v_id) {

            $maxPerPage = 50; //default

            $mall = '';
            $md = '';
            $seller_id = '';
            $access_key = '';
            $secret_key = '';
            $arr_mall_id = $this->coupangModel->getMallId($v_id);
            if (!empty($arr_mall_id)) {
                $mall = $arr_mall_id[0]['mall_id'];
                $code = 'vendorUserId';
                $arr_mall_info = $this->coupangModel->getMallInfo($mall, $code);
                $seller_id = $arr_mall_info[0]['value'];
                $code = 'access_key';
                $access_key = $this->coupangModel->getMallInfo($mall, $code);
                $access_key = $access_key[0]['value'];
                $code = 'secret_key';
                $secret_key = $this->coupangModel->getMallInfo($mall, $code);
                $secret_key = $secret_key[0]['value'];
            }


            date_default_timezone_set("GMT+0");

            $datetime = date("ymd") . 'T' . date("His") . 'Z';

            $method = "GET";
            $path = "/v2/providers/openapi/apis/api/v4/vendors/{$v_id}/ordersheets";
            $query = "createdAtFrom={$createdAtFrom}&createdAtTo={$createdAtTo}&maxPerPage={$maxPerPage}&status={$c_status}&nextToken={$nextToken}";

            $message = $datetime . $method . $path . $query;

            $algorithm = "HmacSHA256";

            $signature = hash_hmac('sha256', $message, $secret_key);

            $authorization = "CEA algorithm={$algorithm}, access-key={$access_key}, signed-date={$datetime}, signature={$signature}";

            $url = 'https://api-gateway.coupang.com' . $path . '?' . $query;


            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:  application/json;charset=UTF-8", "Authorization:" . $authorization, "X-EXTENDED-TIMEOUT:90000"));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);

            curl_close($curl);

            $arr_json_result = json_decode($result, TRUE);


            if ($arr_json_result['code'] == "200") {
                $api_result = "Success";
                $api_result_msg = $arr_json_result['message'];
            } else {
                $api_result = "Fail";
                $api_result_msg = $arr_json_result['message'];
            }

            date_default_timezone_set('Asia/Seoul');

            $arr_insert_data = array(

                "data" => array(
                    "orderList" => array(),
                ),
                "result" => $api_result,
                "result_msg" => $api_result_msg,
                "request_time" => date("Y-m-d H:i:s"),
                "site" => "쿠팡",

            );


            if ($arr_json_result['code'] == "200") {

                $arr_temp = array();

                foreach ($arr_json_result['data'] as $result) {


                    $result['orderedAt'] = str_replace("T", " ", $result['orderedAt']);
                    $result['paidAt'] = str_replace("T", " ", $result['paidAt']);
                    $result['orderItems'][0]['sellerProductItemName'] = str_replace(" ", "|", $result['orderItems'][0]['sellerProductItemName']);

                    $arr_temp['seller_id'] = $seller_id;

                    $arr_temp['mall_order_no'] = $result['orderId'] . "_" . $result['shipmentBoxId'];

                    $arr_temp['place_order_date'] = $result['orderedAt'];
                    $arr_temp['buyer_name'] = $result['orderer']['name'];
                    $arr_temp['buyer_hp'] = $result['orderer']['safeNumber'];
                    $arr_temp['buyer_phone'] = $result['orderer']['ordererNumber'];
                    $arr_temp['delivery_message'] = $result['parcelPrintMessage'];
                    $arr_temp['buyer_id'] = $result['orderer']['email'];

                    $status = '';
                    if ($c_status == 'INSTRUCT' || $c_status == 'ACCEPT') {
                        $status = '0099';
                    }
                    $arr_temp['status'] = $status;

                    $arr_temp['pay_date'] = $result['paidAt'];

                    if (!empty($mall)) {
                        $arr_mall_info = $this->coupangModel->getMdInfo($result['orderItems'][0]['externalVendorSkuCode']);
                        if (!empty($arr_mall_info)) {
                            $md = $arr_mall_info[0]['md'];
                        }
                    }


                    $arr_temp['md'] = $md;
                    $arr_temp['mall'] = $mall;

                    $arr_temp['reci_name'] = $result['receiver']['name'];
                    $arr_temp['reci_hp'] = $result['receiver']['safeNumber'];
                    $arr_temp['reci_phone'] = $result['receiver']['receiverNumber'];
                    $address = $result['receiver']['addr1'] . " " . $result['receiver']['addr2'];
                    $arr_temp['reci_address_1'] = $address;
                    $arr_temp['reci_zip_code'] = $result['receiver']['postCode'];

                    $arr_temp['item_name'] = $result['orderItems'][0]['vendorItemName'];
                    $arr_temp['product_price'] = $result['orderItems'][0]['orderPrice'];
                    $arr_temp['total_order_price'] = ($result['orderItems'][0]['orderPrice']);
//                    $arr_temp['total_order_price'] = ($result['orderItems'][0]['orderPrice']) * ($result['orderItems'][0]['shippingCount']);
                    $arr_temp['real_pay_price'] = ($result['orderItems'][0]['orderPrice']);
//                    $arr_temp['real_pay_price'] = ($result['orderItems'][0]['orderPrice']) * ($result['orderItems'][0]['shippingCount']);
                    $arr_temp['itemId'] = $result['orderItems'][0]['externalVendorSkuCode'];


                    $arr_temp['itemID_original'] = $result['orderItems'][0]['externalVendorSkuCode'];
                    $arr_temp['delivery_price'] = $result['orderItems'][0]['deliveryChargeTypeName'];
                    $arr_temp['option_user_code'] = $result['orderItems'][0]['vendorItemId'];

                    //20210201 find skus logic.
                    $item_cnt = $result['orderItems'][0]['shippingCount'];
                    $full_item_name = $arr_temp['item_name'] . "-" . $item_cnt;

                    $arr_data = array(
                        "item_name" => $full_item_name,
                        "itemId" => $arr_temp['itemId'],
                    );
                    $skus = $this->find_option($arr_data);
                    $arr_temp['skus'] = $skus;

                    $arr_temp['tax_code'] = $result['overseaShippingInfoDto']['personalCustomsClearanceCode'];

                    $arr_temp['order_cnt'] = $result['orderItems'][0]['shippingCount'];
                    $arr_temp['order_option'] = $result['orderItems'][0]['sellerProductItemName'];

                    $arr_temp['delivery_method'] = $result['deliveryCompanyName'];
                    $arr_temp['invoice'] = $result['invoiceNumber'];
                    $arr_temp['settle_date'] = $result['deliveredDate'];
                    $arr_temp['site'] = "쿠팡";
                    $arr_temp['extra_5'] = "Air";
                    $arr_temp['create_date'] = date("Y-m-d H:i:s");
                    $arr_temp['warehouse_no'] = "5001";

                    //                $arr_temp['result'] = "Success";
                    //                $arr_temp['result_msg'] = "{$arr_json_result['message']}";

                    array_push($arr_insert_data['data']['orderList'], $arr_temp);

                    //TODO db insert
                    $this->coupangModel->insertOrderList($arr_temp);

                    //TODO change status
                    $data_params = array(
                        'vendorId' => $v_id,
                        'shipmentBoxId' => $result['shipmentBoxId'],
                        'access_key' => $access_key,
                        'secret_key' => $secret_key,
                    );
                    $this->changeStatus($data_params);

                    //0.01
                    //usleep(100000000);

                }

            }

            if($arr_insert_data['result'] == 'Fail'){
                if($arr_insert_data['result_msg'] = 'Invalid vendor ID'){
                    mail("test0@naver.com", "쿠팡 Auto Order Error List", "{$mall} 의 쿠팡 업체코드({$v_id}) 값이 정확한지 확인해주세요.", "From: ethan@test.com");
                }
            }

        }

    }









}