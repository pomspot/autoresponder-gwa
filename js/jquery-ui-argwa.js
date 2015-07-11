jQuery(document).ready(function($) {

	// Tabs
	$('#tabs').tabs();


	// Dialog			
	$('#dialog').dialog({
    position:['middle',40],
		autoOpen: false,
		modal: true,
		width: 700,
		buttons: {
			"Cancel": function() {
				$(this).dialog("close");
			}
		}
	});
	
	// Slider
	$('#sliderRun').slider({
		min: 1,
		max: 60,
      change: function(event, ui) {
        $('#arRun').val(ui.value);
      }
	});
	
	$('#sliderPause').slider({
		min: 1,
		max: 100,
      change: function(event, ui) {
        $('#arMpr').val(ui.value);
      }
	});

	$('#sliderThrottle').slider({
		min: 30,
		max: 90,
      change: function(event, ui) {
        $('#arThrottle').val(ui.value);
      }
	});

	//hover states on the static widgets
	$('#dialog_link, ul#icons li').hover(
		function() { $(this).addClass('ui-state-hover'); }, 
		function() { $(this).removeClass('ui-state-hover'); }
	);
});