<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courses extends MX_Controller {

	public function __construct() {
		// Load Config and Model files required throughout Users sub-module
		$config = ['config_coaching', 'config_course', 'config_virtual_class'];
		$models = ['coaching_model', 'courses_model', 'lessons_model', 'users_model', 'tests_model'];
		$this->common_model->autoload_resources($config, $models);
	}

	public function index ($coaching_id=0, $cat_id='-1') {
		
		$data['page_title'] = 'Courses';
		$is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		if($is_admin):
			$data['bc'] = array ('Dashboard'=>'coaching/home/dashboard/'.$coaching_id);
		else:
			$data['bc'] = array ('Dashboard'=>'coaching/home/teacher/'.$coaching_id);
		endif;
		$data['cat_id'] = $cat_id;
		$data['coaching_id'] = $coaching_id;
		$data['categories'] = $this->courses_model->course_categories($coaching_id);
		$data['courses_uncategorized'] = $this->courses_model->courses_uncategorized($coaching_id);

		$data['count_un'] = $this->courses_model->count_un_courses ($coaching_id);
		$data['count_all'] = $this->courses_model->count_all_courses ($coaching_id);
		if ($is_admin) {
			$data['courses'] = $this->courses_model->courses ($coaching_id, $cat_id);
			$data['toolbar_buttons'] = array(
				'<i class="fa fa-plus-circle"></i> New Course' => 'coaching/courses/create/' . $coaching_id . '/' . $cat_id,
				'<i class="fa fa-plus-circle"></i> New Category' => 'coaching/courses/create_category/' . $coaching_id,
			);
		} else {
			$data['courses'] = $this->courses_model->member_courses ($coaching_id, $cat_id);
		}
		$data['is_admin'] = $is_admin;
		$data['script'] = $this->load->view('courses/scripts/index', $data, true);
		if ($is_admin) {
 			$data['right_sidebar'] = $this->load->view('courses/inc/index', $data, true);
		}
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/index', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}


	public function batch_courses ($coaching_id=0, $cat_id='-1') {
		$data['page_title'] = 'Batch Courses';
		$is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		$data['bc'] = array('Dashboard' => 'coaching/home/teacher/' . $coaching_id);
		$data['cat_id'] = $cat_id;
		$data['coaching_id'] = $coaching_id;
		$data['categories'] = $this->courses_model->course_categories($coaching_id);
		$data['courses_uncategorized'] = $this->courses_model->courses_uncategorized($coaching_id);
		if ($is_admin) {
			redirect("coaching/courses/index/".$coaching_id.'/'.$cat_id);
		} else {
			$data['courses'] = $this->courses_model->member_courses_by_type (COURSE_ENROLMENT_BATCH, $coaching_id);
			$data['num_courses'] = count($data['courses']);
		}
		$data['is_admin'] = $is_admin;
		$data['script'] = $this->load->view('courses/scripts/index', $data, true);
		$data['right_sidebar'] = $this->load->view('courses/inc/index', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/index', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function direct_courses ($coaching_id=0, $cat_id='-1') {
		$data['page_title'] = 'Direct Courses';
		$is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		$data['bc'] = array('Dashboard' => 'coaching/home/teacher/' . $coaching_id);
		$data['cat_id'] = $cat_id;
		$data['coaching_id'] = $coaching_id;
		$data['categories'] = $this->courses_model->course_categories($coaching_id);
		$data['courses_uncategorized'] = $this->courses_model->courses_uncategorized($coaching_id);
		if ($is_admin) {
			redirect("coaching/courses/index/".$coaching_id.'/'.$cat_id); 
		} else {
			$data['courses'] = $this->courses_model->member_courses_by_type (COURSE_ENROLMENT_DIRECT, $coaching_id);
			$data['num_courses'] = count($data['courses']);
		}
		$data['is_admin'] = $is_admin;
		$data['script'] = $this->load->view('courses/scripts/index', $data, true);
		$data['right_sidebar'] = $this->load->view('courses/inc/index', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/index', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function edit($coaching_id = 0, $cat_id = -1, $course_id = 0) {
		$this->create($coaching_id, $cat_id, $course_id);
	}

	public function create ($coaching_id = 0, $cat_id = -1, $course_id = 0) {
		$data['page_title'] = 'Course';
		$data['sub_title'] = ($this->router->fetch_method() == "create") ? 'Create New Course' : 'Edit Course';
		$data['submit_label'] = ($this->router->fetch_method() == "create") ? 'Create' : 'Update';
		$data['submit_title'] = ($this->router->fetch_method() == "create") ? 'Create New Course' : 'Update This Course';
		$data['cat_id'] = $cat_id;
		$data['coaching_id'] = $coaching_id;
		$data['course_id'] = $course_id;
		$data['categories'] = $this->courses_model->course_categories($coaching_id);
		if ($course_id > 0) {
			$data['course'] = $this->courses_model->get_course_by_id ($course_id);
		}
		$data['is_admin'] = $is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		// $data['script'] = $this->load->view ('courses/scripts/create', $data, true);
		if (!$is_admin) {
			redirect("coaching/courses/index/".$coaching_id); 
		}
		$data['bc'] = ($this->router->fetch_method() == "create") ? array('Courses' => 'coaching/courses/index/' . $coaching_id) : array('Manage Course' => 'coaching/courses/manage/'.$coaching_id.'/'.$course_id);

		if ($course_id > 0) {
			$data['right_sidebar'] = $this->load->view ('courses/inc/manage_course', $data, true);
		}		
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/create', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function edit_category($coaching_id = 0, $cat_id = 0) {
		$this->create_category($coaching_id, $cat_id);
	}

	public function create_category ($coaching_id = 0, $cat_id = 0) {
		$data['page_title'] = 'Create Category';
		$data['sub_title'] = ($this->router->fetch_method() == "create_category") ? 'Create New Course Category' : 'Edit Course Category';
		$data['submit_label'] = ($this->router->fetch_method() == "create_category") ? 'Create' : 'Update';
		$data['submit_title'] = ($this->router->fetch_method() == "create_category") ? 'Create New Category' : 'Update This Category';
		if ($cat_id > 0) {
			$data['category'] = $this->courses_model->get_course_category_by_id($cat_id);
		}
		$data['cat_id'] = $cat_id;
		$data['coaching_id'] = $coaching_id;
		$data['is_admin'] = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/create_cat', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function manage ($coaching_id=0, $course_id=0) {
		$data['page_title'] = 'Manage Course';

		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);
		if (! $course) {
			$this->message->set ('Course not found', 'danger', true);
			redirect ('coaching/courses/index/'.$coaching_id);			
		}
		$data['num_lessons'] = $this->courses_model->count_course_lessons ($coaching_id, $course_id);
		$data['num_tests'] = $this->courses_model->count_course_tests ($coaching_id, $course_id); 
		$data['teachers'] = $this->courses_model->get_teachers_assigned ($coaching_id, $course_id);
		$data['num_teachers'] = count ($data['teachers']);

		$data['cat_id'] = $this->courses_model->get_course_cat_id ($coaching_id, $course_id);
		$data['coaching_id'] = $coaching_id;
		$data['course_id'] = $course_id;
		$data['is_admin'] = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		$data['bc'] = array('Courses' => 'coaching/courses/index/'.$coaching_id);

		$data['right_sidebar'] = $this->load->view ('courses/inc/manage_course', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/manage', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function preview ($coaching_id=0, $course_id=0, $lesson_id=0, $page_id=0) {
		$data['page_title'] = 'Preview';
		$data['coaching_id'] = $coaching_id;
		$data['course_id'] = $course_id;
		$data['lesson_id'] = $lesson_id;
		$data['page_id'] = $page_id;

		$data['course'] = $this->courses_model->get_course_by_id ($course_id);
		$data['contents'] = $this->courses_model->get_course_content ($coaching_id, $course_id);	

		if ($lesson_id > 0) {
			$data['lesson'] = $this->lessons_model->get_lesson ($coaching_id, $course_id, $lesson_id);
		} else {
			$data['lesson'] = false;
		}

		if ($page_id > 0) {
			$data['page'] = $this->lessons_model->get_page ($coaching_id, $course_id, $lesson_id, $page_id);
			$data['attachments'] = $this->lessons_model->get_attachments ($coaching_id, $course_id, $lesson_id, $page_id);
		} else {
			$data['page'] = false;
			$data['attachments'] = false;
		}
		$data['is_admin'] = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));

		/* --==// Back //==-- */
		$data['bc'] = ['Manage'=>'coaching/courses/manage/'.$coaching_id.'/'.$course_id];
		$data['script'] = $this->load->view ('courses/scripts/preview', $data, true);
		$data['right_sidebar'] = $this->load->view ('courses/inc/course_preview', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view("courses/preview", $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function teachers ($coaching_id=0, $course_id=0, $type=TEACHERS_ASSIGNED, $status=1) {

		$data['page_title'] 	= 'Course Teachers';
		$data['coaching_id'] 	= $coaching_id;
		$data['course_id']		= $course_id;
		$data['type'] 			= $type;
		$data['status'] 		= $status;

		$data['bc'] = array ('Manage '=>'coaching/courses/manage/'.$coaching_id.'/'.$course_id);
		
		$data['course'] = $this->courses_model->get_course_by_id ($course_id);

		$teachers_assigned 		= $this->courses_model->get_teachers_assigned ($coaching_id, $course_id, $status);
		// Count enroled users
		if ($teachers_assigned > 0) 
			$num_assigned = count ($teachers_assigned);
		else 
			$num_assigned = 0;
		
		$teachers_not_assigned 		= $this->courses_model->get_teachers_not_assigned ($coaching_id, $course_id, $status);
		// Count enroled users
		if ($teachers_not_assigned > 0) 
			$num_not_assigned = count ($teachers_not_assigned);
		else 
			$num_not_assigned = 0;

		$data['num_assigned']  = $num_assigned;
		$data['num_not_assigned']  = $num_not_assigned;

		if ($type == TEACHERS_ASSIGNED) {
			$results = $teachers_assigned;
		} else if ($type == TEACHERS_NOT_ASSIGNED) {
			$results = $teachers_not_assigned;
		}
		$data['results'] = $results;
		$data['data']			= $data;
		$is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		$data['is_admin'] = $is_admin;
		$separator_tabs = array(
			array(
				'title'=> 'Teachers Assigned',
				'href'=> site_url ('coaching/courses/teachers/'.$coaching_id.'/'.$course_id.'/'.TEACHERS_ASSIGNED),
				'count'=> $num_assigned,
				'if_admin'=>false,
				'active'=> ($type==TEACHERS_ASSIGNED)? ' active' : null
			),
			array(
				'title'=> 'Teachers Not Assigned',
				'href'=> site_url ('coaching/courses/teachers/'.$coaching_id.'/'.$course_id.'/'.TEACHERS_NOT_ASSIGNED),
				'count'=> $num_not_assigned,
				'if_admin'=>false,
				'active'=> ($type==TEACHERS_NOT_ASSIGNED)? ' active' : null
			)
		);
		$data['separator_tabs'] = $separator_tabs;
		$data['script'] = $this->load->view ('courses/scripts/teachers', $data, true);
		$data['filter_block']  = $this->load->view ('courses/inc/teachers_filters', $data, true);
		$data['right_sidebar'] = $this->load->view ('courses/inc/manage_course', $data, true);
		
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/teachers', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function organize ($coaching_id=0, $course_id=0, $batch_id=0) {

		$data['page_title'] 	= 'Organize Content';
		$data['coaching_id'] 	= $coaching_id;
		$data['course_id'] 		= $course_id;
		$data['batch_id'] 		= $batch_id;	

		$status = 1;

		$data['contents'] = $this->courses_model->get_course_content ($coaching_id, $course_id);
		
		$data['course'] = $this->courses_model->get_course_by_id ($course_id);
		$data['is_admin'] = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));

		/* --==// Back //==-- */
		$data['bc'] = ['Manage'=>'coaching/courses/manage/'.$coaching_id.'/'.$course_id];

		$data['style'] = '<link rel="stylesheet" href="'.base_url(THEME_PATH . 'assets/css/vendor/sortable.css').'" />'; 
		$data['script'] = $this->load->view ('courses/scripts/sortable', $data, true);
		$data['right_sidebar'] = $this->load->view ('courses/inc/manage_course', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/organize', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function settings ($coaching_id=0, $course_id=0) {
		$data['is_admin'] = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		$data['script'] = $this->load->view ('courses/scripts/teachers', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/teachers', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function enrolment_requests ($coaching_id=0) {
		$data['page_title'] 	= 'Enrolment Requests';
		$data['coaching_id'] 	= $coaching_id;

		/* --==// Back //==-- */
		$data['bc'] = ['Manage'=>'coaching/courses/manage/'.$coaching_id];

		$data['enrol_requests'] = $this->courses_model->get_enrolment_requests ($coaching_id);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/enrolment_requests', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);

	}
}