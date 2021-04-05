    <div class="modal" id="input_method_modal" tabindex="-1" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4>
                   <?php echo _l('check_in'); ?> / <?php echo _l('check_out'); ?>
                </h4>
            </div>
            <div class="modal-body">
                  <div class="row">
                    <div class="col-md-12">
                         <div class="preview-tabs-top">
                          <div class="horizontal-tabs">
                            <ul class="nav nav-tabs mbot15 gen_cart" role="tablist">
                              <li role="presentation" class="active">
                                 <a href="#tab1" class="exits_show" aria-controls="tab1" role="tab" data-toggle="tab" >
                                  <?php echo _l('check_in'); ?>
                                 </a>
                              </li>
                              <li role="presentation">
                                 <a href="#tab2" class="exits_show" aria-controls="tab2" role="tab" data-toggle="tab" >
                                  <?php echo _l('check_out'); ?>
                                 </a>
                              </li>
                            </ul>
                          </div>
                        </div> 
                        <div class="tab-content cart-tab w-100">
                          <div role="tab1" class="tab-pane active" id="tab1">
                                <?php echo form_open(admin_url('timesheets/check_in_ts'),array('id'=>'timesheets-form-check-in')); ?>
                                    <?php if(is_admin()){ ?>
                                        <div class="col-md-12">  
                                            <label for="staff_id" class="control-label"><?php echo _l('staff'); ?></label>
                                            <select id="staff_id" name="staff_id" class="selectpicker" data-width="100%" data-none-selected-text="Non selected" data-live-search="true" tabindex="-98">
                                                    <option></option>
                                                    <?php 
                                                    $CI = &get_instance();
                                                            $CI->load->model('staff_model');
               
                                                            $staffs = $CI->staff_model->get($id);
                                                    ?>
                                                    <?php foreach ($staffs as $key => $value) {  ?>
                                                        <option value="<?php echo html_entity_decode($value['staffid']); ?>"><?php echo html_entity_decode($value['lastname']).' '.$value['firstname']; ?></option>
                                                    <?php } ?>
                                             </select> 
                                            <div class="clearfix"></div>                                      
                                            <br>
                                            <br> 
                                        </div>
                                        <div class="col-md-12">
                                          <?php echo render_datetime_input('date','date',_d(date('Y-m-d H:i:s'))); ?>                                        
                                        </div>
                                        <input type="hidden" name="hours" value="">
                                        <input type="hidden" name="admin" value="<?php echo get_staff_user_id(); ?>">
                                    <?php }else{ ?>
                                        <div class="wrap_clock">
                                        <input type="hidden" name="staff_id" value="<?php echo get_staff_user_id(); ?>">
                                        <span class="date"><?php echo date('Y-m-d'); ?></span>
                                        <span class="hours time_script"></span>
                                        <input type="hidden" name="date" value="<?php echo _d(date('Y-m-d')); ?>">
                                        <input type="hidden" name="hours">
                                        </div>
                                    <?php } ?>
                                        <input type="hidden" name="type_check" value="1">
                                  <div class="bottom_btn">
                                    <button class="btn btn-primary"><?php echo _l('check_in'); ?></button>
                                  </div>
                                <?php echo form_close(); ?>            
                          </div>
                          <div role="tab2" class="tab-pane" id="tab2">
                                <?php echo form_open(admin_url('timesheets/check_out_ts'),array('id'=>'timesheets-form-check-out')); ?>
                                 <?php if(is_admin()){ ?>
                                      <div class="col-md-12">
                                        <label for="staff_id" class="control-label"><?php echo _l('staff'); ?></label>
                                        <select id="staff_id" name="staff_id" class="selectpicker" data-width="100%" data-none-selected-text="Non selected" data-live-search="true" tabindex="-98">
                                          <option></option>
                                          <?php foreach ($staffs as $key => $value) {  ?>
                                              <option value="<?php echo html_entity_decode($value['staffid']); ?>"><?php echo html_entity_decode($value['lastname']).' '.$value['firstname']; ?></option>
                                          <?php } ?>
                                        </select>  
                                      <div class="clearfix"></div>                                      
                                      <br>
                                      <br>
                                      </div>
                                      <div class="col-md-12">
                                        <?php echo render_datetime_input('date','date',_d(date('Y-m-d H:i:s'))); ?>                                        
                                      </div>
                                      <input type="hidden" name="hours" value="">
                                      <input type="hidden" name="admin" value="<?php echo get_staff_user_id(); ?>">
                                  <?php }else{ ?>
                                      <div class="wrap_clock">
                                          <input type="hidden" name="staff_id" value="<?php echo get_staff_user_id(); ?>">
                                          <span class="date"><?php echo date('Y-m-d'); ?></span>
                                          <span class="hours time_script"></span>
                                          <input type="hidden" name="date" value="<?php echo _d(date('Y-m-d')); ?>">
                                          <input type="hidden" name="hours">
                                      </div>
                                  <?php } ?>
                                      <input type="hidden" name="type_check" value="2">
                                <div class="bottom_btn">
                                  <button class="btn btn-primary"><?php echo _l('check_out'); ?></button>
                                </div>
                                <?php echo form_close(); ?>           
                          </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                  </div>
            </div>
            <div class="modal-footer">
                <button type="" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
      </div>
    </div>
