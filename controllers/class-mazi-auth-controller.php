<?php 
include_once MAZI_PATH.'/models/class-mazi-user-model.php';

class Mazi_Auth_Controller extends WP_REST_Controller 
    implements Mazi_Interface_Controller {

    /**
     * User has athorized
     *
     * @var WP_User
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    protected $user;

    public function __construct() {
        $this->namespace = 'mazi/v1';        
    }
            
    /**
     * Register rounter
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/auth/reset_password', [
            [
                'methods'  => WP_REST_Server::CREATABLE,
                'callback' => [ $this, 'reset_password' ],
            ]
        ]);

        register_rest_route( $this->namespace, '/auth/user', [
            [
                'methods'  => WP_REST_Server::READABLE,
                'callback' => [ $this, 'user' ]
            ]
        ]);
    }
            
    /**
     * Reset user's password
     * - Send email + attach url reset password
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function reset_password($request) {
        $email = sanitize_email($request['email']);

        if(!is_email($email)) {
            return rest_ensure_response([
                'success' => FALSe,
                'msg' => __('Email invalid', 'mazi-wp')
            ]); 
        }

        $user_data = get_user_by('email',  $email);
        
        if ( !$user_data ) {
            return rest_ensure_response([
                'code' => 'auth_reset_password',
                'message' => __('User not found. Please correct your email again.', 'mazi-wp'),
                'data' => [
                    'status' => 403
                ]
            ]);
        }

        do_action('lostpassword_post');
        
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = get_password_reset_key( $user_data );
        
        $message = __('Someone requested that the password be reset for the following account:') . '\r\n\r\n';
        $message .= network_home_url( '/' ) . '\r\n\r\n';
        $message .= sprintf(__('Username: %s'), $user_login) . '\r\n\r\n';
        $message .= __('If this was a mistake, just ignore this email and nothing will happen.') . '\r\n\r\n';
        $message .= __('To reset your password, visit the following address:') . '\r\n\r\n';
        $message .= network_site_url('wp-login.php?action=rp&key=$key&login=' . rawurlencode($user_login), 'login');
        
        if ( is_multisite() ) {
            $blogname = $GLOBALS['current_site']->site_name;
        } else {
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }

        $title = sprintf( __('[%s] Password Reset'), $blogname );
        $title = apply_filters('retrieve_password_title', $title);
        $message = apply_filters('retrieve_password_message', $message, $key);

        if ( $message && !wp_mail($user_email, $title, $message)) {
            return rest_ensure_response([
                'code' => 'auth_reset_password',
                'message' => __('The e-mail could not be sent.') . __('Possible reason: your host may have disabled the mail() function...'),
                'data' => [
                    'status' => 403
                ]
            ]);
        }

        return rest_ensure_response([
            'success' => TRUE,
            'msg' => __('Check your email for the confirmation link.', 'mazi-wp')
        ]);  
    }

     /**
     * Return current user's profile
     *
     * @param \WP_REST_Request $request Full data about the request.
     * @return \WP_REST_Response
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function user($request) {
        $this->user = wp_get_current_user();
        
        // Check authorized
        if(!$this->user->ID) {
            return new WP_Error( 'rest_permission', __( 'Permission denied', 'mazi-wp' ), ['status' => 200] );
        } 

        $response = rest_ensure_response(
            Mazi_User_Model::refactor_user_data($this->user->data)
        );
        
        return $response;
    }
}