<?php

App::uses('AppController', 'Controller');
App::uses('DateHelper', 'View/Helper');
App::uses('CakeEmail', 'Network/Email');
App::uses('Money2Helper', 'View/Helper');

class WebservicesController extends AppController
{

    public function isAuthorized($user)
    {
        return TRUE;
    }

    public function beforeFilter()
    {
        parent::beforeFilter();

        $this->Auth->allow();
    }

    public function index()
    {
        $this->layout = 'ajax';
        $this->autoRender = FALSE;

        return json_encode(array('success' => FALSE, 'msg' => "you're doing it wrong!"));
    }

    public function findAddressOfCep()
    {
        $this->autoRender = FALSE;
        $this->layout = 'ajax';

        try {
            $reg = simplexml_load_file("http://cep.republicavirtual.com.br/web_cep.php?formato=xml&cep=" . $this->request->data['cep']);

            $data = array(
                'success' => (string)$reg->resultado,
                'address' => (string)$reg->tipo_logradouro . ' ' . $reg->logradouro,
                'neighborhood' => (string)$reg->bairro,
                'city' => (string)$reg->cidade,
                'state' => (string)$reg->uf
            );
        } catch (Exception $e) {
            $data = array('success' => FALSE, 'street' => '', 'neighborhood' => '', 'city' => '', 'state' => '');
        }

        header("Content-type: application/json");
        echo json_encode($data);
    }

    public function serviceFindClients()
    {
        $this->layout = false;
        $this->autoRender = false;

        $this->loadModel('Client');

        if ($this->request->data['clientSecretKey'] == $this->keyServices):

            $page = $this->request->data['page'];

            $where = array();

            $this->request->data = $this->Client->find('all', array(
                'limit' => ((isset($this->request->data['limit']) AND $this->request->data['limit'] != NULL) ? $this->request->data['limit'] : 30),
                'page' => $page,
                'conditions' => $where,
                'order' => array('Client.created' => 'DESC')
            ));

            $data_count = $this->Client->find('count', array(
                'conditions' => $where
            ));

            $numbers = ceil(($data_count / ((isset($this->request->data['limit']) AND $this->request->data['limit'] != NULL) ? $this->request->data['limit'] : 30)));

            header("Content-type: application/json");
            echo json_encode(array('success' => true, 'msgError' => 'Sucesso', 'data' => $this->request->data, 'numbers' => $numbers, 'page' => $page));
            exit;

        else:
            header("Content-type: application/json");
            echo json_encode(array('success' => false, 'msgError' => 'Error!'));
            exit;
        endif;
    }

    public function serviceFindEvents()
    {
        $this->layout = false;
        $this->autoRender = false;

        $this->loadModel('Event');

        if ($this->request->data['clientSecretKey'] == $this->keyServices):

            $page = $this->request->data['page'];

            $q = '';
            $where = array();
            if (isset($this->request->data['q'])) {
                $q = $this->request->data['q'];
                $where = array(
                    'OR' => array(
                        'Event.title LIKE' => '%' . $q . '%'
                    )
                );
            }

            $this->request->data = $this->Event->find('all', array(
                'limit' => ((isset($this->request->data['limit']) AND $this->request->data['limit'] != NULL) ? $this->request->data['limit'] : 30),
                'page' => $page,
                'conditions' => $where,
                'order' => array('Event.created' => 'DESC')
            ));

            $data_count = $this->Event->find('count', array(
                'conditions' => $where
            ));

            $numbers = ceil(($data_count / ((isset($this->request->data['limit']) AND $this->request->data['limit'] != NULL) ? $this->request->data['limit'] : 30)));

            header("Content-type: application/json");
            echo json_encode(array('success' => true, 'msgError' => 'Sucesso', 'data' => $this->request->data, 'numbers' => $numbers, 'page' => $page));
            exit;

        else:
            header("Content-type: application/json");
            echo json_encode(array('success' => false, 'msgError' => 'Error!'));
            exit;
        endif;
    }

    public function serviceFindEventById()
    {
        $this->layout = false;
        $this->autoRender = false;

        $this->loadModel('Event');

        if ($this->request->data['clientSecretKey'] == $this->keyServices):

            $this->request->data = $this->Event->findById($this->request->data['event_id']);

            header("Content-type: application/json");
            echo json_encode(array('success' => true, 'msgError' => 'Sucesso', 'data' => $this->request->data));
            exit;

        else:
            header("Content-type: application/json");
            echo json_encode(array('success' => false, 'msgError' => 'Error!'));
            exit;
        endif;
    }

    public function serviceAddComment()
    {
        $this->layout = false;
        $this->autoRender = false;

        $this->loadModel('EventComment');

        if ($this->request->data['clientSecretKey'] == $this->keyServices):

            $this->EventComment->create();
            if ($this->EventComment->saveAll($this->request->data, array('DEEP' => TRUE))) {
                header("Content-type: application/json");
                echo json_encode(array('success' => true, 'msgError' => 'Sucesso'));
                exit;
            } else {
                header("Content-type: application/json");
                echo json_encode(array('success' => false, 'msgError' => 'Sucesso'));
                exit;
            }

        else:
            header("Content-type: application/json");
            echo json_encode(array('success' => false, 'msgError' => 'Error!'));
            exit;
        endif;
    }

    public function serviceFindComments()
    {
        $this->layout = false;
        $this->autoRender = false;

        $this->loadModel('EventComment');

        if ($this->request->data['clientSecretKey'] == $this->keyServices):

            $page = $this->request->data['page'];

            $where = array(
                'AND' => array(
                    'EventComment.event_id' => $this->request->data['event_id']
                )
            );

            $q = '';
            if (isset($this->request->data['q'])) {
                $q = $this->request->data['q'];
                $where['OR'] = array(
                    'EventComment.name LIKE' => '%' . $q . '%'
                );
            }

            $this->request->data = $this->EventComment->find('all', array(
                'limit' => ((isset($this->request->data['limit']) AND $this->request->data['limit'] != NULL) ? $this->request->data['limit'] : 30),
                'page' => $page,
                'conditions' => $where,
                'order' => array('EventComment.created' => 'DESC')
            ));

            $data_count = $this->EventComment->find('count', array(
                'conditions' => $where
            ));

            $numbers = ceil(($data_count / ((isset($this->request->data['limit']) AND $this->request->data['limit'] != NULL) ? $this->request->data['limit'] : 30)));

            header("Content-type: application/json");
            echo json_encode(array('success' => true, 'msgError' => 'Sucesso', 'data' => $this->request->data, 'numbers' => $numbers, 'page' => $page));
            exit;

        else:
            header("Content-type: application/json");
            echo json_encode(array('success' => false, 'msgError' => 'Error!'));
            exit;
        endif;
    }
}