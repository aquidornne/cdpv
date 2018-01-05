<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <link href="<?php echo $this->webroot ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $this->webroot ?>font-awesome/css/font-awesome.css" rel="stylesheet">

    <link href="<?php echo $this->webroot ?>css/animate.css" rel="stylesheet">
    <link href="<?php echo $this->webroot ?>css/style.css" rel="stylesheet">
</head>

    <body class="gray-bg">

        <div class="login text-center loginscreen  animated fadeInDown">
            <div>
                <div>
                    <a href="<?php echo $this->webroot ?>" class="img-circle"><img src="<?php echo $this->webroot; ?>img/logo_cdpv.PNG" style="max-width: 300px;"></a>
                </div>

                <?php echo $this->Form->create('User', array('action' => 'login', 'class' => 'm-t')); ?>
                    <div class="form-group">
                        <?php echo $this->Form->input('User.email', array('div' => false, 'class' => 'form-control', 'label' => false, 'placeholder' => 'E-mail', 'type' => 'text')); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $this->Form->input('User.password', array('div' => false, 'class' => 'form-control', 'label' => false, 'placeholder' => 'Senha')); ?>
                    </div>

                    <?php echo $this->Form->submit('Entrar', array('class' => 'btn btn-primary block full-width m-b', 'div'=>false));?>
                <?php echo $this->Form->end(); ?>
            </div>

            <span><?php echo $this->Session->flash(); ?></span>
        </div>

        <!-- Mainly scripts -->
        <script src="<?php echo $this->webroot ?>js/jquery-2.1.1.js"></script>
        <script src="<?php echo $this->webroot ?>js/bootstrap.min.js"></script>

    </body>

</html>