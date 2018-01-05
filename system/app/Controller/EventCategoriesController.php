<?php
App::uses('AppController', 'Controller');

class EventCategoriesController extends AppController
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
        $this->Paginator->settings = array('order' => array('EventCategory.title' => 'DESC'));

        $q = '';
        $where = array();
        if (isset($this->request->query['q'])) {
            $q = $this->request->query['q'];
            $where = array(
                'OR' => array(
                    'EventCategory.title LIKE' => '%' . $q . '%'
                )
            );
        }

        $this->set(array('q' => $q, 'list' => $this->Paginator->paginate('EventCategory', $where)));
        $this->loadCombos();
    }

    public function add()
    {

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->EventCategory->create();
            if ($this->EventCategory->saveAll($this->request->data, array('DEEP' => TRUE))) {
                $this->Session->setFlash('A categoria foi cadastrada com sucesso!', 'success');
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

            $this->EventCategory->id = $id;
            if ($this->EventCategory->saveAll($this->request->data, array('DEEP' => TRUE))) {
                $this->Session->setFlash('A categoria foi atualizada com sucesso!', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        } else {
            $this->request->data = $this->EventCategory->findById($id);
        }

        $this->loadCombos();
    }

    public function view($id = NULL)
    {
        if (!$this->EventCategory->exists($id)) {
            $this->Session->setFlash('A categoria não pode ser encontrada.', 'warning');
            $this->redirect(array('action' => 'index'));
        }

        $this->request->data = $this->EventCategory->find('first', array(
            'conditions' => 'EventCategory.id = ' . $id
        ));

        $this->loadCombos();
    }

    public function remove($id = NULL)
    {
        if (empty($id) || !$this->EventCategory->exists($id))
            $this->redirect(array('action' => 'index'));

        $this->EventCategory->id = $id;
        if ($this->EventCategory->delete($id, FALSE)) {
            $this->Session->setFlash('A categoria foi removida com sucesso.', 'success');
        } else {
            $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
        }

        $this->redirect(array('action' => 'index'));
    }

    private function loadCombos()
    {
    }
}