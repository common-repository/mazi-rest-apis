<?php 
include_once MAZI_PATH.'/models/class-mazi-comment-model.php';

class Mazi_Comment_Controller extends WP_REST_Controller 
    implements Mazi_Interface_Controller {

    public function __construct() {
        $this->namespace = 'mazi/v1';

        // Register > callback function for action wp_insert_comment
        add_action('wp_insert_comment', array($this, 'after_save_comment'));
    }

    /**
     * Register Rest API router
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/comments', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'list' ]
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
        try {
            $post = Mazi::valid_post($request['post_id']);

            $args = [
                'comment_status' => 1,
                'post_id' => $post->ID
            ];
    
            $query  = new WP_Comment_Query($args);
            $rows   = $query->get_comments();
    
            if(is_array($rows) && !empty($rows)) {
                foreach($rows as &$comment) {
                    $comment->rate = get_comment_meta( $comment->comment_ID, 'rating', true );
                }
            }            
    
            $response = rest_ensure_response([
                'success' => TRUE,
                'data' => $rows
            ]);
    
            return $response;
        } catch (Exception $e) {
            return new WP_Error( 'rest_invalid_post', $e->getMessage(), array( 'status' => 400 ) );    
        }                 
    }

    /**
     * Callback after comment
     *
     * @param integer $comment_id
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function after_save_comment($comment_id) {
        try {
            $post_id = isset($_POST['post']) ? absint($_POST['post']) : 0;
            $rating = isset($_POST['rating']) ? absint($_POST['rating']) : 0;

            // Check valid post 
            $post = Mazi::valid_post($post_id);
            
            // Update rating meta data
            if(add_comment_meta( $comment_id, 'rating', $rating )) {
                Mazi_Comment_Model::set_rating_meta($post->ID);
            } 
        } catch (Exception $e) {
            // Do nothings
        }
    }
}