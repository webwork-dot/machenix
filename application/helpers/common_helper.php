<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* CodeIgniter
*
* An open source application development framework for PHP 5.1.6 or newer
*
* @package		CodeIgniter
* @author		ExpressionEngine Dev Team
* @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
* @license		http://codeigniter.com/user_guide/license.html
* @link		http://codeigniter.com
* @since		Version 1.0
* @filesource
*/

if ( ! function_exists('slugify'))
{
    function slugify($text) {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        //$text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
        return 'n-a';
        return $text;
    }
}

if (!function_exists('category_name')) {
    function category_name($category)
    {
        if (!empty($category)) {
            if (!empty($category->name)) {
                return html_escape($category->name);
            } else {
                if (!empty($category->second_name)) {
                    return html_escape($category->second_name);
                }
            }
        }
        return "";
    }
}

//get categories json
if (!function_exists('get_categories_json')) {
	function get_categories_json()
	{
		$ci =& get_instance();
		return $ci->category_model->get_categories_json();
	}
}

if ( ! function_exists('trans'))
{
    function trans($phrase = '') {
        $key = strtolower(preg_replace('/\s+/', '_', $phrase));
        $langArray[$key] = ucwords(str_replace('_', ' ', $key));
        return $langArray[$key];
    }
}

if (!function_exists('item_count')) {
    function item_count($items)  {
        if (!empty($items) && is_array($items)) {
            return count($items);
        }
        return 0;
    }
}

if (!function_exists('ind_currency')) {
    function ind_currency($price){
        // Check if the price is negative
        $is_negative = $price < 0;

        // Remove the negative sign temporarily
        $price = number_format($price, 2, '.', '');
        $price = abs((double)$price);

        $decimal_part = '';

        // Separate the decimal part if it exists
        if (strpos($price, '.') !== false) {
            list($price, $decimal_part) = explode('.', $price);
        }

        $explrestunits = "";
        if (strlen($price) > 3) {
            $lastthree = substr($price, strlen($price) - 3, strlen($price));
            $restunits = substr($price, 0, strlen($price) - 3);
            $restunits = (strlen($restunits) % 2 == 1) ? "0" . $restunits : $restunits;
            $expunit = str_split($restunits, 2);

            for ($i = 0; $i < sizeof($expunit); $i++) {
                if ($i == 0) {
                    $explrestunits .= (int)$expunit[$i] . ","; // Convert first value to integer
                } else {
                    $explrestunits .= $expunit[$i] . ",";
                }
            }
            $thecash = '₹' . $explrestunits . $lastthree;
        } else {
            $thecash = '₹' . $price;
        }

        // Add the decimal part back
        if ($decimal_part != '') {
            $thecash .= '.' . $decimal_part;
        }

        // Add the negative sign back if needed
        if ($is_negative) {
            $thecash = '-' . $thecash;
        }

        return $thecash;
    }
}

if ( ! function_exists('get_video_extension'))
{
    // Checks if a video is youtube, vimeo or any other
    function get_video_extension($url) {
        if (strpos($url, '.mp4') > 0) {
            return 'mp4';
        } elseif (strpos($url, '.webm') > 0) {
            return 'webm';
        } else {
            return 'unknown';
        }
    }
}

if ( ! function_exists('ellipsis'))
{
    // Checks if a video is youtube, vimeo or any other
    function ellipsis($long_string, $max_character = 30) {
        $short_string = strlen($long_string) > $max_character ? substr($long_string, 0, $max_character)."..." : $long_string;
        return $short_string;
    }
}

