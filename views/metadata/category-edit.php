<!-- Color -->
<tr class="form-field term-colorpicker-wrap">
    <th scope="row"><label for="color"><?php echo __('Color', 'mazi-wp');?></label></th>
    <td>
        <input name="color" value="<?php echo $term->color ? esc_attr($term->color) : '#000'; ?>" class="color-field" id="color" />
    </td>
</tr>
<!-- Icon -->
<tr class="form-field term-colorpicker-wrap">
    <th scope="row"><label for="icon"><?php echo __('Icon', 'mazi-wp');?></label></th>
    <td>
        <button class="button"><i class="<?php echo $term->icon ? esc_attr($term->icon) : 'fas fa-star'; ?>"></i></button>
        <input data-placement="right" class="icp iconpicker button button-secondary"  name="icon" value="<?php echo $term->icon ? esc_attr($term->icon) : 'fas fa-star'; ?>">  
    </td>
</tr>
<!-- Image -->
<tr class="form-field term-group-wrap">
    <th scope="row">
        <label for="featured-image"><?php _e( 'Image', 'mazi-wp' ); ?></label>
    </th>
    <td>
        <input type="hidden" id="featured-image" name="featured_image" value="<?php echo esc_attr( $term->featured_image ); ?>">
        <div id="featured-image-wrapper">
        <?php if( $term->featured_image ) { ?>
            <div class='screen-thumb'>
                <?php echo wp_get_attachment_image( $term->featured_image, 'thumbnail' ); ?>
            </div>
        <?php } ?>
        </div>
        <p>
            <input type="button" class="button button-secondary btn-featured-image" id="btn-featured-image" name="media_button" value="<?php _e( 'Add Image', 'mazi-wp' ); ?>" />
            <input type="button" class="button button-secondary btn-featured-image-reset" id="btn-featured-image-reset" name="media_remove" value="<?php _e( 'Remove Image', 'mazi-wp' ); ?>" />
        </p>
    </td>
</tr>