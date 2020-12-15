 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Qb_actions extends MX_Controller {	

	public function __construct () {
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_coaching'];
	    $models = ['tests_model', 'coaching_model', 'users_model' ,'qb_model'];
	    $this->common_model->autoload_resources ($config, $models);
	}


	public function import_from_word ( $coaching_id=0, $course_id=0, $parent_id=0, $test_id=0) {
		
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
		$config['allowed_types'] = 'docx';
		$config['overwrite'] = true;
 
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload()) {
			$errors = $this->upload->display_errors();
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>$errors)));
		} else {			
			$upload_data = $this->upload->data();
			$file = $upload_dir . $upload_data['file_name'];			
			//$get_file = file ($file, FILE_SKIP_EMPTY_LINES);
			
			$zip = zip_open($file);
			
			if ( ! $zip || is_numeric($zip)) {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'This is not in proper Word 2007 format. Please upload file in DOCX format.')));
			}	
			
			$content = '';
			while ($zip_entry = zip_read($zip)) {
				
				if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
				
				if (zip_entry_name($zip_entry) != "word/document.xml") continue;
				
				$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

				zip_entry_close($zip_entry);
				
			}// end while
		
			zip_close($zip);
			
			// PREPARE CONTENT
			$content = str_replace ('</w:r></w:p></w:tc><w:tc>', " ", $content);			
			$content = explode ('</w:r></w:p>', $content);
			//print_r ($content);
			//exit;
			// DEFAULT PARAMETERS
			$answer_counter = array (1=>'a', 2=>'b', 3=>'c', 4=>'d', 5=>'e', 6=>'f');
			// Required settings 
			$question_heading   = '';
			$question			= '';
			$def_difficulty 	= 'Medium';
			$def_category	 	= 'Knowledge';
			$def_marks 			= 1;
			$def_time 			= 120;		// 2 minutes
			$def_neg_marks		= 0;		// Negative marking
			$type				= QUESTION_MCSC;		// Question Type			
			// Answer choices
			$choice[1] 			= '';
			$choice[2] 			= '';
			$choice[3] 			= '';
			$choice[4] 			= '';
			$choice[5] 			= '';
			$choice[6] 			= '';
			// Correct answers
			$answer[1] 			= '';
			$answer[2]			= '';
			$answer[3] 			= '';
			$answer[4] 			= '';
			$answer[5]			= '';
			$answer[6] 			= '';
			// For Matching-type questions (optional)
			$option[1] 			= '';
			$option[2] 			= '';
			$option[3] 			= '';
			$option[4] 			= '';
			$option[5] 			= '';
			$option[6] 			= '';

			$question_group = array ();
			$question_main  = array ();
			$group_count = 0;
			$question_count = 0;
			
			if ( ! empty ($content) ) {
				foreach ($content as $line) {
					$line = strip_tags ($line);
					if ( strlen ($line) > 0 ) {				// Not an empty line

						echo $line;
							
						// Question heading
						// This is a question group
						if ( preg_match ('/^#QH/', $line) == 1 ) {
							$qh 	= substr ($line, 4);
							$question_count = 0;
							$group_count++;
							$question_group[$group_count]['qh'] = array ('question'=>ascii_to_entities ($qh));
						} else if ( preg_match ('/^@QT/', $line) == 1 ) {
							// Time 
							$time = substr ($line, 3);
							$question_group[$group_count]['qh']['time'] = addslashes(trim($time));
						} else if ( preg_match ('/^@NM/', $line) == 1 ) {
							// Negative Marks 
							$neg_marks = substr ($line, 3);
							$question_group[$group_count]['qh']['negmarks'] = addslashes(trim($neg_marks));
						} else if ( preg_match ('/^@QM/', $line) == 1 ) {
							// Marks 
							$marks = substr ($line, 3);
							$question_group[$group_count]['qh']['marks'] = addslashes(trim($marks));
						} else if ( preg_match ('/^[\d]+[\.|\)]/', $line) == 1 ) {
							// Main question
							$match =  preg_split ('/^[\d]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
							$question = $match[0];
							$question_count++;
							// Individual question array
							$question = ascii_to_entities (addslashes(trim($question)));
							$question_main[$group_count][$question_count] = array ('question'=>ascii_to_entities ($question));
						} else if ( preg_match ('/^\*+[a-fA-F]+[\.|\)]/', $line) == 1) {
							// Correct Answers 
							preg_match ('/^\*+[a-fA-F]+[\.|\)]/', $line, $match ); 
							// this is a correct answer
							if ( ! empty ( $match )) {
								// answer counter
								$match = $match[0];
								$get_char = substr ($match, 1, 1);
								$num = 0;
								foreach ($answer_counter as $n=>$c) {
									if ( strtoupper ($c) == strtoupper ($get_char)) {
										$num = $n;
									}
								}
								// answer text
								$get_answer =  preg_split ('/^\*+[a-fA-F]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
								//$prep['answer'][$num] = $num;
								//$prep['choice'][$num] = ascii_to_entities (trim($get_answer[0]));
								
								$question_main[$group_count][$question_count]['choice_'.$num] =ascii_to_entities (addslashes(trim($get_answer[0]))) ;
								$question_main[$group_count][$question_count]['answer_'.$num] = $num;
							} 
						// Answer Choices
						} else if ( preg_match ('/^[a-fA-F]+[\.|\)]/', $line) == 1) {
							preg_match ('/^[a-fA-F]+[\.|\)]/', $line, $match );
							// this is an answer choice
							if ( ! empty ( $match )) {
								// answer counter
								$match = $match[0];
								$get_char = substr ($match, 0, 1);
								$num = 0;
								foreach ($answer_counter as $n=>$c) {
									if ( strtoupper ($c) == strtoupper ($get_char)) {
										$num = $n;
									}
								} 
								// answer text
								$get_answer =  preg_split ('/^[a-fA-F]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
								$question_main[$group_count][$question_count]['choice_'.$num] =ascii_to_entities (addslashes(trim($get_answer[0]))) ; 
							}
						} else if ( preg_match ('/^@QC/', $line) == 1 ) {
							// Category 
							$classification = substr ($line, 3);
							$question_main[$group_count][$question_count]['category_id'] =ascii_to_entities (addslashes(trim($classification))) ;
						} else if ( preg_match ('/^@DT/', $line) == 1 ) {							
							// Question difficulty
							$difficulty = substr ($line, 3);
							$question_main[$group_count][$question_count]['clsf_id'] =ascii_to_entities (addslashes(trim($difficulty))) ;
						} else if ( preg_match ('/^@QF/', $line) == 1) {
							// Question feedback
							$match =  preg_split ('/^@QF/', $line, 0, PREG_SPLIT_NO_EMPTY );
							$match = $match[0];
							$question_main[$group_count][$question_count]['question_feedback'] =ascii_to_entities (addslashes(trim($match))) ;
						} else if ( preg_match ('/^@AF/', $line) == 1) {
							// Answer feedback
							$match =  preg_split ('/^@AF/', $line, 0, PREG_SPLIT_NO_EMPTY );
							$match = $match[0];
							$question_main[$group_count][$question_count]['answer_feedback'] =ascii_to_entities (addslashes(trim($match))) ;
						} else {
							if ($question_count > 0) {
								$current = $question_main[$group_count][$question_count];
								if ( ! empty ($current) ) {
									end ($current);
									$cline = key ($current);
									$question_main[$group_count][$question_count][$cline] .= '<br>';
									$question_main[$group_count][$question_count][$cline] .= $line;
								}
							} else {
								$current = $question_group[$group_count]['qh'];
								if ( ! empty ($current) ) {
									end ($current);
									$cline = key ($current);
									$question_group[$group_count]['qh'][$cline] .= '<br>';
									$question_group[$group_count]['qh'][$cline] .= $line;
								}
							}
						}						
					}
				}
			}
			
			//print_r ($question_main); exit;
			$parent_id = 0;
			if ( ! empty ($question_group)) {
				foreach ($question_group as $gnum=>$group ) {
					if ( ! empty ($group) ) {
						foreach ($group as $question_heading) {
							// insert heading
							$parent_id = $this->qb_model->import_text_save_group ($coaching_id, $course_id, $question_heading, $type);
							
							// insert question
							$questions = $question_main[$gnum];							
							$this->qb_model->import_text_save_question ($coaching_id, $course_id, $parent_id, $questions, $test_id);
						}
					}
				}
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Questions uploaded successfully', 'redirect'=>site_url('coaching/tests/questions/'.$coaching_id.'/'.$course_id.'/'.$test_id) )));
			} else { 
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'There was an error uploading questions. Please check if the questions were in proper format.' )));
			}

		}
	}
	
	/* 
		IMPORT QUESTIONS
		Function to import questions from text file
	*/
	public function import_from_text ( $lesson_id=0, $parent_id=0, $test_id=0) {
		
		$this->load->helper ('directory');
		$this->load->helper ('file');
		
		$member_id = $this->session->userdata ('member_id');
		
		$upload_dir = $this->config->item ('upload_dir'). $member_id . '/temp/';
		$temp_upload = directory_map ('./' . $upload_dir);
		if ( ! is_array ($temp_upload)) {
			@mkdir ($upload_dir, 0755, true);
		}
		
		$config['upload_path'] = './' . $upload_dir;
		$config['allowed_types'] = 'txt';
		$config['overwrite'] = true;
 
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload()) {
			$errors = $this->upload->display_errors();
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>$errors)));
		} else {			
			$upload_data = $this->upload->data();
			$file = $upload_dir . $upload_data['file_name'];
			$get_file = file ($file, FILE_SKIP_EMPTY_LINES);
			
			// DEFAULT PARAMETERS
			$answer_counter = array (1=>'a', 2=>'b', 3=>'c', 4=>'d', 5=>'e', 6=>'f');
			// Required settings 
			$question_heading   = '';
			$question			= '';
			$def_difficulty 	= 'Medium';
			$def_category	 	= 'Knowledge';
			$def_marks 			= 1;
			$def_time 			= 120;		// 2 minutes
			$def_neg_marks		= 0;		// Negative marking
			$type				= QUESTION_MCSC;		// Question Type			
			// Answer choices
			$choice[1] 			= '';
			$choice[2] 			= '';
			$choice[3] 			= '';
			$choice[4] 			= '';
			$choice[5] 			= '';
			$choice[6] 			= '';
			// Correct answers
			$answer[1] 			= '';
			$answer[2]			= '';
			$answer[3] 			= '';
			$answer[4] 			= '';
			$answer[5]			= '';
			$answer[6] 			= '';
			// For Matching-type questions (optional)
			$option[1] 			= '';
			$option[2] 			= '';
			$option[3] 			= '';
			$option[4] 			= '';
			$option[5] 			= '';
			$option[6] 			= '';

			$question_group = array ();
			$question_main  = array ();
			$group_count = 0;
			$question_count = 0;
			
			if ( ! empty ($get_file) ) {
				foreach ($get_file as $line) {
					//echo $line;
					//echo br ();
					if ( strlen ($line) > 0 ) {				// Not an empty line
							
						// Question heading
						// This is a question group
						if ( preg_match ('/^#QH/', $line) == 1 ) {
							$qh 	= substr ($line, 4);
							$question_count = 0;
							$group_count++;
							$question_group[$group_count]['qh'] = array ('question'=>ascii_to_entities ($qh));
						} else if ( preg_match ('/^@QT/', $line) == 1 ) {
							// Time 
							$time = substr ($line, 3);
							$question_group[$group_count]['qh']['time'] = trim($time);
						} else if ( preg_match ('/^@NM/', $line) == 1 ) {
							// Negative Marks 
							$neg_marks = substr ($line, 3);
							$question_group[$group_count]['qh']['negmarks'] = trim($neg_marks);
						} else if ( preg_match ('/^@QM/', $line) == 1 ) {
							// Marks 
							$marks = substr ($line, 3);
							$question_group[$group_count]['qh']['marks'] = trim($marks);
						} else if ( preg_match ('/^[\d]+[\.|\)]/', $line) == 1 ) {
							// Main question
							$match =  preg_split ('/^[\d]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
							$question = $match[0];
							$question_count++;
							// Individual question array
							$question = ascii_to_entities (trim($question));
							$question_main[$group_count][$question_count] = array ('question'=>ascii_to_entities ($question));
						} else if ( preg_match ('/^\*+[a-fA-F]+[\.|\)]/', $line) == 1) {
							// Correct Answers 
							preg_match ('/^\*+[a-fA-F]+[\.|\)]/', $line, $match ); 
							// this is a correct answer
							if ( ! empty ( $match )) {
								// answer counter
								$match = $match[0];
								$get_char = substr ($match, 1, 1);
								$num = 0;
								foreach ($answer_counter as $n=>$c) {
									if ( strtoupper ($c) == strtoupper ($get_char)) {
										$num = $n;
									}
								}
								// answer text
								$get_answer =  preg_split ('/^\*+[a-fA-F]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
								//$prep['answer'][$num] = $num;
								//$prep['choice'][$num] = ascii_to_entities (trim($get_answer[0]));
								
								$question_main[$group_count][$question_count]['choice_'.$num] =ascii_to_entities (trim($get_answer[0])) ;
								$question_main[$group_count][$question_count]['answer_'.$num] = $num;
							} 
						// Answer Choices
						} else if ( preg_match ('/^[a-fA-F]+[\.|\)]/', $line) == 1) {
							preg_match ('/^[a-fA-F]+[\.|\)]/', $line, $match );
							// this is an answer choice
							if ( ! empty ( $match )) {
								// answer counter
								$match = $match[0];
								$get_char = substr ($match, 0, 1);
								$num = 0;
								foreach ($answer_counter as $n=>$c) {
									if ( strtoupper ($c) == strtoupper ($get_char)) {
										$num = $n;
									}
								} 
								// answer text
								$get_answer =  preg_split ('/^[a-fA-F]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
								$question_main[$group_count][$question_count]['choice_'.$num] =ascii_to_entities (trim($get_answer[0])) ; 
							}
						} else if ( preg_match ('/^@QC/', $line) == 1 ) {
							// Category 
							$classification = substr ($line, 3);
							$question_main[$group_count][$question_count]['category_id'] =ascii_to_entities (trim($classification)) ;
						} else if ( preg_match ('/^@DT/', $line) == 1 ) {							
							// Question difficulty
							$difficulty = substr ($line, 3);
							$question_main[$group_count][$question_count]['clsf_id'] =ascii_to_entities (trim($difficulty)) ;
						} else if ( preg_match ('/^@QF/', $line) == 1) {
							// Question feedback
							$match =  preg_split ('/^@QF/', $line, 0, PREG_SPLIT_NO_EMPTY );
							$match = $match[0];
							$question_main[$group_count][$question_count]['question_feedback'] =ascii_to_entities (trim($match)) ;
						} else if ( preg_match ('/^@AF/', $line) == 1) {
							// Answer feedback
							$match =  preg_split ('/^@AF/', $line, 0, PREG_SPLIT_NO_EMPTY );
							$match = $match[0];
							$question_main[$group_count][$question_count]['answer_feedback'] =ascii_to_entities (trim($match)) ;
						} else {
							if ($question_count > 0) {
								$current = $question_main[$group_count][$question_count];
								end ($current);
								$cline = key ($current);
								$question_main[$group_count][$question_count][$cline] .= '<br>';
								$question_main[$group_count][$question_count][$cline] .= $line;
							} else {
								$current = $question_group[$group_count]['qh'];
								end ($current);
								$cline = key ($current);
								$question_group[$group_count]['qh'][$cline] .= '<br>';
								$question_group[$group_count]['qh'][$cline] .= $line;
							}
							
						}
						
					}
				}
			}
			
			//print_r ($question_main); exit;
			$pid = 0;
			$success = false;
			if ($parent_id > 0) {
				if ( ! empty ($question_main)) {
					foreach ($question_main as $questions ) {
						// insert question
						$this->qb_model->import_text_save_question ($lesson_id, $parent_id, $questions);
					}
					$success = true;
				}
			} else {					
				if ( ! empty ($question_group)) {
					foreach ($question_group as $gnum=>$group ) {
						if ( ! empty ($group) ) {
							foreach ($group as $question_heading) {
								
								//$qh = $question_heading['question'];
								//$qm = $question_heading['marks']; 
								// insert heading
								if ($parent_id > 0) {
									$pid = $parent_id;
								} else {
									$pid = $this->qb_model->import_text_save_group ($lesson_id, $question_heading, $type);
								}							
								// insert question
								$questions = $question_main[$gnum];							
								$this->qb_model->import_text_save_question ($lesson_id, $pid, $questions, $test_id);
							}
						}
					}
					$success = true;
				}
			}
			if ($success == true) {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Questions uploaded successfully', 'redirect'=>site_url('qb/page/import/'.$lesson_id) )));
			} else { 
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'There was an error uploading questions. Please check if the questions were in proper format.' )));
			}

		}
	}
	
	


	/* 
		IMPORT CSV QUESTIONS
		Function to import questions from csv file
	*/
	public function import_from_csv ($lesson_id=0) {
		
		$this->load->helper ('directory');
		$this->load->helper ('file');
		
		$member_id = $this->session->userdata ('member_id');
		
		$upload_dir = $this->config->item ('upload_dir'). $member_id . '/temp/';
		$temp_upload = directory_map ('./' . $upload_dir);
		if ( ! is_array ($temp_upload)) {
			@mkdir ($upload_dir, 0755, true);
		}
		
		$config['upload_path'] = './' . $upload_dir; 
		$config['allowed_types'] = 'csv';
		$config['overwrite'] = true;
 
		$this->load->library('upload', $config);
		if ( ! $this->upload->do_upload()) {
			$errors = $this->upload->display_errors();
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>$errors )));
		} else {			
			$upload_data = $this->upload->data();
			$file = $upload_dir . $upload_data['file_name'];
			//$get_file = file ($file, FILE_SKIP_EMPTY_LINES);
			$get_file = read_file($file);
			
			$i = 0;
			$data = array ();
			$group_count = 1;
			$parent_id = 0;
			if (($handle = fopen (base_url($file), "r")) !== FALSE) {
				while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$questions = array ();
					if ($i == 0 || $row[0] == '') {
					} else {
						$num = count($row);
						for ($c=0; $c < $num; $c++) {
							if ($row[$c] != '') {
								$questions[$c] = $row[$c];
							}
						}
						//print_r ($questions);
						//echo '<br>';
						$num = count($questions);
						if ($num >= 2 && $num <= 4 ) {
							// This is a question group
							$parent_id = 0;
							$parent_id = $this->qb_model->upload_questions_csv ($lesson_id, $parent_id, $row);
						} else if ($num >= 5 && $num <= 15 ) {
							// This is a question
							$this->qb_model->upload_questions_csv ($lesson_id, $parent_id, $row);
						}
						//$row++;
					}
					$i++;
				}
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Questions uploaded successfully', 'redirect'=>site_url('qb/page/import/'.$lesson_id) )));
			} else {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'There was an error uploading questions. Please check if the questions were in proper format.' )));
			}
			
		}
	}

}