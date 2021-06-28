<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');


//echo "<img src='".$cdn_domain.$upload_file."'>";

/*
 * 쿠팡 할인전 가격 계산
 * ex 1.10의 경우 실제판매가 10000원일 경우 11000의
 */
function getCoupangOriginalPrice($originalPriceRate=0,$salePrice){
    $originalPriceRate = floatval($originalPriceRate);
    if($originalPriceRate<=0 || $originalPriceRate>=1 ){
        $originalPriceRate=0;
    }
    $originalPrice = $salePrice/(1-$originalPriceRate);
    $originalPrice=round($originalPrice,-1);
    return $originalPrice;
}

function getItemMasterStatus($status) {
	switch ($status) {
		case '1111' :
			return "Hi";
			break;

		default :
			return "Hi";
			break;
	}
}
 
function is_date( $str ) {
    $d = date('Y-m-d', strtotime( $str ));
    //var_dump( $d );
    return $d == $str;
}
 
function getItemMasterStatusLabel($status) {
	switch ($status) {
		case '1111' :
			return "Hi";
			break;

		default :
			return "Hi";
			break;
	}
}


function getFormStatus($val) {
	if(!isset($val)) {
		return "has-error";
	}
	if($val=="") {
		return "has-error";
	} else {
		return "has-success";
	}
}
function get_title($url) {
	if (filter_var($url, FILTER_VALIDATE_URL) === false) {
			return "";
	}
	$str = file_get_contents($url);
	if (strlen($str) > 0) {
		$str = trim(preg_replace('/\s+/', ' ', $str));
		// supports line breaks inside <title>
		preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title);

		// ignore case
		$title = $title[1];
		//
		$title = ConvertToUTF8($title);
		return $title;
	}
}

function get_title_kr($url) {
	$str = file_get_contents($url);
	if (strlen($str) > 0) {
		$str = trim(preg_replace('/\s+/', ' ', $str));
		// supports line breaks inside <title>
		preg_match("/\<title\>(.*)\<\/title\>/i", $str, $title);

		// ignore case
		$title = $title[1];
		//
		$title = ConvertToUTF8_kr($title);
		return $title;
	}
}

function ConvertToUTF8($str) {
	if (mb_detect_encoding($str, "UTF-8, ISO-8859-1, GBK") != "UTF-8") {

		return iconv("gbk", "utf-8", $str);

	} else {
		return $str;
	}

}

function ConvertToUTF8_kr($str) {
	if (mb_detect_encoding($str, "UTF-8, ISO-8859-1, EUC-KR") != "UTF-8") {

		return iconv("EUC-KR", "utf-8", $str);

	} else {
		return $str;
	}

}

function getItemId($target_url) {
		$array_url = parse_url($target_url);
		$query = isset($array_url['query']) ? $array_url['query'] : "";
		
		parse_str($query, $item_url);
		$item_id = isset($item_url['id']) ? $item_url['id'] : "";
		if ($item_id == "") {
			$item_id = isset($item_url['itemid']) ? $item_url['itemid'] : "";
			if ($item_id == "") {
					
					$path_parts = pathinfo($array_url['path']);
					$file_name= $path_parts['basename'];
					$file_name = basename($file_name,".htm");
					$item_id=$file_name;						
			}
			
		}
		return $item_id;	
}

function get1688ItemId($target_url) {
	$array_url = parse_url($target_url);
	$query = isset($array_url['query']) ? $array_url['query'] : "";
	parse_str($query, $item_url);
	$path_parts = pathinfo($array_url['path']);
	$file_name= $path_parts['basename'];
	$file_name = basename($file_name,".html");
	$item_id=$file_name;
	return $item_id;
}

function getAuctionItemId($target_url) {
        $array_url = parse_url($target_url);
        $query = isset($array_url['query']) ? $array_url['query'] : "";
        
        parse_str($query, $item_url);
        $item_id = isset($item_url['itemno']) ? $item_url['itemno'] : "";
        if ($item_id == "") {

            $item_id = isset($item_url['ItemNo']) ? $item_url['ItemNo'] : "";
            if ($item_id == "") {
                    
                    $path_parts = pathinfo($array_url['path']);
                    $file_name= $path_parts['basename'];
                    $file_name = basename($file_name,".htm");
                    $item_id=$file_name;                        
            }
            
        }
        return $item_id;    
}

function getItemIdForAutoScrap($target_url) {
		$array_url = parse_url($target_url);
		$query = isset($array_url['query']) ? $array_url['query'] : "";
		
		parse_str($query, $item_url);
		$item_id = isset($item_url['id']) ? $item_url['id'] : "";
		if ($item_id == "") {
			$item_id = isset($item_url['itemid']) ? $item_url['itemid'] : "";
			if ($item_id == "") {
					$item_id="";						
			}
			
		}
		return $item_id;	
}
function getItemNo($target_url) {
		$array_url = parse_url($target_url);
		$query = isset($array_url['query']) ? $array_url['query'] : "";
		
		parse_str($query, $item_url);
		$item_id = isset($item_url['itemno']) ? $item_url['itemno'] : "";
		if ($item_id == "") {
			$item_id = isset($item_url['ItemNo']) ? $item_url['ItemNo'] : "";
			if ($item_id == "") {
					
					$path_parts = pathinfo($array_url['path']);
					$file_name= $path_parts['basename'];
					$file_name = basename($file_name,".htm");
					$item_id=$file_name;						
			}
			
		}
		return $item_id;	
}
function getGoodscode($target_url) {
		$array_url = parse_url($target_url);
		$query = isset($array_url['query']) ? $array_url['query'] : "";
		
		parse_str($query, $item_url);
		$item_id = isset($item_url['goodscode']) ? $item_url['goodscode'] : "";
		if ($item_id == "") {
			
			$nextUrl = isset($item_url['nextUrl']) ? $item_url['nextUrl'] : "";
			$array_url = parse_url($nextUrl);
			$query = isset($array_url['query']) ? $array_url['query'] : "";
		
			parse_str($query, $item_url);
			$item_id = isset($item_url['goodscode']) ? $item_url['goodscode'] : "";
		
			if ($item_id == "") {
					
					$path_parts = pathinfo($array_url['path']);
					$file_name= $path_parts['basename'];
					$file_name = basename($file_name,".htm");
					$item_id=$file_name;						
			}
			
		}
		return $item_id;	
}

