<?php
namespace Kuroit\AdvancedAdminSearch;
class AASKP_SearchBox
{
    public function __construct()
    {
        //hooks to add search box to admin bar
        add_action('admin_bar_menu', array($this, 'AASKP_displaySearchBox'));
        add_action('in_admin_header', array($this, 'AASKP_displayInput'));
    }
    
    public function AASKP_displaySearchBox()
    {
        global $wp_admin_bar;
        $wp_admin_bar->add_menu(
            array(
                'id' => 'search_form',
                'parent' => 'top-secondary',
                'title' => __('<ul class="post_search_box">
                <li class="advance_search_box"><span class="dashicons dashicons-search"
                onclick="AASKP_displayInputBox()"></span><div class="sf-d"><input
                name="autocomplete" type="text" placeholder="Search Database"
                id="post_search_box" autocomplete="off" style="height:20px;margin:5px
                0;"/><label for="submit"><span class="dashicons dashicons-search"
                style="display:block !important;"></span></label><input type="submit"
                id="submit" name="search" value="Search" style="display:none;"><div
                class="ajax-loader" style="display: none;"><img src="'.plugin_dir_url
                (__FILE__).'image/loading.gif" class="img-responsive" /></div><ul
                class="search_list"></ul></div></li>
                </ul>', 'advanced-admin-search')
            )
        );
    }
    public function AASKP_displayInput()
    {
        _e('<div class="sf-m"><div id="search_fields" style="display:none;">
        <input type="text" placeholder="Search Database" id="mobile_search_fields"
        autocomplete="off" style="line-height:1em;"/><label for="submit"><span
        class="dashicons dashicons-search"></span></label><input type="submit"
        id="submit" name="search" value="Search" style="display:none;"></div>
        <div class="ajax-loading"><img src="'.plugin_dir_url(__FILE__).'image/loading.gif"
        class="img-responsive" /></div><ul class="mobile_search_list">
        </ul></div>', 'advanced-admin-search');
    }
}
