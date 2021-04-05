<script>
 var salesChart;
 var groupsChart;
 var paymentMethodsChart;
 var customersTable;
 var report_from = $('input[name="report-from"]');
 var report_to = $('input[name="report-to"]');

 var report_leave_statistics = $('#leave-statistics');

 var date_range = $('#date-range');
 var report_from_choose = $('#report-time');
 var fnServerParams = {
   "report_months": '[name="months-report"]',
   "report_from": '[name="report-from"]',
   "report_to": '[name="report-to"]',
   "role_filter": "[name='role[]']",
   "department_filter": "[name='department[]']",
   "staff_filter": "[name='staff[]']",
   "months_filter": "[name='months-report']",
   "year_requisition": "[name='year_requisition']",
 };
(function(){
  "use strict";

   $('').on('change', function() {
     gen_reports();
   });

   report_from.on('change', function() {
     var val = $(this).val();
     var report_to_val = report_to.val();
     if (val != '') {
       report_to.attr('disabled', false);
       if (report_to_val != '') {
         gen_reports();
       }
     } else {
       report_to.attr('disabled', true);
     }
   });

   report_to.on('change', function() {
     var val = $(this).val();
     if (val != '') {
       gen_reports();
     }
   });

   $('select[name="months-report"]').on('change', function() {
     var val = $(this).val();
     report_to.attr('disabled', true);
     report_to.val('');
     report_from.val('');
     if (val == 'custom') {
       date_range.addClass('fadeIn').removeClass('hide');
       return;
     } else {
       if (!date_range.hasClass('hide')) {
         date_range.removeClass('fadeIn').addClass('hide');
       }
     }
     gen_reports();
   });

    $('select[name="year_requisition"]').on('change', function() {
     var val = $(this).val();
     gen_reports();
   });

   
   $('select[name="role[]"],select[name="department[]"],select[name="staff[]"]').on('change', function() {
     gen_reports();
   });
})(jQuery);

 function init_report(e, type) {
  "use strict";
   var report_wrapper = $('#report');

   if (report_wrapper.hasClass('hide')) {
        report_wrapper.removeClass('hide');
   }

   $('head title').html($(e).text());
   $('.leave-statistics-gen').addClass('hide');

   
   report_leave_statistics.addClass('hide');


   report_from_choose.addClass('hide');

   $('select[name="months-report"]').selectpicker('val', 'this_month');
       report_to.val('');
       report_from.val('');
       $('#report-time').removeClass('hide');
       $('.title_table').text('');
       $('.sorting_table').addClass('hide');
       $('select[name="role[]"]').closest('.col-md-4').removeClass('hide');
       $('select[name="staff[]"]').closest('.col-md-4').removeClass('hide');

        $('.working-hours-gen').addClass('hide');
        $('#leave-reports').addClass('hide');
        $('#year_requisition').addClass('hide');
        $('#general_public_report').addClass('hide');
        $('#report-time').addClass('hide');
        $('#requisition_report').addClass('hide');


        $('.working-hours-gen').addClass('hide');
        $('#leave-reports').addClass('hide');
        $('#report_the_employee_quitting').addClass('hide');
        $('#list_of_employees_with_salary_change').addClass('hide');

       if(type == 'working_hours'){
          $('.working-hours-gen').removeClass('hide');
          $('#report-time').removeClass('hide');
       } 
       else if(type == 'annual_leave_report'){
          $('.sorting_table').removeClass('hide');
          $('#leave-reports').removeClass('hide');
          $('#year_requisition').removeClass('hide');
       } 
       else if(type == 'general_public_report'){      
          $('.sorting_table').removeClass('hide');
          $('#general_public_report').removeClass('hide');
          $('#report-time').removeClass('hide');
       }else if(type == 'requisition_report'){
          $('.sorting_table').removeClass('hide');
          $('#requisition_report').removeClass('hide');
          $('#report-time').removeClass('hide');        
       }    
       gen_reports();
    }
   

   function report_by_working_hours() {
  "use strict";
     if (typeof(groupsChart) !== 'undefined') {
       groupsChart.destroy();
     }
     var data = {};
     data.months_report = $('select[name="months-report"]').val();
     data.report_from = report_from.val();
     data.report_to = report_to.val();


     $.post(admin_url + 'timesheets/report_by_working_hours', data).done(function(response) {
       response = JSON.parse(response);
       //get data for hightchart
      Highcharts.setOptions({
          chart: {
              style: {
                  fontFamily: 'inherit !important',
                  fill: 'black'
              }
          },
          colors: [ '#119EFA','#ef370dc7','#15f34f','#791db2d1', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263','#6AF9C4','#50B432','#0d91efc7','#ED561B']
         });
      Highcharts.chart('working-hours-gen', {
          chart: {
              type: 'column'
          },
          title: {
              text: '<?php echo _l('working_hours'); ?>'
          },
          credits: {
                enabled: false
            },
          xAxis: {
              categories: response.categories,
              crosshair: true
          },
          yAxis: {
              min: 0,
              title: {
                  text: ''
              }
          },
          tooltip: {
              headerFormat: '<span class="fontsize10">{point.key}</span><table>',
              pointFormat: '<tr><td style="color:{series.color};" class="padding0">{series.name}: </td>' +
                  '<td class="padding0"><b>{point.y:.1f}</b></td></tr>',
              footerFormat: '</table>',
              shared: true,
              useHTML: true
          },
          plotOptions: {
              column: {
                  pointPadding: 0.2,
                  borderWidth: 0
              }
          },
          series: [ {
              name: '<?php echo _l('total_work_hours'); ?>',
              data: response.total_work_hours

          }, {
              name: '<?php echo _l('total_work_hours_approved'); ?>',
              data: response.total_work_hours_approved

          }]
      });
       

     });
   }
   // Main generate report function
   function gen_reports() {
    "use strict";

    if (!$('.working-hours-gen').hasClass('hide')) {
       report_by_working_hours();
    }
    if(!$('#leave-reports').hasClass('hide')){
      leave_report();
    }

    if(!$('#general_public_report').hasClass('hide')){
      general_public_report();
    }
    if(!$('#requisition_report').hasClass('hide')){
      requisition_report();
    }     
  }


 
  function leave_report(){
    "use strict";
    $('.title_table').text('<?php echo _l('annual_leave_report'); ?>');
    if ($.fn.DataTable.isDataTable('.table-leave-report')) {
       $('.table-leave-report').DataTable().destroy();
     } 
     initDataTable('.table-leave-report', admin_url + 'timesheets/leave_reports', false, false, fnServerParams, [0, 'desc']);
 }
  function general_public_report(){
  "use strict";
  $('.title_table').text('<?php echo _l('general_public_report'); ?>');
  if ($.fn.DataTable.isDataTable('.table-general_public_report')) {
     $('.table-general_public_report').DataTable().destroy();
  } 
   initDataTable('.table-general_public_report', admin_url + 'timesheets/general_public_report', false, false, fnServerParams, [0, 'desc']);
 }
 function report_the_employee_quitting(){
  "use strict";
  $('.title_table').text('<?php echo _l('report_the_employee_quitting'); ?>');
    if ($.fn.DataTable.isDataTable('.table-report_the_employee_quitting')) {
     $('.table-report_the_employee_quitting').DataTable().destroy();
    } 
   initDataTable('.table-report_the_employee_quitting', admin_url + 'timesheets/report_the_employee_quitting', false, false, fnServerParams, [0, 'desc']);
 }

function list_of_employees_with_salary_change(){ 
  "use strict";
    $('.title_table').text('<?php echo _l('list_of_employees_with_salary_change'); ?>');
    if ($.fn.DataTable.isDataTable('.table-list_of_employees_with_salary_change')) {
    $('.table-list_of_employees_with_salary_change').DataTable().destroy();
    } 
   initDataTable('.table-list_of_employees_with_salary_change', admin_url + 'timesheets/list_of_employees_with_salary_change', false, false, fnServerParams, [0, 'desc']);
}
function attendance_report(){
  "use strict";
    $('.title_table').text('<?php echo _l('attendance_report'); ?>');
    if ($.fn.DataTable.isDataTable('.table-attendance_report')) {
    $('.table-attendance_report').DataTable().destroy();
    } 
   initDataTable('.table-attendance_report', admin_url + 'timesheets/attendance_report', false, false, fnServerParams, [0, 'desc']);
}
function requisition_report(){
  "use strict";
    $('.title_table').text('<?php echo _l('manage_requisition_report'); ?>');
    if ($.fn.DataTable.isDataTable('.table-requisition_report')) {
    $('.table-requisition_report').DataTable().destroy();
    } 
   initDataTable('.table-requisition_report', admin_url + 'timesheets/requisition_report', false, false, fnServerParams, [0, 'desc']);
}
</script>
