<?php
include_once MAZI_PATH.'/models/class-mazi-setting-model.php';

class Mazi_Admin_Setting_Controller {    

    static $page = 'mazi';

    public function __construct() {
        add_action('admin_menu', array($this, 'add'));
    }

    /**
     * Create Setting Menu
     *
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public function add() {            
        add_options_page(__('Mazi Options', 'mazi-wp'), 
            __('Mazi Options', 'mazi-wp'), 
            'manage_options', self::$page, array($this, 'form')
        );
    }    

    /**
     * Load form UI
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     */
    public function form() {
        $mazi           = Mazi::get_instance();        
        $active_tab     = isset($_GET['tab'])       ? sanitize_text_field($_GET['tab'])     : 'general';
        $action         = isset($_GET['action'])    ? sanitize_text_field($_GET['action'])  : NULL;
        $page           = isset($_GET['page'])      ? sanitize_text_field($_GET['page'])    : NULL;

        if ($page === self::$page && $action) {
            if ($action === 'save') {  
                $post_options = Mazi_Setting_Model::get_combine_options($active_tab);          
                foreach ($post_options as $value) {                
                    if (isset($_REQUEST[$value['id']])) {
                        // Auto-paragraphs for any WYSIWYG    
                        $data = $value['type'] == 'wysiwyg' ? wpautop(sanitize_textarea_field($_REQUEST[$value['id']])) 
                            : trim(sanitize_textarea_field($_REQUEST[$value['id']]));                    
                        update_option($value['id'], $data);
                    } else {
                        delete_option($value['id']);
                    }
                }
            } else if ($action === 'reset') {
                // Do nothings        
            }        
        }

        /**
         * These variable will be used in views
         * @var $tab_options : use for setting form 
         * @var $tab_data : use for setting form 
         */
        $tab_options    = Mazi_Setting_Model::get_options();
        $tab_data       = Mazi_Setting_Model::get_options($active_tab);        
        include_once $mazi->plugin_path() . '/views/setting/option.php';
    }
}
