<?php
    $siteUrl = site_url();
    $wpContentUrl = \Waboot\inc\core\utils\Utilities::pathToUrl(WP_CONTENT_DIR);
    $themeUrl = $wpContentUrl.'/themes/waboot';
    $shopUrl = rtrim(get_permalink(wc_get_page_id( 'shop')),'/');
    $ajaxUrl = admin_url('admin-ajax.php');
?>
<?php do_action('waboot/head/start'); ?>
<title><?php echo \wp_get_document_title(); ?> – Waboot</title>
<meta name="robots" content="max-image-preview:large">
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//s.w.org">
<link rel="alternate" type="application/rss+xml" title="Waboot » Feed" href="<?php echo $siteUrl ?>/feed/">
<link rel="alternate" type="application/rss+xml" title="Waboot » Feed dei commenti" href="<?php echo $siteUrl ?>/comments/feed/">
<link rel="alternate" type="application/rss+xml" title="Waboot » Prodotti Feed" href="<?php echo $shopUrl ?>/feed/">
<link rel="stylesheet" id="dashicons-css" href="<?php echo $siteUrl ?>/wp-includes/css/dashicons.css" type="text/css" media="all">
<link rel="stylesheet" id="admin-bar-css" href="<?php echo $siteUrl ?>/css/admin-bar.css" type="text/css" media="all">
<link rel="stylesheet" id="wp-block-library-css" href="<?php echo $siteUrl ?>/wp-includes/css/dist/block-library/style.css" type="text/css" media="all">
<link rel="stylesheet" id="wp-block-library-theme-css" href="<?php echo $siteUrl ?>/wp-includes/css/dist/block-library/theme.css" type="text/css" media="all">
<style id="woocommerce-inline-inline-css" type="text/css">
    .woocommerce form .form-row .required { visibility: visible; }
</style>
<!-- Waboot styles: begin -->
<link rel="stylesheet" id="google-font-css" href="https://fonts.googleapis.com/css?family=Montserrat:400,400i,700,700i&amp;display=swap" type="text/css" media="all">
<link rel="stylesheet" id="main-style-css" href="<?php echo $themeUrl ?>/assets/dist/css/main.min.css" type="text/css" media="all">
<link rel="stylesheet" id="owlcarousel-css-css" href="<?php echo $themeUrl ?>/assets/vendor/owlcarousel/owl.carousel.min.css" type="text/css" media="all">
<link rel="stylesheet" id="venobox-css-css" href="<?php echo $themeUrl ?>/assets/vendor/venobox/venobox.min.css" type="text/css" media="all">
<!-- Waboot styles: end -->
<!-- Global variables: begin -->
<script type="text/javascript" id="global-js-extra">
    var ajaxUrl = "<?php echo $ajaxUrl; ?>";
    var ajax_url = "<?php echo $ajaxUrl; ?>";
    var ajaxurl = "<?php echo $ajaxUrl; ?>";
</script>
<!-- Global variables: end -->
<!-- jQuery: begin -->
<script type="text/javascript" src="<?php echo $siteUrl ?>/wp-includes/js/jquery/jquery.js" id="jquery-core-js"></script>
<!-- jQuery: end -->
<!-- Waboot scripts: begin -->
<script type="text/javascript" src="<?php echo $themeUrl ?>/assets/vendor/owlcarousel/owl.carousel.min.js" id="owlcarousel-js-js"></script>
<script type="text/javascript" src="<?php echo $themeUrl ?>/assets/vendor/venobox/venobox.min.js" id="venobox-js-js"></script>
<script type="text/javascript" src="<?php echo $themeUrl ?>/assets/dist/js/main.pkg.js" id="main-js-js"></script>
<!-- Waboot: end -->
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="<?php echo $siteUrl ?>/wp-includes/wlwmanifest.xml">
<?php do_action('waboot/head/end'); ?>
