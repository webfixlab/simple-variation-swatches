<?php

// Display pre-saved admin notices
function svsw_display_notice(){
    global $svsw__;

    // Display notices
    if( isset( $svsw__['notice'] ) ){
        foreach( $svsw__['notice'] as $notice ){
            echo wp_kses_post( $notice );
        }
    }

    if( isset( $_POST['svsw_tab'] ) ){
    ?>
    <div id="message" class="updated notice notice-success">
        <p>Settings saved successfully.</p><button type="button" class="notice-dismiss">
    </div>
    <?php
    }
}

/**
 * Calculate date difference and some other accessories
 * @param $key | option meta key
 * @param $notice_interval | Alarm after this day's difference
 * @param @skip_ | skip this value
 */
function svsw_date_diff( $key, $notice_interval, $skip_ = '' ){
    $value = get_option( $key );
    if( empty( $value ) || $value == '' ){

        // if skip value is meta value - return false
        if( $skip_ != '' && $skip_ == $value ) return false;        
        else{
            $c = date_create( date( 'Y-m-d' ) );
            $d = date_create( $value );
            $dif = date_diff( $c, $d );
            $b = (int) $dif->format( '%d' );
            
            // if days difference meets minimum given interval days - return tru
            if( $b >= $notice_interval ) return true;
        }
    }else add_option( $key, date( 'Y-m-d' ) );
    return false;
}

// display what you want to show in the notice
function svsw_client_feedback_notice(){
    global $svsw__;

    // get current page
    $page = sanitize_url( $_SERVER['REQUEST_URI'] );

    // dynamic extra parameter adding beore adding new url parameters
    $page .= strpos( $page, '?' ) !== false ? '&' : '?'; ?>
    <div class="notice notice-info is-dismissible">
        <h3><?php echo esc_html( $svsw__['plugin']['name'] ); ?></h3>
        <p>
            Excellent! You've been using <strong><a href="<?php echo esc_url( $svsw__['plugin']['wporg_url'] ); ?>"><?php echo esc_html( $svsw__['plugin']['name'] ); ?></a></strong> for a while. We'd appreciate if you kindly rate us on <strong><a href="<?php echo esc_url( $svsw__['plugin']['review_url'] ); ?>">WordPress.org</a></strong>
        </p>
        <p>
            <a href="<?php echo esc_url( $svsw__['plugin']['wporg_url'] ); ?>" class="button-primary">Rate it</a> <a href="<?php echo esc_url( $page ); ?>rate_svsw=done" class="button">Already Did</a> <a href="<?php echo esc_url( $page ); ?>rate_svsw=cancel" class="button">Cancel</a>
        </p>
    </div><?php
}

// if this is correctly within our plugin screen scope
function svsw_in_screen_scope(){
    global $svsw__;

    $screen = get_current_screen();

    // check with our plugin screens
    if( in_array( $screen->id, $svsw__['plugin']['screen'] ) ) return true;
    elseif( in_array( $screen->base, array(
            'product_page_product_attributes',
            'edit-tags',
            'term'
        ) ) && $screen->post_type == 'product' ) return true;
    else return false;
}

// Notice - if woocommerce is deactivated - auto deactivate this plugin
function svsw_show_wc_new_inactive_notice(){
    global $svsw__; ?>
    <div class="error">
        <p>
            <a href="<?php echo esc_url( $svsw__['plugin']['wporg_url'] ); ?>" target="_blank"><?php echo esc_html( $svsw__['plugin']['name'] ); ?></a> plugin has been deactivated due to deactivation of <a href="<?php echo esc_url( $svsw__['plugin']['wc_url'] ); ?>" target="_blank">WooCommerce</a> plugin
        </p>
    </div><?php
}

// Notice - this plugin needs woocommerce plugin first
function svsw_show_wc_inactive_notice(){
    global $svsw__; ?>
    <div class="error">
        <p>
            Please install and activate <a href="<?php echo esc_url( $svsw__['plugin']['wc_url'] ); ?>" target="_blank">WooCommerce</a> plugin first
        </p>
    </div><?php
}

// Client feedback - rating
function svsw_get_client_feedback(){
    global $svsw__;

    if( isset( $_GET['rate_svsw'] ) ){
        $task = sanitize_title( $_GET['rate_svsw'] );
        
        if( $task == 'done' ) update_option( 'rate_svsw', "done" );
        else if( $task == 'cancel' ) update_option( 'rate_svsw', date( 'Y-m-d' ) );
        return;
    }

    if( svsw_date_diff( 'rate_svsw', $svsw__['plugin']['notice_interval'], 'done' ) ){
        // show notice to rate us after 15 days interval
        add_action( 'admin_notices', 'svsw_client_feedback_notice' );
    }
}

// Top level menu callback function
function svsw_render_admin_settings() {

    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) return;
  
    // show error/update messages
    settings_errors( 'wporg_messages' );

    // Display admin html content
    include( SVSW_PATH . 'templates/admin/settings.php' );
}

// Save all admin notices for displaying later
function svsw_handle_admin_notice(){
    global $svsw__;

    // check scope, without it return
    if( ! svsw_in_screen_scope() ) return;

    // Buffer only the notices
    ob_start();
    do_action( 'admin_notices' );
    $content = ob_get_contents();
    ob_get_clean();
    
    // Keep the notices in global $svsw__;
    array_push( $svsw__['notice'], $content );

    // Remove all admin notices as we don't need to display in it's place
    remove_all_actions( 'admin_notices' );
}

/**
 * Admin menu icon and notice title CSS styles
 * CHECK AGAIN - MISSED SOMETHING
 * NOT DONE
 */
