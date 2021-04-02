<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="modal-title" id="myModalLabel">
                                <?php
                                if(isset($data)){?>
                                    <span class="edit-title"><?php echo _l('opportunity_edit'); ?></span>
                                <?php }else{?>
                                    <span class="edit-title"><?php echo _l('opportunity_create'); ?></span>
                                <?php }?>


                            </h4>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                            <div class="clearfix"></div>
                            <div id="">

                                <?php
                                if(isset($opportunity))
                                    echo form_open('opportunity/opportunities/'.$opportunity->id,array('id'=>'opportunity'));
                                else
                                    echo form_open('opportunity/opportunities',array('id'=>'opportunity'));

                                ?>


                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = isset($opportunity) ? $opportunity->project_name : ''; ?>
                                        <?php echo render_input('project_name','opportunity_project_name',$value); ?>

                                        <label for="client_contracts" class="control-label"><?php echo _l('opportunity_client_contract'); ?></label>
                                        <select name="client_contracts[]" id="client_contracts" class="selectpicker" data-width="100%" multiple="true" data-none-selected-text="<?php echo _l('opportunity_select_customer'); ?>">
                                            <?php if( isset($contact) ){?>
                                                <?php foreach($contact as $contact){?>
                                                    <option value="<?php echo $contact['id']; ?>"<?php ?><?php  if (in_array( $contact['id'],$client)){echo 'selected';}  ?>>
                                                        <?php  echo ($contact['firstname']); ?>
                                                    </option>
                                                <?php } ?>
                                            <?php } ?>

                                        </select>


                                        <?php $value = isset($opportunity) ? $opportunity->delivery_date : ''; ?>
                                        <?php echo render_date_input('delivery_date','opportunity_delivery_date',$value,array('data-date-min-date' => _d(date('Y-m-d')))); ?>

                                        <label for="probability" class="control-label"><?php echo _l('opportunity_probability'); ?></label>
                                        <select name="probability" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('opportunity_no_status'); ?>">
                                            <option value="" selected><?php echo _l('opportunity_no_status'); ?></option>
                                            <option value="10"
                                                <?php if(isset($opportunity)){ if($opportunity->probability == '10'){ echo 'selected'; }} ?>>
                                                <?php echo _l('probability_ten'); ?>%</option>
                                            <option value="25"
                                                <?php if(isset($opportunity)){if($opportunity->probability == '25'){echo 'selected';}} ?>>
                                                <?php echo _l('probability_twentyfive'); ?>%</option>
                                            <option value="50"
                                                <?php if(isset($opportunity)){if($opportunity->probability == '50'){echo 'selected';}} ?>>
                                                <?php echo _l('probability_fifty'); ?>%</option>
                                            <option value="60"
                                                <?php if(isset($opportunity)){if($opportunity->probability == '60'){echo 'selected';}} ?>>
                                                <?php echo _l('probability_sixty'); ?>%</option>
                                            <option value="75"
                                                <?php if(isset($opportunity)){if($opportunity->probability == '75'){echo 'selected';}} ?>>
                                                <?php echo _l('probability_seventyfive'); ?>%</option>
                                            <option value="80"
                                                <?php if(isset($opportunity)){if($opportunity->probability == '80'){echo 'selected';}} ?>>
                                                <?php echo _l('probability_eighty'); ?>%</option>
                                            <option value="90"
                                                <?php if(isset($opportunity)){if($opportunity->probability == '90'){echo 'selected';}} ?>>
                                                <?php echo _l('probability_ninety'); ?>%</option>
                                        </select>

                                    </div>
                                    <div class="col-md-6">

                                        <?php $value = isset($opportunity) ? $opportunity->account : ''; ?>
                                        <?php echo render_select('account',$customer,array('userid',array('company')),'Account',$value); ?>

                                        <label for="owner" class="control-label"><?php echo _l('opportunity_owner'); ?></label>
                                        <select class="selectpicker" data-none-selected-text="<?php echo _l('opportunity_no_status'); ?>" name="owner" data-width="100%">
                                            <option value="" selected><?php echo _l('opportunity_no_status'); ?></option>
                                            <?php foreach($staff as $staff){?>
                                                <option value="<?php echo $staff['staffid']; ?>"<?php ?><?php if(isset($owner)){ if($owner->staffid == $staff['staffid']){ echo 'selected'; }} ?>>
                                                    <?php echo $staff['firstname'] ; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <?php $value = isset($opportunity) ? $opportunity->projected_sale_date : ''; ?>
                                        <?php echo render_date_input('projected_sale_date','opportunity_projected_sale_date',$value,array('data-date-min-date' => _d(date('Y-m-d')))); ?>
                                        <div id="all_status">
                                            <label for="status" class="control-label"><?php echo _l('opportunity_status'); ?></label>
                                            <select name="status"  id="status" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('opportunity_no_status'); ?>">
                                                <option value="" selected><?php echo _l('opportunity_no_status'); ?></option>
                                                <option value="prospecting"
                                                    <?php if(isset($opportunity)){ if($opportunity->status == 'prospecting'){ echo 'selected'; }} ?>>
                                                    <?php echo _l('status_prospecting'); ?></option>
                                                <option value="proposal sent"
                                                    <?php if(isset($opportunity)){if($opportunity->status == 'proposal sent'){echo 'selected';}} ?>>
                                                    <?php echo _l('status_proposal_sent'); ?></option>
                                                <option value="negotiating"
                                                    <?php if(isset($opportunity)){if($opportunity->status == 'negotiating'){echo 'selected';}} ?>>
                                                    <?php echo _l('status_negotiating'); ?></option>
                                                <option value="investigating"
                                                    <?php if(isset($opportunity)){if($opportunity->status == 'investigating'){echo 'selected';}} ?>>
                                                    <?php echo _l('status_investigating'); ?></option>
                                                <option value="closed"
                                                    <?php if(isset($opportunity)){if($opportunity->status == 'closed'){echo 'selected';}} ?>>
                                                    <?php echo _l('status_closed'); ?></option>
                                            </select>
                                        </div>
                                        <div id="closed_status">
                                            <select name="close_status"  id="close_status" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('opportunity_no_status'); ?>">
                                                <option value="" selected><?php echo _l('opportunity_no_status'); ?></option>
                                                <option value="1"
                                                    <?php if(isset($opportunity)){ if($opportunity->close_status == '1'){ echo 'selected'; }} ?>>
                                                    <?php echo _l('status_lost'); ?></option>
                                                <option value="2"
                                                    <?php if(isset($opportunity)){if($opportunity->close_status == '2'){echo 'selected';}} ?>>
                                                    <?php echo _l('status_won'); ?></option>
                                                <option value="3"
                                                    <?php if(isset($opportunity)){if($opportunity->close_status == '3'){echo 'selected';}} ?>>
                                                    <?php echo _l('status_dead'); ?></option>
                                            </select>
                                        </div>
                                    </div>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>
                <!-- <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
                    <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                </div> -->

                <?php echo form_close(); ?>
            </div>

<?php //$this->load->view('admin/clients/client_group'); ?>
