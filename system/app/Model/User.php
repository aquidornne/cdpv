<?php
App::uses('AppModel', 'Model');

class User extends AppModel {

    public $validate = array(
        'name' => array(
            'notempty',
        ),
        'email' => array(
            'email',
            'unique' => array(
                'rule' => array('isUnique'),
                'message' => 'E-mail já cadastrado',
                'required' => 'create',
            ),
        ),
        'password' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Você deve definir uma senha',
                'on' => 'create',
            ),
        ),
        /*'confirm_password' => array(
            'required' => array(
                'rule' => array('equalToField', 'password'),
                'message' => 'A confirmação de senha deve ser igual a senha'
            )
        ),*/
    );

    public $belongsTo = array(
        'Role' => array(
            'className' => 'Role',
            'foreignKey' => 'role_id'
        )
    );

    public function beforeSave($options = array())
    {
        if (empty($this->data['User']['password']))
            unset($this->data['User']['password']);

        if (isset($this->data['User']['password'])) {
            $this->data['User']['password'] = AuthComponent::password($this->data['User']['password']);
        }

        return true;
    }

    public function whatYourName($user_id = null) {
        $user = $this->find('first', array('conditions' => array('id' => $user_id)));

        if(empty($user))
            return false;

        if(!empty($user))
            return $user['User']['name'];
    }
}