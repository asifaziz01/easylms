<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Enrolment_actions extends MX_Controller {
	
    var $toolbar_buttons = []; 

	public function __construct () {
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_coaching', 'config_course', 'config_virtual_class'];
	    $models = ['coaching_model', 'enrolment_model' , 'tests_model', 'virtual_class_model', 'users_model'];
	    $this->common_model->autoload_resources ($config, $models);
	}
	
	
	public function create_batch ($coaching_id=0, $course_id=0, $batch_id=0) {
		
		$this->form_validation->set_rules ('batch_name', 'Batch Name', 'required|min_length[3]|max_length[100]');
		$this->form_validation->set_rules ('start_date', 'Batch Start Date', 'required');
		$this->form_validation->set_rules ('end_date', 'Batch End Date', 'required');
		
		if ( $this->form_validation->run() == true)  {
			if ($batch_id > 0) {
				$message = "Batch updated successfully.";
			} else {
				$message = "Batch created successfully.";
			}
			$id = $this->enrolment_model->create_batch ($coaching_id, $course_id, $batch_id);
			if ($batch_id == 0) {
				$this->virtual_class_model->create_classroom ($coaching_id, 0, $course_id, $id);
			}
			
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>$message, 'redirect'=>site_url('coaching/enrolments/batches/'.$coaching_id.'/'.$course_id) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
		
	} 
	
	public function get_batch_users ($coaching_id=0, $batch_id=0) {
		$all_users = array ();
		$batch_users = array ();
		$as = $this->enrolment_model->get_users ($coaching_id, USER_ROLE_STUDENT);
		$bs = $this->enrolment_model->batch_users ($batch_id);
		if ( ! empty ($as) ) {
			foreach ($as as $a) {
				$all_users[] = $a['member_id'];
			}
		}
		if ( ! empty ($bs) ) {
			foreach ($bs as $a) {
				$batch_users[] = $a['member_id'];
			}
		}
		$result = array_diff($all_users, $batch_users);
		$data['result'] = $result;
		$data['batch_id'] = $batch_id;
		$data['coaching_id'] = $coaching_id;
	}
	
	
	public function add_users_to_batch ($coaching_id=0, $course_id=0, $batch_id=0) {

		$this->form_validation->set_rules ('users[]', 'Users', 'required');
		if ($this->form_validation->run () == true) {
			$this->enrolment_model->add_users_to_batch ($coaching_id, $course_id, $batch_id);			
			$message = 'User(s) added to batch successfully';
			$this->message->set ($message, 'success', true);
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>$message, 'redirect'=>site_url('coaching/enrolments/batch_users/'.$coaching_id.'/'.$course_id.'/'.$batch_id.'/0') )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors () )));
		}
	}

	public function approve_enrolment ($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0) {		
		$this->enrolment_model->approve_enrolment ($coaching_id, $course_id, $batch_id, $member_id);
		$message = 'User enroled in course successfully';
		$this->message->set ($message, 'success', true);
		redirect ('coaching/courses/enrolment_requests/'.$coaching_id);
	}
	
	public function remove_batch_users ($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0, $add_user=0) {		
		$this->form_validation->set_rules ('users[]', 'Users', 'required');
		if ($this->form_validation->run () == true) {
			$users = $this->input->post ('users');
			foreach ($users as $member_id) {
				$this->enrolment_model->remove_batch_user ($coaching_id, $course_id, $batch_id, $member_id);
			}
		} else {
			$this->enrolment_model->remove_batch_user ($coaching_id, $course_id, $batch_id, $member_id);	
		}
		$message = 'User(s) removed from batch successfully';
		$this->message->set ($message, 'success', true);
		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'message'=>$message, 'redirect'=>site_url('coaching/enrolments/batch_users/'.$coaching_id.'/'.$course_id.'/'.$batch_id) )));
		//redirect ('coaching/enrolments/batch_users/'.$coaching_id.'/'.$course_id.'/'.$batch_id);
	}
	

	public function remove_batch_user ($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0, $add_user=0) {
		$this->enrolment_model->remove_batch_user ($coaching_id, $course_id, $batch_id, $member_id);
		$this->message->set ('User removed from batch successfully', 'success', true);
		redirect ('coaching/enrolments/batch_users/'.$coaching_id.'/'.$course_id.'/'.$batch_id);
	}
	
	public function delete_batch ($coaching_id=0, $course_id=0, $batch_id=0) {
		$this->enrolment_model->delete_batch ($coaching_id, $course_id, $batch_id);
		redirect ('coaching/enrolments/batches/'.$coaching_id.'/'.$course_id);
	}


	public function add_schedule ($coaching_id=0, $course_id=0, $batch_id=0) {

		$this->form_validation->set_rules ('repeat', 'Repeat', 'required');
		$this->form_validation->set_rules ('instructor', 'Instructor', 'required');
		$this->form_validation->set_rules ('classroom', 'Classroom', 'required');
		
		$message = 'Schedule added successfully';
		if ( $this->form_validation->run() == true)  {
			//$message = "Schedule created successfully";
			$this->enrolment_model->add_schedule ($coaching_id, $course_id, $batch_id);
			
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>$message, 'redirect'=>site_url('coaching/enrolments/schedule/'.$coaching_id.'/'.$course_id.'/'.$batch_id))));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
		
	}
}