<?php
defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);

$info_right_column = '';
$info_left_column  = '';
$info_left_column .= salary_pdf_logo_url();
$info_right_column .= pdf_logo_url();
pdf_multi_row($info_left_column, $info_right_column, $pdf);



$pdf->Ln(5);

$html = '';
$html.='<h1 style="text-align:centre;font-weight:bold;font-size:30px;">BITSCLAN IT SOLUTIONS <br> (PRIVATE) LIMITED</h1><br>';
$html.='<p style="text-align:centre;font-size:23px;">Pay Slip ('.salary_date($salary->salary_month).')</p><br>';

$pdf->writeHTML($html, true, 0, true, 0);

$info_right_column = $info_left_column  = '';
$info_right_column .= '<p>Dated: '.salary_issue_date().'</p>';
pdf_multi_row($info_left_column, $info_right_column, $pdf);'';


$pdf->Ln(5);

$tbl = '';
$tbl.='
<table cellspacing="0" cellpadding="12" border="1">
    <tr>
        <td style="font-weight:bold;">Mr. '.staff_name($salary->staff_member).'</td>
        <td align="centre" style="font-weight:bold;">'.staff_designation($salary->staff_member).'</td>
    </tr>
    <tr>
       <td style="font-weight:bold;">Description:</td>
       <td align="centre" style="font-weight:bold;">Gross Income</td>
    </tr>
     <tr>
       <td>Basic Salary:</td>
       <td align="centre">'.$salary->basic_salary.'/-</td>

    </tr>
     <tr>
       <td>Bonus:</td>
       <td align="centre">'.$salary->bonus.'/-</td>

    </tr>
     <tr>
       <td>Un Paid Leave (Deduction): '.$salary->unpaid_leaves.'</td>
       <td align="centre">'.intval($salary->unpaid_leaves * ($salary->basic_salary / 30)) .'/-</td>

    </tr>
     <tr>
       <td>Withholding Tax 5%:</td>
       <td align="centre">'.salary_tax($salary->basic_salary).'/-</td>

    </tr>
     <tr>
       <td style="font-weight:bold;">Total:</td>
       <td align="centre">'.$salary->total_salary.'/-</td>
    </tr>

</table>';

$tbl.='

<table cellspacing="0" cellpadding="12" border="1">
  <tr>
    <td rowspan="3">Payment Date: '.salary_issue_date().' <br>Bank Name: '.staff_bank_name($salary->staff_member).' <br>Bank Account Title:'.staff_account_name($salary->staff_member).' <br>Bank Account No.:'.staff_account_no($salary->staff_member).'</td>
    <td align="centre"><b>NET PAY</b></td>
  </tr>

  <tr>
    <td align="centre">'.$salary->total_salary.'/-</td>
  </tr>

  <tr>
    <td align="centre"> '.convertNumberToWord($salary->total_salary).' only.</td>
  </tr>
</table>

';

$pdf->writeHTML($tbl, true, false, false, false, '');



$sign_section = '
<table style="padding-top:25px;" >
  
  <tbody>
    <tr>
      <td>
        <table cellpadding="10" style="padding-left: 25px;">
          <tr>
            <td>______________________</td>
          </tr>

          <tr>
            <td>
                <table style="padding-left: 15px;"> <tr> <td>Applicant`s Signature</td> </tr> </table>
            </td>
          </tr>
        </table>

      </td>

      
      <td>
        
        <table cellpadding="10" style="text-align:right; padding-right: 25px;">
          <tr>
            <td>____________________________</td>
          </tr>

          <tr>
            <td>
                <table style="padding-right: 15px;"> <tr> <td>Muhammad Awais Sarwar</td> </tr> </table>
            </td>
          </tr>

          <tr>
            <td>
                <table style="padding-right: 35px;"> <tr> <td>CEO/Director</td> </tr> </table>
            </td>
          </tr>
          
        </table>

      </td>

    </tr>
  </tbody>

</table>';

$pdf->writeHTML($sign_section, true, false, true, false, '');



$footer = '';

$footer .='
<table style="padding-top: 0px;">
  <tr>
    <td align="centre">________________________________________________________________________________________</td>
  </tr>
</table>';
$pdf->writeHTML($footer, true, false, true, false, '');


$footer ='
<table style="font-size: 13px;" >
  <tr>

    <td style="width:28%">
      <table cellpadding="1">
        <tr>
          <td style="width:17%;"><img src="'.image_phone().'" style="width:35px;"></td>
          <td align="left" style="width:83%;">+92 322 4625175<br>+92 322 9309305</td>
        </tr>
      </table>
    </td>

    <td style="width:30%"> 
      <table cellpadding="1" >
        <tr>
          <td style="width:40%;" align="right"><img  src="'.image_email().'" style="width:35px;"></td>
          <td style="width:60%;">  info@bitsclan.com<br>  hello@bitsclan.com</td>
        </tr>
      </table>
    </td>

    <td style="width:42%">
        <table cellpadding="1" >
        <tr>
          <td style="width:40%;" align="right"><img  src="'.image_location().'" style="width:30px;"></td>
          <td style="width:60%;">  First Floor, Rehman Arcade<br>  218 Main Ferozpur Road.</td>
        </tr>
      </table>
    </td>

  </tr>

</table>';
$pdf->Rect(0, 282, 2000, 15,'F',array(),array(129, 186, 34));


$pdf->writeHTML($footer, true, false, true, false, '');