function svsw_add_menu_icon_style() {
    ?>
    <style>
        #toplevel_page_svsw-settings img {
            width: 18px;
            opacity: 1 !important;
        }
        .notice h3{
            margin-top: .5em;
            margin-bottom: 0;
        }
    </style>
    <?php
}

// Check conditions before actiavation of the plugin
function svsw_pre_activation(){
    $plugin = 'simple-variation-swatches/simple-variation-swatches.php';

    // check if WC is active
    $is_wc_active = is_plugin_active( 'woocommerce/woocommerce.php' );

    // check if our plugin is active
    $is_svsw_active = is_plugin_active( $plugin );

    if( ! $is_wc_active ){
        
        if( $is_svsw_active ){
            deactivate_plugins( $plugin );
            add_action( 'admin_notices', 'svsw_show_wc_new_inactive_notice' );
        } else add_action( 'admin_notices', 'svsw_show_wc_inactive_notice' );

        return false;
    }

    svsw_get_client_feedback();
    return true;
}

// Add Settings to WooCommerce > Settings > Products > WC Multiple Cart
function svsw_add_plugin_action_links( $links ){
    global $svsw__;
	$action_links = array();

	$action_links['settings'] = sprintf( '<a href="%s">Settings</a>', admin_url( 'admin.php?page=svsw-settings' ) );

	return array_merge( $action_links, $links );
}

function svsw_add_plugin_desc_meta( $links, $file ){
    
    // if it's not Role Based Product plugin, return
    if ( plugin_basename( SVSW ) !== $file ) return $links;

    global $svsw__;
	$row_meta = array();

	$row_meta['docs'] = sprintf( '<a href="%s">Docs</a>', esc_url( $svsw__['plugin']['documentation'] ) );
	$row_meta['apidocs'] = sprintf( '<a href="%s">Support</a>', esc_url( $svsw__['plugin']['request_quote'] ) );
    
	return array_merge( $links, $row_meta );
}

function svsw_frontend_scripts(){
    global $svsw__;

    /**
     * check wheather to apply frontend style and script 
     * 
     * if not skip
     */
    
    // get saved settings data, if any
    $data = get_option( 'svsw_settings' );
    
    wp_enqueue_style( 'svsw-frontend', plugin_dir_url( SVSW ) . 'assets/svsw-frontend.css', array(), $svsw__['plugin']['version'], 'all' );

    // also script
    wp_register_script( 'svsw-frontend', plugin_dir_url( SVSW ) . 'assets/svsw-frontend.js', array( 'jquery' ), $svsw__['plugin']['version'], true );
            
    // wp_enqueue_script( 'svsw-frontend' );
    wp_enqueue_script( 'svsw-frontend', plugin_dir_url( SVSW ) . 'assets/svsw-frontend.js', array( 'jquery' ), $svsw__['plugin']['version'], false );
    
    // localize script
    if( isset( $script['localize'] ) ){
        wp_localize_script( 'svsw-frontend', 'wcsvw_frontend', array(
            'svsw' => 'yes'
        ) );
    }
}

// Register and enqueue a custom stylesheet in the WordPress admin.
function svsw_load_admin_scripts() {
    global $svsw__;

    // check scope, without it return
    if( ! svsw_in_screen_scope() ) return;
    
    // enqueue style
    wp_register_style( 'svsw_admin_style', plugin_dir_url( SVSW ) . 'assets/admin/svsw-admin-style.css', false, $svsw__['plugin']['version'] );
    
    wp_enqueue_style( 'svsw_admin_style' );
    wp_enqueue_style( 'wp-color-picker' );
    
    wp_enqueue_script( 'jquery' );
    
    // load media uploader script
    wp_enqueue_media();

    wp_enqueue_script( 'svsw_admin_script', plugin_dir_url( SVSW ) . 'assets/admin/svsw-admin-script.js', array( 'wp-color-picker' ), false, true );
    
    $var = array(
        'ajaxurl'       => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce('ajax-nonce')
    );

    // apply hook for editing localized variables in admin script
    $var = apply_filters( 'svsw_update_admin_local_val', $var );
    
    wp_localize_script( 'svsw_admin_script', 'svsw_admin_data', $var );
}

// Add menu and submenu pages
function svsw_add_dashboar_menu() {

    // Main menu
    add_menu_page( 
        'Variation Swatch',
        'Variation Swatch',
        'manage_options',
        'svsw-settings',
        'svsw_render_admin_settings',
        plugin_dir_url( SVSW ) . 'assets/images/admin-icon.svg',
        57
    );

    // settings submenu - settings
    add_submenu_page(
        'svsw-settings',
        'Variation Swatch - Settings',
        'Settings',
        'manage_options',
        'svsw-settings'
    );
}

// save admin settings
function svsw_save_settings(){
    // if( ! check_admin_referer( 'svsw_save' ) ) return; // does not work
    if( ! isset( $_POST['svsw_nonce_field'] ) || ! wp_verify_nonce( $_POST['svsw_nonce_field'], 'svsw_save' ) ) return;

    $data = array();

    if( isset( $_POST['attr_to_swatches'] ) ) $data['attr_to_swatches'] = sanitize_key( $_POST['attr_to_swatches'] );
    
    if( isset( $_POST['hide_attr_name'] ) ) $data['hide_attr_name'] = sanitize_key( $_POST['hide_attr_name'] );

    if( isset( $_POST['svsw_admin_type'] ) ) $data['svsw_admin_type'] = sanitize_key( $_POST['svsw_admin_type'] );

    if( isset( $_POST['svsw_size'] ) ) $data['svsw_size'] = sanitize_key( $_POST['svsw_size'] );
    
    if( isset( $_POST['svsw_font_size'] ) ) $data['svsw_font_size'] = sanitize_key( $_POST['svsw_font_size'] );

    update_option( 'svsw_settings', $data );
}