// Human readable time
if ( ! function_exists('readable_time_for_humans')){
    function readable_time_for_humans($duration) {
        if ($duration) {
            $duration_array = explode(':', $duration);
            $hour   = $duration_array[0];
            $minute = $duration_array[1];
            $second = $duration_array[2];
            if ($hour > 0) {
                $duration = $hour.' '.get_phrase('hr').' '.$minute.' '.get_phrase('min');
            }elseif ($minute > 0) {
                if ($second > 0) {
                    $duration = ($minute+1).' '.get_phrase('min');
                }else{
                    $duration = $minute.' '.get_phrase('min');
                }
            }elseif ($second > 0){
                $duration = $second.' '.get_phrase('sec');
            }else {
                $duration = '00:00';
            }
        }else {
            $duration = '00:00';
        }
        return $duration;
    }
}

if ( ! function_exists('trimmer'))
{
    function trimmer($text) {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
        return 'n-a';
        return $text;
    }
}

// RANDOM NUMBER GENERATOR FOR ELSEWHERE
if (! function_exists('random')) {
  function random($length_of_string) {
    // String of all alphanumeric character
    $str_result = '0123456789';

    // Shufle the $str_result and returns substring
    // of specified length
    return substr(str_shuffle($str_result), 0, $length_of_string);
  }
}

//generate unique id
if (!function_exists('generate_unique_id')) {
	function generate_unique_id()
	{
		$id = uniqid("", TRUE);
		return str_replace(".", "-", $id);
	}
}

//generate short unique id
if (!function_exists('generate_short_unique_id')) {
	function generate_short_unique_id()
	{
		$id = uniqid("", TRUE);
		return str_replace(".", "-", $id);
	}
}

//generate order number
if (!function_exists('generate_transaction_number')) {
	function generate_transaction_number()
	{
		$transaction_number = uniqid("", TRUE);
		return str_replace(".", "-", $transaction_number);
	}
}


if ( ! function_exists('get_phrase'))
{
    function get_phrase($phrase = '') {
        $key = strtolower(preg_replace('/\s+/', '_', $phrase));
        $langArray[$key] = ucwords(str_replace('_', ' ', $key));
        return $langArray[$key];
    }
}


	if(!function_exists('get_ref_no')){
		function get_ref_no()
		{
        	 
		$ci =& get_instance();
		return $ci->crud_model->get_ref_no();
	
		}
	}	




if (!function_exists('get_time_difference')) {
 function get_time_difference($timestamp){  
  date_default_timezone_set("Asia/Kolkata");         
  $time_ago        = strtotime($timestamp);
  $current_time    = time();
  $time_difference = $current_time - $time_ago;
  $seconds         = $time_difference;
  
  $minutes = round($seconds / 60); // value 60 is seconds  
  $hours   = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec  
  $days    = round($seconds / 86400); //86400 = 24 * 60 * 60;  
  $weeks   = round($seconds / 604800); // 7*24*60*60;  
  $months  = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60  
  $years   = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60
                
  if ($seconds <= 60){
    return "Just Now";
  } else if ($minutes <= 60){
    if ($minutes == 1){
      return "one minute ago";
    } else {
      return "$minutes minutes ago";
    }

  } else if ($hours <= 24){
    if ($hours == 1){
      return "an hour ago";
    } else {
      return "$hours hrs ago";
    }
  } else if ($days <= 7){
    if ($days == 1){
      return "yesterday";
    } else {
      return "$days days ago";
    }
  } else {    
     return "$days days ago";
  }
}

 
}


if (!function_exists('initials')) {
 function initials($name){  
     $name=strtoupper($name);
    //prefixes that needs to be removed from the name
    $remove = ['.', 'MRS', 'MISS', 'MS', 'MASTER', 'DR', 'MR'];
    $nameWithoutPrefix=str_replace($remove," ",$name);

  $words = explode(" ", $nameWithoutPrefix);

//this will give you the first word of the $words array , which is the first name
 $firtsName = reset($words); 

//this will give you the last word of the $words array , which is the last name
 $lastName  = end($words);

 $f1= substr($firtsName,0,1); // this will echo the first letter of your first name
 $f2= substr($lastName ,0,1); // this will echo the first letter of your last name
 
 return $f1.$f2;
 }
}



	if(!function_exists('get_unread_pending_approval')){
		function get_unread_pending_approval()
		{
        	 
		$ci =& get_instance();
		return $ci->crud_model->get_unread_pending_approval();
	
		}
	}	
	
	

