<?php 
include_once MAZI_PATH.'/models/class-mazi-post-model.php';

class Mazi_Wishlist_Controller extends WP_REST_Controller 
    implements Mazi_Interface_Controller {

    protected $user;

    public function __construct() {
        $this->namespace = 'mazi/v1';
        $this->user = wp_get_current_user();   
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/wishlist/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
                'permission_callback' => [ $this, 'permission_callback' ],
                'args'     => [
                    'page' => [
                        'description'       => __( 'Current page of the collection.', 'mazi-wp' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1,
                    ],
                    'per_page' => [
                        'description'       => __( 'Maximum number of items to be returned in result set.', 'mazi-wp' ),
                        'type'              => 'integer',
                        'default'           => (int) get_option( 'posts_per_page' ),
                        'minimum'           => 1,
                        'maximum'           => 100,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                    ],
                ]
            ]
        ]);

        register_rest_route( $this->namespace, '/wishlist/save', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'save' ],
                'permission_callback' => [ $this, 'permission_callback' ],
                'args'     => [
                    'post_id' => [
                        'description'       => __( 'Post ID.', 'mazi-wp' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1,
                    ]
                ]
            ]
        ]);

        register_rest_route( $this->namespace, '/wishlist/remove', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'remove' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        ]);

        register_rest_route( $this->namespace, '/wishlist/reset', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'permission_callback' => [ $this, 'permission_callback' ],
                'callback' => [ $this, 'reset' ],
            ]
        ]);    
    }
       
    /**
     * Check token permssion
     *
     * @return void
     * @author Paul <paul@hanbiro.com>
     * @since 1.0.0
     */
    public function permission_callback() {
        if(!$this->user->ID) {
            return new WP_Error( 'rest_permission', __( 'Permission denied', 'mazi-wp' ), 
['status' => 200] );
        }   

        return TRUE;
    }

    /**
     * Get wishlist data
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        $post_ids = get_user_meta($this->user->ID, Mazi_Wishlist_Model::$meta_key);       
        
        $args = [
            'post_type' => Mazi::$post_type,
            'post_status' => 'publish',
            'post__in' => !empty($post_ids) ? $post_ids : [0],
            'force_no_results' => true,
            'paged' => $request['page'],
            'posts_per_page' => $request['per_page'],
        ];
        
        $query       = new WP_Query($args);
        $posts       = $query->get_posts();
        $page        = (int) $args['paged'];
        $total_posts = (int) $query->found_posts;    

        if ( $total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $args );
            $total_posts = (int) $count_query->found_posts;
        }

        $max_pages = ceil( $total_posts / (int) $query->query_vars['posts_per_page'] );

        if ( $page > $max_pages && $total_posts > 0 ) {
            return new WP_Error( 'rest_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'mazi-wp' ), ['status' => 400] );
        }

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                Mazi_Post_Model::assign_data_list($post);                
            }
        }

        $response = rest_ensure_response([
            'success' => TRUE,
            'pagination' => [
                'page' => $page,
                'per_page' => (int) $request['per_page'],
                'max_page' => $max_pages,
                'total' => $total_posts,
            ],
            'data' => $posts,            
        ]);

        return $response;
    }

    /**
     * Save wishlist data 
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function save($request) {
        try {
            $post = Mazi::valid_post($request['post_id']);
            Mazi_Wishlist_Model::save($this->user->ID, $post->ID);

            // Return endpoint data
            $response = rest_ensure_response([
                'success' => TRUE,
                'message' => __('Saved wishlist successfully')
            ]);
            return $response;

        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400] );    
        } 
    }

    /**
     * Remove wishlist data
     * - Single
     * - Multiple
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function remove($request) {
        try {
            $post = Mazi::valid_post($request['post_id']);
            Mazi_Wishlist_Model::remove($this->user->ID, $post->ID);

            // Return endpoint data
            $response = rest_ensure_response([
                'success' => TRUE,
                'message' => __('Removed wishlist successfully', 'mazi-wp')
            ]);
            return $response;

        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), ['status' => 400] );    
        } 
    }

    /**
     * Reset all wishlist data
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function reset($request) {
        Mazi_Wishlist_Model::reset($this->user->ID);

        // Return endpoint data
        $response = rest_ensure_response([
            'success' => TRUE,
            'message' => __('Reset wishlist successfully', 'mazi-wp')
        ]);
        
        return $response;
    }
}