<?php

namespace WBF\modules\components;


use WBF\modules\options\Framework;

class ComponentsManager {

    static $last_error = "";

    /**
     * Add hooks, detect components into components directory and updates relative options
     */
    static function init(){
	    add_action("wbf/theme_options/register",'\WBF\modules\components\ComponentsManager::addRegisteredComponentOptions'); //register component options
        /** Detect components in main theme **/
        self::_detect_components(get_template_directory()."/components");
        /** Detect components in child theme **/
        if(is_child_theme()){
            self::_detect_components(get_stylesheet_directory()."/components",true);
        }
    }

    static function scripts($hook){
        global $plugin_page;
        if($plugin_page == "waboot_components"){
            // Enqueue custom CSS
            $stylesheet = wbf_locate_template_uri('wbf/admin/css/componentsframework.css');
            if ($stylesheet != ""){
                wp_enqueue_style('waboot-theme-components-style', $stylesheet, array(), '1.0.0', 'all'); //Custom Theme Options CSS
            }
            if(WABOOT_ENV == "dev"){
                wp_register_script('component-page-script',WBF_URL."/sources/js/admin/components-page.js",array('jquery'));
            }else{
                wp_register_script('component-page-script',WBF_URL."/admin/js/components-page.min.js",array('jquery'));
            }
            wp_enqueue_script('component-page-script');
        }
    }

    /**
     * Detect the components in the their directory and update the registered component WP option
     *
     * @param $components_directory
     * @param bool $child_theme
     *
     * @return mixed|void
     */
    static function _detect_components( $components_directory, $child_theme = false ) {
        $registered_components = $child_theme ? self::get_child_registered_components() : self::get_waboot_registered_components();

        //Unset deleted components
        foreach ( $registered_components as $name => $data ) {
            if ( ! is_file( $data['file'] ) ) {
                unset( $registered_components[ $name ] );
            }
        }

        $components_files = listFolderFiles( $components_directory );
        foreach ( $components_files as $file ) {
            //$component_data = get_plugin_data($file);
            $component_data = self::get_component_data( $file );
            if ( $component_data['Name'] != "" ) {
                //The component is valid, now checks if is already in registered list
                $component_name = basename( dirname( $file ) );
                if ( $component_name == "components" ) { //this means that the component file is in root directory
                    $pinfo          = pathinfo( $file );
                    $component_name = $pinfo['filename'];
                }
                if ( ! array_key_exists( $component_name, $registered_components ) ) {
                    $registered_components[ $component_name ] = array(
                      'nicename'        => $component_name,
                      'file'            => $file,
                      'child_component' => $child_theme,
                      'enabled'         => false
                    );
                }
            }
        }
        if ( ! $child_theme ) {
            self::update_waboot_registered_components( $registered_components );
        } //update the WP Option of registered component
        else {
            self::update_child_registered_components( $registered_components );
        } //update the WP Option of registered component

        return $registered_components;
    }

    /**
     * Get the value of "{$template_name}_registered_components" option (default to empty array). $template_name is the current active template.
     * @return mixed|void
     */
    static function get_child_registered_components() {
        $template_name = basename( get_stylesheet_directory_uri() );

        return get_option( "{$template_name}_registered_components", array());
    }

    /**
     * Get the value of "waboot_registered_components" option (default to empty array)
     * @return mixed|void
     */
    static function get_waboot_registered_components() {
        return get_option( "waboot_registered_components", array());
    }

    /**
     * Get the component metadata from the beginning of the file. Mimics the get_plugin_data() WP funtion.
     * @param $component_file
     * @return array
     */
    static function get_component_data( $component_file ) {
        $default_headers = array(
          'Name'         => 'Component Name',
          'Version'      => 'Version',
          'Description'  => 'Description',
          'Author'       => 'Author',
          'AuthorURI'    => 'Author URI',
          'ComponentURI' => 'Component URI',
        );

        $component_data = get_file_data( $component_file, $default_headers );

        return $component_data;
    }

