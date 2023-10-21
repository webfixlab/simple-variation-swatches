<?php
global $svsw__;

// Include admin settings functions
include( SVSW_PATH . 'includes/core-data.php' );
include( SVSW_PATH . 'includes/admin-functions.php' );


add_action( 'admin_head', 'svsw_admin_head' );
add_action( 'init', 'svsw_init' );
add_action( 'admin_init', 'svsw_init_admin' );

register_activation_hook( SVSW, 'svsw_do_active' );
register_deactivation_hook( SVSW, 'svsw_do_deactive' );

// for admin haed - handle notice and add menu styles
function svsw_admin_head(){
    // process notice - to display it in specific section
    svsw_handle_admin_notice();

    // add admin menu icon styling
    svsw_add_menu_icon_style();
}

// Start the plugin
function svsw_init(){

    // check prerequisits
    if( !svsw_pre_activation() ) return;    

    // add extra links right under plug
    add_filter( 'plugin_action_links_' . plugin_basename( SVSW ), 'svsw_add_plugin_action_links' );
    add_filter( 'plugin_row_meta', 'svsw_add_plugin_desc_meta', 10, 2 );
    
    include( SVSW_PATH . 'includes/class/class-svswatch-admin.php' );
    include( SVSW_PATH . 'includes/class/class-svswatch.php' );

    // needs to be off the hook in the next version.
    include( SVSW_PATH . 'includes/functions.php' );

    // Enqueue admin script and style.
    add_action( 'wp_enqueue_scripts', 'svsw_frontend_scripts' );

    // // Enqueue admin script and style.
    add_action( 'admin_enqueue_scripts', 'svsw_load_admin_scripts' );

    // // Add admin menu page
    add_action( 'admin_menu', 'svsw_add_dashboar_menu' );
}

// start to active svsw plugin
function svsw_do_active(){
    // initial starter
    svsw_init();

    flush_rewrite_rules();
}

function svsw_do_deactive(){
    /**
     * do stuffs when you don't need this plugin anymore
     * also remember, don't do suffs that might be needed later
     */

    flush_rewrite_rules();
}

function svsw_init_admin(){
    // save admin settings data
    svsw_save_settings();
}
