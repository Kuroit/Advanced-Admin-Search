function AASKP_adminMenuSearch(text) {
text = text.toLowerCase();

  var countMenu = 0;
  jQuery("#adminmenu > li").each(function(){
    var parentMenu = jQuery(this).find('.wp-menu-name').text().toLowerCase();
    var val = parentMenu.indexOf(text);

      if (text=='')
      {
        jQuery( ".search_list" ).html('');
      }

      if (val > -1 && parentMenu != "")
      {
        var parentMenuLink = jQuery(this).find('a').attr('href');
        jQuery( ".search_list" ).append("<li><a class='search_result' href="+parentMenuLink+">"+parentMenu+"<p class='list_type'>Type: admin link </p></a></li>");
        countMenu = countMenu+1;
      }
  });
  
  var currentCount = parseInt(jQuery('.result-count').text());
  
  currentCount = currentCount + countMenu;
  jQuery('.result-count').html(currentCount);
}

function AASKP_adminMenuMobileSearch(text) {
  text = text.toLowerCase();
  var countMenu = 0;
  jQuery("#adminmenu > li").each(function(){
    var parentMenu = jQuery(this).find('.wp-menu-name').text().toLowerCase();
    var val = parentMenu.indexOf(text);

      if (text=='')
      {
        jQuery( ".mobile_search_list" ).html('');
      }

      if (val > -1 && parentMenu != "")
      {
        var parentMenuLink = jQuery(this).find('a').attr('href');
        jQuery( ".mobile_search_list" ).append("<li><a class='search_result' href="+parentMenuLink+">"+parentMenu+"<p class='list_type'>Type: admin link </p></a></li>");
        countMenu = countMenu+1;
      }
  });
  
  var currentCount = parseInt(jQuery('.result-count').text());
  
  currentCount = currentCount + countMenu;
  jQuery('.result-count').html(currentCount);
}

function AASKP_desktopSearch() { 
jQuery(document).ready(function($) {
	
	var search_dropdown={};
	search_dropdown.list=function() {
	jQuery( ".search_list" ).html('');
	
	var data = {
	    'action': 'search_result',
	    'beforeSend': function(){
    	jQuery('.ajax-loader').css("visibility", "visible").css("display", "block");
  		},  
	    'security': advanced_admin_search.ajax_nonce,
	    'post_search': jQuery('#post_search_box').val(),
	};
		
		jQuery.post(advanced_admin_search.ajaxurl, data, function(response) {
			AASKP_adminMenuSearch(jQuery('#post_search_box').val());
			jQuery( ".search_list" ).append(response);
			jQuery('.ajax-loader').css("visibility", "hidden").css("display", "none");
			
			var noneResult = jQuery('.none_result').text();
			var currentCount = jQuery('.result-count').text();
  			var countMenu = jQuery(".search_list").children().length;
  			
  			if (noneResult == 0) {
  				noneResult = noneResult + countMenu - 1;
  				if (noneResult) {
					jQuery('.none_result').parent().css('display', 'none');
				}
			}
			if (currentCount != '') {
				currentCount = parseInt(countMenu) - 1; //parseInt(currentCount) + 
  				jQuery('.result-count').html(currentCount);
			}
		   
		    $(document).on("click", function(event){
		        var $trigger = $(".post_search_box");
		        if($trigger !== event.target && !$trigger.has(event.target).length){
		           // $(".search_list").slideUp("fast");
		            $( ".search_list" ).html('');
		            $( "input#post_search_box" ).val('');
		        }            
		    });
 	    });	   
	}
	
	jQuery('#post_search_box').keypress(function(e) {
	if(e.key === "Enter") search_dropdown.list();
	});
	
	$('#submit').click(search_dropdown.list);
});
}

function AASKP_mobileSearch() { 
jQuery(document).ready(function($) {
//	jQuery('#search_fields').keyup(function() {
	var search_dropdown={};
	search_dropdown.list=function() {
	jQuery( ".mobile_search_list" ).html('');
	var data = {
	    'action': 'search_result',
	    'beforeSend': function(){
    	jQuery('.ajax-loading').css("visibility", "visible").css("display", "block");
  		},
	    'security': advanced_admin_search.ajax_nonce,
	    'post_search': jQuery('#mobile_search_fields').val()
	};

		jQuery.post(advanced_admin_search.ajaxurl, data, function(response) {
			AASKP_adminMenuMobileSearch(jQuery('#mobile_search_fields').val());
			jQuery( ".mobile_search_list" ).append(response);
			jQuery('.ajax-loading').css("visibility", "hidden").css("display", "none");
			
			jQuery(document).on("click", function(event){
		        var $trigger = jQuery(".sf-m");
		        if($trigger !== event.target && !$trigger.has(event.target).length){
		            jQuery( ".mobile_search_list" ).html('');
		            jQuery( "input#mobile_search_fields" ).val('');
		        }            
		    });
	    });
	}
	
	jQuery('#mobile_search_fields').keypress(function(e) {
	if(e.key === "Enter") search_dropdown.list();
	});
	
	$('#submit').click(search_dropdown.list);
	
});
}

function AASKP_displayInputBox() {
  var searchField = document.getElementById("search_fields");

    if (!searchField.style.display || searchField.style.display === "none")
    {
        searchField.style.display = "block";
    } 
    else 
    {
        searchField.style.display = "none";
        jQuery("#search_fields, textarea").val("");
        jQuery( ".mobile_search_list" ).html('');
    }
}