    /**
     * Get the possibile paths for a component named $c_name. The component does not have to exists.
     * @param $c_name
     * @return array
     */
    static function generate_component_mainfile_path($c_name){
        $core_dir = get_root_components_directory();
        $child_dir = get_child_components_directory();

        $c_name = strtolower($c_name);

        return array(
          'core' => $core_dir.$c_name."/$c_name.php",
          'child' => $core_dir.$c_name."/$c_name.php"
        );
    }

    /**
     * Update the "waboot_registered_components" option
     *
     * @param $registered_components
     */
    static function update_waboot_registered_components( $registered_components ) {
        update_option( "waboot_registered_components", $registered_components );
    }

    /**
     * Update the "{$template_name}_registered_components" option, where $template_name is the current active template.
     * @param $registered_components
     */
    static function update_child_registered_components( $registered_components ) {
        $template_name = basename( get_stylesheet_directory_uri() );
        update_option( "{$template_name}_registered_components", $registered_components );
    }

    static function add_menu($parent_slug) {
        add_submenu_page( $parent_slug, __( "Waboot Components", "wbf" ), __( "Components", "wbf" ), "activate_plugins", "waboot_components", '\WBF\modules\components\ComponentsManager::components_admin_page', "", 66 );
    }

    /**
     * Exec detectFilters() method on active components.
     */
    static function setupComponentsFilters(){
        $components = self::getAllComponents();
        foreach ( $components as $c ) {
            if ( self::is_active( $c ) ) {
                require_once( $c['file'] );
                $className  = ucfirst( $c['nicename'] ) . "Component";
                $oComponent = new $className( $c );
                if(method_exists($oComponent,"detectFilters")){
                    $oComponent->detectFilters();
                }
            }
        }
    }

    /**
     * Exec the setup() method on active components (called during "init" action)
     */
    static function setupRegisteredComponents() {
        $components = self::getAllComponents();
        foreach ( $components as $c ) {
            if ( self::is_active( $c ) ) {
                require_once( $c['file'] );
                $className  = ucfirst( $c['nicename'] ) . "Component";
                $oComponent = new $className( $c );
                if(method_exists($oComponent,"setup"))
                    $oComponent->setup();
            }
        }
    }

    /**
     * Exec widgets() method on active components (during widgets_init action)
     */
    static function registerComponentsWidgets(){
        $components = self::getAllComponents();
        foreach ( $components as $c ) {
            if ( self::is_active( $c ) ) {
                require_once( $c['file'] );
                $className  = ucfirst( $c['nicename'] ) . "Component";
                $oComponent = new $className( $c );
                if(method_exists($oComponent,"widgets"))
                    $oComponent->widgets();
            }
        }
    }

    /**
     * Exec onInit()\run(), scripts(), styles() methods on active components; ONLY into the pages that support them.
     * See components-hooks.php.
     */
    static function enqueueRegisteredComponent( $action ) {
        $components = self::getAllComponents();
        foreach ( $components as $c ) {
            if ( self::is_active( $c ) ) {
                require_once( $c['file'] );
                $className  = ucfirst( $c['nicename'] ) . "Component";
                $oComponent = new $className( $c );
	            $oComponent->detectFilters();
                if ( self::is_enable_for_current_page( $oComponent ) ) {
                    self::addLoadedComponent( $oComponent );
                    switch ( $action ) {
                        case "wp":
                            if(method_exists($oComponent,"onInit")){
                                $oComponent->onInit(); //deprecated
                            }else{
                                if(method_exists($oComponent,"run")){
                                    $oComponent->run();
                                }
                            }
                            break;
                        case "wp_enqueue_scripts":
                            if(method_exists($oComponent,"scripts"))
                                $oComponent->scripts();
                            if(method_exists($oComponent,"styles"))
                                $oComponent->styles();
                            break;
                    }
                }
            }
        }
    }

