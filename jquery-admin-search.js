function adminMenuSearch(text) {
text = text.toLowerCase();

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
      }
  });
  
  var currentCount = jQuery('.result-count').text();

  var countMenu = jQuery(".search_list").children().length;
  
  currentCount = currentCount + countMenu;
  jQuery('.result-count').html(currentCount);
}

function adminMenuMobileSearch(text) {
text = text.toLowerCase();

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
      }
  });
  
  var currentCount = jQuery('.result-count').text();

  var countMenu = jQuery(".mobile_search_list").children().length;
  
  currentCount = currentCount + countMenu;
  jQuery('.result-count').html(currentCount);
}

function desktopSearch() { 
jQuery(document).ready(function($) {
	jQuery('#post_search_box').keyup(function() {
	jQuery( ".search_list" ).html('');

	var data = {
	    'action': 'search_result',
	    'security': advanced_admin_search.ajax_nonce,
	    'post_search': jQuery('#post_search_box').val()
	};
	    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
			adminMenuSearch(jQuery('#post_search_box').val());
			jQuery( ".search_list" ).append(response);			
	    });
	});
});
}

function mobileSearch() { 
jQuery(document).ready(function($) {
	jQuery('#search_fields').keyup(function() {
	jQuery( ".mobile_search_list" ).html('');
	var data = {
	    'action': 'search_result',
	    'security': advanced_admin_search.ajax_nonce,
	    'post_search': jQuery('#search_fields').val()
	};
	    // points to admin-ajax.php
		jQuery.post('/wp-admin/admin-ajax.php', data, function(response) {
			adminMenuMobileSearch(jQuery('#search_fields').val());
			jQuery( ".mobile_search_list" ).append(response);
	    });
	});
});
}

function displayInputBox() {
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