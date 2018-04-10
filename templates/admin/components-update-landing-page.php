<?php require_once(ABSPATH . 'wp-admin/admin-header.php'); ?>
<div class="wrap">
	<h1> <?php _e( 'Update Components', 'waboot' ) ?> </h1>
	<p>
		<?php if(!$error_occurred): ?>
			<?php printf(__('Component %s has been updated successfully', 'waboot'), $component_nicename); ?>
		<?php else: ?>
			<?php printf(__('Component %s update failed', 'waboot'), $component_nicename); ?><br  /><br />
			<?php echo $error; ?>
		<?php endif; ?>
	</p>
</div>
<?php include(ABSPATH . 'wp-admin/admin-footer.php');
