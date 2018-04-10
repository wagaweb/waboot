<?php require_once(ABSPATH . 'wp-admin/admin-header.php'); ?>
<div class="wrap">
	<h1> <?php _e( 'Update Components', 'waboot' ) ?> </h1>
	<p>
		<?php if(!$error_occurred): ?>
			<?php printf(__('Component <strong>%s</strong> has been updated successfully.', 'waboot'), $component_nicename); ?>
		<?php else: ?>
			<?php printf(__('Component <strong>%s</strong> update failed.', 'waboot'), $component_nicename); ?><br  /><br />
			<?php echo $error; ?>
		<?php endif; ?>
	</p>
    <p>
        <?php $ai = 1; foreach ($update_actions as $action): ?>
            <?php echo $action; ?><?php if($ai !== count($update_actions)): ?> | <?php endif; ?>
        <?php $ai++; endforeach; ?>
    </p>
</div>
<?php include(ABSPATH . 'wp-admin/admin-footer.php');
