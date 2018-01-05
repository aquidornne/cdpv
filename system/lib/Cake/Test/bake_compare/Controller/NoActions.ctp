<?php
App::uses('AppController', 'Controller');
/**
 * Events Controller
 *
 * @property Event $Event
 * @property AclComponent $Acl
 * @property AuthComponent $Auth
 * @property PaginatorComponent $Paginator
 */
class EventsController extends AppController {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Js', 'Time');

/**
 * Components
 *
 * @var array
 */
	public $components = array('Acl', 'Auth', 'Paginator');

}
