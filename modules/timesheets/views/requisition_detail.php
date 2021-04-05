<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
 $check = $this->input->get('check'); ?>
<?php if(isset($id)) { ?>
    <div id="wrapper">
    <div class="content">
      <div class="row">
<div class="content mt-6 p-2 row">
<div class="panel_s">
	<div class="panel-body w-100">
    <div class="wrap">

      <?php 
        $status['name'] = '';
        $status['color'] = '';

 ?>
      <div class="ribbonc" ><span><?php echo html_entity_decode($status['name']); ?></span></div>
      </div>
  		<div class="row">
      
    	</div>
        <h4><?php echo _l('general_infor'); ?></h4>
      
    		<hr/>
    	<div class="col-md-6">
    		<table class="table border table-striped ">
            <tbody>
              <tr>
                <td><?php echo _l('subject'); ?></td>
                <td><?php echo html_entity_decode($request_leave->subject); ?></td>
              </tr>
              <tr>
                <td><?php echo _l('category_for_leave'); ?></td>
                <td><?php                
                echo _l($rel_type); ?></td>
              </tr>
              <?php 
              if($rel_type == 'Leave'){ 

                ?>
              <tr>
                <td><?php echo _l('type_of_leave'); ?></td>
                <td><?php echo get_type_of_leave_name($request_leave->type_of_leave); ?></td>
              </tr>
             <?php }
              ?>
            
              <tr>
                <td><?php echo _l('follower'); ?></td>
                <td>
                <?php 
                $followers = explode(',', $request_leave->followers_id);
                $views = '';
                if(count($followers) > 0){
                  foreach($followers as $fl){
                    $views .= '<a href="' . admin_url('staff/profile/' . $fl) . '">' . staff_profile_image($fl,[
                      'staff-profile-image-small mright5',
                      ], 'small', [
                      'data-toggle' => 'tooltip',
                      'data-title'  => get_staff_full_name($fl),
                      ]) . '</a>';
                  }
                }
                echo html_entity_decode($views);
                ?></td>
              </tr>

              <!-- handover recipients -->
              <?php if($rel_type == 'Leave'){  ?>
                <tr>
                  <td><?php echo _l('handover_recipients'); ?></td>
                  <td>

                  <?php 
                  $handover_recipient = $request_leave->handover_recipients;
                  $views_handover_recipient = '';
                  if(($handover_recipient != null ) && $handover_recipient != ''){
                      $views_handover_recipient .= '<a href="' . admin_url('staff/profile/' . $handover_recipient) . '">' . staff_profile_image($handover_recipient,[
                        'staff-profile-image-small mright5',
                        ], 'small', [
                        'data-toggle' => 'tooltip',
                        'data-title'  => get_staff_full_name($handover_recipient),
                        ]) . '</a>';
                  }
                  echo html_entity_decode($views_handover_recipient);
                  ?></td>
                </tr>
            <?php } ?>

            </tbody>
        </table>
    	</div>
       
    	<div class="col-md-6">
    		<table class="table table-striped">
            <tbody>
              <tr>
                <td><?php echo _l('project_datecreated'); ?></td>
                <td><?php 
                $datecreated = $request_leave->datecreated;
                if($datecreated == ''){
                  $datecreated = $request_leave->start_time;
                }
                echo _d($datecreated); ?></td>
              </tr>
              <tr>
                <td><?php echo _l('time'); ?></td>
                <td><?php echo _d($request_leave->start_time).' - '._d($request_leave->end_time); ?></td>
              </tr>
              <?php if($request_leave->rel_type != 4){ ?>
                 <tr>
                  <td><?php echo _l('Number_of_leaving_day'); ?></td>
                  <td><?php echo html_entity_decode($request_leave->number_of_leaving_day); ?></td>
                </tr>
                <tr>
                  <td><?php echo _l('number_of_leave_days_allowed'); ?></td>
                  <td><?php echo html_entity_decode($number_day_off); ?></td>
                </tr>
               <?php } ?>
               <tr>
                <td><?php echo _l('reason'); ?></td>
                <td><?php echo html_entity_decode($request_leave->reason); ?></td>
              </tr>
            </tbody>
         </table>
    	</div>
  

    	<div class="col-md-12">
    		  <h4><?php echo _l('Other_Info') ?></h4>
        <hr/>
    	</div>
    	
      
    	<div class="col-md-6">
    		<table class="table table-striped">  
          <tbody>
              <tr>
                <td><?php echo _l('requester'); ?></td>
                <td>
                <?php 
                $_data = '<a href="' . admin_url('staff/profile/' . $request_leave->staff_id) . '">' . staff_profile_image($request_leave->staff_id, [
                'staff-profile-image-small',
                ]) . '</a>';
                $_data .= ' <a href="' . admin_url('staff/profile/' . $request_leave->staff_id) . '">' . get_staff_full_name($request_leave->staff_id) . '</a>';
                echo html_entity_decode($_data);
                ?></td>
              </tr>

              <tr>
                <td><?php echo _l('email'); ?></td>
                <td><?php echo html_entity_decode($request_leave->email); ?></td>
              </tr>
              <tr>
                <td><?php echo _l('department'); ?></td>
                <td><?php echo html_entity_decode($request_leave->name); ?></td>
              </tr>
            </tbody>
        </table>
    	</div>

      <?php 
       if($request_leave->rel_type == 4 && count($advance_payment) > 0){ ?>
      <div class="col-md-6">
        <p class="bold text-success"><?php echo _l('advance_payment_money').': '; ?></p>
        <table class="table table-striped">  
          
          <tbody>
            <tr>
            <td><?php echo _l('used_to'); ?></td>
            <td><?php echo _l('amount_of_money'); ?></td>

          </tr>
            <?php 
            $sum_mn = 0;
            foreach($advance_payment as $ad){ ?>
              <tr>
                <td><?php echo html_entity_decode($ad['used_to']); ?></td>
                <td><?php echo app_format_money($ad['amoun_of_money'],''); ?></td>
              </tr>
            <?php 
            $sum_mn += $ad['amoun_of_money'];
            } ?>
              <tr>
                <td><?php echo _l('total'); ?></td>
                <td><?php echo app_format_money($sum_mn,''); ?></td>
              </tr>
              <tr>
                <td><?php echo _l('advance_payment_reason'); ?></td>
                <td><?php echo html_entity_decode($advance_payment[0]['advance_payment_reason']); ?></td>
              </tr>
              <tr>
                <td><?php echo _l('request_date'); ?></td>
                <td><?php echo _d($advance_payment[0]['request_date']); ?></td>
              </tr>
            </tbody>

        </table>
        <div class="row">
        <div class="col-md-5">
          <?php $amount_received = (isset($request_leave) ? app_format_money($request_leave->amount_received,'') : '');
          ?>

          <label for="amount_received" class="control-label"><?php echo _l('amount_received') ?></label>
           <input type="text" id="amount_received" name="amount_received" class="form-control"  value="<?php echo html_entity_decode($amount_received); ?>" aria-invalid="false" data-type="currency" required>
        </div>
        <div class="col-md-5">
          <?php $received_date = (isset($request_leave) ? _d($request_leave->received_date): '');
           echo render_date_input('received_date','received_date',$received_date); ?>
        </div>
        <div class="col-md-2 update-btn">          
          <a href="#" onclick="advance_payment_update(<?php echo html_entity_decode($request_leave->id); ?>); return false;" class="btn btn-info pull-right"><?php echo _l('update'); ?></a>
        </div>
      </div>
      </div>
    <?php } ?>     
      <div class="row col-md-12">
    	<div class="col-md-12 ">
        <h4><?php echo _l('Authentication_Info') ?></h4>
        <hr/>
      </div>
              <a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
              <?php echo _l('contract_attachments'); ?>            
              </a>                 
         
              <div id="contract_attachments" class="mtop30">
                 <?php    
                 $href_url = '';
                    $data = '<div class="row">';
                    foreach($request_leave->attachments as $attachment) {
                      $href_url = site_url('modules/timesheets/uploads/requisition_leave/'.$attachment['rel_id'].'/'.$attachment['file_name']).'" download';
                      $data .= '<div class="display-block contract-attachment-wrapper">';
                      $data .= '<div class="col-md-10">';
                      $data .= '<div class="col-md-1">';
                      $data .= '<a class="btn btn-info pull-right display-block" data-file='.$attachment['id'].' data-id='.$attachment['rel_id'].' onclick="preview_asset_btn(this)">';
                      $data .= '<i class="fa fa-eye" ></i>'; 
                      $data .= '</a>';
                      $data .= '</div>';
                      $data .= '<div class=col-md-9>';
                      $data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
                      $data .= '<a href="'.$href_url.'>'.$attachment['file_name'].'</a>';
                      $data .= '<p class="text-muted">'.$attachment["filetype"].'</p>';
                      $data .= '</div>';
                      $data .= '</div>';
                      $data .= '<div class="col-md-2 text-right">';
                      if($attachment['staffid'] == get_staff_user_id() || is_admin()){
                       $data .= '<a href="#" class="text-danger" onclick="delete_requisition_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
                     }
                     $data .= '</div>';
                     $data .= '<div class="clearfix"></div><hr/>';
                     $data .= '</div>';
                    }
                    $data .= '</div>';
                    echo html_entity_decode($data);
                    ?>
                    
              </div>
        </div>
      <div class="col-md-12">
        <h4 class="bold"><?php echo _l('approval_infor'); ?></h4>
        <hr/>

        <div class="project-overview-right">
          <?php 
           if(count($list_approve_status) > 0){ ?>
            
           <div class="row">
             <div class="col-md-12 project-overview-expenses-finance">
              <?php 
                $this->load->model('staff_model');
              foreach ($list_approve_status as $value) { 
                $value['staffid'] = explode(', ',$value['staffid']);
                ?>
              <div class="col-md-6">
                   <p class="text-uppercase text-muted no-mtop bold">
                    <?php
                    $staff_name = '';
                    foreach ($value['staffid'] as $key => $val) {
                      if($staff_name != '')
                      {
                        $staff_name .= ' or ';
                      }
                      $staff_name .= $this->staff_model->get($val)->firstname;
                    }
                    echo html_entity_decode($staff_name); 
                    ?></p>
                   <?php if($value['approve'] == 1){ 
                    ?>
                    <img src="<?php echo site_url(TIMESHEETS_PATH.'approval/approved.png'); ?>" >
                   <?php }elseif($value['approve'] == 2){ ?>
                      <img src="<?php echo site_url(TIMESHEETS_PATH.'approval/rejected.png'); ?>" >
                  <?php } ?> 
                  <br><br>  
                  <p class="bold text-center text-<?php if($value['approve'] == 1){ echo 'success'; }elseif($value['approve'] == 2){ echo 'danger'; } ?>"><?php echo _dt($value['date']); ?></p> 
              </div>
              <?php 
              } ?>
             </div>
          </div>
          <?php } ?>
          </div>
          <div class="pull-left">
              <?php if($request_leave->status == 0 && ($check_approve_status == false || $check_approve_status == 'reject')){ ?>
            <div id="choose_approver">
              <?php if($check != 'choose'){ ?>
              <a data-toggle="tooltip" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-success lead-top-btn lead-view" data-placement="top" href="#" onclick="send_request_approve(<?php echo html_entity_decode($request_leave->id); ?>); return false;"><?php echo _l('send_request_approve'); ?></a>
            <?php } ?>

              <?php if($check == 'choose'){ 
                   $html = '<div class="col-md-12">';
              $html .= '<div class="col-md-9"><select name="approver_c" class="selectpicker" data-live-search="true" id="approver_c" data-width="100%" data-none-selected-text="'. _l('please_choose_approver').'" required> 
                                          <option value=""></option>'; 
              foreach($list_staff as $staff){ 
                  $html .= '<option value="'.$staff['staffid'].'">'.$staff['staff_identifi'].' - '.$staff['firstname'].'</option>';                  
              }
              $html .= '</select></div>';
              
                  $html .= '<div class="col-md-3"><a href="#" onclick="choose_approver();" class="btn btn-success lead-top-btn lead-view" data-loading-text="'._l('wait_text').'">'._l('choose').'</a></div>';
              
              $html .= '</div>';

              echo html_entity_decode($html);
              }
                ?>

              
            </div>
        <?php } 
          if(isset($check_approve_status['staffid'])){
              ?>
              <?php 
          if(in_array(get_staff_user_id(), $check_approve_status['staffid'])){ ?>
              <div class="btn-group" >
                     <a href="#" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo _l('approve'); ?><span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-left">
                      <li>
                        <div class="col-md-12">
                          <?php echo render_textarea('reason', 'reason'); ?>
                        </div>
                      </li>
                      <li>
                          <div class="row bottom-approve">
                            <a href="#" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="approve_request(<?php echo html_entity_decode($request_leave->id); ?>); return false;" class="btn btn-success mright5"><?php echo _l('approve'); ?></a>
                           <a href="#" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="deny_request(<?php echo html_entity_decode($request_leave->id); ?>); return false;" class="btn btn-warning"><?php echo _l('deny'); ?></a></div>
                        </li>
                     </ul>
                  </div>
            <?php }
              ?>
              <?php 
               }
              ?>
              <?php if($rel_type == 'Leave' && $request_leave->status == 1 && is_admin() && date('Y-m-t', strtotime($datecreated)) == date('Y-m-t')){ ?>
                <a href="#" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="cancel_request(<?php echo html_entity_decode($request_leave->id); ?>); return false;" class="btn btn-warning"><?php echo _l('cancel_approval'); ?></a>
              <?php } ?>
            </div>

      </div>   
      </div>

</div>
 </div>
 </div>
 <div id="asset_file_data">
 </div>
 </div>
 </div>
<?php } ?>
<?php init_tail(); ?>
<?php require 'modules/timesheets/assets/js/requisition_detail_js.php';?>
