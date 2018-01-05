<div class="row border-bottom white-bg dashboard-header">
    <h2 class="noMarginTop">Editar Evento</h2>
    <?php //echo $this->element('breadcrumb'); ?>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <?php echo $this->Form->create('Event', array('id' => 'form_edit', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal m-t-md')); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5></h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-down"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content" style="">
                    <div class="form-group noMarginBottom">
                        <label class="col-sm-2 control-label"></label>

                        <div class="col-sm-10">

                            <div class="row">
                                <div class="col-xs-12 col-md-12 m-b">
                                    <label class="control-label">Conteúdo</label>
                                    <?php echo $this->Form->textarea('content', array('div' => FALSE, 'label' => FALSE, 'class' => 'form-control jqte', 'placeholder' => 'Conteúdo')); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-12 m-b">
                                    <label class="control-label">Resumo</label>
                                    <?php echo $this->Form->textarea('resume', array('div' => FALSE, 'label' => FALSE, 'class' => 'form-control jqte', 'placeholder' => 'Resumo')); ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-md-6 m-b">
                                    <label class="control-label">Título</label>
                                    <?php echo $this->Form->input('title', array('div' => FALSE, 'label' => FALSE, 'class' => 'form-control', 'placeholder' => 'Título')); ?>
                                </div>
                                <div class="col-xs-12 col-md-6 m-b">
                                    <label class="control-label">Categoria</label>
                                    <?php echo $this->Form->input('event_category_id', array('div' => FALSE, 'label' => FALSE, 'options' => $articlecategories, 'empty' => TRUE, 'class' => 'form-control chosen-select', 'data-placeholder' => 'Categoria', 'placeholder' => 'Categoria')); ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-md-12 m-b">
                                    <div class="">
                                        <label class="control-label">Capa</label>
                                        <input id="cover" type="file" name="data[Event][cover]">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 col-md-12">
                                    <?php if(!empty($this->data['Event']['cover'])){ ?>
                                        <a href="<?php echo $this->webroot . 'files/events/' . $this->data['Event']['cover']; ?>" class="fancybox">
                                            <img src="<?php echo $this->webroot . 'files/events/' . $this->data['Event']['cover']; ?>">
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <div class="col-sm-4 pull-right">
                    <?php echo $this->Form->hidden('Event.id', array('value' => $this->data['Event']['id'])); ?>
                    <?php echo $this->Form->hidden('Event.cover_old', array('value' => $this->data['Event']['cover'])); ?>

                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" type="submit">Cancelar</a>
                    <?php echo $this->Form->submit('Salvar', array('div' => FALSE, 'id' => 'btn_submit', 'class' => 'btn btn-primary')); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>

<script>

    $('#btn_submit').click(function (e){
        e.preventDefault();

        var error = [];

        if (!$('#EventContent').val()) {
            application.inlineFieldAlert('add', 'input-prepend', $('#EventContent'), 'Informe o conteúdo.', 'warning', false);
            error.push(1);
        } else {
            application.inlineFieldAlert('clean', 'input-prepend', $('#EventContent'));
        }

        if (!$('#EventTitle').val()) {
            application.inlineFieldAlert('add', 'input', $('#EventTitle'), 'Inform um título.', 'warning', false);
            error.push(2);
        } else {
            application.inlineFieldAlert('clean', 'input', $('#EventTitle'));
        }

        if (!$('#EventEventCategoryId').val()) {
            application.inlineFieldAlert('add', 'input', $('#EventEventCategoryId'), 'Inform uma categoria.', 'warning', false);
            error.push(3);
        } else {
            application.inlineFieldAlert('clean', 'input', $('#EventEventCategoryId'));
        }

        console.log(error);

        if (error.length > 0) {
            toastr.warning('Dados inválidos no fomulário.');
            return false;
        } else {
            $('#form_edit').submit();
        }

    });

</script>