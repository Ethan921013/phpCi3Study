<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

function ifNextExistKeyInArray($array, $key, $default_return = '') {

	if (array_key_exists($key, $array)) {
		return trim($array[$key]);
	} else {
		return $default_return;
	}

	//
	// if(is_array($array)) {
	//
	// } else {
	// //String 케이스
	// return $array;
	// }
}

function IsNullOrEmptyString($question) {
	return (!isset($question) || trim($question) == '');
}
function IsNullOrEmptyString2($question) {
	if (!isset($question) || trim($question) == '' || empty($question) || strlen($question) == 0) {
		return true;
	} else {
		return false;
	}
}
function IsNullOrEmptyStringSetDefault($question, $default_return = '') {

	if (!isset($question) || trim($question) == '') {

		return $default_return;
	} else {
		return $question;
	}
}

function IfDefalut($value, $compare, $return) {
	if ($value == $compare) {
		return $return;
	} else {
		return $value;
	}
}

function time_elapsed_string($ptime) {
	$etime = time() - $ptime;

	if ($etime < 10) {
		return '방금전';
	}

	$a = array(12 * 30 * 24 * 60 * 60 => '년', 30 * 24 * 60 * 60 => '개월', 24 * 60 * 60 => '일', 60 * 60 => '시간', 60 => '분', 1 => '초');

	foreach ($a as $secs => $str) {
		$d = $etime / $secs;
		if ($d >= 1) {
			$r = round($d);
			return $r . '' . $str . ($r > 1 ? '' : '') . '전';
		}
	}
}

function autolink($string) {
	$content_array = explode(" ", $string);
	$output = '';

	foreach ($content_array as $content) {
		//starts with http://
		if (substr($content, 0, 7) == "http://")
			$content = '<a href="' . $content . '" target=_blank>' . $content . '</a>';

		//starts with www.
		if (substr($content, 0, 4) == "www.")
			$content = '<a href="http://' . $content . '" target=_blank>' . $content . '</a>';

		$output .= " " . $content;
	}

	$output = trim($output);
	return $output;
}

function getMobileLanguage($lang) {
	$lang = strtolower($lang);
	if (strpos($lang, 'en') !== false) {
		$lang = "en";
	} else if (strpos($lang, 'cn') !== false) {
		$lang = "zh";
	} else if (strpos($lang, 'tw') !== false) {
		$lang = "tw";
	} else if (strpos($lang, 'ja') !== false || strpos($lang, 'jp') !== false) {
		$lang = "ja";
	} else if (strpos($lang, 'ko') !== false) {
		$lang = "ko";
	} else {
		$lang = "en";
	}
	return $lang;

}

function getWebLanguage($lang) {
	$lang = strtolower($lang);
	if (strpos($lang, 'en') !== false) {
		$lang = "en";
	} else if (strpos($lang, 'zh') !== false) {
		$lang = "en";
	} else if (strpos($lang, 'tw') !== false) {
		$lang = "en";
	} else if (strpos($lang, 'ja') !== false || strpos($lang, 'jp') !== false) {
		$lang = "en";
	} else if (strpos($lang, 'ko') !== false) {
		$lang = "ko";
	} else {
		$lang = "en";
	}
	return $lang;

}

function getICNCurlLangunageCode($lang) {
	$lang = strtolower($lang);
	if (strpos($lang, 'en') !== false) {
		$lang = "E";
	} else if (strpos($lang, 'cn') !== false) {
		$lang = "C";
	} else if (strpos($lang, 'tw') !== false) {
		$lang = "C";
	} else if (strpos($lang, 'ja') !== false || strpos($lang, 'jp') !== false) {
		$lang = "J";
	} else if (strpos($lang, 'ko') !== false) {
		$lang = "K";
	} else {
		$lang = "E";
	}
	return $lang;

}

