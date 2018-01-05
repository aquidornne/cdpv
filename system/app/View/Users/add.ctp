<div class="row border-bottom white-bg dashboard-header">
    <h2 class="noMarginTop">Cadastrar Usuário</h2>
    <?php //echo $this->element('breadcrumb'); ?>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <?php echo $this->Form->create('User', array('class' => 'new_user_form', 'role' => 'form', 'type' => 'file', 'enctype' => 'multipart/form-data')); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h3 class="box-title"><?php echo 'Novo Administrador'; ?></h3>
                </div>

                <div class="ibox-content">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo $this->Form->input('User.name', array('div' => false, 'label' => 'Nome:', 'class' => 'form-control', 'placeholder' => 'Nome')); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo 'E-mail'; ?></label>
                                <div class="input-group marginBottomDefault">
                                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                    <?php echo $this->Form->input('User.email', array('div' => false, 'label' => false, 'class' => 'form-control', 'placeholder' => 'E-mail')); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo $this->Form->input('User.password', array('div' => false, 'label' => 'Senha:', 'class' => 'form-control', 'value' => '', 'type' => 'password', 'placeholder' => 'Senha', 'autocomplete' => 'off')); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?php echo $this->Form->input('User.role_id', array('div' => false, 'label' => 'Grupo de Acesso:', 'class' => 'form-control', 'options' => $roles)); ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="checkbox">
                                <label>
                                    <?php echo $this->Form->input('User.active', array('div' => false, 'label' => 'Senha:', 'type' => 'checkbox', 'class' => 'minimal-red', 'value' => 1, 'checked' => true, 'label' => ' Permitir Login?')); ?>
                                </label>
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
                    <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" type="submit">Cancelar</a>
                    <?php echo $this->Form->submit('Cadastrar', array('div' => FALSE, 'id' => 'btn_submit', 'class' => 'btn btn-primary')); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>


<script>
</script>