<?php
include_once MAZI_PATH.'/models/class-mazi-setting-model.php';

class Mazi_Category_Model {
    /**
     * Define variables for metadata fields
     * @var array
     */
    public static $metadata = array(
        'image' => array(
            'format' => 'text',
            'mapping' => ''
        ),
        'icon' => array(
            'format' => 'text',
            'mapping' => ''
        ),
        'color' => array(
            'format' => 'text',
            'mapping' => ''
        )
    );

    /**
     * Get mapping fieldds
     *
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @version 1.0.0
     */
    public static function get_mapping_field() {
        foreach(self::$metadata as $key => &$value) {
            switch($key) {
                case 'image':
                    $new_key = Mazi_Setting_Model::get_option('category_field_image');
                break;
                case 'icon':
                    $new_key = Mazi_Setting_Model::get_option('category_field_icon');
                break;
                case 'color':
                    $new_key = Mazi_Setting_Model::get_option('category_field_color');
                break;
            }

            if($new_key && $new_key != $key) {
                $value['mapping'] = $new_key;
            } else {
                $value['mapping'] = $key;
            }
        }
    }

    /**
     * Assign metadata
     *
     * @param WP_Term_Object $term
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function assign_metadata(&$term) {
        if(is_object($term) && $term->term_id) {
            $metadata = get_term_meta($term->term_id, '', TRUE);    
            // Common fields
            if(is_array($metadata) && !empty($metadata)) {
                // Convert single value
                $metadata = mazi_convert_single_value($metadata);            
                
                foreach(self::$metadata as $key => $value) {
                    $new_key = $value['mapping'];
                    if(isset($metadata[$new_key])) {
                        switch($value['format']) {
                            case 'integer':
                                $term->{$key} = absint($metadata[$new_key]);
                                break;
                            case 'text':
                                $term->{$key} = esc_attr($metadata[$new_key]);
                                break;     
                            case 'json':
                                $term->{$key} = json_decode(stripslashes($metadata[$new_key]));
                        }
                    } else {
                        $term->{$key} = NULL;
                    }                    
                }
            } else {
                foreach(self::$metadata as $key => $value) {
                    $term->{$key} = NULL;
                }
            }
        }
    }

    /**
     * Set metadata
     *
     * @param integer $post_id
     * @param $_POST $_post
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function set_metadata($term_id = 0, $_post = array()) { 
        foreach(self::$metadata as $key => $value) {   
            if (array_key_exists($key, $_post)) {
                update_term_meta($term_id, $key, $_post[$key]);
            }
        }
    }
}