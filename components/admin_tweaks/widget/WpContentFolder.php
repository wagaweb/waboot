<?php
/**
 * Folder Size Dashboard Widget
 *
 */


class WpContentFolder
{
	/**
	 * Transient time 2 hours
	 *
	 * @since   2.3.7
	 */
	const TIME = 7200;

	/**
	 * Transient prefix
	 *
	 * @since   2.3.7
	 */
	const TRANSI = 'at_folder_sizes_';

	/**
	 * Select widget
	 *
	 * @since   2.3.7
	 */
	static $what_widget;

	/**
	 * Select widget
	 *
	 * @since   2.3.7
	 */
	static $widget_config = array(
		'wpcontent'      => array(
			'id'       => 'b5f_folder_sizes',
			'callback' => 'widget1',
		),
		'root' => array(
			'id'       => 'b5f_root_sizes',
			'callback' => 'widget2',
		)
	);

	/**
	 * Adds actions and such.
	 *
	 * @since   2.3.7
	 * @access  public
	 * @uses    add_action
	 */
	public static function init( $what = array( ) )
	{
		self::$what_widget = $what;
		self::$widget_config['root']['title'] = __( 'Root directories', 'waboot' );
		self::$widget_config['wpcontent']['title'] = __( 'Wp-content directories', 'waboot' );
		$hook = is_multisite() ? 'network_' : '';
		add_action( "wp_{$hook}dashboard_setup", array( __CLASS__, 'dashboard_setup' ) );
	}


	/**
	 * Hooked into `template_redirect`.  Adds the admin bar stick/unstick
	 * button if we're on a single post page and the current user can edit
	 * the post
	 *
	 * @since   2.3.7
	 * @access  public
	 * @uses    add_action
	 */
	public static function dashboard_setup()
	{
		// Admins only
		if( !current_user_can( 'install_plugins' ) )
			return;

		foreach( self::$what_widget as $wid )
		{
			wp_add_dashboard_widget(
				self::$widget_config[$wid]['id'], self::$widget_config[$wid]['title'], array(
				__CLASS__, self::$widget_config[$wid]['callback'] ), array( __CLASS__,
					'widget_handle' )
			);
		}
		add_action( 'admin_head', array( __CLASS__, 'head_style' ) );
	}


