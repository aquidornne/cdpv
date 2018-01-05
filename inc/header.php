<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo $config['title']; ?></title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <meta name="viewport" content="width=device-width, user-scalable=no">

    <meta name="keywords" content="<?php echo $config['keywords']; ?>">
    <meta name="rights" content="<?php echo $config['rights']; ?>">
    <meta name="description" content="<?php echo $config['description']; ?>">

	<link rel="shortcut icon" type="image/x-icon" href="<?php echo _PROJECT_; ?>img/logo_cpdv_branca.PNG">

    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/theme.css">
    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/util.css">
	<link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/custom-bootstrap.css">

    <script src="<?php echo _PROJECT_; ?>js/jquery-2.2.0.min.js"></script>
    <script src="<?php echo _PROJECT_; ?>js/bootstrap.js"></script>
    <script src="<?php echo _PROJECT_; ?>js/modernizr.js"></script>

    <!-- Toasty -->
    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/toastr/toastr.min.css">
    <script src="<?php echo _PROJECT_; ?>js/toastr/toastr.js"></script>

    <!-- Add mousewheel plugin (this is optional) -->
    <script src="<?php echo _PROJECT_; ?>js/fancybox/jquery.mousewheel-3.0.6.pack.js"></script>

    <!-- Add fancyBox main JS and CSS files -->
    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/fancybox/jquery.fancybox.css" media="screen">
    <script src="<?php echo _PROJECT_; ?>js/fancybox/jquery.fancybox.js"></script>

    <!-- Add Button helper (this is optional) -->
    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/fancybox/helpers/jquery.fancybox-buttons.css">
    <script src="<?php echo _PROJECT_; ?>js/fancybox/helpers/jquery.fancybox-buttons.js"></script>

    <!-- Add Thumbnail helper (this is optional) -->
    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/fancybox/helpers/jquery.fancybox-thumbs.css">
    <script src="<?php echo _PROJECT_; ?>js/fancybox/helpers/jquery.fancybox-thumbs.js"></script>

    <!-- Add Media helper (this is optional) -->
    <script src="<?php echo _PROJECT_; ?>js/fancybox/helpers/jquery.fancybox-media.js"></script>

    <link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/slider/jquery.animateSlider.css">
    <script src="<?php echo _PROJECT_; ?>js/slider/jquery.animateSlider.js"></script>

    <script src="<?php echo _PROJECT_; ?>js/fancybox/helpers/jquery.fancybox-thumbs.js"></script>

    <script src="<?php echo _PROJECT_; ?>js/jquery.maskedinput.js"></script>

	<script src="<?php echo _PROJECT_; ?>js/jquery.easing.1.3.js"></script>
	<script src="<?php echo _PROJECT_; ?>js/jquery.fitvids.js"></script>

	<script src="<?php echo _PROJECT_; ?>js/bxslider/jquery.bxslider.js"></script>
	<script src="<?php echo _PROJECT_; ?>js/bxslider/jquery.bxslider.min.js"></script>

	<script src="<?php echo _PROJECT_; ?>js/wow.min.js"></script>

	<link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/bxslider/jquery.bxslider.css">

	<link rel="stylesheet" type="text/css" href="<?php echo _PROJECT_; ?>css/animate.css">

    <script src="<?php echo _PROJECT_; ?>js/application.js"></script>

	<script>
		$(document).ready(function(){
		  $('.slider1').bxSlider({
			slideWidth: 230,
			minSlides: 2,
			maxSlides: 4,
			slideMargin: 2,
			controls: false,
			auto: true
		  });
		});

		toastr.options = {
		  "closeButton": true,
		  "debug": false,
		  "newestOnTop": false,
		  "progressBar": false,
		  "positionClass": "toast-bottom-left",
		  "preventDuplicates": false,
		  "onclick": null,
		  "showDuration": "300",
		  "hideDuration": "1000",
		  "timeOut": "5000",
		  "extendedTimeOut": "1000",
		  "showEasing": "swing",
		  "hideEasing": "linear",
		  "showMethod": "fadeIn",
		  "hideMethod": "fadeOut"
		}
	</script>

    <?php echo ((isset($meta_increment)) ? $meta_increment : ''); ?>
</head>
<body>

<div id="loadingPage"><img class="loading" src="<?php echo _PROJECT_; ?>img/loading2.svg"></div>

<header class="">
	<div class="top">
		<div class="container">
			<div class="row">
				<div class="col-xs-6 col-md-4 logo">
					<a class="" href="<?php echo _PROJECT_; ?>"><img class="img-responsive" src="<?php echo _PROJECT_; ?>img/logo_cdpv.PNG" title="" alt=""></a>
				</div>
				<div class="col-xs-6 col-md-1 btn-responsive-menu-col">
					<a id="responsive-menu" href="#" class="btn-responsive-menu type-02"></a>
				</div>
				<div class="col-xs-12 col-md-8">
					<nav id="main-menu">
						<ul class="nav-menu active">
							<?php if($config['menu'] == 0){ ?>
								<li class="">
									<a href="<?php echo _PROJECT_; ?>site" menu="" class="<?php echo (($page_current == 'home') ? 'active' : ''); ?> no-default">
										<div class="div-border">Home</div>
									</a>
								</li>
								<li class="">
									<a href="<?php echo _PROJECT_; ?>eventos" menu="" class="<?php echo (($page_current == 'events') ? 'active' : ''); ?> no-default">
										<div class="div-border">Eventos</div>
									</a>
								</li>
								<li>
									<a href="<?php echo _PROJECT_; ?>fale_conosco" menu="" class="<?php echo (($page_current == 'contact') ? 'active' : ''); ?> no-default">
										<div class="div-border">Contato</div>
									</a>
								</li>
								<li>
									<a target="_blank" href="<?php echo _PROJECT_; ?>system" menu="" class="<?php echo (($page_current == 'system') ? 'active' : ''); ?> no-default">
										<div class="div-border">Administração</div>
									</a>
								</li>
							<?php } else if($config['menu'] == 1){ ?>
								<!-- Responsive -->
							<?php } ?>
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</div>
</header>

<div class="container-general">