function myFilter($var){
    return ($var !== NULL && $var !== FALSE && $var !== '');
}

function getGmarketMinishopCode($target_url) {
		error_reporting(E_ALL & ~E_NOTICE);
		libxml_use_internal_errors(true);	
		$html = file_get_html($target_url);
		//echo $html;
		
		$minishop = $html->find('p.minishop_title' , 0);
		//echo $minishop -> innertext . '<br>';
		if (empty($minishop)) {
			return "";
		}
		
		$content = $minishop->find('a' , 0);
		  
		//foreach ($minishop->find('a') as $minishop2) {
		if (!empty($content)) {			
				$minishopId= $content -> href . '<br>';
		} else {
				$minishopId="";
		}
			 
		//}
		$minishopId=str_replace("http://minishop.gmarket.co.kr/","",$minishopId);
		return $minishopId;
}

function getImageArrayFromHtml($itemID,$key,$html_tag) {
	$array_deail_images = array();			
	libxml_use_internal_errors(true);	
	$doc = new DOMDocument();
	$doc->loadHTML($html_tag);
	$xpath = new DOMXpath($doc);
	$imgs = $xpath->query("//img");
	for ($i=0; $i < $imgs->length; $i++) {
	    $img = $imgs->item($i);
	    $src = $img->getAttribute("src");
	    // do something with $src
	    //echo $img;
		//
		//echo $src;
		//$array_deail_images['detail_images'] = $src;
		array_push($array_deail_images,array("Uri" => $src,"itemID" => $itemID,"key" =>$key));
	}
		
	return $array_deail_images;
}


function object_to_array($obj) {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
                $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
                $arr[$key] = $val;
        }
        return $arr;
}


function removeSpecialCharacter($text){
 $text = strip_tags($text);
 $text = htmlspecialchars($text);
 $text = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $text);
 return $text;
}

function multiexplode ($delimiters,$string) {

    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return  $launch;
}

