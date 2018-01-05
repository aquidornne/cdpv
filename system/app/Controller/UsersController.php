<?php
App::uses('AppController', 'Controller');

class UsersController extends AppController
{
    public function isAuthorized($user)
    {
        return $this->hasPermission(SimplePermission::ManageAll);
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('login');
    }

    public function index()
    {
        $this->Paginator->settings = array(
            'contain' => array('Role')
        );

        $q = '';
        $where = array();
        if (isset($this->request->query['q'])) {
            $q = $this->request->query['q'];
            $where = array('OR' => array(
                'User.name LIKE' => '%' . $q . '%',
                'User.email LIKE' => '%' . $q . '%',
                'Role.name LIKE' => '%' . $q . '%',
            ));
        }
        $this->set('q', $q);
        $this->set('list', $this->Paginator->paginate('User', $where));
    }

    public function add()
    {
        if ($this->request->is('post') || $this->request->is('put')) {

            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('O usuário foi criado com sucesso.', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        }

        $this->loadCombos();
    }

    public function edit($id = null)
    {
        if (empty($id) || !$this->User->exists($id))
            $this->redirect(array('action' => 'index'));

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->User->id = $id;
            if ($this->User->save($this->request->data)) {

                $this->Session->write('Auth.User.name', $this->request->data['User']['name']);
                $this->Session->write('Auth.User.email', $this->request->data['User']['email']);

                $this->Session->setFlash('O usuário foi alterado com sucesso.', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        } else {
            $this->request->data = $this->User->findById($id);
        }

        $this->loadCombos();
    }

    public function remove($id = null)
    {
        if (empty($id) || !$this->User->exists($id))
            $this->redirect(array('action' => 'index'));

        $this->User->id = $id;
        if ($this->User->delete($id, false)) {
            $this->Session->setFlash('O usuário foi removido com sucesso.', 'success');
        } else {
            $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
        }

        $this->redirect(array('action' => 'index'));
    }

    public function login()
    {
        $this->layout = false;

        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash('Dados incorretos. Tente novamente.', 'auth');
            }
        }
    }

    public function logout()
    {
        $this->redirect($this->Auth->logout());
    }

    private function loadCombos()
    {
        $this->loadModel('Role');
        $roles = $this->Role->find('all', array('order' => array('Role.name' => 'asc')));
        $roles = Set::combine($roles, '{n}.Role.id', '{n}.Role.name');
        $this->set('roles', $roles);
    }
}
