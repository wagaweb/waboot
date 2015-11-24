<?php

namespace WBF\admin\acfFields;

class wcfGallery extends \acf_field{

    function __construct(){
        $this->name = 'wcf_gallery';
        $this->label = __("WBF Gallery",'wbf');
        $this->category = 'content';
        $this->defaults = array(
            'preview_size'	=> 'thumbnail',
            'library'		=> 'all',
            'min'			=> 0,
            'max'			=> 0,
            'min_width'		=> 0,
            'min_height'	=> 0,
            'min_size'		=> 0,
            'max_width'		=> 0,
            'max_height'	=> 0,
            'max_size'		=> 0,
            'mime_types'	=> ''
        );
        $this->l10n = array(
            'select'		=> __("Add Image to Gallery",'wbf'),
            'edit'			=> __("Edit Image",'wbf'),
            'update'		=> __("Update Image",'wbf'),
            'uploadedTo'	=> __("uploaded to this post",'wbf'),
            'max'			=> __("Maximum selection reached",'wbf')
        );
        add_action('save_post', array($this,'saveGalleryMeta'));
        add_action( 'admin_enqueue_scripts', array($this,'load_custom_wp_admin_style') );
        add_action('wp_ajax_wcf_get_thumbnail',				array($this, 'ajax_wcf_get_thumbnail'));
        add_action('wp_ajax_nopriv_wcf_get_thumbnail',		array($this, 'ajax_wcf_get_thumbnail'));
        add_action('wp_ajax_wcf_media_info',				array($this, 'ajax_wcf_media_info'));
        add_action('wp_ajax_nopriv_wcf_media_info',		array($this, 'ajax_wcf_media_info'));
        add_action('wp_ajax_wcf_update_media_info',				array($this, 'ajax_wcf_update_media_info'));
        add_action('wp_ajax_nopriv_wcf_update_media_info',		array($this, 'ajax_wcf_update_media_info'));
        parent::__construct();
    }
    public function ajax_wcf_get_thumbnail(){
        $id= $_POST['id'];
        $fullImageUrl = wp_get_attachment_url($id);
        $uploadImageUrl = substr($fullImageUrl,0, strrpos($fullImageUrl,'/'));
        $image = wp_get_attachment_metadata($id);
        $imageUrl = $uploadImageUrl.'/'.$image["sizes"]["thumbnail"]["file"];
        echo json_encode(['thumb'=>$imageUrl]);
        wp_die();
    }

    public function ajax_wcf_media_info(){
        $id= $_POST['id'];
        $mediaInfo = wp_prepare_attachment_for_js( $id );
        $imgMeta['thumb'] = $mediaInfo['sizes']['thumbnail']['url'];
        $imgMeta['name'] = $mediaInfo['filename'];
        $imgMeta['upload'] = $mediaInfo['dateFormatted'];
        $imgMeta['filesize'] = $mediaInfo['filesize'];
        $imgMeta['size'] = $mediaInfo['width'].'x'.$mediaInfo['height'];
        $imgMeta['title'] = $mediaInfo['title'];
        $imgMeta['caption'] = $mediaInfo['caption'];
        $imgMeta['alt'] = $mediaInfo['alt'];
        $imgMeta['description'] = $mediaInfo['description'];

        echo json_encode($imgMeta);
        wp_die();
    }
    public function ajax_wcf_update_media_info(){
        $id= $_POST['id'];
        $post = get_post( $id, ARRAY_A );
        $post['post_title'] = $_POST['title'];
        $post['post_excerpt'] = $_POST['caption'];
        $alt = $_POST['alt'];
        $post['post_content'] = $_POST['description'];
        update_post_meta( $id, '_wp_attachment_image_alt', wp_slash($alt));
        $resp = wp_update_post( $post );
        if($resp==$id) {
            echo json_encode(['response' => 'true']);
        }else{
            echo json_encode(['response' => 'false']);
        }
        wp_die();
    }
    /**
     * Render field settings during field group creation
     * @param $field
     */
    function render_field_settings( $field ) {
        acf_render_field_setting( $field, array(
            'label'			=> __('Maximum file number','waboot'),
            'instructions'	=> '',
            'type'			=> 'number',
            'name'			=> 'max'
        ));

        // allowed type
        acf_render_field_setting( $field, array(
            'label'			=> __('Allowed file types','waboot'),
            'instructions'	=> __('Comma separated list. Leave blank for all types','waboot'),
            'type'			=> 'text',
            'name'			=> 'mime_types',
        ));
    }