function getOrderOptions($site,$option_string) {
	$arr_return = array("user_option_name"=>"","user_option_amt"=>"","user_option_item"=>"");
	if($site=="옥션"){
		$arr_user_option_name = explode(":",$option_string);
		//debug_var($arr_user_option_name);		
	    if(count($arr_user_option_name)>1) {
	       $user_option_name= $arr_user_option_name[0];// -->/라쿤패딩(블랙):M|1개 
	    } else {
	       $arr_user_option_name_2 = explode("|",$option_string);
           $user_option_name=isset($arr_user_option_name_2[0]) ? $arr_user_option_name_2[0] : "";
	    }
		//$user_option_name=isset($arr_user_option_name[0]) ? $arr_user_option_name[0] : "";
	//	debug_var($user_option_name);
		//debug_var(count($arr_user_option_name));
		//if(count($arr_user_option_name)>0) {
		$arr_return['user_option_name']=trim($user_option_name);
		//} else {
			//$arr_return['user_option_name']="";
		//}
		//$arr_return['user_option_name']="";
		//debug_var($arr_return);

		$arr_user_option_amt = explode("|",$option_string);		
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";
 
		$arr_user_option_item = explode("|",str_replace(":","|",$option_string),-1);				
		$arr_return['user_option_item']=implode("|",$arr_user_option_item);
		
	}

	
	else if($site=="G마켓"){
		$arr_user_option_name = explode(":",$option_string);
		$user_option_name=isset($arr_user_option_name[0]) ? $arr_user_option_name[0] : "";
		
		if(count($arr_user_option_name)>1) {
			$arr_return['user_option_name']=$user_option_name;	
		} else {
			$arr_return['user_option_name']="";
		}
		//$arr_return['user_option_name']="";

		$arr_user_option_amt = explode("|",$option_string);		
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";

		$arr_user_option_item = explode("|",str_replace(":","|",$option_string),-1);				
		$arr_return['user_option_item']=implode("|",$arr_user_option_item);		
	}
	else if($site=="11번가"){
	    
        $arr_option_string_except_amt = explode("-",$option_string);
        $cnt_option_except_amt =  count($arr_option_string_except_amt);
//        echo $cnt_option_except_amt;
        
        if($cnt_option_except_amt == 2) {   
            $option_string_except_amt=isset($arr_option_string_except_amt[0]) ? $arr_option_string_except_amt[0] : "";
        } else if($cnt_option_except_amt==3){
            $option_string_except_amt=$arr_option_string_except_amt[0].$arr_option_string_except_amt[1];
        } else if($cnt_option_except_amt==4){
            $option_string_except_amt=$arr_option_string_except_amt[0].$arr_option_string_except_amt[1].$arr_option_string_except_amt[2];
        }
        
		$arr_user_option = multiexplode(array("/",","),$option_string_except_amt);
        		

        // 옵션명
        $user_option_name="";
        foreach ((array)@$arr_user_option as $key => $value) {
            //$arr_user_option_name = explode("|",$value);
            $arr_user_option_name = multiexplode(array("|",":"),$value);
            $tmp_user_option_name=isset($arr_user_option_name[0]) ? $arr_user_option_name[0] : "";
            $user_option_name= $user_option_name."|".$tmp_user_option_name;
        }
		//$user_option_name=isset($arr_user_option_name[0]) ? $arr_user_option_name[0] : "";
		// 공백제거, 첫번째 | 제거
		$user_option_name=substr(trim($user_option_name),1);
		$arr_return['user_option_name']=$user_option_name;

        // 아이템
        $user_option_item="";
        foreach ((array)@$arr_user_option as $key => $value) {
            //$arr_user_option_item = explode("|",$value);
            $arr_user_option_item = multiexplode(array("|",":"),$value);
            
            $tmp_user_option_item=isset($arr_user_option_item[1]) ? $arr_user_option_item[1] : "";
            $user_option_item= $user_option_item."|".$tmp_user_option_item;
        }
        $user_option_item=substr(trim($user_option_item),1);
        $arr_return['user_option_item']=$user_option_item;


		//$arr_return['user_option_name']="";
        // 옵션 수량 구하기
		$arr_user_option_amt = explode("-",$option_string);
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";
				
	}
	else if($site=="스마트스토어"){
		$arr_user_option_name = explode(":",$option_string);
		$user_option_name=isset($arr_user_option_name[0]) ? $arr_user_option_name[0] : "";
		
		if(count($arr_user_option_name)>0) {
			$arr_return['user_option_name']=$user_option_name;
		} else {
			$arr_return['user_option_name']="";
		}
		//$arr_return['user_option_name']="";		

		$arr_user_option_amt = explode("-",$option_string);		
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";

		$arr_user_option_item = explode("-",str_replace(":","|",$option_string),-1);				
		$arr_return['user_option_item']=implode("|",$arr_user_option_item);				
	}
    else if($site=="쿠팡"){


        $only_option_string_except_amount = substr($option_string,0,strrpos($option_string,"-"));
        $only_amount = substr($option_string,strrpos($option_string,"-"),strlen($option_string));
        $removed_option_string_except_amount = str_replace("-","_",$only_option_string_except_amount);
        $option_string=$removed_option_string_except_amount.$only_amount;

		// 로직 시작
        $arr_user_option = explode("-",$option_string);
        $cnt_user_option_amt = isset($arr_user_option) ? count($arr_user_option) : 0;
        $arr_return['user_option_amt']=isset($arr_user_option[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option[$cnt_user_option_amt-1])) : "";

//        debug_var($arr_user_option);

        $option_first_string = isset($arr_user_option[0]) ? $arr_user_option[0] : "";
        //debug_var($option_first_string);

        $arr_user_option_item_name = array_map('trim', explode(',', $option_first_string));
        //debug_var($arr_user_option_item_name);

        $arr_return['user_option_name'] = isset($arr_user_option_item_name[0]) ? $arr_user_option_item_name[0]:"";


        if(isset($arr_user_option_item_name[0])){
            unset($arr_user_option_item_name[0]);
        }

        $arr_return['user_option_item']=implode("|",$arr_user_option_item_name);


		if(empty($arr_return['user_option_item'])){

            $only_option_string_except_amount = substr($option_string,0,strrpos($option_string,"-"));
            $only_amount = substr($option_string,strrpos($option_string,"-"),strlen($option_string));
            $removed_option_string_except_amount = str_replace("-","_",$only_option_string_except_amount);
            $option_string=$removed_option_string_except_amount.$only_amount;

            $arr_user_option = explode("-",$option_string);
            $cnt_user_option_amt = isset($arr_user_option) ? count($arr_user_option) : 0;
            $arr_return['user_option_amt']=isset($arr_user_option[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option[$cnt_user_option_amt-1])) : "";


            $option_first_string = isset($arr_user_option[0]) ? $arr_user_option[0] : "";
            //debug_var($option_first_string);

            $arr_user_option_item_name = array_map('trim', explode(' ', $option_first_string));
            //debug_var($arr_user_option_item_name);

            $arr_return['user_option_name'] = "";

            $arr_user_option_item_name=array_filter($arr_user_option_item_name, function($value) { return $value !== ''; });




            $arr_return['user_option_item']=implode("|",$arr_user_option_item_name);
		}
    }

	else if($site=="위메프2.0"){
		
		$arr_user_option_amt = explode("-",$option_string);
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";

		$arr_user_option_item = explode("-",str_replace(":","|",$option_string),-1);
		$arr_return['user_option_item']=implode("|",$arr_user_option_item);
	}
	else if($site=="티몬"){

		$arr_user_option_name = explode("|",$option_string);


		if(isset($arr_user_option_name[0])){
			unset($arr_user_option_name[0]);
		}

		$option_string=implode("|",$arr_user_option_name);

		$arr_user_option_amt = explode("-",$option_string);
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";

		$arr_user_option_item = explode("-",str_replace(":","|",$option_string),-1);
		$arr_return['user_option_item']=implode("|",$arr_user_option_item);
	}
	else if($site=="인터파크"){

		$arr_option_string_except_amt = explode("-",$option_string);
		$cnt_option_except_amt =  count($arr_option_string_except_amt);
        //echo $cnt_option_except_amt;

		if($cnt_option_except_amt == 2) {
			$option_string_except_amt=isset($arr_option_string_except_amt[0]) ? $arr_option_string_except_amt[0] : "";
		} else if($cnt_option_except_amt==3){
			$option_string_except_amt=$arr_option_string_except_amt[0].$arr_option_string_except_amt[1];
		} else if($cnt_option_except_amt==4){
			$option_string_except_amt=$arr_option_string_except_amt[0].$arr_option_string_except_amt[1].$arr_option_string_except_amt[2];
		}


		//$arr_user_option = explode("/",$option_string_except_amt);
		$arr_user_option = explode("|",$option_string_except_amt);
		$arr_user_option = array_map('trim', $arr_user_option);


		$arr_return['user_option_name']="";


		$user_option_item="";
		//옵션이 2개이상
		if(count($arr_user_option)>3){
			$user_option_item=$arr_user_option[1]."|".$arr_user_option[3];
		} else if(count($arr_user_option)>=2){
			$user_option_item=$arr_user_option[1];
		}

		// 아이템
		//$user_option_item="";
		$arr_return['user_option_item']=$user_option_item;


		// 옵션 수량 구하기
		$arr_user_option_amt = explode("-",$option_string);
		$cnt_user_option_amt = isset($arr_user_option_amt) ? count($arr_user_option_amt) : 0;
		$arr_return['user_option_amt']=isset($arr_user_option_amt[$cnt_user_option_amt-1]) ? trim(str_replace("-","",$arr_user_option_amt[$cnt_user_option_amt-1])) : "";
	}
	return $arr_return;
}


