<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Users extends MX_Controller {
	
    var $toolbar_buttons = []; 
    var $is_admin;
	public function __construct () {
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_coaching', 'config_course'];
	    $models = ['users_model', 'coaching_model', 'subscription_model', 'enrolment_model'];
	    $this->common_model->autoload_resources ($config, $models);
	    
        $cid = $this->uri->segment (4);
        $this->toolbar_buttons['<i class="fa fa-list"></i> All Users']= 'coaching/users/index/'.$cid;
        $this->toolbar_buttons['<i class="fa fa-plus-circle"></i> New User']= 'coaching/users/create/'.$cid;
        $this->toolbar_buttons['<i class="fa fa-upload"></i> Import Users']= 'coaching/users/import/'.$cid;
        // $this->toolbar_buttons['<i class="fa fa-users"></i> Batches']= 'coaching/users/batches/'.$cid;
        // $this->toolbar_buttons['<i class="fa fa-plus"></i> New Batch']= 'coaching/users/create_batch/'.$cid;
        $this->is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
        // Security step to prevent unauthorized access through url
        if ($this->session->userdata ('is_admin') == TRUE) {
        } else {
            if ($cid == true && $this->session->userdata ('coaching_id') <> $cid) {
                $this->message->set ('Direct url access not allowed', 'danger', true);
                redirect ('coaching/home/dashboard');
            }
        }
	}
	
	
	/* LIST USERS
		Function to list all or selected users
	*/
	public function index ($coaching_id=0, $role_id=0, $status='-1', $batch_id=0, $sort=SORT_ALPHA_ASC) {
		
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		if ($coaching_id == 0) {
			$coaching_id = $this->session->userdata ('coaching_id');
		}
		/* --==== GET USERS  ====-- */
		$role_lvl 		 	= $this->session->userdata ('role_lvl');	
		$data['results'] 	= $this->users_model->get_users ($coaching_id, $role_id, $status, $batch_id);
		$data['roles']	 	=  $this->users_model->user_roles_by_level ($role_lvl);
		$data['batches']	 	=  $this->users_model->get_batches ($coaching_id);
		$data['user_status']	= $this->common_model->get_sys_parameters (SYS_USER_STATUS);
		$data['coaching_id']	= $coaching_id;
		$data['role_id'] 	= $role_id;
		switch ($role_id) {
			case USER_ROLE_COACHING_ADMIN:
			$role_label = "Coaching Admin";
				break;
			case USER_ROLE_TEACHER:
			$role_label = "Teacher";
				break;
			case USER_ROLE_STUDENT:
			$role_label = "Student";
				break;
			default:
			$role_label = "All Roles";
				break;
		}
		$data['role_label'] 	= $role_label;
		$data['status'] 	= $status;
		switch ($status) {
			case USER_STATUS_DISABLED:
			$status_label = "Disabled";
				break;
			case USER_STATUS_ENABLED:
			$status_label = "Enabled";
				break;
			case USER_STATUS_UNCONFIRMED:
			$status_label = "Pending";
				break;
			default:
			$status_label = "All Status";
				break;
		}
		$data['status_label'] 	= $status_label;
		if($sort==SORT_ALPHA_ASC){
			$data['sort_label'] 	= 'Name: A to Z';
		}
		$data['batch_id'] 	= $batch_id;
		$data['sort'] 		= $sort;
		$data['data'] 		= $data;
		$data['page_title'] = 'Users';

		if (! empty ($data['results'])) {
			$data['num_results'] = count ($data['results']);
		} else {
			$data['num_results'] = 0;
		}
		
		$data['page_title'] = 'Users';
		$data['sub_title']  = 'All Users';
		$data['bc'] = array ('Dashboard'=>'coaching/home/dashboard/'.$coaching_id);

		$data['script'] = $this->load->view ('users/scripts/index', $data, true);
 		$data['filter_block']  = $this->load->view ('users/inc/index_filters', $data, true);

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/index', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);		
	}
	
	public function edit ($coaching_id=0, $role_id=0, $member_id=0) {
		$this->create($coaching_id, $role_id, $member_id);
	}

	/* CREATE USER
		Function to create new user.
	*/
	public function create ($coaching_id=0, $role_id=0, $member_id=0) {
		if($member_id==$this->session->userdata ('member_id')){
			redirect ('coaching/users/my_account/'.$coaching_id.'/'.$member_id);
		}
		$data['coaching_id'] 	= $coaching_id;
		$data['member_id'] 	= $member_id;

		// Reference Id
		$subscription = $this->subscription_model->get_coaching_subscription ($coaching_id);
		$data['profile_image'] 	= $this->users_model->view_profile_image ($member_id, $coaching_id);
		$data['result'] 	= $user = $this->users_model->get_user ($member_id);
		$user['role']	 	= $this->users_model->user_role_name ($user['role_id']);
		$data['num_users']  = $this->coaching_model->num_users ($coaching_id);
		$data['max_users'] 	= $subscription['max_users'];


		$role_lvl 		 	= $this->session->userdata ('role_lvl');
		$admin 				= FALSE;
		$data['roles']	 	= $this->users_model->get_user_roles ($admin, $role_lvl);
		$data['role_id'] 	= $role_id;
		
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['bc'] = array ('Users'=>'coaching/users/index/'.$coaching_id);
		if ($member_id > 0) {
			$data['page_title'] = 'Edit Account';
			$data['sub_title']  = $data['result']['first_name'];
		} else {
			$data['page_title'] = 'Create Account';
		}

		$data['data']	=	$data;
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view ('users/create', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	
	// VIEW USER ACCOUNT
	public function view ($coaching_id=0, $role_id=0, $member_id=0) {
		$data['page_title'] = "View Profile";
		$data['member_id'] 	= $member_id;
		$data['role_id'] 	= $role_id;
		$data['coaching_id'] 	= $coaching_id;
		$data['profile_image'] 	= $this->users_model->view_profile_image ($member_id);
		$user 				= $this->users_model->get_user ($member_id);
		$user_profile 		= $this->users_model->member_profile ($member_id);
		$user_batches 		= $this->users_model->member_batches ($member_id);
		
		if ( is_array ($user) ) {
			$data['result'] 	= array_merge ($user, $user_profile, $user_batches);
		} else {
			$data['result'] = false;
		}
		$data['data'] = $data;		
		$this->load->view ( INCLUDE_PATH . 'header', $data);
		$this->load->view ( 'users/view', $data);
		$this->load->view ( INCLUDE_PATH . 'footer', $data);
	}

	/* CHANGE USER PASSWORD
		Function to change password of selected user
	*/
	public function change_password ($coaching_id=0, $member_id=0) {	
		$data['result'] = $this->users_model->get_user ($member_id);
		$data['profile_image'] = $this->users_model->view_profile_image ($member_id);
		$data['page_title'] = 'Change Password'; 
		$data['sub_title']  = $data['result']['first_name']; 
		$data['member_id']  = $member_id;
		$data['role_id']  = $data['result']['role_id'];
		$data['coaching_id']   = $coaching_id;
		$data["bc"] = array ( 'Users'=>'coaching/users/index/'.$coaching_id );
		// $data['toolbar_buttons'] = $this->toolbar_buttons;

		$data['data'] = $data;
		
		$data['script'] = $this->load->view ('users/scripts/change_password', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/change_password', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	/*----------- MY ACCOUNT FUNCTIONS -------------*/
	public function my_account ($coaching_id=0, $member_id=0) {
		
		$data['page_title'] = 'My Account';
		if ($member_id == 0) {
			$member_id = $this->session->userdata ('member_id');
		}
		
		$data['member_id'] 		= $member_id;
		$data['profile_image'] 	= $this->users_model->view_profile_image ($member_id);
		$user 					= $this->users_model->get_user ($member_id);
		$user_profile 			= $this->users_model->member_profile ($member_id);
		if ( is_array ($user) ) {
			$data['result'] 	= array_merge ($user, $user_profile);			
		} else {
			$data['result'] 	= false;
		}		
		$data['coaching_id'] 	= $coaching_id;
		$data['role_id'] 		= $user['role_id'];
		$data['roles']	 		= $this->users_model->user_role_name ($user['role_id']);
		$data['rand_str'] 		= time ();
		
		/* Breadcrumbs */
		if($this->is_admin){
			$data['bc'] = array ('Dashboard'=>'coaching/home/dashboard/'.$coaching_id);
		}else{
			$data['bc'] = array ('Dashboard'=>'coaching/home/teacher/'.$coaching_id);
		}
		$data['data'] = $data;
		// $data['script'] = $this->load->view ('users/scripts/my_account', $data, true);

		$this->load->view (INCLUDE_PATH . 'header', $data);
		$this->load->view ('users/my_account', $data);
		$this->load->view (INCLUDE_PATH . 'footer', $data);
	}
	
	/*----------- MY PASSWORD ----------------*/
	public function my_password ($coaching_id=0, $member_id=0) {
		$data['result'] = $this->users_model->get_user ($member_id);
		$data['profile_image'] = $this->users_model->view_profile_image ($member_id);
		$data['page_title'] = 'Change Password'; 
		$data['member_id']  = $member_id;       
		$data['coaching_id']   = $coaching_id;
		$data["bc"] = array ( 'Users'=>'coaching/users/my_account/'.$coaching_id.'/'.$member_id );
		$data['data'] = $data;
		
		$data['script'] = $this->load->view ('users/scripts/change_password', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/my_password', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	/* MEMBER LOG FUNCTIONS */
	public function member_log ($coaching_id=0, $role=0, $member_id=0, $log_id=0) {		
		$data["page_title"] = "Member Log";
		$data["coaching_id"] 	= $coaching_id;
		$data["role_id"] 		= $role;
		$data["log_id"] 	= $log_id;
		$data["member_id"] 	= $member_id;		
		$data['profile_image'] 		= $this->users_model->view_profile_image ($member_id);
		$data['bc'] = array ('Users'=>'coaching/users/index/'.$coaching_id);
		
		$data['results'] = $this->users_model->all_log ($member_id);
		$user 				= $this->users_model->get_user ($member_id);
		$user_profile 		= $this->users_model->member_profile ($member_id);
		$user_batches 		= $this->users_model->member_batches ($member_id);
		if ( is_array ($user) ) {
			$data['result'] 	= array_merge ($user, $user_profile, $user_batches);
		} else {
			$data['result'] = false;
		}
		$data['data']	=	$data;
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/member_log', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);				
	}
	
	public function import ($coaching_id=0, $role_id=USER_ROLE_STUDENT) { 
		$data['page_title'] = 'Import Users';
		$data['coaching_id'] 	= $coaching_id;
		$data['role_id'] 	= $role_id;
		$data['bc'] 		= array ('Users'=>'coaching/users/index/'.$coaching_id.'/'.$role_id);
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$role_lvl 		 	= $this->session->userdata ('role_lvl');	
		$data['roles']	 	=  $this->users_model->user_roles_by_level ($role_lvl);
		$data['batches']	 	=  $this->users_model->get_batches ($coaching_id);
		
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/import', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	
	/*
	 * Batches
	*/
	public function batches ($coaching_id=0, $batch_id=0) {
		$data["page_title"] = "Batches";
		$data["sub_title"] = "All Batches";
		$data["batch_id"] = $batch_id;
		$data["coaching_id"] = $coaching_id;
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['toolbar_buttons']['<i class="fa fa-plus"></i> New Batch'] = 'coaching/users/create_batch/'.$coaching_id;
		$data["bc"] = array ( 'Users'=>'coaching/users/index/'.$coaching_id );
		$data['all_batches'] = $this->users_model->get_batches ($coaching_id);

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/batches', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);		
	}
	
	public function create_batch ($coaching_id=0, $batch_id=0) {
		$data["page_title"] = "Create Batch";
		$data["sub_title"] = "Create New Batch";
		$data["batch_id"] = $batch_id;
		$data["coaching_id"] = $coaching_id;
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['toolbar_buttons']['<i class="fa fa-plus"></i> New Batch'] = 'coaching/users/create_batch/'.$coaching_id;
		$data["bc"] = array ( 'Batches'=>'coaching/users/batches/'.$coaching_id );
		$data['batch'] = $this->users_model->get_batch_details ($batch_id);
        if ($data['batch']) {
            $data['sub_title'] = 'Edit Batch: ' . $data['batch']['batch_name'];
        }
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/batch_create', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);		
	}
	
	
	public function batch_users ($coaching_id=0, $batch_id=0, $add_users=0) {
		$batch = $this->users_model->get_member_batches ($batch_id);
		if ( ! empty ($batch)) {
			$batch_title = $batch['batch_name'];
		} else {
			$batch_title = 'Batch Users';
		}
		$data["page_title"]  = 'Batch Users';
		$data["sub_title"] = "Users in ";
        if ($batch) {
            $data['sub_title'] .= $batch['batch_name'];
        }
		$data["batch_title"] = $batch_title;
		$data["batch_id"] = $batch_id;
		$data["coaching_id"] = $coaching_id;
		$data['add_users'] = $add_users;
		$data["bc"] = array ( 'Batches'=>'coaching/users/batches/'.$coaching_id);
		$data['toolbar_buttons'] = $this->toolbar_buttons;

		$users_not_in_batch = $this->users_model->users_not_in_batch ($batch_id, $coaching_id);
		if (! empty($users_not_in_batch)) {
		    $num_users_notin = count($users_not_in_batch);
		} else {
		    $num_users_notin = 0;
		}
		$data['num_users_notin'] = $num_users_notin;
		
		$users_in_batch = $this->users_model->batch_users ($batch_id);
		if (! empty($users_in_batch)) {
		    $num_users_in = count($users_in_batch);
		} else {
		    $num_users_in = 0;
		}
		$data['num_users_in'] = $num_users_in;
		
		if ($add_users > 0) {
		    $result = $users_not_in_batch;
		} else {
		    $result = $users_in_batch;
		}
		
		$data['result'] = $result;

		$data['script'] = $this->load->view('users/scripts/batch_users', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/batch_users', $data); 
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	/* Tests Report */
	public function courses ($coaching_id=0, $role_id=0, $member_id=0) {
		
		$this->load->model ('courses_model');
		$this->load->model ('tests_reports');
		$this->load->model ('tests_model');
		
        $data['courses'] = $courses = $this->courses_model->my_courses ($coaching_id, $member_id);
		$result 				= $this->users_model->get_user ($member_id);
		$data['profile_image']	= $this->users_model->view_profile_image ($member_id);
		$data['result'] 		= $result;
		$data['coaching_id'] 		= $coaching_id;
		$data['role_id'] 		= $role_id;
		$data['member_id'] 		= $member_id; 
		$data['page_title'] 	= 'Courses';
		$data['data']			= $data;
		
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/courses', $data); 
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	/* Tests Report */
	public function tests_taken ($coaching_id=0, $role_id=0, $member_id=0) {
		
		$this->load->model ('tests_reports');
		$this->load->model ('tests_model');
		$this->load->model ('qb_model');
		
		$tests_taken 			= $this->tests_model->test_taken_by_member ($coaching_id, $member_id);
		$result 				= $this->users_model->get_user ($member_id);
		$data['profile_image']	= $this->users_model->view_profile_image ($member_id);
		$data['result'] 		= $result;
		$data['coaching_id'] 		= $coaching_id;
		$data['role_id'] 		= $role_id;
		$data['member_id'] 		= $member_id; 
		$data['test_taken'] 	= $tests_taken;
		$data['page_title'] 	= 'Tests Taken';
		$data['data']			= $data;
		
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('users/tests_taken', $data); 
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	public function download_file ($file='users_sample') {

		$this->load->helper('download');
		$this->load->helper('file');

		if ($file == 'users_sample') {
			$file_name = 'UsersListSample.csv';
			$this->users_model->file_download($file_name);
		}
	}
}