<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="author" content="Inovexia Software Services">    
    <meta name="description" content="<?php echo SITE_TITLE; ?> ">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="application-name" content="Easy Coaching App">
    <meta name="apple-mobile-web-app-title" content="Easy Coaching App">
    <meta name="theme-color" content="#4285F4">
    <meta name="msapplication-navbutton-color" content="#4285F4">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="msapplication-starturl" content="<?php echo site_url (''); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!--==== Apple Touch Icons ====-->
    <link rel="icon" sizes="128x128" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon128.png'); ?>">
    <link rel="apple-touch-icon" sizes="128x128" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon128.png'); ?>">
    <link rel="icon" sizes="192x192" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon192.png'); ?>">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon192.png'); ?>">
    <link rel="icon" sizes="256x256" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon256.png'); ?>">
    <link rel="apple-touch-icon" sizes="256x256" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon256.png'); ?>">
    <link rel="icon" sizes="384x384" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon384.png'); ?>">
    <link rel="apple-touch-icon" sizes="384x384" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon384.png'); ?>">
    <link rel="icon" sizes="512x512" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon512.png'); ?>">
    <link rel="apple-touch-icon" sizes="512x512" href="<?php echo base_url(THEME_PATH . 'assets/img/touch/app-icon512.png'); ?>">

    <!--==== Fav-icon ====-->
    <link rel="icon" href="<?php echo base_url(THEME_PATH . 'assets/img/fav-icon.png'); ?>" type="image/png" sizes="512x512">
    <!--==== Manifest JSON ====-->
    <link rel="manifest" href="<?php echo base_url ('manifest.json'); ?>">

    <title><?php if (isset($page_title)) echo $page_title . ': '; echo $this->session->userdata ('site_title'); ?></title>

    <!--==== Core CSS ====-->
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/font/iconsmind-s/css/iconsminds.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/font/simple-line-icons/css/simple-line-icons.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/font/fontawesome/fontawesome.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/css/vendor/bootstrap.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/css/vendor/bootstrap.rtl.only.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/css/vendor/bootstrap-float-label.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/css/scrollbar.light.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/css/dore.light.blue.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(THEME_PATH . 'assets/css/main.css'); ?>" />

    <!--==== Other CSS ====-->
    <!-- Toastr CSS -->
    <link type="text/css" href="<?php echo base_url(THEME_PATH . 'assets/css/toastr.min.css'); ?>" rel="stylesheet">

    <!-- Dynamically included CSS -->
    <?php
    if (isset ($dyn_css) && !empty ($dyn_css)) {
        foreach ($dyn_css as $css) {
            echo '<link rel="stylesheet" href="'.base_url(THEME_PATH . $css).'" />';
        }
    }
    ?>

    <!-- Dynamically included Javascript -->
    <?php
    if (isset ($script_header) && !empty ($script_header)) {
        foreach ($script_header as $script) {
            echo '<script src="'.base_url($script).'" type="text/javascript"></script>'; 
        }
    }
    ?>

</head>

<body class="background show-spinners no-footer">
    <div class="fixed-background"></div>
    <main>
        <div class="container">