function getOrderOptions_v2($site,$option_string) {

	$option_1_name = "";
	$option_1_value = "";
	$option_2_name = "";
	$option_2_value = "";
	$user_option_amt = "";
	$size_map = array("사이즈","치수","Size","size","크기","높이","키","길이","규격","색상");
	$arr_return = array("user_option_name"=>"","user_option_amt"=>"","user_option_item"=>"");

	if($site=="옥션"){

		//$option_string = "아이보리:4XL|1개";
		//$option_string = "라이트핑크:one size|free|1개";

		if(strpos($option_string, "|")){

			$arr_option_string_except_pipe = explode("|",$option_string);
			if(sizeof($arr_option_string_except_pipe) === 3){

				$user_option_amt = $arr_option_string_except_pipe[2];

				$arr_option_string_except_colon = explode(":",$arr_option_string_except_pipe[0]);
				$option_1_value = trim($arr_option_string_except_colon[1])."/".trim($arr_option_string_except_pipe[1])."|";
				$option_2_value = $arr_option_string_except_colon[0];
			}
			if(sizeof($arr_option_string_except_pipe) === 2){

				$user_option_amt = $arr_option_string_except_pipe[1];
				if(strpos($user_option_amt, "-")){
					$arr_user_option_amt = explode("-",$user_option_amt);
					$user_option_amt = $arr_user_option_amt[1];
				}

				$arr_option_string_except_colon = explode(":",$arr_option_string_except_pipe[0]);
				$option_1_value = trim($arr_option_string_except_colon[1])."|";
				$option_2_value = $arr_option_string_except_colon[0];
			}
		}
	}

	else if($site=="11번가"){

		if(strpos($option_string, "-")){

			$arr_option_string_except_amt = explode("-",$option_string);

			$user_option_amt = $arr_option_string_except_amt[1];

			$arr_option_string_except_comma = explode(",",$arr_option_string_except_amt[0]);

			//FOR ONE OPTION
			if(sizeof($arr_option_string_except_comma) === 1){

				$arr_option_string_except_comma_colon = explode(":",$arr_option_string_except_comma[0]);
				$option_1_name = $arr_option_string_except_comma_colon[0];
				$option_1_value = $arr_option_string_except_comma_colon[1];
			}

			//FOR TWO OPTION
			if(sizeof($arr_option_string_except_comma) === 2){

				$arr_option_string_except_comma_colon_0 = explode(":",$arr_option_string_except_comma[0]);
				$arr_option_string_except_comma_colon_1 = explode(":",$arr_option_string_except_comma[1]);

				$option_1_name = $arr_option_string_except_comma_colon_0[0];
				if(in_array($option_1_name, $size_map)){

					$option_1_name = trim($arr_option_string_except_comma_colon_0[0])."|";
					$option_1_value = trim($arr_option_string_except_comma_colon_0[1])."|";

					$option_2_name = $arr_option_string_except_comma_colon_1[0];
					$option_2_value = $arr_option_string_except_comma_colon_1[1];
				}
				else{

					$option_1_name = trim($arr_option_string_except_comma_colon_1[0])."|";
					$option_1_value = trim($arr_option_string_except_comma_colon_1[1])."|";

					$option_2_name = $arr_option_string_except_comma_colon_0[0];
					$option_2_value = $arr_option_string_except_comma_colon_0[1];
				}
			}
		}
	}

	else if($site=="쿠팡"){
		if(strpos($option_string, "-")){

			$arr_option_string_except_amt = explode("-",$option_string);

			if(sizeof($arr_option_string_except_amt) === 2){

				$user_option_amt = $arr_option_string_except_amt[1];
				if(strpos($user_option_amt, "[")){
					$user_option_amt = substr($user_option_amt, 0, strpos($user_option_amt, "["));
				}

				if(strpos($arr_option_string_except_amt[0], ",")){

					$arr_option_string_except_amt_comma = explode(",",$arr_option_string_except_amt[0]);

					$option_1_name = $arr_option_string_except_amt_comma[0];
					if(sizeof($arr_option_string_except_amt_comma) === 2){

						$option_1_value = $arr_option_string_except_amt_comma[1];
					}
					if(sizeof($arr_option_string_except_amt_comma) === 3){

						$option_1_value = trim($arr_option_string_except_amt_comma[2])."|";
						$option_2_value = $arr_option_string_except_amt_comma[1];
					}
					if(sizeof($arr_option_string_except_amt_comma) === 4){

						$option_2_value = $arr_option_string_except_amt_comma[1];
						$option_1_value = trim($arr_option_string_except_amt_comma[2]).",".trim($arr_option_string_except_amt_comma[3])."|";
					}
				}
			}
			if(sizeof($arr_option_string_except_amt) === 3) {

				$user_option_amt = $arr_option_string_except_amt[2];
				if(strpos($user_option_amt, "[")){
					$user_option_amt = substr($user_option_amt, 0, strpos($user_option_amt, "["));
				}

				if(strpos($arr_option_string_except_amt[0], ",")){

					$arr_option_string_except_amt_comma = explode(",", $arr_option_string_except_amt[0]);

					if(sizeof($arr_option_string_except_amt_comma) === 2){

						$option_1_name = trim($arr_option_string_except_amt_comma[0]);
						$option_2_value = trim($arr_option_string_except_amt_comma[1]);

						if(strpos($arr_option_string_except_amt[1], ",")) {

							$arr_option_string_except_amt_comma_1 = explode(",", $arr_option_string_except_amt[1]);

							$option_2_value = $option_2_value."-".trim($arr_option_string_except_amt_comma_1[0]);
							$option_1_value = trim($arr_option_string_except_amt_comma_1[1])."|";
						}
						else {
							$option_1_value = $arr_option_string_except_amt_comma[2] . "-" . $arr_option_string_except_amt[1] . "|";
						}
					}
					if(sizeof($arr_option_string_except_amt_comma) === 3){

						$option_1_name = $arr_option_string_except_amt_comma[0];
						$option_2_value = $arr_option_string_except_amt_comma[1];
						$option_1_value = trim($arr_option_string_except_amt_comma[2])."-".trim($arr_option_string_except_amt[1])."|";
					}
				}
				else{

					if(strpos($arr_option_string_except_amt[1], ",")){

						$arr_option_string_except_amt_comma_1 = explode(",", $arr_option_string_except_amt[1]);

						$option_1_name = $arr_option_string_except_amt[0]."-".$arr_option_string_except_amt_comma_1[0];
						$option_2_value = $arr_option_string_except_amt_comma_1[2];
						$option_1_value = trim($arr_option_string_except_amt_comma_1[1])."|";
					}
				}
			}
			if(sizeof($arr_option_string_except_amt) === 4) {

				$user_option_amt = $arr_option_string_except_amt[3];
				$option_1_name = $arr_option_string_except_amt[0];

				if(strpos($arr_option_string_except_amt[1], ",")){

					$arr_option_string_except_amt_comma = explode(",", $arr_option_string_except_amt[1]);

					$option_1_value = trim($arr_option_string_except_amt_comma[1])."-".trim($arr_option_string_except_amt[2])."|";
					$option_2_value = $arr_option_string_except_amt_comma[0];
				}
			}
		} else {
			$option_string = $option_string."- ";
			$arr_option_string_except_amt = explode("-",$option_string);

			if(sizeof($arr_option_string_except_amt) === 2){

				$user_option_amt = $arr_option_string_except_amt[1];
				if(strpos($user_option_amt, "[")){
					$user_option_amt = substr($user_option_amt, 0, strpos($user_option_amt, "["));
				}

				if(strpos($arr_option_string_except_amt[0], ",")){

					$arr_option_string_except_amt_comma = explode(",",$arr_option_string_except_amt[0]);

					$option_1_name = $arr_option_string_except_amt_comma[0];
					if(sizeof($arr_option_string_except_amt_comma) === 2){

						$option_1_value = $arr_option_string_except_amt_comma[1];
					}
					if(sizeof($arr_option_string_except_amt_comma) === 3){

						$option_1_value = trim($arr_option_string_except_amt_comma[2])."|";
						$option_2_value = $arr_option_string_except_amt_comma[1];
					}
					if(sizeof($arr_option_string_except_amt_comma) === 4){

						$option_2_value = $arr_option_string_except_amt_comma[1];
						$option_1_value = trim($arr_option_string_except_amt_comma[2]).",".trim($arr_option_string_except_amt_comma[3])."|";
					}
				}
			}
			if(sizeof($arr_option_string_except_amt) === 3) {

				$user_option_amt = $arr_option_string_except_amt[2];
				if(strpos($user_option_amt, "[")){
					$user_option_amt = substr($user_option_amt, 0, strpos($user_option_amt, "["));
				}

				if(strpos($arr_option_string_except_amt[0], ",")){

					$arr_option_string_except_amt_comma = explode(",", $arr_option_string_except_amt[0]);

					if(sizeof($arr_option_string_except_amt_comma) === 2){

						$option_1_name = trim($arr_option_string_except_amt_comma[0]);
						$option_2_value = trim($arr_option_string_except_amt_comma[1]);

						if(strpos($arr_option_string_except_amt[1], ",")) {

							$arr_option_string_except_amt_comma_1 = explode(",", $arr_option_string_except_amt[1]);

							$option_2_value = $option_2_value."-".trim($arr_option_string_except_amt_comma_1[0]);
							$option_1_value = trim($arr_option_string_except_amt_comma_1[1])."|";
						}
						else {
							$option_1_value = $arr_option_string_except_amt_comma[2] . "-" . $arr_option_string_except_amt[1] . "|";
						}
					}
					if(sizeof($arr_option_string_except_amt_comma) === 3){

						$option_1_name = $arr_option_string_except_amt_comma[0];
						$option_2_value = $arr_option_string_except_amt_comma[1];
						$option_1_value = trim($arr_option_string_except_amt_comma[2])."-".trim($arr_option_string_except_amt[1])."|";
					}
				}
				else{

					if(strpos($arr_option_string_except_amt[1], ",")){

						$arr_option_string_except_amt_comma_1 = explode(",", $arr_option_string_except_amt[1]);

						$option_1_name = $arr_option_string_except_amt[0]."-".$arr_option_string_except_amt_comma_1[0];
						$option_2_value = $arr_option_string_except_amt_comma_1[2];
						$option_1_value = trim($arr_option_string_except_amt_comma_1[1])."|";
					}
				}
			}
			if(sizeof($arr_option_string_except_amt) === 4) {

				$user_option_amt = $arr_option_string_except_amt[3];
				$option_1_name = $arr_option_string_except_amt[0];

				if(strpos($arr_option_string_except_amt[1], ",")){

					$arr_option_string_except_amt_comma = explode(",", $arr_option_string_except_amt[1]);

					$option_1_value = trim($arr_option_string_except_amt_comma[1])."-".trim($arr_option_string_except_amt[2])."|";
					$option_2_value = $arr_option_string_except_amt_comma[0];
				}
			}
		}
	}

	else if($site=="G마켓"){

		if(strpos($option_string, "|")){

			$arr_option_string_except_pipe = explode("|",$option_string);
			if(sizeof($arr_option_string_except_pipe) === 3){

				$user_option_amt = $arr_option_string_except_pipe[2];

				$arr_option_string_except_colon = explode(":",$arr_option_string_except_pipe[0]);
				$option_1_value = trim($arr_option_string_except_colon[1])."/".trim($arr_option_string_except_pipe[1])."|";
				$option_2_value = $arr_option_string_except_colon[0];
			}
			if(sizeof($arr_option_string_except_pipe) === 2){

				$user_option_amt = $arr_option_string_except_pipe[1];
				if(strpos($user_option_amt, "-")){
					$arr_user_option_amt = explode("-",$user_option_amt);
					$user_option_amt = $arr_user_option_amt[1];
				}

				$arr_option_string_except_colon = explode(":",$arr_option_string_except_pipe[0]);
				$option_1_value = trim($arr_option_string_except_colon[1])."|";
				$option_2_value = $arr_option_string_except_colon[0];
			}
		}
	}

	else if($site=="스마트스토어"){

		if(strpos($option_string, "-")){

			$arr_option_string_except_amt = explode("-",$option_string);

			if(sizeof($arr_option_string_except_amt) === 2){

				$user_option_amt = $arr_option_string_except_amt[1];

				$arr_option_string_except_colon = explode(":",$arr_option_string_except_amt[0]);

			}
			if(sizeof($arr_option_string_except_amt) === 3){

				$user_option_amt = $arr_option_string_except_amt[2];

				$option_string_except_amt_bracket = substr($arr_option_string_except_amt[0], 0, strpos($arr_option_string_except_amt[0], "("));
				$arr_option_string_except_colon = explode(":",$option_string_except_amt_bracket);
			}

			if(strpos($arr_option_string_except_colon[0], "/")){

				$arr_option_string_except_slash_0 = explode("/",$arr_option_string_except_colon[0]);
				$arr_option_string_except_slash_1 = explode("/",$arr_option_string_except_colon[1]);

				$option_1_name = $arr_option_string_except_slash_0[0];
				if(in_array($option_1_name, $size_map)){

					$option_1_name = trim($arr_option_string_except_slash_0[0])."|";
					$option_2_name = $arr_option_string_except_slash_0[1];

					$option_1_value = trim($arr_option_string_except_slash_1[0])."|";
					$option_2_value = $arr_option_string_except_slash_1[1];
				}
				else{

					$option_1_name = trim($arr_option_string_except_slash_0[1])."|";
					$option_2_name = $arr_option_string_except_slash_0[0];

					$option_1_value = trim($arr_option_string_except_slash_1[1])."|";
					$option_2_value = $arr_option_string_except_slash_1[0];
				}
			}
			else{
				$option_1 = $arr_option_string_except_colon[0];
				if(in_array($option_1, $size_map)){

					$arr_option_string_except_colon_slash = explode("/",$arr_option_string_except_colon[1]);

					if(sizeof($arr_option_string_except_colon_slash) === 1){

						$option_1_name = $option_1;
						$option_1_value = $arr_option_string_except_colon_slash[0];
					}
					if(sizeof($arr_option_string_except_colon_slash) === 2){

						$option_1_name = trim($arr_option_string_except_colon_slash[1]) . "|";
						$option_1_value = trim($arr_option_string_except_colon[2]) . "|";

						$option_2_name = $arr_option_string_except_colon[0];
						$option_2_value = $arr_option_string_except_colon_slash[0];
					}
				}
			}
		}
	}

	else if($site=="위메프2.0"){

		if(strpos($option_string, "-")){

			$arr_option_string_except_amt = explode("-",$option_string);
			$arr_option_string_except_pipe = explode("|",$arr_option_string_except_amt[0]);

			if(sizeof($arr_option_string_except_pipe) === 2){

				$option_1_value = trim($arr_option_string_except_pipe[1]) . "|";
				$option_2_value = $arr_option_string_except_pipe[0];
			}

			$user_option_amt = $arr_option_string_except_amt[1];
		}
	}
	else if($site=="위메이크프라이스"){
		$option_string = htmlspecialchars_decode($option_string);
		if(strpos($option_string, "<옵션>")){

			$arr_option_string = explode("<옵션>",$option_string);
			$arr_option= explode("|",$arr_option_string[1]);

			$option_1_value = isset($arr_option[0]) ? trim($arr_option[0]) : "";
			$option_2_value = isset($arr_option[1]) ? "|".trim($arr_option[1]) : "";
//			if(sizeof($arr_option_string_except_pipe) === 2){
//
//				$option_1_value = trim($arr_option_string_except_pipe[1]) . "|";
//				$option_2_value = $arr_option_string_except_pipe[0];
//			}

			$user_option_amt = "";
		}
	}

	else if($site=="티몬"){

		if(strpos($option_string, "-")){

			$arr_option_string_except_amt = explode("-",$option_string);
			$arr_option_string_except_pipe = explode("|",$arr_option_string_except_amt[0]);

			if(sizeof($arr_option_string_except_pipe) === 3){

				$option_1_value = trim($arr_option_string_except_pipe[2]) . "|";
				$option_2_value = $arr_option_string_except_pipe[1];
			}
			if(sizeof($arr_option_string_except_pipe) === 2){

				$option_1_value = trim($arr_option_string_except_pipe[1]) . "|";
				$option_2_value = $arr_option_string_except_pipe[0];
			}

			$user_option_amt = $arr_option_string_except_amt[1];
		}
	}

	else if($site=="인터파크"){

		if(strpos($option_string, "-")){

			$arr_option_string_except_amt = explode("-",$option_string);
			$arr_option_string_except_pipe = explode("|",$arr_option_string_except_amt[0]);

			//FOR TWO OPTIONS
			if(sizeof($arr_option_string_except_pipe) === 4){

				$option_1_name = $arr_option_string_except_pipe[0];
				if(in_array($option_1_name, $size_map)){

					$option_1_name = trim($arr_option_string_except_pipe[0]) . "|";
					$option_1_value = trim($arr_option_string_except_pipe[1]) . "|";

					$option_2_name = $arr_option_string_except_pipe[2];
					$option_2_value = $arr_option_string_except_pipe[3];
				}
				else{

					$option_1_name = trim($arr_option_string_except_pipe[2]) . "|";
					$option_1_value = trim($arr_option_string_except_pipe[3]) . "|";

					$option_2_name = $arr_option_string_except_pipe[0];
					$option_2_value = $arr_option_string_except_pipe[1];
				}
			}

			//FOR ONE OPTION
			if(sizeof($arr_option_string_except_pipe) === 2){

				$option_1_name = $arr_option_string_except_pipe[0];
				$option_1_value = $arr_option_string_except_pipe[1];
			}

			$user_option_amt = $arr_option_string_except_amt[1];
		}
	}


	$user_option_name = trim($option_1_name) . trim($option_2_name);
	$user_option_item = trim($option_1_value) . trim($option_2_value);
	$user_option_amt = trim($user_option_amt);

	$arr_return['user_option_name'] = $user_option_name;
	$arr_return['user_option_item'] = $user_option_item;
	$arr_return['user_option_amt'] = $user_option_amt;
//        debug_var($arr_return);exit();

	return $arr_return;
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function set_max_number($value,$max){
		if($value>=$max){
			return $max;
		} else {
			return $value;
		}
}

function set_min_number($value,$min){
         $value ="" ? 0 : $value;
          
        if($value < $min){
            return $min;
        } else {
            return $value;
        }
}

  function formatSizeUnits($bytes) {
		//echo $bytes;
	  	if(is_array($bytes)){
	  		return "B";
		}
        if ($bytes >= 1073741824)
            {
                $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' kB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
            }
    
            return $bytes;
    }
        
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    // $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
} 

