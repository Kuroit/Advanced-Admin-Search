function AASKP_adminMenuSearch(text)

{

    text = text.toLowerCase();

    var countMenu = 0;

    jQuery("#adminmenu > li").each(function () {

        var parentMenu = jQuery(this).find('.wp-menu-name').text().toLowerCase();

        var val = parentMenu.indexOf(text);

        if (text=='') {

            jQuery(".search_list").html('');

        }

        if (val > -1 && parentMenu != "") {

            var parentMenuLink = jQuery(this).find('a').attr('href');

            jQuery(".search_list").append("<li><a class='search_result' href="+parentMenuLink+"><p class='list_title'>"+parentMenu+"</p><p class='list_type'>Type: admin link </p></a></li>");

            countMenu = countMenu+1;

        }

    });

  

    var currentCount = parseInt(jQuery('.result-count').text());

    currentCount = currentCount + countMenu;

    jQuery('.result-count').html(currentCount);

}



function AASKP_desktopSearch() { //desktop search and results display 

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

            //parse the json result string
            var jsonData = JSON.parse(response);

            jQuery('.ajax-loader').css("visibility", "hidden").css("display", "none");

            var count=jsonData['count'];

            var search=jsonData['search'];

            var myCustomsearch='"'+jsonData['search']+'"';



            if(jsonData['result']=='success'){   

                if(count==0){ //no results found

                    jQuery( ".search_list" ).append("<li class='count_result'><a class='count_post media_list' href='#'><span class='none_result' style='display:none;'>"+count+

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

                        if(image!='' && title!=''){ //image and title available

                            var images = "<img class='image_thumb' src='"+image+"'>";

                            jQuery( ".search_list" ).append("<li class='search_rows'><a class='search_result' href='"+link+"'>"+images+"<p class='list_title'>"+title+"</p><p class='list_status'>"+status+"</p><p class='list_type'>"+info+"</p></a></li>"); 

                        }

                        else{

                            if(title!=''){ 

                                jQuery( ".search_list" ).append("<li class='search_rows'><a class='search_result' href='"+link+"'><p class='list_title'>"+title+"</p><p class='list_status'>"+status+"</p><p class='list_type'>"+info+"</p></a></li>");

                            }

                            else{   //no title available

                                jQuery( ".search_list" ).append("<li class='search_rows'><a class='search_result' href='"+link+"'><p class='list_title'>(no title)</p><p class='list_status'>"+status+"</p><p class='list_type'>"+info+"</p></a></li>");

                            }

                        }

                    });

                    jQuery( ".search_list" ).append("<li class='count_result' onclick='ASAK_pageView("+myCustomsearch+")'><a class='count_post media_list' href='#'>View all <span class='result-count'>"+count+"</span> search results</a></li> ");

                }

            }

            else{

                jQuery( ".search_list" ).append("<li class='count_result'><a class='count_post media_list' href='#'><span class='none_result' style='display:none;'></span>"+jsonData['message']+"</a></li>");

            }



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

                currentCount = parseInt(countMenu) - 1; 

                jQuery('.result-count').html(currentCount);

            }



            $(document).on("click", function(event){

                var $trigger = $(".post_search_box");

                if($trigger !== event.target && !$trigger.has(event.target).length){

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



function ASAK_pageView(myCustomsearch){

    window.location.href="tools.php?page=advanced-admin-search&keyword="+myCustomsearch;
}

