 jQuery(document).ready(function() {


	   jQuery(function() {
	 	  jQuery( "#tabs" ).tabs();
	   });

		jQuery(document).ready(function() {
			jQuery("#save-slug").on( "click", function(e) {
				e.preventDefault()
				jQuery(".spinner").show();
				mighty_seramo_update_slug();
			});
		 });
		
		
        function mighty_seramo_update_slug(){
	        jQuery.post(ajaxurl,
	        	{
		        	action :'mighty_seramo_save_slug',
		        	new_slug : jQuery("#seramo_slug").val()
	        	},
		        	function( data ) {
	     
	        			jQuery("#seramo_slug").val(data);
	        			jQuery(".seramo_slug").html(data);
	        			jQuery(".spinner").hide();
		        	}
	        );
        }

});