<?php
   /*
   Plugin Name: Advanced Admin Search
   Plugin URI: https://www.kuroit.com/product/advanced-admin-search
   description: A WordPress plugin which adds extra searching feature into admin bar.
   Version: 0.9.1
   Author: Kuroit
   Author URI: https://www.kuroit.com
   License: GPLv2 or later
   License URI: http://www.gnu.org/licenses/gpl-2.0.html
   */
   /* The Advanced Admin Search Plugin
	*
	* Advanced Admin Search is a wordpress plugin which adds extra searching feature into admin bar.
	*
	*/
namespace Kuroit\AdvancedAdminSearch;

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

class AASKP_advancedAdminSearch{

function __construct() {

		// Hook to add input box in admin panel for searching.
        add_action('admin_bar_menu', array( $this, 'AASKP_SearchBox'));
        add_action('in_admin_header', array( $this, 'AASKP_displayInput'));

        // Hooks to get the searching data like admin menu, media labraries, post, pages.
        add_action( 'admin_bar_menu', array( $this, 'AASKP_desktopSearchJavascript' ));
        add_action( 'in_admin_header', array( $this, 'AASKP_mobileSearchJavascript' ));
        add_action( 'wp_ajax_search_result', array( $this, 'AASKP_searchAction' ));

        // Hook to add javascript.
        add_action( 'admin_enqueue_scripts', array( $this, "AASKP_adminJavascript" ) );
        add_action( 'wp_enqueue_scripts', array( $this, "AASKP_adminJavascript" ) );
}

function AASKP_adminJavascript() {
	wp_enqueue_style( 'advaced_admin_search_style',  plugin_dir_url( __FILE__ ) . 'css/style.css' );
	wp_enqueue_script('advaced_admin_search_script' , plugin_dir_url( __FILE__ ) . 'jquery-admin-search.js' );

    $params = array(
	  'ajaxurl' => admin_url('admin-ajax.php'),
	  'ajax_nonce' => wp_create_nonce('advanced_search_submit'),
	);

	wp_register_script( 'advanced_admin_search' );
    wp_enqueue_script( 'advanced_admin_search' );
	wp_localize_script( 'advanced_admin_search', 'advanced_admin_search', $params );

}

function AASKP_SearchBox() {
global $wp_admin_bar;

$wp_admin_bar->add_menu(array(
    'id' => 'search_form',
    'parent' => 'top-secondary',
    'title' => '<ul class="post_search_box">
    	<li class="advance_search_box"><span class="dashicons dashicons-search" onclick="AASKP_displayInputBox()"></span><div class="sf-d"><input name="autocomplete" type="text" placeholder="Search Database" id="post_search_box" autocomplete="off" style="height:20px;margin:5px 0;"/><label for="submit"><span class="dashicons dashicons-search" style="display:block !important;"></span></label><input type="submit" id="submit" name="search" value="Search" style="display:none;"><div class="ajax-loader"><img src="'.plugin_dir_url( __FILE__ ).'image/loading.gif" class="img-responsive" /></div><ul class="search_list"></ul></div></li>
    </ul>'
));

}

function AASKP_displayInput() {
    echo '<div class="sf-m"><div id="search_fields" style="display:none;"><input type="text" placeholder="Search Database" id="mobile_search_fields" autocomplete="off" style="line-height:1em;"/><label for="submit"><span class="dashicons dashicons-search"></span></label><input type="submit" id="submit" name="search" value="Search" style="display:none;"></div><div class="ajax-loading"><img src="'.plugin_dir_url( __FILE__ ).'image/loading.gif" class="img-responsive" /></div><ul class="mobile_search_list"></ul></div>';
}

function AASKP_desktopSearchJavascript() { 
	echo '<script type="text/javascript"> AASKP_desktopSearch(); </script>';
}

function AASKP_mobileSearchJavascript() { 
	echo '<script type="text/javascript"> AASKP_mobileSearch(); </script>';
}

function AASKP_searchAction() {

if (isset($_POST['post_search']) && isset($_POST['security']))
{
	$post_search = sanitize_text_field( $_POST['post_search'] );
	$security_check = sanitize_text_field( $_POST['security'] );

	$check = wp_create_nonce('advanced_search_submit');

	if($security_check == $check)
	{
		if(!empty($post_search))
	    {

	    	$post_types = get_post_types(array('public' => true));
	    	$post_types = array_values($post_types);

			// get the register user.
			$users = get_users( array( 'search' => "*{$post_search}*", 'fields' => array( 'display_name', 'user_registered', 'id' ) ) );
			$countUser = 0;

			foreach ( $users as $user ) {
				$url = admin_url( 'user-edit.php?user_id='.$user->id ); 
				
				$getUser = get_userdata( $user->id );
				$role = $getUser->roles;
				$countUser++;
				
				foreach ($role as $value) {
					echo "<li class='search_rows'><a class='search_result' href='".$url."'><img class='image_thumb' src='".esc_url( get_avatar_url( $user->ID ) )."'>" . $user->display_name . "<p class='list_status'>" . $value . "</p><p class='list_type'>" . $user->user_registered . "</p></a></li>";
				}
			}

			// get posts and pages
			if ($countUser < 10)
			{
				$postPerPage = 10 - $countUser;
				
				$posts = get_posts(
				    array(
					    's' 				=> $post_search,
					    'post_status' 		=> array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash'),
					    'post_type' 		=> 'any',
					    'posts_per_page' 	=> $postPerPage
				    )
				);

				$countPost = count($posts);
				
				foreach ($posts as $post) {
				    $url = admin_url( 'post.php?post='.$post->ID.'&action=edit' ); 
				    $post_type = $post->post_type;

				    echo "<li><a class='search_result' href='".$url."''>".$post->post_title."<p class='list_status'>". $post->post_status."</p> <p class='list_type'>Type: ".$post->post_type."</p></a></li>";
				} 
			}
			
			$totalPost = $countPost + $countUser;

			// get media libraries
			if ($totalPost < 10)
			{
				$mediaPerPage = 10 - $totalPost;
				
				$mediaPosts = get_posts(
				    array(
					    's' => $post_search,
					    'post_type' => 'attachment',
					    'post_status' => 'inherit',
					    'posts_per_page' => $mediaPerPage
				    )
				);

				foreach ($mediaPosts as $mediaPost) {
				    $url = admin_url( 'post.php?post='.$mediaPost->ID.'&action=edit' ); 
				    $post_type = $mediaPost->post_type;
				    $image_url = wp_get_attachment_image_src($mediaPost->ID);

				    echo "<li class='search_rows'><a class='search_result' href='".$url."''><img class='image_thumb' src='".$image_url[0]."'>".$mediaPost->post_title."<p class='list_type'>".$mediaPost->post_date."</p></a></li>";
				} 
			}

			$queryPost = get_posts(
			    array(
				    's' => $post_search,
				    'post_type' =>  'any',
				    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
				    'posts_per_page' => -1
			    )
			);
			
			$countQueryPost = count($queryPost);
			
			$total = $countUser + $countQueryPost;
			if ($total == 0)
			{
				echo "<li class='count_result'><a class='count_post media_list' href='#'><span class='none_result' style='display:none;'>".$total."</span> Result not Found. Please Refine Your Search</a></li>";
			}
			else if ($total > 10)
			{
				echo "<li class='count_result' onclick='javascript:alert(\'This feature is coming soon.\');'><a class='count_post media_list' href='#'>'".$post_search."' search has ";
				echo "<span class='result-count'>".$total."</span>";
				echo " results.</a></li>";
			}
		}
	}
	else
	{
		echo "Invalid Request";
	}
}
else
{
	echo "Refine Your Search";
}
  wp_die(); // this is required to terminate immediately and return a proper response
}

}
new AASKP_advancedAdminSearch();
?>