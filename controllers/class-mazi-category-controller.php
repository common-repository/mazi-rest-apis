<?php 
include_once MAZI_PATH.'/models/class-mazi-wishlist-model.php';

class Mazi_Category_Controller extends WP_REST_Controller 
    implements Mazi_Interface_Controller {

    public function __construct() {
        $this->namespace = 'mazi/v1';
        $this->rest_base = 'category';
    }

    public function register_routes() {
        register_rest_route( $this->namespace, '/'.$this->rest_base.'/list', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ],
            ]
        ]);
    }
    
    /**
     * Get list category
     *
     * @param \WP_REST_Request $request Full data about  the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function list($request) {
        $terms = get_terms( 'category', [
            'parent' => 0, 
            'hide_empty' => 0
        ]); 

        if(is_array($terms) && !empty($terms)) {
            // Change mapping field by setting
            Mazi_Category_Model::get_mapping_field();

            // Assign Customize data
            foreach($terms as &$term) {
                Mazi_Category_Model::assign_metadata($term);
            }
        }            

        $response = rest_ensure_response([
            'success' => TRUE,
            'data' => $terms
        ]);

        return $response;
    }
}