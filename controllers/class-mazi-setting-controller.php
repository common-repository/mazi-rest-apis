<?php 
include_once MAZI_PATH.'/models/class-mazi-category-model.php';
include_once MAZI_PATH.'/models/class-mazi-post-model.php';
include_once MAZI_PATH.'/models/class-mazi-setting-model.php';

class Mazi_Setting_Controller extends WP_REST_Controller 
    implements Mazi_Interface_Controller {

    public function __construct() {
        $this->namespace = 'mazi/v1';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/setting/init', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'init' ],
            ]
        ]);

        register_rest_route( $this->namespace, '/setting/api', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'api' ],
            ]
        ]);
    }

    /**
     * For checking plugin has installed or hasn't yet
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function api($request)
    {
        $response = rest_ensure_response([
            'success' => TRUE,
            'data' => [
                'app_ios' => Mazi_Setting_Model::get_option('app_ios'),
                'app_android' => Mazi_Setting_Model::get_option('app_android'),
                'version' => Mazi_Setting_Model::get_option('app_version'),
            ]
        ]);

        return $response;
    }
    
    /**
     * Get common setting data
     * - Basic setting
     * - Category data 
     * - Location data
     * - Featured data
     * - Setting data 
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function init($request) {
        // Get category data
        $categories = get_terms( 'category', [ 
            'parent' => 0, 
            'hide_empty' => 0
        ]); 

        if(is_array($categories) && !empty($categories)) {
            // Change mapping field by setting
            Mazi_Category_Model::get_mapping_field();
            foreach($categories as &$term) {
                $term->name = html_entity_decode($term->name);
                Mazi_Category_Model::assign_metadata($term);
            }
        }            
       
        // Recent Post
        $recent_posts = Mazi_Post_Model::get_recent_data([
            'fields' => ['ID', 'post_title', 'post_date', 'post_date_gmt', 'post_author']
        ]);

        // Basic Setting
        $settings = [
            'per_page' => (int) Mazi_Setting_Model::get_option('per_page'),
            'color_option' => Mazi_Setting_Model::get_color_option(),
            'list_mode' => Mazi_Setting_Model::get_option('list_mode')
        ];

        $response = rest_ensure_response([
            'success' => TRUE,
            'data' => [
                'categories' => $categories,
                'recent_posts' => $recent_posts,
                'top_posts' => $recent_posts,
                'settings' => $settings
            ]
        ]);

        return $response;
    }
}