<?php
use Kuroit\AdvancedAdminSearch\AASKP_searchResults as searchclass;

// Add menu item for the plugin
add_action('admin_menu', 'aaskp_menu_items');
function aaskp_menu_items(){
    add_submenu_page('tools.php','Advance Admin Search', 'Advanced Admin Search', 'manage_options', 'advanced-admin-search', 'aaskp_search_page_callback');
}

// Pre search call to fetch user & roles
add_filter( 'aaskp_pre_search', 'aaskp_pre_search_callback');
function aaskp_pre_search_callback($user) {
    $output = array();
    
    if(is_numeric($user)){
        $userData=get_user_by('id', $user);
    } else {
        $userData=get_user_by('login', $user);
    }

    $url = admin_url('user-edit.php?user_id='.$userData->ID);
    $role = $userData->roles;
    
    foreach ($role as $value) {
            $output[] = array( 
                'link' => $url,
                'title' => $userData->display_name,
                'status' => $value,
                'info' => $userData->user_registered,
                'image' => esc_url(get_avatar_url($userData->ID)),
                'type' => 'User'
            );
    }

    return $output;
}


function aaskp_search_page_callback() { 

    // Pick all data from query string and sanitise it
    $selectedKeyword = (isset($_GET['keyword'])) ? sanitize_text_field($_GET['keyword']) : '';
    $selectedFilter = (isset($_GET['select'])) ? sanitize_text_field($_GET['select']) : '';
    $selectedStatus = (isset($_GET['status'])) ? sanitize_text_field($_GET['status']) : '';

    $selectedUser = (isset($_GET['user'])) ? sanitize_text_field($_GET['user']) : '';
    $selectedMetaKey = (isset($_GET['metaKey'])) ? sanitize_text_field($_GET['metaKey']) : '';
    $selectedMetaVal = (isset($_GET['metaValue'])) ? sanitize_text_field($_GET['metaValue']) : '';
    $selectedMatchType = (isset($_GET['matchType'])) ? sanitize_text_field($_GET['matchType']) : '';

    // Advance search form view - TODO: switch to separate template later on
    ?>
    <div class="advanced-admin-wrapper">
        <label for="post_search_box1"><h2 class="page_title_AASKP">Advanced Admin Search: Full Search</h2></label>
        <form action="" method="GET">
            <p>Search Filters:</p>
            <input type="hidden" class="input_page" name="page" value="advanced-admin-search">
            
            <input name="keyword" class="input_search" type="text" value="<?php if(isset($selectedKeyword) && $selectedKeyword!='') echo(esc_attr($selectedKeyword)); ?>" placeholder="Search in database" id="post_search_box1" autocomplete="off" required />
            
            <select name="select" class="select1">
                <option value="All" <?php if(isset($selectedFilter) && $selectedFilter=='All') echo 'selected="selected"';?>>All</option>
                <option value="Users" <?php if(isset($selectedFilter) && $selectedFilter=='Users') echo 'selected="selected"';?>>User</option>
                <option value="PostsAndPages" <?php if(isset($selectedFilter) && $selectedFilter=='PostsAndPages') echo 'selected="selected"';?>>Post and Pages</option>
                <option value="Media" <?php if(isset($selectedFilter) && $selectedFilter=='Media') echo 'selected="selected"';?>>Media</option>
                <option value="Taxonomies" <?php if(isset($selectedFilter) && $selectedFilter=='Taxonomies') echo 'selected="selected"';?>>Taxonomy</option>
                <option value="PostMeta" <?php if(isset($selectedFilter) && $selectedFilter=='PostMeta') echo 'selected="selected"';?>>Postmeta</option>
                <option value="Comments" <?php if(isset($selectedFilter) && $selectedFilter=='Comments') echo 'selected="selected"';?>>Comments</option>
            </select>
            
            <select name="status" class="select1">
                <option value="">Select Status</option>
                <option value="publish" <?php if(isset($selectedStatus) && $selectedStatus=='publish') echo 'selected="selected"';?>>Publish</option>
                <option value="future" <?php if(isset($selectedStatus) && $selectedStatus=='future') echo 'selected="selected"';?>>Future</option>
                <option value="draft" <?php if(isset($selectedStatus) && $selectedStatus=='draft') echo 'selected="selected"';?>>Draft</option>
                <option value="pending" <?php if(isset($selectedStatus) && $selectedStatus=='pending') echo 'selected="selected"';?>>Pending</option>
                <option value="private" <?php if(isset($selectedStatus) && $selectedStatus=='private') echo 'selected="selected"';?>>Private</option>
                <option value="trash" <?php if(isset($selectedStatus) && $selectedStatus=='trash') echo 'selected="selected"';?>>Trash</option>
                <option value="auto-draft" <?php if(isset($selectedStatus) && $selectedStatus=='auto-draft') echo 'selected="selected"';?>>Auto-draft</option>
                <option value="inherit" <?php if(isset($selectedStatus) && $selectedStatus=='inherit') echo 'selected="selected"';?>>Inherit</option>
            </select>

            <input type="text" input class="input_page input_search" name="user" placeholder="Filter by author ID or username" value="<?php if(isset($selectedUser) && $selectedUser!='') echo(esc_attr($selectedUser)); ?>"/></br>

            <div class="advanced_search">
                <p>Advanced Admin search meta filters:</p>
                <p><span><input type="checkbox" id="open_advance_search" /><label for="open_advance_search" class="highlighted_adv_label"><i>Check this box to enable meta search</i></label></span></p>

                <input type="text" input class="input_key input_search" name="metaKey" placeholder="Filter by meta key" value="<?php if(isset($selectedMetaKey) && $selectedMetaKey!='') echo(esc_attr($selectedMetaKey)); ?>" disabled="" title="Check the box above to enable meta search fields" />

                <input type="text" input class="input_value input_search" name="metaValue" placeholder="Filter by meta value" value="<?php if(isset($selectedMetaVal) && $selectedMetaVal!='') echo(esc_attr($selectedMetaVal)); ?>" disabled="" title="Check the box above to enable meta search fields" />

                <select name="matchType" input class="select1" disabled="" title="Check the box above to enable meta search fields">
                    <option value="exact" <?php if(isset($selectedMatchType) && $selectedMatchType=='exact') echo 'selected="selected"';?>>Exact</option>
                    <option value="starting" <?php if(isset($selectedMatchType) && $selectedMatchType=='starting') echo 'selected="selected"';?>>Starting with</option>
                    <option value="ending" <?php if(isset($selectedMatchType) && $selectedMatchType=='ending') echo 'selected="selected"';?>>Ending with</option>
                </select>

                <input type="submit" class="btn1 button_AASKP" id="submit1" name="submit" value="SEARCH">
            </div>
        </form>
    </div>
    <?php

    // Get search data if keyword is available
    if (isset($selectedKeyword)) {
        $postSearch = sanitize_text_field($selectedKeyword);
        
        if (!empty($postSearch)) {

            // Filter keys from search form
            $keys=array('select','status','user','metaKey','metaValue','matchType');
            
            // Prep filters data from form
            $filters=array_fill_keys($keys,"");
            if (isset($selectedFilter) && $selectedFilter != ''){
                $filters['select']=sanitize_text_field($selectedFilter);    
            }
            if (isset($selectedStatus) && $selectedStatus != ''){
                $filters['status']=sanitize_text_field($selectedStatus);
            }
            if (isset($selectedUser) && $selectedUser != ''){
                $filters['user']=sanitize_text_field($selectedUser);
            }
            if (isset($selectedMetaKey) && $selectedMetaKey != ''){
                $filters['metaKey']=sanitize_text_field($selectedMetaKey);  
            }
            if (isset($selectedMetaVal) && $selectedMetaVal != ''){
                $filters['metaValue']=sanitize_text_field($selectedMetaVal);  
            }
            if (isset($selectedMatchType) && $selectedMatchType != ''){
                $filters['matchType']=sanitize_text_field($selectedMatchType);  
            }
            
            // Get all results
            $results = array();
            $post_types = get_post_types(array('public' => true));
            $post_types = array_values($post_types);

            // Apply local pre-search hook
            if(!empty($filters['user'])){
                $user=$filters['user'];
                $pre_filtered_result = apply_filters('aaskp_pre_search', $user);
            
                if (is_array($pre_filtered_result)) {
                    $results = array_merge($results, $pre_filtered_result);
                }
            }

            // Init search and get search based data
            $object = searchclass::getInstance();
            switch($filters['select']) {
                case "Users":
                    $results=array_merge(
                            $results, 
                            $object->AASKP_getUsers($postSearch,$filters,true)
                        );
                    break;
                case "PostsAndPages":
                    $results=array_merge(
                            $results, 
                            $object->AASKP_getPostsAndPages($postSearch,$filters,true)
                        );
                    break;
                case "Media":
                    $results=array_merge(
                            $results, 
                            $object->AASKP_getMedia($postSearch,$filters,true)
                        );
                    break;
                case "Taxonomies":
                    $results=array_merge(
                            $results, 
                            $object->AASKP_getTaxonomies($postSearch,$filters,true)
                        );
                    break;
                case "PostMeta":
                    $results=array_merge(
                            $results, 
                            $object->AASKP_getPostMeta($postSearch,$filters,true)
                        );
                    break;
                case "Comments":
                    $results=array_merge(
                            $results, 
                            $object->AASKP_getComments($postSearch,$filters,true)
                        );
                    break;
                case "All":
                case 0:
                    $results = array_merge(
                            $results, // pre search
                            $object->AASKP_getUsers($postSearch,$filters,true),  // user results
                            $object->AASKP_getPostsAndPages($postSearch,$filters,true), // post types
                            $object->AASKP_getMedia($postSearch,$filters,true), // attachments
                            $object->AASKP_getTaxonomies($postSearch,$filters,true), // taxonomies
                            $object->AASKP_getPostMeta($postSearch,$filters,true), // post meta
                            $object->AASKP_getComments($postSearch,$filters,true) // comments
                        );
            }
            
            // Apply local post-search hook 
            $post_filtered_result = apply_filters('aaskp_post_search', $postSearch);
            if (is_array($post_filtered_result)) {
                $results = array_merge($results, $post_filtered_result);   
            }

            // Start working on pagination
            $numPerPage=10;
            $numPages = ceil(count($results)/$numPerPage);
            if (isset($_GET['offset'])) {
                if(!empty($_GET['offset'])) {
                    $offset=sanitize_text_field($_GET['offset']);
                }
                else {
                    $offset=1;
                }
            }
            else {
                $offset=1;
            }
            $start=($offset-1)*$numPerPage;  
            $end=min(($offset*$numPerPage),count($results));

            // Print total search count
            if(count($results) > 1){
                // More than one result
                _e('<h2 class="search_result_count">Total <span>'.count($results).'</span> Search Results Found</h2>', 'advanced-admin-search');
            }elseif(count($results) == 0){
                // No result found
                _e('<h2 class="search_result_count">No Search Result Found.</h2>', 'advanced-admin-search');
            }else{
                // Single result
                _e('<h2 class="search_result_count">Only <span>'.count($results).'</span> Search Result Found</h2>', 'advanced-admin-search');
            }
            
            // Start printing search results table
            echo "<table class=\"search_list2 table_fxd\" cellpadding=\"10\" border=\"1\">";
            if(count($results)==0){ 
                // No results found - refine it!
                echo "<tr><td colspan=\"5\" class=\"result_row\">Please refine your search</td></tr>";

                // End table
                echo "</table>";
            }else{  
                // Display the found results 
                echo "<tr class=\"search_rows tb-heading\"><th class=\"table_type\">Type</th><th class=\"table_type1\">Thumbnail</th><th class=\"table_type2\">Title</th><th class=\"table_type1\">Status</th><th class=\"table_type2\">Info</th></tr>";

                for($i=$start;$i<$end;$i++){
                    $values=$results[$i];
                    if(array_key_exists("status",$values)){
                        $status = $values['status'];
                    }
                    else {
                        $status="[NO STATUS]";
                    }
                    $title = $values['title'];
                    $link = $values['link'];
                    
                    $info = $values['info'];
                    $types=$values['type'];
                    
                    $image = '';
                    if(array_key_exists("image",$values)){
                        $image = $values['image'];
                        $images = __("<img class='image_thumb1' src='".$image."'>", "advanced-admin-search");
                    }else{
                        $images="[NO IMAGE]";
                    }
                    
                    if($status=="administrator") {
                        $status="admin";
                    }
                    if($title ==''){
                        $title="No title";
                    } 
                    
                    switch($types) {
                        case "User":
                            $type = __("<label class='color' style='background: #dc3545;'>".$types."</label>", "advanced-admin-search");
                        break;
                    case "Post":
                            $type = __("<label class='color' style='background: #03254d;'>".$types."</label>", "advanced-admin-search");
                        break;
                    case "Media":
                            $type = __("<label class='color' style='background: #dec610;'>".$types."</label>", "advanced-admin-search");
                        break;
                    case "Taxonomy":
                            $type = __("<label class='color' style='background: #32a852;'>".$types."</label>", "advanced-admin-search");
                        break;
                    case "PostMeta":
                            $type = __("<label class='color' style='background: #c7550e;'>".$types."</label>", "advanced-admin-search");
                        break;
                    case "Comment":
                            $type = __("<label class='color' style='background: #6c757d;'>".$types."</label>", "advanced-admin-search");
                        break;
                    }

                    // Print search row
                    _e("<tr class=\"search_rows search_result_rows\" onclick=\"clickLink('".$link."');\"><td class=\"td_rows\">".$type."</td><td class=\"td_rows\">".$images."</td><td><p class=\"list_title1\">".$title."</p></td><td class=\"td_rows\"><p class=\"list_status1\">".$status."</p></td><td><p class=\"list_type1\">".$info."</p></td></a></tr>", "advanced-admin-search");
                }

                // End table
                echo "</table>";

                // Prep url for pagination
                $adminUrl= get_admin_url();
                $url= __($adminUrl."tools.php?page=advanced-admin-search&keyword=".$postSearch."&select=".$filters['select']."&status=".$filters['status']."&user=".$filters['user']."&metaKey=".$filters['metaKey']."&metaValue=".$filters['metaValue']."&matchType=".$filters['matchType'], "advanced-admin-search");
                
                //pagination for results found
                createPagination($url,$offset,$numPages);
                
            }
        }
    }
}
function createPagination($url,$offset,$numPages)
{
    $previousPage = $offset - 1;
    $nextPage = $offset + 1;
    $secondLast=$numPages-1;
    if($numPages>1) {

        echo "<div class=\"center\"><div class=\"pagination\">";
        if($offset==1) {
            echo "<a class=\"isDisabled\">PREV</a>";
        } else {
            _e("<a href='".$url."&offset=".$previousPage."' class=\"btn-color\">PREV</a>", "advanced-admin-search"); 
        }

        // Results less than 10
        if ($numPages <= 10){  
            for ($counter = 1; $counter <= $numPages; $counter++){
                if ($counter == $offset) {
                    _e("<a href=\"".$url."&offset=".$counter."\" class=\"active\">".$counter."</a>", "advanced-admin-search"); 
                 }else{
                    _e("<a href=\"".$url."&offset=".$counter."\">".$counter."</a>", "advanced-admin-search");
                 }
            }
        }

        // Results greater than 10
        elseif ($numPages > 10){
            if($offset <= 4){

                for ($counter = 1; $counter < 8; $counter++){ 
                    if ($counter == $offset) {
                        _e("<a href=\"".$url."&offset=".$counter."\" class=\"active\">".$counter."</a>", "advanced-admin-search");
                    }else{
                        _e("<a href=\"".$url."&offset=".$counter."\">".$counter."</a>", "advanced-admin-search");
                    }
                }
                _e("<a>...</a>", "advanced-admin-search");
                _e("<a href=\"".$url."&offset=".$secondLast."\">".$secondLast."</a>", "advanced-admin-search");
                _e("<a href=\"".$url."&offset=".$numPages."\">".$numPages."</a>", "advanced-admin-search");

            } elseif ($offset > 4 && $offset < ($numPages - 4)) { 

                _e("<a href=\"".$url."&offset=1\">1</a>", "advanced-admin-search");
                _e("<a href=\"".$url."&offset=2\">2</a>", "advanced-admin-search");
                _e("<a>...</a>", "advanced-admin-search");
                for ($counter = ($offset - 2);$counter <= ($offset + 2);$counter++) { 
                    if ($counter == $offset) {
                        _e("<a href=\"".$url."&offset=".$counter."\" class=\"active\">".$counter."</a>", "advanced-admin-search");
                    }else{
                        _e("<a href=\"".$url."&offset=".$counter."\">".$counter."</a>", "advanced-admin-search");
                    }                  
                }
                _e("<a>...</a>", "advanced-admin-search");
                _e("<a href=\"".$url."&offset=".$secondLast."\">".$secondLast."</a>", "advanced-admin-search");
                _e("<a href=\"".$url."&offset=".$numPages."\">".$numPages."</a>", "advanced-admin-search");

            } else {
                _e("<a href=\"".$url."&offset=1\">1</a>", "advanced-admin-search");
                _e("<a href=\"".$url."&offset=2\">2</a>", "advanced-admin-search");
                _e("<a>...</a>", "advanced-admin-search");
                for ($counter=($numPages - 6);$counter <=$numPages;$counter++
                     ) {
                    if ($counter == $offset) {
                        _e("<a href=\"".$url."&offset=".$counter."\" class=\"active\">".$counter."</a>", "advanced-admin-search");
                    }else{
                        _e("<a href=\"".$url."&offset=".$counter."\">".$counter."</a>", "advanced-admin-search");
                    }                   
                }
            }
        }

        if($offset==$numPages) {
            echo "<a class=\"isDisabled\">NEXT</a>";
        }
        else {
            _e("<a href=\"".$url."&offset=".$nextPage."\" class=\"btn-color\">NEXT</a>", "advanced-admin-search");
        }
        echo "</div></div>";
    }
}
?>