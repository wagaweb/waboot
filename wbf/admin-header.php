<div class="waboot-header">
    <div class="waboot-header-inner">
        <div class="waboot-header-logo">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/waboot-marchio.png" />
            <h1><?php if(isset($page_title)) echo $page_title; ?></h1>
            <?php if($page_title == 'Components') : ?><a class="btn-add" href="<?php echo admin_url('admin.php?page=wbf_components&section=add_new'); ?>"><?php _e('Add New', 'waboot') ?></a><?php endif ?>
        </div>
        <div class="waboot-header-nav">
            <ul>
                <li><a href="/wp-admin/admin.php?page=wbf_options">Theme Options</a></li>
                <li><a href="/wp-admin/admin.php?page=wbf_components">Components</a></li>
                <!--<li><a href="#">Plugins</a></li>-->
                <li><a href="/wp-admin/admin.php?page=wbf_status">WBF Status</a></li>
            </ul>
        </div>
    </div>
</div>