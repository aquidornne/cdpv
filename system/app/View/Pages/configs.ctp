<?php //echo debug($data_contacts);die; ?>

<div class="row border-bottom white-bg dashboard-header">
    <h2 class="noMarginTop">Opções</h2>
    <?php //echo $this->element('breadcrumb'); ?>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-down"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content" style="">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <a href="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'index')); ?>" class="btn btn-primary btn-lg"><i class="fa fa-user fa-1x"></i> Usuários</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
</script>