if (!function_exists('rupees_word')) {
function rupees_word($number) {
    $number = abs($number);
    $no = round($number);
    $decimal = round($number - ($no = floor($number)), 2) * 100;    
    $digits_length = strlen($no);    
    $i = 0;
    $str = array();
    $words = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety');
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;            
            $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural;
        } else {
            $str [] = null;
        }  
    }
    
    $Rupees = implode(' ', array_reverse($str));
    if($decimal<20){ $paise = ($decimal) ? "And  " . ($words[$decimal - $decimal]) ." " .($words[$decimal])." Paise" : '';  }
    else{  $paise = ($decimal) ? "And  " . ($words[$decimal - $decimal%10]) ." " .($words[$decimal%10])." Paise" : '';   }
    return ($Rupees ? 'Rupees ' . $Rupees : '') . $paise . " Only";
}
} 

if (!function_exists('admin_url')) {
	function admin_url()
	{
		return base_url() . "admin/";
	}
}
  
if (!function_exists('accounts_url')) {
	function accounts_url()
	{
		return base_url() . "accounts/";
	}
}

if (!function_exists('staff_url')) {
	function staff_url()
	{
		return base_url() . "staff/";
	}
}

if (!function_exists('manager_url')) {
	function manager_url()
	{
		return base_url() . "manager/";
	}
}

if (!function_exists('digital_coordinator_url')) {
	function digital_coordinator_url()
	{
		return base_url() . "digital_coordinator/";
	}
}

if (!function_exists('digital_url')) {
	function digital_url()
	{
		return base_url() . "digital_coordinator/";
	}
}

if (!function_exists('patient_url')) {
	function patient_url()
	{
		return base_url() . "patient_coordinator/";
	}
}
if (!function_exists('patient_coordinator_url')) {
	function patient_coordinator_url()
	{
		return base_url() . "patient_coordinator/";
	}
}


if (!function_exists('hr_url')) {
	function hr_url()
	{
		return base_url() . "hr/";
	}
}
/*
if (!function_exists('currentUrl')) {
function currentUrl( $trim_query_string = false ) {
    $pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "//" : "//";
    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    if( ! $trim_query_string ) {
        // $pageURL1 = preg_replace('#^www\.(.+\.)#i', '$1', $pageURL);
        $pageURL1 = str_replace("www.","",$pageURL);
        // $pageURL1 = $pageURL;
        return $pageURL1;
    } else {
        $url = explode( '?',$pageURL);
        $x_url = str_replace("www.","",$url);
        return $x_url;
    }
}
}*/

if (!function_exists('currentUrl')) {
    function currentUrl($trim_query_string = false) {
        // Use 'http://' instead of just '//'
        $pageURL = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

        if (!$trim_query_string) {
            // Use str_replace instead of preg_replace to remove 'www.'
            $pageURL1 = str_replace("www.", "", $pageURL);
            return $pageURL1;
        } else {
            $url = explode('?', $pageURL);
            $x_url = str_replace("www.", "", $url[0]); // Use $url[0] to get the URL without query string
            return $x_url;
        }
    }
}


if ( ! function_exists('getExtension'))
{
    function getExtension($str) {
         $i = strrpos($str, ".");
        if (!$i) {
            return "";
        }
        
        $l   = strlen($str) - $i;
        $ext = substr($str, $i + 1, $l);
        return $ext;
    }
}


if (!function_exists('main_url')) {
	function main_url()
	{
	   return "https://webwork.co.in/rapl_crm/";
	}
}


if (!function_exists('get_product_image_url')) {
	function get_product_image_url($image, $size_name)
	{
		return base_url() . $image->$size_name;
	}
}


