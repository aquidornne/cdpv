<?php 
App::uses('AppHelper', 'View/Helper');

class FormXHelper extends AppHelper {
	
	public function __construct(View $view, $settings = array()) {
        parent::__construct($view, $settings);
    }
	
    public function input($field, $label = NULL, $inputOpts = array()) {
		$inputOpts['div'] = false;
		$inputOpts['label'] = false;
        $inputOpts['error'] = false;
		echo $this->_View->element('admin/input-field', array('field' => $field, 'label' => $label ? $label : $field, 'inputOpts' => $inputOpts));
    }
	
    public function checkbox($field, $label = NULL, $labelCheck = NULL, $fieldId = NULL, $inputOpts = array()) {
		$inputOpts['div'] = false;
		$inputOpts['label'] = false;
		echo $this->_View->element('admin/check-field', array('field' => $field, 'fieldId' => $fieldId, 'label' => $label ? $label : $field, 'labelCheck' => $labelCheck, 'inputOpts' => $inputOpts));
    }
	
	public function select($field, $label = NULL, $options = array(), $inputOpts = array()) {
		$inputOpts['div'] = false;
		$inputOpts['label'] = false;
		$inputOpts['options'] = $options;
		echo $this->_View->element('admin/select-field', array('field' => $field, 'label' => $label ? $label : $field, 'inputOpts' => $inputOpts));
    }
	
	public function date($field, $label = NULL, $inputOpts = array(), $minYear = -5, $maxYear = 10) {
		$inputOpts['div'] = false;
		$inputOpts['label'] = false;
		$dayInputOpts = array_merge($inputOpts);
		if(!isset($dayInputOpts['class']))
			$dayInputOpts['class'] = '';
		$dayInputOpts['class'] .= ' small';
		
		echo $this->_View->element('admin/date-field', array(
			'field' => $field, 
			'label' => $label ? $label : $field,
			'dayInputOpts' => $dayInputOpts,
			'monthInputOpts' => $dayInputOpts,
			'yearInputOpts' => $dayInputOpts,
			'minYear' => date('Y') + $minYear,
			'maxYear' => date('Y') + $maxYear
		));
    }
	
	public function hasError($field){
		return is_array($this->_View->validationErrors) && 
			isset($this->_View->validationErrors[$this->_View->Form->defaultModel]) && 
			isset($this->_View->validationErrors[$this->_View->Form->defaultModel][$field]) && 
			count($this->_View->validationErrors[$this->_View->Form->defaultModel][$field]) > 0;
	}
	
	public function validationError($field){
		if(!$this->hasError($field))
			return;
		
		foreach($this->_View->validationErrors[$this->_View->Form->defaultModel][$field] as $error){
			echo '<span class="alert-msg"><i class="icon-remove-sign"></i> '.$error.'</span>';
		}
	}
}