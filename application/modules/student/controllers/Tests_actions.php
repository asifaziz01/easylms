 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Tests_actions extends MX_Controller { 
	var $coaching_id = FALSE;
	
	public function __construct () { 
		$config = ['config_student'];
	    $models = ['tests_model', 'coaching_model', 'users_model' ,'qb_model'];
	    $this->common_model->autoload_resources ($config, $models);
	}
	
	
	public function search_tests ($coaching_id=0) {
		$data = $this->tests_model->search_tests ($coaching_id);
		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'data'=>$data)));
	}
	
	public function export_pdf ($category_id=0, $test_id=0) {

		$test = $this->tests_model->view_tests ($test_id);
		$coaching = $this->coaching_model->get_coaching ($this->session->userdata('coaching_id'));
		$coaching_name = $coaching['coaching_name'];
		$title = $test['title'];		
		
		$this->load->helper ('tcpdf');
		tcpdf();
		$obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$obj_pdf->SetCreator(PDF_CREATOR);		
		
		$obj_pdf->SetTitle($title);
		$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $coaching_name, $title);
		$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$obj_pdf->SetDefaultMonospacedFont('helvetica');
		$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$obj_pdf->SetFont('helvetica', '', 9);
		$obj_pdf->setFontSubsetting(false);
		$obj_pdf->AddPage();

		$data['category_id'] = $category_id;
		$data['test_id'] 	 = $test_id;
		
		
		$questions = $this->tests_model->getTestQuestions ($test_id);
		$questionMarks = $this->tests_model->getTestQuestionMarks ($test_id);
		$questionTime = $this->tests_model->getTestQuestionTime ($questions);
		$result = array ();
		$answers = array ();
		if ( ! empty($questions)) {
			foreach ($questions as $id) {
				$row = $this->qb_model->getQuestionDetails ($id);
				$parent_id = $row['parent_id'];
				$result[$parent_id][] = $id;
				// Correct Answer
				for ($i=1; $i<=6; $i++) {
					if ($row['answer_'.$i] > 0) {
						$answers[$id] = $row['choice_'.$i];
					}
				}
			}
		}
		$data['results'] 				= $result;
		$data['test_marks'] 			= $questionMarks;
		$data['answers'] 				= $answers;
		$data['questionTimeSeconds'] 	= $questionTime;
		$data['questionTime'] 			= date("H:i", mktime(0,0, $questionTime,0,0,0));		

		
		ob_start();
		// we can have any view part here like HTML, PHP etc
		$content = '';
		$content .= 	$this->load->view('print_pdf', $data, true);
		$file = $title . '.pdf';
		$obj_pdf->writeHTML($content, true, false, false, false, '');

		$count = 1;
		$answer_content = '';
		$answer_content .= '<h4>Answer Sheet</h4>';
		if ( ! empty($questions)) {
			foreach ($questions as $id) {
				$answer_content .= '<table width="100%">';
					$answer_content .= '<tr>';
						$answer_content .= '<td width="5%">';
							$answer_content .= $count . '.';
						$answer_content .= '</td>';
						$answer_content .= '<td width="">';
							$answer_content .= $answers[$id];
						$answer_content .= '</td>';
					$answer_content .= '</tr>';
				$answer_content .= '</table>';
				$count++;
			}
		}
		$obj_pdf->AddPage();
		$obj_pdf->writeHTML($answer_content, true, false, true, false, '');
		ob_end_clean();
		$obj_pdf->Output($file, 'I');
		
		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Your file is being downloaded', 'redirect'=>site_url('tests/admin/preview_test/'.$category_id.'/'.$test_id) )));

	}
	
	public function submit_test ($coaching_id=0, $member_id=0, $course_id=0, $test_id=0) { 
		$questions  = $this->input->post('questions');
		$attempt_id = $this->input->post('attempt_id');
		$ans 		= $this->input->post('ans');

    	if (! empty($ans)) {    		
			foreach ($ans as $qid=>$answer) {
				if ( ! empty ($answer) ) {
					$this->tests_model->insert_answers ($coaching_id, $member_id, $test_id, $qid, $answer);
				}
				unset($answer);
	    	}
		}

		$this->tests_model->update_submission_time ($coaching_id, $attempt_id, $test_id, $member_id);

		//$this->message->set ('You have successfully completed your test. Now you can review your scores', 'success', true);
		redirect ('student/tests/test_submitted/'.$coaching_id.'/'.$course_id.'/'.$test_id.'/'.$member_id.'/'.$attempt_id);
	}

	public function request_reset ($coaching_id=0, $member_id=0, $test_id=0, $attempt_id=0) {
		$this->message->set ('Reset request has been sent. Please wait till the test become available again', 'success', true);
		redirect ('student/tests/index/'.$coaching_id.'/'.$member_id.'/'.$test_id);
	}
	
}