<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MX_Controller {
    
    public function __construct () {
		$config = ['config_login'];
	    $models = ['login_model', 'coaching/coaching_model'];
	    $this->common_model->autoload_resources ($config, $models);
	    $this->load->helper ('file');
	}
	
 	public function index () {    
    	$data['page_title'] = '';
		$data['script'] = $this->load->view ('scripts/validate', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		//$this->load->view('find_coaching', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	// For backward compatibility
	public function logout () {
		$this->session->sess_destroy ();
		$redirect = site_url ('login/user/index');
		redirect ($redirect);
	}

}