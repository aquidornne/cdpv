<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element text-center">
                    <span>
                        <a href="<?php echo $this->webroot; ?>" class="img-circle"><img src="<?php echo $this->webroot; ?>img/logo_cpdv_branca.PNG" style="max-width: 180px;"></a>
                    </span>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'edit', $this->Session->read('Auth.User.id'))); ?>">Profile</a></li>
                    </ul>
                </div>
                <div class="logo-element">
                    R+
                </div>
            </li>
            <li class="<?php echo $this->Css->menu_active('Pages', 'index'); ?>">
                <a href="<?php echo $this->Html->url(array('controller' => 'Pages', 'action' => 'index')); ?>"><i class="fa fa-home"></i> <span class="nav-label">Início</span></a>
            </li>
            <li class="<?php echo $this->Css->menu_active('Clients'); ?><?php echo $this->Css->menu_active('Events'); ?><?php echo $this->Css->menu_active('EventCategories'); ?><?php echo $this->Css->menu_active('EventComments'); ?>">
                <a href="index.html"><i class="fa fa-at"></i> <span class="nav-label">Site</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level">
                    <li class="<?php echo $this->Css->menu_active('Clients'); ?>">
                        <a href="<?php echo $this->Html->url(array('controller' => 'Clients', 'action' => 'index')); ?>"><i class="fa fa-file-image-o"></i> <span class="nav-label">Clientes</span></a>
                    </li>
                    <li class="<?php echo $this->Css->menu_active('Events'); ?>">
                        <a href="<?php echo $this->Html->url(array('controller' => 'Events', 'action' => 'index')); ?>"><i class="fa fa fa-file-word-o"></i> <span class="nav-label">Eventos</span></a>
                    </li>
                    <li class="<?php echo $this->Css->menu_active('EventCategories'); ?>">
                        <a href="<?php echo $this->Html->url(array('controller' => 'EventCategories', 'action' => 'index')); ?>"><i class="fa fa-ellipsis-h"></i> <span class="nav-label">Categorias</span></a>
                    </li>
                    <li class="<?php echo $this->Css->menu_active('EventComments'); ?>">
                        <a href="<?php echo $this->Html->url(array('controller' => 'EventComments', 'action' => 'index')); ?>"><i class="fa fa-comments"></i> <span class="nav-label">Comentários</span></a>
                    </li>
                </ul>
            </li>

            <li class="<?php echo $this->Css->menu_active('Pages', 'configs'); ?><?php echo $this->Css->menu_active('Users'); ?>">
                <a href="<?php echo $this->Html->url(array('controller' => 'Pages', 'action' => 'configs')); ?>"><i class="glyphicon glyphicon-wrench"></i> <span class="nav-label">Opções</span></a>
            </li>
        </ul>
    </div>
</nav>