function print_image_size($image_url,$width_limit = 600, $size_limit = 1048576){

	$CI =& get_instance();
	//$CI->load->library('Fastimage');
    $image_url=str_replace('//test.', '//image.', $image_url);
    $image_url=str_replace('//test.', '//image.', $image_url);
    $image_url=str_replace(':8888', '', $image_url);
    $image_url=str_replace('//test.', '//image.', $image_url);
    $image_url_server=str_replace('//test-test.', '//image.', $image_url);

    $path_parts = substr($image_url,-3);
    if(stripos($path_parts,"gif")>0) {
        echo $path_parts;
	}

    $arr_return = array();
    $size = getimagesize($image_url_server);
//debug_var($size);
	//$image = new FastImage($image_url_server);
	//$size = $image->getSize();
    //print_r($size);

	//debug_var($size);

	if($size){
		$width = isset($size[0]) ? $size[0] : 0;
		$height = isset($size[1]) ? $size[1] : 0;
		echo "<ul class='list-group'>";

		if($width < $width_limit || $height < $width_limit) {
			echo "<li class='list-group-item list-group-item-danger'> Error ";
		} else {
			echo "<li class='list-group-item list-group-item-success'>";
		}

		echo "".$width;
		echo "x".$height;

		$img = get_headers($image_url, 1);
		//print_r($img);
		$file_size = isset($img["Content-Length"]) ? $img["Content-Length"] : 0;
		//echo $file_size;
        echo "<br>";
		if($file_size>=$size_limit) {
			echo "<span class='label label-danger pull-right'>";
		} else {
			echo "<span class='label label-success pull-right'>";
		}
		echo formatSizeUnits($file_size);
		echo "</span>";
		echo "&nbsp;<small class='label bg-light-blue'>".getImageMimeImageLocalPathFromUrl($image_url)."</small>";
		echo "</li>";


		echo "</ul>";
    } else {
    	echo "";
	}
}


	function get_image_size($image_url){

		$arr_return = array();

		$size = getimagesize($image_url);
		//debug_var($image_url);

		$width = isset($size[0]) ? $size[0] : 0;
		$height = isset($size[1]) ? $size[1] : 0;

		$arr_ret =array();
		$arr_ret['width']=$width;
		$arr_ret['height']=$height;

		return $arr_ret;
	}




     function encodeSerial($mall_order_no = null) {
         if($mall_order_no==null){
             echo "Need order no";
             return;
             exit;
         }
        //$dt = date('Y-m-d H:i:s');
        $dt = date('Y-m-d');
        $url_encoded = urlencode(base64_encode(serialize(array($mall_order_no,$dt))));
        return $url_encoded;

        //$temp = unserialize(base64_decode(urldecode($test)));
        //debug_var ($temp);

    }
     
      function is_jpeg(&$pict)
      {
        return (bin2hex($pict[0]) == 'ff' && bin2hex($pict[1]) == 'd8');
      }
    
      function is_png(&$pict)
      {
        return (bin2hex($pict[0]) == '89' && $pict[1] == 'P' && $pict[2] == 'N' && $pict[3] == 'G');
      }   
      
      
      function getImageMimeImageLocalPathFromUrl($image_url){
		//debug_var($image_url);
        $CI =& get_instance();

        if(strpos($image_url,".test.com") == true) {

           $image_url = str_replace($CI->config->item('arr_service_urls'),'',$image_url);

           $type = @exif_imagetype($_SERVER["DOCUMENT_ROOT"].$image_url);
           
            switch ($type) {
                case IMAGETYPE_GIF:
                    return "GIF";
                    break;
                case IMAGETYPE_JPEG:
                    return "JPG";
                    break;
                case IMAGETYPE_PNG:
                    return "PNG";
                    break;
                default:
                    return "ETC";
            }
        } else {
            return "";
        }
      }  
      
      
      function getCodeSettingLanguage($val,$site_lang){
              
          
          
          if($site_lang=="" || $site_lang=="english"){
              return $val->title;
          } else if($site_lang=="zh_cn"){
              return $val->title_cn;
          } else {
              return $val->title;
          }
      }
      
      
    function alert($msg='', $url='') {
         $CI =& get_instance();
         
         if (!$msg) $msg = '비정상 경로로 접근하였습니다.';
         
         echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$CI->config->item('charset')."\">";
         echo "<script type='text/javascript'>alert('".$msg."');";
            if ($url)
                echo "location.replace('".$url."');";
         else
          echo "history.go(-1);";
          echo "</script>";
         exit;
    }      
    function alert_after_close($msg='', $url='') {
         $CI =& get_instance();
         
         if (!$msg) $msg = '비정상 경로로 접근하였습니다.';
         
         echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$CI->config->item('charset')."\">";
         echo "<script type='text/javascript'>alert('".$msg."');";
            if ($url)
                echo "location.replace('".$url."');";
         else
          echo "window.opener.parent.location.reload();";
		//window.opener.document.location.reload();
          echo "window.self.close();";
          echo "</script>";
         exit;
    }