    /**
     * Render field into post editing
     * @param $field
     */
    function render_field( $field ) {
        global $post_id;

        wp_enqueue_media();
        $val = '';
        $values = get_field('field_wbf_gallery', $post_id);
        if($values) {
            foreach ($values as $in => $value) {
                if ($in == '0') {
                    $val .= $value;
                } else {
                    $val .= ',' . $value;
                }
            }
        }
        ?>
        <div>
            <div class="mainContainer">
            <div id="imageContainer">
            <?php $this->renderGalleryMeta($post_id); ?>

            </div>
            <div class="uploadContainer">
                <input type="button" name="upload-btn" id="upload-btn" class="button-primary button" value="Upload Image">
            </div>
            </div>
            <div id="imageInfo">
                <div class="header">
                    <div id="imgThumb">
                        <img src="">
                    </div>
                    <div id="mainInfo">
                        <p class="imgName"></p>
                        <p class="upload"></p>
                        <p class="dimensions"></p>
                    </div>
                </div>
                <div class="body">
                    <p class="title">
                        <label>Titolo</label><input name="title" id="imageTitle" type="text" value=""/>
                    </p>
                    <p class="caption">
                        <label>Didascalia</label><textarea rows="3" id="imageCaption"></textarea>
                    </p>
                    <p class="alt">
                        <label>Testo Alt</label><input type="text" name="imageAlt" id="imageAlt" value=""/>
                    </p>
                    <p class="description">
                        <label>Descrizione</label><textarea rows="3" id="imageDescription"></textarea>
                    </p>
                </div>
                <div class="footer">
                    <button id="closeBtn" class="button">Close</button><button id="updateBtn" class="button-primary button">Update</button><span class="spinner"></span>
                </div>
            </div>
            <input type="hidden" name="imgId" id="imgId" value=" <?php echo $val; ?>">
            <!--<input type="button" name="upload-btn" id="upload-btn" class="button-primary button" value="Upload Image">-->

        </div>
        <?php
    }

    function saveGalleryMeta($postId){
        if(isset($_POST['imgId'])) {
            $fields = get_field('field_wbf_gallery', $postId);
            $ids = array();
            $has_thumbnail = get_the_post_thumbnail($postId);
            if ( !$has_thumbnail ) {
                $images = get_field('field_wbf_gallery', false, false);
                $image_id = $images[0];
                if ( $image_id ) {
                    set_post_thumbnail( $postId, $image_id );
                }
            }
            $ids = explode(',', $_POST['imgId']);
            update_field('field_wbf_gallery', $ids, $postId);

        }
    }
    function renderGalleryMeta($postId){
        $fields = get_field('field_wbf_gallery', $postId);
        if($fields[0] !='') {
            foreach ($fields as $index => $field) {
                $fullImageUrl = wp_get_attachment_url($field);
                $uploadImageUrl = substr($fullImageUrl,0, strrpos($fullImageUrl,'/'));
                $img = wp_get_attachment_metadata($field);
                if(isset($img["sizes"])) {
                    $thumbnail = $uploadImageUrl . '/' . $img["sizes"]["thumbnail"]["file"];
                    echo '<div class="containerImgGalleryAdmin">
                    <img class="imgGalleryAdmin" src=" ' . $thumbnail . '" data-id="' . $field . '">
                        <div class="deleteImg">
                            <a class="acf-icon dark remove-attachment " data-index="' . $index . '" href="#" data-id="' . $field . '">
                                <i class="acf-sprite-delete"></i>
                            </a>
                        </div>
                    </div>';
                }
            }
        }
    }
    function load_custom_wp_admin_style() {
        wp_enqueue_script( 'jquery-ui-sortable' );
    }

}