<?php
require('init.php');
global $page_current;
$page_current = 'events';

$event = $system->serviceFindEventById(array(
        'event_id' => $_GET['id']
    )
);

$comments = $system->serviceFindComments(array(
        'event_id' => $_GET['id'],
        'q' => ((isset($_GET['q']) AND !empty($_GET['q'])) ? $_GET['q'] : ''),
        'page' => ((isset($_GET['page']) AND !empty($_GET['page'])) ? $_GET['page'] : 1),
        'limit' => ((isset($_GET['limit']) AND !empty($_GET['limit'])) ? $_GET['limit'] : 28)
    )
);

$meta_increment = '
    <meta property="og:url"           content="' . _PROJECT_ . 'evento' . '/' . $event->data->Event->id . '/' . Tools::string_rewrite($event->data->Event->title) . '" />
    <meta property="og:type"          content="website" />
    <meta property="og:title"         content="' . $event->data->Event->title . '" />
    <meta property="og:description"   content="' . $event->data->Event->resume . '" />
    <meta property="og:image"         content="<img src=' . _PROJECT_ . 'system/files/events/' . $event->data->Event->cover . '>" />
';

?>

<?php include_once('inc/header.php'); ?>

    <main>
        <section id="" class="theme-type-style-03">
            <div class="container pdt-100 pdb-80">
                <div class="row">
                    <div class="col-xs-12 col-md-3 pdb-20">
                        <div class="">
                            <form method="get" action="<?php echo _PROJECT_; ?>eventos">
                                <div class="input-group">
                                    <input type="text" name="q" class="input-sm form-control" placeholder="Faça sua busca" value="<?php echo ((isset($_GET['q']) AND !empty($_GET['q'])) ? $_GET['q'] : ''); ?>">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-sm btn-primary" value="Buscar">
                                    </span>
                                </div>

                                <input type="hidden" name="page" value="<?php echo ((isset($_GET['page']) AND !empty($_GET['page'])) ? $_GET['page'] : 1); ?>">
                                <input type="hidden" name="limit" value="<?php echo ((isset($_GET['limit']) AND !empty($_GET['limit'])) ? $_GET['limit'] : 28); ?>">
                            </form>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-9">
                        <div class="bg-grey">
                            <div class="news-home">
                                <?php if(isset($event->data->Event) AND !empty($event->data->Event)){ ?>

                                    <div class="pdb-20">
                                        <h2 class="style-02" title="<?php echo $event->data->Event->title; ?>"><?php echo $event->data->Event->title; ?></h2>
                                    </div>

                                    <div class="pdb-20">
                                        <div class="fb-share-button" data-href="<?php echo _PROJECT_; ?>artigo/<?php echo $event->data->Event->id; ?>/<?php echo Tools::string_rewrite($event->data->Event->title); ?>" data-layout="button_count" data-size="small" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse">Compartilhar</a></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-12 col-md-12 pdb-20">
                                            <img src="<?php echo _PROJECT_; ?>system/files/events/<?php echo $event->data->Event->cover; ?>" class="img-responsive" alt="" title="">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-xs-12 col-md-12 pdb-20">
                                            <?php echo $event->data->Event->content; ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="" class="">
            <div class="container pdt-50 pdb-20">
                <div class="pdb-50">
                    <h2 class="style-02" title="Comentários"><b>Comentários</b></h2>
                </div>

                <?php if(isset($comments->data) AND !empty($comments->data)){ ?>
                    <?php $i = 1; foreach($comments->data as $key => $row){ ?>

                        <?php if($i == 1){ ?>
                            <div class="row">
                        <?php } ?>

                        <div class="col-xs-12 col-md-4 pdb-30">
                            <h4 class="style-01" title="<?php echo $row->EventComment->name; ?>"><?php echo $row->EventComment->name; ?></h4>
                            <p><?php echo $row->EventComment->comment; ?></p>
                        </div>

                        <?php if($i == 3 OR !isset($comments->data[($key + 1)])){ $i = 1; ?>
                            </div>
                        <?php }else { $i++;} ?>
                    <?php } ?>
                <?php } ?>

                <div class="row">
                    <div class="col-md-12 pdb-30">
                        <form id="form_comment" method="POST">
                            <div class="form_comment" class="wow fadeInUp" data-wow-duration="1s">
                                <div class="form-group">
                                    <input type="text" name="name" class="form-control" placeholder="Nome" validation="1">
                                </div>
                                <div class="form-group">
                                    <input type="text" name="email_form" class="form-control" placeholder="E-mail" validation="0">
                                </div>
                                <div class="form-group">
                                    <textarea name="comment" rows="5" class="form-control" placeholder="Comentário" validation="1"></textarea>
                                </div>

                                <input type="hidden" name="event_id" value="<?php echo $_GET['id']; ?>">
                                <input type="hidden" name="form_type" value="comment">
                                <button type="button" class="btn btn-site btn_comment pull-right">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        <?php if(isset($result) AND !empty($result)){ ?>
            <?php if($result){ ?>
                $(document).ready(function(){ toastr.success('Comentário enviado com sucesso!'); });
            <?php }else{ ?>
                $(document).ready(function(){ toastr.error('Ocorreu algum erro, tente outra novamente mais tarde.'); });
            <?php } ?>
        <?php } ?>
    </script>

    <div id="fb-root"></div>
    <script>(function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.10&appId=395247850627185";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>

<?php include_once('inc/footer.php'); ?>