function alert_after_close_do_not_refresh($msg='', $url='') {
    $CI =& get_instance();

    if (!$msg) $msg = '비정상 경로로 접근하였습니다.';

    echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=".$CI->config->item('charset')."\">";
    echo "<script type='text/javascript'>alert('".$msg."');";
    if ($url){
        echo "location.replace('".$url."');";
    }
    //else
    //    echo "window.opener.parent.location.reload();";
    //window.opener.document.location.reload();
    echo "window.self.close();";
    echo "</script>";
    exit;
}

function getOpenMarketItemLink($site,$itemId,$str){
        if($site=="옥션"){
            return "<a href='http://itempage3.auction.co.kr/detailview.aspx?itemno=".$itemId."' target=_blank>".$str."</a>";
        }  else if($site=="G마켓") {
            return "<a href='http://item.gmarket.co.kr/Item?goodscode=".$itemId."'  target=_blank>".$str."</a>";
        } else if($site=="11번가"){
            return "<a href='http://www.11st.co.kr/product/SellerProductDetail.tmall?method=getSellerProductDetail&prdNo=".$itemId."'  target=_blank>".$str."</a>";
        }
    }

    function convertItemIdToTaobao($itemId){
        $arr_itemId = explode ("_", $itemId);
        return $arr_itemId[0];
    }

    function getFilePathFromURL($image_url){
        $CI =& get_instance();
        $image_url = str_replace($CI->config->item('arr_service_urls'),'',$image_url);

        //?이하 제거
        $pos =strpos($image_url, '?');
		if ($pos >0) {
            $image_url =substr($image_url,0,$pos);
		}

    return $image_url;
    }
    function getFilePathFromURL_slash($image_url){
        $CI =& get_instance();
        $image_url = str_replace($CI->config->item('arr_service_urls_slash'),'',$image_url);

        //?이하 제거
        $pos =strpos($image_url, '?');
        if ($pos >0) {
            $image_url =substr($image_url,0,$pos);
        }
        return $image_url;
    }
    
    function isUrl( $text )  
    {  
        return filter_var( $text, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) !== false;  
    }


	function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
	}

  function getCDNImageLink($original_url=NULL){
	$operation_mode = 1; // 0 : use default ,1: use own server,2 : use : use CDN
    $CI =& get_instance();
	switch ($operation_mode){
        case 0:
        	return $original_url;
        	break;
		case 1:
            $cdn_domain = $CI->config->item('basic_image_service_url');

            $service_urls = implode("|",$CI->config->item('arr_service_urls'));
            $service_urls=str_replace('http://','',$service_urls);
            $service_urls=str_replace('https://','',$service_urls);

            if(preg_match('('.$service_urls.')', $original_url) === 1) {

				//echo "mathc";
                //$target_image = str_replace($CI->config->item('arr_service_urls'),'',$original_url);
                $target_image=getFilePathFromURL($original_url);

                $upload_dir = dirname($target_image);// "uploads/test_down/20181129";
                $upload_file = $target_image;//"uploads/test_down/20181129/fff9e02b88fa88c80692b4659161bd4e.jpg";
                $local_path =FCPATH.$upload_file;
                $remote_path =$upload_file;

                return $cdn_domain.$upload_file;
            } else {
                return $original_url;
            }
			break;
		case 2:
			//NOT USE FOR NOW,FOR OUT
            $cdn_domain = "http://img.test.com/sangrime/";

            $service_urls = implode("|",$CI->config->item('arr_service_urls'));
            $service_urls=str_replace('http://','',$service_urls);
            $service_urls=str_replace('https://','',$service_urls);


            if(preg_match('('.$service_urls.')', $original_url) === 1) {

                $target_image=getFilePathFromURL($original_url);


                $CI->load->library('ftp');

                $config['hostname'] = '';
                $config['username'] = '';
                $config['password'] = '';
                //$config['debug']        = TRUE;
                $config['port']     = 21;
                $config['passive']  = TRUE;

                $CI->ftp->connect($config);
                $upload_dir = dirname($target_image);
                $upload_file = $target_image;
                $local_path =FCPATH.$upload_file;
                $remote_path =$upload_file;

                $flag_mkdir = $CI->ftp->mkdir($upload_dir);

                $list =$CI->ftp->list_files($remote_path);
                //print_r($list);
                //없으면 올립니다.
                if(!is_array($list)){
                    $CI->ftp->upload($local_path, $remote_path, 'auto');
                }
                $CI->ftp->close();;
                return $cdn_domain.$upload_file;
            } else {
                return $original_url;
            }

			break;

        default:

            break;
	}
  }



