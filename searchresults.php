<?php

namespace Kuroit\AdvancedAdminSearch;


class AASKP_searchResults
{
    public function __construct()
    {
        add_action('wp_ajax_search_result', array($this, 'AASKP_searchAction'));
    }

    public function AASKP_searchAction()
    {

        if (isset($_POST['post_search']) && isset($_POST['security'])) {
                $post_search = sanitize_text_field($_POST['post_search']);
                $security_check = sanitize_text_field($_POST['security']);
                $check = wp_create_nonce('advanced_search_submit');
            if ($security_check == $check) {
                $results = array(); // all results available for search
                if (!empty($post_search)) {
                        $post_types = get_post_types(array('public' => true));
                        $post_types = array_values($post_types);

                        // get pre search results from hook
                        $pre_filtered_result = apply_filters('aaskp_pre_search', $post_search);
                    if (is_array($pre_filtered_result)) {
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
                    if (is_array($post_filtered_result)) {
                            $results = array_merge($results, $post_filtered_result);
                    }
                    echo json_encode(array('result'=>'success', 'data'=>$results, 'count'=>count($results), 'search'=>$post_search));
                }
                else {
                     echo json_encode(array('result'=>'success', 'count'=> count($results)));
                }
            } else {
                echo json_encode(array('result'=>'error', 'message'=>'Invalid Request'));
            }
        } else {
                echo json_encode(array('result'=>'none', 'message'=>'Refine Your Search'));
        }
            wp_die();
    }

    public function AASKP_getUsers($post_search)
    {
            $output = array();

            $users = get_users(array('search' => "*{$post_search}*", 'fields'
            => array('display_name', 'user_registered', 'id')));

        foreach ($users as $user) {
                $url = admin_url('user-edit.php?user_id='.$user->id);
                $getUser = get_userdata($user->id);
                $role = $getUser->roles;

            foreach ($role as $value) {
                    $output[] = array(
                        'link' => $url,
                        'title' => $user->display_name,
                        'status' => $value,
                        'info' => $user->user_registered,
                        'image' => esc_url(get_avatar_url($user->ID))
                    );
            }
        }
            return $output;
    }

    public function AASKP_getPostsAndPages($post_search)
    {
            $output = array();
            $posts = get_posts(
                array(
                    's'                 => $post_search,
                    'post_status'       => array('publish', 'pending', 'draft',
                    'auto-draft', 'future', 'private', 'trash'),
                    'post_type'         => 'any',
                    'posts_per_page'    => -1
                )
            );
        foreach ($posts as $post) {
                $url = admin_url('post.php?post='.$post->ID.'&action=edit');
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

    public function AASKP_getMedia($post_search)
    {
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
                $url = admin_url('post.php?post='.$mediaPost->ID.'&action=edit');
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

    public function AASKP_getTaxonomies($post_search)
    {
            $output = array();
            $taxonomies = get_terms(
                array('search' => $post_search)
            );

        foreach ($taxonomies as $taxonomy) {
                $url = admin_url('term.php?taxonomy='.$taxonomy->taxonomy.
                '&tag_ID='.$taxonomy->term_id.'&post_type=post&wp_http_referer
                =%2Fwp-admin%2Fedit-tags.php%3Ftaxonomy%3Dcategory');
                $output[]=array(
                    'link' => $url,
                    'title' => $taxonomy->name,
                    'info' => 'Taxonomy: '.$taxonomy->taxonomy,

                );
        }

            return $output;
    }

    public function AASKP_getPostMeta($post_search)
    {
            global $wpdb;
            $output = array();

            $postMeta = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE meta_key
            LIKE '%".$post_search."%' OR meta_value LIKE '%".$post_search."%'");
        foreach ($postMeta as $meta) {
                $url = admin_url('post.php?post='.$meta->post_id.'&action=edit');
                $getPost = get_post($meta->post_id);
            if (strpos($meta->meta_key, $post_search) !== false) {
                        $output[]=array(
                        'link' => $url,
                        'title' => $getPost->post_title,
                        'status' =>'PostMeta',
                        'info' =>  $meta->meta_value,

                    );
            } 
                        else {
                    $output[]=array(
                        'link' => $url,
                        'title' => $getPost->post_title,
                        'status' => $getPost->post_status,
                        'info' => 'Meta Value: '.$meta->meta_value,
                    );
            }
        }
            return $output;
    }

    public function AASKP_getComments($post_search)
    {
            $output = array();
            $comments = get_comments(array('search' => $post_search));

        foreach ($comments as $comment) {
                $url = admin_url('edit-comments.php');
                $output[]=array(
                    'link' => $url,
                    'title' => $comment->comment_author_email,
                    'info' =>'Comment'. $comment->comment_content,

                );
        }

        return $output;
    }
}
