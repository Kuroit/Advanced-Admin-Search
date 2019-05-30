<?php
namespace Kuroit\AdvancedAdminSearch;

class AASKP_SearchData
{
    public function __construct()
    {
        add_action('admin_bar_menu', array($this, 'AASKP_desktopSearchJavascript'));
        add_action('in_admin_header', array($this, 'AASKP_mobileSearchJavascript'));

        // Hook to add javascript.
        add_action('admin_enqueue_scripts', array($this, "AASKP_adminJavascript"));
        add_action('wp_enqueue_scripts', array($this, "AASKP_adminJavascript"));

    }
    public function AASKP_desktopSearchJavascript()
    {
        echo '<script type="text/javascript"> AASKP_desktopSearch(); </script>';
    }
    public function AASKP_mobileSearchJavascript()
    {
        echo '<script type="text/javascript"> AASKP_mobileSearch(); </script>';
    }
    function AASKP_adminJavascript()
    {
        wp_enqueue_style( 'advanced_admin_search_style',  plugin_dir_url( __FILE__ ) . 'css/style.css' );
        wp_register_script('advanced_admin_desktop_search_script' , plugin_dir_url( __FILE__ ) . 'js/jquery-admin-desktop-search.js' );
        wp_register_script('advanced_admin_mobile_search_script' , plugin_dir_url( __FILE__ ) . 'js/jquery-admin-mobile-search.js' );

        $params = array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('advanced_search_submit'),
            );

        wp_localize_script( 'advanced_admin_desktop_search_script', 'advanced_admin_search', $params );
        wp_localize_script( 'advanced_admin_mobile_search_script', 'advanced_admin_search', $params );

        wp_enqueue_script( 'advanced_admin_desktop_search_script' );
        wp_enqueue_script( 'advanced_admin_mobile_search_script' );

    }
}
