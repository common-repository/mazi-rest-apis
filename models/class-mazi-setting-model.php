<?php
class Mazi_Setting_Model {
    /**
     * Storing default options
     * @var array
     */
    static $combine_options = [];

    /**
     * Get Options
     * 
     * @param string $id
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    public static function get_options($id = '') {
        $options = array(
            'general' => [
                'title' => __('General', 'mazi-wp'),
                'options' => [
                    [
                        'name' => __('Label', 'mazi-wp'),
                        'desc' => __('The label of leftside menu', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'label',
                        'type' => 'text',
                        'default' => 'Mazi'
                    ],
                    [
                        'name' => __('Default Icon', 'mazi-wp'),
                        'desc' => __('The default icon will be use for meta data field', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'icon',
                        'type' => 'text',
                        'default' => 'fa fa-star',
                    ], 
                    [
                        'name' => __('Post Per Page', 'mazi-wp'),
                        'desc' => __('Total number post per page will be displayed', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'per_page',
                        'type' => 'text',
                        'default' => 20,
                    ],
                    [
                        'name' => __('Color Default', 'mazi-wp'),
                        'desc' => __('Then color dault use for icon', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'color',
                        'type' => 'text',
                        'default' => '#E5634D',
                    ],  
                    [
                        'name' => __('Color Option', 'mazi-wp'),
                        'desc' => __('List color is separated by comma', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'color_option',
                        'type' => 'text',
                        'default' => '#E5634D, #5DADE2, #A569BD, #58D68D, #FDC60A, #3C5A99, #5D6D7E',
                    ]
                ]
            ],
            'map' => [
                'title' => __('Mapping Fields', 'mazi-wp'),
                'options' => [
                    [
                        'name' => __('Category Image', 'mazi-wp'),
                        'desc' => __('Please define the meta field name of category image', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'category_field_image',
                        'default' => 'featured_image',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Category Color', 'mazi-wp'),
                        'desc' => __('Please define the meta field name of category color', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'category_field_color',
                        'default' => 'color',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Category Icon', 'mazi-wp'),
                        'desc' => __('Please define the meta field name of category icon', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'category_field_icon',
                        'default' => 'icon',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Post Image', 'mazi-wp'),
                        'desc' => __('Please define the meta field name of post image', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'post_field_image',
                        'default' => 'featured_image',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Post Color', 'mazi-wp'),
                        'desc' => __('Please define the meta field name of post color', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'post_field_color',
                        'default' => 'color',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Post Icon', 'mazi-wp'),
                        'desc' => __('Please define the meta field name of icon', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'post_field_icon',
                        'default' => 'icon',
                        'type' => 'text',
                    ],
                ] 
            ],
            'app' => [
                'title' => __('Mobile', 'mazi-wp'),
                'options' => [
                    [
                        'name' => __('Ios ID', 'mazi-wp'),
                        'desc' => __('ID of your IOS app'),
                        'id'   =>  Mazi::$option_prefix. 'app_ios',
                        'default' => '1501601754',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Android ID', 'mazi-wp'),
                        'desc' => __('ID of your Android app', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'app_android',
                        'default' => '4975166712910870703',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('API version', 'mazi-wp'),
                        'desc' => __('Information of API version', 'mazi-wp'),
                        'id'   =>  Mazi::$option_prefix. 'version',
                        'default' => '1.0.0',
                        'type' => 'text',
                    ]
                ]
            ]
        );

        return isset($options[$id]) ? $options[$id] : $options;
    }

    /**
     * Get combine options
     *
     * @return array
     * @param string $tab_id [If there have tab id > Just getting setting tab]
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_combine_options($tab_id = '') {
        $options = array();

        if($tab_id) {
            $data = self::get_options($tab_id);    
            foreach($data['options'] as $setting) {
                $options[$setting['id']] = $setting;
            }
        } else {
            $data = self::get_options();
            foreach($data as $tab_id => $tab_data) {
                foreach($tab_data['options'] as $setting) {
                    $options[$setting['id']] = $setting;
                }
            }
        }        
            
        return $options;
    }

    /**
     * Install default value when the plugin is activated
     *
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function install() {
        $options = self::get_combine_options();

        // Check and insert default 
        foreach($options as $option) {
            $value = get_option($option['id']);
            if(!$value) {
                update_option($option['id'], $option['default']);
            }
        }
    }

    /**
     * Get single option without prefix
     *
     * @param string $id
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_option($id = '') {
        $key = Mazi::$option_prefix.$id;
        $option = get_option($key);

        // Get default option if can't found data setting
        if(!$option) {
            if(empty(self::$combine_options)) {
                self::$combine_options = self::get_combine_options();
            }            
            $option = isset(self::$combine_options[$id]['default']) ?
                self::$combine_options[$key]['default'] : '';
        }

        return $option;
    }

    /**
     * Get single option prefix
     *
     * @param string $id
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_option_prefix($id = '') {
        $option = get_option($id);

        // Get default option if can't found data setting
        if(!$option) {
            if(empty(self::$combine_options)) {
                self::$combine_options = self::get_combine_options();
            }            
            $option = isset(self::$combine_options[$id]['default']) ?
                self::$combine_options[$id]['default'] : '';
        }

        return $option;
    }

    /**
     * Get color option as array
     *
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     */
    static function get_color_option() {
        $color_option = self::get_option('color_option');
        $color_option = explode(',', $color_option);
        if(!empty($color_option)) {
            foreach($color_option as &$color) {
                $color = trim($color);
            }
        }

        return $color_option;
    }
}