 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Tests_actions extends MX_Controller {	

	public function __construct () {
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_coaching'];
	    $models = ['tests_model', 'coaching_model', 'users_model' ,'qb_model'];
	    $this->common_model->autoload_resources ($config, $models);
	}


	/*-----===== Test Categories =====-----*/
	public function add_category ($coaching_id=0, $course_id=0) {

		$this->form_validation->set_rules ('title', 'Title', 'required');

		if ($this->form_validation->run () == true) {
			$id = $this->tests_model->create_category ($coaching_id, $course_id);
			if ($course_id > 0) {
				$message = 'Category updated successfully';
				$redirect = 'coaching/tests/categories/'.$coaching_id;
			} else {
				$message = 'Category created successfully';
				$redirect = 'coaching/tests/categories/'.$coaching_id;
			}
			$this->message->set ($message, 'success', true);
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>$message, 'redirect'=>site_url ($redirect) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}	    
	}
	
	// Delete Test Plan
	public function remove_category ($coaching_id=0, $course_id=0) {
		// Check if this plan is given to any coaching
		$this->tests_model->remove_category ($coaching_id, $course_id);
		$this->message->set ('Category deleted successfully', 'success', true);
		redirect ('coaching/tests/categories/'.$coaching_id);
	}
	

    public function search_tests ($coaching_id=0) {
		$tests = $this->tests_model->search_tests ($coaching_id);
		$data['coaching_id'] = $coaching_id;
		$data['course_id'] = $this->input->post ('course_id');
		$data['status'] = $this->input->post ('status');
		$data['tests'] = $tests;
		$tests = $this->load->view ('tests/inc/index', $data, true);
		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'data'=>$tests)));
	}
	
	public function create_test ($coaching_id=0, $course_id=0, $test_id=0) {

		$this->form_validation->set_rules ('title', 'Title', 'required|trim');
		$this->form_validation->set_rules ('pass_marks', 'Passing Percentage', 'required|trim|less_than[100]');
		$this->form_validation->set_rules ('time_min', 'Test Duration', 'required|trim');
		if ( $this->form_validation->run () == true )  {
			$data = $this->tests_model->create_test ($coaching_id, $course_id, $test_id);
			$redirect = 'coaching/tests/manage/'.$coaching_id.'/'.$data['course_id'].'/'.$data['test_id'];
			
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Test created successfully.', 'redirect'=>site_url($redirect) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		} 
	}
	
	
	public function set_method ($course_id=0, $test_id=0, $method=0) {
		$this->tests_model->set_method ($test_id, $method);
		if ($method == TEST_ADDQ_QB) {
			redirect('coaching/tests/add_test_questions/'.$course_id.'/'.$test_id);
		} elseif ($method == TEST_ADDQ_CREATE) {
			redirect('coaching/tests/question_group_create/'.$course_id.'/'.$test_id);			 
		} elseif ($method == TEST_ADDQ_UPLOAD) {
			redirect('coaching/tests/upload_test_questions/'.$course_id.'/'.$test_id);			 
		} else {
			redirect('coaching/tests/select_method/'.$course_id.'/'.$test_id);
		}
	}

	public function save_test_questions ($course_id=0, $test_id=0, $lesson_id=0, $cat_ids=0, $diff_ids=0, $exclude=0) {
		
		$this->form_validation->set_rules ('mycheck[]', 'Questions', 'required');
		$this->form_validation->set_message ('required', 'You must select question(s) before using this button');
		
		if ($this->form_validation->run() == true) {
			$questions = $this->input->post ('mycheck');
			foreach ($questions as $id ) {
				// we dont save question headings (if selected)
				$q = $this->qb_model->getQuestionDetails ($id);
				if ( $q['parent_id'] > 0 ) {
					$this->tests_model->saveQuestionsSimple($id, $test_id);
				}
			}
			$this->message->set ("Questions added to test. Select a lesson to add more questions.", 'success', true);
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Questions added successfully.', 'redirect'=>site_url('coaching/tests/add_test_questions/'.$course_id.'/'.$test_id.'/'.$lesson_id.'/'.$cat_ids.'/'.$diff_ids.'/'.$exclude) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
		
	}
	
	/* 
	// 	Remove Multiple Test Question
	*/
	public function remove_questions ($course_id=0, $test_id=0) {
		
		$this->form_validation->set_rules ('questions[]', 'Question', 'required');
		
		if ($this->form_validation->run () == true) {
			$i = 0;
			$questions = $this->input->post ('questions');
			foreach ($questions as $id) {
				$this->tests_model->deleteTestQuestion ($test_id, $id);
				$this->qb_model->delete_questions ($id);
				$i++;
			}
			$this->message->set ("<strong>$i</strong> Question(s) removed from test.", 'success', true);
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Question(s) removed successfully from test.', 'redirect'=>site_url('coaching/tests/preview_test/'.$course_id.'/'.$test_id) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>'Select Questions To Delete' )));	
		}
	}
	
	
	// Remove Individual Questions
	public function remove_question ($coaching_id=0, $course_id=0, $test_id=0, $parent_id=0, $question_id=0) {
		$result = $this->tests_model->deleteTestQuestion ($test_id, $question_id);
		$this->message->set('Question removed from this test', 'success', true);
		redirect ('coaching/tests/questions/'.$coaching_id.'/'.$course_id.'/'.$test_id);
	}	
	
	// Remove Individual Questions From Add Questions PAge
	public function remove_added_question ($course_id=0, $test_id=0, $id=0, $lesson_id=0, $cat_ids=0, $diff_ids=0, $exclude=0) {
		$result = $this->tests_model->deleteTestQuestion ($test_id, $id);
		redirect ('coaching/tests/add_questions/'.$course_id.'/'.$test_id.'/'.$lesson_id.'/'.$cat_ids.'/'.$diff_ids.'/'.$exclude);
	}
	
	// Reset Test	
	public function reset_test ($coaching_id=0, $course_id=0, $test_id=0 ) {
		$this->tests_model->resetTest ($coaching_id, $test_id);
		$this->message->set('All questions removed from this test', 'success', true);
		redirect ('coaching/tests/manage/'.$coaching_id.'/'.$course_id.'/'.$test_id);
	}
	
	/* Finalise Test
	// 
	*/
	public function finalise_test ($coaching_id=0, $course_id=0, $test_id) {

		$questions = $this->tests_model->getTestQuestions ($coaching_id, $test_id);
		$testMarks = $this->tests_model->getTestQuestionMarks ($coaching_id, $test_id);
		
		if (! empty($questions)) {
			$num_test_questions = count ($questions);
		} else {
			$num_test_questions = 0;
		}
 
		if ($num_test_questions == 0) {
			$this->message->set ('No questions in test. Test can not be finalized.', 'danger', true);
		} else {
			$result = $this->tests_model->finaliseTest ($coaching_id, $test_id, $testMarks);
			$this->message->set('Test published successfully', 'success', true);
		}
		redirect ('coaching/tests/manage/'.$coaching_id.'/'.$course_id.'/'.$test_id);
	}
	
	/* UnFinalise Test
	// 
	*/
	public function unfinalise_test ($coaching_id=0, $course_id=0, $test_id=0) {
		$result = $this->tests_model->unfinaliseTest ($coaching_id, $test_id);
		$this->message->set('Test Unpublished successfully. You can now add/remove questions or users.', 'success', true);
		redirect ('coaching/tests/manage/'.$coaching_id.'/'.$course_id.'/'.$test_id);
	}
	
	/* Release Result
	// 
	*/
	public function release_result ($coaching_id=0, $course_id=0, $test_id=0) {
		$result = $this->tests_model->release_result ($coaching_id, $test_id);
		$this->message->set('Result released successfully. Users will be able to see result now', 'success', true);
		redirect ('coaching/tests/manage/'.$coaching_id.'/'.$course_id.'/'.$test_id);
	}
	
	
	/* DELETE TEST
		Function to delete existing test
	*/
	public function delete_test ($coaching_id, $course_id, $test_id)	{		
		$this->tests_model->delete_tests ($test_id);	
		$this->message->set("Test deleted successfully", "success", TRUE);
		redirect("coaching/tests/index/".$coaching_id.'/'.$course_id);
	}
	
	public function export_pdf ($coaching_id=0, $course_id=0, $test_id=0) {

		$this->load->helper ('tcpdf');
		tcpdf();
		/*

		$test = $this->tests_model->view_tests ($test_id);
		$coaching = $this->coaching_model->get_coaching ($coaching_id);
		$coaching_name = $coaching['coaching_name'];
		$title = $test['title'];		
		
		$obj_pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$obj_pdf->SetCreator(PDF_CREATOR);		
		
		$obj_pdf->SetTitle($title);
		$obj_pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, $coaching_name, $title);
		$obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$obj_pdf->SetDefaultMonospacedFont('helvetica');
		$obj_pdf->setListIndentWidth(4);
		$obj_pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$obj_pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$obj_pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$obj_pdf->SetFont('helvetica', '', 9);
		$obj_pdf->setFontSubsetting(false);
		$obj_pdf->AddPage();

		$data['course_id'] = $course_id;
		$data['test_id'] 	 = $test_id;
		
		$questions = $this->tests_model->getTestQuestions ($coaching_id, $test_id);
		$questionMarks = $this->tests_model->getTestQuestionMarks ($coaching_id, $test_id);
		$questionTime = $this->tests_model->getTestQuestionTime ($coaching_id, $questions);
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
		$data['test_marks'] 			= $questionMarks =0;
		$data['answers'] 				= $answers;
		$data['questionTimeSeconds'] 	= $questionTime;
		$data['questionTime'] 			= date("H:i", mktime(0,0, $questionTime,0,0,0));		
		
		ob_start();
		// we can have any view part here like HTML, PHP etc
		$content = '';
		//$content .= 	$this->load->view('tests/print_pdf', $data, true);
		$file = $title . '.pdf';
		$obj_pdf->writeHTML($content, true, false, false, false, '');

		$count = 1;
		$answer_content = '';
		*/
		/*
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
		*/
		
		//$this->output->set_content_type("application/json");
		//$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Your file is being downloaded', 'redirect'=>site_url('coaching/tests/preview_test/'.$coaching_id.'/'.$course_id.'/'.$test_id) )));

				// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Nicola Asuni');
		$pdf->SetTitle('TCPDF Example 006');
		$pdf->SetSubject('TCPDF Tutorial');
		$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
		    require_once(dirname(__FILE__).'/lang/eng.php');
		    $pdf->setLanguageArray($l);
		}

		// ---------------------------------------------------------

		// set font
		$pdf->SetFont('dejavusans', '', 10);

		// add a page
		$pdf->AddPage();

		// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
		// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

		// create some HTML content
		$html = '<h1>HTML Example</h1>
		Some special characters: &lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt; \\slash \\\\double-slash \\\\\\triple-slash
		<h2>List</h2>
		List example:
		<ol>
		    <li><img src="images/logo_example.png" alt="test alt attribute" width="30" height="30" border="0" /> test image</li>
		    <li><b>bold text</b></li>
		    <li><i>italic text</i></li>
		    <li><u>underlined text</u></li>
		    <li><b>b<i>bi<u>biu</u>bi</i>b</b></li>
		    <li><a href="http://www.tecnick.com" dir="ltr">link to http://www.tecnick.com</a></li>
		    <li>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.<br />Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</li>
		    <li>SUBLIST
		        <ol>
		            <li>row one
		                <ul>
		                    <li>sublist</li>
		                </ul>
		            </li>
		            <li>row two</li>
		        </ol>
		    </li>
		    <li><b>T</b>E<i>S</i><u>T</u> <del>line through</del></li>
		    <li><font size="+3">font + 3</font></li>
		    <li><small>small text</small> normal <small>small text</small> normal <sub>subscript</sub> normal <sup>superscript</sup> normal</li>
		</ol>
		<dl>
		    <dt>Coffee</dt>
		    <dd>Black hot drink</dd>
		    <dt>Milk</dt>
		    <dd>White cold drink</dd>
		</dl>
		<div style="text-align:center">IMAGES<br />
		
		</div>';

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');


		
		// reset pointer to the last page
		$pdf->lastPage();

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Print a table

		

		// add a page
		$pdf->AddPage();

		// create some HTML content
		$html = '<h1>Image alignments on HTML table</h1>';

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');

		// reset pointer to the last page
		$pdf->lastPage();


		// ---------------------------------------------------------
		ob_end_clean ();
		
		//Close and output PDF document
		$pdf->Output('example_006.pdf', 'I');

		//============================================================+
		// END OF FILE
		//============================================================+

	}


	// Ajax enrol users
	public function enrol_users ($coaching_id=0, $course_id=0, $test_id=0, $type=0, $role_id=0, $class_id=0, $batch_id=0, $status='-1') {		
		
		$this->form_validation->set_rules('users[]', 'Users', 'required');
		if ( $this->form_validation->run () == true ) {
			$ids = $this->input->post ('users');
			foreach ($ids as $member_id) {
				$this->tests_model->enrol_member ($coaching_id, $member_id, $test_id);
			}
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>'User(s) enroled in test', 'redirect'=>site_url('coaching/tests/enrolments/'.$coaching_id.'/'.$course_id.'/'.$test_id.'/'.$type.'/'.$role_id.'/'.$class_id.'/'.$batch_id.'/'.$status) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}

	// Ajax unenrol users
	public function unenrol_users ($coaching_id=0, $course_id=0, $test_id=0, $type=0, $role_id=0, $class_id=0, $batch_id=0, $status='-1') {
		
		$this->form_validation->set_rules('actions', 'Actions', 'required');
		if ( $this->form_validation->run () == true ) {			
			$ids = $this->input->post ('users');
			$actions = $this->input->post ('actions');
			if (! empty ($ids)) {				
				foreach ($ids as $id) {
					if ($actions == 'archive') {
						$x = $this->tests_model->archive_member ($test_id, $id);						
					} else if ($actions == 'unenrol') {
						$x = $this->unenrol_user ($coaching_id, $course_id, $test_id, $role_id, $class_id, $type, $batch_id, $status, $id, $redirect=0);
					}
				}
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>'User(s) un-enroled from test', 'redirect'=>site_url('coaching/tests/enrolments/'.$coaching_id.'/'.$course_id.'/'.$test_id.'/'.$type.'/'.$role_id.'/'.$class_id.'/'.$batch_id.'/'.$status) )));
			} else {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'Select one or more users to complete this action' )));
			}
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}
	// Ajax unenrol user
	public function unenrol_user ($coaching_id=0, $course_id=0, $test_id=0, $type=0, $role_id=0, $class_id=0, $batch_id=0, $status='-1', $member_id=0, $redirect=1) { 
		$this->tests_model->unenrol_member ($coaching_id, $member_id, $test_id); 
		if ($redirect == 1) {
			$this->message->set ('User un-enroled from test', 'success', true);
			redirect ('coaching/tests/enrolments/'.$coaching_id.'/'.$course_id.'/'.$test_id.'/'.$type.'/'.$role_id.'/'.$class_id.'/'.$batch_id.'/'.$status);
		}
	}
	
	// Ajax validation question group
	public function validate_question_group_create ($coaching_id=0, $course_id=0, $test_id=0, $question_id=0) {
		$this->form_validation->set_rules('question', 'Question Group Title', 'required|trim');
		$this->form_validation->set_rules('marks', 'Marks', 'required|is_natural|trim|max_length[3]');
		//$this->form_validation->set_rules('negmarks', 'Negative Marks', 'is_natural|trim|max_length[2]');
		
		if (($this->form_validation->run() == true))  {
			$id = $this->qb_model->save_group ($coaching_id, $course_id, $question_id);
			$this->message->set ("Question heading created. Now add questions to it", 'success', true);
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Question created successfully', 'redirect'=>site_url('coaching/tests/question_create/'.$coaching_id.'/'.$course_id.'/'.$test_id.'/'.$id) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}
	
	
	// Ajax validation question 
	public function validate_question_create ($coaching_id=0, $course_id=0, $test_id=0, $parent_id=0, $question_id=0) {
		
		$type = $this->input->post ('question_type');
		$this->form_validation->set_rules('classification', 'Classification', 'required');
		$this->form_validation->set_rules('question', 'Question', 'required|trim');
		$this->form_validation->set_rules('marks', 'Marks', 'required');
		$this->form_validation->set_rules('optional_feedback', 'Optional Feedback', 'trim');
		$this->form_validation->set_rules('answer_feedback', 'Answer Feedback', 'trim');

		switch ($type) {
			// ===========================
			case QUESTION_TF:
				$this->form_validation->set_rules('answer', 'Correct Answer', 'required');
			break;
			//
			// ===========================
			case QUESTION_MCMC:
				$this->form_validation->set_rules('answer[]', 'Correct Answer', 'required');
				$this->form_validation->set_rules('choice[1]', 'Choice 1', 'required|trim'); 
				$this->form_validation->set_rules('choice[2]', 'Choice 2', 'required|trim'); 
				$this->form_validation->set_rules('choice[3]', 'Choice 3', 'required|trim'); 
				$this->form_validation->set_rules('choice[4]', 'Choice 4', 'trim'); 
				$this->form_validation->set_rules('choice[5]', 'Choice 5', 'trim'); 
				$this->form_validation->set_rules('choice[6]', 'Choice 6', 'trim'); 
			break;
			//
			// ===========================
			case QUESTION_LONG:
				$this->form_validation->set_rules('answer', 'Correct Answer', '');
				$this->form_validation->set_rules('choice[1]', 'Word Limit', 'trim'); 
				$this->form_validation->set_rules('choice[2]', 'Sample Answer', 'trim'); 
			break;
			//
			// ===========================
			case QUESTION_MATCH:
				$this->form_validation->set_rules('answer', 'Correct Answer', '');
				$this->form_validation->set_rules('choice[1]', 'COLOUMN 1', 'required|trim'); 
				$this->form_validation->set_rules('choice[2]', 'COLOUMN 2', 'required|trim'); 
				$this->form_validation->set_rules('choice[3]', 'COLOUMN 3', 'required|trim'); 
				$this->form_validation->set_rules('choice[4]', 'COLOUMN 4', 'trim'); 
				$this->form_validation->set_rules('choice[5]', 'COLOUMN 5', 'trim'); 
				$this->form_validation->set_rules('choice[6]', 'COLOUMN 6', 'trim'); 
				$this->form_validation->set_rules('option[1]', 'COLOUMN A', 'required|trim'); 
				$this->form_validation->set_rules('option[2]', 'COLOUMN B', 'required|trim'); 
				$this->form_validation->set_rules('option[3]', 'COLOUMN C', 'required|trim');
				$this->form_validation->set_rules('option[4]', 'COLOUMN D', 'trim'); 
				$this->form_validation->set_rules('option[5]', 'COLOUMN E', 'trim'); 
				$this->form_validation->set_rules('option[6]', 'COLOUMN F', 'trim'); 
			break;
			//
			// ===========================
			default:
				$this->form_validation->set_rules('answer', 'Correct Answer', 'required');
				$this->form_validation->set_rules('choice[1]', 'Choice 1', 'required|trim'); 
				$this->form_validation->set_rules('choice[2]', 'Choice 2', 'required|trim'); 
				$this->form_validation->set_rules('choice[3]', 'Choice 3', 'required|trim'); 
				$this->form_validation->set_rules('choice[4]', 'Choice 4', 'trim'); 
				$this->form_validation->set_rules('choice[5]', 'Choice 5', 'trim'); 
				$this->form_validation->set_rules('choice[6]', 'Choice 6', 'trim'); 
			break;
		}
		
		
		if (($this->form_validation->run() == true))  {
			$id = $this->qb_model->save_question ($coaching_id, 0, $parent_id, $question_id);
			$this->tests_model->add_to_test ($coaching_id, $id, $test_id);
			if ($question_id > 0) {
				$msg = 'Question updated successfully';
			} else {
				$msg = 'Question created successfully';
			}
			$this->message->set ($msg, 'success', true);
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>$msg, 'redirect'=>site_url('coaching/tests/question_create/'.$coaching_id.'/'.$course_id.'/'.$test_id.'/'.$parent_id.'/0/'.$type) )));
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}


	public function answer_choice_template ($coaching_id=0, $question_id=0, $question_type=QUESTION_MCSC) {
		$data['result'] = $this->qb_model->getQuestionDetails ($coaching_id, $question_id);
		$answers = $this->load->view (ANSWER_TEMPLATE . 'create_'.$question_type, $data, true);
		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'data'=>$answers )));
	}
	
	public function delete_attempt ($coaching_id=0, $attempt_id=0, $member_id=0, $test_id=0) {
		$this->tests_model->delete_attempt ($coaching_id, $attempt_id, $member_id, $test_id);
		$this->message->set ('Attempt deleted successfully', 'success', true);
		redirect ('coaching/reports/all_reports/'.$coaching_id.'/0/'.$member_id.'/'.$test_id);
	}
	
	/*****************************************/
	public function submit_test ($course_id, $test_id) {
		$questions  = $this->input->post('questions');
		$attempt_id = $this->input->post('attempt_id');
		$member_id  = $this->session->userdata('member_id');
		$ans 		= $this->input->post('ans');
    
		foreach ($ans as $qid=>$answer) {
			if ( ! empty ($answer) ) {
				$this->tests_model->insert_answers ($test_id, $qid, $answer);
			}
			unset($answer);
		}
		$this->message->set ('You have successfully completed your test. Now you can review your scores', 'success', true);
		//redirect ('tests/reports/all_reports/'.$attempt_id.'/'.$member_id.'/'.$test_id);
		echo site_url('tests/frontend/test_submitted/'.$attempt_id.'/'.$member_id.'/'.$test_id);
    exit;
    //redirect ('tests/frontend/test_submitted/'.$attempt_id.'/'.$member_id.'/'.$test_id);
	}
	public function mark_for_demo ($test_id=0, $data=0) {
		$this->tests_model->mark_for_demo ($test_id, $data);
        $this->output->set_content_type("application/json");
        $this->output->set_output(json_encode(array('status'=>true, 'message'=>'marked')));
	}

	public function upload_questions ($coaching_id=0, $course_id=0, $test_id=0) {
		
		/*
			Steps taken
			1. Upload the file in a temporary location
			2. Read the content of the file
			3. Based on file extension, send it for processing to concerned precessor
			4. Processor will analyze the content and send back reponse 
				- If the content is as per template display likewise in page
				- if the content has errors, try to highlight the error parts
				- If the content is empty, throw back an error
			5.  
		*/
		$this->load->helper ('directory');
		$this->load->helper ('file');
		
		$test = $this->tests_model->view_tests ($test_id);
		
		$member_id = $this->session->userdata ('member_id');
		
		$upload_dir = $this->config->item ('upload_dir'). $member_id . '/temp/';
		$temp_upload = directory_map ('./' . $upload_dir);
		if ( ! is_array ($temp_upload)) {
			@mkdir ($upload_dir, 0755, true);
		}
		
		$config['upload_path'] = './' . $upload_dir;
		$config['allowed_types'] = ['docx', 'txt'];
		$config['overwrite'] = true;
 		
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload()) {
			$errors = $this->upload->display_errors();
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>$errors)));
		} else {		
			$upload_data = $this->upload->data();
			$file_path = base_url ($upload_dir . $upload_data['file_name']);
			$file = file_get_contents($file_path);
		}


	}


}