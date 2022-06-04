function clickLink(link){
    window.open(link,'_blank');
}
jQuery( document ).ready(function() {
    jQuery('#open_advance_search').on('click',function(){
    	if( jQuery(this).is(':checked') ){
    		jQuery('.advanced_search .input_search,.advanced_search select').removeAttr('disabled');
    		jQuery('.advanced_search .input_search,.advanced_search select').removeAttr('title');
    	}else{
    		jQuery('.advanced_search .input_search,.advanced_search select').prop('disabled','true');
    		jQuery('.advanced_search .input_search,.advanced_search select').prop('title','Check the box above to enable meta search fields');
    	}    	
		
	});
});
