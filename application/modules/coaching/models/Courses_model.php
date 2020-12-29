<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Courses_model extends CI_Model {

	public function __construct() {
		parent::__construct();
		// echo $this->db->last_query();
	}

	public function course_categories($coaching_id, $status = CATEGORY_STATUS_ACTIVE) {
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where('status', $status);
		$sql = $this->db->get('coaching_course_category');		$categories = $sql->result_array();
		foreach ($categories as $i => $category) {
			$this->db->select('count(course_id) as course_count');
			$this->db->where('cat_id', $category['cat_id']);
			$sql = $this->db->get('coaching_courses');
			extract($sql->row_array());
			$categories[$i]['course_count']=$course_count;
		}
		return $categories;
	}
	
	public function courses_uncategorized ($coaching_id=0) {
		$this->db->select('count(course_id) as uncategorized_courses');
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where('(cat_id=0 OR cat_id="NULL")');
		$sql = $this->db->get('coaching_courses');
		extract($sql->row_array());
		return $uncategorized_courses;
	}
	
	public function courses ($coaching_id=0, $cat_id='-1', $status=CATEGORY_STATUS_ALL) {
		$this->db->select ('CONCAT(M.first_name, M.last_name) AS user_name, CC.*');
		$this->db->from ('coaching_courses CC');
		$this->db->join ('members M', 'CC.created_by=M.member_id');
		$this->db->where ('CC.coaching_id', $coaching_id);
		if ($cat_id == '-1') {
			
		} else if ($cat_id == 0) {
			$this->db->where('(CC.cat_id=0 OR CC.cat_id="NULL")');
		} else {
			$this->db->where ('CC.cat_id', $cat_id);
		}
		if ($status > CATEGORY_STATUS_ALL) {
			$this->db->where('CC.status', $status);
		}
		$this->db->order_by ('CC.created_on', 'DESC');
		$sql = $this->db->get();
		$courses = $sql->result_array();
		$result  = [];
		if (! empty($courses)) {
			foreach ($courses as $i => $course) {
				$cat_id = intval ($course['cat_id']);
				$cat = $this->get_course_category_by_id ($cat_id);
				if ($cat) {
					$course['cat_title'] = $cat['title'];					
				}
				$result[] = $course;
			}
		}
		return $result;
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


	public function count_all_courses ($coaching_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$sql = $this->db->get('coaching_courses');
		$num = $sql->num_rows ();
		return $num;
	}

	public function count_un_courses ($coaching_id=0) {
		$this->db->where ('CC.coaching_id', $coaching_id);
		$this->db->where('(cat_id=0 OR cat_id="NULL")');
		$sql = $this->db->get('coaching_courses CC');
		$num = $sql->num_rows ();
		return $num;
	}

	public function member_courses ($coaching_id, $cat_id, $status = CATEGORY_STATUS_ALL){
        $this->db->select(
        	array(
        		'coaching_courses.*',
        		'coaching_course_teachers.created_on AS assigned_on',
        		'coaching_course_teachers.created_by AS assigned_by'
        	)
        );
		if($status != CATEGORY_STATUS_ALL){
			$this->db->where('coaching_courses.status', $status);
		}
		if ($cat_id > 0) {
			$this->db->where('coaching_courses.cat_id', $cat_id);
		}
        $this->db->where('coaching_courses.coaching_id', $coaching_id);
        $this->db->where('coaching_course_teachers.member_id', $this->session->userdata ('member_id'));
        $this->db->join('coaching_course_teachers','coaching_courses.course_id = coaching_course_teachers.course_id');
        $sql = $this->db->get('coaching_courses');
		$courses = $sql->result_array();
		foreach ($courses as $i => $course) {
			$cat = $this->get_course_category_by_id ($course['cat_id']);
			$courses[$i]['cat_title'] = $cat['title'];
			$created_by = $this->users_model->get_user($course['created_by']);
			$courses[$i]['created_by'] = $created_by['first_name'] . " " . $created_by['last_name'];
			$assigned_by = $this->users_model->get_user($course['assigned_by']);
			$courses[$i]['assigned_by'] = $assigned_by['first_name'] . " " . $assigned_by['last_name'];
		}
		return $courses;
	}
	public function member_courses_by_type($type = COURSE_ENROLMENT_DIRECT, $coaching_id=0, $cat_id=0, $status = CATEGORY_STATUS_ALL){
        $this->db->select(
        	array(
        		'coaching_courses.*',
        		'coaching_course_teachers.created_on AS assigned_on',
        		'coaching_course_teachers.created_by AS assigned_by'
        	)
        );
		$this->db->where('coaching_courses.enrolment_type', $type);
        if($status != CATEGORY_STATUS_ALL){
			$this->db->where('coaching_courses.status', $status);
		}
		if($cat_id>0){
			$this->db->where('coaching_courses.cat_id', $cat_id);
		}
        $this->db->where('coaching_courses.coaching_id', $coaching_id);
        $this->db->where('coaching_course_teachers.member_id', $this->session->userdata ('member_id'));
        $this->db->join('coaching_course_teachers','coaching_courses.course_id = coaching_course_teachers.course_id');
        $sql = $this->db->get('coaching_courses');
		$courses = $sql->result_array();
		foreach ($courses as $i => $course) {
			$cat = $this->get_course_category_by_id ($course['cat_id']);
			$courses[$i]['cat_title'] = $cat['title'];
			$created_by = $this->users_model->get_user($course['created_by']);
			$courses[$i]['created_by'] = $created_by['first_name'] . " " . $created_by['last_name'];
			$assigned_by = $this->users_model->get_user($course['assigned_by']);
			$courses[$i]['assigned_by'] = $assigned_by['first_name'] . " " . $assigned_by['last_name'];
		}
		return $courses;
	}
	public function get_course_category_by_id ($category_id=0) {
		$this->db->where('cat_id', $category_id);
		$sql = $this->db->get('coaching_course_category');
		return $sql->row_array();
	}

	public function get_course_by_id ($course_id=0) {
		$this->db->where('course_id', $course_id);
		$this->db->order_by ('created_on', 'DESC');
		$sql = $this->db->get('coaching_courses');
		return $sql->row_array();
	}

	public function get_course_cat_id($coaching_id, $course_id) {
		$this->db->select('cat_id');
		$this->db->where('course_id', $course_id);
		$this->db->where('coaching_id', $coaching_id);
		$sql = $this->db->get('coaching_courses');
		extract($sql->row_array());
		if($cat_id===null){
			$cat_id = 0;
		}
		return $cat_id;
	}

	public function add_course_category ($coaching_id, $category_id, $status = COURSE_STATUS_INACTIVE) {
		$data['title'] = $this->input->post('title');
		$data['status'] = $status;
		$member_id = $this->session->userdata('member_id');
		if ($category_id > 0) {
			$this->db->where('coaching_id', $coaching_id);
			$this->db->where('cat_id', $category_id);
			$this->db->update('coaching_course_category', $data);
		} else {
			$data['coaching_id'] = $coaching_id;
			$data['created_on'] = time();
			$data['created_by'] = $this->session->userdata('member_id');
			$this->db->insert('coaching_course_category', $data);
			$category_id = $this->db->insert_id();
		}
		return $category_id;
	}

	public function add_course($coaching_id, $category_id, $course_id, $feat_img, $status = COURSE_STATUS_INACTIVE) {
		$cat_id = $this->input->post('cat_id');
		if($category_id != $cat_id){
			$data['cat_id'] = ($cat_id!='null')?$cat_id:null;
			$category_id = $cat_id;
		}
		$data['title'] = $this->input->post('title');
		$data['description'] = $this->input->post('description');
		$data['curriculum'] = $this->input->post('curriculum');
		$data['price'] = $this->input->post('price');
		if($feat_img!==null){
			$data['feat_img'] = $feat_img;
		}
		if ($course_id > 0) {
			$this->db->where('course_id', $course_id);
			$this->db->where('coaching_id', $coaching_id);
			$this->db->update('coaching_courses', $data);
		} else {
			$data['status'] = $status;
			$data['enrolment_type'] = $this->input->post('enrolment_type');
			$data['coaching_id'] = $coaching_id;
			if ($category_id>0) {
				$data['cat_id'] = $category_id;
			}
			$data['created_on'] = time();
			$data['created_by'] = $this->session->userdata('member_id');
			$this->db->insert('coaching_courses', $data);
			$course_id = $this->db->insert_id ();
		}
		return $course_id;
	}
	
	public function publish($coaching_id, $course_id){
		$data['status'] = 1;
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where('course_id', $course_id);
		$this->db->update('coaching_courses', $data);
	}
	public function unpublish($coaching_id, $course_id){
		$data['status'] = 0;
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where('course_id', $course_id);
		$this->db->update('coaching_courses', $data);
	}

	public function delete_course_category($category_id){
		$this->db->set ('cat_id', 0);
		$this->db->where ('cat_id', $category_id);
		$this->db->update('coaching_courses');

		$this->db->where ('cat_id', $category_id);		
		$this->db->delete('coaching_course_category');
	}
	public function delete_course ($coaching_id, $course_id) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);	
		$this->db->delete('coaching_courses');
		
	}
	
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
	public function search_teachers_assigned ($coaching_id=0, $data=[]) {
		$search = $this->input->post ('search_text');
		$status = $this->input->post('filter_status');
		$this->db->select('member_id');
		$this->db->from('coaching_course_teachers');
		$this->db->where ('course_id', $data['course_id']);
		$sub_query = $this->db->get_compiled_select();
		
		if ( ! empty($search)) {
			$where = "(adm_no LIKE '%$search%' OR login LIKE '%$search%' OR first_name LIKE '%$search%' OR second_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR primary_contact LIKE '%$search%')";
			$this->db->where ($where);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('coaching_id', $coaching_id);
		if($status > -1){
			$this->db->where ('status', $status);
		}
		$this->db->where ('role_id', USER_ROLE_TEACHER);
		$this->db->where ("member_id IN ($sub_query)");
		$sql = $this->db->get ('members');
		return $sql->result_array();
	}
	public function get_teachers_not_assigned ($coaching_id=0, $course_id=0, $status=1) {
		$this->db->select('member_id');
		$this->db->from('coaching_course_teachers');
		$this->db->where ('course_id', $course_id);
		$sub_query = $this->db->get_compiled_select();
		
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('status', $status);
		$this->db->where ('role_id', USER_ROLE_TEACHER);
		$this->db->where ("member_id NOT IN ($sub_query)");
		$sql = $this->db->get ('members');
		return $sql->result_array();
	}
	public function search_teachers_not_assigned ($coaching_id=0, $data=[]) {
		$search = $this->input->post ('search_text');
		$status = $this->input->post('filter_status');
		$this->db->select('member_id');
		$this->db->from('coaching_course_teachers');
		$this->db->where ('course_id', $data['course_id']);
		$sub_query = $this->db->get_compiled_select();
		
		if ( ! empty($search)) {
			$where = "(adm_no LIKE '%$search%' OR login LIKE '%$search%' OR first_name LIKE '%$search%' OR second_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR primary_contact LIKE '%$search%')";
			$this->db->where ($where);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('coaching_id', $coaching_id);
		if($status > -1){
			$this->db->where ('status', $status);
		}
		$this->db->where ('role_id', USER_ROLE_TEACHER);
		$this->db->where ("member_id NOT IN ($sub_query)");
		$sql = $this->db->get ('members');
		return $sql->result_array();
	}
	public function add_teachers_assignment($coaching_id=0, $course_id=0){
		$users = $this->input->post('users');
		$add_count = 0;
		$user_count = count($users);
		foreach ($users as $i => $member_id) {
			if($this->add_teacher_assignment($coaching_id, $course_id, $member_id)){
				$add_count += 1;
			}
		}
		if($add_count===$user_count){
			$returnValue = true;
		} else {
			$returnValue = false;
		}
		return $returnValue;
	}
	public function add_teacher_assignment($coaching_id=0, $course_id=0, $member_id=0, $status=1){
		$data['course_id'] = $course_id;
		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['status'] = $status;
		$data['created_on'] = time();
		$data['created_by'] = $this->session->userdata('member_id');
		$this->db->insert('coaching_course_teachers', $data);
		if ($this->db->affected_rows() > 0) {
			$returnValue = true;
		} else {
			$returnValue = false;
		}
		return $returnValue;
	}

	public function remove_teachers_assignment($coaching_id=0, $course_id=0){
		$users = $this->input->post('users');
		$remove_count = 0;
		$users_count = count($users);
		foreach ($users as $i => $member_id) {
			if($this->remove_teacher_assignment($coaching_id, $course_id, $member_id)){
				$remove_count += 1;
			}
		}
		if($users_count===$remove_count){
			$returnValue = true;
		} else {
			$returnValue = false;
		}
		return $returnValue;
	}

	public function remove_teacher_assignment($coaching_id=0, $course_id=0, $member_id=0){
		$this->db->where ('course_id', $course_id);
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('member_id', $member_id);
		$this->db->delete('coaching_course_teachers');	
		if ($this->db->affected_rows() > 0) {
			$returnValue = true;
		} else {
			$returnValue = false;
		}
		return $returnValue;
	}

	public function get_course_lessons ($coaching_id=0, $course_id=0, $batch_id=0) {

		$prefix = $this->db->dbprefix;
		$select = 'SELECT resource_id FROM '.$prefix.'coaching_course_contents WHERE resource_type='.COURSE_CONTENT_CHAPTER.' AND coaching_id='.$coaching_id.' AND course_id='.$course_id;

		$this->db->select ('CL.*');
		$this->db->from ('coaching_course_lessons CL');
		$this->db->where ('CL.coaching_id', $coaching_id);
		$this->db->where ('CL.course_id', $course_id);
		$this->db->where ('CL.lesson_id NOT IN ('.$select.')');
		$sql = $this->db->get ();
		return $sql->result_array ();
	}

	public function save_order ($coaching_id=0, $course_id=0, $batch_id=0, $raw_data=[]) {
		

		$member_id = $this->session->userdata ('member_id');
		$time = time ();

		if (! empty ($raw_data)) {
			foreach ($raw_data as $num=>$row) {
				$position = $num + 1;
				list($resource_type, $resource_id) = explode ('-', $row);

				// If record exist 
				$this->db->select ('id');
				$this->db->where ('coaching_id', $coaching_id);
				$this->db->where ('course_id', $course_id);
				$this->db->where ('batch_id', $batch_id);
				$this->db->where ('resource_type', $resource_type);
				$this->db->where ('resource_id', $resource_id);
				$sql = $this->db->get ('coaching_course_contents');
				if ($sql->num_rows () > 0) {
					$rec = $sql->row_array ();
					$id = $rec['id'];
					$this->db->set ('position', $position);
					$this->db->where ('id', $id);
					$sql = $this->db->update ('coaching_course_contents');					
				} else {
					$data = [];
					$data['coaching_id'] 	= $coaching_id;
					$data['course_id'] 		= $course_id;
					$data['batch_id'] 		= $batch_id;
					$data['resource_type'] 	= $resource_type;
					$data['resource_id'] 	= $resource_id;
					$data['position'] 		= $position;
					$data['for_demo'] 		= 0;
					$data['created_on'] 	= $time;
					$data['created_by'] 	= $member_id;
					$sql = $this->db->insert ('coaching_course_contents', $data);
				}
			}
		}
	}


	public function get_course_content ($coaching_id=0, $course_id=0) {

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
 					$pages = $this->lessons_model->get_all_pages ($coaching_id, $course_id, $row['resource_id']);
 					$lesson['resource_type'] = $row['resource_type'];
 					$lesson['resource_id'] = $row['resource_id']; 
 					$lesson['pages'] = $pages;
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
				$pages = $this->lessons_model->get_all_pages ($coaching_id, $course_id, $row['lesson_id']);
 				$row['pages'] = $pages;
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
	
	public function duplicate_course ($coaching_id=0, $course_id=0) {
		// Get course
		$course = $this->get_course_by_id ($course_id);

		//--> Copy course data
		$c_data = $course;
		$c_data['course_id'] = NULL;
		$c_data['title'] = $course['title'] . ' - Duplicate';
		$c_data['status'] = 0;
		$c_data['created_on'] = time ();
		$c_data['created_by'] = $this->session->userdata ('member_id');
		$this->db->insert ('coaching_courses', $c_data);
		$new_course_id = $this->db->insert_id ();

		// 1. COPY LESSONS
		// Get all lessons in this course
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_course_lessons');
		if ($sql->num_rows () > 0) {
			$lessons = $sql->result_array ();
			foreach ($lessons as $lesson) {
				//--> Insert Lessons -------------------
				$c_data = $lesson;
				$c_data['lesson_id'] = NULL;
				$c_data['course_id'] = $new_course_id;
				$c_data['created_on'] = time ();
				$c_data['created_by'] = $this->session->userdata ('member_id');
				$this->db->insert ('coaching_course_lessons', $c_data);
				$new_lesson_id = $this->db->insert_id();

				$ref_id = $this->lessons_model->generate_reference_id ($coaching_id, $new_course_id, $new_lesson_id);
				$this->db->set ('lesson_key', $ref_id);
				$this->db->where ('coaching_id', $coaching_id);
				$this->db->where ('course_id', $new_course_id);
				$this->db->where ('lesson_id', $new_lesson_id);
				$this->db->update ('coaching_course_lessons');
				//--> Insert Lessons ---------------------

				// Get all pages in this lesson
				$this->db->where ('coaching_id', $coaching_id);
				$this->db->where ('course_id', $course_id);
				$this->db->where ('lesson_id', $lesson['lesson_id']);
				$l_sql = $this->db->get ('coaching_course_lesson_pages');
				if ($l_sql->num_rows () > 0) {
					$pages = $l_sql->result_array ();
					foreach ($pages as $page) {
						//--> Insert Page -----------------------------
						$c_data = $page;
						$c_data['page_id'] = NULL;
						$c_data['course_id'] = $new_course_id;
						$c_data['lesson_id'] = $new_lesson_id;
						$c_data['created_on'] = time ();
						$c_data['created_by'] = $this->session->userdata ('member_id');
						$this->db->insert ('coaching_course_lesson_pages', $c_data);
						$new_page_id = $this->db->insert_id ();
						//--> Insert Page -----------------------------

						// Get all attachments in this page
						$this->db->where ('coaching_id', $coaching_id);
						$this->db->where ('course_id', $course_id);
						$this->db->where ('lesson_id', $lesson['lesson_id']);
						$this->db->where ('page_id', $page['page_id']);
						$a_sql = $this->db->get ('coaching_course_lesson_attachments');
						if ($a_sql->num_rows () > 0) {
							$att = $a_sql->result_array ();
							foreach ($att as $at) {
								//--> Insert Attachment -----------------------------
								$c_data = $at;
								$c_data['att_id'] = NULL;
								$c_data['course_id'] = $new_course_id;
								$c_data['lesson_id'] = $new_lesson_id;
								$c_data['page_id'] = $new_page_id;
								$c_data['created_on'] = time ();
								$c_data['created_by'] = $this->session->userdata ('member_id');
								$this->db->insert ('coaching_course_lesson_attachments', $c_data);
								//--> Insert Page -----------------------------

							}
						}


					}
				}


			}
		}

		// 2. COPY TESTS
		// Get all tests in this course
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$sql = $this->db->get ('coaching_tests');
		if ($sql->num_rows () > 0) {
			$tests = $sql->result_array ();
			foreach ($tests as $test) {
				//--> Insert Tests ---------------------
				$c_data = $test;
				$c_data['test_id'] = NULL;
				$c_data['course_id'] = $new_course_id;
				$c_data['created_by'] = $this->session->userdata ('member_id');
				$c_data['creation_date'] = time ();
				$this->db->insert ('coaching_tests', $c_data);
				$new_test_id = $this->db->insert_id ();

				$ref_id = $this->tests_model->generate_reference_id ($coaching_id, $new_course_id, $new_test_id);
				$this->db->set ('unique_test_id', $ref_id);
				$this->db->where ('coaching_id', $coaching_id);
				$this->db->where ('course_id', $new_course_id);
				$this->db->where ('test_id', $new_test_id);
				$this->db->update ('coaching_tests');
				//--> Insert Tests ---------------------

				// Get test questions
				$this->db->select ('CQ.*');
				$this->db->from ('coaching_test_questions TQ');
				$this->db->join ('coaching_questions CQ', 'TQ.question_id=CQ.question_id');
				$this->db->where ('TQ.coaching_id', $coaching_id);
				$this->db->where ('TQ.test_id', $test['test_id']);
				$sql_q = $this->db->get ();
				if ($sql_q->num_rows () > 0) {
					$questions = $sql_q->result_array ();
					foreach ($questions as $q) {
						//--> Insert question
						$c_data = $q;
						$c_data['question_id'] = NULL;
						$c_data['creation_date'] = time ();
						$c_data['created_by'] = $this->session->userdata ('member_id');
						$this->db->insert ('coaching_questions', $c_data);
						$new_question_id = $this->db->insert_id ();
						//--> Insert question

						// Add to test
						$a_data = [];
						$a_data['coaching_id'] = $coaching_id;
						$a_data['test_id'] = $new_test_id;
						$a_data['question_id'] = $new_question_id;
						$this->db->insert ('coaching_test_questions', $a_data);
					}
				}
			}
		}


		return $new_course_id;

	}


	public function get_enrolment_requests ($coaching_id=0) {
		$this->db->select ('ER.*, U.first_name, U.last_name, U.primary_contact, C.title');
		$this->db->from ('coaching_course_enrol_request ER');
		$this->db->join ('members U', 'U.member_id=ER.member_id');
		$this->db->join ('coaching_courses C', 'C.course_id=ER.course_id');
		//$this->db->where ('U.member_id', 'ER.member_id');
		//$this->db->where ('C.course_id', 'ER.course_id');
		$this->db->where ('ER.coaching_id', $coaching_id);
		$this->db->where ('ER.status', 0);
		$sql = $this->db->get ();
		if ($sql->num_rows () > 0) {
			$result = $sql->result_array ();
		} else {
			$result = false;
		}
		return $result;

	}
}