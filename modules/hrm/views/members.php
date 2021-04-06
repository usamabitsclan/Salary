<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
<div class="content">
   <div class="row">
      <?php echo form_open_multipart($this->uri->uri_string(),array('class'=>'staff-form','autocomplete'=>'off')); ?>
      <div class="col-md-<?php if(!isset($member)){echo '8 col-md-offset-2';} else {echo '12';} ?>" id="small-table">
         <div class="panel_s">
            <div class="panel-body">
               <ul class="nav nav-tabs" role="tablist">
                <?php if(isset($member)) { ?>
                  <li role="presentation" class="active">
                     <a href="#tab_staff_profile" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">
                     <span class="glyphicon glyphicon-user"></span>&nbsp;<?php echo _l('staff_profile_string'); ?>
                     </a>
                  </li>
                  <?php if(is_admin()){ ?>
                  <li role="presentation">
                     <a href="#staff_permissions" aria-controls="staff_permissions" role="tab" data-toggle="tab">
                     <span class="glyphicon glyphicon-lock"></span>&nbsp;<?php echo _l('staff_add_edit_permissions'); ?>
                     </a>
                  </li>
                <?php } ?>

                  <li role="presentation">
                     <a href="#staff_contract" aria-controls="staff_contract" role="tab" data-toggle="tab">
                     <span class="glyphicon glyphicon-file"></span>&nbsp;<?php echo _l('staff_contract'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#insurrance" aria-controls="insurrance" role="tab" data-toggle="tab">
                     <i class="fa fa-medkit hrm-fontsize15"></i>&nbsp;<?php echo _l('insurrance'); ?>
                     </a>
                  </li>


                  <li role="presentation">
                     <a href="#attachments" aria-controls="attachments" role="tab" data-toggle="tab">
                     <span class="fa fa-link"></span>&nbsp;<?php echo _l('attachments'); ?>
                     </a>
                  </li>
                  
                <?php } else { ?>
                  <li role="presentation" class="active">
                     <a href="#tab_staff_profile" aria-controls="tab_staff_profile" role="tab" data-toggle="tab">
                     <?php echo _l('staff_profile_string'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#staff_permissions" aria-controls="staff_permissions" role="tab" data-toggle="tab">
                     <?php echo _l('staff_add_edit_permissions'); ?>
                     </a>
                  </li>
               <?php } ?>
               </ul>
               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="tab_staff_profile">
                     <div class="row">
                     <?php $value = (isset($member) ? $member->firstname : ''); ?>
                     <?php $attrs = (isset($member) ? array() : array('autofocus'=>true)); ?>
                         <div class="col-md-4">
                          <?php  $hr_codes = (isset($member) ? $member->staff_identifi : ''); ?>
                          <div class="form-group" app-field-wrapper="staff_identifi">
                            <label for="staff_identifi" class="control-label"><?php echo _l('hr_code'); ?></label>

                            <div class="input-group">
                            <span class="input-group-addon">
                               <?php echo htmlspecialchars('BC21'); ?>
                               </span>
                            <input type="text" id="staff_identifi" name="staff_identifi" class="form-control" value="<?php echo htmlspecialchars($hr_codes); ?>" aria-invalid="false" <?php if(!is_admin() && !hrm_permissions('hrm','', 'edit')){ echo 'disabled' ; }  ?>>

                               </div>
                          </div>
                         </div>   
                         <div class="col-md-4">
                         <?php echo render_input('firstname','full_name',$value,'text',$attrs); ?>
                         </div>
                         <div class="col-md-4">
                              <label for="sex" class="control-label"><?php echo _l('sex'); ?></label>
                        <select name="sex" class="selectpicker" id="sex" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"> 
                           <option value=""></option>                  
                           <option value="<?php echo 'male'; ?>" <?php if(isset($member) && $member->sex == 'male'){echo 'selected';} ?>><?php echo _l('male'); ?></option>
                           <option value="<?php echo 'female'; ?>" <?php if(isset($member) && $member->sex == 'female'){echo 'selected';} ?>><?php echo _l('female'); ?></option>
                        </select>
                        </div>

                     </div>                      
                     <div class="row">
                        <div class="col-md-4">
                             <label for="marital_status" class="control-label"><?php echo _l('marital_status'); ?></label>
                        <select name="marital_status" class="selectpicker" id="marital_status" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"> 
                           <option value=""></option>                  
                           <option value="<?php echo 'single'; ?>" <?php if(isset($member) && $member->marital_status == 'single'){echo 'selected';} ?>><?php echo _l('single'); ?></option>
                           <option value="<?php echo 'married'; ?>" <?php if(isset($member) && $member->marital_status == 'married'){echo 'selected';} ?>><?php echo _l('married'); ?></option>
                        </select>
                        </div>
                        <div class="col-md-4">
                            <?php 
                             $religion = (isset($member) ? $member->religion : '');
                            echo render_input('religion','religion',$religion,'text'); ?>
                        </div>
                        <div class="col-md-4">
                            <?php 
                            $current_address = (isset($member) ? $member->current_address : '');
                            echo render_input('current_address','current_address',$current_address,'text'); ?>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-4">
                           <?php $value = (isset($member) ? $member->designation : ''); ?>
                          <?php echo render_input('designation','staff_designation',$value); ?>
                        </div>
                        <div class="col-md-4">
                          <?php $value = (isset($member) ? $member->phonenumber : ''); ?>
                          <?php echo render_input('phonenumber','staff_add_edit_phonenumber',$value); ?>
                        </div>

                      <div class="col-md-4">
                        <?php $value = (isset($member) ? $member->email : ''); ?>
                        <div class="form-group" app-field-wrapper="email">
                          <label for="email" class="control-label">Email</label>
                          <input type="email" id="email" name="email" class="form-control" autocomplete="off" value="<?php echo htmlspecialchars($value); ?>" <?php if(!is_admin() && !hrm_permissions('hrm','', 'edit')){ echo 'disabled' ;}  ?>>
                        </div>

                      </div>

                     </div>
                     <div class="row">
                        <div class="col-md-4">
                            <?php
                            $account_number = (isset($member) ? $member->account_number : '');
                            echo render_input('account_number','bank_account_number',$account_number, 'text'); ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $name_account = (isset($member) ? $member->name_account : '');
                            echo render_input('name_account','name_account',$name_account, 'text'); ?>
                        </div>
                        <div class="col-md-4">
                            <?php
                            $issue_bank = (isset($member) ? $member->issue_bank : '');
                            echo render_input('issue_bank','bank_name',$issue_bank, 'text'); ?>
                        </div>
                     </div>

                     <div class="row">
                      <div class="col-md-4">
                       <div class="form-group">
                          <label for="facebook" class="control-label"><i class="fa fa-facebook"></i> <?php echo _l('staff_add_edit_facebook'); ?></label>
                          <input type="text" class="form-control" name="facebook" value="<?php if(isset($member)){echo htmlspecialchars($member->facebook);} ?>">
                       </div>
                       </div>
                      <div class="col-md-4">
                       <div class="form-group">
                          <label for="linkedin" class="control-label"><i class="fa fa-linkedin"></i> <?php echo _l('staff_add_edit_linkedin'); ?></label>
                          <input type="text" class="form-control" name="linkedin" value="<?php if(isset($member)){echo htmlspecialchars($member->linkedin);} ?>">
                       </div>
                       </div>
                      <div class="col-md-4">
                       <div class="form-group">
                          <label for="skype" class="control-label"><i class="fa fa-skype"></i> <?php echo _l('staff_add_edit_skype'); ?></label>
                          <input type="text" class="form-control" name="skype" value="<?php if(isset($member)){echo htmlspecialchars($member->skype);} ?>">
                       </div>
                       </div>
                     </div>
                     <div class="row">
                      <div class="col-md-12">
                         <?php
                            $basic_salary = (isset($member) ? $member->salary : '');
                            echo render_input('salary','basic_salary',$basic_salary, 'number'); ?>
                      </div>
                     </div>
                     <?php $rel_id = (isset($member) ? $member->staffid : false); ?>
                     <?php echo render_custom_fields('staff',$rel_id); ?>


                     <?php if(!isset($member) || is_admin() || !is_admin() && $member->admin == 0) { ?>
                     <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                     <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1"/>
                     <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>
                     <div class="clearfix form-group"></div>
                     <label for="password" class="control-label"><?php echo _l('staff_add_edit_password'); ?></label>
                     <div class="input-group">
                        <input type="password" class="form-control password" name="password" autocomplete="off">
                        <span class="input-group-addon">
                        <a href="#password" class="show_password" onclick="showPassword('password'); return false;"><i class="fa fa-eye"></i></a>
                        </span>
                        <span class="input-group-addon">
                        <a href="#" class="generate_password" onclick="generatePassword(this);return false;"><i class="fa fa-refresh"></i></a>
                        </span>
                     </div>
                     <?php if(isset($member)){ ?>
                     <p class="text-muted"><?php echo _l('staff_add_edit_password_note'); ?></p>
                     <?php if($member->last_password_change != NULL){ ?>
                     <?php echo _l('staff_add_edit_password_last_changed'); ?>:
                     <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($member->last_password_change); ?>">
                        <?php echo time_ago($member->last_password_change); ?>
                     </span>
                     <?php } } ?>
                  <?php } ?>
                  <div class="text-right btn-toolbar-container-out">
				  <br>
                     <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                  </div><br>

                  </div>
                  <div role="tabpanel" class="tab-pane" id="staff_permissions">
                     <?php
                        hooks()->do_action('staff_render_permissions');
                        $selected = '';
                        foreach($roles as $role){
                         if(isset($member)){
                          if($member->role == $role['roleid']){
                           $selected = $role['roleid'];
                         }
                        } else {
                        $default_staff_role = get_option('default_staff_role');
                        if($default_staff_role == $role['roleid'] ){
                         $selected = $role['roleid'];
                        }
                        }
                        }
                        ?>
                     <?php echo render_select('role',$roles,array('roleid','name'),'staff_add_edit_role',$selected); ?>
                     <hr />
                     <h4 class="font-medium mbot15 bold"><?php echo _l('staff_add_edit_permissions'); ?></h4>
                     <?php
                     $permissionsData = [ 'funcData' => ['staff_id'=> isset($member) ? $member->staffid : null ] ];
                     if(isset($member)) {
                        $permissionsData['member'] = $member;
                     }
                     $this->load->view('admin/staff/permissions', $permissionsData);
                     ?>
                  <?php if (is_admin()) {  ?>
                  <div class=" text-right btn-toolbar-container-out">
                     <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                  </div>
                <?php } ?>
                  </div>
      <?php echo form_close(); ?>



                <?php if(isset($member)) { ?>
                   <div role="tabpanel" class="tab-pane" id="staff_contract">
                  
                     <?php
                        $table_data = array(
                            _l('id'),
                            _l('contract_code'),
                            _l('name_contract'),
                            _l('staff'),

                            _l('start_valid'),
                            _l('end_valid'),
                            _l('contract_status'),
                            );
                        $custom_fields = get_custom_fields('staff',array('show_on_table'=>1));
                        foreach($custom_fields as $field){
                            array_push($table_data,$field['name']);
                        }
                        render_datatable($table_data,'table_contract');
                        ?>
                   </div>

                   <div role="tabpanel" class="tab-pane" id="insurrance">
                      <div class="insurance-info">
                        <div class="row publib-insurance-title">
                             <div class="col-md-8">
                                <h4>
                                    <span class="publib-infor"><?php echo _l('insurance_info') ?></span>
                                </h4>
                             </div>
                         </div>
                      <hr class="hr-panel-heading">

                        <div class="col-md-12">
                      <div class="row">
                          <?php $value = (isset($contracts) ? $contracts[0]['name_contract'] : ''); ?>
                          <?php $attrs = (isset($contracts) ? array() : array('autofocus'=>true)); ?>

                        <div class="col-md-6">

                            <?php $insurance_book_num = isset($insurances[0]) ? $insurances[0]['insurance_book_num'] : '' ?>  

                            <div class="form-group" app-field-wrapper="contract_code">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4 class="contract_info bold"><?php echo _l('insurance_book_number') ?>:</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="contract_info"><?php echo htmlspecialchars($insurance_book_num); ?></h4>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6">
                          <?php $health_insurance_num = isset($insurances[0]) ? $insurances[0]['health_insurance_num'] : '' ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="contract_info bold"><?php echo _l('health_insurance_number') ?>:</h4>
                                </div>
                                <div class="col-md-6">
                                        <h4 class="contract_info"><?php echo htmlspecialchars($health_insurance_num); ?></h4>
                                </div>
                            </div>
                        </div>
                      </div>
          
                      <div class="row">
                        <div class="col-md-6">
                          <?php $city_code = isset($insurances[0]) ? $insurances[0]['city_code'] : '' ?>
                           <div class="row">
                                <div class="col-md-6">
                                    <h4  class="contract_info bold"><?php echo _l('province_city_id') ?>:</h4>
                                </div>
                                <div class="col-md-6">
                                        <h4 class="contract_info"><?php echo htmlspecialchars($city_code); ?></h4>
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6">
                          <?php $registration_medical = isset($insurances[0]) ? $insurances[0]['registration_medical'] : '' ?>
                          <div class="row">
                                <div class="col-md-6">
                                    <h4  class="contract_info bold"><?php echo _l('registration_medical_care') ?>:</h4>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="contract_info"><?php echo htmlspecialchars($registration_medical); ?></h4>
                                </div>
                            </div>
    
                        </div>
                      </div>
                    
                    
                    </div>

                    </div>

                   </div>


                   <div role="tabpanel" class="tab-pane" id="timekeeping">
                    <?php  echo _l('timekeeping') ?>
                   </div>



                  


                   <div role="tabpanel" class="tab-pane" id="attachments">

                      <?php echo form_open_multipart('admin/hrm/upload_file',array('id'=>'hrm_attachment','class'=>'dropzone')); ?>
                      <?php echo form_close(); ?>   

                    <div>
                       <div id="contract_attachments" class="mtop30 col-md-8 col-md-offset-2">
                           <?php
                              $data = '<div class="row" id="attachment_file">';
                              foreach($hrm_staff as $attachment) {
                                $href_url = site_url('modules/hrm/uploads/'.$attachment['rel_id'].'/'.$attachment['file_name']).'"';
                                if(!empty($attachment['external'])){
                                  $href_url = $attachment['external_link'];
                                }
                                $data .= '<div class="display-block contract-attachment-wrapper">';
                                $data .= '<div class="col-md-10">';
                                $data .= '<div class="col-md-2">';

								if (strpos($href_url, '.jpg') !== false || strpos($href_url, '.jpeg') !== false || strpos($href_url, '.png') !== false) {
									$data .= '<a target="_blank" name="preview-btn" href="'.$href_url.'" data-lightbox="attachment"';
									$data .= 'rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'._l("preview_file").'">';
								    $data .= '<i class="fa fa-eye"></i>'; 
									$data .= '</a>'; 
									$data .= '<a target="_blank" name="preview-btn" href="'.$href_url.'"';
									$data .= 'rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-right" data-toggle="tooltip" title data-original-title="'._l("download_file").'">';
								    $data .= '<i class="fa fa-download"></i>'; 
									$data .= '</a>';
									
								} elseif ( strpos($href_url, '.doc') || strpos($href_url, '.xls') ) {
									$data .= '<a target="_blank" name="preview-btn" href="https://view.officeapps.live.com/op/embed.aspx?src='.$href_url.'"';
									$data .= 'rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'._l("preview_file").'">';
								    $data .= '<i class="fa fa-eye"></i>'; 
									$data .= '</a>'; 
									$data .= '<a target="_blank" name="preview-btn" href="'.$href_url.'"';
									$data .= 'rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-right" data-toggle="tooltip" title data-original-title="'._l("download_file").'">';
								    $data .= '<i class="fa fa-download"></i>'; 
									$data .= '</a>'; 
								} 
								
								else {
									$data .= '<a target="_blank" name="preview-btn" href="'.$href_url.'"';
									$data .= 'rel_id = "'.$attachment['rel_id'].'" id = "'.$attachment['id'].'" href="Javascript:void(0);" class="mbot10 btn btn-success pull-left" data-toggle="tooltip" title data-original-title="'._l("download_file").'">';
								    $data .= '<i class="fa fa-download"></i>'; 
									$data .= '</a>'; 
								}
								
                                $data .= '</div>';
                                $data .= '<div class=col-md-9>';
                                $data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
                                $data .= ''.$attachment['file_name'].'';
                                $data .= '<p class="text-muted">'.$attachment["filetype"].'</p>';
                                $data .= '</div>';
                                $data .= '</div>';
                                $data .= '<div class="col-md-2 text-right">';
                                if($attachment['staffid'] == get_staff_user_id() || is_admin() || hrm_permissions('hrm', '', 'edit')){
                                 $data .= '<a href="#" class="text-danger" onclick="delete_contract_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
                               }
                               $data .= '</div>';
                               $data .= '<div class="clearfix"></div><hr/>';
                               $data .= '</div>';
                              }
                              $data .= '</div>';
                              echo ''.$data;
                              ?>
                              
                        </div>
                   </div>
                     <div id="contract_file_data"></div>
                   </div>



              <?php } ?>

               </div>
            </div>
         </div>
      </div>

   </div>
   <div class="btn-bottom-pusher"></div>
</div>
<?php init_tail(); ?>


<script>
    //unable all checkbox
    $("#checkbox_record *").prop('disabled',true);
    $("#add_records_received").hide();
    $("#cacel_records_received").hide();


   $(function() {
     var ContractsServerParams = {
      "memberid": "[name='memberid']",
     };
      table_contract = $('table.table-table_contract');
     initDataTable(table_contract, admin_url+'hrm/table_contract', undefined, undefined, ContractsServerParams,<?php echo hooks()->apply_filters('contracts_table_default_order', json_encode(array(6,'asc'))); ?>);
       $('select[name="role"]').on('change', function() {
           var roleid = $(this).val();
           init_roles_permissions(roleid, true);
       });

       $('input[name="administrator"]').on('change', function() {
           var checked = $(this).prop('checked');
           var isNotStaffMember = $('.is-not-staff');
           if (checked == true) {
               isNotStaffMember.addClass('hide');
               $('.roles').find('input').prop('disabled', true).prop('checked', false);
           } else {
               isNotStaffMember.removeClass('hide');
               isNotStaffMember.find('input').prop('checked', false);
               $('.roles').find('.capability').not('[data-not-applicable="true"]').prop('disabled', false)
           }
       });

       $('#is_not_staff').on('change', function() {
           var checked = $(this).prop('checked');
           var row_permission_leads = $('tr[data-name="leads"]');
           if (checked == true) {
               row_permission_leads.addClass('hide');
               row_permission_leads.find('input').prop('checked', false);
           } else {
               row_permission_leads.removeClass('hide');
           }
       });

       init_roles_permissions();

       appValidateForm($('.staff-form'), {
           firstname: 'required',
           lastname: 'required',
           username: 'required',
           designation: 'required',
           password: {
               required: {
                   depends: function(element) {
                       return ($('input[name="isedit"]').length == 0) ? true : false
                   }
               }
           },
           email: {
               required: true,
               email: true,
               remote: {
                   url: site_url + "admin/misc/staff_email_exists",
                   type: 'post',
                   data: {
                       email: function() {
                           return $('input[name="email"]').val();
                       },
                       memberid: function() {
                           return $('input[name="memberid"]').val();
                       }
                   }
               }
           },
           staff_identifi: {
               required: true,
               remote: {
                url: site_url + "admin/hrm/hr_code_exists",
                type: 'post',
                data: {
                    staff_identifi: function() {
                        return $('input[name="staff_identifi"]').val();
                    },
                    memberid: function() {
                        return $('input[name="memberid"]').val();
                    }
                }
            }
           }
       });
   });

   $("#update_records_received").on('click', function () {
      $("#add_records_received").show();
      $("#cacel_records_received").show();
      $("#update_records_received").hide();
      $("#checkbox_record *").prop('disabled',false);

   })

   $("#cacel_records_received").on('click', function () {
      location.reload();
      
   })

   $('#add_records_received').on('click', function () {
    if($('#records_received').change()){
     var formData = new FormData();
    formData.append("csrf_token_name", $('input[name="csrf_token_name"]').val());
    formData.append("staffid", $('input[name="memberid"]').val());
    var data = [];
    var data_record = {};
   $("input[name ='records_received']").each(function(){ if(this.checked == true){
        var datakey = $(this).val();
        var value   = $(this).val();
        data.push({datakey, value});

       }else{
        var datakey = $(this).val();
        var value   = '';
        data.push({datakey, value});
       
        }
        JSONString = JSON.stringify(data);
        formData.append("dt_record", JSONString );
  });
    
    $.ajax({ 
      url: admin_url + 'hrm/records_received', 
      method: 'post', 
      data: formData, 
      contentType: false, 
      processData: false
      
    }).done(function(response) {
    response = JSON.parse(response);
      if(response.message == 'Add records received success'){
        alert_float('success', response.message);
      }else{
        alert_float('warning', response.message);
      }
      $("#add_records_received").hide();
      $("#cacel_records_received").hide();
      $("#update_records_received").show();
      $("#checkbox_record *").prop('disabled',true);

    });
  }
   });

   //hrmattachment

   if (typeof (hrmAttachmentDropzone) != 'undefined') {
      hrmAttachmentDropzone.destroy();
      hrmAttachmentDropzone = null;
   }
   Dropzone.autoDiscover = false;
  if($('#hrm_attachment').length){
   hrmAttachmentDropzone = new Dropzone("#hrm_attachment", appCreateDropzoneOptions({
      uploadMultiple: true,
      parallelUploads: 20,
      maxFiles: 20,
      paramName: 'file',
      sending: function (file, xhr, formData) {
         formData.append("staffid", $('input[name="memberid"]').val());
      },
      success: function (files, response) {
         response = JSON.parse(response);
         alert_float('success', response.message);
         var html ='';
         var data = response.data;
         if(data){
          $("#attachment_file").empty();
          $("#attachment_file").append(data);

         }
         if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
         }
      }
   }));
 }



</script>
</body>
</html>
