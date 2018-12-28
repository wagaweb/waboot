<?php use WBF\modules\components\ComponentFactory;
use WBF\modules\components\GUI;
?>

<?php require_once get_template_directory() . '/wbf/admin-header.php'; ?>

<?php WBF()->services()->get_notice_manager()->show_manual_notices(); ?>

<?php if(count($registered_components) <= 0) : ?>
	<div class="wrap">
		<h2><?php _e("Components", "waboot"); ?></h2>
		<p>
			<?php _e("No components available in the current theme. You can create components into /components/ directory under theme directory.","waboot"); ?>
		</p>
	</div>
<?php return; endif; ?>

<div id="componentframework-wrapper" class="componentframework-wrapper admin-wrapper wrap" data-components-gui>
    <div class="componentframework-nav" data-nav>
        <ul>
			<?php foreach($categorized_registered_components as $category => $components): ?>
				<li data-category="<?php echo str_replace(" ","_",strtolower($category)); ?>"><?php echo $category?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div id="componentframework-content" class="componentframework-content">
		<form method="post" action="admin.php?page=<?php echo GUI::$wp_menu_slug; ?>">
			<!-- Components List -->
			<?php foreach($registered_components as $component): ?>
				<?php
                    $data = ComponentFactory::get_component_data( $component->file );
                    $screenshot = \Waboot\functions\components\get_preview_image($component);
                ?>
				<section id="component-<?php echo $component->name; ?>" class="section-group section-component" data-component="<?php echo $component->name; ?>" data-category="<?php echo str_replace(" ","_",strtolower($component->category)); ?>">
                    <div class="component-inner">
                        <?php if($screenshot): ?>
                        <div class="component-preview">
                            <img src="<?php echo $screenshot; ?>">
                        </div>
                        <?php endif; ?>
                        <div class="component-content">
                            <h2><?php if(isset($data['Name'])) echo $data['Name']; else echo ucfirst($component->name); ?></h2>
                            <div class="component-description">
                                <p>
                                    <?php echo $data['Description']; ?>
                                </p>
                                <?php if(\WBF\modules\components\ComponentsManager::is_child_component($component)): ?>
                                    <p class="child-component-notice">
                                        <?php _e("This is a component of the current child theme", "waboot"); ?>
                                        <?php
                                        if(isset($component->override)) {
                                            if($component->override){
                                                _e(", and <strong>override a core component</strong>", "waboot");
                                            }
                                        }
                                        ?>
                                    </p>
                                <?php endif; ?>
                                <div class="<?php WBF\modules\components\print_component_status($component); ?> second plugin-version-author-uri">
                                    <div class="author-version">
                                        <?php
                                        $component_meta = array();
                                        if(empty($data['Version'])){
                                            $component_meta[] = sprintf( __( 'Version %s' ), $data['Version'] );
                                        }
                                        if(!empty($data['Author'])) {
                                            $author = $data['Author'];
                                            if(!empty($data['AuthorURI'])){
                                                $author = '<a href="' . $data['AuthorURI'] . '" title="' . esc_attr__( 'Visit author homepage' ) . '">' . $data['Author'] . '</a>';
                                            }
                                            $component_meta[] = sprintf( __( 'By %s' ), $author );
                                        }
                                        if(!empty($plugin_data['PluginURI'])){
                                            $component_meta[] = '<a href="' . $data['ComponentURI'] . '" title="' . esc_attr__( 'Visit plugin site' ) . '">' . __( 'Visit plugin site' ) . '</a>';
                                        }
                                        echo implode(' | ', $component_meta);
                                        ?>
                                    </div>
                                    <?php if(isset($component->tags) && !empty($component->tags)): ?>
                                        <div class="tags">
                                            <strong><?php _ex("Tags:","Components Page","waboot"); ?></strong>
                                            <ul>
                                                <?php foreach ($component->tags as $tag): ?>
                                                    <li class="tag-<?php echo str_replace(" ","_",strtolower($tag)); ?>"><?php echo $tag ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                        <div class="components-actions">
                            <div class="wb-onoffswitch">
                                <?php if(!\WBF\modules\components\ComponentsManager::is_active($component)): ?>
                                    <input type="checkbox" name="components_status[<?php echo $component->name; ?>]" class="wb-onoffswitch-checkbox" id="components_status[<?php echo $component->name; ?>]">
                                <?php else: ?>
                                    <input type="checkbox" name="components_status[<?php echo $component->name; ?>]" class="wb-onoffswitch-checkbox" id="components_status[<?php echo $component->name; ?>]" checked>
                                <?php endif; ?>
                                <label class="wb-onoffswitch-label" for="components_status[<?php echo $component->name; ?>]">
                                    <span class="wb-onoffswitch-inner"></span>
                                    <span class="wb-onoffswitch-switch"></span>
                                </label>
                            </div>
                        </div>
                        <?php if(\WBF\modules\components\ComponentsManager::is_active($component)): ?>
                        <div class="open-details" data-action="open-details">
                            <img class="active" src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-down.svg" />
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon/arrow-up.svg" />
                        </div>
                        <?php endif; ?>
                    </div>
					<?php if(\WBF\modules\components\ComponentsManager::is_active($component)): ?>
					<div class="options-group" style="display: none;" data-component-options>
						<div class="options-group-data">
							<h3><?php _e(sprintf("%s Settings",isset($data['Name']) ? $data['Name'] : ucfirst($component->name)),"waboot"); ?></h3>
							<?php \WBF\modules\options\GUI::print_fields($compiled_components_options[$component->name]); ?>
							<?php do_action('wbf/modules/components/active_component/'.$component->name.'/settings'); ?>
						</div>
					</div>
					<?php endif; ?>
				</section>
			<?php endforeach;?>
			<!-- Components List -->
			<div id="componentframework-submit">
				<input type="submit" name="submit-components-options" id="submit" class="button button-primary" value="Save Changes">
				<input type="submit" class="reset-button button-secondary" name="restore_defaults_components" value="<?php esc_attr_e( 'Restore default component status', 'waboot' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to restore defaults. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
				<input type="submit" class="reset-button button-secondary" name="reset_components" value="<?php esc_attr_e( 'Reset components status', 'waboot' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
			</div>
		</form>
	</div><!-- #componentframework-wrap -->
</div><!-- .wrap: end -->