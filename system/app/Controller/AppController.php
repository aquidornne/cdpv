<?php
App::uses('Controller', 'Controller');
App::uses('ModelBehavior', 'Model');

class AppController extends Controller
{
    public $keyServices = "7d581fbf82cd0e4ff4df0bdd8dd5f35a";

    public $sitePath = "../../../";

    public $components = array(
        'Session',
        'Paginator',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'Pages', 'action' => 'index'),
            'logoutRedirect' => array('controller' => 'Users', 'action' => 'login'),
            'authenticate' => array(
                'Form' => array(
                    'fields' => array('username' => 'email')
                )
            ),
            'authError' => "Você não está autorizado a fazer isso!",
            'flash' => array('element' => 'auth', 'key' => 'auth', 'params' => array()),
            'authorize' => array('Controller')
        ),
        'RequestHandler'
    );

    public $helpers = array('Form', 'FormX', 'Html', 'TextX', 'Money', 'Date');

    public function beforeFilter()
    {
        parent::beforeFilter();

        //if($this->Auth->loggedIn()) { }

        Configure::write('Config.language', 'ptb');

        $this->Auth->deny('*');
    }

    protected function hasPermission($permission)
    {
        $this->loadModel('Role');
        return $this->Role->HasPermission(AuthComponent::user('role_id'), $permission);
    }
}

abstract class SimplePermission
{
    const ManageAll = 4;
}