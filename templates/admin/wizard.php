<form id="waboot-wizard-form" action="<?php echo admin_url("admin.php?page=waboot_setup_wizard"); ?>" method="post">
	<?php foreach($generators as $generator_slug => $generator_data): ?>
		<label>
			<?php if(isset($generator_data->preview)): ?>
				<img src="<?php echo $generator_data->preview ?>" width="100px" height="100px" title="<?php echo $generator_data->name; ?>" alt="[ <?php echo $generator_data->name; ?> preview]" />
			<?php else: ?>
				<img src="http://placehold.it/250x300" width="250px" height="300px" title="<?php echo $generator_data->name; ?>" alt="[ <?php echo $generator_data->name; ?> preview]" />
			<?php endif; ?>
			<input type="radio" name="generator" value="<?php echo $generator_slug ?>"><?php echo $generator_data->name; ?>
		</label>
	<?php endforeach; ?>
    <div id="progress-status" class="progress-status"></div>
	<p class="submit">
		<button type="submit" class="button button-primary"><?php _e("Start wizard","waboot"); ?></button>
	</p>
	<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
</form>
<script type="text/template" id="progress-tpl">
    <div class="progress-meter">
        <%= step_message %>
    </div>
    <div class="progress-bar-wrapper" style="width: 100%">
        <div class="progress-bar" style="text-align: center; width: <%= current_percentage %>%; background-color: #00a8c6;"><%= current_percentage %>%</div>
    </div>
</script>
