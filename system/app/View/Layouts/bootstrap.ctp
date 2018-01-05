<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>Hyde One Transport</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <link href="<?php echo $this->webroot; ?>css/bootstrap.min.css" rel="stylesheet">
        <link href="<?php echo $this->webroot; ?>font-awesome/css/font-awesome.css" rel="stylesheet">

        <!-- Toastr style -->
        <link href="<?php echo $this->webroot; ?>css/plugins/toastr/toastr.min.css" rel="stylesheet">

        <!-- Gritter -->
        <link href="<?php echo $this->webroot; ?>js/plugins/gritter/jquery.gritter.css" rel="stylesheet">

        <link href="<?php echo $this->webroot; ?>css/animate.css" rel="stylesheet">
        <link href="<?php echo $this->webroot; ?>css/style.css" rel="stylesheet">

        <link href="<?php echo $this->webroot; ?>css/plugins/steps/jquery.steps.css" rel="stylesheet">

        <!-- Fancybox -->
        <link href="<?php echo $this->webroot; ?>js/plugins/fancybox/jquery.fancybox.css" rel="stylesheet">

        <link href="<?php echo $this->webroot; ?>css/plugins/summernote/summernote.css" rel="stylesheet">
        <link href="<?php echo $this->webroot; ?>css/plugins/summernote/summernote-bs3.css" rel="stylesheet">

        <link href="<?php echo $this->webroot; ?>css/custom.css" rel="stylesheet">

        <!-- Mainly scripts -->
        <script src="<?php echo $this->webroot ?>js/jquery-2.1.1.js"></script>
		<!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>-->

		<!-- Masks -->
		<script src="<?php echo $this->webroot; ?>js/jquery.maskedinput.js"></script>
		<script src="<?php echo $this->webroot; ?>js/jquery.maskMoney.js"></script>

		<script src="<?php echo $this->webroot ?>js/bootstrap.min.js"></script>
        <script src="<?php echo $this->webroot ?>js/plugins/metisMenu/jquery.metisMenu.js"></script>
        <script src="<?php echo $this->webroot ?>js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        <!-- Flot -->
        <script src="<?php echo $this->webroot; ?>js/plugins/flot/jquery.flot.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/flot/jquery.flot.tooltip.min.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/flot/jquery.flot.spline.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/flot/jquery.flot.resize.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/flot/jquery.flot.pie.js"></script>

        <!-- Peity -->
        <script src="<?php echo $this->webroot; ?>js/plugins/peity/jquery.peity.min.js"></script>
        <script src="<?php echo $this->webroot ?>js/demo/peity-demo.js"></script>

        <!-- Custom and plugin javascript -->
        <script src="<?php echo $this->webroot; ?>js/inspinia.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/pace/pace.min.js"></script>

        <!-- jQuery UI -->
        <script src="<?php echo $this->webroot; ?>js/plugins/jquery-ui/jquery-ui.min.js"></script>

        <!-- GITTER -->
        <script src="<?php echo $this->webroot; ?>js/plugins/gritter/jquery.gritter.min.js"></script>

        <!-- Sparkline -->
        <script src="<?php echo $this->webroot; ?>js/plugins/sparkline/jquery.sparkline.min.js"></script>

        <!-- Sparkline demo data  -->
        <script src="<?php echo $this->webroot; ?>js/demo/sparkline-demo.js"></script>

        <!-- ChartJS-->
        <script src="<?php echo $this->webroot; ?>js/plugins/chartJs/Chart.min.js"></script>

        <!-- Toastr -->
        <script src="<?php echo $this->webroot; ?>js/plugins/toastr/toastr.min.js"></script>

        <script src="<?php echo $this->webroot; ?>js/plugins/staps/jquery.steps.min.js"></script>

        <script src="<?php echo $this->webroot; ?>js/plugins/metisMenu/jquery.metisMenu.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

        <!-- Custom and plugin javascript -->
        <script src="<?php echo $this->webroot; ?>js/inspinia.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/pace/pace.min.js"></script>

        <!-- Summernote -->
        <script src="<?php echo $this->webroot; ?>js/plugins/summernote/summernote.min.js"></script>

        <!-- Fancybox -->
        <script src="<?php echo $this->webroot; ?>js/plugins/fancybox/jquery.fancybox.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/fancybox/jquery.fancybox.pack.js"></script>

        <!-- Jquery Confirm -->
        <script src="<?php echo $this->webroot; ?>js/plugins/jquery-confirm/jquery.confirm.js"></script>
        <script src="<?php echo $this->webroot; ?>js/plugins/jquery-confirm/jquery.confirm.min.js"></script>

        <link href="<?php echo $this->webroot; ?>css/plugins/iCheck/custom.css" rel="stylesheet">
        <script src="<?php echo $this->webroot; ?>js/plugins/iCheck/icheck.min.js"></script>

		<!-- Select2 -->

		<!--
		<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>

		<link href="<?php echo $this->webroot; ?>css/plugins/select2.css" rel="stylesheet">
		<script src="<?php echo $this->webroot; ?>js/plugins/select2.min.js"></script>
		-->

		<link href="<?php echo $this->webroot; ?>css/plugins/chosen/chosen.css" rel="stylesheet">
		<script src="<?php echo $this->webroot; ?>js/plugins/chosen/chosen.jquery.js"></script>

		<link href="<?php echo $this->webroot; ?>css/plugins/datepicker/datepicker3.css" rel="stylesheet">
		<script src="<?php echo $this->webroot; ?>js/plugins/datepicker/bootstrap-datepicker.js"></script>

        <link href="<?php echo $this->webroot; ?>css/plugins/fullcalendar/fullcalendar.css" rel="stylesheet">
        <link href="<?php echo $this->webroot; ?>css/plugins/fullcalendar/fullcalendar.print.css" rel="stylesheet" media="print">
		<script src="<?php echo $this->webroot; ?>js/plugins/fullcalendar/moment.min.js"></script>
		<script src="<?php echo $this->webroot; ?>js/plugins/fullcalendar/fullcalendar.min.js"></script>
		<script src="<?php echo $this->webroot; ?>js/plugins/fullcalendar/locale/pt-br.js"></script>

		<script src="<?php echo $this->webroot; ?>js/application.js" type="text/javascript"></script>

    </head>
    <body class="">

        <?php echo $this->Session->flash(); ?>

        <div id="loadingPage">
            <div class="loading"><img src="<?php echo $this->webroot; ?>img/loading/loading-bars.svg"/></div>
        </div>

        <div id="wrapper">
            <div id="page-wrapper" class="gray-bg dashbard-1">
                <?php //echo $this->element('sidebar'); ?>
                <?php echo $this->fetch('content'); ?>
            </div>
        </div>

        <script>
            $(function (){
                application.webroot = '<?php echo $this->webroot; ?>';
                application.urlbase = '<?php echo Router::fullBaseUrl(); ?>';
            });

            $(document).ready(function(){
                $('.summernote').summernote();
                $("a.fancybox").fancybox();
            });
        </script>
        <?php echo $this->fetch('custom_js'); ?>
    </body>
</html>