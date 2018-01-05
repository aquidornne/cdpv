<?php
$this->Breadcrumb->here = Router::url(array('plugin' => null, 'controller' => $this->params['controller'], 'action' => $this->params['action']));
$result = $this->Breadcrumb
    ->addCrumb($this->params['controller'], array('plugin' => null, 'controller' => false, 'action' => false))
    ->addCrumb((isset($label) ? $label : ''), array('plugin' => null, 'controller' => $this->params['controller'], 'action' => $this->params['action']))
    ->getCrumbs();

//debug($this->params); die;
?>
<?php echo $result; ?>

<ol class="breadcrumb hide">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Dashboard</li>
</ol>