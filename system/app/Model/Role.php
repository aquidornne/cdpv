<?php
App::uses('AppModel', 'Model');
App::uses('RolePermission', 'Model');

class Role extends AppModel {

    public $displayField = 'name';

	public $validate = array(
		'name' => 'notempty',
	);

    public $hasMany = array(
        'RolePermission' => array(
            'className' => 'RolePermission',
            'joinTable' => 'role_permissions',
            'foreignKey' => 'role_id'
        )
    );

    public function HasPermission($role_id, $permission){
        $rp = new RolePermission();
        $data = $rp->find('count', array(
            'conditions' => array('permission_id' => $permission, 'role_id' => $role_id)
        ));

        return $data > 0;
    }

    public function afterSave($created, $options = array()){
        $this->savePermissions();
    }

    public function savePermissions() {
        if($this->id == 0)
            return;

        $rp = new RolePermission();
        $rp->deleteAll(array('RolePermission.role_id' => $this->id), false);

        if(isset($this->data['RolePermission']) && count($this->data['RolePermission']) > 0){
            foreach($this->data['RolePermission']['permission_id'] as $pid => $permission){
                $rp->save(array(
                    'role_id' => $this->id,
                    'permission_id' => $permission
                ));
            }
        }
    }
}
