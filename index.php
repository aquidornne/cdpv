<?php
require('init.php');
global $page_current;
$page_current = 'home';

$data_clients = $system->serviceFindClients(array(
        'page' => ((isset($_GET['page']) AND !empty($_GET['page'])) ? $_GET['page'] : 1),
        'limit' => ((isset($_GET['limit']) AND !empty($_GET['limit'])) ? $_GET['limit'] : 10)
    )
);

$data_events = $system->serviceFindEvents(array(
        'page' => ((isset($_GET['page']) AND !empty($_GET['page'])) ? $_GET['page'] : 1),
        'limit' => ((isset($_GET['limit']) AND !empty($_GET['limit'])) ? $_GET['limit'] : 10)
    )
);
?>

<?php include_once('inc/header.php'); ?>

    <main>
        <section class="theme-type-style-01">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <ul class="anim-slider anim-slider01">

                            <li class="anim-slide">
                                <div class="block-content">
                                    <a href="#" class="link-01"></a>
                                    <div class="text01 block-vertical-center">
                                        <div class="noMarginTop text-style-01" title=""><b>Desde 2003, somos líder nacional em treinamento de equipes comerciais.</b></div>
                                    </div>
                                </div>
                                <img src="<?php echo _PROJECT_; ?>img/banners/banner_01.JPG" class="img-bg" style="bottom: 0;">
                            </li>

                            <li class="anim-slide">
                                <div class="block-content">
                                    <a href="#" class="link-01"></a>
                                    <div class="text01 block-vertical-center">
                                        <div class="noMarginTop text-style-01" title=""><b>Mais um parceiro importante para nossa jornada.</b></div>
                                        <div class="noMarginTop text-style-01" title=""><b>Bem vinda, Capemisa!</b></div>
                                    </div>
                                </div>
                                <img src="<?php echo _PROJECT_; ?>img/banners/banner_02.JPG" class="img-bg" style="bottom: 0;">
                            </li>

                            <nav class="anim-arrows">
                                <span class="anim-arrows-prev"></span>
                                <span class="anim-arrows-next"></span>
                            </nav>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <section id="" class="">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <div class="container-main pdt-50 pdb-30">
                            <div class="">
                                <div class="col-xs-12 col-md-7 pdb-20">
                                    <div class="">
                                        <h2 class="no-margin style-02 mgb-20">Bem vindos ao nosso site, conheça um pouco sobre a CDPV</h2>
                                        <p class="text-justify">Reconhecido como líder nacional no treinamento de equipes comerciais, o CDPV (Centro de Desenvolvimento do Profissional de Vendas) está em atividade desde 2003 em diversos estados brasileiros.</p>
                                        <p class="text-justify">Ao longo de nossa trajetória, diversos foram os impactos gerados nas nossas empresas-clientes e em seus colaboradores.</p>
                                        <p class="text-justify">Seguimos cada vez mais dedicados a construção de nossa história de sucesso, que continua a ser escrita com determinação, espírito empreendedor e total comprometimento com nossos clientes – ingredientes que sempre fizeram parte de nosso DNA. Deixe a gente te surpreender.</p>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-md-5 pdb-20">
                                    <div class="box-event">
                                        <h2 class="no-margin style-03 mgb-20">Próximos Eventos</h2>
                                        <div class="">
                                            <?php if(isset($data_events->success) AND $data_events->success == TRUE){ ?>
                                                <?php foreach($data_events->data as $key => $row){ ?>
                                                    <div class="accordion">
                                                        <h3><a href="#"><?php echo $row->Event->title; ?></a></h3>
                                                        <div>
                                                            <div class="">
                                                                <a href="<?php echo _PROJECT_; ?>evento/<?php echo $row->Event->id; ?>/<?php echo Tools::string_rewrite($row->Event->title); ?>" title="<?php echo $row->Event->title; ?>"><?php echo $row->Event->resume; ?></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="btn-box-event">
                                        <a href="<?php echo _PROJECT_; ?>eventos">Ver todos</a>
                                    </div>
                                </div>
                            </div>

                            <div class="">
                                <div class="col-xs-12 col-md-12">
                                    <h2 class="no-margin style-02 mgb-20">Principais Clientes</h2>

                                    <?php if(isset($data_clients->success) AND $data_clients->success == TRUE){ ?>
                                        <div class="slider1">
                                            <?php foreach($data_clients->data as $key => $row){ ?>
                                                <div class="slide"><img src="<?php echo _SYSTEM_FILES_; ?>clients/<?php echo $row->Client->file; ?>" alt="<?php echo $row->Client->comments; ?>" title="<?php echo $row->Client->comments; ?>"></div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        $(".anim-slider01").animateSlider(
            {
                autoplay: true,
                interval: 11000,
                animations: {
                    0: 	//Slide No1
                        {
                            ".link-01": {
                                show: "fadeIn",
                                hide: "fadeOut",
                                delayShow: "delay0s"
                            },
                            ".text01": {
                                show: "bounceInUp",
                                hide: "bounceOutLeft",
                                delayShow: "delay3s"
                            },
                            ".img-bg": {
                                show: "bounceInRight",
                                hide: "fadeOut",
                                delayShow: "delay1s"
                            },
                            ".block-content": {
                                show: "fadeIn",
                                hide: "fadeOut",
                                delayShow: "delay0s"
                            }
                        },
                    1: 	//Slide No1
                        {
                            ".link-01": {
                                show: "fadeIn",
                                hide: "fadeOut",
                                delayShow: "delay0s"
                            },
                            ".text01": {
                                show: "bounceInUp",
                                hide: "bounceOutLeft",
                                delayShow: "delay3s"
                            },
                            ".img-bg": {
                                show: "bounceInRight",
                                hide: "fadeOut",
                                delayShow: "delay1s"
                            },
                            ".block-content": {
                                show: "fadeIn",
                                hide: "fadeOut",
                                delayShow: "delay0s"
                            }
                        }
                }
            }
        );
    </script>

<?php include_once('inc/footer.php'); ?>