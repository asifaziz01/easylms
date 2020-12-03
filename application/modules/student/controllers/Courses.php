<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Courses extends MX_Controller {
	
	public function __construct() {
		// Load Config and Model files required throughout Users sub-module
		$config = ['config_student', 'config_course'];
		$models = ['courses_model', 'lessons_model', 'enrolment_model', 'virtual_class_model', 'tests_model'];
		$this->common_model->autoload_resources($config, $models);
	}
	
	public function index ($coaching_id=0, $member_id=0, $cat_id='-1') {

		$data['page_title'] = 'Course Catalog';
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0) {
            $member_id = $this->session->userdata ('member_id');
        }
        $data['toolbar_buttons'] = [
			'<i class="fa fa-book"></i> My Courses' => 'student/courses/my_courses/'.$coaching_id.'/'.$member_id,
		];

		$data['bc'] = array ('Dashboard' => 'student/home/dashboard/' . $coaching_id);
		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['cat_id'] = $cat_id;
		$data['categories'] = $this->courses_model->get_categories ();
		$data['courses'] = $this->courses_model->get_courses ($cat_id);
		$data['data'] = $data;

		$data['script'] = $this->load->view('courses/scripts/index', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/index', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	

	public function my_courses ($coaching_id = 0, $member_id=0, $cat_id='-1') {
		$data['page_title'] = 'My Courses';
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0){
            $member_id = $this->session->userdata ('member_id');
        }
       
		$data['bc'] = array('All Courses' => 'student/courses/index/' . $coaching_id . '/' . $member_id);
		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['member_id'] = $member_id;
		$data['batch_id'] = 0;
		$data['cat_id'] = $cat_id;
		$data['courses'] = $this->courses_model->my_courses ($coaching_id, $member_id);
		$data['script'] = $this->load->view('courses/scripts/my_courses', $data, true);

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/my_courses', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function enrol_in_course ($coaching_id = 0, $course_id=0, $member_id=0) {
		
		$data['page_title'] = 'Enrol In Courses';
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0){
            $member_id = $this->session->userdata ('member_id');
        }
       
		$data['bc'] = array('All Courses' => 'student/courses/index/' . $coaching_id . '/' . $member_id);
		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['batch_id'] = 0;
		//$data['courses'] = $this->courses_model->my_courses ($coaching_id, $member_id);
		//$data['script'] = $this->load->view('courses/scripts/my_courses', $data, true);

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/enrol_in_course', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function view ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0) {
            $member_id = $this->session->userdata ('member_id');
        }

		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);
		if (! $course) {
		    $this->message->set ('Course not found. This course has been either been un-published or deleted', 'danger', true);
		    redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		}
		$data['lessons'] = $this->lessons_model->get_lessons ($coaching_id, $course_id);
		$data['tests'] = $this->courses_model->get_course_tests ($coaching_id, $course_id, TEST_TYPE_PRACTICE);
		$data['batches'] = $this->enrolment_model->get_batches ($coaching_id, $course_id);
		$data['teachers'] = $this->courses_model->get_teachers_assigned ($coaching_id, $course_id);
		$data['enroled'] = $this->courses_model->is_enroled ($coaching_id, $course_id, $batch_id, $member_id);
		$data['today']	= mktime (0, 0, 0, date('m'), date('d'), date('Y'));
		$data['bc'] = ['Course Catalog'=>'student/courses/index/'.$coaching_id.'/'.$member_id];

		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['page_title'] = $data['course']['title'];

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view("courses/view", $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function home ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {

		if ($this->courses_model->is_enroled ($coaching_id, $course_id, $batch_id, $member_id) == false) {
			$this->message->set ('You are not enroled in this course', 'danger', true);
			redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		} else {
		}
		
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }

        if ($member_id==0){
            $member_id = $this->session->userdata ('member_id');
        }


		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);

		if (! $course) {
		    $this->message->set ('Course not found. This course has either been un-published or deleted', 'danger', true);
		    redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		}
		$data['lessons'] = $this->lessons_model->get_lessons ($coaching_id, $course_id);
		$data['tests'] = $this->courses_model->get_course_tests ($coaching_id, $course_id, TEST_TYPE_PRACTICE);
		$data['teachers'] = $this->courses_model->get_teachers_assigned ($coaching_id, $course_id);
		$data['batch'] = $batch = $this->enrolment_model->get_batch ($coaching_id, $course_id, $batch_id);
		$data['classrooms'] = $this->virtual_class_model->my_classroom ($coaching_id, $member_id, $course_id, $batch_id);
		$data['cp'] = $this->enrolment_model->get_course_progress ($coaching_id, $member_id, $course_id);
		$data['last_activity'] = $this->courses_model->get_last_activity ($coaching_id, $member_id, $course_id);	

		$data['bc'] = ['My Courses'=>'student/courses/my_courses/'.$coaching_id.'/'.$member_id];
		
		$data['page_title'] 	= $course['title'];
		$data['coaching_id'] 	= $coaching_id;
		$data['member_id'] 		= $member_id;
		$data['course_id'] 		= $course_id;
		$data['batch_id'] 		= $batch_id;
		
		$data['right_sidebar'] = $this->load->view ('courses/inc/home', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view("courses/home", $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	public function details ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0) {
            $member_id = $this->session->userdata ('member_id');
        }

		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);
		if (! $course) {
		    $this->message->set ('Course not found. This course has been either been un-published or deleted', 'danger', true);
		    redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		}
		$data['batch'] = $batch = $this->enrolment_model->get_batch ($coaching_id, $course_id, $batch_id);

		$data['bc'] = ['My Courses'=>'student/courses/home/'.$coaching_id.'/'.$member_id.'/'.$course_id];

		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['page_title'] = $data['course']['title'];

		$data['right_sidebar'] = $this->load->view ('courses/inc/home', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view("courses/details", $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	
	public function schedule ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0, $start=0) {
		
		$data['page_title'] 	= 'Schedule';
		$data['coaching_id'] 	= $coaching_id;
		$data['member_id'] 		= $member_id;
		$data['course_id'] 		= $course_id;
		$data['batch_id'] 		= $batch_id;

		$data["bc"] = array ( 'Course Home'=>'student/courses/home/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id);
		$is_admin = USER_ROLE_COACHING_ADMIN === intval($this->session->userdata('role_id'));
		$data['is_admin'] = $is_admin;
		
		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);
		if (! $course) {
		    $this->message->set ('Course not found. This course has been either been un-published or deleted', 'danger', true);
		    redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		}
		$data['batch'] = $batch = $this->enrolment_model->get_batch ($coaching_id, $course_id, $batch_id);
		//$data['classrooms'] = $this->coaching_model->get_classrooms ($coaching_id);
		$data['instructors'] = $this->enrolment_model->get_course_instructors ($coaching_id, $course_id);

		$schedule = [];
		$interval = 24 * 60 * 60; 		// 1 day in seconds
		$now = time ();
		if ($start == 0) {
			$start_date = $batch['start_date'];
		} else {
			$start_date = $start;
		}

		if ($batch['end_date'] > 0) {
			$end_date = $batch['end_date'];
		} else {
			$end_date = $now + (365 * $interval); 	// 1 year from today
		}

		$count = 0;
		for ($i=$start_date; $i<=$end_date; $i=$i+$interval) {
			$d = [];
			// get data for this date
			if ($scd = $this->enrolment_model->get_schedule_data ($coaching_id, $course_id, $batch_id, $i)) {
				$schedule[$i]['scd'] = $scd;
			}
			if ($vc = $this->virtual_class_model->get_all_classes ($coaching_id, $course_id, $batch_id, $i)) {
				$schedule[$i]['vc'] = $vc;
			}
			$count++;
			if ($count >= 7) {
				break;
			}
		}
		

		$data['start_date'] = $start_date;
		$data['end_date'] 	= $end_date;
		$data['interval'] 	= $interval;
		$data['schedule'] 	= $schedule;

		$data['course'] = $this->courses_model->get_course_by_id ($course_id);
		$data['class'] = false;

		$data['right_sidebar'] = $this->load->view ('courses/inc/home', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view('courses/schedule', $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}

	
	public function learn ($coaching_id=0, $member_id=0, $batch_id=0, $course_id=0, $lesson_id=0, $page_id=0) {
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0) {
            $member_id = $this->session->userdata ('member_id');
        }

		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);
		if (! $course) {
		    $this->message->set ('Course not found. This course has been either been un-published or deleted', 'danger', true);
		    redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		}
		$data['contents'] = $this->courses_model->get_course_content ($coaching_id, $course_id, $member_id);
		$data['cp'] = $this->enrolment_model->get_course_progress ($coaching_id, $member_id, $course_id);

		$data['bc'] = ['Course Home'=>'student/courses/home/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id];

		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['course_id'] = $course_id;
		$data['lesson_id'] = $lesson_id;
		$data['page_id']   = $page_id;
		$data['batch_id'] = $batch_id;
		//$data['page_title'] = $data['course']['title'];

		$data['right_sidebar'] = $this->load->view ('courses/inc/learn', $data, true);
		$data['script'] = $this->load->view('courses/scripts/content', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		if ($page_id > 0) {
			$data['lesson'] = $this->lessons_model->get_lesson ($coaching_id, $course_id, $lesson_id);
			$data['page'] = $this->lessons_model->get_page ($coaching_id, $course_id, $lesson_id, $page_id);
			$data['attachments'] = $this->lessons_model->get_attachments ($coaching_id, $course_id, $lesson_id, $page_id);
			$this->lessons_model->mark_progress ($coaching_id, $member_id, $course_id, $lesson_id, $page_id);
			$this->load->view("courses/page", $data);
		} else if ($lesson_id > 0) {
			$data['lesson'] = $this->lessons_model->get_lesson ($coaching_id, $course_id, $lesson_id);
			$data['pages'] = $this->lessons_model->get_all_pages ($coaching_id, $course_id, $lesson_id);
			$this->load->view("courses/lesson", $data);
		} else {
			$this->load->view("courses/learn", $data);
		}
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}


	public function try_course ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0, $lesson_id=0, $page_id=0) {
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0) {
            $member_id = $this->session->userdata ('member_id');
        }
        $demo_only = 1;
		$data['course'] = $course = $this->courses_model->get_course_by_id ($course_id);
		if (! $course) {
		    $this->message->set ('Course not found. This course has been either been un-published or deleted', 'danger', true);
		    redirect ('student/courses/my_courses/'.$coaching_id.'/'.$member_id);
		}
		$data['contents'] = $this->courses_model->get_course_content ($coaching_id, $course_id, $member_id);

		if ($page_id > 0) {
			$data['bc'] = ['Course Home'=>'student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$batch_id.'/'.$lesson_id];
		} else if ($lesson_id > 0) {
			$data['bc'] = ['Course Home'=>'student/courses/try_course/'.$coaching_id.'/'.$member_id.'/'.$course_id];
		} else {
			$data['bc'] = ['Course Home'=>'student/courses/view/'.$coaching_id.'/'.$member_id.'/'.$course_id];
		}

		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['course_id'] = $course_id;
		$data['lesson_id'] = $lesson_id;
		$data['page_id']   = $page_id;
		$data['batch_id'] = $batch_id;
		//$data['page_title'] = $data['course']['title'];

		$data['right_sidebar'] = $this->load->view ('courses/inc/try_course', $data, true);
		$this->load->view(INCLUDE_PATH . 'header', $data);
		if ($page_id > 0) {
			$data['lesson'] = $this->lessons_model->get_lesson ($coaching_id, $course_id, $lesson_id);
			$data['page'] = $this->lessons_model->get_page ($coaching_id, $course_id, $lesson_id, $page_id);
			$data['attachments'] = $this->lessons_model->get_attachments ($coaching_id, $course_id, $lesson_id, $page_id);
		} else if ($lesson_id > 0) {
			$data['lesson'] = $this->lessons_model->get_lesson ($coaching_id, $course_id, $lesson_id);
			$data['pages'] = $this->lessons_model->get_all_pages ($coaching_id, $course_id, $lesson_id);
		}
		$this->load->view("courses/try_course", $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}



	/*
	public function continue ($coaching_id=0, $member_id=0, $course_id=0){
		extract($this->courses_model->continue_course($coaching_id, $member_id, $course_id));
		redirect("student/courses/content/".$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$lesson_id.'/'.$page_id);
	}
	public function begin ($coaching_id=0, $member_id=0, $course_id=0){
		extract($this->courses_model->begin_course($coaching_id, $course_id));
		redirect("student/courses/content/".$coaching_id.'/'.$member_id.'/'.$course_id.'/'.$lesson_id);
	}

	public function content ($coaching_id=0, $member_id=0, $course_id=0, $lesson_id=0, $page_id=0) {
		if ($coaching_id==0) {
            $coaching_id = $this->session->userdata ('coaching_id');
        }
        if ($member_id==0){
            $member_id = $this->session->userdata ('member_id');
        }
		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['course_id'] = $course_id;
		$data['lesson_id'] = $lesson_id;
		$data['page_id'] = $page_id;
		if($page_id>0){
			$this->lessons_model->make_progress($member_id, $coaching_id, $course_id, $lesson_id, $page_id);
		}
		$data['full_progress'] = $this->lessons_model->get_full_progress($member_id, $coaching_id, $course_id);
		$data['progress'] = $this->lessons_model->get_progress($member_id, $coaching_id, $course_id);

		$data['course'] = $this->courses_model->get_course_by_id($course_id);
		$data['page_title'] = $data['course']['title'];
		$data['lessons'] = $this->lessons_model->get_lessons ($coaching_id, $course_id);
		$data['tests'] = $this->courses_model->get_course_tests ($coaching_id, $course_id, TEST_TYPE_PRACTICE);
		$data['teachers'] = $this->courses_model->get_teachers_assigned ($coaching_id, $course_id);
	
		if ($lesson_id > 0) {
			$data['lesson'] = $this->lessons_model->get_lesson ($coaching_id, $course_id, $lesson_id);
			$data['pages'] = $this->lessons_model->get_all_pages ($coaching_id, $course_id, $lesson_id);
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
		$data['bc'] = ['Course'=>'student/courses/view/'.$coaching_id.'/'.$member_id.'/'.$course_id];
		$data['right_sidebar'] = $this->load->view ('courses/inc/content', $data, true);
		$data['script'] = $this->load->view('courses/scripts/content', $data, true);

		$this->load->view(INCLUDE_PATH . 'header', $data);
		$this->load->view("courses/content", $data);
		$this->load->view(INCLUDE_PATH . 'footer', $data);
	}
	*/
}