<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Enrolment_model extends CI_Model {
    
	
	// Get Batches
	public function get_batches ($coaching_id=0, $course_id=0) {
		if ($course_id > 0) {
	    	$this->db->where ('CB.course_id', $course_id);
		}
	    $this->db->where ('coaching_id', $coaching_id);
	    $sql = $this->db->get ('coaching_course_batches CB');
	    return $sql->result_array ();
	}
	
	// Get single batch details
	public function get_batch ($coaching_id=0, $course_id=0, $batch_id=0) {
	    $this->db->where ('coaching_id', $coaching_id);
	    if ($course_id > 0) {
	    	$this->db->where ('course_id', $course_id);
		}
	    $this->db->where ('batch_id', $batch_id);
	    $sql = $this->db->get ('coaching_course_batches');
	    return $sql->row_array ();
	}
	
	public function batch_users ($coaching_id=0, $course_id=0, $batch_id=0) {
		$this->db->select ('M.*, SR.description, MB.enroled_on');
		$this->db->from ('coaching_course_batch_users MB, sys_roles SR');
		$this->db->join ('members M', 'MB.member_id=M.member_id');
		$this->db->where ('SR.role_id=M.role_id');
		$this->db->where ('MB.batch_id', $batch_id);
		$this->db->where ('MB.course_id', $course_id);
		$this->db->where ('MB.coaching_id', $coaching_id);
		$sql = $this->db->get ();		
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->result_array ();
		}else{
			$result = false;
		}		
		return $result;
	}	


	public function create_batch ($coaching_id=0, $course_id=0, $batch_id=0) {
		
		$time = time ();
		$data = array ();
		$data['batch_name'] = $this->input->post ('batch_name');
		if ($this->input->post ('max_users')) {
			$data['max_users'] = $this->input->post ('max_users');
		} else {
			$data['max_users'] = 0;
		}
		$start_date = $this->input->post ('start_date');
		$end_date 	= $this->input->post ('end_date');
		if (! empty($start_date)) {
			list ($sd, $sm, $sy)	= explode ('-', $start_date);
			$sdate = mktime (0, 0, 0, $sm, $sd, $sy);			
			$data['start_date'] = ($sdate);
		}
		if (! empty($end_date)) {
			list ($ed, $em, $ey)	= explode ('-', $end_date);
			$edate = mktime (0, 0, 0, $em, $ed, $ey);
			$data['end_date'] = ($edate);
		}
		
		if ($batch_id > 0) {
			$this->db->where ('batch_id', $batch_id);
			$this->db->update ('coaching_course_batches', $data);			
		} else {
			$data['status'] = 1;
			$data['coaching_id'] 	= $coaching_id;
			$data['course_id'] 		= $course_id;
			$data['creation_date'] 	= $time;
			$data['created_by'] 	= intval ($this->session->userdata ('member_id'));
			$this->db->insert ('coaching_course_batches', $data);
			$batch_id = $this->db->insert_id ();
		}

		return $batch_id;
	}

	// Check if user in batch
	public function user_in_batch ($coaching_id=0, $course_id=0, $member_id=0, $batch_id=0) {
	    $this->db->where ('coaching_id', $coaching_id);
	    $this->db->where ('course_id', $course_id);
	    $this->db->where ('batch_id', $batch_id);
	    $this->db->where ('member_id', $member_id);
	    $sql = $this->db->get ('coaching_batch_users');
	    return $sql->row_array ();
	}	
	

	public function users_not_in_batch ($coaching_id=0, $course_id=0, $batch_id=0, $role_id=USER_ROLE_STUDENT) {
		// Get batch users
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->get ('coaching_course_batch_users');
		$users = $sql->result_array ();
		$data = [];
		if (! empty ($users)) {
			foreach ($users as $u) {
				$data[] = $u['member_id'];
			}
		}

		// Get all users not in batch
		$this->db->select ('M.*, R.description');
		$this->db->from ('members M');
		$this->db->join ('sys_roles R', 'M.role_id=R.role_id');
		if (! empty($data)) {
			$this->db->where_not_in ('M.member_id', $data);
		}
		$this->db->where ('M.role_id', $role_id);
		$this->db->where ('M.coaching_id', $coaching_id);

		$sql = $this->db->get ();
		$users = $sql->result_array ();
		return $users;
	}	
	
	public function add_users_to_batch ($coaching_id=0, $course_id=0, $batch_id=0) {
		
		$users = $this->input->post ('users');
		foreach ($users as $member_id) {
			$this->db->where ('coaching_id', $coaching_id);
			$this->db->where ('course_id', $course_id);
			$this->db->where ('batch_id', $batch_id);
			$this->db->where ('member_id', $member_id);
			$sql = $this->db->get ('coaching_course_batch_users');
			if  ($sql->num_rows () == 0 ) { 
				$data['coaching_id'] = $coaching_id;
				$data['course_id'] = $course_id;
				$data['member_id'] = $member_id;
				$data['batch_id']  = $batch_id;
				$data['enroled_on']  = time ();
				$sql = $this->db->insert ('coaching_course_batch_users', $data);
			}
		}
		$vc = $this->virtual_class_model->get_batch_vc ($coaching_id, $course_id, $batch_id);
		if ($vc) {
			$this->virtual_class_model->add_participants ($coaching_id, $vc['class_id'], $course_id, $batch_id);
		}
	}

	public function remove_batch_user ($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0) {
		// Remove from batch
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->delete ('coaching_course_batch_users');

		// delete progress
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->delete ('coaching_course_progress');

		// Remove Test history
		$this->tests_model->unenrol_member ($coaching_id, $member_id);
		
		// Remove Virtual Class enrolments
		$this->tests_model->unenrol_member ($coaching_id, $member_id);
	}

	public function delete_batch ($coaching_id=0, $course_id=0, $batch_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->delete ('coaching_course_batches');

		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->delete ('coaching_course_batch_users');
	}
	
	public function get_course_instructors ($coaching_id=0, $course_id=0) {
		$this->db->select ('M.*');
		$this->db->from ('coaching_course_teachers CC');
		$this->db->join ('members M', 'CC.member_id=M.member_id');
		$this->db->where ('CC.coaching_id', $coaching_id);
		$this->db->where ('CC.course_id', $course_id);
		$sql = $this->db->get ();
		return $sql->result_array ();
	}

	public function get_course_schedule ($coaching_id=0, $course_id=0, $batch_id=0) {
		$this->db->select ('CC.*, CONCAT (M.first_name, M.last_name) AS name'); 
		$this->db->from ('coaching_course_schedules CC');
		$this->db->join ('members M', 'CC.member_id=M.member_id');
		$this->db->where ('CC.coaching_id', $coaching_id);
		$this->db->where ('CC.course_id', $course_id);
		$this->db->where ('CC.batch_id', $batch_id);
		$sql = $this->db->get ();
		return $sql->result_array ();
	}

	public function get_schedule_data ($coaching_id=0, $course_id=0, $batch_id=0, $dow=0) {
		$this->db->select ('CCL.title AS room_name, CC.*, CONCAT (M.first_name, M.last_name) AS name'); 
		$this->db->from ('coaching_course_schedules CC, coaching_classrooms CCL');
		$this->db->join ('members M', 'CC.member_id=M.member_id');
		$this->db->where ('CCL.id=CC.room_id');
		$this->db->where ('CC.coaching_id', $coaching_id);
		$this->db->where ('CC.course_id', $course_id);
		$this->db->where ('CC.batch_id', $batch_id);
		$this->db->where ('CC.dow', $dow);
		$sql = $this->db->get ();
		return $sql->result_array ();
	}

	public function add_schedule ($coaching_id=0, $course_id=0, $batch_id=0) {

		$batch = $this->get_batch ($coaching_id, $course_id, $batch_id);
		//$start_time = $batch['start_date'];
		//$end_time 	= $batch['end_date'];
		$start_time = strtotime($this->input->post ('repeat_start'));
		$end_time = strtotime($this->input->post ('repeat_end'));
		$repeat = $this->input->post ('repeat');
		$now = time ();
		$insert = [];
		if ($repeat == SCHEDULE_REPEAT_DAILY) {
			$interval = 24 * 60 * 60; 		// 1 day in seconds
			for ($i=$start_time; $i<=$end_time; $i=$i+$interval) {
				$data['id'] 			= NULL;
				$data['coaching_id'] 	= $coaching_id;
				$data['course_id'] 		= $course_id;
				$data['batch_id'] 		= $batch_id;
				$data['member_id'] 		= $this->input->post ('instructor');
				$data['room_id'] 		= $this->input->post ('classroom');
				$data['start_time'] 	= $this->input->post ('start_time');
				$data['end_time'] 		= $this->input->post ('end_time');
				$data['dow'] 			= $i;
				$data['created_by'] 	= $this->session->userdata ('member_id');
				$data['created_on'] 	= $now;
				$insert[]				= $data;
			}
			$this->db->insert_batch ('coaching_course_schedules', $insert);
		} else if ($repeat == SCHEDULE_REPEAT_WEEKLY) {
			$dow = $this->input->post ('dow');
			$num_weeks = date ('W', $end_time) - date ('W', $start_time);
			$first_dow = date ('N', $start_time);
			$inc = 7;
			$week_days = 0;
			$insert = [];
			for ($i=1; $i<=$num_weeks; $i++) {
				foreach ($dow as $w) {
					$day = $week_days - $first_dow + $w;
					$interval = $day * 24 * 60 * 60; 		// Num days in seconds
					$timestamp = $start_time + $interval;						
					$data['id'] 			= NULL;
					$data['coaching_id'] 	= $coaching_id;
					$data['course_id'] 		= $course_id;
					$data['batch_id'] 		= $batch_id;
					$data['member_id'] 		= $this->input->post ('instructor');
					$data['room_id'] 		= $this->input->post ('classroom');
					$data['start_time'] 	= $this->input->post ('start_time');
					$data['end_time'] 		= $this->input->post ('end_time');
					$data['dow'] 			= $timestamp;
					$data['created_by'] 	= $this->session->userdata ('member_id');
					$data['created_on'] 	= $now;
					$insert[]				= $data;
				}
				$week_days = $week_days + $inc;
			}
			$this->db->insert_batch ('coaching_course_schedules', $insert);

		}
	}

	public function get_progress ($member_id=0, $coaching_id=0, $course_id=0){
		$this->db->select ('count(page_id) as total_pages');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_course_lesson_pages');
		$total_pages = $sql->row_array();
		$this->db->select ('count(progress_id) as total_progress');
		$this->db->where ('member_id', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_course_progress');
		$total_progress = $sql->row_array();
		return array_merge($total_pages, $total_progress);
	}

	public function get_course_progress ($coaching_id=0, $member_id=0, $course_id=0) {
		$result = [];
		$this->db->select ('COUNT(page_id) as total_pages');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_course_lesson_pages');
		$row = $sql->row_array();
		$result['total_pages'] = $row['total_pages'];

		$this->db->select ('COUNT(progress_id) as total_progress');
		$this->db->where ('member_id', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_course_progress');
		$row = $sql->row_array();
		$result['total_progress'] = $row['total_progress'];
		return $result;
	}

	public function get_lesson_progress ($coaching_id=0, $member_id=0, $course_id=0, $lesson_id=0) {
		$result = [];
		$this->db->select ('COUNT(page_id) as total_pages');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('lesson_id', $lesson_id);
		$sql = $this->db->get ('coaching_course_lesson_pages');
		$row = $sql->row_array();
		$result['total_pages'] = $row['total_pages'];

		$this->db->select ('COUNT(progress_id) as total_progress');
		$this->db->where ('member_id', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('lesson_id', $lesson_id);
		$sql = $this->db->get ('coaching_course_progress');
		$row = $sql->row_array();
		$result['total_progress'] = $row['total_progress'];
		return $result;
	}

	public function approve_enrolment ($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0) {
		
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->get ('coaching_course_batch_users');
		if  ($sql->num_rows () == 0 ) { 
			$data['coaching_id'] = $coaching_id;
			$data['course_id'] = $course_id;
			$data['member_id'] = $member_id;
			$data['batch_id']  = $batch_id;
			$data['enroled_on']  = time ();
			$sql = $this->db->insert ('coaching_course_batch_users', $data);
			// change request
			$this->db->set ('status', 1);
			$this->db->where ('coaching_id', $coaching_id);
			$this->db->where ('course_id', $course_id);
			$this->db->where ('batch_id', $batch_id);
			$this->db->where ('member_id', $member_id);
			$sql = $this->db->update ('coaching_course_enrol_request');
		}
	
		$vc = $this->virtual_class_model->get_batch_vc ($coaching_id, $course_id, $batch_id);
		if ($vc) {
			$this->virtual_class_model->add_participants ($coaching_id, $vc['class_id'], $course_id, $batch_id);
		}
	}

}