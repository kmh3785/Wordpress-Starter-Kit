<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en-US" > <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en-US" > <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en-US" "> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en-US" > <!--<![endif]-->
<head>
<!-- <base href="http://base-url-goes-here.com/" /> -->
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width">
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<!--[if lt IE 9]>
		<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
		<![endif]-->
		

		<!-- Mobile viewport optimized: j.mp/bplateviewport -->
		<meta name="viewport" content="width=device-width" />

	<!-- Favicon and Feed -->
	<link rel="shortcut icon" type="image/png" href="<?php echo get_template_directory_uri(); ?>/favicon.png">	
	<!-- <link rel='stylesheet' href='<?php echo get_template_directory_uri(); ?>/css/style.css' type='text/css' media='all' /> -->
	<link rel='canonical' href='' />
	<link rel='shortlink' href='' />

	<?php wp_head(); ?>

</head>
<body data-bodyclass >