function parseMakingURL($product_url, $target_url) {

	$parts = parse_url($product_url);
	// 스키마없이 시작하는 URL을 처리함!
	//var_dump($parts);
	//echo "\n";
	//log_message('debug',"OUT : ".print_r($parts)."");

	if (isset($parts["host"]) == true) {
		//log_message('debug',$product_url);

		if (!isset($parts["scheme"])) {
			// $product_url = "http:$product_url";
		}
	}
	//	log_message('debug',"1");
	//log_message('debug',"OUT : ".$parts["scheme"]."");
	//log_message('debug',$product_url);

	if (filter_var(trim($product_url), FILTER_VALIDATE_URL) === FALSE) {
		log_message('debug', "URL이 아님 : " . $product_url . " :: " . isset($parts["host"]) . "");
		//echo $product_url."(O)";
		//echo "\n";
		//echo $target_url;
		//echo "\n";
		//echo parse_url($target_url, PHP_URL_SCHEME);
		//echo "\n";
		//echo parse_url($target_url, PHP_URL_SCHEME)."://".parse_url($target_url, PHP_URL_HOST).$product_url;
		//echo "\n";
		//echo parse_url($target_url, PHP_URL_HOST);
		//echo parse_url($target_url, PHP_URL_SCHEME)."://".parse_url($target_url, PHP_URL_HOST).$product_url;
		//exit;
		return parse_url($target_url, PHP_URL_SCHEME) . "://" . parse_url($target_url, PHP_URL_HOST) . $product_url;
	} else {
		//echo $product_url."(X)";
		//echo "\n";
		return $product_url;
	}
}

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function generateRandomSimpleString($length = 10) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function generateRandomSimpleStringWithNumber($length = 10) {
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function remoteFileExists($url) {
	$curl = curl_init($url);
	//don't fetch the actual page, you only want to check the connection is ok
	curl_setopt($curl, CURLOPT_NOBODY, true);
	//do request
	$result = curl_exec($curl);
	$ret = false;
	//if request did not fail
	if ($result !== false) {
		//if request was ok, check response code
		$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($statusCode == 200) {
			$ret = true;
		}
	}
	curl_close($curl);
	return $ret;
}

function remoteFileExists_by_fopen($url) {
	$handle = @fopen($url, 'r');
	$ret = false;
	if (!$handle) {
		$ret = false;
	} else {
		$ret = true;
	}
}

/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles (default)                         :*/
/*::                  'K' is kilometers                                      :*/
/*::                  'N' is nautical miles                                  :*/
/*::  Worldwide cities and other features databases with latitude longitude  :*/
/*::  are available at http://www.geodatasource.com                          :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: http://www.geodatasource.com                        :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2015		   		     :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
}


function set_error_form($msg) {
	
	if(strlen($msg)>0){
		return "has-error";
	}
}


function is_english($string) {
	if (!preg_match('/[^A-Za-z0-9]/', $string)) // '/[^a-z\d]/i' should also work.
	{
		return true;
	} else {
		return false;
	}
}

/**
 * @param $value
 * @return mixed
 */
function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
    $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
    $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
    $result = str_replace($escapers, $replacements, $value);
    return $result;
}

function addQuotes($string) {
    return '"'. implode('","', explode(',', $string)) .'"';
}
/**
 * cut string in utf-8
 * @author Taegon Kim (https://taegon.kim)
 * @param string $str     source string
 * @param int    $len     cut length
 * @param int    $checkmb if this argument is true, the function treats multibyte character as two bytes. Default: false.
 * @param string $tail    abbreviation symbol
 * @return string  processed string
 */
function strcut_utf8($str, $len, $checkmb=false, $tail='...') {
    preg_match_all('/[\xEA-\xED][\x80-\xFF]{2}|./', $str, $match);

    $m    = $match[0];
    $slen = strlen($str);  // length of source string
    $tlen = strlen($tail); // length of tail string
    $mlen = count($m); // length of matched characters

    if ($slen <= $len) return $str;
    if (!$checkmb && $mlen <= $len) return $str;

    $ret   = array();
    $count = 0;

    for ($i=0; $i < $len; $i++) {
        $count += ($checkmb && strlen($m[$i]) > 1)?2:1;

        if ($count + $tlen > $len) break;
        $ret[] = $m[$i];
    }

    return join('', $ret).$tail;
}
?>