if (!function_exists('getDatesFromRange')) {
function getDatesFromRange($start, $end, $format = 'Y-m-d') { 
      
    // Declare an empty array 
    $array = array(); 
      
    // Variable that store the date interval 
    // of period 1 day 
    $interval = new DateInterval('P1D'); 
  
    $realEnd = new DateTime($end); 
    $realEnd->add($interval); 
  
    $period = new DatePeriod(new DateTime($start), $interval, $realEnd); 
  
    // Use loop to store date into array 
    foreach($period as $date) {                  
        $array[] = $date->format($format);  
    } 
  
    // Return the array elements 
    return $array; 
} 
} 
  

//delete file from server
if (!function_exists('delete_file_from_server')) {
	function delete_file_from_server($path)
	{
		$full_path = FCPATH . $path;
		if (strlen($path) > 15 && file_exists($full_path)) {
			@unlink($full_path);
		}
	}
}

//generate slug
if (!function_exists('str_slug')) {
	function str_slug($text)
	{
	    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        //$text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text))
        return 'n-a';
        return $text;
	}
}

if (!function_exists('price_format_decimal')) {
	function price_format_decimal($price)
	{
		return number_format($price, 2, ".", "");
	}
}

if( ! function_exists('get_time_difference_php'))
    {
        function get_time_difference_php($created_time)
        {
            
            date_default_timezone_set('Asia/Calcutta');
            $str            = strtotime($created_time);
            $today          = strtotime(date('Y-m-d H:i:s'));
            $time_differnce = $today - $str;
            $years          = 60 * 60 * 24 * 365;
            $months         = 60 * 60 * 24 * 30;
            $days           = 60 * 60 * 24;
            $hours          = 60 * 60;
            $minutes        = 60;
            if (intval($time_differnce / $years) > 1) {
                return "on " . date('D, d F Y h:i a', strtotime($created_time));
            } elseif (intval($time_differnce / $years) > 0) {
                return "on " . date('D, d F Y h:i a', strtotime($created_time));
            } elseif (intval($time_differnce / $months) > 1) {
                return "on " . date('D, d F Y h:i a', strtotime($created_time));
            } elseif (intval(($time_differnce / $months)) > 0) {
                return "on " . date('D, d F h:i a', strtotime($created_time));
            } elseif (intval(($time_differnce / $days)) > 1) {
                return "on " . date('D, d F h:i a', strtotime($created_time));
            } elseif (intval(($time_differnce / $days)) > 0) {
                $var = date('D, d F h:i a', strtotime($created_time));
                return "on " . $var;
            } elseif (intval(($time_differnce / $hours)) > 1) {
                return intval(($time_differnce / $hours)) . ' hrs ' . 'ago';
            } elseif (intval(($time_differnce / $hours)) > 0) {
                return intval(($time_differnce / $hours)) . ' hr ' . 'ago';
            } elseif (intval(($time_differnce / $minutes)) > 1) {
                return intval(($time_differnce / $minutes)) . ' mins ' . 'ago';
            } elseif (intval(($time_differnce / $minutes)) > 0) {
                return intval(($time_differnce / $minutes)) . ' min ' . 'ago';
            } elseif (intval(($time_differnce)) > 1) {
                return "Just now";
            } else {
                return 'few seconds';
            }
        }
    }




if (!function_exists('getDistance')) {
function getDistance($latitude, $longitude, $checkout_latitude,$checkout_longitude){ 
    $latitudeFrom   = $latitude;
    $longitudeFrom  = $longitude;
    $latitudeTo     = $checkout_latitude;
    $longitudeTo    = $checkout_longitude;
    
    $theta    = $longitudeFrom - $longitudeTo;
    $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
    $dist    = acos($dist);
    $dist    = rad2deg($dist);
    $miles    = $dist * 60 * 1.1515;
    
  
    return round($miles * 1.609344, 2);//in km
 }
}

