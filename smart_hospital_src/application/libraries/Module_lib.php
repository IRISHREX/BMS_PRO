<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Module_lib {

    private $allModules = array();
    private $allPatientModules = array();
    protected $modules;
    var $perm_category;
    public $CI;
    public $patientModules;

    function __construct() {
        $this->CI = & get_instance();
        $this->modules = array();
        $this->patientModules = array();
        self::loadModule(); 
        self::loadPatientModule(); 
    }

    function loadModule() {
        if (!isset($this->CI->module_model)) {
            $this->CI->load->model('Module_model');
        }
        $model = isset($this->CI->module_model) ? $this->CI->module_model : new Module_model();
        $this->allModules = $model->get();

        if (!empty($this->allModules)) {
            foreach ($this->allModules as $mod_key => $mod_value) {

                if ($mod_value->is_active == 1) {
                    $this->modules[$mod_value->short_code] = true;
                } else {

                    $this->modules[$mod_value->short_code] = false;
                }
            }
        }
    }

    function loadPatientModule() {
        if (!isset($this->CI->module_model)) {
            $this->CI->load->model('Module_model');
        }
        $model = isset($this->CI->module_model) ? $this->CI->module_model : new Module_model();
        $this->allPatientModules = $model->getPatientModule();

        if (!empty($this->allPatientModules)) {
            foreach ($this->allPatientModules as $mod_key => $mod_value) {

                if ($mod_value->is_active == 1) {
                    $this->patientModules[$mod_value->short_code] = true;
                } else {

                    $this->patientModules[$mod_value->short_code] = false;
                }
            }
        }
    }

    function hasActive($module = null) {

        if ($this->modules[$module]) {
            return true;
        }

        return false;
    }

    function hasPatientActive($module = null) {
        if ($this->patientModules[$module]) {
            return true;
        }
        return false;
    }

	function hasModule($module_shortcode) {
        $model = (isset($this->CI->module_model) && $this->CI->module_model) ? $this->CI->module_model : new Module_model();
        $count = $model->hasModule($module_shortcode);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    

}
