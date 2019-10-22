<?php
   /*
   Plugin Name: Advanced Admin Search
   Plugin URI: https://www.kuroit.com/product/advanced-admin-search
   description: Easily search everything in WordPress admin panel from one single search field.
   Version: 1.0
   Author: Kuroit
   Author URI: https://www.kuroit.com
   License: GPLv2 or later
   License URI: http://www.gnu.org/licenses/gpl-2.0.html
   */
   /* The Advanced Admin Search Plugin
	*
	* Advanced Admin Search is a wordpress plugin which adds extra searching feature into admin bar. Easily search everything in WordPress admin panel from one single search field.
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
			wp_register_script('advaced_admin_search_script' , plugin_dir_url( __FILE__ ) . 'jquery-admin-search.js' );

			$params = array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'ajax_nonce' => wp_create_nonce('advanced_search_submit'),
			);

			//wp_register_script( 'advaced_admin_search_script', 'dummy.js' );
			wp_enqueue_script( 'advaced_admin_search_script' );
			wp_localize_script( 'advaced_admin_search_script', 'advanced_admin_search', $params );

		}

		function AASKP_SearchBox() {

			global $wp_admin_bar;

			$wp_admin_bar->add_menu(array(
				'id' => 'search_form',
				'parent' => 'top-secondary',
				'title' => '<ul class="post_search_box">
				<li class="advance_search_box"><span class="dashicons dashicons-search" onclick="AASKP_displayInputBox()"></span><div class="sf-d"><input name="autocomplete" type="text" placeholder="Search Database" id="post_search_box" autocomplete="off" style="height:20px;margin:5px 0;"/><label for="submit"><span class="dashicons dashicons-search" style="display:block !important;"></span></label><input type="submit" id="submit" name="search" value="Search" style="display:none;"><div class="ajax-loader" style="display: none;"><img src="'.plugin_dir_url( __FILE__ ).'image/loading.gif" class="img-responsive" /></div><ul class="search_list"></ul></div></li>
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
						$results = array(); // all results available for search

						$post_types = get_post_types(array('public' => true));
						$post_types = array_values($post_types);

						// get pre search results from hook
						$pre_filtered_result = apply_filters('aaskp_pre_search', $post_search);
						if( is_array($pre_filtered_result) ){
							$results = array_merge($results, $pre_filtered_result);
						}

						$results = array_merge(
							$results, // pre search
							$this->AASKP_getUsers($post_search),  // user results
							$this->AASKP_getPostsAndPages($post_search), // post types
							$this->AASKP_getMedia($post_search), // attachments
							$this->AASKP_getTaxonomies($post_search), // taxonomies
							$this->AASKP_getPostMeta($post_search), // post meta
							$this->AASKP_getComments($post_search) // comments
						);

						// get post search results from hook
						$post_filtered_result = apply_filters('aaskp_post_search', $post_search);
						if( is_array($post_filtered_result) ){
							$results = array_merge($results, $post_filtered_result);
						}

						// finally print the results
						$this->AASKP_printResults($results,$post_search);

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
			wp_die(); 
		}
		function AASKP_printResults($results,$post_search){
			if(count($results)==0)
			{
				echo "<li class='count_result'><a class='count_post media_list' href='#'><span class='none_result' style='display:none;'>".count($results)."</span> Result not Found. Please Refine Your Search</a></li>";
			}
			else{
				if(count($results)>10)
				{
				$results1=array_slice($results,0,10);
				}
				else{
				echo "<script> function alertbox() {alert('Feature is coming soon.');} </script>";
				$results1=array_merge($results);
				}
				foreach ($results1 as $row) {
					$image = "";
					if(isset($row['image'])){
						$image = "<img class='image_thumb' src='".$row['image']."'>";
					}
					echo "<li class='search_rows'><a class='search_result' href='".$row['link']."'>" . $image . $row['title'] . "<p class='list_status'>" . $row['status'] . "</p><p class='list_type'>" . $row['info'] . "</p></a></li>";
				}
				echo "<li class='count_result' onclick='alertbox()'><a class='count_post media_list' href='#'>'".$post_search."' search has ";
				echo "<span class='result-count'>".count($results)."</span>";
				echo " results.</a></li>";

			}
		}

		function AASKP_getUsers($post_search){
			$output = array();

			$users = get_users( array( 'search' => "*{$post_search}*", 'fields' => array( 'display_name', 'user_registered', 'id' ) ) );

			foreach ( $users as $user ) {
				$url = admin_url( 'user-edit.php?user_id='.$user->id ); 

				$getUser = get_userdata( $user->id );
				$role = $getUser->roles;

				foreach ($role as $value) {
					$output[] = array(
						'link' => $url,
						'title' => $user->display_name,
						'status' => $value,
						'info' => $user->user_registered,
						'image' => esc_url( get_avatar_url( $user->ID ) )
					);
				}
			}
			return $output;
		}

		function AASKP_getPostsAndPages($post_search){
			$output = array();
			$posts = get_posts(
				array(
					's' 				=> $post_search,
					'post_status' 		=> array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash'),
					'post_type' 		=> 'any',
					'posts_per_page' 	=> -1
				)
			);
			foreach ($posts as $post) {
				$url = admin_url( 'post.php?post='.$post->ID.'&action=edit' ); 
				$post_type = $post->post_type;

				$output[]=array(
					'link' => $url,
					'title' => $post->post_title,
					'status' => $post->post_status,
					'info' => 'Type: '.$post->post_type,

				);

			} 
			return $output;
		}


		function AASKP_getMedia($post_search){
			$output = array();
			$mediaPosts = get_posts(
				array(
					's' => $post_search,
					'post_type' => 'attachment',
					'post_status' => 'inherit',
					'posts_per_page' => -1
				)
			);
			foreach ($mediaPosts as $mediaPost) {
				$url = admin_url( 'post.php?post='.$mediaPost->ID.'&action=edit' ); 
				$post_type = $mediaPost->post_type;
				$image_url = wp_get_attachment_image_src($mediaPost->ID);

				$output[]=array(
					'link' => $url,
					'title' => $mediaPost->post_title,

					'info' => $mediaPost->post_date,
					'image'=> $image_url[0]
				);

			} 
			return $output;
		}


		function AASKP_getTaxonomies($post_search){
			$output = array();
			$taxonomies = get_terms( 
				array( 'search' => $post_search ) 
			);

			foreach ($taxonomies as $taxonomy) {
				$url = admin_url( 'term.php?taxonomy='.$taxonomy->taxonomy.'&tag_ID='.$taxonomy->term_id.'&post_type=post&wp_http_referer=%2Fwp-admin%2Fedit-tags.php%3Ftaxonomy%3Dcategory' );
				$output[]=array(
					'link' => $url,
					'title' => $taxonomy->name,
					'info' => 'Taxonomy: '.$taxonomy->taxonomy,

				);
			}

			return $output;
		}

		function AASKP_getPostMeta($post_search){
			global $wpdb;
			$output = array();

			$postMeta = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE meta_key LIKE '%".$post_search."%' OR meta_value LIKE '%".$post_search."%'");

			foreach ($postMeta as $meta) {
				$url = admin_url( 'post.php?post='.$meta->post_id.'&action=edit' ); 
				$getPost = get_post( $meta->post_id );


				if (strpos($meta->meta_key,$post_search) !== false) {

					$output[]=array(
						'link' => $url,
						'title' => $getPost->post_title,
						'status' => 'Meta Key: '.$meta->meta_key,
						'info' =>  $meta->meta_value,

					);

				}
				else
				{
					$output[]=array(
						'link' => $url,
						'title' => $getPost->post_title,
						'info' => 'Meta Value: '.$meta->meta_value,
					);
				}
			}
			return $output;
		}

		function AASKP_getComments($post_search){
			$output = array();
			$comments = get_comments( array( 'search' => $post_search ) );

			foreach ($comments as $comment) {
				$url = admin_url( 'edit-comments.php' );
				$output[]=array(
					'link' => $url,
					'title' => $comment->comment_author_email,

					'info' =>'Comment'. $comment->comment_content,

				);

			}

			return $output;
		} 

	}

	new AASKP_advancedAdminSearch();

?>