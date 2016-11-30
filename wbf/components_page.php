<?php use WBF\modules\components\ComponentFactory;
use WBF\modules\components\GUI;

if($last_error): ?>
	<div class="error">
		<p><?php echo $last_error; ?></p>
	</div>
<?php elseif($options_updated_flag) : ?>
	<div class="updated">
		<p><?php _ex("Options updated successfully","Component Page","wbf"); ?></p>
	</div>
<?php endif; ?>
<?php if(count($registered_components) <= 0) : ?>
	<div class="wrap">
		<h2><?php _e("Components", "wbf"); ?></h2>
		<p>
			<?php _e("No components available in the current theme. You can create components into /components/ directory under theme directory.","wbf"); ?>
		</p>
	</div>
	<?php return; endif; ?>
<div id="componentframework-wrapper" class="wrap" data-components-gui>
	<div class="categories-header">
		<ul>
			<?php foreach($categorized_registered_components as $category => $components): ?>
				<li data-category="<?php echo str_replace(" ","_",strtolower($category)); ?>"><?php echo $category?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<div id="componentframework-content-wrapper">
		<form method="post" action="admin.php?page=<?php echo GUI::$wp_menu_slug; ?>">
			<!-- Components List -->
			<?php foreach($registered_components as $component): ?>
				<?php $data = ComponentFactory::get_component_data( $component->file ); ?>
				<div id="<?php echo $component->name; ?>" class="component" data-category="<?php echo str_replace(" ","_",strtolower($component->category)); ?>">
					<div class="component-preview">
						<img src="http://placehold.it/135x95">
					</div>
					<div class="component-content">
						<h2><?php if(isset($data['Name'])) echo $data['Name']; else echo ucfirst($component->name); ?></h2>
						<div class="component-description">
							<p>
								<?php echo $data['Description']; ?>
							</p>
							<?php if(\WBF\modules\components\ComponentsManager::is_child_component($component)): ?>
								<p class="child-component-notice">
									<?php _e("This is a component of the current child theme", "wbf"); ?>
									<?php
									if(isset($component->override)) {
										if($component->override){
											_e(", and <strong>override a core component</strong>", "wbf");
										}
									}
									?>
								</p>
							<?php endif; ?>
							<?php if(isset($component->tags) && !empty($component->tags)): ?>
								<div class="tags">
									<strong><?php _ex("Tags:","Components Page","wbf"); ?></strong>
									<ul style="list-style-type: none; display: inline; margin-left: 5px;">
										<?php foreach ($component->tags as $tag): ?>
											<li class="tag-<?php echo str_replace(" ","_",strtolower($tag)); ?>" style="margin-right: 5px; padding: 0 3px; border: 1px solid #ddd; display: inline;"><?php echo $tag ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif ?>
							<div class="<?php WBF\modules\components\print_component_status($component); ?> second plugin-version-author-uri">
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
						</div>
					</div>
					<div class="components-actions">
						<ul>
							<?php if(\WBF\modules\components\ComponentsManager::is_active($component)): ?>
							<li>[rotella]</li>
							<?php endif; ?>
							<li>
								<?php if(!\WBF\modules\components\ComponentsManager::is_active($component)): ?>
									<input id="<?php echo $component->name; ?>_status" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="components_status[<?php echo $component->name; ?>]" >
								<?php else: ?>
									<input id="<?php echo $component->name; ?>_status" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="components_status[<?php echo $component->name; ?>]" checked="checked">
								<?php endif; ?>
								<label class="wb-onoffswitch-label" for="<?php echo $component->name; ?>_status"><span class="wb-onoffswitch-inner"></span>
									<span class="wb-onoffswitch-switch"></span>
								</label>
							</li>
						</ul>
					</div>
					<div data-action="open-details">
						<a href="#">[open]</a>
					</div>
					<?php if(\WBF\modules\components\ComponentsManager::is_active($component)): ?>
					<div class="component-options">
						<div data-fieldgroup>
							<h3><?php _e(sprintf("%s Settings",isset($data['Name']) ? $data['Name'] : ucfirst($component->name)),"wbf"); ?></h3>
							<?php \WBF\modules\options\GUI::print_fields($compiled_components_options[$component->name]); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
			<?php endforeach;?>
			<!-- /Components List -->
			<div id="componentframework-submit">
				<input type="submit" name="submit-components-options" id="submit" class="button button-primary" value="Save Changes">
				<input type="submit" class="reset-button button-secondary" name="restore_defaults_components" value="<?php esc_attr_e( 'Restore default component status', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to restore defaults. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
				<input type="submit" class="reset-button button-secondary" name="reset_components" value="<?php esc_attr_e( 'Reset components status', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
			</div>
		</form>
	</div><!-- #componentframework-wrap -->
	<?php \WBF::print_copyright(); ?>
</div><!-- .wrap: end -->