    /**
     * Exec register_options method on active components (executed during "wbf/theme_options/register" action)
     */
    static function addRegisteredComponentOptions(){
        $components = self::getAllComponents();
        foreach ( $components as $c ) {
            if ( self::is_active( $c ) ) {
                require_once( $c['file'] );
                $className  = ucfirst( $c['nicename'] ) . "Component";
                $oComponent = new $className( $c );
                $oComponent->register_options();
            }
        }
    }

    /**
     * Returns and array of components data (aka in array mode, this do not retrive Waboot_Component)
     * @return array
     */
    static function getAllComponents() {
        global $registered_components;
        if ( ! empty( $registered_components ) ) {
            return $registered_components;
        } else {
            $components = self::retrieve_components();
            self::update_global_components_vars();
            return $components;
        }
    }

    /**
     * Gets all registered components (in array mode). Maybe this is a duplicate of getAllComponents()
     * @return array
     */
    static function getRegisteredComponents(){
        $registered_components = array();

        $main_components = self::get_waboot_registered_components();
        $registered_components = array_merge($registered_components,$main_components);
        if(is_child_theme()){
            $child_components = self::get_child_registered_components();
            $registered_components = array_merge($child_components,$main_components);
        }

        return $registered_components;
    }

    static function update_global_components_vars(){
        global $registered_components;
        $registered_components = self::retrieve_components();
    }

    static function retrieve_components(){
        $core_components  = self::get_waboot_registered_components();
        $child_components = is_child_theme() ? self::get_child_registered_components() : array();
        if ( is_child_theme() ) {
            foreach ( $core_components as $name => $comp ) {
                if ( array_key_exists( $name, $child_components ) ) {
                    $child_components[ $name ]['override'] = true;
                    //unset($child_components[$name]); //todo: per ora, non permettere la sovrascrizione
                }
            }
            $components = array_merge( $core_components, $child_components ); //this will override core_components with child_components with same name
        } else {
            /*foreach($core_components as $name => $comp){
                if(in_array($name,$child_components)){
                    unset($child_components[$name]);
                }
            }*/
            $components = $core_components;
        }

        return $components;
    }

    /**
     * Checks if the component called $name is loaded
     * @param $name
     * @return bool
     */
    static function is_loaded_by_name($name){
        global $loaded_components;
        if(array_key_exists($name,$loaded_components)) {
            return true;
        }
        return false;
    }

