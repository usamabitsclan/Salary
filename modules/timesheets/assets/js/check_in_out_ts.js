  (function(){
   		"use strict";
  	$(document).ready(function () {
	    setInterval(updateClock, 1000);
	});
  appValidateForm($('#timesheets-form-check-in'), {
           'staff_id': 'required',
           'date': 'required'
  })
  appValidateForm($('#timesheets-form-check-out'), {
           'staff_id': 'required',
           'date': 'required'
  })
  })(jQuery);
  function open_check_in_out(){
     "use strict";
    $('#input_method_modal').modal('show');    
  }

function updateClock() {
   "use strict";
    var currentTime = new Date();
    var currentHoursAP = currentTime.getHours();
    var currentHours = currentTime.getHours();
    var currentMinutes = currentTime.getMinutes();
    var currentSeconds = currentTime.getSeconds();
    currentMinutes = (currentMinutes < 10 ? "0" : "") + currentMinutes;
    currentSeconds = (currentSeconds < 10 ? "0" : "") + currentSeconds;
    var timeOfDay = (currentHours < 12) ? "AM" : "PM";
    currentHoursAP = (currentHours > 12) ? currentHours - 12 : currentHours;
    currentHoursAP = (currentHoursAP == 0) ? 12 : currentHoursAP;
    var currentTimeString =  currentHours + ":" + currentMinutes + ":" + currentSeconds;
    
   $('.time_script').text(currentTimeString);
   "use strict";
   $('input[name="hours"]').val(currentTimeString);
 }

