<?php
class Mazi_Comment_Model {
    /**
     * The average of total comments
     * @var string
     */
    static $meta_avg_key = 'rating_avg';

    /**
     * Total rating count
     * @var string
     */
    static $meta_count_key = 'rating_count';

    /**
     * Count by rating value
     * @var string
     */
    static $meta_rating_key = 'rating_meta';

    /**
     * Set rating meta for WP_Post
     * 
     * @param integer $post_id
     * @return void
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function set_rating_meta($post_id) {
        $rating_data = self::get_ratings_data($post_id);
        update_post_meta($post_id, self::$meta_avg_key, $rating_data['avg']);
        update_post_meta($post_id, self::$meta_count_key, $rating_data['count']);
        update_post_meta($post_id, self::$meta_rating_key, json_encode($rating_data['meta']));
    }

    /**
     * Get rating meta data
     *
     * @param WP_Post $post
     * @param boolean $meta [assign rating meta data]
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function assign_rating_meta(&$post, $meta = FALSE) {
        $post->rating_avg = (float)get_post_meta($post->ID, self::$meta_avg_key, TRUE);
        $post->rating_count = (int)get_post_meta($post->ID, self::$meta_count_key, TRUE);
        if($meta) {
            $post->rating_meta = json_decode(get_post_meta($post->ID, self::$meta_rating_key, TRUE));
        }
    }

    /**
     * Calculate rating data
     * - total rating
     * - total user comment (approved)
     * - average rating
     *
     * @param integer $post_id
     * @return array
     * @author Paul <paul.passionui@gmail.com>
     * @since 1.0.0
     */
    static function get_ratings_data( $post_id ) {
        $comments   = get_approved_comments( $post_id );
        $count      = sizeof($comments);
        $meta       = array_fill_keys(['1', '2', '3', '4', '5'], 0);

        if ( $comments ) {
            $i = 0;
            $total = 0;
            foreach( $comments as $comment ){
                $rate = get_comment_meta( $comment->comment_ID, 'rating', true );
                if( isset( $rate ) && '' !== $rate ) {
                    $i++;
                    // Total raring
                    $total += $rate;
                    // Update meta counter by rating number
                    if(isset($meta[$rate])) {
                        $meta[$rate]++;
                    }
                }
            }
    
            if ( 0 === $i ) {
                return ['count' => $count, 'avg' => 0, 'meta' => $meta];
            } else {
                return ['count' => $count, 'avg' => round( $total / $i, 1 ), 'meta' => $meta];
            }
        } else {
            return ['count' => 0, 'avg' => 0, 'meta' => $meta];
        }
    }
}