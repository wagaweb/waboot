<?php

namespace WBF\admin\acfFields;

class MultipleFileUpload extends \acf_field{

	function __construct(){
		$this->name = 'multiple_file_upload';
		$this->label = __("Multiple File Upload",'wbf');
		$this->category = 'content';

		parent::__construct();
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
		?>
		<div class="mfu-main">
			<script type="text/template" id="FileUploadInput">
				<input type="" name="" value="" />
			</script>
			<div class="mfu-files">
				<div class="file-input">
					<input type="text" name="<?php echo esc_attr($field['name']) ?>" value="" />&nbsp;
					<a href="#" class="acf-button blue add-attachment"><?php _e('Upload', 'wbf'); ?></a>
				</div>
			</div>
			<div class="mfu-toolbar">
				<a href="#" class="acf-button blue add-attachment"><?php _e('Add new file', 'wbf'); ?></a>
			</div>
		</div>
		<?php
	}
}