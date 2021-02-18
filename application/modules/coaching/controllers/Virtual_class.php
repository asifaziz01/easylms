<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Virtual_class extends MX_Controller {	

	var $toolbar_buttons = [];

	public function __construct () {		
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_coaching', 'config_virtual_class', 'config_course'];
	    $models = ['virtual_class_model', 'users_model', 'subscription_model', 'courses_model', 'enrolment_model'];
	    $this->common_model->autoload_resources ($config, $models);

	    $this->load->helper ('string'); 

	    $cid = $this->uri->segment (4);
	    $course_id = $this->uri->segment (5);
	    $batch_id = $this->uri->segment (6);
        //$this->toolbar_buttons['<i class="fa fa-list"></i> All Classes']= 'coaching/virtual_class/index/'.$cid.'/'.$course_id.'/'.$batch_id;
        
        if ($this->session->userdata ('is_admin') == TRUE) {
        } else {
        	// Security step to prevent unauthorized access through url
            if ($cid == true && $this->session->userdata ('coaching_id') <> $cid) {
                $this->message->set ('Direct url access not allowed', 'danger', true);
                redirect ('coaching/home/dashboard');
            }

        	// Check subscription plan expiry
            $coaching = $this->subscription_model->get_coaching_subscription ($cid);
            $today = time ();
            $current_plan = $coaching['subscription_id'];
            if ($today > $coaching['ending_on'] && $this->session->userdata ('role_id') != USER_ROLE_STUDENT) {
            	$this->message->set ('Your subscription has expired. Choose a plan to upgrade', 'danger', true);
            	redirect ('coaching/subscription/browse_plans/'.$cid.'/'.$current_plan);
            }
        }
	}
	
	public function index ($coaching_id=0, $course_id=0, $batch_id=0) {
		
		$is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));

		$data['is_admin'] 		= $is_admin;
		$data['coaching_id'] 	= $coaching_id;
		$data['course_id'] 		= $course_id;
		$data['batch_id'] 		= $batch_id;		
		$member_id 				= $this->session->userdata ('member_id');
		$data['page_title'] 	= 'Virtual Classrooms';


		$data['bc'] = array('Manage'=>'coaching/enrolments/batches/'.$coaching_id.'/'.$course_id);

		//$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['class'] = $this->virtual_class_model->get_all_classes($coaching_id, $course_id, $batch_id);

		$data['course'] = $this->courses_model->get_course_by_id ($course_id);

        $data['script'] = $this->load->view('virtual_class/scripts/index', $data, true);
		$data['right_sidebar'] = $this->load->view ('courses/inc/manage_course', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/index', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}

	public function my_classes ($coaching_id=0, $member_id=0){
		if($coaching_id==0){
			$coaching_id 				= $this->session->userdata ('coaching_id');
		}
		$data['coaching_id'] 	= $coaching_id;
		if($member_id==0){
			$member_id 				= $this->session->userdata ('member_id');
		}
		$data['member_id'] 	= $member_id;
		$data['page_title'] 	= 'My Classes';
		$data['bc'] = array('Dashboard'=>'coaching/home/teacher/'.$coaching_id);
		$data['my_classes'] = $this->virtual_class_model->get_my_classes($coaching_id, $member_id);

		$this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/my_classes', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function create_class ($coaching_id=0, $class_id=0, $course_id=0, $batch_id=0) {
		
        if ($this->session->userdata ('role_id') == USER_ROLE_TEACHER ) {
        	$this->message->set ('Not allowed', 'danger', true);
        	redirect ('coaching/virtual_class/index/'.$coaching_id.'/'.$class_id.'/'.$course_id.'/'.$batch_id);
        }

		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['page_title'] = 'Create Classroom';
		$data['bc'] = array('Virtual Class'=>'coaching/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);

		$data['class'] = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		$data['categories'] = $this->virtual_class_model->get_categories ($coaching_id);
		$data['attendee_pwd'] = random_string ('numeric', 4);
		$data['moderator_pwd'] = random_string ('numeric', 4);

		if ($class_id > 0) {
			$data['category_id'] = $data['class']['category_id'];
		} else {
			$data['category_id'] = $category_id;
		}

        $data['script'] = $this->load->view('virtual_class/scripts/create_class', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/create_class', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}

	public function add_participants ($coaching_id=0, $class_id=0, $course_id=0, $batch_id=0, $role_id=USER_ROLE_STUDENT) {
		
		$roles = [USER_ROLE_COACHING_ADMIN, USER_ROLE_TEACHER, USER_ROLE_STUDENT];

		if ($role_id <> USER_ROLE_STUDENT) {
			//$batch_id = 0;
		}

		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['class'] = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		if ($role_id == USER_ROLE_COACHING_ADMIN) {
			$data['users'] = $this->users_model->get_users ($coaching_id, $role_id, 1);
		} else if ($role_id == USER_ROLE_TEACHER) {
			$data['users'] = $this->enrolment_model->get_course_instructors ($coaching_id, $course_id, $batch_id);
		} else {
			$data['users'] = $this->enrolment_model->batch_users ($coaching_id, $course_id, $batch_id);
		}
		$data['roles'] = $this->users_model->get_user_roles (0, 0, $roles);
		$data['batches'] = $this->enrolment_model->get_batches ($coaching_id, $course_id);
		$participants = $this->virtual_class_model->get_participants ($coaching_id, $class_id);
		if (! empty($participants)) {
			$num_participants = count($participants);
		} else {
			$num_participants = 0;
		}

		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['course_id'] = $course_id;
		$data['role_id'] = $role_id;
		$data['batch_id'] = $batch_id;
		$data['num_participants'] = $num_participants;
		$data['page_title'] = 'Add Participants';
		$data['bc'] = array('Participants'=>'coaching/virtual_class/participants/'.$coaching_id.'/'.$class_id.'/'.$course_id.'/'.$batch_id);

        $data['script'] = $this->load->view ('virtual_class/scripts/add_participants', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/add_participants', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}

	public function participants ($coaching_id=0, $class_id=0, $course_id=0, $batch_id=0) {		

		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['class'] = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		$data['participants'] = $this->virtual_class_model->get_participants ($coaching_id, $class_id);
		$data['api_setting'] = $this->virtual_class_model->get_api_settings ('join_url');

		if (! empty($data['participants'])) {
			$num_participants = count($data['participants']);
		} else {
			$num_participants = 0;
		}

		$data['num_participants'] = $num_participants;
		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['page_title'] = 'Participants';
		$data['bc'] = array('Virtual Classroom'=>'coaching/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);

        //$data['script'] = $this->load->view('attendance/scripts/index', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/participants', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}


	public function class_details ($coaching_id=0, $class_id=0) {		

		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['page_title'] = 'Virtual Classroom';
		$data['bc'] = array('Dashboard'=>'coaching/virtual_class/index/'.$coaching_id);

		$member_id = $this->session->userdata ('member_id');
		$user = $this->users_model->get_user ($member_id);
		$fullName = $user['first_name'];
		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);

		$api_setting = $this->virtual_class_model->get_api_settings ();
		$shared_secret = $api_setting['shared_secret'];

		$call_name = 'join';

		$query_string = '';
		$query_string .= 'fullName='.$fullName;
		$query_string .= '&meetingID='.$class['meeting_id'];
		$query_string .= '&password='.$class['moderator_pwd'];
		$query_string .= '&userID='.$user['adm_no'];

		$final_string = $call_name . $query_string . $shared_secret;

		$checksum = sha1 ($final_string);

		$meeting_url = '';
		$meeting_url .= $api_setting['api_url'];
		$meeting_url .= $call_name;
		$meeting_url .= '?';
		$meeting_url .= $query_string;
		$meeting_url .= '&checksum='.$checksum;

		$data['class'] = $class;
		$data['meeting_url'] = $meeting_url;
		$data['api_setting'] = $api_setting;

        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/class_details', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}

	public function recordings ($coaching_id=0, $class_id=0, $meeting_id=0, $course_id=0, $batch_id=0) {		

		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);
 
		// Recordings 
		$recordings = $this->virtual_class_model->get_recording_data ($coaching_id, $class_id, $meeting_id, $course_id, $batch_id);

		$data['recordings'] = $recordings;

		$data['coaching_id'] 	= $coaching_id;
		$data['class_id'] 		= $class_id;
		$data['class'] 			= $class;
		$data['meeting_id'] 	= $meeting_id;
		$data['course_id'] 		= $course_id;
		$data['batch_id'] 		= $batch_id;
		$data['page_title'] 	= 'Recordings';
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$data['bc'] = array('Classrooms'=>'coaching/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);

        $data['script'] = $this->load->view ('virtual_class/scripts/recordings', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/recordings', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);
	}
		

	public function join_class ($coaching_id=0, $class_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		
		$api_setting = $this->virtual_class_model->get_api_settings ('join_url');
		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		$join = $this->virtual_class_model->join_class ($coaching_id, $class_id, $member_id);

		$error_code = $this->config->item ('vc_error_code');

		if ($join) {
			// User is a participant in this classroom
			$meeting_url = $join['meeting_url'];
			$api_join_url = $api_setting['join_url'];
			$join_url = $api_join_url . $meeting_url;

			$is_running = $this->virtual_class_model->is_meeting_running ($coaching_id, $class_id);

			if ( $is_running == 'true') {
				// if class has already started, join class
				$this->virtual_class_model->add_member_log ($coaching_id, $class_id, $member_id);
				redirect ($join_url);
			} else {
				// if class has not started, create class and join (if moderator of class)
				$response = $this->virtual_class_model->create_meeting ($coaching_id, $class_id);
				if ($response == 'SUCCESS') {
					$this->virtual_class_model->add_member_log ($coaching_id, $class_id, $member_id);
					redirect ($join_url);
				} else {
					$error = $error_code[1];
				}
			}
		} else {
			$error = $error_code[3];
		}
		
		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['page_title'] = 'Classroom Error';
		$data['bc'] = array('Virtual Classroom'=>'coaching/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);
		
		$data['error'] = $error;

        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/error', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}

	public function end_meeting ($coaching_id=0, $class_id=0, $course_id=0, $batch_id=0) {

		$member_id = $this->session->userdata ('member_id');

		$this->virtual_class_model->update_member_log ($coaching_id, $member_id);
		$this->virtual_class_model->update_meeting_log ($coaching_id);
		
		if ($this->session->userdata ('role_id') == USER_ROLE_STUDENT) {
			redirect ('student/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);
		} else {
			redirect ('coaching/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);
		}
	}


	public function meeting_logs ($coaching_id=0, $class_id=0, $course_id=0, $batch_id=0) {
		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['page_title'] = 'Meeting Logs';

		$data['bc'] = array('Virtual Classroom'=>'coaching/virtual_class/index/'.$coaching_id.'/'.$course_id.'/'.$batch_id);

		//$data['info'] = $this->virtual_class_model->get_meeting_info ($coaching_id, $class_id);
		$data['history'] = $this->virtual_class_model->get_meeting_logs ($coaching_id, $class_id, $course_id);
		
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('virtual_class/meeting_logs', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	public function get_participant_logs ($coaching_id=0, $class_id=0, $member_id=0, $course_id=0, $batch_id=0) {

	}
}