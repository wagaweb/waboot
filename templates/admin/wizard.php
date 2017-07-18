<div id="wizard-wrapper" class="admin-wrapper wizard-wrapper">
    <form id="waboot-wizard-form" action="<?php echo admin_url("admin.php?page=waboot_setup_wizard"); ?>" method="post">
        <div class="title-wrapper">
            <h1 class="title"><img src="<?php echo $images_uri.'/wizard/waboot-logo.png' ?>" alt="Waboot" /></h1>
        </div>
        <div class="wizard-content">
            <div class="headline">
                <h2><?php _ex('Thank you for choosing Waboot!','Wizard','waboot'); ?></h2>
                <?php if(!\Waboot\functions\wbf_exists()): ?>
                    <p>
	                    <?php _ex('Choose one of the layouts below to install all requirements and startup your new project with some predefined options.','Wizard','waboot'); ?>
                    </p>
                <?php else: ?>
                    <p>
                        <?php
                        printf(
                            _x(
                                'Choose one of the layouts below to startup your new project with some predefined options, or use manual setup button below.',
                                'Wizard',
                                'waboot'
                            ),
	                        admin_url('admin.php?page=wbf_components'),
	                        admin_url('admin.php?page=wbf_options')
                        );
                        ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="content">
                <div class="generators">
		            <?php foreach($generators as $generator_slug => $generator_data): ?>
                        <div class="generator-selector">
                            <?php if(isset($generator_data->name)) : ?>
                            <h3><?php echo $generator_data->name; ?></h3>
                            <?php if(isset($generator_data->description)): ?>
                                <p class="description"><?php echo $generator_data->description; ?></p>
                            <?php endif; ?>
                            <?php endif; ?>
				            <?php if(isset($generator_data->preview)): ?>
                                <img data-select='<?php echo $generator_slug; ?>' src="<?php echo $generator_data->preview_basepath.'/'.$generator_data->preview ?>" title="<?php echo $generator_data->name; ?>" alt="[ <?php echo $generator_data->name; ?> preview]" />
				            <?php else: ?>
                                <img data-select='<?php echo $generator_slug; ?>' src="http://placehold.it/250x300" width="250px" height="300px" title="<?php echo $generator_data->name; ?>" alt="[ <?php echo $generator_data->name; ?> preview]" />
				            <?php endif; ?>
                            <label class="generator">
                                <input type="radio" name="generator" value="<?php echo $generator_slug ?>"><?php echo $generator_data->name; ?>
                            </label>
                        </div>
		            <?php endforeach; ?>
                </div>

            </div>
            <div class="footer">
                <div id="progress-status" class="progress-status"></div>
                <div class="submit-wrapper">
                    <button type="submit"><?php _e("Start wizard","waboot"); ?></button>
                    <a title="<?php _ex('Go to components page','wizard', 'waboot'); ?>" href="<?php echo admin_url('admin.php?page=wbf_components') ?>" class="manual-setup"><?php _ex('Manual setup', 'wizard', 'waboot'); ?></a>
                </div>
                <div class="info">
                    <h3><?php _ex('Learn more', 'wizard', 'waboot') ?></h3>
                    <ul>
                        <li><a target="_blank" href="https://www.waboot.io"><?php _ex('Learn more about Waboot','wizard','waboot'); ?></a></li>
                        <li><a target="_blank" href="https://github.com/wagaweb/waboot"><?php _ex('Waboot docs','wizard','waboot'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
    </form>
</div>
<script type="text/template" id="progress-tpl">
    <div class="progress-meter">
        <%= step_message %>
    </div>
    <div class="progress-bar-wrapper" style="width: 100%">
        <div class="progress-bar" style="text-align: center; width: <%= current_percentage %>%; background-color: #00a8c6;"><%= current_percentage %>%</div>
    </div>
</script>
<script type="text/template" id="finish-tpl">
    <a id="setup-finish-btn" title="<?php _ex('Go to theme options page','wizard', 'waboot'); ?>" href="<?php echo admin_url('admin.php?page=wbf_options') ?>" class="setup-finish-btn"><?php _ex('Customize Settings', 'wizard', 'waboot'); ?></a>
</script>
