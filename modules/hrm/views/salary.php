<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="modal-title" id="myModalLabel">
                            <?php
                            if(isset($data)){?>
                                <span class="edit-title"><?php echo _l('Edit Your Salary'); ?></span>
                            <?php }else{?>
                                <span class="edit-title"><strong><?php echo _l('Generate Your Salary'); ?></strong></span>
                            <?php }?>


                        </h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <div class="clearfix"></div>
                        <div id="car_modulee">

                        <?php
                        if(isset($data))
                            echo form_open_multipart('hrm/salary/manage/'.$data->id,array('id'=>'salaries'));
                        else
                            echo form_open_multipart('hrm/salary/manage',array('id'=>'salaries'));

                        ?>


                        <div class="row">
                            <div class="col-md-12">
                              <?php
                                 $i = 0;
                                 $selected = '';
                                 foreach($staff as $member){
                                  if(isset($data)){
                                    if($data->staff_member == $member['staffid']) {
                                      $selected = $member['staffid'];
                                    }
                                  }
                                  $i++;
                                 }
                                 echo render_select('staff_member',$staff,array('staffid',array('firstname','lastname')),'staff_member',$selected);
                                 ?>
                           </div>
                        </div>


                        <div class="row">

                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->designation : '';
                                ?>
                                <?php echo render_input('designation','Designation',$value); ?>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->bank_name : '';
                                ?>
                                <?php echo render_input('bank_name','Bank Name',$value); ?>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->bank_account_title : '';
                                ?>
                                <?php echo render_input('bank_account_title','Bank Account Title.',$value); ?>
                            </div>
                        </div>                        
                        <div class="row">

                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->bank_account_no : '';
                                ?>
                                <?php echo render_input('bank_account_no','Bank Account No.',$value); ?>
                            </div>
                            <div class="col-md-12">
                                <?php
                                $value =  isset($data) ? $data->id : '';
                                ?>

                                <?php echo form_hidden('id',$value); ?>

                                <?php
                                $value = isset($data) ? $data->basic_salary : 0;
                                ?>
                                <?php echo render_input('basic_salary','Basic Salary',$value,'number'); ?>

                            </div>
                        </div>



                        <div class="row">

                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->paid_leaves : 0;
                                ?>
                                <?php echo render_input('paid_leaves','Paid Leaves',$value,'number'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->unpaid_leaves : 0;
                                ?>
                                <?php echo render_input('unpaid_leaves','UnPaid Leaves',$value,'number'); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                $value = isset($data) ? $data->bonus : 0;
                                ?>
                                <?php echo render_input('bonus','Bonus',$value,'number'); ?>
                            </div>
                        </div>

                        <?php if(isset($data)){?>
                            <div class="">
                                <button type="submit" class="btn btn-info pull-right"><?php echo _l('Update'); ?></button>
                            </div>

                            <?php }else{?>

                            <div class="">
                                <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                            </div>
                            <?php }?>
                               <?php echo form_close(); ?>
                       </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    window.addEventListener('load',function(){
        appValidateForm($('#salaries'), {
            staff_member: 'required | ',
            basic_salary: 'required',
            paid_leaves: 'required',
            unpaid_leaves: 'required',
        });


    });

    function manage_car(form) {
        //alert("hereerer");
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                if($.fn.DataTable.isDataTable('.table-salaries')){
                    $('.table-salaries').DataTable().ajax.reload();
                }
                alert_float('success', response.message);
            }

        });
        return false;
    }
</script>
</body>
</html>


