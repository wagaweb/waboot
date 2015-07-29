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
		// vars
		$uploader = acf_get_setting('uploader');
		// enqueue
		if( $uploader == 'wp' ) {
			acf_enqueue_uploader();
		}
		?>
		<div class="acf-hidden">
			<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => $field['value'], 'data-name' => 'id' )); ?>
		</div>
		<div class="mfu-main" data-maxfile="<?php echo $field['max'] ?>">
			<script type="text/template" id="FileUploadInput">
				<div class="file-input">
					<input type="text" name="<?php echo esc_attr($field['name']) ?>[]" value="" />
					<a href="#" class="acf-button blue upload-attachment"><?php _e('Upload', 'wbf'); ?></a>
				</div>
			</script>
			<div class="mfu-files">
				<?php if( $field['value'] && is_array($field['value']) ) : ?>
					<?php foreach($field['value'] as $k => $v) : ?>
						<div class="file-input">
							<input type="text" name="<?php echo esc_attr($field['name']) ?>[<?php echo $k; ?>]" value="<?php echo $v; ?>" />
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="mfu-toolbar">
				<a href="#" class="acf-button blue add-attachment"><?php _e('Add new file', 'wbf'); ?></a>
			</div>
		</div>
		<?php
	}
}