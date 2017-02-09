<div id="topnav-wrapper" class="topnav-wrapper">
    <div id="topnav-inner" class="topnav-inner <?php echo Waboot\functions\get_option( 'topnav_width','container-fluid' ); ?> ">
        <?php
        // Top Nav widgets
        \Waboot\functions\print_widgets_in_area('topnav');
        ?>
        <?php do_action('waboot/component/topnav/after_widgets')?>
    </div>
</div>