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


jQuery("#seramo-thickbox-content").on("click", '#seramo_add_argument_btn', function(e) {
	e.preventDefault();
	
	var wp_args_select_value = jQuery("#wp_args_select").val();
	
	if( wp_args_select_value != '' ){
		jQuery('#saramo-insert-dumps .seramo_arg_type_' + wp_args_select_value ).clone().appendTo('#seramo-added-arguments-wrapper');
	}
});


jQuery("#seramo-thickbox-content").on("click", '#seramo_add_save_btn', function(e) {
	e.preventDefault();
	
	
	console.log( jQuery('#seramo_addquery_form').serializeArray() );
	
	jQuery('#seramo_addquery_form').submit();
	
});

/*
jQuery("#seramo-thickbox-content").on("click", '.seramo_repeat_argument', function(e) {
	e.preventDefault();
	console.log('#saramo-insert-dumps .seramo_arg_type_' + jQuery(this).closest('.seramo_argument').attr('argtype'));
	
	jQuery(this).closest('.seramo_argument').after( jQuery('#saramo-insert-dumps .seramo_arg_type_' + jQuery(this).closest('.seramo_argument').attr('argtype') + '_repeater').clone() );
	
});
*/
