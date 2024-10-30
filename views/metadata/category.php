<!-- Color -->
<div class="form-field term-group">
    <label for="color"><?php echo __('Color', 'mazi-wp');?></label>
    <input type="text" name="color" id="color" class="color-field" value="" />
</div>
<!-- Icon -->
<div>    
    <label for="Icon" term-group><?php echo __('Icon', 'mazi-wp');?></label>
    <button class="button"><i class="<?php echo $term->icon ? esc_attr($term->icon) : 'fas fa-star'; ?>"></i></button>
    <input data-placement="right" class="icp iconpicker button" name="icon" value="fa-archive">
</div>
<!-- Image -->
<div class="form-field term-group">
    <label for="featured-image"><?php _e('Image', 'mazi-wp'); ?></label>
    <input type="hidden" id="featured-image" name="featured_image" value="">
    <div id="featured-image-wrapper"></div>
    <p>
        <input type="button" class="button button-secondary btn-featured-image" id="btn-featured-image" name="media_button" value="<?php _e( 'Add Image', 'mazi-wp' ); ?>" />
        <input type="button" class="button button-secondary btn-featured-image-reset" id="btn-featured-image-reset" name="media_remove" value="<?php _e( 'Remove Image', 'mazi-wp' ); ?>" />
    </p>
</div>