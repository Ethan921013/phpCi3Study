<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Outputs an array or variable
 *
 * @param    $var array, string, integer
 * @return    string
 */
function isJson($string) {

    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
}


function debug_var($var = '') {

    echo _before();
	if (is_array($var)) {
		print_r($var);
	} else {
		echo $var;
	}
	echo _after();
}


function show_json_result($var = '',$extra ='') {

    if(isJson($var)) {
		$arr_var = json_decode($var);	
	} else {
		return;
	}
	//var_dump($arr_var);
	//echo($arr_var->result);
	//echo ($arr_var["result"]);
	if($arr_var->result=="00") {
		echo _before_success_json("003300");	
		echo "[".$extra."][".$arr_var->it_id."] ==> ".$arr_var->result_message;
	} 
	else if($arr_var->result=="01") {
		echo _before_success_json("996633");
		echo "[".$extra."][".$arr_var->it_id."] ==> ".$arr_var->result_message;
	}
	else {
		echo _before_fail_json();
		echo "[".$extra."][".$arr_var->it_id."] ==> faild! <BR> Reason : ".$arr_var->result_message;
	}
	

	echo _after();
}

//------------------------------------------------------------------------------

/**
 * Outputs the last query
 *
 * @return    string
 */

function debug_last_query() {
	$CI = &get_instance();
	echo _before();
	echo $CI -> db -> last_query();
	echo _after();
}

/**
 * Outputs the last query
 *
 * @return    string
 */

function debug_last_query_error() {
	$CI = &get_instance();
	echo _before();
	echo $CI -> db -> last_query();
	echo $CI -> db ->_error_message();
	echo _after();
}


//------------------------------------------------------------------------------

/**
 * Outputs the query result
 *
 * @param    $query object
 * @return    string
 */

function debug_query_result($query = '') {
	echo _before();
	print_r($query -> result_array());
	echo _after();
}

//------------------------------------------------------------------------------

/**
 * Outputs all session data
 *
 * @return    string
 */
function debug_session() {
	$CI = &get_instance();
	echo _before();
	print_r($CI -> session -> all_userdata());
	echo _after();
}

//------------------------------------------------------------------------------

/**
 * Logs a message or var
 *
 * @param    $message array, string, integer
 * @return    string
 */

function debug_log($message = '') {
	is_array($message) ? log_message('debug', print_r($message)) : log_message('debug', $message);
}

//------------------------------------------------------------------------------

/**
 * _before
 *
 * @return    string
 */
function _before() {
	$rnd = rand(444444,999999);
	$rnd ="511383";
	$before = '<div style="padding:10px 20px 10px 20px; background-color:#'.$rnd.'; border:1px solid #d893a1; color: #fff; font-size: 12px;>' . "\n";
	$before .= '<h5 style="font-family:verdana,sans-serif; font-weight:bold; font-size:18px;">Debug Helper Output</h5>' . "\n";
	$before .= '<pre>' . "\n";
	return $before;
}
/**
 * _before
 *
 * @return    string
 */
function _before_success_json($color='') {
	$rnd = rand(444444,999999);
	$rnd =$color;
	$before = '<div style="padding:10px 20px 10px 20px; background-color:#'.$rnd.'; border:1px solid #d893a1; color: #fff; font-size: 12px;>' . "\n";
	$before .= '<h5 style="font-family:verdana,sans-serif; font-weight:bold; font-size:18px;">Result</h5>' . "\n";
	$before .= '<pre>' . "\n";
	return $before;
}
/**
 * _before
 *
 * @return    string
 */
function _before_fail_json() {
	$rnd = rand(444444,999999);
	$rnd ="FF3333";
	$before = '<div style="padding:10px 20px 10px 20px; background-color:#'.$rnd.'; border:1px solid #d893a1; color: #fff; font-size: 12px;>' . "\n";
	$before .= '<h5 style="font-family:verdana,sans-serif; font-weight:bold; font-size:18px;">Result</h5>' . "\n";
	$before .= '<pre>' . "\n";
	return $before;
}

//------------------------------------------------------------------------------

/**
 * _after
 *
 * @return    string
 */

function _after() {
	$after = '</pre>' . "\n";
	$after .= '</div>' . "\n";
	return $after;
}


function ordutf8($string, &$offset) {
    $code = ord(substr($string, $offset,1)); 
    if ($code >= 128) {        //otherwise 0xxxxxxx
        if ($code < 224) $bytesnumber = 2;                //110xxxxx
        else if ($code < 240) $bytesnumber = 3;        //1110xxxx
        else if ($code < 248) $bytesnumber = 4;    //11110xxx
        $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
        for ($i = 2; $i <= $bytesnumber; $i++) {
            $offset ++;
            $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
            $codetemp = $codetemp*64 + $code2;
        }
        $code = $codetemp;
    }
    $offset += 1;
    if ($offset >= strlen($string)) $offset = -1;
    return $code;
}

//------------------------------------------------------------------------------

function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                $level--;
                $ends_line_level = NULL;
                $new_line_level = $level;
                break;

                case '{': case '[':
                $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                $char = "";
                $ends_line_level = $new_line_level;
                $new_line_level = NULL;
                break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}
 ?>
