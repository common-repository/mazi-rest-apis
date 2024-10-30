<!-- Message Handler -->
<?php if (isset($_REQUEST['action']) && sanitize_text_field($_REQUEST['action']) === 'save') { ?>
    <div id="message" class="updated fade"><p><strong><?php echo __('Setting saved successfully', 'mazi-wp');?></strong></p></div>
<?php } ?>
<!-- Main Content -->
<div class="wrap">
    <div id="icon-options-general" class="icon32"></div>
    <h2><?php echo __('Settings', 'mazi-wp'); ?></h2>
    <h2 class="nav-tab-wrapper" style="margin-bottom: 5px;">
        <?php foreach($tab_options as $id => $tab) { ?>
        <a href="?page=mazi&tab=<?php echo esc_attr($id);?>" 
            class="nav-tab <?php echo $active_tab === $id ? 'nav-tab-active' : ''; ?>">
            <?php echo $tab['title'] ;?>
        </a>
        <?php } ?>
    </h2>
    <form  method="post">
        <div id="setting" class="ui-sortable meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            <?php foreach($tab_data['options'] as $option) { ?>
                            <tr>
                                <th scope="row">
                                    <label>
                                        <?php echo esc_html($option['name']); ?>
                                    </label>
                                </th>
                                <?php switch ($option['type']) { 
                                    // Teaxt Area
                                    case 'textarea':
                                    case 'wysiwyg':
                                        if($option['type'] == 'wysiwyg') {
                                            ?>
                                            <td>
                                                <?php wp_editor( stripslashes(Mazi_Setting_Model::get_option_prefix($option['id'])), $option['id']);?>
                                                <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                            </td>
                                            <?php
                                        } else {
                                            ?>
                                            <td>
                                                <textarea name="<?php echo esc_attr($option['id']); ?>" id="<?php echo esc_attr($option['id']); ?>" 
                                                    type="<?php echo esc_attr($option['type']); ?>"  class="widefat" cols="" rows="5"
                                                ><?php echo esc_attr(Mazi_Setting_Model::get_option_prefix($option['id'])); ;?></textarea>
                                                <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                            </td>
                                            <?php
                                        }
                                    break;
                                        ?>
                                        <td>
                                            <input class="large-text" type="text" 
                                                name="<?php echo esc_attr($option['id']); ?>"
                                                id="<?php echo esc_attr($option['id']); ?>"
                                                value="<?php echo esc_attr(Mazi_Setting_Model::get_option_prefix($option['id']));?>"
                                            >
                                            <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                        </td>
                                        <?php
                                    // Select
                                    case 'select':
                                        ?>
                                        <td>
                                            <select name="<?php echo esc_attr($option['id']); ?>" id="<?php echo esc_attr($option['id']); ?>">
                                                <?php foreach ($option['options'] as $value => $label) { ?>
                                                <option value="<?php echo esc_attr($value);?>" 
                                                <?php if (Mazi_Setting_Model::get_option_prefix($option['id']) == $value) {
                                                    echo 'selected="selected"';
                                                } ?>><?php echo esc_html($label); ?></option><?php } ?>
                                            </select>
                                            <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                        </td>
                                        <?php
                                    break;
                                    // Checkbox
                                    case 'checkbox':
                                        $checked = get_option($option['id']) ? "checked=\"checked\"" : "";
                                        ?>
                                        <td>
                                            <input type="checkbox" 
                                                name="<?php echo esc_attr($option['id']); ?>" 
                                                id="<?php echo esc_attr($option['id']); ?>" 
                                                value="true" <?php echo esc_attr($checked); ?> />
                                            <label for="<?php echo esc_attr($option['id']); ?>"><?php echo esc_html($option['desc']); ?></label>
                                        </td>
                                        <?php     
                                    break;
                                    // Default
                                    default:
                                        ?>
                                        <td>
                                            <input class="large-text" type="text" 
                                                name="<?php echo esc_attr($option['id']); ?>"
                                                id="<?php echo esc_attr($option['id']); ?>"
                                                value="<?php echo esc_attr(Mazi_Setting_Model::get_option_prefix($option['id']));?>"
                                            >
                                            <p><small><?php echo esc_html($option['desc']); ?></small></p>
                                        </td>
                                        <?php 
                                    break;  
                                }    
                                ?>                                
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>  
            </div> 
        </div>        
        <input name="Submit" class="button" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        <input type="hidden" name="action" value="save" />
    </form>
</div>