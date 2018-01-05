<?php
App::uses('AppController', 'Controller');

class EventCommentsController extends AppController
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
        $this->Paginator->settings = array('order' => array('EventComment.created' => 'DESC'));

        $q = '';
        $where = array();
        if (isset($this->request->query['q'])) {
            $q = $this->request->query['q'];
            $where = array(
                'OR' => array(
                    'EventComment.name LIKE' => '%' . $q . '%'
                )
            );
        }

        $this->set(array('q' => $q, 'list' => $this->Paginator->paginate('EventComment', $where)));
    }

    public function remove($id = NULL)
    {
        if (empty($id) || !$this->EventComment->exists($id))
            $this->redirect(array('action' => 'index'));

        $this->EventComment->id = $id;
        if ($this->EventComment->delete($id, FALSE)) {
            $this->Session->setFlash('O comentário foi removido com sucesso.', 'success');
        } else {
            $this->Session->setFlash('Ocorreu algum erro, por favor, tente novamente mais tarde.', 'failure');
        }

        $this->redirect(array('action' => 'index'));
    }

}