if (!function_exists('indian_price')) {
function indian_price($price){
    $price=(int) $price;
    $explrestunits = "" ;
    if(strlen($price)>3){
        $lastthree = substr($price, strlen($price)-3, strlen($price));
        $restunits = substr($price, 0, strlen($price)-3); // extracts the last three digits
        $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
        $expunit = str_split($restunits, 2);
        for($i=0; $i<sizeof($expunit); $i++){
            // creates each of the 2's group and adds a comma to the end
            if($i==0)
            {
                $explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer
            }else{
                $explrestunits .= $expunit[$i].",";
            }
        }
        $thecash = $explrestunits.$lastthree;
    } else {
        $thecash = $price;
    }
    return $thecash;
 }
}

if (!function_exists('numberToCurrency')) {
function numberToCurrency($num)
{
    if(setlocale(LC_MONETARY, 'en_IN'))
      return money_format('%.0n', $num);
    else {
      $explrestunits = "" ;
      if(strlen($num)>3){
          $lastthree = substr($num, strlen($num)-3, strlen($num));
          $restunits = substr($num, 0, strlen($num)-3); // extracts the last three digits
          $restunits = (strlen($restunits)%2 == 1)?"0".$restunits:$restunits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
          $expunit = str_split($restunits, 2);
          for($i=0; $i<sizeof($expunit); $i++){
              // creates each of the 2's group and adds a comma to the end
              if($i==0)
              {
                  $explrestunits .= (int)$expunit[$i].","; // if is first value , convert into integer
              }else{
                  $explrestunits .= $expunit[$i].",";
              }
          }
          $thecash = $explrestunits.$lastthree;
      } else {
          $thecash = $num;
      }
      return '₹ ' . $thecash;
    }
}
}

if (!function_exists('num2alpha')) {

function num2alpha($n)
{
    for($r = ""; $n >= 0; $n = intval($n / 26) - 1)
        $r = chr($n%26 + 0x41) . $r;
    return $r;
}
}


if (!function_exists('page_number')) {

function page_number($per_page)
{
   $page = 1;
   $page     =$_GET['page'];
   if (isset($page) && $page != ""):
    $page = $page;
  else:
    $page = 1;
  endif;
  $start= $per_page*($page-1);
   return $start;
}
}

if (!function_exists('rapl_url')) {
	function rapl_url()
	{
	   return "https://raplgroup.in/";
	}
}

if (!function_exists('getBetweenDates')) {
	function getBetweenDates($startDate, $endDate)
    {
        $rangArray = [];
    
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);
    
        for (
            $currentDate = $startDate;
            $currentDate <= $endDate;
            $currentDate += (86400)
        ) {
    
            $date = date('Y-m-d', $currentDate);
            $rangArray[] = $date;
        }
    
        return $rangArray;
    }
}


if (!function_exists('total_days')) {
function total_days($system_date){
//get Date diff as intervals 
date_default_timezone_set('Asia/Calcutta'); 
$system_date=date("Y-m-d", strtotime($system_date));
$current_date=date("Y-m-d");

$d1 = new DateTime($current_date);
$d2 = new DateTime($system_date);
$interval = $d1->diff($d2);
$diffInDays    =  $interval->format('%a'); 
return $diffInDays; 
}
}


if (!function_exists('cal_percentage')) {
 function cal_percentage($num_amount, $num_total){
  $count1 = $num_amount / $num_total;
  $count2 = $count1 * 100;
  $count = number_format($count2, 0);
  return $count;
 }
}
 
if (!function_exists('get_ext')) {
 function get_ext($file_name){
  $extension = pathinfo($file_name, PATHINFO_EXTENSION);
  return $extension;
 }
}

if (!function_exists('get_salary_per')) {
 function get_salary_per($salary,$per){
	$cal_per = price_format_decimal($per/100);
	$total_salary = $salary * $cal_per;
	return price_format_decimal($total_salary);
 }
}

if (!function_exists('roundToNearestHalf')) {
	function roundToNearestHalf($inputValue) {
    return floor($inputValue * 2) / 2;
 }
}

