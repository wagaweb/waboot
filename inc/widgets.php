<?php

if ( ! function_exists( 'waboot_widgets_init' ) ):
/**
 * Register widgetized areas and widgets
 *
 * @package Waboot
 * @since 0.1.0
 */
function waboot_widgets_init() {

    /* Sidebars */

	register_sidebar( array(
		'name'          => __( 'Primary Sidebar', 'waboot' ),
		'description'   => __( 'The main widget area displayed in the sidebar.', 'waboot' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Secondary Sidebar', 'waboot' ),
		'description'   => __( 'The main widget area displayed in the sidebar.', 'waboot' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 1', 'waboot' ),
		'description'   => __( 'The footer widget area displayed after all content.', 'waboot' ),
		'id'            => 'footer-1',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 2', 'waboot' ),
		'description'   => __( 'The second footer widget area, displayed below the Footer widget area.', 'waboot' ),
		'id'            => 'footer-2',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => __( 'Footer 3', 'waboot' ),
		'description'   => __( 'The third footer widget area, displayed below the Footer widget area.', 'waboot' ),
		'id'            => 'footer-3',
		'before_widget' => '',
		'after_widget'  => '',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

    register_sidebar( array(
        'name'          => __( 'Footer 4', 'waboot' ),
        'description'   => __( 'The fourth footer widget area, displayed below the Footer widget area.', 'waboot' ),
        'id'            => 'footer-4',
        'before_widget' => '',
        'after_widget'  => '',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name' => 'Top Bar',
        'id' => 'topbar',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="rounded">',
        'after_title' => '</h2>',
    ) );

    register_sidebar( array(
        'name' => 'Banner',
        'id' => 'banner',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="rounded">',
        'after_title' => '</h2>',
    ) );

    register_sidebar( array(
        'name' => 'Content Bottom',
        'id' => 'contentbottom',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="rounded">',
        'after_title' => '</h2>',
    ) );

    register_sidebar( array(
        'name' => 'Header Left',
        'id' => 'header-left',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="rounded">',
        'after_title' => '</h2>',
    ) );

    register_sidebar( array(
        'name' => 'Header Right',
        'id' => 'header-right',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="rounded">',
        'after_title' => '</h2>',
    ) );

    /* Widgets */
    locate_template( '/inc/widgets/widget-login-form.php', true );
    locate_template( '/inc/widgets/widget-nav-stacked-pills-menu.php', true );
    locate_template( '/inc/widgets/widget-featured-post-slider.php', true );
    register_widget( 'waboot_Widget_Login' );
    register_widget( 'Nav_Stacked_Pills_Menu_Widget' );
    register_widget( 'Waboot_Feaured_Post_Slider' );
}
add_action( 'widgets_init', 'waboot_widgets_init' );
endif;

if ( ! function_exists( 'waboot_wp_login_form' ) ):
function waboot_wp_login_form( $args = array() ) {
	$defaults = array(
		'echo'           => true,
		'redirect'       => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], // Default redirect is back to the current page
		'form_id'        => 'loginform',
		'label_username' => __( 'Username', 'waboot' ),
		'label_password' => __( 'Password', 'waboot' ),
		'label_remember' => __( 'Remember Me', 'waboot' ),
		'label_log_in'   => __( 'Log In', 'waboot' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'remember'       => true,
		'value_username' => '',
		'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
	);
	$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );

	$form = '
	<form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '" method="post">
		' . apply_filters( 'login_form_top', '', $args ) . '
		<p class="login-username">
			<label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
			<input type="text" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input form-control" value="' . esc_attr( $args['value_username'] ) . '" size="20" tabindex="10" />
		</p>
		<p class="login-password">
			<label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
			<input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input form-control" value="" size="20" tabindex="20" />
		</p>
		' . apply_filters( 'login_form_middle', '', $args ) . '
		' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever" tabindex="90"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
		<p class="login-submit">
			<input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="btn btn-success" value="' . esc_attr( $args['label_log_in'] ) . '" tabindex="100" />
			<input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
		</p>
		' . apply_filters( 'login_form_bottom', '', $args ) . '
	</form>';

	if ( $args['echo'] )
		echo $form;
	else
		return $form;
}
endif;