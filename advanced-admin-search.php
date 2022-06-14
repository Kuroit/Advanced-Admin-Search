<?php
/**
 * Plugin Name:       Advanced Admin Search
 * Plugin URI:        https://www.kuroit.com/product/advanced-admin-search
 * Description:       Easily search everything in WordPress admin panel from one single search field.
 * Version:           1.1.6
 * Author:            Kuroit
 * Author URI:        https://www.kuroit.com
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 */

/* The Advanced Admin Search Plugin
 * Advanced Admin Search is a wordpress plugin which adds extra searching feature into admin bar.
 * Easily search everything in WordPress admin panel from one single search field.
 */

namespace Kuroit\AdvancedAdminSearch;

if (!defined('ABSPATH')) {
    die();// Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'searchresults.php';
require_once plugin_dir_path(__FILE__) . 'menu.php';
require_once plugin_dir_path(__FILE__) . 'scripts.php';
require_once plugin_dir_path(__FILE__) . 'searchbox.php';

// Re-direct on plugin activation
add_action( 'activated_plugin',  'Kuroit\AdvancedAdminSearch\AASKP_plugin_activated' );

function AASKP_plugin_activated($plugin){
    if ( plugin_basename( __FILE__ ) === $plugin ) {
        exit( wp_safe_redirect( 'admin.php?page=advanced-admin-search' ) );
    }
}

class AASKPadvancedAdminSearch
{

    public function __construct() // instantiation
    {

        // add input box in admin panel for searching.
        new AASKP_SearchBox();

        // get the searching data like admin menu, media labraries, post, pages
        // and Hook to add javascript.
        new AASKP_SearchData();

        // get the search results
        new AASKP_searchResults();

    }
}

// fire it up
new AASKPadvancedAdminSearch();

