<?php
   /*
   Plugin Name: Advanced Admin Search
   Plugin URI: 
   description: A WordPress plugin which adds extra searching feature into admin bar.
   Version: 1.0
   Author: 
   Author URI: 
   License: GPLv2 or later
   License URI: http://www.gnu.org/licenses/gpl-2.0.html
   */
   /* The Advanced Admin Search Plugin
	*
	* Advanced Admin Search is a wordpress plugin which adds extra searching feature into admin bar.
	*
	***/
class aask_advancedAdminSearch{

function __construct() {

		// Hook to add input box in admin panel for searching.
        add_action('admin_bar_menu', array( $this, 'SearchBox'));
        add_action('in_admin_header', array( $this, 'displayInput'));

        // Hooks to get the searching data like admin menu, media labraries, post, pages.
        add_action( 'admin_bar_menu', array( $this, 'desktopSearchJavascript' ));
        add_action( 'in_admin_header', array( $this, 'mobileSearchJavascript' ));
        add_action( 'wp_ajax_search_result', array( $this, 'searchAction' ));

        // Hook to add javascript.
        add_action( 'admin_enqueue_scripts', array( $this, "adminJavascript" ) );
}

function adminJavascript() {
	wp_enqueue_style( 'advaced_admin_search_style',  plugin_dir_url( __FILE__ ) . 'css/style.css' );
	wp_enqueue_script('advaced_admin_search_script' , plugin_dir_url( __FILE__ ) . 'jquery-admin-search.js' );

    $params = array(
	  'ajaxurl' => admin_url('admin-ajax.php', 'https'),
	  'ajax_nonce' => wp_create_nonce('advanced_search_submit'),
	);

	wp_register_script( 'advanced_admin_search' );
    wp_enqueue_script( 'advanced_admin_search' );
	wp_localize_script( 'advanced_admin_search', 'advanced_admin_search', $params );

}

function SearchBox() {
global $wp_admin_bar;

$wp_admin_bar->add_menu(array(
    'id' => 'search_form',
    'parent' => 'top-secondary',
    'title' => '<ul class="post_search_box" style="display:none;">
    	<li class="advance_search_box"><span class="dashicons dashicons-search" onclick="displayInputBox()"></span><div class="sf-d"><input name="autocomplete" type="text" placeholder="Search Database" id="post_search_box" autocomplete="off" style="height:20px;margin:5px 0;"/><label for="submit"><i class="fa fa-search" aria-hidden="true"></i></label><input type="submit" id="submit" name="search" value="Search" style="display:none;"><div class="ajax-loader"><img src="'.plugin_dir_url( __FILE__ ).'image/loading.gif" class="img-responsive" /></div><ul class="search_list"></ul></div></li>
    </ul>'
));

}

function displayInput() {
    echo '<div class="sf-m"><input type="text" placeholder="Search Database" id="search_fields" autocomplete="off" style="line-height:1em; display:none;"/><ul class="mobile_search_list"></ul></div>';
}

function desktopSearchJavascript() { 
	echo '<script type="text/javascript"> desktopSearch(); </script>';
}

function mobileSearchJavascript() { 
	echo '<script type="text/javascript"> mobileSearch(); </script>';
}

function searchAction() {
	$post_search = $_POST['post_search'];

	$check = wp_create_nonce('advanced_search_submit');

	if($_POST['security'] == $check)
	{
		if(!empty($post_search))
	    {
			// get the register user.
			$users = get_users( array( 'search' => "*{$post_search}*", 'fields' => array( 'display_name', 'user_registered', 'id' ) ) );
			$countUser = 0;

			foreach ( $users as $user ) {
				$url = admin_url( 'user-edit.php?user_id='.$user->id, 'https' ); 
				$getUser = new WP_User( $user->id );
				$role = $getUser->roles;
				$countUser = count($user->display_name);
				
				foreach ($role as $value) {
					echo "<li class='search_rows'><a class='search_result' href='".$url."'><img class='image_thumb' src='".esc_url( get_avatar_url( $user->ID ) )."'>" . $user->display_name . "<p class='list_status'>". $value."</p><p class='list_type'>" . $user->user_registered . "</p></a></li>";
				}
			}

			// get posts and pages
			if ($countUser < 10)
			{
				$postPerPage = 10 - $countUser;
				
				$query = new WP_Query(
				    array(
					    's' => $post_search,
					    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash'),
					    'posts_per_page' => $postPerPage
				    )
				);

				$posts = $query->posts;
				$countPost = count($posts);
				
				foreach ($posts as $post) {
				    $url = admin_url( 'post.php?post='.$post->ID.'&action=edit', 'https' ); 
				    $post_type = $post->post_type;

				    print_r("<li><a class='search_result' href='".$url."''>".$post->post_title."<p class='list_status'>". $post->post_status."</p> <p class='list_type'>Type: ".$post->post_type."</p></a></li>");
				} 
			}
			
			$totalPost = $countPost + $countUser;

			// get media libraries
			if ($totalPost < 10)
			{
				$mediaPerPage = 10 - $totalPost;
				
				$mediaQuery = new WP_Query(
				    array(
					    's' => $post_search,
					    'post_type' => 'attachment',
					    'post_status' => 'inherit',
					    'posts_per_page' => $mediaPerPage
				    )
				);

				$mediaPosts = $mediaQuery->posts;
				
				foreach ($mediaPosts as $mediaPost) {
				    $url = admin_url( 'post.php?post='.$mediaPost->ID.'&action=edit', 'https' ); 
				    $post_type = $mediaPost->post_type;

				    print_r("<li class='search_rows'><a class='search_result' href='".$url."''><img class='image_thumb' src='".$mediaPost->guid."'>".$mediaPost->post_title."<p class='list_type'>".$mediaPost->post_date."</p></a></li>");
				} 
			}

			$queryPost = new WP_Query(
			    array(
				    's' => $post_search,
				    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
				    'posts_per_page' => -1
			    )
			);
			$countQueryPost = count($queryPost->posts);
			
			if ($countQueryPost > 10)
			{
				echo "<li class='count_result'><a class='count_post media_list' href='#'>'".$post_search."' search has ";
				$total = $countUser + $countQueryPost;
				echo "<span class='result-count'>".$total."</span>";
				echo " results.</a></li>";
			}
		}
	}
	else
	{
		echo "Invalid Request";
	}
  wp_die(); // this is required to terminate immediately and return a proper response
}

}
new aask_advancedAdminSearch();
?>