if (!function_exists('roundToNearestHalfHigh')) {
	function roundToNearestHalfHigh($inputValue) {
    return ceil($inputValue * 2) / 2;
 }
}


if (!function_exists('gross_salary_earned')) {
 function gross_salary_earned($gross_package,$day_of_month,$present_day){
	$gross_salary_earned = ($gross_package/$day_of_month)*$present_day;
	return (int) round($gross_salary_earned);
 }
}

if (!function_exists('round_int')) {
 function round_int($amount){
	return (int) round($amount);
 }
}


if (!function_exists('calculate_ptax')) {
	  function calculate_ptax($punch_date, $gross_salary, $gender) {
			$def_date=date("m", strtotime($punch_date));
			$ptax = 0;        
			if ($gender === 'Male') {
				if ($gross_salary <= 7500) {
					$ptax = 0;
				} elseif ($gross_salary > 7500 && $gross_salary <= 10000) {
					$ptax = 175;
				} else { // Above 10000
					// In February, P.TAX is 300, otherwise 200
					$ptax = $def_date === '02' ? 300 : 200;
				}
			} elseif ($gender === 'Female') {
				if ($gross_salary <= 25000) {
					$ptax = 0;
				} else { // Above 25000
					// In February, P.TAX is 300, otherwise 200
					$ptax = $def_date === '02' ? 300 : 200;
				}
			}

			return $ptax;
		}
		}

   if (!function_exists('calculate_pf')) {
	function calculate_pf($basic, $edu_allowances) {
        // Calculate total amount (basic + edu_allowances)
        $total_amount = $basic + $edu_allowances;
		$percentage = 12;
		$pf_tax = $percentage / 100;
		
        if ($total_amount >= 15000) {
            // PF is a fixed amount of 1800 if total amount is 15000 or more
            $pf = 1800;
        } else {
            // Calculate PF @ 12% of total amount for total amount less than 15000
            $pf = $total_amount * $pf_tax;
        }

        return round_int($pf);
     }
    }

	if (!function_exists('calculate_esic')) {	
	  function calculate_esic($basic, $hra, $edu_allowances) {
        // Calculate total package (basic + hra + edu_allowances)
        $total_package = $basic + $hra + $edu_allowances;
			
		$percentage = 0.75;
		$esic_tax = $percentage / 100;
		
        // Check if the total package is less than Rs. 21000
        if ($total_package < 21000) {
            // Calculate ESIC contribution (employee's contribution) as 0.75% of the total package
            $esic_contribution = $total_package * $esic_tax;
        } else {
            // If the total package is greater than or equal to Rs. 21000, ESIC is not applicable (R/OFF)
            $esic_contribution = 0;
        }

        return round_int($esic_contribution);
    }
    }

   if (!function_exists('get_emi')) {
	function get_emi($total, $instalment) {
		$emi = $total / $instalment;
        return price_format_decimal($emi);
     }
    }
	
	
if (!function_exists('convertFileSize')) {
   function convertFileSize($bytes) {
    if ($bytes >= 1099511627776) {
        return number_format($bytes / 1099511627776, 2) . ' TB';
    } elseif ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
 }
} 

 if (!function_exists('ff_round')) {
	function ff_round($number) {
	   if ($number == 0.5) {
		  return 0;
		} else {
		  return round($number);
		}
    }
  }
  
if (!function_exists('clean_and_escape')) {
  function clean_and_escape($str){
        $CI =& get_instance();
        $CI->load->helper('security');

        // Remove white spaces and escape the string
        $cleaned_str = html_escape(trim($str));

        return $cleaned_str;
    }
}
if (!function_exists('isSpecialSaturday')) {
  function isSpecialSaturday($date){
	  return (date('N', strtotime($date)) == 6);
    }
}
if (!function_exists('current_year')) {
  function current_year(){
        $today = new DateTime();
        $year = $today->format('Y');
        $next_year = $today->modify('+1 year')->format('y');
        $academic_year = $year . '-' . $next_year;

        return $academic_year;
    }
}
  
