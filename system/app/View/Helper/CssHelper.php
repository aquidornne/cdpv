<?php
    App::uses('AppHelper', 'View/Helper');

    class CssHelper extends AppHelper {
        private $current_controller = NULL;
        private $current_action = NULL;

        public function __construct(View $view, $settings = array()) {
            parent::__construct($view, $settings);
            $this->current_controller = strtolower($view->request->controller);
            $this->current_action = strtolower($view->request->action);
        }

        public function is_menu_active($controller, $action = NULL) {
            if(strtolower($this->current_controller) == strtolower($controller) &&
                ($action == NULL || $action == '' || strtolower($this->current_action) == strtolower($action)))
                return true;

            return false;
        }

        public function is_multi_menu_active($urls) {
            foreach($urls as $url){
                $parts = explode(' ', $url);
                if(count($parts) == 1)
                    $parts[1] = NULL;

                if($this->is_menu_active($parts[0], $parts[1])){
                    return true;
                }
            }

            return false;
        }

        public function menu_active($controller, $action = NULL, $cls = 'active') {
            if($this->is_menu_active($controller, $action)){
                echo $cls;
            }
        }

        public function multi_menu_active($urls, $cls = 'active') {
            $active = false;
            foreach($urls as $url){
                $parts = explode(' ', $url);
                if(count($parts) == 1)
                    $parts[1] = NULL;

                if($this->is_menu_active($parts[0], $parts[1])){
                    $active = true;
                    break;
                }
            }

            if($active)
                echo $cls;
        }
    }