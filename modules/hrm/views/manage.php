<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <?php if (has_permission('hrm','','create')) { ?>
                                <a href="<?php echo admin_url('hrm/salary/manage'); ?>" class="btn btn-info mright5 test pull-left display-block">
                                    <?php echo _l('add_salary'); ?></a>
                            <?php } ?>

                            <div class="visible-xs">
                                <div class="clearfix"></div>
                            </div>
                        </div>
                        <div class="clearfix mtop20"></div>
                        <a href="#" data-toggle="modal" data-target="#salary_bulk_action" class="bulk-actions-btn table-btn hide" data-table=".table-salary"><?php echo _l('bulk_actions'); ?></a>
                        <div class="modal fade bulk_actions" id="salary_bulk_action" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="checkbox checkbox-danger">
                                            <input type="checkbox" name="mass_delete" id="mass_delete">
                                            <label for="mass_delete"><?php echo _l('mass_delete'); ?></label>
                                        </div>
                                        <hr class="mass_delete_separator" />

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                                        <a href="" class="btn btn-info" onclick="salary_bulk_action(this); return false;"><?php echo _l('confirm'); ?></a>
                                    </div>
                                </div>
                                <!-- /.modal-content -->
                            </div>
                            <!-- /.modal-dialog -->
                        </div>

                        <div class="clearfix mtop20"></div>
                        <?php
                        $table_data = array();
                        $_table_data = array(
                            '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="salaries"><label></label></div>',
                            array(
                                'name'=>_l('staff_member'),
                                'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-project-name')
                            ),
                            array(
                                'name'=>_l('basic_salary'),
                                'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-salary')
                            ),
                            array(
                                'name'=>_l('paid_leaves'),
                                'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-owner')
                            ),
                            array(
                                'name'=>_l('unpaid_leaves'),
                                'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-probability')
                            ),
                            array(
                                'name'=>_l('bonus'),
                                'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-status')
                            ),
                            array(
                                'name'=>_l('total_salary'),
                                'th_attrs'=>array('class'=>'toggleable', 'id'=>'th-delivery-date')
                            ),
                            array(
                                'name'=>_l('created_at'),
                                'th_attrs'=>array('class'=>'toggleable sorting_asc', 'id'=>'th-account',)
                            ),
                        );
                        foreach($_table_data as $_t){
                            array_push($table_data,$_t);
                        }

                        $custom_fields = get_custom_fields('salaries',array('show_on_table'=>1));
                        foreach($custom_fields as $field){
                            array_push($table_data,$field['name']);
                        }

                        $table_data = hooks()->apply_filters('salaries_table_columns', $table_data);

                        render_datatable($table_data,'salaries',[],[
                            'data-last-order-identifier' => 'created_at',
                            'data-default-order'         => get_table_last_order('created_at'),
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        var salaryServerParams = {};
        $.each($('._hidden_inputs._filters input'),function(){
            salaryServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
        });
        salaryServerParams['exclude_inactive'] = '[name="exclude_inactive"]:checked';

        var tAPI = initDataTable('.table-salaries', admin_url+'hrm/salary/table', [0], [0], salaryServerParams,<?php echo hooks()->apply_filters('salary_table_default_order', json_encode(array(2,'asc'))); ?>);
        $('input[name="exclude_inactive"]').on('change',function(){
            tAPI.ajax.reload();
        });
    });
    function salary_bulk_action(event) {

        var r = confirm(app.lang.confirm_action_prompt);
        if (r == false) {
            return false;
        } else {

            var mass_delete = $('#mass_delete').prop('checked');
            var ids = [];
            var data = {};

            data.mass_delete = true;
            var rows = $('.table-salaries').find('tbody tr');
            //console.log(rows);
            $.each(rows, function() {
                var checkbox = $($(this).find('td').eq(0)).find('input');
                if (checkbox.prop('checked') == true) {
                    ids.push(checkbox.val());
                }
            });
            data.ids = ids;
            $(event).addClass('disabled');
            setTimeout(function(){
                $.post(admin_url +'hrm/salary/bulk_action', data).done(function() {

                    window.location.reload();
                });
            },50);
        }
    }
</script>
</body>
</html>
