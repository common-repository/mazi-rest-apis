<?php
/*
Plugin Name: Mazi REST APIs
Plugin URI: https://passionui.com
Description: This plugin help create REST APIs for Mazi mobile application
Version: 1.0.0
Author: Paul
Author URI: https://www.facebook.com/passionui/
License: GPL2
Text Domain: mazi-wp
Domain Path: /languages
*/

/**
 * Copyright (c) 2019 Paul (email: paul.passionui@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */
if ( !defined( 'MAZI_PATH' ) ) {
    define( 'MAZI_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
}

if (!defined('ABSPATH')) exit;

/**
 * Mazi class
 *
 * @class Mazi The class that holds the entire Mazi plugin
 */
class Mazi {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Refers to a single instance of this class
     *
     * @var self
     */
    private static $instance = null;

    /**
     * The plugin url
     * 
     * @var string
     */
    public $plugin_url;

    /**
     * The plugin path
     *
     * @var string
     */
    public $plugin_path;

    /**
     * The theme directory path
     *
     * @var string
     */
    public $theme_dir_path;

    /**
     * The post type name
     *
     * @var string
     */
    static $post_type = 'post';

    /**
     * Prefix setting
     * @var string
     */
    static $option_prefix = 'mazi_';

    /**
     * Creates or returns an instance of this class.
     *
     * @return  Mazi A single instance of this class.
     */
    public static function get_instance() {
        if ( null == self::$instance ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Initializes the Mazi() class
     *
     * Checks for an existing Mazi() instance
     * and if it doesn't find one, creates it.
     */
    public function __construct() {
        // Register script & style
        add_action('admin_enqueue_scripts', array($this, 'load_scripts')); 

        // Load libs
        $this->load_libraries();

        // Load language
        add_action('init', array($this, 'load_language'));

        // Rest API init
        add_action('rest_api_init', array( $this, 'rest_api_init' ) );
        add_filter('jwt_auth_token_before_dispatch', array($this, 'auth_token_before_dispatch'), 10, 2);        
        add_filter('wp_rest_cache/allowed_endpoints', array($this, 'wprc_add_acf_posts_endpoint'), 10, 2);

        // Called when plugin is activated
        register_activation_hook(__FILE__, array($this, 'activate'));
    }            

    public function wprc_add_acf_posts_endpoint( $allowed_endpoints ) {
        if ( ! isset( $allowed_endpoints[ 'mazi/v1' ] )) {
            $allowed_endpoints[ 'mazi/v1' ][] = 'init';
        }
        return $allowed_endpoints;
    }

    /**
     * Load libraries
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_libraries() {
        include_once dirname(__FILE__) . '/includes/functions.php';
        include_once dirname(__FILE__) . '/models/class-mazi-setting-model.php';
        
        /**
         * Load admin function
         */
        if (is_admin()) {   
            include_once dirname(__FILE__) . '/controllers/class-mazi-admin-setting-controller.php';
            new Mazi_Admin_Setting_Controller();
        }
    }

    /**
     * Load admin scripts needed
     * - Just register script only 
     * - Only load when need
     * - Css
     * - Javascript
     * - https://developer.wordpress.org/reference/functions/wp_register_script/
     * 
     * @author Paul<paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_scripts() {
        $assets_url = $this->plugin_url() . '/assets';

        /**
         * Load 3rd scripts
         * - Fontawesome
         * - https://www.jqueryscript.net/other/Simple-FontAwesome-Icon-Picker-Plugin-Bootstrap.html
         */
        wp_register_style('fontawesome', 'https://use.fontawesome.com/releases/v5.7.1/css/all.css', array(), $this->version);        
        wp_register_style('fontawesome-iconpicker-css', $assets_url . '/css/fontawesome-iconpicker.min.css', array(), $this->version);        
        wp_register_script('fontawesome-iconpicker-js', $assets_url . '/js/fontawesome-iconpicker.min.js', array(), $this->version);        

        /**
         * Load admin scripts
         * - JS
         * - CSS
         */
        wp_register_style('mazi-admin-css', $assets_url . '/css/admin.css', array(), $this->version);                
        wp_enqueue_script('mazi-admin-js', $assets_url . '/js/admin.js', array(), $this->version);        

        /**
         * Load variable
         * - Use for js file
         */
        wp_localize_script( 'mazi-admin-js', 'mazi_vars', array(
            'admin_ajax' => admin_url( 'admin-ajax.php' ),
            'option' => [
                'color_option' => Mazi_Setting_Model::get_color_option(),
                'map_use' => Mazi_Setting_Model::get_option('map_use'),
                'map_center' => [Mazi_Setting_Model::get_option('gmap_center_lat'), Mazi_Setting_Model::get_option('gmap_center_long')],
                'map_zoom' => (int) Mazi_Setting_Model::get_option('gmap_zoom')
            ]
        ));
    }

    /**
     * Init reset API
     * 
     * @param WP_REST_Server $server Server request data
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function rest_api_init($server) {
        // Define endpoints
        $endpoints = array('category', 'post', 'wishlist', 'comment', 'setting', 'auth');

        // Include interface for controller
        include_once dirname(__FILE__) . '/includes/class-mazi-interface-controller.php';
        
        // Loop register routers & related class
        foreach($endpoints as $endpoint) {
            $file = dirname(__FILE__) . '/controllers/class-mazi-'.$endpoint.'-controller.php';
            // Register routes base on controller include
            if(file_exists($file)) {
                include_once $file;
                $class_name = 'Mazi_'.ucfirst($endpoint).'_Controller';                
                $controller = new $class_name();
                $controller->register_routes();
            } else {
                debug($file);
            }                    
        }
    }

    /**
     * Authentication callback function after login
     * - Data before token dispatch
     *
     * @param array $data
     * @param WP_User $user
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function auth_token_before_dispatch($data, $user) {
        include_once dirname(__FILE__) . '/models/class-mazi-user-model.php';
        $respone = Mazi_User_Model::refactor_user_data($user);
        $respone['token'] = $data['token'];
        return $respone;
    }

    /**
     * Called when plugin is activated
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function activate() {
        flush_rewrite_rules();

        update_option(self::$option_prefix.'installed', time());
        update_option(self::$option_prefix.'version', $this->version);

        if (is_admin()) {
            // Install default settings            
            Mazi_Setting_Model::install();
        }
    }

    /**
     * Load language
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function load_language() {
        load_plugin_textdomain('mazi-wp', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    /**
     * Check post type
     * - Check same format with post id
     * 
     * @param integer $post
     * @return Object
     * @author Paul <paul.passionui@gmail.com>
     */
    static function valid_post($post_id) {
        $post_id = absint($post_id);
        if ( $post_id <= 0 ) {
            throw new Exception(__( 'Invalid ID.', 'mazi-wp'));
        }

        $post = get_post( $post_id);
        
        if ( empty( $post ) || empty( $post->ID ) || $post->post_type !== Mazi::$post_type) {
            throw new Exception(__( 'Invalid data.', 'mazi-wp'));
        }

        return $post;
    }

    /**
     * Get the plugin url.
     *
     * @return string
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function plugin_url() {
        if ($this->plugin_url) {
            return $this->plugin_url;
        }

        return $this->plugin_url = untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Get the plugin path.
     *
     * @return string
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function plugin_path() {
        if ($this->plugin_path) return $this->plugin_path;

        return $this->plugin_path = untrailingslashit(plugin_dir_path(__FILE__));
    }
} // Mazi

/**
 * Initialize the plugin
 *
 * @return \Mazi
 */
Mazi::get_instance();
