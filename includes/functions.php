<?php

if(!function_exists("debug")) {
    /**
     * Debug function
     *
     * @param array $data
     * @param boolean $exit
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function debug($data = array(), $exit = FALSE) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if($exit) {
            exit();
        }
    }
}

if(!function_exists("get_range_time")) {
    /**
     * Get range tome
     *
     * @param integer $lower
     * @param integer $upper
     * @param integer $step 
     * @param string $format
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function get_range_time($lower = 0, $upper = 23, $step = 1, $format = NULL) {
        if ($format === NULL) {
            $format = get_option('time_format'); // 9:30pm
        }

        $start = new \DateTime('00:00');
        $range = 24 * $step; // 24 hours * 30 mins in an hour

        if($step == 1) {
            $interval = '1H';
        } else if($step > 1) {
            $interval = 60/$step.'M';
        }
         
        for ($i = 0; $i < $range-1; $i++) {
            $start->add(new \DateInterval('PT'.$interval));
            $key = $start->format('H:i');
            $times[$key] = $start->format($format);
        }

        return $times;
    }
}

if(!function_exists("mazi_get_single_value")) {
    /**
     * convert multible to single value from array given
     *
     * @param array $val
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function mazi_get_single_value($val) {
        return $val[0];
    }
}

if(!function_exists("mazi_convert_single_value")) {
    /**
     * Convert array with item multiple to single value
     *
     * @param array $data
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function mazi_convert_single_value($data = array()) {
        $result = array_map('mazi_get_single_value', $data);
        return $result;
    }
}

if(!function_exists("is_edit_page")) {
    /**
     * Convert array with item multiple to single value
     *
     * @param array $data
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    function is_edit_page($new_edit = null){
        global $pagenow;
        
        //make sure we are on the backend
        if (!is_admin()) return false;
        
        if($new_edit == "edit")
            return in_array( $pagenow, array( 'post.php',  ) );
        elseif($new_edit == "new") //check for new post page
            return in_array( $pagenow, array( 'post-new.php' ) );
        else //check for either new or edit
            return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
    }
}