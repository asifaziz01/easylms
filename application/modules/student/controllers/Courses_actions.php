 <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Courses_actions extends MX_Controller {
	public function __construct() {
		// Load Config and Model files required throughout Users sub-module
		$config = ['config_student', 'config_course', 'config_virtual_class'];
		$models = ['courses_model', 'lessons_model', 'enrolment_model', 'virtual_class_model', 'users_model'];
		$this->common_model->autoload_resources($config, $models);
	}

	public function buy_course ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		$this->courses_model->buy_course ($coaching_id, $member_id, $course_id, $batch_id);
		$vc = $this->virtual_class_model->get_batch_vc ($coaching_id, $course_id, $batch_id);
		if ($vc) {
			$this->virtual_class_model->add_participant ($coaching_id, $vc['class_id'], $member_id);
		}
		$this->message->set("You have successfully enroled in this course", "info", TRUE);
		redirect("student/courses/my_courses/".$coaching_id.'/'.$member_id);
	}

	public function enrol_in_course ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0) {
		$this->courses_model->enrol_in_course ($coaching_id, $member_id, $course_id, $batch_id);
		//$this->message->set("You have successfully enroled in this course", "info", TRUE);
		redirect("student/courses/enrol_in_course/".$coaching_id.'/'.$course_id.'/'.$member_id);
	}

	public function search ($coaching_id=0, $member_id=0) {

		$data['coaching_id'] = $coaching_id;
		$data['member_id'] = $member_id;
		$data['cat_id'] = $category_id = $this->input->post ('category');
		$data['courses'] = $this->courses_model->get_courses ($coaching_id, $category_id);

		$data['data']	= $data;

		$output  = $this->load->view ('courses/inc/index', $data, true);

		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'data'=>$output )));
	}

}