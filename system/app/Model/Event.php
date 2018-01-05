<?php
App::uses('AppModel', 'Model');

class Event extends AppModel
{

    public $validate = array(
//        'name' => array(
//            'notempty',
//        )
    );

    public $belongTo = array(
        'EventCategory' => array(
            'className' => 'EventCategory',
            'foreignKey' => 'event_category_id'
        ),
    );

    public $hasMany = array(
        'EventComment' => array(
            'className' => 'EventComment',
            'foreignKey' => 'event_id'
        ),
    );
}