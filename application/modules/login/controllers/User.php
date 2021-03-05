<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MX_Controller {
    
    public function __construct () {
		$config = ['config_login'];
	    $models = ['coaching/coaching_model', 'login_model'];	    
	    $this->common_model->autoload_resources ($config, $models);
		$this->load->helper ('file');
		
		$this->load->helper('captcha');
	}
	
 	public function index () {

    	// Default settings
		$logo_path = $this->config->item ('system_logo');
		$logo = base_url ($logo_path);
		$page_title = 'Sign-In';
		$found = false;

		if (isset ($_GET['sub']) && ! empty ($_GET['sub']) && $_GET['sub'] != 'undefined') {
    		$access_code = $_GET['sub'];
		} else {
			$access_code = ACCESS_CODE;
		}

		$coaching = $this->coaching_model->get_coaching_by_slug ($access_code);
		if ($coaching) {
			$coaching_dir = 'contents/coachings/' . $coaching['id'] . '/';
			$coaching_logo = $this->config->item ('coaching_logo');
			$logo_path =  $coaching_dir . $coaching_logo;
			$logo = base_url ($logo_path);
			$page_title = $coaching['coaching_name'];
			$website_link = $coaching['website'];
			$found = true;
		}

		$data['page_title'] = $page_title;
		$data['website_link'] = $website_link;
		$data['logo'] = $logo;
		$data['access_code'] = $access_code;
		$data['found'] = $found;
		$data['coaching'] = $coaching;

		$captcha_dir = $this->config->item ('captcha_dir');
		if ( ! is_dir ($captcha_dir) ) {
			mkdir ($captcha_dir, 755, true);
		}
		$vals = array(
				'word'          => '',
				'img_path'      => './'.$captcha_dir,
				'img_url'       => base_url($captcha_dir),
				'img_width'     => '125',
				'img_height'    => 30,
				'word_length'   => 6,
				'font_size'     => 16,
				'img_id'        => 'Imageid',
				'pool'          => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
				

				// White background and border, black text and red grid
				'colors'        => array(
						'background' => array(20, 83, 136),
						'border' => array(255, 255, 255),
						'text' => array(255,255,255),
						'grid' => array(20, 83, 136)
				)
		);

		$cap = create_captcha($vals);
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$captcha_key = array('time' => $cap['time'], 'ip_address' => $ip_address, 'word' => $cap['word']);
		$this->session->set_userdata('captcha_key', $captcha_key);
		$data['captcha'] = $cap['image'];		
		
		$data['script'] = $this->load->view ('scripts/login', $data, true); 
		$this->load->view (INCLUDE_PATH . 'header', $data);
		$this->load->view ('login', $data);
		$this->load->view (INCLUDE_PATH . 'footer', $data);
	}
	
	/* Refresh captcha */
	
	public function refresh_captcha(){
	    // Captcha configuration
		$captcha_dir = $this->config->item ('captcha_dir');
		if ( ! is_dir ($captcha_dir) ) {
			mkdir ($captcha_dir, 755, true);
		}
		$vals = array(
					'word'          => '',
					'img_path'      => './'.$captcha_dir,
					'img_url'       => base_url($captcha_dir),
					'img_width'     => '125',
					'img_height'    => 30,
					'word_length'   => 6,
					'font_size'     => 16,
					'img_id'        => 'Imageid',
					'pool'          => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
					

					// White background and border, black text and red grid
					'colors'        => array(
							'background' => array(20, 83, 136),
							'border' => array(255, 255, 255),
							'text' => array(255,255,255),
							'grid' => array(20, 83, 136)
					)
			);
		$captcha = create_captcha($vals);
		

		$cap = create_captcha($vals);
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$captcha_key = array('time' => $cap['time'], 'ip_address' => $ip_address, 'word' => $cap['word']);
			$this->session->unset_userdata('captcha_key');
			$this->session->set_userdata('captcha_key', $captcha_key);
			$data['captcha'] = $cap['image'];


		// Display captcha image
		// echo $cap['image'];
		$this->load->view ('refresh_captcha', $data);
	}
	
	/* Register Page */
	public function register () {		

		// Default settings
		$access_code = '';
		$logo_path = $this->config->item ('system_logo');
		$logo = base_url ($logo_path);
		$page_title = SITE_TITLE;
		$found = false;

		if (isset ($_GET['sub']) && ! empty ($_GET['sub']) && $_GET['sub'] != 'undefined') {
    		$access_code = $_GET['sub'];
		} else {
			$access_code = ACCESS_CODE;
		}

		$coaching = $this->coaching_model->get_coaching_by_slug ($access_code);
		if ($coaching) {
			$coaching_dir = 'contents/coachings/' . $coaching['id'] . '/';
			$coaching_logo = $this->config->item ('coaching_logo');
			$logo_path =  $coaching_dir . $coaching_logo;
			$logo = base_url ($logo_path);
			$page_title = $coaching['coaching_name'];
			$website_link = $coaching['website'];
			$found = true;
		}

    	if (isset ($_GET['role']) && ! empty ($_GET['role'])) {
    		$role_id = $_GET['role'];
    	} else {
    		$role_id = USER_ROLE_STUDENT;
    	}

		$data['access_code'] = $access_code;
		$data['page_title'] = $page_title;
		$data['logo'] = $logo;
		$data['role_id'] = $role_id;
		$data['found'] = $found;
		$data['coaching'] = $coaching;
		$data['website_link'] = $website_link;
			

		$captcha_dir = $this->config->item ('captcha_dir');
		if ( ! is_dir ($captcha_dir) ) {
			mkdir ($captcha_dir, 755, true);
		}
		$vals = array(
				'word'          => '',
				'img_path'      => './'.$captcha_dir,
				'img_url'       => base_url($captcha_dir),
				'img_width'     => '125',
				'img_height'    => 30,
				'word_length'   => 6,
				'font_size'     => 16,
				'img_id'        => 'Imageid',
				'pool'          => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
				

				// White background and border, black text and red grid
				'colors'        => array(
						'background' => array(20, 83, 136),
						'border' => array(255, 255, 255),
						'text' => array(255,255,255),
						'grid' => array(20, 83, 136)
				)
		);

		$cap = create_captcha($vals);
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$captcha_key = array('time' => $cap['time'], 'ip_address' => $ip_address, 'word' => $cap['word']);
		$this->session->set_userdata('captcha_key', $captcha_key);
		$data['captcha'] = $cap['image'];
		
		
		$data['script'] = $this->load->view ('scripts/register', $data, true); 
		$this->load->view (INCLUDE_PATH . 'header', $data);
		$this->load->view ( 'register', $data); 
		$this->load->view (INCLUDE_PATH . 'footer', $data);
	}

	
	/* forgot password */
	public function reset_password () {
		// Default settings
		$access_code = '';
		$logo_path = $this->config->item ('system_logo');
		$logo = base_url ($logo_path);
		$page_title = SITE_TITLE;
		$found = false;

		if (isset ($_GET['sub']) && ! empty ($_GET['sub']) && $_GET['sub'] != 'undefined') {
    		$access_code = $_GET['sub'];
		} else {
			$access_code = ACCESS_CODE;
		}
		$coaching = $this->coaching_model->get_coaching_by_slug ($access_code);
		if ($coaching) {
			$coaching_dir = 'contents/coachings/' . $coaching['id'] . '/';
			$coaching_logo = $this->config->item ('coaching_logo');
			$logo_path =  $coaching_dir . $coaching_logo;
			$logo = base_url ($logo_path);
			$page_title = $coaching['coaching_name'];
			$found = true;
		}

		$data['page_title'] = $page_title;
		$data['logo'] 		= $logo;
		$data['access_code']= $access_code;
		$data['found']		= $found;
		// $data['page_title'] = $page_title;
		
		$this->load->view (INCLUDE_PATH . 'header', $data);
		$this->load->view ('reset_password');		
		$this->load->view (INCLUDE_PATH . 'footer', $data);
	}

	/* forgot password */
	public function get_access_code () {
		// Default settings
		$access_code = '';
		$logo_path = $this->config->item ('system_logo');
		$logo = base_url ($logo_path);
		$page_title = SITE_TITLE;

		$data['page_title'] = $page_title;
		$data['logo'] 		= $logo;
		$data['access_code']= $access_code;
		$data['page_title'] = $page_title;
		
		$this->load->view (INCLUDE_PATH . 'header', $data);
		$this->load->view ('get_access_code');		
		$this->load->view (INCLUDE_PATH . 'footer', $data);
	}

	/* create password for new user register */
	public function create_password ($user_id='') {


		if (isset ($_GET['sub']) && ! empty ($_GET['sub']) && $_GET['sub'] != 'undefined') {
    		$access_code = $_GET['sub'];
		} else {
			$access_code = ACCESS_CODE;
		}

		$coaching = $this->coachings_model->get_coaching_by_slug ($access_code);
		if ($coaching) {
			$coaching_dir = 'contents/coachings/' . $coaching['id'] . '/';
			$coaching_logo = $this->config->item ('coaching_logo');
			$logo_path =  $coaching_dir . $coaching_logo;
			$logo = base_url ($logo_path);
			$page_title = 'Create Password ' . $coaching['coaching_name'];
		} else {
			$access_code = '';
			$logo = base_url ($this->config->item('system_logo'));
			$page_title = 'Create Password ' . SITE_TITLE;
		}

    	if ( empty ($access_code)) {
			$this->message->set ('Direct registration not allowed', 'danger', true);
			redirect ('login/user/index');    		
    	}

		$result			=	$this->login_model->get_member_by_md5login ($user_id);
		$coaching_id	=	$result['coaching_id'];
		
		$link_send_time	=	$result['link_send_time'];
		$difference		=	time() - $link_send_time;
		if ($difference > 3600*48) {		// Email link is valid only for 48 hours
			echo 'This link has expired. Please '.anchor ('login/user/forgot_password', 'Try again');
		} else {
			$coaching = $this->coachings_model->get_coaching($coaching_id );
			$data['coaching_id'] 		= $coaching_id;
			$data['coaching'] 			= $coaching;
			$data['member_id'] 			= $result['member_id'];
			$data['result'] 			= $result;
			$data['hide_left_sidebar'] 	= true;
			$data['page_title'] = $page_title;
			$data['slug'] = $slug;
			$data['logo'] = $logo;
			$this->load->view ( INCLUDE_PATH . 'header', $data);
			$this->load->view ( 'password', $data);
			$this->load->view ( INCLUDE_PATH . 'footer', $data);
		}
	}	

	/* forget password */
	public function _forgot_password (){
		if ($this->session->userdata('is_logged_in')) {
			$this->login_model->logout ();
		}
		if (isset ($_GET['sub']) && ! empty ($_GET['sub']) && $_GET['sub'] != 'undefined') {
    		$access_code = $_GET['sub'];
		} else {
			$access_code = ACCESS_CODE;
		}
		$coaching = $this->coaching_model->get_coaching_by_slug ($slug);
		if ($coaching) {
			$coaching_dir = 'contents/coachings/' . $coaching['id'] . '/';
			$coaching_logo = $this->config->item ('coaching_logo');
			$logo_path =  $coaching_dir . $coaching_logo;				
			$logo = base_url ($logo_path);

			$page_title = 'Forgot Password ' . $coaching['coaching_name'];
			$data['coaching_id'] = $coaching['id'];
			$data['coaching'] 			= $coaching;
		} else {
			$slug = '';
			$logo = base_url ($this->config->item('system_logo'));
			$page_title = 'Forgot Password ' . SITE_TITLE;
		}

		$data['page_title'] 		= $page_title;
		$data['slug'] = $slug;
		$data['logo'] = $logo;
		$this->load->view (INCLUDE_PATH . 'header', $data);
		$this->load->view ('forgot_password');		
		$this->load->view (INCLUDE_PATH . 'footer', $data);
	}	

}