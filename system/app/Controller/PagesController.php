<?php
App::uses('AppController', 'Controller');

class PagesController extends AppController
{
    public function isAuthorized($user)
    {
        return $this->hasPermission(SimplePermission::ManageAll);
    }

    public function index()
    {
    }

    public function configs()
    {
    }

    private function loadCombos()
    {
        $this->set(compact(''));
    }
}