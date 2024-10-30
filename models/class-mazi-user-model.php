<?php

class Mazi_User_Model {

    /**
     * Get user's profile
     *
     * @param int $id User ID
     * @return array
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_userdata($id) {
        $user = get_userdata($id);
        return self::refactor_user_data($user->data);
    }

    /**
     * Refactor user's data 
     *
     * @param \WP_User $user User's data
     * @return array
     * 
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function refactor_user_data($user) {
        $result = [
            'id' => (int) $user->ID,
            'user_email' => $user->user_email,
            'user_url' => $user->user_url,
            'user_nicename' => $user->user_nicename,       
            'user_level' => (int) get_user_meta($user->ID, 'wp_user_level', TRUE),       
            'description' => get_user_meta($user->ID, 'description', TRUE),
            'locale' => get_user_meta($user->ID, 'locale', TRUE),
            'display_name' => $user->display_name
        ];

        /**
         * User's image > access from gravatar.com 
         * format gravatar.com/avatar/md5({user_email})
         * Wordpress not support upload user's avata
         */
        $result['user_photo'] = 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($user->user_email)));

        return $result;
    }
}