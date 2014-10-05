<?php
/**
 * Behaviors Framework
 *
 * @package   Behaviors Framework
 * @author    Riccardo D'Angelo <me@riccardodangelo.com>
 * @license   copyrighted
 * @link      http://www.waga.it
 * @copyright 2014 Riccardo D'Angelo and WAGA.it
 * @todo - Realizzare le input mancanti;
 * @todo - Creare una classe per la gestione del framework (meglio due classi? Una per il Behavior e una per l'opzione singola?)
 * @todo - Fare in modo che i testi siano traducibili: spostarsi da un file json a un file php?
 */

add_action( 'add_meta_boxes', 'waboot_behavior_create_metabox' );

add_action( 'save_post', 'waboot_behavior_save_metabox' );
add_action( 'pre_post_update', 'waboot_behavior_save_metabox' );
add_action( 'edit_post', 'waboot_behavior_save_metabox' );
add_action( 'publish_post', 'waboot_behavior_save_metabox' );
add_action( 'edit_page_form', 'waboot_behavior_save_metabox' );

//add_action( 'optionsframework_after_validate','waboot_reset_defaults' );

/**
 * Get a behaviour.
 * @param $name
 * @param string $return (value OR array)
 * @return array|bool|mixed|string
 */
function get_behavior($name,$return = "value"){
    global $post;

    $behaviors = waboot_behavior_get_options(); //retrive all behaviours
    $selected_behavior = array();
    foreach($behaviors as $b){ //find the desidered behaviour
        if($b['name'] == $name){
            $selected_behavior = $b;
            $selected_behavior['default'] = waboot_behavior_get_default($name); //here we have the "default" specified in file, but it could has been modified via theme options, so we reassign it
        }
    }

    if(!isset($post) || $post->ID == 0){
        if($return == "value"){
            return waboot_behavior_get_default($name);
        }else{
            return $selected_behavior;
        }
    }

    if(!empty($selected_behavior)){
        $current_behavior_value = get_post_meta($post->ID,"_behavior_".$name,waboot_behavior_get_default($name));

        if($current_behavior_value == "" && ($selected_behavior['type'] != "textarea" || $selected_behavior['type'] != "input"))
            $current_behavior_value = "_default";

        if($current_behavior_value == "_default" || (is_array($current_behavior_value) && $current_behavior_value[0] == "__default"))
            $current_behavior_value = waboot_behavior_get_default($name);

        if(is_array($current_behavior_value))
            $current_behavior_value = $current_behavior_value[0];

        if($return == "array"){
            $selected_behavior['value'] = $current_behavior_value;
            return $selected_behavior;
        }else{
            return $current_behavior_value;
        }
    }else{
        return false;
    }
}

function waboot_behavior_create_metabox(){
    $options = waboot_behavior_import_predefined_options();
    add_meta_box("behavior","Behaviors","waboot_behavior_display_metabox",null,"advanced","core",array($options));
}

