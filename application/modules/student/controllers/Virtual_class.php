<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Virtual_class extends MX_Controller {	

	var $toolbar_buttons = [];

	public function __construct () {		
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_student', 'config_virtual_class', 'config_course'];
	    $models = ['virtual_class_model', 'users_model', 'courses_model', 'enrolment_model'];
	    $this->common_model->autoload_resources ($config, $models);

        $cid = $this->uri->segment (4);        
        
        // Security step to prevent unauthorized access through url
        if ($this->session->userdata ('is_admin') == TRUE) {
        } else {
            if ($cid == true && $this->session->userdata ('coaching_id') <> $cid) {
                $this->message->set ('Direct url access not allowed', 'danger', true);
                redirect ('student/home/dashboard');
            }
        }
	}

	public function index ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		$this->my_classroom ($coaching_id, $member_id, $course_id, $batch_id);
	} 

	public function my_classroom ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		
		if ($coaching_id == 0) {
			$coaching_id = $this->session->userdata ('coaching_id');
		}
		
		if ($member_id == 0) {
			$member_id = $this->session->userdata ('member_id');
		}

		$data['coaching_id'] = $coaching_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['member_id'] = $member_id;
		$data['bc'] = array('Course'=>'student/courses/home/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id);
		$data['class'] = $this->virtual_class_model->my_classroom ($coaching_id, $member_id, $course_id, $batch_id);
		$data['course'] = $this->courses_model->get_course_by_id ($course_id);
		$data['batch'] = $batch = $this->enrolment_model->get_batch ($coaching_id, $course_id, $batch_id);
		if ($course_id > 0) {
			$data['right_sidebar'] = $this->load->view ('courses/inc/home', $data, true);			
		}
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/my_classroom', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}	

	public function join_class ($coaching_id=0, $class_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		
		$api_setting = $this->virtual_class_model->get_api_settings ('join_url');
		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		$join = $this->virtual_class_model->join_class ($coaching_id, $class_id, $member_id);
		$meeting_url = $join['meeting_url'];
		$api_join_url = $api_setting['join_url'];
		$join_url = $api_join_url . $meeting_url;
		
		$is_running = $this->virtual_class_model->is_meeting_running ($coaching_id, $class_id);

		$now = time ();

			if ( $is_running == 'true') {
				redirect ($join_url);
			} else {
				/*
				$response = $this->virtual_class_model->create_meeting ($coaching_id, $class_id);
				if ($response == 'SUCCESS') {
					redirect ($join_url);
				}
				*/
				$data['error'] = 'Class has not started yet';
			}
		//} else {
		//}

		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['member_id'] = $member_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['page_title'] = 'Classroom Not Started';
		$data['bc'] = array('Virtual Classroom'=>'student/virtual_class/index/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id);

        $data['script'] = $this->load->view('virtual_class/scripts/error', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/error', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);		
	}

	public function recordings ($coaching_id=0, $class_id=0, $meeting_id=0, $course_id=0, $batch_id=0, $member_id=0) {		

		$api_setting = $this->virtual_class_model->get_api_settings ();
		$data['toolbar_buttons'] = $this->toolbar_buttons;
		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);

		if ($class['recording_for_students'] != 'true') {
			$this->message->set ('No recordings available', 'success', true);
			redirect ('student/virtual_class/my_classroom/'.$coaching_id.'/'.$class_id);
		}

		// Recordings 
		$recordings = $this->virtual_class_model->get_recording_data ($coaching_id, $class_id, $meeting_id, $course_id, $batch_id);

		$data['recordings'] 	= $recordings;

		$data['coaching_id'] 	= $coaching_id;
		$data['class_id'] 		= $class_id;
		$data['meeting_id'] 	= $meeting_id;
		$data['course_id'] 		= $course_id;
		$data['batch_id'] 		= $batch_id;
		$data['class'] 			= $class;
		$data['page_title'] 	= 'Recordings';
		$data['bc'] = array('Classrooms'=>'student/virtual_class/index/'.$coaching_id);

        $data['script'] = $this->load->view ('virtual_class/scripts/recordings', $data, true);
        $this->load->view(INCLUDE_PATH . 'header', $data);
        $this->load->view('virtual_class/recordings', $data);
        $this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function end_meeting ($coaching_id=0, $class_id=0) {
		redirect ('student/virtual_class/index/'.$coaching_id);
	}

	public function is_meeting_running ($coaching_id=0, $class_id=0, $member_id=0) {
		
		$api_setting = $this->virtual_class_model->get_api_settings ('join_url');
		$join = $this->virtual_class_model->join_class ($coaching_id, $class_id, $member_id);
		$meeting_url = $join['meeting_url'];
		$api_join_url = $api_setting['join_url'];
		$join_url = $api_join_url . $meeting_url;

		$is_running = $this->virtual_class_model->is_meeting_running ($coaching_id, $class_id);

		if ( $is_running == 'true') {
			$redirect = $join_url;
			$this->output->set_content_type("application/json");
	        $this->output->set_output(json_encode(array('status'=>true, 'redirect'=>$redirect )));
		} else {
			$this->output->set_content_type("application/json");
	        $this->output->set_output(json_encode(array('status'=>false, 'message'=>'' )));
		}
	}

}