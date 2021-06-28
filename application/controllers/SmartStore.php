<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class SmartStore extends CI_Controller
{

    var $data;
    var $module_name = "smartStore";
    var $username = "";
    var $mall_id = "";

    public function __construct()
    {
        parent::__construct();

        $this->load->database();

        $this->load->helper('url');
        $this->load->helper('array_helper');
        $this->load->helper('shop_helper');
        $this->load->helper('debug_helper');
        $this->load->helper('form');

        $this->load->library('curl');
        $this->load->library('simplexml');
        $this->load->library('ion_auth');
        $this->load->library('smartstore/Smartstoresecret');
        $this->load->library('MarketAPI/testItemAuth');
        $this -> load -> library('simple_html_dom');
        $this -> load -> model('testmodel', 'testmodel');
        $this -> load -> model('testModernModel', 'testModernModel');

        $this->data = array(
            "bootstrap_version" => "/assets/test/bootstrap",
            "bootstrap_theme" => "/assets/bootstrap-3.3.3.6/css/flatly",//없으면 "" 있으면 superhero/
            "jquery_version" => "/assets/js/2.2.0",
            "module_name" => $this->module_name,
            "asset_directory" => "/assets/test", //없으면 "" 있으면 superhero/
            "version" => "1.0.0"
        );

        if (!$this->ion_auth->logged_in()) {

            $no_auth_method = array();
            if (!in_array($this->router->fetch_method(), $no_auth_method)) {
                redirect('/auth/login/?redirect=' . urlencode(current_url()), 'refresh');
            }
        }
        else {

            $this->data['user_info']= $this->ion_auth->user()->row();
            $this->username         = $this->data['user_info']->username;
            $this->mall_id          = $this->data['user_info']->mall_id;
            $this->data['username'] = $this->data['user_info']->username;
        }

        $this->data['active_menu'] = $this->router->fetch_method();

    }

    function registerItem(){

//        debug_var("you can't access this function after working time ..");
//        exit;

        $data = $this -> data;

        $arr_response_map = array();

        $chetested_item = IsNullOrEmptyStringSetDefault($this -> input -> get_post("chetested_item"), "");

        $arr_chetested_item = explode(',',$chetested_item);

        //1. GET AND CHEtest SmartStore ACCESS KEY
        $mall_id        = $this -> data['user_info']->mall_id;
        $code           = "SS_AK";
        $retail         = "SS";

        unset($param);
        $param              = array();
        $param['mall_id']   = $mall_id;
        $param['code']      = $code;

        $arr_MarketAPIAccessKey = $this->testModernModel->getMarketAPIAccessKey($param);

        $responseMap = array();

        if(sizeof($arr_MarketAPIAccessKey) < 1){

            $responseMap["Result"] = "FAIL";
            $responseMap["Message"] = "Sorry, you can't upload data to SmartStore. Please contact administrator.";
            echo json_encode($responseMap);
            return "";

        }

        $MarketAPIAccessKey = $arr_MarketAPIAccessKey[0]['value'];


        //2. CHEtest ACCESS TO SmartStore API
        unset($headerMap);
        $headerMap                      = array();
        $headerMap["MALL_ID"]           = $mall_id;
        $headerMap["MARKET"]            = $retail;
        $headerMap["API_ACCESS_KEY"]    = $MarketAPIAccessKey; //SmartStore test ACCESS KEY

        $itemId = IsNullOrEmptyStringSetDefault($this -> input -> get_post("itemId"), "");

        $is_bool = $this -> testitemauth -> CHEtest_MARKET_API_ACCESS_KEY($headerMap);

        $arr_reuslt = array();
        $arr_json = array();

        $smart_productId = '';

        foreach ($arr_chetested_item as $values){

            $msg = '';

            $arr_register_result = array();

            if($is_bool){

                $target_url = "{$_SERVER['HTTP_HOST']}/testitem/gettestItemInfo?itemId={$values}";

                unset($headers);
                $headers = array(
                    "Content-Type: application/json",
                    "charset=UTF-8",
                    "Authorization: Basic test"
                );

                unset($params);
                $params = array();
                $params['CURLOPT_URL'] = $target_url;
                $params['CURLOPT_HTTPHEADER'] = $headers;

                $response = $this->curl->curlExec($params);

                $arr_json = json_decode($response['Message'],TRUE);

            }else{
                $arr_result['result'] = 'Fail';
                $arr_result['result_code'] = '412';
                $arr_result['result_msg'] = '인증키를 확인해주세요.';
                echo json_encode($arr_result);
                exit;
            }

            $PatestDate = date("Y-m", strtotime("-6 month", time()));

            $arr_json['PatestDate'] = $PatestDate;


            // register for smartstore

            $naver_id = $arr_json['md'];
            $last_result = '';

            // this is seller id or mall_id for test
            unset($code);
            $code = 'SS_Seller_id';
            $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
            $arr_json['SS_Seller_id'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

            $seller_id = $arr_json['SS_Seller_id'];

//            debug_var($arr_json['data']['skuBase']);
//            exit;

            if(empty($seller_id)){

                $msg = $values."\n상품등록에 실패하였습니다. Seller_id를 반드시 설정해주세요.";

            }else{

                // license
                $accessLicense = '123';

                // secret key
                $key = '123';

                $service = 'ProductService';
                $operation = 'ManageProduct';

                // create timestamp
                $timestamp = $this -> smartstoresecret -> getTimestamp();

                // create signature
                $request_str = $timestamp.$service.$operation;
                $signature = $this -> smartstoresecret -> generateSign($request_str,$key);


                // size , color -> string info
                $arr_size = array();
                $arr_color = array();
                $arr_one_option = array();

                $chetest_count = count(explode(';',isset($arr_json['data']['skuBase']['skus'][0]['prop'])?$arr_json['data']['skuBase']['skus'][0]['prop']:""));

                if($chetest_count == 1){

                    if(!empty($arr_json['data']['skuBase']['skus'])){

                        foreach ($arr_json['data']['skuBase']['skus'] as $value){

                            array_push($arr_one_option,$value);

                        }

                    }

                    if(!empty($arr_json['data']['skuBase']['props'])){

                        foreach ($arr_json['data']['skuBase']['props'] as $value){

                            array_push($arr_one_option,$value);

                        }

                    }

                }else if($chetest_count == 2){

                    foreach ($arr_json['data']['skuBase']['skus'] as $value){

                        $arr_devide_size = explode(';',$value['prop']);

                        if($arr_devide_size[0]){
                            array_push($arr_color,$arr_devide_size[0]);
                        }

                        if($arr_devide_size[1]){
                            array_push($arr_size,$arr_devide_size[1]);
                        }

                    }

                }

//                debug_var("Exception");
//                debug_var($arr_json['data']['skuBase']['props']);
//                debug_var($arr_json['data']['skuBase']['skus']);
//                exit;

                $arr_color = array_unique($arr_color);
                $arr_size = array_unique($arr_size);

                $str_color = implode(',',$arr_color);
                $str_size = implode(',',$arr_size);

                $arr_json['accessLicense'] = $accessLicense;
                $arr_json['timestamp'] = $timestamp;
                $arr_json['signature'] = $signature;
                $arr_json['request_id'] = $naver_id;
                //$arr_json['itemId'] = $itemId;
                $arr_json['itemId'] = $values;
                $arr_json['version'] = "4.0";
                $arr_json['not_essential'] = "";
                $arr_json['seller_id'] = $seller_id;
                $arr_json['str_color'] = $str_color;
                $arr_json['str_size'] = $str_size;


                // 전송 전 productId 존재여부 살피기
                $arr_chetest_params = array(
                    //'itemId'=>$itemId,
                    'itemId'=>$values,
                    'retail'=>'smartstore',
                    'mall'=>$arr_json['mall'],
                );

                $chetest_result = $this->testModernModel->_chetest_test_item_retail_productId($arr_chetest_params);

                $arr_json['productId'] = '';
                if(!empty($chetest_result[0]['retail_itemId'])){
                    $arr_json['productId'] = $chetest_result[0]['retail_itemId'];
                    $responseMap['flag'] = 'existed';
                }else{
                    $responseMap['flag'] = 'posted';
                }

                // 반품교환지 주소를 가져옵니다.
                $code = 'SS_ReturnDeliveryCode';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['ReturnAddressId'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 몰이름 가져옵니다.
                $code = 'SS_MallName';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['MallName'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 출고지 주소를 가져옵니다.
                $code = 'SS_StartDeliveryCode';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['ShippingAddressId'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 반품비를 가져옵니다.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_ReturnFee';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['ReturnFee'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"20000";

                unset($code);
                unset($arr_setting_result);
                $code = 'SS_kc_confirmation';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $kc_confirmation = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"N";
                $arr_json['kc_confirmation'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"N";

                // 교환비를 가져옵니다.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_ExchangeFee';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['ExchangeFee'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"40000";

                // 상단이미지를 가져옵니다.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_top_img';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['top_img'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 하단이미지를 가져옵니다.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_bottom_img';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['bottom_img'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 할인가격을 가져옵니다.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_discountAmount';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['discountAmount'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // A/S 안내값을 가져옵니다.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_AfterServiceGuideContent';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['AfterServiceGuideContent'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"상품상세 이미지 참조";

                // 구매 시 포인트.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_point';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['Point'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 이벤트 문구.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_eventMsg';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['eventMsg'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 배송 속성 코드.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_ExpectedDeliveryPeriod';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['ExpectedDeliveryPeriod'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:"";

                // 배송비.
                unset($code);
                unset($arr_setting_result);
                $code = 'SS_BaseFee';
                $arr_setting_result = $this->gettestSetting($arr_json['mall'],$code);
                $arr_json['BaseFee'] = isset($arr_setting_result[0]['value'])?$arr_setting_result[0]['value']:0;


                if($arr_json['BaseFee'] != 0){
                    $arr_json['FeeType'] = '3';
                }else{
                    $arr_json['FeeType'] = '1';
                }

                //youtube_url
//                $arr_youtube_info = $this -> testmodel -> getYoutubeHistory($values);
//                $youtube_url = '';
//                if(!empty($arr_youtube_info)){
//                    debug_var($arr_youtube_info);
//                    $youtube_url = $arr_youtube_info[0]['upload_url'];
//                }


                // html img 추출
                $html_description = str_get_html($arr_json['data']['item']['sizeHTML']);
                $arr_tmp = array();
                foreach($html_description->find('img') as $element){

                    $image_url =$element->src;

                    array_push($arr_tmp,$image_url);

                }



                if(!empty($arr_json['top_img'])){

                    $arr_json['top_img'] = str_replace("test.","image.",$arr_json['top_img']);
                    array_unshift($arr_tmp,$arr_json['top_img']);

                }

                if(!empty($arr_json['bottom_img'])){

                    $arr_json['bottom_img'] = str_replace("test.","image.",$arr_json['bottom_img']);
                    array_push($arr_tmp,$arr_json['bottom_img']);

                }

//                debug_var($arr_tmp);
//                exit;


//                $arr_sizeHTML_for_options = explode('<p>',$arr_json['data']['item']['sizeHTML']);
//
//                $arr_optionImgUrl = array();
//                if(!empty($arr_sizeHTML_for_options)){
//
//                    $arr_json['data']['item']['it_explain'] = array();
//
//
//                    foreach ($arr_sizeHTML_for_options as $value){
//
//                        array_push($arr_json['data']['item']['it_explain'],$this->getAttribute($value,'src'));
//
//                    }
//
//                }

//                debug_var($arr_json['data']['item']['it_explain']);
//                exit;

                //$arr_optionImgUrl = array_filter($arr_optionImgUrl);

//                $arr_sizeHTML = explode('<div>',$arr_json['data']['item']['sizeHTML']);
//
//                $arr_sizeHTML[1] = isset($arr_sizeHTML[1])?$arr_sizeHTML[1]:array();
//
//                $sizeChart_url = '';
//                if(!empty($arr_sizeHTML[1])){
//                    $sizeChart_url = $this->getAttribute($arr_sizeHTML[1],'src');
//                    array_push($arr_json['data']['item']['it_explain'],$sizeChart_url);
//                }


//                if(!empty($arr_json['top_img'])){
//
//                    $arr_json['top_img'] = str_replace("test.","image.",$arr_json['top_img']);
//                    array_unshift($arr_json['data']['item']['it_explain'],$arr_json['top_img']);
//
//                }
//
//                if(!empty($arr_json['bottom_img'])){
//
//                    $arr_json['bottom_img'] = str_replace("test.","image.",$arr_json['bottom_img']);
//                    array_push($arr_json['data']['item']['it_explain'],$arr_json['bottom_img']);
//
//                }


//                unset($arr_json['data']['item']['sizeHTML']);

                $strHTML = "<p>";

                foreach ($arr_tmp as $v){
                    if(!empty($v)) {
                        $strHTML .= "<img src=\"$v\"><br>";
                    }
                }

//                if(!empty($optionImgUrl)){
//                    foreach ($arr_optionImgUrl as $v){
//                        if(!empty($v)){
//                            $strHTML .= "<img src=\"$v\"><br>";
//                        }
//                    }
//                }

                $strHTML .= "</p>";

                $arr_json['data']['item']['sizeHTML'] = $strHTML;


//                debug_var($arr_json['data']['item']['sizeHTML']);
//                exit;


                // 카테고리 정보를 가져옵니다.
                $arr_category_id = $this -> testmodel -> getSmartStoreCategoryId($arr_json['data']['item']['ca_id']);
                $arr_json['categoryId'] = isset($arr_category_id[0]['smartStore_ca_id'])?$arr_category_id[0]['smartStore_ca_id']:"";

                $chetest_skus = count($arr_json['data']['skuBase']['skus']);
                $chetest_props = count($arr_json['data']['skuBase']['props']);

                if($chetest_skus == 0 || $chetest_props == 0){

                    $msg .= "\nsize / color 정보를 정확히 설정해주세요. formal json skus or props error..";

                }else{

                    //debug_var($arr_json['categoryId']);

                    if(empty($arr_json['categoryId'])){

                        //debug_var($values." : category 정보를 세팅해주세요.");
                        $msg .= "\n정확한 category id 정보를 세팅해주세요.";

                    }else{

                        // 속성 값 작업 working

                        // attribute 조회 결과
//                        $response_attribute_xml = $this->requestApiInfo("GetAttributeList",$arr_json);
//                        $arr_attribute_response = $this->simplexml->xml_parse($response_attribute_xml);

                        // attribute Value 조회 결과
                        $response_attributeValue_xml = $this->requestApiInfo("GetAttributeValueList",$arr_json);
                        $arr_attributeValue_response = $this->simplexml->xml_parse($response_attributeValue_xml);


//                        $arr_main_selection_code = isset($arr_attribute_response['soapenv:Body']['n:GetAttributeListResponse']['AttributeList']['n:Attribute'])?$arr_attribute_response['soapenv:Body']['n:GetAttributeListResponse']['AttributeList']['n:Attribute']:"";
                        $arr_detail_selection_code = isset($arr_attributeValue_response['soapenv:Body']['n:GetAttributeValueListResponse']['AttributeValueList']['n:AttributeValue'])?$arr_attributeValue_response['soapenv:Body']['n:GetAttributeValueListResponse']['AttributeValueList']['n:AttributeValue']:"";


//                        debug_var($arr_main_selection_code);
//                        debug_var($arr_detail_selection_code);
//                        exit;

                        // 해당 아이템의 속성 번역값을 가져온다.
                        $arr_props = $this -> testmodel -> getItemInfoProps($values);

                        $arr_attribute_values = array(
                            'AttributeSeq' => array(),
                            'AttributeValueSeq' => array(),
                        );

//                        debug_var($arr_props);
//                        exit;

                        foreach ($arr_props as $props_values){

                            foreach ($arr_detail_selection_code as $detail_values){

                                $detail_values['n:MinAttributeValue'] = isset($detail_values['n:MinAttributeValue'])?$detail_values['n:MinAttributeValue']:"";

                                if($props_values['props_value_ko'] === $detail_values['n:MinAttributeValue']){

                                    array_push($arr_attribute_values['AttributeSeq'],$detail_values['n:AttributeSeq']);
                                    array_push($arr_attribute_values['AttributeValueSeq'],$detail_values['n:AttributeValueSeq']);

                                }

                            }

                        }

//                        debug_var($arr_attribute_values);
//                        exit;

                        $arr_attribute_values = isset($arr_attribute_values)?$arr_attribute_values:"";
                        $arr_json['arr_attribute_values'] = $arr_attribute_values;

//                        debug_var($arr_json['arr_attribute_values']);
//                        exit;

                        // category 결과로 인증값을 기입해야 합니다.
                        $response_category_xml = $this->requestApiInfo("category_info",$arr_json);

                        $arr_category_response = $this->simplexml->xml_parse($response_category_xml);
                        $confirm_category = isset($arr_category_response['soapenv:Body']['n:GetCategoryInfoResponse']['Category']['n:ExceptionalCategoryList']['n:ExceptionalCategory']['n:Code'])?$arr_category_response['soapenv:Body']['n:GetCategoryInfoResponse']['Category']['n:ExceptionalCategoryList']['n:ExceptionalCategory']['n:Code']:"";

                        // 인증 절차를 거친다.
                        if($confirm_category == 'KC' && $kc_confirmation == 'N'){

                            $arr_json['kc_type_exclusion'] = 'N';
                            $arr_json['kc_type'] = 'SAFE_CRITERION';

                            $msg .= "\nKC 인증철자가 필요한 상품은 업로드 불가합니다.";

                        }else{

                            $arr_json['kc_type_exclusion'] = 'Y';

                            $arr_json['confirm_category'] = $confirm_category;

                            if($kc_confirmation == 'Y'){
                                $arr_json['confirm_category'] = 'SAFE';
                                $arr_json['kc_type_exclusion'] = 'KC_EXEMPTION';
                                $arr_json['kc_type'] = 'OVERSEAS';
                            }

                            // china code
                            $arr_json['original_area_code'] = '0200037';
                            $arr_json['importer'] = 'test';
                            $arr_json['tax_type'] = 'TAX';
                            $arr_json['img_reference'] = '상품상세 이미지 참조';


                            // 이미지 개수는 7개로 제한한다.
                            $arr_json['data']['item']['images'] = array_slice($arr_json['data']['item']['images'], 0, 6);

                            // get img response url after upload img to naver
                            $response_img_xml = $this->requestApiInfo("uploadImg",$arr_json);
                            $arr_img_response = $this->simplexml->xml_parse($response_img_xml);

                            $arr_img['uploaded_img'] = array();
                            $arr_img_result = isset($arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image'])?$arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image']:"";

                            $img_criteria = isset($arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image'][0])?$arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image']:"one";


                            if($img_criteria == 'one'){

                                array_push($arr_img['uploaded_img'],isset($arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image']['n:URL'])?$arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image']['n:URL']:"");

                            }else{

                                if(!empty($arr_img_result)){

                                    foreach ($arr_img_response['soapenv:Body']['n:UploadImageResponse']['ImageList']['n:Image'] as $value){
                                        array_push($arr_img['uploaded_img'],isset($value['n:URL'])?$value['n:URL']:"");
                                    }

                                }else{

                                    $msg .= "\n이미지 업로드 Error.";

                                }

                            }


                            $arr_json['arr_img'] = $arr_img;

                            // get Information
                            $strjson = $this->load->view('test/smartstore/smartstore_register_xml_v3', $arr_json, TRUE);

//                            debug_var(htmlentities($strjson));

                            // send data to naver
                            $target_url = "http://ec.api.naverc.com/ShopN/{$service}";

                            $arr_register_data = array(
                                'strjson'=>$strjson,
                                'target_url'=>$target_url,
                                'service'=>$service,
                                'operation'=>$operation
                            );

                            //register
                            $response_register_xml = $this->forwardCurl($arr_register_data);

//                            debug_var($response_register_xml);
//                            exit;

                            $arr_register_result = $this->simplexml->xml_parse($response_register_xml);

//                            debug_var($arr_register_result);
//                            exit;

                            $fail_result = isset($arr_register_result['soapenv:Body']['n:ManageProductResponse']['n:Error']['n:Detail'])?$arr_register_result['soapenv:Body']['n:ManageProductResponse']['n:Error']['n:Detail']:"";


                            // 판매중지 상태로 변경합니다.
                            //        $response_sellStatus_xml = $this->requestApiInfo("sellStatus",$arr_json);

                            $result_code = isset($arr_register_result['soapenv:Body']['n:ManageProductResponse']['n:ResponseType'])?$arr_register_result['soapenv:Body']['n:ManageProductResponse']['n:ResponseType']:"";
                            $smart_productId = isset($arr_register_result['soapenv:Body']['n:ManageProductResponse']['ProductId'])?$arr_register_result['soapenv:Body']['n:ManageProductResponse']['ProductId']:"";

                            if($result_code == 'SUCCESS'){

                                $arr_params = array(
                                    'itemId' => $values,
                                    'mall' => $arr_json['mall'],
                                    'retail' => 'smartstore',
                                    'retail_link' => "https://smartstore.naverc.com/{$arr_json['MallName']}/products/{$smart_productId}",
                                    'create_date' => date("Y-m-d H:i:s"),
                                    'retail_itemId' => $smart_productId,

                                );

                                if($arr_json['productId'] == ''){

                                    $result = $this->testModernModel->_chetest_test_item_retail_productId($arr_chetest_params);

                                    if(!empty($result)){

                                        $arr_update_params = array(
                                            'retail_itemId' => $smart_productId,
                                            'create_date' => date("Y-m-d H:i:s"),
                                            'retail_link' => "https://smartstore.naverc.com/{$arr_json['MallName']}/products/{$smart_productId}",
                                            'itemId' => $values,
                                            'retail' => 'smartstore',
                                            'mall' => $arr_json['mall'],
                                    );

                                        $this->testModernModel->update_item_retail($arr_update_params);

                                    }else{

                                        $this->testModernModel->_insert2_test_item_retail_list($arr_params);

                                    }

                                }

                                $smart_productId = $arr_register_result['soapenv:Body']['n:ManageProductResponse']['ProductId'];

                            }else{

                                $msg .= "{$fail_result}";

                            }

                            if(!empty($arr_json['productId'])){
                                $smart_productId = $arr_json['productId'];
                            }

                            // option을 등록
                            $arr_option_info = array(

                                'optionInfo'=>$arr_json['data']['skuBase']['skus'],
                                'props'=>$arr_json['data']['skuBase']['props'],
                                'productId'=>$smart_productId,
                                'version'=>'2.0',
                                'requestId'=>'test',
                                'sellerId'=>$seller_id,
                                'option_subject'=>$arr_json['data']['item']['it_option_subject'],
                                'original_price'=>$arr_json['data']['item']['it_scrap_price'],
                                'one_option'=>$arr_one_option,

                            );

                            $response_option_xml = $this->requestApiInfo("options",$arr_option_info);

                            $arr_option_result = $this->simplexml->xml_parse($response_option_xml);

                            //debug_var($arr_option_result);

                            unset($result_code);
                            $result_code = isset($arr_option_result['soapenv:Body']['n:ManageOptionResponse']['n:ResponseType'])?$arr_option_result['soapenv:Body']['n:ManageOptionResponse']['n:ResponseType']:"";

                            $option_fail_result = isset($arr_option_result['soapenv:Body']['n:ManageOptionResponse']['n:Error']['n:Detail'])?$arr_option_result['soapenv:Body']['n:ManageOptionResponse']['n:Error']['n:Detail']:"";


                            // 옵션 등록 실패 시
                            if($result_code == 'ERROR'){

                                // retail history 에서 제거
                                $arr_delete_params = array(
                                    'retail' => 'smartstore',
                                    'itemId' => $arr_json['itemId'],
                                );

                                // 기록에서 삭제합니다.
                                $this->testModernModel->_delete_test_item_retail($arr_delete_params);

                                $arr_request_info = array(
                                    'productId'=>$smart_productId,
                                    'version'=>'2.0',
                                    'requestId'=>'test',
                                    'sellerId'=>$seller_id,
                                );

                                // 옵션 실패하면 해당상품을 삭제 합니다.
                                $this->requestApiInfo("delete_product",$arr_request_info);

                                $msg .= "\n옵션 등록에 실패하였습니다. - {$option_fail_result} \n옵션을 수정 한 후에 다시 등록해주시기 바랍니다.";

                            }else{

                                $last_result = 'Success';

                            }

                        }

                    }

                }

            }

            //debug_var($msg);

            // 0.01 seconds delay

            $responseMap['itemId'] = $values;
            $responseMap['Result'] = $last_result;
            $responseMap['it_name'] = $arr_json['data']['item']['it_name'];
            $responseMap['retail_itemId'] = $smart_productId;
            $responseMap['Message'] = $msg;

            //debug_var($responseMap);

            array_push($arr_response_map, $responseMap);

            usleep(10000);

        }


        $data['API_NAME'] = "SmartStore";
        $data['arr_product'] = $arr_response_map;
        $data['include_css_file'] = "include_css/review_item_mgt_css.php";
        $this->load->view('test/market_api_result_view', $data);


    }


    function requestApiInfo($segment,$arr_data = null){

        $seller_id = isset($arr_data['SS_Seller_id'])?$arr_data['SS_Seller_id']:"";

        $accessLicense = '123';

        // secret key
        $key = '123';

        $operation = '';

        $service = '';

        switch ($segment){
            case "area" :
                $operation = 'GetOriginAreaList';
                $service = 'ProductService';
                break;
            case "address" :
                $operation = 'GetAddressBookList';
                $service = 'AddressBookService';
                break;
            case "model" :
                $operation = 'GetModelList';
                $service = 'ProductService';
                break;
            case "sellStatus" :
                $operation = 'ChangeProductSaleStatus';
                $service = 'ProductService';
                break;
            case "all_category" :
                $operation = 'GetAllCategoryList';
                $service = 'ProductService';
                break;
            case "GetAttributeList" :
                $operation = 'GetAttributeList';
                $service = 'ProductService';
                break;
            case "GetAttributeValueList" :
                $operation = 'GetAttributeValueList';
                $service = 'ProductService';
                break;
            case "uploadImg" :
                $operation = 'UploadImage';
                $service = 'ImageService';
                break;
            case "productList" :
                $operation = 'GetProductList';
                $service = 'ProductService';
                break;
            case "delete_product" :
                $operation = 'DeleteProduct';
                $service = 'ProductService';
                break;
            case "options" :
                $operation = 'ManageOption';
                $service = 'ProductService';
                break;
            case "options_confirm" :
                $operation = 'GetOption';
                $service = 'ProductService';
                break;
            case "return_company" :
                $operation = 'GetReturnsCompanyList';
                $service = 'DeliveryInfoService';
                break;
            case "category_info" :
                $operation = 'GetCategoryInfo';
                $service = 'ProductService';
                break;
            case "GetCustomerInquiryList" :
                $operation = 'GetCustomerInquiryList';
                $service = 'CustomerInquiryService';
                break;
            case "AnswerCustomerInquiry" :
                $operation = 'AnswerCustomerInquiry';
                $service = 'CustomerInquiryService';
                break;
            case "GetProductOrderInfoList" :
                $operation = 'GetProductOrderInfoList';
                $service = 'SellerService41';
                break;
            case "GetChangedProductOrderList" :
                $operation = 'GetChangedProductOrderList';
                $service = 'SellerService41';
                break;
            case "PlaceProductOrder" :
                $operation = 'PlaceProductOrder';
                $service = 'SellerService41';
                break;
            case "ApproveCancelApplication" :
                $operation = 'ApproveCancelApplication';
                $service = 'SellerService41';
                break;
            case "CancelSale" :
                $operation = 'CancelSale';
                $service = 'SellerService41';
                break;
            case "DelayProductOrder" :
                $operation = 'DelayProductOrder';
                $service = 'SellerService41';
                break;
            case "ShipProductOrder" :
                $operation = 'ShipProductOrder';
                $service = 'SellerService41';
                break;
            case "RequestReturn" :
                $operation = 'RequestReturn';
                $service = 'SellerService41';
                break;
            case "ApproveReturnApplication" :
                $operation = 'ApproveReturnApplication';
                $service = 'SellerService41';
                break;
            case "RejectReturn" :
                $operation = 'RejectReturn';
                $service = 'SellerService41';
                break;
            case "WithholdReturn" :
                $operation = 'WithholdReturn';
                $service = 'SellerService41';
                break;
            case "ReleaseReturnHold" :
                $operation = 'ReleaseReturnHold';
                $service = 'SellerService41';
                break;
            case "ApproveCollectedExchange" :
                $operation = 'ApproveCollectedExchange';
                $service = 'SellerService41';
                break;
            case "ReDeliveryExchange" :
                $operation = 'ReDeliveryExchange';
                $service = 'SellerService41';
                break;
            case "RejectExchange" :
                $operation = 'RejectExchange';
                $service = 'SellerService41';
                break;
            case "WithholdExchange" :
                $operation = 'WithholdExchange';
                $service = 'SellerService41';
                break;
            case "ReleaseExchangeHold" :
                $operation = 'ReleaseExchangeHold';
                $service = 'SellerService41';
                break;
            default :
                break;
        }


        $version = '';

        if($service == 'ProductService'){
            // 상품 api 경우
            $version = '2.0';
        }else{
            // 주문 api 경우
            $version = '4.1';
        }

        // create timestamp
        $timestamp = $this -> smartstoresecret -> getTimestamp();

        $naver_id = isset($arr_data['md'])?$arr_data['md']:"";

        // create signature
        $request_str = $timestamp.$service.$operation;
        $signature = $this -> smartstoresecret -> generateSign($request_str,$key);

        $arr_json['accessLicense'] = $accessLicense;
        $arr_json['timestamp'] = $timestamp;
        $arr_json['signature'] = $signature;
        $arr_json['naver_id'] = $naver_id;
        $arr_json['request_id'] = $naver_id;
        $arr_json['version'] = $version;
        $arr_json['not_essential'] = "";
        $arr_json['seller_id'] = $seller_id;


        $arr_data['accessLicense'] = $accessLicense;
        $arr_data['timestamp'] = $timestamp;
        $arr_data['signature'] = $signature;
        $arr_data['naver_id'] = $naver_id;
        $arr_data['version'] = $version;
        $arr_data['seller_id'] = $seller_id;


        $strjson = '';
        switch ($segment){
            case "area" :
                $strjson = $this->load->view('test/smartstore/smartstore_original_area_xml', $arr_data, TRUE);
                break;
            case "model" :
                $strjson = $this->load->view('test/smartstore/smartstore_model_xml', $arr_data, TRUE);
                break;
            case "all_category" :
                $strjson = $this->load->view('test/smartstore/smartstore_all_category_xml', $arr_data, TRUE);
                break;
            case "GetAttributeList" :
                $strjson = $this->load->view('test/smartstore/smartstore_attributeList_xml', $arr_data, TRUE);
//                debug_var($strjson);
                break;
            case "GetAttributeValueList" :
                $strjson = $this->load->view('test/smartstore/smartstore_attributeValueList_xml', $arr_data, TRUE);
                break;
            case "uploadImg" :
                $strjson = $this->load->view('test/smartstore/smartstore_uploadImg_xml', $arr_data, TRUE);
                break;
            case "productList" :
                $strjson = $this->load->view('test/smartstore/smartstore_productList_xml', $arr_data, TRUE);
                break;
            case "delete_product" :
                //debug_var($arr_data);
                $strjson = $this->load->view('test/smartstore/smartstore_delete_product_xml', $arr_data, TRUE);
                break;
            case "options" :
                $strjson = $this->load->view('test/smartstore/smartstore_options_xml_v2', $arr_data, TRUE);
                //debug_var(htmlentities($strjson));
                break;
            case "sellStatus" :
                $arr_data['stop'] = 'SUSP';
                $strjson = $this->load->view('test/smartstore/smartstore_sellStatus_xml', $arr_data, TRUE);
                break;
            case "options_confirm" :
                $strjson = $this->load->view('test/smartstore/smartstore_options_confirm_xml', $arr_data, TRUE);
                break;
            case "return_company" :
                $strjson = $this->load->view('test/smartstore/smartstore_return_company_xml', $arr_data, TRUE);
                break;
            case "category_info" :
                $strjson = $this->load->view('test/smartstore/smartstore_category_info_xml', $arr_data, TRUE);
                //echo $strjson;
                break;
            case "address" :
                $strjson = $this->load->view('test/smartstore/smartstore_address_xml', $arr_data, TRUE);
                break;
            case "customer_inquery_list" :
                $strjson = $this->load->view('test/smartstore/smartstore_address_xml', $arr_data, TRUE);
                break;
            case "GetProductOrderInfoList" :
                $strjson = $this->load->view('test/smartstore/smartstore_order_info_xml', $arr_data, TRUE);
                break;
            case "GetCustomerInquiryList" :
                $strjson = $this->load->view('test/smartstore/smartstore_inquery_list_xml', $arr_data, TRUE);
                break;
            case "GetChangedProductOrderList" :
                $strjson = $this->load->view('test/smartstore/smartstore_order_list_xml', $arr_data, TRUE);
                break;
            case "PlaceProductOrder" :
                $strjson = $this->load->view('test/smartstore/smartstore_product_order_xml', $arr_data, TRUE);
                break;
            case "CancelSale" :
                $strjson = $this->load->view('test/smartstore/smartstore_cancel_sale_xml', $arr_data, TRUE);
                break;
            case "DelayProductOrder" :
                $strjson = $this->load->view('test/smartstore/smartstore_delay_order_xml', $arr_data, TRUE);
                break;
            case "ShipProductOrder" :
                $strjson = $this->load->view('test/smartstore/smartstore_ship_order_xml', $arr_data, TRUE);
                break;
            case "RequestReturn" :
                $strjson = $this->load->view('test/smartstore/smartstore_request_return_xml', $arr_data, TRUE);
                break;
            case "ApproveReturnApplication" :
                $strjson = $this->load->view('test/smartstore/smartstore_approve_return_order_xml', $arr_data, TRUE);
                break;
            case "RejectReturn" :
                $strjson = $this->load->view('test/smartstore/smartstore_reject_return_xml', $arr_data, TRUE);
                break;
            case "WithholdReturn" :
                $strjson = $this->load->view('test/smartstore/smartstore_withhold_return_xml', $arr_data, TRUE);
                break;
            case "ReleaseReturnHold" :
                $strjson = $this->load->view('test/smartstore/smartstore_release_return_xml', $arr_data, TRUE);
                break;
            case "ApproveCollectedExchange" :
                $strjson = $this->load->view('test/smartstore/smartstore_approve_exchange_xml', $arr_data, TRUE);
                break;
            case "ReDeliveryExchange" :
                $strjson = $this->load->view('test/smartstore/smartstore_redelivery_exchange_xml', $arr_data, TRUE);
                break;
            case "RejectExchange" :
                $strjson = $this->load->view('test/smartstore/smartstore_reject_exchange_xml', $arr_data, TRUE);
                break;
            case "WithholdExchange" :
                $strjson = $this->load->view('test/smartstore/smartstore_withhold_exchange_xml', $arr_data, TRUE);
                break;
            case "ReleaseExchangeHold" :
                $strjson = $this->load->view('test/smartstore/smartstore_release_exchange_xml', $arr_data, TRUE);
                break;
            case "AnswerCustomerInquiry" :
                $strjson = $this->load->view('test/smartstore/smartstore_answer_inquery_xml', $arr_data, TRUE);
                break;
            case "ApproveCancelApplication" :
                $strjson = $this->load->view('test/smartstore/smartstore_approve_cancel_xml', $arr_data, TRUE);
                break;
            default :
                break;
        }


        $target_url = "http://ec.api.naverc.com/ShopN/{$service}";

        $arr_curl_data = array(
            'target_url'=>$target_url,
            'service'=>$service,
            'operation'=>$operation,
            'strjson'=>$strjson
        );


        $result = $this->forwardCurl($arr_curl_data);

        return $result;

    }



    function getAddressInfo($seller_id){

        $accessLicense = '123';

        // secret key
        $key = '123';


        $operation = 'GetAddressBookList';
        $service = 'AddressBookService';

        $version = '2.0';

        // create timestamp
        $timestamp = $this -> smartstoresecret -> getTimestamp();

        // create signature
        $request_str = $timestamp.$service.$operation;
        $signature = $this -> smartstoresecret -> generateSign($request_str,$key);

        $arr_data = array(
            'timestamp' => $timestamp,
            'operation' => $operation,
            'accessLicense' => $accessLicense,
            'seller_id' => $seller_id,
            'version'=>$version,
            'signature'=>$signature,
        );

        $strjson = $this->load->view('test/smartstore/smartstore_address_xml', $arr_data, TRUE);

        $target_url = "http://ec.api.naverc.com/ShopN/{$service}";

        $arr_curl_data = array(
            'target_url'=>$target_url,
            'service'=>$service,
            'operation'=>$operation,
            'strjson'=>$strjson,
        );

        $result = $this->forwardCurl($arr_curl_data);

        $arr_address_response = $this->simplexml->xml_parse($result);

        $result = isset($arr_address_response['soapenv:Body']['n:GetAddressBookListResponse']['AddressBookList'])?$arr_address_response['soapenv:Body']['n:GetAddressBookListResponse']['AddressBookList']:"";

        $fail_result = isset($arr_address_response['soapenv:Body']['n:GetAddressBookListResponse']['n:Error']['n:Message'])?$arr_address_response['soapenv:Body']['n:GetAddressBookListResponse']['n:Error']['n:Message']:"";

        if($result){
            debug_var($result);
        }else{

            debug_var($fail_result);
        }

    }

    function registerSeller($seller_id,$api_id){

        if(empty($seller_id) || empty($api_id)){
            $arr_result = array(
                'result' => 'Fali',
                'msg' => '두 개의 정확한 값을 입력해주세요.',
            );
            echo json_encode($arr_result);
            exit;
        }

        $accessLicense = '123';

        // secret key
        $key = '123';

        $operation = 'RegisterSeller';
        $service = 'RegisterSellerService';

        $version = '2.0';

        // create timestamp
        $timestamp = $this -> smartstoresecret -> getTimestamp();

        // create signature
        $request_str = $timestamp.$service.$operation;
        $signature = $this -> smartstoresecret -> generateSign($request_str,$key);

        $arr_data = array(
            'timestamp' => $timestamp,
            'operation' => $operation,
            'accessLicense' => $accessLicense,
            'seller_id' => $seller_id,
            'version'=>$version,
            'signature'=>$signature,
            'api_id'=>$api_id,
        );

        $strjson = $this->load->view('test/smartstore/smartstore_register_seller_xml', $arr_data, TRUE);

        $target_url = "http://ec.api.naverc.com/ShopN/{$service}";

        $arr_curl_data = array(
            'target_url'=>$target_url,
            'service'=>$service,
            'operation'=>$operation,
            'strjson'=>$strjson,
        );

        $result = $this->forwardCurl($arr_curl_data);

        $arr_address_response = $this->simplexml->xml_parse($result);

        $result = isset($arr_address_response['soapenv:Body']['n:GetAddressBookListResponse']['AddressBookList'])?$arr_address_response['soapenv:Body']['n:GetAddressBookListResponse']['AddressBookList']:"";

        if($result){
            debug_var($result);
        }else{
            $arr_result = array();
            $arr_result['result'] = 'Fail';
            $arr_result['result_msg'] = '스마트스토어에 조회된 정보가 없습니다.';
            echo json_encode($arr_result);
            exit;
        }

    }

    function forwardCurl($params){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $params['target_url']);

        $headers = array(
            "Content-Type: text/xml;charset=UTF-8",
            "SOAPAction", $params['service'] . "#" . $params['operation'],
        );

        // For xml, change the content-type.
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params['strjson']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Send to remote and return data to caller.
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }


    function Test_registerItem_function(){

        $url = "http://testtest2.test.com:8888/marketApi/SmartStore/register?itemId=600108511572_D7E7E";

        $headers = array(
            "Content-Type: application/json",
            "charset=UTF-8",
            "MALL_ID: dmall",
            "MARKET: SS",
            "API_ACCESS_KEY: test",
            "Authorization: Basic test"
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "Error: " . curl_error($ch);
        }
        else {
            echo $response;
        }
        curl_close($ch);
    }


    private function gettestSetting($mall_id,$code){

        $arr_setting_params = array();
        $arr_setting_params['mall_id'] = $mall_id;
        $arr_setting_params['code'] = $code;
        $arr_setting_result = $this->testmodel->gettestSettingInfoForSmartStore($arr_setting_params);
        return $arr_setting_result;

    }

    function getAttribute($html,$tag) {
        preg_match( '@'.$tag.'="([^"]+)"@' , $html, $match );
        return array_pop($match);
    }


}