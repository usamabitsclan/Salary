<?php

defined('BASEPATH') or exit('No direct script access allowed');

function salary_pdf($salary, $tag = '')
{      
    return app_salary_pdf('salary', LIBSPATH . 'pdf/salary_pdf', $salary, $tag);
}

function app_salary_pdf($type, $path, ...$params)
{
    $basename = ucfirst(basename(strbefore($path, EXT)));

    if (!endsWith($path, EXT)) {
        $path .= EXT;
    }

    $path = hooks()->apply_filters("{$type}_pdf_class_path", $path, ...$params);

    $path =  module_dir_path('hrm').'libraries'.'/pdf'.'/salary_pdf.php';

    include_once($path);

    return (new $basename(...$params))->prepare();
}
function salary_pdf_logo_url()
{
    $custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
    $width                     = 100;
    $logoUrl                   = '';
    
    $logoUrl = get_upload_path_by_type('company') . get_option('company_logo');
    

    $logoImage = '';
    if ($logoUrl != '') {
        $logoImage = '<img width="' . $width . 'px" src="' . $logoUrl . '">';
    }

    return hooks()->apply_filters('pdf_logo_url', $logoImage);
}


function salary_pdf_footer_logo()
{
    $custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
    $width                     = 4000;
    $logoUrl                   = '';
    
    $logoUrl = module_dir_path('hrm').'assets'.'/footer.png';

    $logoImage = '';
    if ($logoUrl != '') {
        $logoImage = '<img width="' . $width . 'px" src="' . $logoUrl . '">';
    }

    return hooks()->apply_filters('pdf_logo_url', $logoImage);
}
function convertNumberToWord($num)
{
    $num = str_replace(array(',', ' '), '' , trim($num));

    $num = (int) $num;
    $words = array();
    $list1 = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven',
        'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'
    );
    $list2 = array('', 'Ten', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety', 'Hundred');
    $list3 = array('', 'Thousand', 'Million', 'Billion', 'Trillion', 'Quadrillion', 'Quintillion', 'Sextillion', 'Septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return implode(' ', $words);
}
function numberTowords($num)
{

    $ones = array(
        0 =>"Zero",
        1 => "One",
        2 => "Two",
        3 => "Three",
        4 => "Four",
        5 => "Five",
        6 => "Six",
        7 => "Seven",
        8 => "Eight",
        9 => "Nine",
        10 => "Ten",
        11 => "Eleven",
        12 => "Twelve",
        13 => "Thirteen",
        14 => "Fourteen",
        15 => "Fifteen",
        16 => "Sixteen",
        17 => "Seventeen",
        18 => "Eighteen",
        19 => "Nineteen",
        "014" => "Fourteen"
    );
    $tens = array( 
        0 => "Zero",
        1 => "Ten",
        2 => "Twenty",
        3 => "Thirty", 
        4 => "Forty", 
        5 => "Fifty", 
        6 => "Sixty", 
        7 => "Seventy", 
        8 => "Eighty", 
        9 => "Ninety" 
    ); 
    $hundreds = array( 
        "Hundred", 
        "Thousand", 
        "Lac", 
        "Crore", 
        "Trillion", 
        "Quardrillion" 
    ); /*limit t quadrillion */
    $num = number_format($num,2,".",","); 
    $num_arr = explode(".",$num); 
    $wholenum = $num_arr[0]; 
    $decnum = $num_arr[1]; 
    $whole_arr = array_reverse(explode(",",$wholenum)); 
    krsort($whole_arr,1); 
    $rettxt = ""; 
    foreach($whole_arr as $key => $i){

        while(substr($i,0,1)=="0")
            $i=substr($i,1,5);
        if($i < 20){ 
            $rettxt .= $ones[$i]; 
        }elseif($i < 100){ 
            if(substr($i,0,1)!="0")  $rettxt .= $tens[substr($i,0,1)]; 
            if(substr($i,1,1)!="0") $rettxt .= " ".$ones[substr($i,1,1)]; 
        }else{ 
            if(substr($i,0,1)!="0") $rettxt .= $ones[substr($i,0,1)]." ".$hundreds[0]; 
            if(substr($i,1,1)!="0")$rettxt .= " ".$tens[substr($i,1,1)]; 
            if(substr($i,2,1)!="0")$rettxt .= " ".$ones[substr($i,2,1)]; 
        } 
        if($key > 0){ 
            $rettxt .= " ".$hundreds[$key]." "; 
        }
    } 
    if($decnum > 0){
        $rettxt .= " and ";
        if($decnum < 20){
            $rettxt .= $ones[$decnum];
        }elseif($decnum < 100){
            $rettxt .= $tens[substr($decnum,0,1)];
            $rettxt .= " ".$ones[substr($decnum,1,1)];
        }
    }
    print_r($rettxt);
    exit();
    return $rettxt;
}
function salary_date($date)
{
    $salary_month = date_parse($date);
   
    $dateObj   = DateTime::createFromFormat('!m', date('m')-1);
    $monthName = $dateObj->format('F'); // March
    return $monthName.','.$salary_month['year'];
}

function salary_issue_date()
{
    return date("jS").' '.date("F").' '.date("Y");
}
function salary_tax($tax)
{
    if($tax > 50000)
    {
        $salary = ($tax - 50000) * 5/100;
        return $salary ;
    }else{
        return 'NIL';
    }
}
function staff_name($id){
        
    $CI = & get_instance();
    $CI->load->model('hrm_model');
    $staff = $CI->hrm_model->getStaff($id, ['active' => 1]);
    return $staff->firstname.' '.$staff->lastname;
}
function image_phone()
{
    return module_dir_path('hrm').'assets'.'/phone.png';
    // base_url().'index.php/login/verify';
}
function image_email()
{
    return module_dir_path('hrm').'assets'.'/email.png';
}
function image_location()
{
    return module_dir_path('hrm').'assets'.'/location.png';

}

function debug($arr, $exit = false)
{
  print "<pre>";
  print_r($arr);
  print "</pre>";
  if($exit)
    exit;
}
