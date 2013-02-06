jQuery(document).ready(function() {

	jQuery(function() {
		jQuery("#tabs").tabs();
	});

	jQuery("#save-slug").on("click", function(e) {
		e.preventDefault();
		jQuery(".spinner").show();
		mighty_seramo_update_slug();
	});

	function mighty_seramo_update_slug() {
		jQuery.post(ajaxurl, {
			action : 'mighty_seramo_save_slug',
			new_slug : jQuery("#seramo_slug").val()
		}, function(data) {

			jQuery("#seramo_slug").val(data);
			jQuery(".seramo_slug").html(data);
			jQuery(".spinner").hide();
		});
	}


	jQuery(".seramo-thickbox").on("click", function(e) {
		e.preventDefault();	
		jQuery.post(
		ajaxurl, 
		{
			action : 'mighty_seramo_add_query'
		}, 
		function(data) {
			
			jQuery("#seramo-thickbox-content").html(data);

			
		});
		
	});

	
});


/*
 * 		trigger for switching to queries tab, from settings page
 */
jQuery(".seramo-gotoqueries").on("click", function(e) {
	jQuery("#tabs").tabs({ selected: 1 });
});


jQuery("#seramo-thickbox-content").on("click", '.panel-link', function(e) {
	e.preventDefault();
	jQuery(".active-panel").hide().removeClass('active-panel');
	jQuery(jQuery(this).attr('href')).show().addClass('active-panel');
});