	/**
	 * Prints table styles in dashboard head
	 *
	 * @since 2.3.7
	 * @access public
	 * @uses MP6 Special rules if the plugin is active
	 */
	public static function head_style()
	{
		echo '<style type="text/css">
		#b5f_folder_sizes .inside, #b5f_root_sizes .inside {
			margin:0;padding:0
		} 
		.mtt-dash-widget tbody tr:hover {
			background-color: #FFFACD
		} 
		.alternate{
			font-weight:bold
		}
		#b5f_folder_sizes .dashboard-widget-control-form,
		#b5f_root_sizes .dashboard-widget-control-form {
			padding: 5px 0 20px 20px;
		}';
		if( defined( 'MP6' ) )
		{
			echo '
			.mtt-dash-widget td {
				line-height:0.8em
			} 
			';
		}
		echo '</style>';
	}


	/**
	 * WPContent widget display
	 *
	 * @since 2.3.7
	 * @access public
	 */
	public static function widget1()
	{
		$dir_list = glob( WP_CONTENT_DIR . '/*', GLOB_ONLYDIR );
		self::printFullTable( 'Files', WP_CONTENT_DIR, $dir_list, 'wpcontent' );
	}


	/**
	 * Root widget display
	 *
	 * @since 2.3.7
	 * @access public
	 */
	public static function widget2()
	{
		$dir_list = glob( ABSPATH . '/*', GLOB_ONLYDIR );
		self::printFullTable( 'Files', ABSPATH, $dir_list, 'root' );
	}


	/**
	 * Used for both Widgets configuration
	 *
	 * @since 2.3.7
	 * @access public
	 * @uses delete_transient Reset cache
	 */
	public static function widget_handle()
	{
		if( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST[self::TRANSI] ) )
		{
			foreach( self::$what_widget as $wid )
				delete_transient( self::TRANSI . $wid );
		}
		$name = self::TRANSI;
		$cache_msg = count( self::$what_widget ) > 1 ? __( 'Clears both widgets caches', 'waboot' ) : __( 'Clear widget cache', 'waboot' );
		echo "<p><label><input name='$name' id='$name' type='checkbox' value='1' /> Check to empty the cache</label><br /><em style='margin-left: 23px'>$cache_msg</em></p>";
	}


	/**
	 * Print widgets contents
	 *
	 * @param string    $title
	 * @param string    $root           Initial directory to scan
	 * @param array     $dir_list       Directory list of folders
	 * @param string    $transient_name
	 *
	 * @since 2.3.7
	 * @access private
	 * @uses set_transient
	 */
	private static function printFullTable( $title, $root, $dir_list, $transient_name )
	{
		// $upload_dir 	= wp_upload_dir();
		// dirSize($upload_dir['basedir'])
		self::printTable( $title );
		self::printDirectoryList( $dir_list, $transient_name );
		$cache = get_transient( self::TRANSI . $transient_name );
		if( !isset( $cache['root_folder'] ) )
		{
			$root_size = self::dirSize( $root );
			$cache['root_folder'] = $root_size;
			set_transient( self::TRANSI . $transient_name, $cache, self::TIME );
		}
		else
			$root_size = $cache['root_folder'];

		printf(
			'</tbody>
				<tfoot><tr>
					<th class="row-title">%s</th>
					<th>%s</th>
				</tr></tfoot>', __( 'Total', 'waboot' ), $root_size
		);
		echo '</table>';
	}


	/**
	 * Prints the start of the table
	 *
	 * @param string $title
	 *
	 * @since 2.3.7
	 * @access private
	 */
	private static function printTable( $title = '' )
	{
		?>
		<table class="widefat mtt-dash-widget">
		<thead>
		<tr>
			<th class="row-title"><strong><?php echo $title; ?></strong></th>
			<th><strong>Size</strong></th>
		</tr>
		</thead>
		<tbody>
		<?php
	}


	/**
	 * Prints the list of folders and its sizes
	 *
	 * @param array     $directories    List of folders inside a directory
	 * @param string    $transient_name
	 *
	 * @since 2.3.7
	 * @access private
	 */
	private static function printDirectoryList( $directories, $transient_name )
	{
		$count = 0;
		$cache = get_transient( self::TRANSI . $transient_name );
		$transi = array( );
		if( !$cache ):
			foreach( $directories as $dir )
			{
				$alt = (++$count % 2 ) ? 'alternate' : '';
				$name = basename( $dir );
				$size = self::dirSize( $dir );
				$transi[$name] = $size;
				printf(
					'<tr class="%s">
						<td class="row"><tt>%s</tt></td>
						<td><tt>%s</tt></td>
					</tr>', $alt, $name, $size
				);
			}
			set_transient( self::TRANSI . $transient_name, $transi, self::TIME );
		else:
			unset( $cache['root_folder'] ); // Control value only
			foreach( $cache as $name => $size )
			{
				$alt = (++$count % 2 ) ? 'alternate' : '';
				printf(
					'<tr class="%s">
						<td class="row">%s</td>
						<td>%s</td>
					</tr>', $alt, $name, $size
				);
			}
		endif;
	}


	/**
	 * Iterates through a folder and get its size
	 *
	 * From: http://stackoverflow.com/a/18288029
	 *
	 * @param string $directory
	 * @return string Formatted size
	 *
	 * @since 2.3.7
	 * @access private
	 */
	private static function dirSize( $directory )
	{
		$size = 0;
		foreach( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $directory ) ) as $file )
		{
			try {
				$calc = $file->getSize();
			} catch( Exception $e ) {
				$calc = 0;
			}
			$size += $calc;
		}

		return self::format_size( $size );
	}


	/**
	 * Formats the size into human readable
	 *
	 * @param integer $size
	 * @return string
	 *
	 * @since 2.3.7
	 * @access private
	 */
	private static function format_size( $size )
	{
		$units = explode( ' ', 'B KB MB GB TB PB' );
		$mod = 1024;
		for( $i = 0; $size > $mod; $i++ )
			$size /= $mod;

		$endIndex = strpos( $size, "." ) + 3;
		return substr( $size, 0, $endIndex ) . ' ' . $units[$i];
	}
}
