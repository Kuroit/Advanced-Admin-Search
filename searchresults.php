<?php
namespace Kuroit\AdvancedAdminSearch;

//class for searching results
class AASKP_searchResults
{
    protected static $instance = false;
    public function __construct() {
        // Init the search results
        add_action('wp_ajax_search_result', array($this, 'AASKP_searchAction'));
    }

    // Ajax search callback function
    public function AASKP_searchAction() {

        // Start fetching the results
        if (isset($_POST['post_search']) && isset($_POST['security'])) {

            // Get POST data and sanitise it
            $post_search = sanitize_text_field($_POST['post_search']);
            $security_check = sanitize_text_field($_POST['security']);

            // Check nonce first
            $check = wp_create_nonce('advanced_search_submit');
            if ($security_check == $check) {

                // Prep result array 
                $results = array();

                if (!empty($post_search)) {

                    // Prep filters array
                    $filters=array();

                    // Fetch post types available
                    $post_types = get_post_types(array('public' => true));
                    $post_types = array_values($post_types);

                    // get pre search results from hook
                    $pre_filtered_result = apply_filters('aaskp_pre_search', $post_search);
                    if (is_array($pre_filtered_result)) {
                        $results = array_merge($results, $pre_filtered_result);
                    }

                    $results = array_merge(
                        $results, // pre search
                        $this->AASKP_getUsers($post_search,$filters),  // user results
                        $this->AASKP_getPostsAndPages($post_search,$filters), // post types
                        $this->AASKP_getMedia($post_search,$filters), // attachments
                        $this->AASKP_getTaxonomies($post_search,$filters), // taxonomies
                        $this->AASKP_getPostMeta($post_search,$filters), // post meta
                        $this->AASKP_getComments($post_search,$filters) // comments
                    );

                    $post_filtered_result = apply_filters('aaskp_post_search', $post_search);
                    if (is_array($post_filtered_result)) {
                        $results = array_merge($results, $post_filtered_result);
                    }

                    _e(json_encode(array('result'=>'success', 'data'=>$results, 'count'=>count($results), 'search'=>$post_search)), "advanced-admin-search");
                }
                else {
                    _e(json_encode(array('result'=>'success', 'count'=> count($results))), "advanced-admin-search");
                }
            } else {
                _e(json_encode(array('result'=>'error', 'message'=>'Invalid Request')), "advanced-admin-search");
            }
        } else {
            _e(json_encode(array('result'=>'none', 'message'=>'Refine Your Search')), "advanced-admin-search");
        }
        wp_die();
    }


    public function AASKP_getUsers($post_search,$filters,$flag = false) {

        $output = array();
        if($flag) {
            if(!empty($filters['status'])  || !empty($filters['user']) || !empty($filters['metaKey']) || !empty($filters['metaValue'])) {
                return $output;
            }
        }
        
        $users = get_users(array('search' => "*{$post_search}*", 'fields' => array('display_name', 'user_registered', 'id')));
        foreach ($users as $user) {
            $url = admin_url('user-edit.php?user_id='.$user->id);
            $getUser = get_userdata($user->id);
            $role = $getUser->roles;
            foreach ($role as $value) {
                $output[] = array(
                    'link'      => $url,
                    'title'     => $user->display_name,
                    'status'    => $value,
                    'info'      => $user->user_registered,
                    'image'     => esc_url(get_avatar_url($user->id)),
                    'type'    => 'User'
                );
            }
        }
        return $output;
    }

    public function AASKP_getPostsAndPages($post_search,$filters,$flag = false) {
        $output = array();
        $args = array( 
            's'                 => $post_search,
            'post_type'         => 'any',
            'post_status'       => array('publish', 'pending', 'draft',
                'auto-draft', 'future', 'private', 'trash'),
            'posts_per_page'    => -1
        );
        if($flag) {
            if(!empty($filters['status'])) {
                $args['post_status']=$filters['status'];
            }
            if(!empty($filters['user'])) {
                if (is_numeric($filters['user'])) {
                    $args['author'] = $filters['user'];
                } else {
                    $args['author_name'] = $filters['user'];
                }
            }

            $metaQuery= array();
            if (!empty($filters['metaKey'])) {
                $metaQuery=array(
                    'key' => $filters['metaKey']
                );              
            }
            if (!empty($filters['metaValue'])) {
                $value=$filters['metaValue'];
                $matchType=$filters['matchType'];
                switch($matchType) {
                    case "starting":
                    $compare='REGEXP';
                    $value='^'.$value; 
                    break;
                    case "ending":
                    $compare='REGEXP';
                    $value=$value.'$';
                    break;
                    case 0:
                    case "exact":
                    $compare='=';
                }
                $metaValueQuery=array(
                    'value' => $value,
                    'compare' => $compare                 
                );
                $metaQuery= array_merge($metaQuery,$metaValueQuery);  
            }
            if(!empty($metaQuery)) {

                $args['meta_query']=array($metaQuery);
            }
        }
        $query = new \WP_Query($args);

        $posts = $query->posts;
        foreach ($posts as $post) {
            $url = admin_url('post.php?post='.$post->ID.'&action=edit');
            $post_type = $post->post_type;
            $output[]=array(
                'link'      => $url,
                'title'     => $post->post_title,
                'status'    => $post->post_status,
                'info'      => 'Type: '.$post->post_type,
                'type'    => 'Post'
            );
        }
        return $output;
    }

