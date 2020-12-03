<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Courses_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		// echo $this->db->last_query();
	}

	public function get_categories () {

		$this->db->where ('status', 1);
		$sql = $this->db->get ('coaching_course_category');
		$result = $sql->result_array ();
		return $result;

	}

	public function get_courses ($coaching_id=0, $category_id='-1') {

		$this->db->select ('CC.*');
		if ($this->input->post ('search')) {
			$search = $this->input->post ('search');
			$this->db->like ('CC.title', $search);
		}

		if ($category_id > '-1') {
			if ($category_id == 0) {
				$this->db->where ('CC.cat_id', 0);
				$this->db->or_where ('CC.cat_id', NULL);
			} else {
				$this->db->or_where ('CC.cat_id', $category_id);				
			}
		}
		$this->db->where ('CC.status', 1);
		$this->db->from ('coaching_courses CC');
		$sql = $this->db->get ();
		$courses = $sql->result_array ();
		$result = [];
		if (! empty($courses)) {
			foreach ($courses as $i => $course) {
				$cat = $this->get_course_category_by_id ($course['cat_id']);
				if ($cat) {
					$course['cat_title'] = $cat['title'];
				} else {
					$course['cat_title'] = 'Uncategorized';					
				}
				$result[] = $course;
			}
		}
		return $result;
	}

	public function get_course_category_by_id ($category_id=0) {
		$this->db->where('cat_id', $category_id);
		$sql = $this->db->get('coaching_course_category');
		return $sql->row_array();
	}

	public function get_course_by_id ($course_id) {
		$this->db->where('course_id', $course_id);
		$sql = $this->db->get('coaching_courses');
		$course = $sql->row_array();
		/*
		$this->db->where('course_id', $course_id);
		$sql = $this->db->get('coaching_course_batch_users');
		if(!empty($sql->row_array())){
			$course['in_my_course'] = true;
		}else{
			$course['in_my_course'] = false;
		}
		$this->db->select('first_name, last_name');
		$this->db->where ('member_id', $course['created_by']);
		$users = $this->db->get ('members');
		$created_by = $users->row_array();
		$course['created_by'] = $created_by['first_name'] . " " . $created_by['last_name'];
		*/
		return $course;
	}

	public function my_courses ($coaching_id=0, $member_id=0, $cat_id='-1') {
		$this->db->select ('CC.*, CB.batch_id');
		$this->db->from ('coaching_course_batch_users CB');
		$this->db->join ('coaching_courses CC', 'CB.course_id=CC.course_id');
		$this->db->where ('CB.coaching_id', $coaching_id);
		$this->db->where ('CB.member_id', $member_id);
		if ($cat_id > '-1') {
			//$this->db->where ('CC.cat_id', $cat_id);
		} else {
			//$this->db->where ('(CC.cat_id>=0)');
		}
		$this->db->where('CC.status', COURSE_STATUS_ACTIVE);
		$this->db->order_by ('CB.enroled_on', 'DESC');
		$sql = $this->db->get();
		$courses = $sql->result_array();
		$result  = [];
		if (! empty($courses)) {
			foreach ($courses as $course) {
				$batch = $this->enrolment_model->get_batch($coaching_id, $course['course_id'], $course['batch_id']);
				$progress = $this->enrolment_model->get_course_progress($coaching_id, $member_id, $course['course_id']);
				$cat = $this->get_course_category_by_id ($course['cat_id']);
				$la = $this->get_last_activity ($coaching_id, $member_id, $course['course_id']);
				$course['cat_title'] = $cat['title'];
				$course['batch'] = $batch;
				$course['la'] = $la;
				$course['cp'] = $progress;
				$result[] = $course;
			}
		}
		return $result;
	}


	/*
	public function get_users_courses ($coaching_id, $member_id){
		$this->db->select('course_id');
		$this->db->from('coaching_course_batch_users');
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where ('member_id', $member_id);
		$sub_query = $this->db->get_compiled_select();

		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('status', 1);
		$this->db->where ('enrolment_type', COURSE_ENROLMENT_DIRECT);
		$this->db->where ("course_id NOT IN ($sub_query)");
		$sql = $this->db->get ('coaching_courses');
		$courses = $sql->result_array();
		foreach ($courses as $i => $course) {
			$courses[$i]['lessons'] = $this->count_course_lessons($coaching_id, $course['course_id']);
			$courses[$i]['tests'] = $this->count_course_tests($coaching_id, $course['course_id']);
			$this->db->select('first_name, last_name');
			$this->db->where ('member_id', $course['created_by']);
			$users = $this->db->get ('members');
			$created_by = $users->row_array();
			$courses[$i]['created_by'] = $created_by['first_name'] . " " . $created_by['last_name'];
		}
		return $courses;
	}

	public function get_users_batch_courses ($coaching_id, $member_id){
		$this->db->select('course_id');
		$this->db->from('coaching_course_batch_users');
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where ('member_id', $member_id);
		$sub_query = $this->db->get_compiled_select();

		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('status', 1);
		$this->db->order_by('created_on', 'DESC');
		$this->db->where ("course_id IN ($sub_query)");
		$sql = $this->db->get ('coaching_courses');
		$courses = $sql->result_array();
		foreach ($courses as $i => $course) {
			$courses[$i]['lessons'] = $this->count_course_lessons($coaching_id, $course['course_id']);
			$courses[$i]['tests'] = $this->count_course_tests($coaching_id, $course['course_id']);
			//$courses[$i]['progress'] = $this->lessons_model->get_progress($member_id, $coaching_id, $course['course_id']);
			//$this->db->select('first_name, last_name');
			//$this->db->where ('member_id', $course['created_by']);
			//$users = $this->db->get ('members');
			//$created_by = $users->row_array();
			//$courses[$i]['created_by'] = $created_by['first_name'] . " " . $created_by['last_name'];
		}
		return $courses;
	}
	*/

	public function count_course_lessons ($coaching_id=0, $course_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_course_lessons');
		return $sql->num_rows ();
	}

	public function count_course_tests ($coaching_id=0, $course_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_tests');
		return $sql->num_rows ();
	}

	public function buy_course ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0){
		$this->db->where ('member_id', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->get ('coaching_course_batch_users');
		if ($sql->num_rows() == 0) {
			$data['coaching_id']	 	= $coaching_id;
			$data['course_id']	 		= $course_id;
			$data['batch_id']	 		= $batch_id;
			$data['member_id']	 		= $member_id;
			$data['enroled_on']	 		= time ();
			$data['status']	 			= COURSE_ENROLMENT_STATUS_PENDING;
			$this->db->insert('coaching_course_batch_users', $data);
		}
	}
	
	public function enrol_in_course ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0){
		$this->db->where ('member_id', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$this->db->where ('status', COURSE_ENROLMENT_STATUS_PENDING);
		$sql = $this->db->get ('coaching_course_enrol_request');
		if ($sql->num_rows() == 0) {
			$data['coaching_id']	 	= $coaching_id;
			$data['course_id']	 		= $course_id;
			$data['batch_id']	 		= $batch_id;
			$data['member_id']	 		= $member_id;
			$data['request_date']	 	= time ();
			$data['status']	 			= COURSE_ENROLMENT_STATUS_PENDING;
			$this->db->insert('coaching_course_enrol_request', $data);
		}
	}

	public function get_course_tests ($coaching_id=0, $course_id=0, $status='-1', $type=0) {
		if ( $course_id > 0 ) {
			$this->db->where ('course_id', $course_id);
		}
		if ( $type > 0 ) {
			$this->db->where ('test_type', $type);
		}
		$this->db->where ('finalized', 1);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->order_by ('creation_date', 'DESC');
		
		$query = $this->db->get ("coaching_tests");
		$results = $query->result_array();	
		return $results;
	}

	public function get_teachers_assigned ($coaching_id=0, $course_id=0, $status=1) {
		$this->db->select('member_id');
		$this->db->from('coaching_course_teachers');
		$this->db->where ('course_id', $course_id);
		$sub_query = $this->db->get_compiled_select();

		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('status', $status);
		$this->db->where ('role_id', USER_ROLE_TEACHER);
		$this->db->where ("member_id IN ($sub_query)");
		$sql = $this->db->get ('members');
		return $sql->result_array();
	}

	public function continue_course ($coaching_id, $member_id, $course_id) {
		$this->db->select(['lesson_id', 'page_id']);
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where('member_id', $member_id);
		$this->db->where('course_id', $course_id);
		$this->db->order_by('created_on', 'DESC');
		$this->db->limit(1);
		$last_progress = $this->db->get ('coaching_course_progress');
		return $last_progress->row_array();
	}

	public function begin_course($coaching_id, $course_id){
		$this->db->select('lesson_id');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->order_by ('position', 'ASC');
		$sql = $this->db->get ('coaching_course_lessons');
		$lessons = $sql->result_array();
		return reset($lessons);
		/*
		$this->db->select('lesson_id');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->order_by ('position', 'ASC');
		$this->db->limit(1);
		$sql = $this->db->get ('coaching_course_lessons');
		$first_lesson = $sql->row_array();
		echo $this->db->last_query();
		print_pre($first_lesson);
		*/
	}

	public function is_enroled ($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0) {
		$this->db->where ('member_id', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		//$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->get ('coaching_course_batch_users');
		if ($sql->num_rows() > 0) {
			return $sql->row_array ();
		} else {
			return false;
		}
	}


	public function get_course_content ($coaching_id=0, $course_id=0, $member_id=0) {

 		$this->db->select ('position, resource_type, resource_id');
 		$this->db->from ('coaching_course_contents');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->order_by ('position', 'ASC');
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		
 		$i = 1;
 		
 		$content = [];
 		$added_lessons = [];
 		$added_tests = [];
 		
 		// Get added lessons and tests
 		if (! empty ($result)) {
 			foreach ($result as $row) {
 				if ($row['resource_type'] == COURSE_CONTENT_CHAPTER) {
 					$lesson = $this->lessons_model->get_lesson ($coaching_id, $course_id, $row['resource_id']);
 					$pages = $this->get_pages ($coaching_id, $course_id, $row['resource_id'], $member_id);
					$lp = $this->enrolment_model->get_lesson_progress ($coaching_id, $member_id, $course_id, $row['resource_id']);
 					$lesson['lp'] = $lp;
 					$lesson['pages'] = $pages;
 					$lesson['resource_type'] = $row['resource_type'];
 					$lesson['resource_id'] = $row['resource_id'];
 					$added_lessons[] = $row['resource_id'];
 					$content[$i] = $lesson;
 				} else {
 					$test = $this->tests_model->view_tests ($row['resource_id']);
 					$test['resource_type'] = $row['resource_type'];
 					$test['resource_id'] = $row['resource_id'];
 					$added_tests[] = $row['resource_id'];
 					$content[$i] = $test;
 				}
 				$i++;
 			}
 		}

 		// Get lessons not added
		$this->db->select ('*');
 		$this->db->from ('coaching_course_lessons');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->where ('status', 1);
 		if (! empty($added_lessons)) {
	 		$this->db->where_not_in ('lesson_id', $added_lessons);
 		}
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		if (! empty($result)) {
 			foreach ($result as $row) {
				$lp = $this->enrolment_model->get_lesson_progress ($coaching_id, $member_id, $course_id, $row['lesson_id']);
				$pages = $this->get_pages ($coaching_id, $course_id, $row['lesson_id'], $member_id);
				$row['pages'] = $pages;
				$row['lp'] = $lp;
 				$row['resource_id'] = $row['lesson_id'];
 				$row['resource_type'] = COURSE_CONTENT_CHAPTER;
 				$content[$i] = $row;
 				$i++;
 			}
 		}

 		// Get tests not added
		$this->db->select ('*');
 		$this->db->from ('coaching_tests');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->where ('finalized', 1);
 		if (! empty($added_tests)) {
	 		$this->db->where_not_in ('test_id', $added_tests);
	 	}
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		if (! empty($result)) {
 			foreach ($result as $row) {
 				$row['resource_id'] = $row['test_id'];
 				$row['resource_type'] = COURSE_CONTENT_TEST;
 				$content[$i] = $row;
 				$i++;
 			}
 		}

 		return $content;
	}

	public function get_course_demo_content ($coaching_id=0, $course_id=0, $member_id=0) {

 		$this->db->select ('position, resource_type, resource_id');
 		$this->db->from ('coaching_course_contents');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->order_by ('position', 'ASC');
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		
 		$i = 1;
 		
 		$content = [];
 		$added_lessons = [];
 		$added_tests = [];
 		
 		// Get added lessons and tests
 		if (! empty ($result)) {
 			foreach ($result as $row) {
 				if ($row['resource_type'] == COURSE_CONTENT_CHAPTER) {
 					$lesson = $this->lessons_model->get_lesson ($coaching_id, $course_id, $row['resource_id']);
 					if ($lesson['for_demo'] == 1) {
	 					$lesson['resource_type'] = $row['resource_type'];
	 					$lesson['resource_id'] = $row['resource_id'];
	 					$added_lessons[] = $row['resource_id'];
	 					$content[$i] = $lesson; 						
 					}
 				} else {
 					$test = $this->tests_model->view_tests ($row['resource_id']);
 					if ($test['for_demo'] == 1) {
	 					$test['resource_type'] = $row['resource_type'];
	 					$test['resource_id'] = $row['resource_id'];
	 					$added_tests[] = $row['resource_id'];
	 					$content[$i] = $test;
	 				}
 				}
 				$i++;
 			}
 		}

 		// Get lessons not added
		$this->db->select ('*');
 		$this->db->from ('coaching_course_lessons');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->where ('for_demo', 1);
 		$this->db->where ('status', 1);
 		if (! empty($added_lessons)) {
	 		$this->db->where_not_in ('lesson_id', $added_lessons);
 		}
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		if (! empty($result)) {
 			foreach ($result as $row) {
 				$row['resource_id'] = $row['lesson_id'];
 				$row['resource_type'] = COURSE_CONTENT_CHAPTER;
 				$content[$i] = $row;
 				$i++;
 			}
 		}

 		// Get tests not added
		$this->db->select ('*');
 		$this->db->from ('coaching_tests');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->where ('for_demo', 1);
 		$this->db->where ('finalized', 1);
 		if (! empty($added_tests)) {
	 		$this->db->where_not_in ('test_id', $added_tests);
	 	}
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		if (! empty($result)) {
 			foreach ($result as $row) {
 				$row['resource_id'] = $row['test_id'];
 				$row['resource_type'] = COURSE_CONTENT_TEST;
 				$content[$i] = $row;
 				$i++;
 			}
 		}

 		return $content;
	}

	public function __get_course_content ($coaching_id=0, $course_id=0, $batch_id=0, $for_demo=0, $member_id=0) {

		$this->db->select ('position, resource_type, resource_id');
 		$this->db->from ('coaching_course_contents');
 		$this->db->where ('coaching_id', $coaching_id);
 		$this->db->where ('course_id', $course_id);
 		$this->db->order_by ('position', 'ASC');
 		$sql = $this->db->get ();
 		$result = $sql->result_array ();
 		
 		$i = 1;
 		
 		$content = [];
 		$added_lessons = [];
 		$added_tests = [];
 		
 		// Get added lessons and tests
 		if (! empty ($result)) {
 			foreach ($result as $row) {
 				if ($row['resource_type'] == COURSE_CONTENT_CHAPTER) {
 					$lesson = $this->lessons_model->get_lesson ($coaching_id, $course_id, $row['resource_id']);
 					if (($for_demo == 1 && $lesson['for_demo'] == 1) || $for_demo == 0) {
						$lp = $this->enrolment_model->get_lesson_progress ($coaching_id, $member_id, $course_id, $row['resource_id']);
	 					$pages = $this->get_pages ($coaching_id, $course_id, $row['resource_id'], $member_id);
	 					$lesson['resource_type'] = $row['resource_type'];
	 					$lesson['resource_id'] = $row['resource_id'];
	 					$lesson['pages'] = $pages;
	 					$lesson['lp'] = $lp;
	 					$content[$i] = $lesson;
 					}
 				} else {
 					$test = $this->tests_model->view_tests ($row['resource_id']);
 					if (($for_demo == 1 && $test['for_demo'] == 1) || $for_demo == 0) {
	 					$test['resource_type'] = $row['resource_type'];
	 					$test['resource_id'] = $row['resource_id'];
	 					$content[$i] = $test;
	 				}
 				}
 				$i++;
 			}
 		}
		return $content;
	}


	public function get_last_activity ($coaching_id=0, $member_id=0, $course_id=0) {
		//$this->db->select ('*, MAX(created_on)');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('member_id', $member_id);
		$this->db->where ('course_id', $course_id);
		$this->db->order_by ('created_on', 'DESC');
		$this->db->limit (1);
		$sql = $this->db->get ('coaching_course_progress');
		return $sql->row_array ();
	}
	
	public function get_pages ($coaching_id=0, $course_id=0, $lesson_id=0, $member_id=0) {
		//$this->db->select ('*, MAX(created_on)');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('lesson_id', $lesson_id);
		$this->db->where ('status', LESSON_STATUS_PUBLISHED);
		$sql = $this->db->get ('coaching_course_lesson_pages');
		$result = $sql->result_array ();
		$pages = [];
		if (! empty ($result)) {
			foreach ($result as $row) {
				$page = $this->lessons_model->is_page_viewed ($coaching_id, $member_id, $course_id, $lesson_id, $row['page_id']);
				$row['is_viewed'] = $page;
				$pages[] = $row;
			}
		}
		return $pages;
	}

}