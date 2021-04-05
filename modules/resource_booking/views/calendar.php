<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="panel_s">
					<div class="panel-body rbautooverflow">
						<div class="dt-loader hide"></div>
						<div id="calendars"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade _event" id="newEventModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('new_booking'); ?></h4>
      </div>
      <?php  echo form_open(admin_url('resource_booking/add_edit_booking'),array('id'=>'add_edit_booking-form')); ?>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <?php echo render_input('purpose','purpose'); ?>
            <?php echo form_hidden('orderer',get_staff_user_id()); ?>
            <div class="row">
            <div class="col-md-6">
              <label for="resource_group"><?php echo _l('resource_group'); ?></label>
              <select name="resource_group" id="resource_group" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                <option value=""></option>
                <?php foreach($resource_group as $rg){ ?>
                <option value="<?php echo htmlspecialchars($rg['id']); ?>" <?php if(isset($booking) && $booking->resource_group =$rg['id'] ){echo 'selected';} ?>><?php echo htmlspecialchars($rg['group_name']); ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-6">
               <label for="resource"><?php echo _l('resource'); ?></label>
              <select name="resource" id="resource" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
                <option value=""></option>
                <?php foreach($resources as $rs){ ?>
                <option value="<?php echo htmlspecialchars($rs['id']); ?>"<?php if(isset($booking) && $booking->resource = $rs['id'] ){echo 'selected';} ?>><?php echo htmlspecialchars($rs['resource_name']); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          
          <div class="clearfix mtop15"></div>
            <?php echo render_datetime_input('start_time','start_time'); ?>
            <div class="clearfix mtop15"></div>
            <?php echo render_datetime_input('end_time','end_time'); ?>
            <div class="clearfix mtop15"></div>
            <div class="row">
	          <div class="col-md-12">
	          <label for="follower"><?php echo _l('follower'); ?></label>
	          <select name="follower[]" id="follower" class="selectpicker" multiple="true" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('ticket_settings_none_assigned'); ?>">
	            <?php foreach($staff as $s) { ?>
	              <option value="<?php echo htmlspecialchars($s['staffid']); ?>"><?php echo htmlspecialchars($s['firstname']); ?></option>
	              <?php } ?>
	          </select>
	          </div>
          </div>
          <div class="clearfix mtop15"></div>
            <?php echo render_textarea('description','event_description','',array('rows'=>5)); ?>
          <div class="clearfix mtop15"></div>
          <div class="notification danger"></div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
    <button id="sm_btn" onclick="check_resource_booking(); return false;" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
  </div>
  <?php echo form_close(); ?>
</div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<?php init_tail(); ?>

<script src="<?php echo module_dir_url('resource_booking','assets/js/calendar-primary.js'); ?>"></script>
<script>
function check_resource_booking(){
  resource = $('#resource').val();
  start_time = $('#start_time').val();
  end_time = $('#end_time').val();
  $.post(admin_url+'resource_booking/check_resource_booking/'+resource+'/'+start_time+'/'+end_time).done(function(response){
     response = JSON.parse(response);
     if(response.check == true){
        $("#add_edit_booking-form").on('submit');
        $('#newEventModal').modal('hide');
        location.reload();
     }else{
        $('.notification').html('');
        $('.notification').append('<label class="danger"><?php echo _l('notification_check_resource_booking'); ?></label');
     }
  });
}
</script>
<script src="<?php echo module_dir_url('resource_booking','assets/js/calendar-secondary.js'); ?>"></script>
</body>
</html>