function waboot_behavior_save_metabox($post_id){
    // Check if our nonce is set.
    if ( ! isset( $_POST['behaviors_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['behaviors_meta_box_nonce'], 'behaviors_meta_box' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Then save behaviors...

    $behaviors = waboot_behavior_get_options();
    foreach($behaviors as $b){
        $b['name'] = '_behavior_'.$b['name'];

        if( (!isset( $_POST[$b['name']] ) && $b['type'] == "checkbox") && in_array($_POST['post_type'],$b['valid'])) $_POST[$b['name']] = "0";

        if( isset( $_POST[$b['name']] )){
            $data = sanitize_text_field( $_POST[$b['name']] );
            update_post_meta( $post_id, $b['name'], $data );
        }
    }
}

function waboot_behavior_display_metabox($post,$options){
    $post_type = get_post_type($post);
    $options = $options['args'][0];

    wp_nonce_field('behaviors_meta_box','behaviors_meta_box_nonce');

    ?>
    <?php $opt_n=0; foreach($options as $opt) : ?>
        <?php if(in_array($post_type,$opt['valid'])) : ?>
            <?php
            $opt_n++;
            waboot_behavior_display_option($opt,$post);
            ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if($opt_n == 0) : ?>
        <p>No behavior available for this post type.</p>
    <?php endif;
}

function waboot_behavior_display_option($option,$post){
    //$std = isset($option['default']) ? $option['default'] : ""; //get the default value
    //$std = of_get_option("behavior_".$option['name'],$std); //check if the default value was changed via theme options

    $option['name'] = "_behavior_".$option['name']; //rename options accordingly to Wordpress metabox field nomenclature (prefixed with "_" for hiding).
    $current_value = get_post_meta($post->ID,$option['name'],true); //is an existing value available?

    if($current_value == ""){
        $current_value = "_default";
    }else{
        //$std = $current_value; //if there is an existing value, use it as default value instead
    }

    switch($option['type']){
        case 'checkbox':
            ?>
            <ul>
                <?php if(isset($option['options']) && is_array($option)) : foreach($option['options'] as $c) : //TODO dare la possibilità di concatenare più checkbox in una sola opzione? ?>
                <?php endforeach; else : ?><p><strong><?php echo $option['title'] ?></strong></p>
                    <li>
                        <label for="<?php echo $option['name'] ?>" title="<?php echo $option['title'] ?>">
                            <input type="checkbox" name="<?php echo $option['name'] ?>" id="<?php echo $option['name'] ?>" value="1" <?php if($current_value == 1) echo "checked"?>>
                            <?php echo __("Enable"); ?>
                        </label>
                    </li>
                <?php endif; ?>
                <li>
                    <input type="checkbox" name="<?php echo $option['name'] ?>-default" id="<?php echo $option['name'] ?>-default" value="_default" <?php if($current_value == "_default") echo "checked"?>><?php echo __("Default") ?>
                </li>
            </ul>
            <?php
            break;
        case 'select':
            ?>
            <p><strong><?php echo $option['title'] ?></strong></p>
            <label class="screen-reader-text" for="<?php echo $option['name'] ?>"><?php echo $option['title'] ?></label>
            <select name="<?php echo $option['name'] ?>" id="<?php echo $option['name'] ?>">
                <?php foreach($option['options'] as $k => $v) : ?>
                    <option value="<?php echo $v['value']; ?>" <?php if($v['value'] == $current_value) echo "selected"?>><?php echo $v['name']; ?></option>
                <?php endforeach; ?>
                <option value="_default" <?php if($current_value == "_default") echo "selected"?>><?php echo __("Default") ?></option>
            </select>
            <?php
            break;
    }
}

function waboot_behavior_generate_option($b){
    $option = array(
        'name' => $b['title'],
        'desc' => $b['desc'],
        'id' => "behavior_".$b['name'],
        'type' => $b['type'],
    );

    switch($b['type']){
        case 'select':
            $select_options = array();
            foreach($b['options'] as $o){
                $select_options[$o['value']] = $o['name'];
            }
            $option['options'] = $select_options;
            break;
    }

    if(isset($b['default'])){
        $select_default = array();
        if(is_array($b['default'])){
            foreach($b['default'] as $d){
                $select_default[$d] = 1;
            }
        }else{
            $select_default = $b['default'];
        }
        $option['std'] = $select_default;
    }

    return $option;
}

function waboot_behavior_get_options(){
    return waboot_behavior_import_predefined_options(); //per ora si possono specificare solo via file...
}

function waboot_behavior_import_predefined_options(){

    $behavior_file = get_theme_root()."/".get_template()."/inc/behaviors.json";
    if (file_exists($behavior_file)) {
        $predef_behaviors = json_decode(file_get_contents($behavior_file, true),true);
    }

    if(is_child_theme()){
        $child_behavior_file = get_stylesheet_directory()."/inc/behaviors.json";
        if(file_exists($child_behavior_file)){
            $child_behaviors = json_decode(file_get_contents($child_behavior_file, true),true);
            $predef_behaviors = array_replace_recursive($predef_behaviors,$child_behaviors);
        }
    }

    return $predef_behaviors;
}

function waboot_behavior_get_default($name){
    //Get the default value specified in files
    $behaviors = waboot_behavior_get_options();
    foreach($behaviors as $b){
        if($b['name'] == $name){
            if(isset($b['default'])){
                if($b['type'] == "checkbox"){
                    $base_default = $b['default'] == 1? true : false;
                }else{
                    $base_default = $b['default'];
                }
            }
        }
    }
    //Get the default value specified via Theme Options
    $default = of_get_option("behavior_".$name,$base_default);

    return $default;
}