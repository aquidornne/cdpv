<div class="row border-bottom white-bg dashboard-header">
    <h2 class="noMarginTop">Fotos</h2>
    <?php //echo $this->element('breadcrumb'); ?>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-xs-12 col-md-12">
                            <a href="<?php echo $this->Html->url(array('action' => 'add')); ?>" class="btn btn-success pull-right">Cadastrar</a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <?php echo $this->Form->create('Category', array('type' => 'GET')); ?>
                                <div class="input-group">
                                    <?php echo $this->Form->input('q', array('div' => FALSE, 'label' => FALSE, 'class' => 'input-sm form-control', 'placeholder' => 'Faça sua busca', 'required' => TRUE, 'value' => ((isset($q) AND !empty($q)) ? $q : ''))); ?>
                                    <span class="input-group-btn">
                                            <?php echo $this->Form->submit('Buscar', array('div' => FALSE, 'class' => 'btn btn-sm btn-primary'));?>
                                    </span>
                                </div>
                            <?php echo $this->Form->end(); ?>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th width="80">Nome</th>
                                <th width="20">Ações</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($list as $key => $row){ ?>
                                <tr>
                                    <td><?php echo $row['EventCategory']['name']; ?></td>
                                    <td>
                                        <a href="<?php echo $this->Html->url(array('action' => 'edit', $row['EventCategory']['id'])); ?>" class="btn btn-info"><i class="fa big fa-pencil pull-right"></i></a>
                                        <a href="<?php echo $this->Html->url(array('action' => 'remove', $row['EventCategory']['id'])); ?>" class="btn btn-danger confirm"><i class="fa big fa-trash pull-right"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-md-12">
            <?php echo $this->element('pagination'); ?>
        </div>
    </div>
</div>

<script>
</script>