function contains($str, array $arr)
{
    foreach($arr as $a) {
        if (stripos($str,$a) !== false) return true;
    }
    return false;
}

function copy_ucloud_to_aws($ucloud_url,$down_url){
	return;
	/*
	$image_url_explode_array = explode("/", $ucloud_url);
    $len = count($image_url_explode_array);
    $upload_path = FCPATH.$image_url_explode_array[$len - 4].'/'.$image_url_explode_array[$len - 3].'/'.$image_url_explode_array[$len - 2].'/';
    if(!is_dir($upload_path)){
        //Directory does not exist, so lets create it.
        mkdir($upload_path, 0777,true);
    }

    $remote_down_url ="http://test.test.com/".$down_url;
    //echo $remote_down_url;
    if(copy($remote_down_url, FCPATH.$down_url)) {
        force_download(FCPATH.$down_url, null);
    } else {
        echo "error";
    }
	*/
}
function copy_ucloud_to_aws_v2($ucloud_url,$down_url){
	return;
	/*
	$image_url_explode_array = explode("/", $ucloud_url);
    $len = count($image_url_explode_array);
    $upload_path = FCPATH.$image_url_explode_array[$len - 4].'/'.$image_url_explode_array[$len - 3].'/'.$image_url_explode_array[$len - 2].'/';
    if(!is_dir($upload_path)){
        //Directory does not exist, so lets create it.
        mkdir($upload_path, 0777,true);
    }

    $remote_down_url ="http://test.test.com/".$down_url;
    //echo $remote_down_url;
    copy($remote_down_url, FCPATH.$down_url);
	*/
}
/*  서버에 없으면 aws링크를 반환합니다*/
function getOriginalImageAWSPath($image_url){
	return $image_url;
	$CI =& get_instance();

	if(strpos($image_url,".test.com") == true) {


		$image_url_no_query = strtok($image_url, "?");
		$image_path = str_replace($CI->config->item('arr_service_urls'),'',$image_url_no_query);

		if(file_exists(FCPATH.$image_path)) {
			return $image_url;
		}
		//이미지가 없으면 AWS주소로 변경함.
		else {
			$image_url = str_replace($CI->config->item('arr_service_urls'),'http://test.test.com',$image_url);
			return $image_url;
		}
	} else {
		return $image_url;
	}
}

/*  서버에 없으면 aws링크를 반환합니다 FRO HTML */
function getOriginalImageAWSHTMLPath($description){
	return $description;
	$CI =& get_instance();
	$CI-> load -> library('simple_html_dom');
	$html = str_get_html($description);
	$i=1;

	$arr_images = array();

	foreach($html->find('img') as $element){
		$image_url =$element->src;

		$image_url= getOriginalImageAWSPath($image_url);
		$element->src=$image_url;
	}
		$html->save();
		return $html;
	}


function getSmartSKUS($site,$skus,$option_user_code) {
	if($site=="쿠팡"){
		if(strlen($option_user_code)>=5){
			return $option_user_code;
		}
	}
	return $skus;
}

?>
