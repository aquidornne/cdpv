<?php
App::uses('AppModel', 'Model');

class EventCategory extends AppModel
{

    public $validate = array(
//        'name' => array(
//            'notempty',
//        )
    );

    public $hasMany = array(
        'Event' => array(
            'className' => 'Event',
            'foreignKey' => 'event_category_id'
        ),
    );
}