<?php
App::uses('AppModel', 'Model');

class EventComment extends AppModel
{

    public $validate = array(
//        'name' => array(
//            'notempty',
//        )
    );

    public $belongTo = array(
        'Event' => array(
            'className' => 'Event',
            'foreignKey' => 'event_id'
        ),
    );
}