if (!function_exists('rupeesToWords')) {
  function rupeesToWords($amount){
        $rupeeWords = array(
			"Zero", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten",
			"Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"
		);

		$tensWords = array(
			"", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"
		);

		if ($amount < 20) {
			return $rupeeWords[$amount];
		} elseif ($amount < 100) {
			$tens = (int)($amount / 10);
			$ones = $amount % 10;
			return $tensWords[$tens] . ($ones > 0 ? " " . $rupeeWords[$ones] : "");
		} elseif ($amount < 1000) {
			$hundreds = (int)($amount / 100);
			$remainder = $amount % 100;
			return $rupeeWords[$hundreds] . " Hundred" . ($remainder > 0 ? " and " . rupeesToWords($remainder) : "");
		} elseif ($amount < 100000) {
			$thousands = (int)($amount / 1000);
			$remainder = $amount % 1000;
			return rupeesToWords($thousands) . " Thousand" . ($remainder > 0 ? " " . rupeesToWords($remainder) : "");
		} elseif ($amount < 10000000) {
			$lakhs = (int)($amount / 100000);
			$remainder = $amount % 100000;
			return rupeesToWords($lakhs) . " Lakh" . ($remainder > 0 ? " " . rupeesToWords($remainder) : "");
		} elseif ($amount < 1000000000) {
			$crores = (int)($amount / 10000000);
			$remainder = $amount % 10000000;
			return rupeesToWords($crores) . " Crore" . ($remainder > 0 ? " " . rupeesToWords($remainder) : "");
		} else {
			return "Amount is too large to convert";
		}
    }
}

if (!function_exists('isValidPhoneNumber')) {
  function isValidPhoneNumber($phone_number){
        return preg_match('/^[0-9]{10}+$/', $phone_number);
    }
}


if (!function_exists('get_days_in_month')) {
function get_days_in_month($month,$year) {
    // Check if the provided month and year are valid
    if ($month < 1 || $month > 12 || $year < 1) {
        return 0;
    }

    // Create a DateTime object for the first day of the specified month
    $firstDayOfMonth = new DateTime("$year-$month-01");

    // Get the last day of the month
    $lastDayOfMonth = $firstDayOfMonth->modify('last day of')->format('d');

    return (int)$lastDayOfMonth;
}
}
// ------------------------------------------------------------------------
/* End of file common_helper.php */
/* Location: ./system/helpers/common.php */

function product_arr() {
    return json_decode('', true);
}

if (!function_exists('formatHistoryTime')) {
    function formatHistoryTime(string $datetime, ?int $nowTs = null): string
    {
        $nowTs = $nowTs ?? time();

        $ts = strtotime($datetime);
        if ($ts === false) {
            return '';
        }

        // Start of days
        $todayStart = strtotime(date('Y-m-d 00:00:00', $nowTs));
        $dateStart  = strtotime(date('Y-m-d 00:00:00', $ts));

        $diffDays = (int)(($todayStart - $dateStart) / 86400);

        $timePart = date('g:i A', $ts); // 12-hr format

        if ($diffDays === 0) {
            return 'Today, ' . $timePart;
        }

        if ($diffDays === 1) {
            return 'Yesterday, ' . $timePart;
        }

        // Month short + day
        $monthDay = date('M j', $ts);

        // If different year, include year
        if (date('Y', $ts) !== date('Y', $nowTs)) {
            return $monthDay . ', ' . date('Y', $ts) . ', ' . $timePart;
        }

        return $monthDay . ', ' . $timePart;
    }
}

if (!function_exists('array_to_list')) {
    function array_to_list($arr = []){
        if(count($arr) == 0) {
            return '-';
        } else {
            $html = '';
            foreach($arr as $a) {

                $html .= '<li>' . (($a) ? $a : '-') . '</li>';
            }

            $html = '<ul class="mb-0">' . $html . '</ul>';
            return $html;
        }
    }
}