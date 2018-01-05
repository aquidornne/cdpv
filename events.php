<?php
require('init.php');
global $page_current;
$page_current = 'events';

$events = $system->serviceFindEvents(array(
        'q' => ((isset($_GET['q']) AND !empty($_GET['q'])) ? $_GET['q'] : ''),
        'page' => ((isset($_GET['page']) AND !empty($_GET['page'])) ? $_GET['page'] : 1),
        'limit' => ((isset($_GET['limit']) AND !empty($_GET['limit'])) ? $_GET['limit'] : 28)
    )
);
?>

<?php include_once('inc/header.php'); ?>

    <main>
        <section class="theme-type-style-03">
            <div class="banner-pages">
                <div class="bg-container bg-container-style-01">
                    <div class="container pdt-100 pdb-100">
                        <div class="text01 block-vertical-center">
                            <div>
                                <div class="noMarginTop text-style-01 text-center">Eventos</div>
                                <div class="noMarginBottom text-style-02 no-line-height text-center white"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="" class="">
            <div class="container pdt-100 pdb-50">
                <div class="row">
                    <div class="col-md-3 pdb-50">
                        <div class="">

                            <form method="get">
                                <div class="input-group">
                                    <input type="text" name="q" class="input-sm form-control" placeholder="Faça sua busca" value="<?php echo ((isset($_GET['q']) AND !empty($_GET['q'])) ? $_GET['q'] : ''); ?>">
                                    <span class="input-group-btn">
                                        <input type="submit" class="btn btn-sm btn-primary" value="Buscar">
                                    </span>
                                </div>

                                <input type="hidden" name="event_id" value="<?php echo $_GET['id']; ?>">
                                <input type="hidden" name="page" value="<?php echo ((isset($_GET['page']) AND !empty($_GET['page'])) ? $_GET['page'] : 1); ?>">
                                <input type="hidden" name="limit" value="<?php echo ((isset($_GET['limit']) AND !empty($_GET['limit'])) ? $_GET['limit'] : 28); ?>">
                            </form>

                        </div>
                    </div>
                    <div class="col-md-9 pdb-50">
                        <div class="news-home">
                            <?php if(isset($events->data) AND !empty($events->data)){ ?>
                                <?php $i = 1; foreach($events->data as $key => $row){ ?>

                                    <?php if($i == 1){ ?>
                                        <div class="row news-home">
                                    <?php } ?>

                                    <div class="col-xs-12 col-md-6 pdb-50">
                                        <div class="pdb-10">
                                            <a href="<?php echo _PROJECT_; ?>evento/<?php echo $row->Event->id; ?>/<?php echo Tools::string_rewrite($row->Event->title); ?>" class="news-home-title"><b><?php echo $row->Event->title; ?></b></a>
                                        </div>
                                        <p><?php echo $row->Event->resume; ?></p>
                                        <div class="photo-block"
                                             style="background-image: url('<?php echo _PROJECT_; ?>system/files/events/<?php echo $row->Event->cover; ?>');">
                                            <a href="evento?id=<?php echo $row->Event->id; ?>"></a>
                                        </div>
                                    </div>

                                    <?php if($i == 2 OR !isset($events->data[($key + 1)])){ $i = 1; ?>
                                        </div>
                                    <?php }else { $i++;} ?>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <ul class="pagination">
                            <?php for($i = 1; $i <= $events->numbers; ++$i) { ?>
                                <li class="<?php echo ((isset($_GET['page']) AND $_GET['page'] == $i) ? 'active' : ''); ?>"><a href="<?php echo _PROJECT_; ?>eventos?page=<?php echo $i; ?>&category=<?php echo ((isset($_GET['category']) AND !empty($_GET['category'])) ? $_GET['category'] : ''); ?>"><?php echo $i; ?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    </script>

<?php include_once('inc/footer.php'); ?>