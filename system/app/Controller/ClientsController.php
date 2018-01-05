<?php
App::uses('AppController', 'Controller');
App::uses('DateHelper', 'View/Helper');
App::uses('Money2Helper', 'View/Helper');
App::uses('ToolHelper', 'View/Helper');

class ClientsController extends AppController
{
    public $helpers = array('Date', 'Tool');

    function beforeFilter()
    {
        parent::beforeFilter();
        //            $this->Auth->allow(array(''));
    }

    public function isAuthorized($user)
    {
        return $this->hasPermission(SimplePermission::ManageAll);
    }

    public function index()
    {
        $this->Paginator->settings = array('order' => array('Client.created' => 'DESC'));

        $q = '';
        $where = array();
        if (isset($this->request->query['q'])) {
            $q = $this->request->query['q'];
            $where = array(
                'OR' => array(
                    'Client.comments LIKE' => '%' . $q . '%'
                )
            );
        }

        $this->set(array('q' => $q, 'list' => $this->Paginator->paginate('Client', $where)));
        $this->loadCombos();
    }

    public function add()
    {
        $toolhelper = new ToolHelper(new View());

        if ($this->request->is('post') || $this->request->is('put')) {

            $data  = array();

            $file = $toolhelper->uploadFile($_FILES['files'], 'clients');

            if ($file['success']) {
                $data['Client']['file'] = $file['file'];
                $data['Client']['comments'] = $this->request->data['Client']['comments'];
            }

            $this->Client->create();
            if ($this->Client->saveAll($data, array('DEEP' => TRUE))) {
                $this->Session->setFlash('O cliente foi cadastro com sucesso!', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        }

        $this->loadCombos();
    }

    public function edit($id = NULL)
    {
        if ($this->request->is('post') || $this->request->is('put')) {

            $this->Client->id = $id;
            if ($this->Client->saveAll($this->request->data, array('DEEP' => TRUE))) {
                $this->Session->setFlash('O cliente foi atualizado com sucesso!', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        } else {
            $this->request->data = $this->Client->findById($id);
        }

        $this->loadCombos();
    }

    public function view($id = NULL)
    {
        if (!$this->Client->exists($id)) {
            $this->Session->setFlash('A imagem não pode ser encontrada.', 'warning');
            $this->redirect(array('action' => 'index'));
        }

        $this->request->data = $this->Client->find('first', array(
            'conditions' => 'Client.id = ' . $id
        ));

        $this->loadCombos();
    }

    public function remove($id = NULL, $photo = NULL)
    {
        if (empty($id) || !$this->Client->exists($id))
            $this->redirect(array('action' => 'index'));

        $this->Client->id = $id;
        if ($this->Client->delete($id, FALSE)) {

            unlink(unlink(WWW_ROOT . 'files' . '/' . 'Clients' . '/' . $photo));

            $this->Session->setFlash('A imagem foi removida com sucesso.', 'success');
            $this->redirect(array('action' => 'index'));
        } else {
            $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
        }

        $this->redirect(array('action' => 'index'));
    }

    private function loadCombos()
    {
        /*
        $this->loadModel('Category');
        $categories = $this->Category->find('all', array('order' => 'Category.name ASC'));
        $categories = Set::combine($categories, '{n}.Category.id', '{n}.Category.name');

        $this->set(compact('categories'));
        */
    }
}