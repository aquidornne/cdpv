<?php
App::uses('AppController', 'Controller');
App::uses('ToolHelper', 'View/Helper');

class EventsController extends AppController
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
        $this->Paginator->settings = array('order' => array('Event.name' => 'DESC'));

        $q = '';
        $where = array();
        if (isset($this->request->query['q'])) {
            $q = $this->request->query['q'];
            $where = array(
                'OR' => array(
                    'Event.title LIKE' => '%' . $q . '%'
                )
            );
        }

        $this->set(array('q' => $q, 'list' => $this->Paginator->paginate('Event', $where)));
        $this->loadCombos();
    }

    public function add()
    {
        $toolhelper = new ToolHelper(new View());

        if ($this->request->is('post') || $this->request->is('put')) {

            if (isset($this->request->data['Event']['cover']['name']) AND !empty($this->request->data['Event']['cover']['name'])) {
                $file = $toolhelper->uploadFile($this->request->data['Event']['cover'], 'articles');

                if ($file['success']) {
                    $this->request->data['Event']['cover'] = $file['file'];
                }
            }else {
                $this->request->data['Event']['cover'] = '';
            }

            $this->Event->create();
            if ($this->Event->save($this->request->data)) {
                $this->Session->setFlash('O evento foi cadastrado com sucesso!', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        }

        $this->loadCombos();
    }

    public function edit($id = NULL)
    {
        $toolhelper = new ToolHelper(new View());

        if ($this->request->is('post') || $this->request->is('put')) {

            if (isset($this->request->data['Event']['cover']['name']) AND !empty($this->request->data['Event']['cover']['name'])) {
                $file = $toolhelper->uploadFile($this->request->data['Event']['cover'], 'articles');

                if ($file['success']) {
                    $this->request->data['Event']['cover'] = $file['file'];
                }
            }else {
                $this->request->data['Event']['cover'] = $this->request->data['Event']['cover_old'];
            }

            $this->Event->id = $id;
            if ($this->Event->save($this->request->data)) {
                $this->Session->setFlash('A evento foi atualizado com sucesso!', 'success');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
            }
        } else {
            $this->request->data = $this->Event->findById($id);
        }

        $this->loadCombos();
    }

    public function view($id = NULL)
    {
        if (!$this->Event->exists($id)) {
            $this->Session->setFlash('O evento não pode ser encontrada.', 'warning');
            $this->redirect(array('action' => 'index'));
        }

        $this->request->data = $this->Event->find('first', array(
            'conditions' => 'Event.id = ' . $id
        ));

        $this->loadCombos();
    }

    public function remove($id = NULL, $cover = NULL)
    {
        if (empty($id) || !$this->Event->exists($id))
            $this->redirect(array('action' => 'index'));

        $this->Event->id = $id;
        if ($this->Event->delete($id, FALSE)) {
            unlink(unlink(WWW_ROOT . 'files' . '/' . 'articles' . '/' . $cover));

            $this->Session->setFlash('O evento foi removido com sucesso.', 'success');
            $this->redirect(array('action' => 'index'));
        } else {
            $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
        }

        $this->redirect(array('action' => 'index'));
    }

    private function loadCombos()
    {
        $this->loadModel('EventCategory');
        $articlecategories = $this->EventCategory->find('all', array('order' => 'EventCategory.name ASC'));
        $articlecategories = Set::combine($articlecategories, '{n}.EventCategory.id', '{n}.EventCategory.name');

        $this->set(compact('articlecategories'));
    }
}