<?php 
include_once MAZI_PATH.'/models/class-mazi-post-model.php';
include_once MAZI_PATH.'/models/class-mazi-wishlist-model.php';
include_once MAZI_PATH.'/models/class-mazi-setting-model.php';

class Mazi_Post_Controller extends WP_REST_Controller 
    implements Mazi_Interface_Controller {

    public function __construct() {
        $this->namespace = 'mazi/v1';
        $this->rest_base = 'post';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base.'/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
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
                    ]
                ]
            ]
        ]);

        register_rest_route( $this->namespace, $this->rest_base.'/view', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'view' ],
                'args'     => [
                    'id' => [
                        'description'       => __( 'ID.', 'mazi-wp' ),
                        'type'              => 'integer',
                        'default'           => 1,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => 'rest_validate_request_arg',
                        'minimum'           => 1
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * Get list category
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        $per_page = isset($request['per_page']) ? absint($request['per_page']) 
            : absint(Mazi_Setting_Model::get_option('per_page'));

        $args = [
            'post_type'     => Mazi::$post_type,
            'post_status'   => 'publish',
            'paged'         =>  isset($request['page']) ? absint($request['page']) : 1,
            'posts_per_page' => $per_page
        ];
        
        // Keyword 
        if(isset($request['s']) && $request['s'] != '') {
            $args['s'] = sanitize_text_field($request['s']);
        }

        // Sort
        if(isset($request['orderby']) && $request['orderby'] != '' 
            && isset($request['order']) && $request['order'] != '') {
            $args['orderby'] = sanitize_sql_orderby($request['orderby']);
            $args['order'] = sanitize_text_field($request['order']);
        }

        // Category
        if(isset($request['category']) && $request['category'] != '') {
            $args['tax_query'][] = [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => is_array($request['category']) ? $request['category'] : absint($request['category'])
            ];     
        }

        // Color search
        if(isset($request['color']) && $request['color'] != '') {
            $hash = substr($request['color'], 0, 1);
            if($hash !== '#') {
                $request['color'] = sprintf('#%s', $request['color']);
            }
            
            $args['meta_query'][] = [
                'key'     => 'color',
                'value'   => sanitize_text_field($request['color']),
                'compare' => '=',
            ];
        }
        
        $query  = new WP_Query($args);
        $posts  = $query->get_posts();
        
        $paged       = absint($args['paged']);
        $total_posts = absint($query->found_posts);    

        if ( $total_posts < 1 ) {
            // Out-of-bounds, run the query again without LIMIT for total count.
            unset( $args['paged'] );
            $count_query = new WP_Query();
            $count_query->query( $args );
            $total_posts = (int) $count_query->found_posts;
        }

        $max_pages = ceil( $total_posts / (int) $query->query_vars['posts_per_page'] );

        if ( $paged > $max_pages && $total_posts > 0 ) {
            return new WP_Error( 'rest_invalid_page_number', __( 'The page number requested is larger than the number of pages available.', 'mazi-wp' ), ['status' => 400] );
        }

        if(is_array($posts) && !empty($posts)) {
            foreach($posts as &$post) {
                unset($post->post_content);
                Mazi_Post_Model::assign_data_list($post);                
            }
        }            

        $response = rest_ensure_response([
            'success' => TRUE,
            'sort' => [
                [
                    'title' => __('Lastest Post', 'mazi-wp'),
                    'field' => 'post_date',
                    'value' => 'DESC'
                ],
                [
                    'title' => __('Oldest Post', 'mazi-wp'),
                    'field' => 'post_date',
                    'value' => 'ASC'
                ],
                [
                    'title' => __('Most Views', 'mazi-wp'),
                    'field' => 'comment_count',
                    'value' => 'DESC'
                ]                                 
            ],
            'pagination' => [
                'page' => $paged,
                'per_page' => $per_page,
                'max_page' => $max_pages,
                'total' => $total_posts,
            ],
            'data' => $posts
        ]);

        return $response;
    }

    /**
     * Get detail information of location
     *
     * @param int $id Location id
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0 
     */
    public function view($request) {
        try {
            $post = Mazi::valid_post($request['id']);
            Mazi_Post_Model::assign_data_view($post);
            
            // Wishlist && check authorized
            $user = wp_get_current_user();
        
            // Check authorized
            if($user->ID) {
                $post->wishlist = Mazi_Wishlist_Model::check_exist($user->ID, $post->ID);
            } else {
                $post->wishlist = FALSE;
            }

            // Return endpoint data
            $response = rest_ensure_response([
                'success' => TRUE,
                'data' => $post
            ]);
            return $response;

        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), array( 'status' => 400 ) );    
        }        
    }
}