    public function AASKP_getMedia($post_search,$filters,$flag = false) {
        $output = array();
        $args=array(
            's'                 => $post_search,
            'post_type'         => 'attachment',
            'post_status'       => 'inherit',
            'posts_per_page'    => -1
        );
        if($flag) {
            if(!empty($filters['metaKey']) || !empty($filters['metaValue'])) {
                return $output;
            }
            if(!empty($filters['status'])) {
                $args['post_status']=$filters['status'];   
            }

            if(!empty($filters['user'])) {
                if(is_numeric($filters['user'])) {
                    $args['author'] = $filters['user'];
                }
                else {
                    $args['author_name'] = $filters['user'];
                }
            }
        }
        $mediaPosts = get_posts($args);
        foreach ($mediaPosts as $mediaPost) {
            $url = admin_url('post.php?post='.$mediaPost->ID.'&action=edit');
            $post_type = $mediaPost->post_type;
            $image_url = wp_get_attachment_image_src($mediaPost->ID);
            $image_url_main = $image_url[0];
            if($image_url_main == false){
                $image_url = '';
            }
            $output[]=array(
                'link' => $url,
                'title' => $mediaPost->post_title,
                'info' => $mediaPost->post_date,
                'image'=> $image_url_main,
                'type' => 'Media'
            );
        }
        return $output;
    }

    public function AASKP_getTaxonomies($post_search,$filters,$flag = false) {
        $output = array();
        if($flag) {
            if(!empty($filters['status']) || !empty($filters['user']) || !empty($filters['metaKey']) || !empty($filters['metaValue'])) {
                return $output;
            }
        }

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
                'type' => 'Taxonomy'
            );
        }
        return $output;
    }

    public function AASKP_getPostMeta($post_search,$filters,$flag = false) {
        $output = array();
        if($flag) {
            if(!empty($filters['user']) || !empty($filters['user']) || !empty($filters['metaKey']) || !empty($filters['metaValue'])) {
                return $output;
            }
        }

        global $wpdb;
        $metaTableName = $wpdb->prefix . "postmeta";
        $queryMeta = $wpdb->prepare(
            "SELECT * FROM {$metaTableName} WHERE meta_key LIKE %s OR meta_value LIKE %s;",
            array(
                '%' . $post_search . '%',
                '%' . $post_search . '%',
            )
        );
        $postMeta = $wpdb->get_results($queryMeta);

        foreach ($postMeta as $meta) {
            $url = admin_url('post.php?post='.$meta->post_id.'&action=edit');
            $getPost = get_post($meta->post_id);
            if($flag)
            {  
                if ((strpos($meta->meta_key, $post_search) !== false)) {
                    if(empty($filters['status'])) {
                        $output[]=array(
                            'link'      => $url,
                            'title'     => $getPost->post_title,
                            'status'    =>'PostMeta',
                            'info'      =>  strip_tags($meta->meta_value),
                            'type'    => 'PostMeta'
                        );
                    }
                } else {
                    if(empty($filters['status'])) {
                        $output[]=array(
                            'link' => $url,
                            'title' => $getPost->post_title,
                            'status' => $getPost->post_status,
                            'info' => 'Meta Value: '.strip_tags($meta->meta_value),
                            'type' => 'PostMeta'
                        );
                    } else {
                        if(strcmp($getPost->post_status, $filters['status']) == 0) {
                            $output[]=array(
                                'link' => $url,
                                'title' => $getPost->post_title,
                                'status' => $getPost->post_status,
                                'info' => 'Meta Value: '.strip_tags($meta->meta_value),
                                'type' => 'PostMeta'
                            );
                        }  
                    }
                } 
            } else {
                if (strpos($meta->meta_key, $post_search) !== false) {

                    $output[]=array(
                        'link' => $url,
                        'title' => $getPost->post_title,
                        'status' =>'PostMeta',
                        'info' =>  strip_tags($meta->meta_value),
                        'type' => 'PostMeta'
                    );
                } else {

                    $output[]=array(
                        'link' => $url,
                        'title' => $getPost->post_title,
                        'status' => $getPost->post_status,
                        'info' => 'Meta Value: '.strip_tags($meta->meta_value),
                        'type' => 'PostMeta'
                    );
                }
            }
        }
        return $output;
    }

    public function AASKP_getComments($post_search,$filters,$flag = false) {
        $output = array();
        $args=array('search' => $post_search);
        if($flag){
            if(!empty($filters['status']) || !empty($filters['metaKey']) || !empty($filters['metaValue'])) {
                return $output;
            }  
            if(!empty($filters['user'])) {
                $user=$filters['user'];
                if(is_numeric($user)) {
                    $args['user_id'] = $user;
                } else {
                    $userData=get_user_by('login', $user);
                    $args['user_id'] = $userData->ID;
                }
            }
        }
        $comments = get_comments($args);
        foreach ($comments as $comment) {
            $url = admin_url('edit-comments.php');
            $output[]=array(
                'link' => $url,
                'title' => $comment->comment_author_email,
                'info' =>'Comment'. $comment->comment_content,
                'type' => 'Comment'
            );
        }
        return $output;
    }

    public static function getInstance() {
        if(self::$instance == false){
            self::$instance = new AASKP_searchResults();
        }
        return self::$instance;
    }
}
AASKP_searchResults::getInstance();