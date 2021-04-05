<script>
(function(){
  "use strict";

  $('#reject_btn').on('click',function(){
    $('#approved_div').addClass('hide');
  });
  $('#approved_btn').on('click',function(){
    $('#reject_div').addClass('hide');
  });

  var data_send_mail = {};
  <?php if(isset($send_mail_approve)){ 
    ?>
    data_send_mail = <?php echo json_encode($send_mail_approve); ?>;
    data_send_mail.rel_id = <?php echo html_entity_decode($request_leave->id); ?>;
    data_send_mail.rel_type = '<?php echo html_entity_decode($rel_type); ?>';
    data_send_mail.addedfrom = <?php echo html_entity_decode($request_leave->staff_id); ?>;
    $.post(admin_url+'timesheets/send_mail', data_send_mail).done(function(response){
    });

    if("<?php echo html_entity_decode($rel_type); ?>" == "Leave"){

        $.post(admin_url+'timesheets/send_notifi_handover_recipients', data_send_mail).done(function(response){
        });

         $.post(admin_url+'timesheets/send_notification_recipient', data_send_mail).done(function(response){
        });
    }

  <?php } ?>

  $("input[data-type='currency']").on({
    keyup: function() {        
      formatCurrency($(this));
    },
    blur: function() { 
      formatCurrency($(this), "blur");
    }
  });
})(jQuery);

function new_requisition_detail(){
  "use strict";
          $('#requisition_detail_m').modal('show');
      }
function preview_asset_btn(invoker){
  "use strict";
    var id = $(invoker).data('id');
    var rel_id = $(invoker).data('file');
    view_isn_file(id, rel_id);
}

function view_isn_file(id, rel_id) {
  "use strict";
      $('#asset_file_data').empty();
      $("#asset_file_data").load(admin_url + 'timesheets/file_view_requisition/' + rel_id +'/' + id, function(response, status, xhr) {
          if (status == "error") {
              alert_float('danger', xhr.statusText);
          }
      });
      $('#_project_file').modal('show');
}      


function send_request_approve(id){
  "use strict";
    var data = {};
    data.rel_id = <?php echo html_entity_decode($request_leave->id);; ?>;
    data.rel_type = "<?php echo html_entity_decode($rel_type); ?>";
    data.addedfrom = <?php echo html_entity_decode($request_leave->staff_id); ?>;

    console.log('data', "<?php echo html_entity_decode($rel_type); ?>");

  $("body").append('<div class="dt-loader"></div>');
    $.post(admin_url + 'timesheets/send_request_approve', data).done(function(response){
        response = JSON.parse(response);
        if(response.type == 'choose'){
          $("body").find('.dt-loader').remove();
          if (response.success === true || response.success == 'true') {
              alert_float('success', response.message);
              window.location.reload();
          }else{
            alert_float('warning', response.message);
              window.location.reload();
          }
        }else if(response.type == 'not_choose'){
          $("body").find('.dt-loader').remove();
          $('#choose_approver').html('');
          alert_float('success', response.message);
          $('#choose_approver').append(response.html);
          $('.selectpicker').selectpicker({});
        }
    });
}

function choose_approver(){
  "use strict";
  var data = {};
    data.rel_id = <?php echo html_entity_decode($request_leave->id); ?>;
    data.rel_type = '<?php echo html_entity_decode($rel_type); ?>';
    data.addedfrom = <?php echo html_entity_decode($request_leave->staff_id); ?>;
    data.staffid = $('#approver_c').val();
    if(data.staffid != ''){
      $("body").append('<div class="dt-loader"></div>');
      $.post(admin_url + 'timesheets/choose_approver', data).done(function(response){
          response = JSON.parse(response);
          $("body").find('.dt-loader').remove();
          if (response.success === true || response.success == 'true') {
              alert_float('success', response.message);
              window.location.reload();
          }else{
            alert_float('warning', response.message);
              window.location.reload();
          }
          
      });
    }else if(data.staffid == ''){

      alert_float('warning', '<?php echo _l('please_choose_approver'); ?>');
    }
  
}

function cancel_request(id){
  "use strict";
  var data = {};
    data.rel_id = id;
    data.rel_type = '<?php echo html_entity_decode($rel_type); ?>';
  $.post(admin_url + 'timesheets/cancel_request', data).done(function(response){
      response = JSON.parse(response); 
      if (response.success === true || response.success == 'true') {
          alert_float('success', response.message);
          window.location.reload();
      }
  });
}

function approve_request(id){
  "use strict";
  change_request_approval_status(id,1);
}

function deny_request(id){
  "use strict";
    change_request_approval_status(id,2);
}

function change_request_approval_status(id, status){
  "use strict";

    var data = {};
    data.rel_id = id;
    data.rel_type = '<?php echo html_entity_decode($rel_type); ?>';
    data.approve = status;
    data.note = $('textarea[name="reason"]').val();
    
    $.post(admin_url + 'timesheets/approve_request/' + id, data).done(function(response){
        response = JSON.parse(response); 
        if (response.success === true || response.success == 'true') {
            alert_float('success', response.message);
            window.location.reload();
        }
    });
}


function advance_payment_update(id){
  "use strict";
  var data = {};
  data.amount_received = $('input[id="amount_received"]').val();
  data.received_date = $('input[id="received_date"]').val();
  data.id = id;
  $.post(admin_url+'timesheets/advance_payment_update', data).done(function(response){
    response = JSON.parse(response);
    if(response.success === true || response.success == 'true'){
      alert_float('success','<?php echo _l('updated_successfully'); ?>');
    }else{
      alert_float('warning','<?php echo _l('updated_fail'); ?>');
    }
  });
}



function formatNumber(n) {
  "use strict";
  return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatCurrency(input, blur) {
  "use strict";
  var input_val = input.val();

  if (input_val === "") { return; }

  var original_len = input_val.length;

  var caret_pos = input.prop("selectionStart");

  if (input_val.indexOf(".") >= 0) {

    var decimal_pos = input_val.indexOf(".");

    var left_side = input_val.substring(0, decimal_pos);
    var right_side = input_val.substring(decimal_pos);

    left_side = formatNumber(left_side);

    right_side = formatNumber(right_side);

    right_side = right_side.substring(0, 2);
    input_val = left_side + "." + right_side;

  } else {
    input_val = formatNumber(input_val);
    input_val = input_val;
  }
  input.val(input_val);
  var updated_len = input_val.length;
  caret_pos = updated_len - original_len + caret_pos;
  input[0].setSelectionRange(caret_pos, caret_pos);
}

function delete_requisition_attachment(wrapper, id) {
  "use strict";
    if (confirm_delete()) {
       $.get(admin_url + 'timesheets/delete_timesheets_attachment_file/' + id, function (response) {
          if (response.success == true) {
             $(wrapper).parents('.contract-attachment-wrapper').remove();

             var totalAttachmentsIndicator = $('.attachments-indicator');
             var totalAttachments = totalAttachmentsIndicator.text().trim();
             if(totalAttachments == 1) {
               totalAttachmentsIndicator.remove();
             } else {
               totalAttachmentsIndicator.text(totalAttachments-1);
             }
          } else {
             alert_float('danger', response.message);
          }
       }, 'json');
    }
    return false;
}
</script>