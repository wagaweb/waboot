<?php
    $siteUrl = site_url();
    $wpContentUrl = \Waboot\inc\core\utils\Utilities::pathToUrl(WP_CONTENT_DIR);
    $shopUrl = rtrim(get_permalink(wc_get_page_id( 'shop')),'/');
    $ajaxUrl = admin_url('admin-ajax.php');
?>
<meta charset="UTF-8">
<!-- [if IE]> <meta http-equiv="X-UA-Compatible" content="IE=Edge"/> <! [endif]-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php echo $siteUrl ?>/xmlrpc.php">
<title>Prodotti – Waboot</title>
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
<link rel="stylesheet" id="main-style-css" href="<?php echo $wpContentUrl ?>/themes/waboot/assets/dist/css/main.min.css" type="text/css" media="all">
<link rel="stylesheet" id="owlcarousel-css-css" href="<?php echo $wpContentUrl ?>/themes/waboot/assets/vendor/owlcarousel/owl.carousel.min.css" type="text/css" media="all">
<link rel="stylesheet" id="venobox-css-css" href="<?php echo $wpContentUrl ?>/themes/waboot/assets/vendor/venobox/venobox.min.css" type="text/css" media="all">
<!-- Waboot styles: end -->
<noscript><style>.woocommerce-product-gallery{ opacity: 1 !important; }</style></noscript>
<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style><style type="text/css" media="print">#wpadminbar { display:none; }</style>
<style type="text/css" media="screen">
    html { margin-top: 32px !important; }
    * html body { margin-top: 32px !important; }
    @media screen and ( max-width: 782px ) {
        html { margin-top: 46px !important; }
        * html body { margin-top: 46px !important; }
    }
</style>
<!-- Global variables: begin -->
<script type="text/javascript" id="query-monitor-js-extra">
    var ajaxUrl = "<?php echo $ajaxUrl; ?>"
    var ajax_url = "<?php echo $ajaxUrl; ?>"
    var ajaxurl = "<?php echo $ajaxUrl; ?>"
</script>
<!-- Global variables: end -->
<!-- jQuery: begin -->
<script type="text/javascript" src="<?php echo $siteUrl ?>/wp-includes/js/jquery/jquery.js" id="jquery-core-js"></script>
<!-- jQuery: end -->
<?php if(\Waboot\inc\core\utils\Utilities::isPluginActive('waga-gdpr-compliance/waga-gdpr-compliance.php')): ?>
    <link rel="stylesheet" id="wgdpr-styles-css" href="<?php echo $wpContentUrl ?>/plugins/waga-gdpr-compliance/assets/dist/css/frontend.min.css" type="text/css" media="all">
    <link rel="stylesheet" id="cookieconsent-styles-css" href="<?php echo $wpContentUrl ?>/waga-gdpr-compliance/assets/dist/css/cookieconsent.min.css?ver=1596015771" type="text/css" media="all">
    <script type="text/javascript" src="<?php echo $wpContentUrl ?>/plugins/waga-gdpr-compliance/assets/dist/js/cookieconsent.min.js" id="cookieconsent-script-js"></script>
<?php endif; ?>
<!-- Waboot scripts: begin -->
<script type="text/javascript" src="<?php echo $wpContentUrl ?>/themes/waboot/assets/vendor/owlcarousel/owl.carousel.min.js" id="owlcarousel-js-js"></script>
<script type="text/javascript" src="<?php echo $wpContentUrl ?>/themes/waboot/assets/vendor/venobox/venobox.min.js" id="venobox-js-js"></script>
<script type="text/javascript" src="<?php echo $wpContentUrl ?>/themes/waboot/assets/dist/js/main.pkg.js" id="main-js-js"></script>
<!-- Waboot: end -->
<?php if(\Waboot\inc\core\utils\Utilities::isPluginActive('query-monitor/query-monitor.php')): ?>
    <link rel="stylesheet" id="query-monitor-css" href="<?php echo $wpContentUrl ?>/plugins/query-monitor/assets/query-monitor.css" type="text/css" media="all">
    <script type="text/javascript" src="<?php echo $wpContentUrl ?>/plugins/query-monitor/assets/query-monitor.js?ver=1624435691" id="query-monitor-js"></script>
<?php endif; ?>
<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="<?php echo $siteUrl ?>/wp-includes/wlwmanifest.xml">
