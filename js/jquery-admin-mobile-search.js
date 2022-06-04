function AASKP_adminMenuMobileSearch(text)

{

    text = text.toLowerCase();

    var countMenu = 0;

    jQuery("#adminmenu > li").each(function () {

        var parentMenu = jQuery(this).find('.wp-menu-name').text().toLowerCase();

        var val = parentMenu.indexOf(text);

        if (text=='') {

            jQuery(".mobile_search_list").html('');

        }

        if (val > -1 && parentMenu != "") {

            var parentMenuLink = jQuery(this).find('a').attr('href');

            jQuery( ".mobile_search_list" ).append("<li><a class='search_result' href="+parentMenuLink+"><p class='list_title'>"+parentMenu+"</p><p class='list_type'>Type: admin link </p></a></li>");

            countMenu = countMenu+1;

        }

    });

  

    var currentCount = parseInt(jQuery('.result-count').text());

    currentCount = currentCount + countMenu;

    jQuery('.result-count').html(currentCount);

}



function AASKP_mobileSearch() {     //desktop search and results display 

    jQuery(document).ready(function($) {

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

            //parse the json result string
            var jsonData = JSON.parse(response);

            jQuery('.ajax-loading').css("visibility", "hidden").css("display", "none");

            var count=jsonData['count'];

            var search=jsonData['search'];

            var myCustomsearch='"'+jsonData['search']+'"';
            

            if(jsonData['result']=='success'){

                if(count==0){  //no results found

                    jQuery( ".mobile_search_list" ).append("<li class='count_result'><a class='count_post media_list' href='#'><span class='none_result' style='display:none;'>"+count+

                    "</span> Result not Found. Please Refine Your Search</a></li>");

                }else{  //display the found results 

                    $.each(jsonData['data'], function(key, values){

                        var status = values['status'];

                        var title = values['title'];

                        var link = values['link'];

                        var info = values['info'];

                        var image = '';

                        if(values['image'] != undefined){

                            image = values['image'];

                        }

                        if(count>10){

                            for(var i=0;i<=10;i++){

                                var results1=jsonData[i];

                            }

                        }else{

                            var results1=jsonData;

                        }

                        if(status=="administrator") {

                            status="admin";

                        }

                        if(image!='' && title!=''){  //image and title available

                            var images = "<img class='image_thumb' src='"+image+"'>";

                            jQuery( ".mobile_search_list" ).append("<li class='search_rows'><a class='search_result' href='"+link+"'>"+images+"<p class='list_title'>"+title+"</p><p class='list_status'>"+status+"</p><p class='list_type'>"+info+"</p></a></li>");  

                        }

                        else{

                            if(title!=''){

                                jQuery( ".mobile_search_list" ).append("<li class='search_rows'><a class='search_result' href='"+link+"'><p class='list_title'>"+title+"</p><p class='list_status'>"+status+"</p><p class='list_type'>"+info+"</p></a></li>");

                            }

                            else{  //no title available

                                jQuery( ".mobile_search_list" ).append("<li class='search_rows'><a class='search_result' href='"+link+"'><p class='list_title'>(no title)</p><p class='list_status'>"+status+"</p><p class='list_type'>"+info+"</p></a></li>");

                            }

                        }

                    });

                    jQuery( ".mobile_search_list" ).append("<li class='count_result' onclick='ASAK_pageView("+myCustomsearch+")'><a class='count_post media_list' href='#'>View all <span class='result-count'>"+count+"</span> search results</a></li> ");

                }

            }

            else{

                jQuery( ".mobile_search_list" ).append("<li class='count_result'><a class='count_post media_list' href='#'><span class='none_result' style='display:none;'></span>"+jsonData['message']+"</a></li>");

            }

            



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



function ASAK_pageView(myCustomsearch){

    window.location.href="tools.php?page=advanced-admin-search&keyword="+myCustomsearch;
}