    /**
     * Check if the registered component is active (the component must exists)
     * @param String|Array $component (l'array può essere ottenuto da get_option("waboot_registered_components"))
     * @return bool
     */
    static function is_active( $component ) {

        if(is_array($component)){
            if ( $component['enabled'] == true ) {
                return true;
            }
        }else{
            $registered_components = self::getAllComponents();
            if(isset($registered_components[$component]) && $registered_components[$component]['enabled']){
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the main file of the $component is present
     * @param $component
     * @return bool
     */
    static function is_present( $component ){
        if(is_array($component)){
            if(is_file($component['file'])) return true;
        }else{
            $registered_components = self::getAllComponents();
            if(isset($registered_components[$component]) && is_file($registered_components[$component]['file'])) return true;
        }
        return false;
    }

    /**
     * Checks if the component is allowed for the page\post being displayed
     * @param Component $c
     * @return bool
     */
    static function is_enable_for_current_page( \WBF\modules\components\Component $c ) {
        global $post;

	    $maybe_enabled = false;

        if ( is_admin() )
	        $maybe_enabled = false;

        if ( empty( $c->filters ) ) {
	        $maybe_enabled = false;
        }

        if ( $c->filters['node_id'] == "*" ) {
	        $maybe_enabled = true;
        } else {
	        if(!isset($post->ID)){
		        //We are in archives or 404
		        if(is_404() && in_array("page",$c->filters['post_type'])) $maybe_enabled = true;
				if(is_archive() && in_array("blog", $c->filters['post_type'])) $maybe_enabled = true;
	        }

            $current_post_type = get_post_type( $post->ID );
            if ( is_home() ) {
                $current_post_id = get_option( "page_for_posts" );
            } else {
                $current_post_id = $post->ID;
            }
            if ( in_array( $current_post_id, $c->filters['node_id'] ) || in_array( $current_post_type, $c->filters['post_type'] ) ) {
	            $maybe_enabled = true;
            } else {
	            $maybe_enabled = false;
            }
        }

	    $maybe_enabled = apply_filters("wbf/modules/components/is_enabled_for_current_page",$maybe_enabled,$c);
	    $maybe_enabled = apply_filters("wbf/modules/components/{$c->name}/is_enabled_for_current_page",$maybe_enabled);
	    return $maybe_enabled;
    }

    static function addLoadedComponent( \WBF\modules\components\Component $c ) {
        global $loaded_components;
        if ( ! in_array( $c->name, $loaded_components ) ) {
            $loaded_components[ $c->name] = $c;
        }
    }

    /**
     * Enable or disable components if necessary
     */
    static function toggle_components(){
        global $plugin_page;
        if(is_admin() && isset($_GET['page']) && $_GET['page'] == "waboot_components"){
            if ( isset( $_GET['enable'] ) ) {
                $component_name = $_GET['enable'];
                try {
                    self::enable( $component_name, ComponentsManager::is_child_component( $component_name ) );
                } catch ( \Exception $e ) {
                    self::$last_error = $e->getMessage();
                }
            } elseif ( isset( $_GET['disable'] ) ) {
                $component_name = $_GET['disable'];
                try {
                    self::disable( $component_name, ComponentsManager::is_child_component( $component_name ) );
                } catch ( \Exception $e ) {
                    self::$last_error = $e->getMessage();
                }
            } elseif( isset( $_POST['submit-components-options']) ){
                $registered_components = self::getAllComponents();
                $registered_components_status = isset($_POST['components_status']) ? $_POST['components_status'] : array();
                foreach($registered_components as $component_name => $component_data){
                    if(!array_key_exists($component_name,$registered_components_status)){
                        $registered_components_status[$component_name] = "off";
                    }
                }
                foreach($registered_components_status as $component_name => $component_status){
                    if($component_status == "on" ){
                        if(!self::is_active($registered_components[$component_name])){
	                        try{
		                        self::enable( $component_name, ComponentsManager::is_child_component( $component_name ) );
	                        }catch(\Exception $e){
		                        self::$last_error = $e->getMessage();
		                        wbf_admin_show_message(self::$last_error,"error");
	                        }
                        }
                    }else{
                        if(self::is_active($registered_components[$component_name])){
                            try{
	                            self::disable( $component_name, ComponentsManager::is_child_component( $component_name ) );
                            }catch(\Exception $e){
	                            self::$last_error = $e->getMessage();
	                            wbf_admin_show_message(self::$last_error,"error");
                            }
                        }
                    }
                }
            }
        }
    }

    static function components_admin_page() {

        if( (isset($_GET['enable']) || isset($_GET['disable'])) && !empty(self::$last_error) ){
            ?>
            <div class="error">
                <p><?php echo self::$last_error; ?></p>
            </div>
        <?php
        }

        if(isset($_POST['reset'])){
            self::reset_components_state();
        }

        $registered_components = self::getAllComponents();

        if(isset($_POST['submit-components-options'])){
	        $is_active_component_option = function($opt_name) use($registered_components){
		        preg_match("/^([a-zA-Z0-9]+)_/",$opt_name,$matches);
		        if(isset($matches[1])){
			        $component_name = $matches[1];
			        if(isset($registered_components[$component_name])){
				        $component_data = $registered_components[$component_name];
				        if(self::is_active($component_data) && array_key_exists($component_name,$registered_components)){
					        return true;
				        }
			        }
		        }
		        return false;
	        };

            $of_config_id = Framework::get_options_root_id();
            $of_options = Framework::get_options_values();
            $must_update = false;
            if(isset($_POST[$of_config_id])){
	            $options_to_update = $_POST[$of_config_id];
	            //Add to $ootions_to_update the disabled checkbox:
	            foreach($of_options as $opt_name => $opt_value){
	                if($is_active_component_option($opt_name)){
		                if(!isset($options_to_update[$opt_name]) && Framework::get_option_type($opt_name) == "checkbox"){
			                $options_to_update[$opt_name] = false; //If an option does not exists in $_POST then, it is a checkbox that was set to 0, so change the value...
			                $of_options[$opt_name] = false;
		                }
		                if(isset($options_to_update[$opt_name]) && Framework::get_option_type($opt_name) == "multicheck"){
			                foreach($options_to_update[$opt_name] as $k => $v){
				                //The current checkbox value does not exists in the theme_options array, so add it...
				                if(!array_key_exists($k,$of_options[$opt_name])){
					                $of_options[$opt_name][$k] = "1";
					                $must_update = true; //in this case, always force update
				                }
			                }
			                //Now se to "false" all disabled checkbox, and to "1" all enabled checkbox
			                foreach($of_options[$opt_name] as $k => $v){
				                if(!isset($options_to_update[$opt_name][$k])){
					                $options_to_update[$opt_name][$k] = false;
				                }else{
					                $options_to_update[$opt_name][$k] = "1";
				                }
			                }
		                }
	                }
                }
	            //Check if we must update something...
	            foreach($options_to_update as $opt_name => $opt_value){
		            if( (isset($of_options[$opt_name]) && $of_options[$opt_name] != $opt_value) || !isset($of_options[$opt_name]) ){
			            $of_options[$opt_name] = $opt_value;
			            $must_update = true;
		            }
	            }
            }
            if($must_update)
	            Framework::update_theme_options($of_options);

            //Set the flag that tells that the components was saved at least once
            $theme = wp_get_theme();
            $components_already_saved = (array) get_option( "wbf_components_saved_once", array() );
            if(!in_array($theme->get_stylesheet(),$components_already_saved)){
		        $components_already_saved[] = $theme->get_stylesheet();
		        update_option("wbf_components_saved_once", $components_already_saved);
            }
        }

        $components_options = apply_filters("wbf_components_options",array());
        $compiled_components_options = array();
        $current_element = "";
        foreach($components_options as $key => $option){
            if($option['type'] == "heading"){
                $current_element = preg_replace("/ Component/","",$option['name']);
                $compiled_components_options[$current_element] = array();
                continue;
            }
            $compiled_components_options[$current_element][] = $components_options[$key];
        }

        ?>
        <div id="componentframework-wrapper" class="wrap">
            <div class="componentframework-header">
                <h2><?php _e( "Components", "wbf" ); ?></h2>
            </div>

            <div id="componentframework-content-wrapper">
                <div class="nav-tab-wrapper">
                    <ul>
                        <li><a class="nav-tab" id="component-main-tab" data-show-comp-settings='component-main' href="#component-main">Available components</a></li>
                        <?php foreach($registered_components as $comp_data): if(self::is_active($comp_data)) : ?>
                            <li><a class="nav-tab" id="component-<?php echo $comp_data['nicename']; ?>-link" data-show-comp-settings='component-<?php echo $comp_data['nicename']; ?>' href="#component-<?php echo $comp_data['nicename']; ?>"><?php echo ucfirst($comp_data['nicename']); ?></a></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <div id="componentframework-metabox" class="metabox-holder">
                    <div id="componentframework" class="postbox">
                        <form method="post" action="admin.php?page=waboot_components">
                            <div id="component-main" class="group">
                                <table class="wp-list-table widefat components">
                                    <thead>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col" id="name"
                                            class="manage-column column-name"><?php _e( "Component", "wbf" ) ?></th>
                                        <th scope="col" id="description"
                                            class="manage-column column-description"><?php _e( "Enable\Disable", "wbf" ) ?></th>
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col" id="name"
                                            class="manage-column column-name"><?php _e( "Component", "wbf" ) ?></th>
                                        <th scope="col" id="description"
                                            class="manage-column column-description"><?php _e( "Enable\Disable", "wbf" ) ?></th>
                                    </tr>
                                    </tfoot>
                                    <tbody id="the-list">
                                    <?php $i=1; foreach ( $registered_components as $comp_data ) : ?>
                                        <tr id="<?php echo $comp_data['nicename']; ?>" <class="<?php print_component_status( $comp_data ); ?> <?php if($i%2 == 0) echo "even"; else echo "odd"; ?>">
                                        <?php
                                            //$data = get_plugin_data($comp_data['file']);
                                            $data = self::get_component_data( $comp_data['file'] );
                                        ?>
                                        <th></th>
                                        <th class="component-data column-description desc">
                                            <strong><?php echo $data['Name']; ?></strong>
                                            <div class="component-description">
                                                <?php echo $data['Description']; ?>
                                                <?php if ( self::is_child_component( $comp_data ) ) : ?>
                                                    <p class="child-component-notice">
                                                        <?php _e( "This is a component of the current child theme", "wbf" ); ?>
                                                        <?php
                                                        if ( isset( $comp_data['override'] ) ) {
                                                            if ( $comp_data['override'] ) {
                                                                _e( ", and <strong>override a core component</strong>", "wbf" );
                                                            }
                                                        }
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="<?php print_component_status($comp_data); ?> second plugin-version-author-uri">
                                                <?php
                                                $component_meta = array();
                                                if ( ! empty( $data['Version'] ) ) {
                                                    $component_meta[] = sprintf( __( 'Version %s' ), $data['Version'] );
                                                }
                                                if ( ! empty( $data['Author'] ) ) {
                                                    $author = $data['Author'];
                                                    if ( ! empty( $data['AuthorURI'] ) ) {
                                                        $author = '<a href="' . $data['AuthorURI'] . '" title="' . esc_attr__( 'Visit author homepage' ) . '">' . $data['Author'] . '</a>';
                                                    }
                                                    $component_meta[] = sprintf( __( 'By %s' ), $author );
                                                }
                                                if ( ! empty( $plugin_data['PluginURI'] ) ) {
                                                    $component_meta[] = '<a href="' . $data['ComponentURI'] . '" title="' . esc_attr__( 'Visit plugin site' ) . '">' . __( 'Visit plugin site' ) . '</a>';
                                                }

                                                echo implode( ' | ', $component_meta );

                                                ?>
                                            </div>
                                        </th>
                                        <th class="component-actions">
                                            <div class="row-actions visible">
                                                <div class="wb-onoffswitch">
                                                    <?php if ( ! self::is_active( $comp_data ) ) : ?>
                                                        <input id="<?php echo $comp_data['nicename']; ?>_status" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="components_status[<?php echo $comp_data['nicename']; ?>]" >
                                                    <?php else: ?>
                                                        <input id="<?php echo $comp_data['nicename']; ?>_status" class="checkbox of-input wb-onoffswitch-checkbox" type="checkbox" name="components_status[<?php echo $comp_data['nicename']; ?>]" checked="checked">
                                                    <?php endif; ?>
                                                    <label class="wb-onoffswitch-label" for="<?php echo $comp_data['nicename']; ?>_status"><span class="wb-onoffswitch-inner"></span>
                                                        <span class="wb-onoffswitch-switch"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                <?php $i++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php foreach($registered_components as $comp_data): if(self::is_active($comp_data)) : ?>
                            <div id="component-<?php echo $comp_data['nicename']; ?>" class="group" style="display: none;">
                                <h3><?php _e(sprintf("%s Component Settings",ucfirst($comp_data['nicename'])),"wbf"); ?></h3>
                                <?php \WBF\modules\options\GUI::optionsframework_fields($compiled_components_options[$comp_data['nicename']]); ?>
                                <!-- </div> not necessary (for some reason) -->
                                <?php endif; endforeach; ?>
                                <div id="componentframework-submit">
                                    <input type="submit" name="submit-components-options" id="submit" class="button button-primary" value="Save Changes">
                                    <input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore default component activation state', 'wbf' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'wbf' ) ); ?>' );" />
                                </div>
                        </form>
                    </div>
                </div><!-- #componentframework-content -->
            </div><!-- #componentframework-wrap -->
            <?php \WBF::print_copyright(); ?>
        </div><!-- .wrap: end -->
    <?php
    }

    /**
     * Force enable a component
     * @param $component_name
     * @throws \Exception
     */
    static function ensure_enabled( $component_name ){
        if(self::is_present($component_name)){
            if(!self::is_active($component_name)){
                self::enable($component_name, ComponentsManager::is_child_component( $component_name ));
            }
        }
    }

    /**
     * Enable a component or throw an error
     * @param $component_name
     * @param bool $child_component
     * @throws \Exception
     */
    static function enable( $component_name, $child_component = false ) {
        //chiamo onActivate() del componente
        $registered_components = ! $child_component ? self::get_waboot_registered_components() : self::get_child_registered_components();
        if ( array_key_exists( $component_name, $registered_components ) ) {
            $component = $registered_components[ $component_name ];
            require_once( $component['file'] );
            $className = ucfirst( $component_name ) . "Component";
            if ( class_exists( $className ) ) {
                $oComponent = new $className( $component );
                $oComponent->onActivate();
                $oComponent->active = true;
                $registered_components[ $component_name ]['enabled'] = true;
                if ( ! $child_component ) {
                    self::update_waboot_registered_components( $registered_components );
                } //update the WP Option of registered component
                else {
                    self::update_child_registered_components( $registered_components );
                } //update the WP Option of registered component
                self::update_global_components_vars();
            } else {
                throw new \Exception( sprintf( __( "Component class (%s) not defined. Unable to activate the component.", "wbf" ), $component_name ) );
            }
        } else {
            throw new \Exception( sprintf( __( "Component %s not found among registered components. Unable to activate the component.","wbf" ), $component_name ) );
        }
    }

    /**
     * Disable a component ot throw an error
     * @param $component_name
     * @param bool $child_component
     * @throws \Exception
     */
    static function disable( $component_name, $child_component = false ) {
        //chiamo onDeactivate() del componente
        $registered_components = ! $child_component ? self::get_waboot_registered_components() : self::get_child_registered_components();
        if ( array_key_exists( $component_name, $registered_components ) ) {
            $component = $registered_components[ $component_name ];
            require_once( $component['file'] );
            $className = ucfirst( $component_name ) . "Component";
            if ( class_exists( $className ) ) {
                $oComponent = new $className( $component );
                $oComponent->onDeactivate();
                $oComponent->active = false;
            }
            $registered_components[ $component_name ]['enabled'] = false; //If there is no class defined (eg. due to an previous error), then simply disable the component
            if ( ! $child_component ) {
                self::update_waboot_registered_components( $registered_components );
            } //update the WP Option of registered component
            else {
                self::update_child_registered_components( $registered_components );
            } //update the WP Option of registered component
            self::update_global_components_vars();
        } else {
            throw new \Exception( __( "Component not found among registered components. Unable to deactivate the component.","wbf"));
        }
    }

    static function is_child_component( $registered_component ) {
        if ( is_array( $registered_component ) ) {
            if ( $registered_component['child_component'] == true ) {
                return true;
            }
        } else {
            $components = ComponentsManager::getAllComponents();
            foreach ( $components as $name => $c ) {
                if ( $name == $registered_component ) {
                    if ( $c['child_component'] == true ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    static function reset_components_state(){
        $default_components = apply_filters("wbf_default_components",array());
        $registered_components = \WBF\modules\components\ComponentsManager::getAllComponents();
        foreach($registered_components as $c_name => $c_data){
            self::disable($c_name);
        }
        foreach($default_components as $c_name){
            self::ensure_enabled($c_name);
        }
    }

    /**
     * Delete the options which stores the registere components
     */
    static function reset_registered_components(){
        delete_option("waboot_registered_components");
        if(is_child_theme()){
            $template_name = basename(get_stylesheet_directory_uri());
            delete_option( "{$template_name}_registered_components");
        }
    }
}