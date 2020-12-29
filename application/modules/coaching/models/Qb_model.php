<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Qb_model extends CI_Model {

	public function __construct() {
		parent::__construct();
	}	

	/*
	// Get List of all questions in a lesson
	*/
	
	public function getQuestions ($lesson_id='', $cat_ids='NA', $clsf_ids='NA', $diff_ids='NA', $exclude=0, $offset=0, $limit=0) {
		
		$all_questions = array ();
		
		// Filter Lesson IDs
		//$lesson_ids = explode ('-', $lesson_id);
		//$this->db->where_in ('chapter_id', $lesson_ids );

		// Filter Category IDs
		if ($cat_ids != 'NA') {
			$cats = explode ('-', $cat_ids);
			$this->db->where_in ('category_id', $cats );
		}

		$this->db->where ('parent_id', 0 );
		$query = $this->db->get ('coaching_questions');
		$this->db->last_query ();
		$query->num_rows ();
		
		
		if ($query->num_rows() > 0 ) {
			
			$result_array = $query->result_array();
			foreach ($result_array as $row) { 

				$parent_id = $row['question_id'];				
				
				if ($clsf_ids != 'NA') {
					$clsf_id = explode ('-', $clsf_ids);
					$this->db->where_in ('clsf_id', $clsf_id);
				}
				
				if ($diff_ids != 'NA') {
					$diff_id = explode ('-', $diff_ids);
					$this->db->where_in ('diff_id', $diff_id); 
				}
				
				$this->db->where ('parent_id', $parent_id);
				$sql = $this->db->get ('coaching_questions');
				if ($sql->num_rows () > 0 ) {
					$result = $sql->result_array ();
					foreach ($result as $row_item) {

					// Exclude questions added to any test
						$found = false;
						if ($exclude == 1) {
							//$found = $this->questionInAnyTest ($row_item['question_id']); 
						}
						if ($found == false) {	
							// if question is not added in any test
							
							$row_item['question'] = entities_to_ascii ($row_item['question']);
							$row_item['question_feedback'] = entities_to_ascii ($row_item['question_feedback']);
							$row_item['answer_feedback'] = entities_to_ascii ($row_item['answer_feedback']);
							for ($i=1; $i<=6; $i++) {
								$row_item['choice_'.$i] = entities_to_ascii ($row_item['choice_'.$i]);
								$row_item['option_'.$i] = entities_to_ascii ($row_item['option_'.$i]);
							}
							
							$all_questions[$parent_id][] = $row_item;							
						}
					} // end for
				} // end if
			} // end foreach
		} 

		return $all_questions;
	} 
	
	
	//get default language
	public function getDefault_language(){
		$languages	=	$this->common_model->get_sys_parameters(SYS_QUESTION_LANGUAGE);
		foreach($languages as $languages=>$each){
			if($each['default']){
				$default	=	$each['paramkey'];
			}
		}
		return $default;
	}

	/*
	// Get List of all questions in a lesson
	*/
	public function get_sub_questions ($lesson_id=0, $parent_id=0) {
		
		$result = array ();
		$questions = array ();
		
		$this->db->where ('chapter_id', $lesson_id );
		$this->db->where ('parent_id', $parent_id );
		$query = $this->db->get ('coaching_questions');
		
		if ($query->num_rows() > 0 ) {
			$result = $query->result_array ();
			foreach ($result as $row_item) {
				$row_item['question'] = entities_to_ascii ($row_item['question']);
				$row_item['question_feedback'] = entities_to_ascii ($row_item['question_feedback']);
				$row_item['answer_feedback'] = entities_to_ascii ($row_item['answer_feedback']);
				for ($i=1; $i<=6; $i++) {
					$row_item['choice_'.$i] = entities_to_ascii ($row_item['choice_'.$i]);
					$row_item['option_'.$i] = entities_to_ascii ($row_item['option_'.$i]);
				}
				$questions[] = $row_item;
			}
		}		
		return $questions;
	}
	

	public function get_heading_questions ($lesson_id=0) {
		
		$result = array ();
		$questions = array ();
		
		$this->db->where ('parent_id', 0 );
		$this->db->where ('chapter_id', $lesson_id );
		$query = $this->db->get ('coaching_questions');
		
		if ($query->num_rows() > 0 ) {
			$result = $query->result_array ();
			foreach ($result as $row_item) {
				$row_item['question'] = entities_to_ascii ($row_item['question']);
				$questions[] = $row_item;
			}
		}
		
		return $questions;
	} 

	
	/*
	// Get details of all selected question
	*/
	public function getQuestionDetails ($question_id) {
		$query = $this->db->get_where('coaching_questions', array('question_id'=>$question_id) );
		if ($query->num_rows() > 0 ) {
			$row = $query->row_array();
			$row['question'] 			= entities_to_ascii ($row['question']);
			$row['question_feedback'] 	= entities_to_ascii ($row['question_feedback']);
			$row['answer_feedback'] 	= entities_to_ascii ($row['answer_feedback']);
			for ($i=1; $i<=6; $i++) {
				$row['choice_'.$i] = entities_to_ascii  (($row['choice_'.$i]));
				$row['option_'.$i] = entities_to_ascii  (($row['option_'.$i]));
			}
			return $row;
		} else {
		 	return false;
		}
	}	
	
	
	/*
	// Get details of all selected lingual question
	*/
	public function getQuestionDetails_lingual ($question_id,$lang_id = 0, $parent_id =0) {
		$this->db->where('question_id',$question_id);
		if($lang_id > 0){
			$this->db->where('lang_id',$lang_id);
			if($parent_id > 0 ){
				$this->db->where('parent_id',$parent_id);
			}
			$query = $this->db->get('questions_lingual');
		}
		else{
			$query	= $this->db->get('questions_lingual');
			}
		if ($query->num_rows() > 0 ) {
			$row = $query->row_array();
			$row['question'] 			= entities_to_ascii ($row['question']);
			$row['question_feedback'] 	= entities_to_ascii ($row['question_feedback']);
			$row['answer_feedback'] 	= entities_to_ascii ($row['answer_feedback']);
			for ($i=1; $i<=6; $i++) {
				$row['choice_'.$i] = entities_to_ascii  (($row['choice_'.$i]));
				$row['option_'.$i] = entities_to_ascii  (($row['option_'.$i]));
			}
			return $row;
		} else {
		 	return false;
		}
	}

	
	public function save_group_lingual ($lesson_id, $question_id,$lang_id,$edit) {
		$marks 		= $this->input->post ('marks');
		$type 		= $this->input->post ('type');
		$time 		= 0;
		$negmarks 	= 0;
		$data = array(
					'question_id'			=>$question_id,
					'lang_id'			        =>$this->input->post('language'),
					'type'					=>$type,
					'marks'					=>$marks,  
					'time'					=>$time,
					'negmarks'				=>$negmarks, 
					'question_feedback'		=>'',  
					'answer_feedback'		=>'',  
					'chapter_id'			=>$lesson_id, 
					'question'				=>ascii_to_entities($this->input->post('question')), 
					'parent_id'				=>'0',
					'choice_1'				=>'',  
					'choice_2'				=>'',
					'choice_3'				=>'',
					'choice_4'				=>'',
					'choice_5'				=>'', 
					'choice_6'				=>'',
					'option_1'				=>'',  
					'option_2'				=>'',
					'option_3'				=>'',
					'option_4'				=>'',
					'option_5'				=>'', 
					'option_6'				=>'',
					'answer_1'				=>0,
					'answer_2'				=>0,
					'answer_3'				=>0,
					'answer_4'				=>0, 
					'answer_5'				=>0,
					'answer_6'				=>0,
				);
		if ($edit == 1) {
			$this->db->where('question_id', $question_id);
			$this->db->where('lang_id', $lang_id);
			$this->db->update("questions_lingual", $data);
			// Update Sub Questions
			$this->db->set ('type', $type);
			$this->db->set ('marks', $marks);
			$this->db->set ('time', $time);
			$this->db->set ('negmarks', $negmarks);
			$this->db->where ('parent_id', $question_id);
			$this->db->update("questions_lingual");
			return $question_id ;
		} else {
			$data['creation_date'] = time ();
			$data['created_by'] = $this->session->userdata ('member_id');
			$id = $this->db->insert("questions_lingual", $data);
			if ($id) {
				 return $this->db->insert_id();
			} else {
				return false;
			}
		}
	}
	
/*Get question languages start*/
	public function getQuestion_languages($question_id = 0,$parent_id = 0){
		$this->db->select('lang_id');
		$this->db->where('question_id',$question_id);
		$this->db->where('parent_id',$parent_id);
		$query = $this->db->get('questions_lingual');
		return $query->result_array();
	}	
/*Get question languages end */
	
	/*
	// Get details of all selected question
	*/
	public function get_questions_heading ($question_ids=false) {
		$return = array ();
		if ( ! empty ($question_ids)) {
			$this->db->where_in ( 'question_id', $question_ids );
			$this->db->group_by ( 'parent_id' );
			$query = $this->db->get ('coaching_questions');
			if ($query->num_rows() > 0 ) {
				$result = $query->result_array();
				foreach ($result as $q) {
					$parent_id = $q['parent_id'];
					$row = $this->getQuestionDetails ($parent_id);
					$row['question'] = entities_to_ascii ($row['question']);
					for ($i=1; $i<=6; $i++) {
						$row['choice_'.$i] = entities_to_ascii  (($row['choice_'.$i]));
						$row['option_'.$i] = entities_to_ascii  (($row['option_'.$i]));
					}
					$return[] = $row;
				}
				return $return;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}	
	
	
	public function questions_in_lesson ($lesson_id=0) {
		$results = $this->getQuestions ($lesson_id);		
		$questions_in_lesson = 0;
		if ( ! empty ($results) ) {
			foreach ($results as $question_heading=>$questions) { 
				$num_questions = count ($questions);
				$questions_in_lesson = $questions_in_lesson + $num_questions;
			}
		}
		return $questions_in_lesson;
	}
	
	/* CATEGORIES FUNCTIONS */
	// Create/Edit Category
	public function save_category ($lesson_id=0, $category_id=0) {
		$title 		= $this->input->post ('title');
		$data = array (
					'title'	=>$title
				);
		
		if ($category_id > 0) {
			$this->db->where('id', $category_id);
			$this->db->update("question_categories", $data);
			return $category_id ;
		} else {
			$data['parent_id'] = 0;
			$data['level'] = 1;
			$data['created_by'] = $this->session->userdata ('member_id');
			$this->db->insert("question_categories", $data);
			return $this->db->insert_id();			
		}
	}

	// Create Category
	public function get_categories ($lesson_id=0, $category_id=0) {
		if ($lesson_id > 0) {
			$this->db->where('lesson_id', $lesson_id);
		}
		if ($category_id > 0) {
			$this->db->where('id', $category_id);			
		}
		$sql = $this->db->get ("question_categories");
		if ($sql->num_rows()) {
			return $sql->result_array ();
		} else {
			return false;
		}
	}

	// Create Category
	public function delete_category ($category_id=0) {
		// Unset questions first
		$this->db->set('category_id', 0);
		$this->db->where('category_id', $category_id);
		$sql = $this->db->update ('coaching_questions');

		// Delete Category
		$this->db->where('id', $category_id);
		$sql = $this->db->delete ("question_categories");
	}

	/* QUESTION FUNCTIONS */
	public function save_group ($coaching_id=0, $course_id=0, $question_id=0) {
		$marks 			= $this->input->post ('marks');
		$course_id 		= ($course_id==0)?null:$course_id;
		$chapter_id		= 0;
		$type 			= 0;
		$time 			= 0;
		$negmarks 		= 0;
		$lang 			= 0;
		$data = array(
					'coaching_id'			=>$coaching_id,
					'lang_id'				=>$lang,
					'type'					=>$type,
					'marks'					=>$marks,
					'time'					=>$time,
					'negmarks'				=>$negmarks,
					'chapter_id'			=>$chapter_id,
					'course_id'				=>$course_id,
					'question'				=>ascii_to_entities($this->input->post('question')),
					'question_feedback'		=>'',
					'answer_feedback'		=>'',
					'parent_id'				=>'0',
					'choice_1'				=>'',  
					'choice_2'				=>'',
					'choice_3'				=>'',
					'choice_4'				=>'',
					'choice_5'				=>'', 
					'choice_6'				=>'',
					'option_1'				=>'',  
					'option_2'				=>'',
					'option_3'				=>'',
					'option_4'				=>'',
					'option_5'				=>'', 
					'option_6'				=>'',
					'answer_1'				=>0,
					'answer_2'				=>0,
					'answer_3'				=>0,
					'answer_4'				=>0,
					'answer_5'				=>0,
					'answer_6'				=>0,
				);
		
		if ($question_id > 0) {
			$this->db->where('question_id', $question_id);
			$this->db->update('coaching_questions', $data);			
			return $question_id ;
		} else {
			$data['creation_date'] = time ();
			$data['created_by'] = $this->session->userdata ('member_id');
			$this->db->insert('coaching_questions', $data);
			return $this->db->insert_id();			
		}
	}

	
	public function save_question ( $coaching_id=0, $lesson_id=0, $parent_id=0, $question_id=0 ) {
		$choices    = $this->input->post('choice');		
		$answers    = $this->input->post('answer');
		$type 		= $this->input->post ('question_type');
		$option     = $this->input->post('option');	
		
		switch ($type) {
			// =======================			
			case QUESTION_TF:
				for ($i=1; $i<=6; $i++ ) {
					$option[$i] = '';
					if ($answers == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}
			break;
			
			// =======================			
			case QUESTION_MCMC:
				for ( $i=1; $i<=6; $i++ ) {
					$option[$i] = '';
					if (isset($answers[$i]) && $answers[$i] == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}
			break;
			
			// =======================			
			case QUESTION_MATCH:
				for ( $i=1; $i<=6; $i++ ) {
					if (isset($answers[$i]) && $answers[$i] == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}
			break;
			
			// =======================			
			case QUESTION_LONG:
				for ( $i=1; $i<=6; $i++ ) {
					if (isset($answers[$i]) && $answers[$i] == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
					if ($i > 2) $choices[$i] = '';
					$option[$i] = '';
				}
			break;

			// ========================
			default:
				for ($i=1; $i<=6; $i++ ) {
					$option[$i] = '';
					if ($answers == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}			
			break;			
		}

		$data = array (
					'coaching_id'		=>$coaching_id,
					'course_id'		=>$this->input->post('course_id'), 
					'clsf_id' 			=>$this->input->post('classification'),
					'diff_id' 			=>$this->input->post('difficulty'),
					'lang_id' 			=>$this->input->post('language'),
					'question'			=>ascii_to_entities ($this->input->post('question')), 
					'chapter_id'		=>$lesson_id,
					'type'				=>$type,
					'answer_1'			=> ($answer[1]),
					'answer_2'			=> ($answer[2]),
					'answer_3'			=> ($answer[3]),
					'answer_4'			=> ($answer[4]),
					'answer_5'			=> ($answer[5]),
					'answer_6'			=> ($answer[6]),
					'choice_1'			=>ascii_to_entities ($choices[1]),
					'choice_2'			=>ascii_to_entities ($choices[2]),
					'choice_3'			=>ascii_to_entities ($choices[3]),
					'choice_4'			=>ascii_to_entities ($choices[4]),
					'choice_5'			=>ascii_to_entities ($choices[5]), 
					'choice_6'			=>ascii_to_entities ($choices[6]),
					'option_1'			=>ascii_to_entities ($option[1]),  
					'option_2'			=>ascii_to_entities ($option[2]),
					'option_3'			=>ascii_to_entities ($option[3]),
					'option_4'			=>ascii_to_entities ($option[4]),
					'option_5'			=>ascii_to_entities ($option[5]), 
					'option_6'			=>ascii_to_entities ($option[6]),
					'properties'		=>0, 
					'marks'				=>$this->input->post('marks'), 
					'time'				=>$this->input->post('time'), 
					'negmarks'			=>$this->input->post('negmarks'), 
					'question_feedback'	=>ascii_to_entities ($this->input->post('question_feedback')), 
					'answer_feedback'	=>ascii_to_entities ($this->input->post('answer_feedback')), 
					'parent_id'			=>$parent_id,
					'created_by'		=>$this->session->userdata ('member_id'),
					'creation_date'		=>time ()
				);
		
		if ( $question_id > 0 ) { // Edit question mode
			$save_type = $this->input->post('save_type'); 			
			if ( $save_type == 'save_new' ) {					// save same question as a new question
				$this->db->insert('coaching_questions', $data);
				$qid = $this->db->insert_id ();
				return $qid; 
			} else {												// update question
				$this->db->where('question_id', $question_id);
				$sql = $this->db->update ('coaching_questions', $data);
				return $question_id;
			}
		} else {
			$this->db->insert('coaching_questions', $data);			// to insert new question 
			$qid = $this->db->insert_id ();
			return $qid;
		}
	}
	
	
	public function save_question_lingual ( $lesson_id=0, $parent_id=0, $question_id=0,$lang_id = 0,$edit = 0 ) {
		$choices    = $this->input->post('choice');		
		$answers    = $this->input->post('answer');
		$type 	    = $this->input->post ('type_id');
		$option     = $this->input->post('option');	
		
		switch ($type) {
			// =======================			
			case QUESTION_TF:
				for ($i=1; $i<=6; $i++ ) {
					$option[$i] = '';
					if ($answers == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}
			break;
			
			// =======================			
			case QUESTION_MCMC:
				for ( $i=1; $i<=6; $i++ ) {
					$option[$i] = '';
					if (isset($answers[$i]) && $answers[$i] == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}
			break;
			
			// =======================			
			case QUESTION_MATCH:
				for ( $i=1; $i<=6; $i++ ) {
					if (isset($answers[$i]) && $answers[$i] == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}
			break;
			
			// =======================			
			case QUESTION_LONG:
				for ( $i=1; $i<=6; $i++ ) {
					if (isset($answers[$i]) && $answers[$i] == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
					if ($i > 2) $choices[$i] = '';
					$option[$i] = '';
				}
			break;

			// ========================
			default:
				for ($i=1; $i<=6; $i++ ) {
					$option[$i] = '';
					if ($answers == $i) {
						$answer[$i] = $i;
					} else {
						$answer[$i] = 0;
					}
				}			
			break;
			
		}
		$data = array (
					'category_id'		=>$this->input->post('category'),
					'clsf_id'			=>$this->input->post('classification'),
					'diff_id' 			=>$this->input->post('difficulty'),
					'lang_id' 			=>$this->input->post('language'),
					'question_id'		=>$question_id,
					'chapter_id'		=>$lesson_id,
					'type'				=>$type,
					'answer_1'			=> ($answer[1]),
					'answer_2'			=> ($answer[2]),
					'answer_3'			=> ($answer[3]),
					'answer_4'			=> ($answer[4]),
					'answer_5'			=> ($answer[5]),
					'answer_6'			=> ($answer[6]),
					'choice_1'			=>ascii_to_entities ($choices[1]),
					'choice_2'			=>ascii_to_entities ($choices[2]),
					'choice_3'			=>ascii_to_entities ($choices[3]),
					'choice_4'			=>ascii_to_entities ($choices[4]),
					'choice_5'			=>ascii_to_entities ($choices[5]), 
					'choice_6'			=>ascii_to_entities ($choices[6]),
					'option_1'			=>ascii_to_entities ($option[1]),  
					'option_2'			=>ascii_to_entities ($option[2]),
					'option_3'			=>ascii_to_entities ($option[3]),
					'option_4'			=>ascii_to_entities ($option[4]),
					'option_5'			=>ascii_to_entities ($option[5]), 
					'option_6'			=>ascii_to_entities ($option[6]),
					'properties'		=>($this->input->post('properties')), 
					'marks'				=>$this->input->post('marks'),  
					'time'				=>$this->input->post('time'), 
					'negmarks'			=>$this->input->post('negmarks'), 
					'question'			=>ascii_to_entities ($this->input->post('question')), 
					'question_feedback'	=>ascii_to_entities ($this->input->post('question_feedback')), 
					'answer_feedback'	=>ascii_to_entities ($this->input->post('answer_feedback')), 
					'parent_id'			=>$parent_id,
					'created_by'		=>$this->session->userdata ('member_id'),
					'creation_date'		=>time ()
				);
		if ( $question_id > 0 ) { // Edit question mode
			$save_type = $this->input->post('save_type'); 
			if ( $save_type == 'save_new' ) {					// save same question as a new question
				$this->db->insert("questions_lingual", $data);
				$qid = $this->db->insert_id ();
				return $qid; 
			} else {		// update question
				if($edit){
					$this->db->where('question_id', $question_id);
					$sql = $this->db->update ("questions_lingual", $data);
					}
				else{
					$this->db->insert("questions_lingual", $data); // to insert new question 
					}				
				return $question_id;
			}  
		} 
	}
	
	
	
	//============= Model for delete question =====================
	public function delete_questions ($qid) {
		$this->db->where('question_id', $qid);
		$this->db->delete('coaching_questions');

		$this->db->where('parent_id', $qid);
		$this->db->delete('coaching_questions');
	}
	
	//============= Model for delete question =====================
	public function delete_lesson_questions ($lesson_id) {
		$this->db->where('chapter_id', $lesson_id);
		$this->db->delete('coaching_questions');
	}
	

	//============= get a list of tests in which a question is added =====================
	public function questionInAnyTest ( $qid ) {
		$this->db->select ("question_id, test_id");
		$this->db->where ("question_id", $qid); 
		$q = $this->db->get ("coaching_test_questions");
		if ( $q->num_rows() > 0 ) {
			return $q->result_array();
		} else {
			return false;
		} 	
	}
	
	// tests taken within range
	public function questions_created_between ($start_date=0) {
		
		$result = array ();		
		
		// current timestamp
		$today = time ();
		
		// start date
		if ($start_date == 0) {
			$end_date = time ();
			// 7 days tests from today
			for ($i=0; $i<7; $i++) {
				$count = 0;
				$today_midnight = mktime (0, 0, 0, date ('m'), date ('d')-$i, date ('Y')); 				
				$start_date = $today_midnight;
				
				$this->db->where ('creation_date >= ', $start_date );
				$this->db->where ('creation_date < ',  $end_date);
				$this->db->where ('parent_id >', 0);
				$sql = $this->db->get ('coaching_questions');
				if ($sql->num_rows () > 0) {
					$count = $sql->num_rows ();					
				}
				$result[$end_date] = $count;
				$end_date = $start_date - 1;			// minus one second
			}
			$result = array_reverse ($result, true);
			return $result;
		} else {
			$end_date = $today;
			$this->db->where ('creation_date >= ', $start_date );
			$this->db->where ('creation_date <= ',  $end_date);
			$this->db->where ('parent_id >', 0);
			$sql = $this->db->get ('coaching_questions');
			$count = $sql->num_rows ();
			$result[$end_date] = $count;
		}
		
	}
	
	public function count_all_questions () {
		$this->db->where ('parent_id >', 0 );
		$this->db->from('coaching_questions');
		$num = $this->db->count_all_results(); 
		return $num;
	}
	
	public function questions_in_category ($category_id='', $clsf_id='', $diff_id='') {
		if ($category_id != NULL) {
			$this->db->where ('category_id', $category_id );
			$this->db->where ('parent_id', 0 );
		}
		if ($clsf_id != NULL) {
			$this->db->where ('clsf_id', $clsf_id );
			$this->db->where ('parent_id >', 0 );
		}
		if ($diff_id != NULL) {
			$this->db->where ('diff_id', $diff_id );
			$this->db->where ('parent_id >', 0 );
		}
		
		$sql = $this->db->get ('coaching_questions');
		$this->db->last_query ();
		$num = $sql->num_rows (); 
		return $num;
	}
	
	//============= get a list of tests in which a question is added, excluding given test =====================
	public function questionInOtherTests ( $qid, $test_id ) {
		$this->db->select("question_id, test_id");
		$this->db->where("question_id", $qid);
		$this->db->where("test_id <>", $test_id);
		$q = $this->db->get("coaching_test_questions");
		if ( $q->num_rows() > 0 ) {
			return $q->result_array();
		} else {
			return false;
		} 	
	}

	/* IMPORT AND SAVE QUESTIONS FROM TEXT */
	public function import_text_save_group ($coaching_id=0, $course_id=0, $question=[], $type=QUESTION_MCSC) {
		
		$data = array ();
		//$data = array_fill (0, 30, '');
		$marks = 1;
		$negmarks = 0;
		$time = 120;
		if ( isset($question['marks'])) {
			$marks = $question['marks'];
		}
		if ( isset($question['negmarks'])) {
			$negmarks = $question['negmarks'];
		}
		if ( isset($question['time'])) {
			$time = $question['time'];
		}
		if ( isset($question['question']) && $question['question'] != '' ) {
			$quest = xss_clean(trim ($question['question']));
		}
		$data['coaching_id'] 		= $coaching_id;
		$data['course_id'] 			= $course_id;
		$data['chapter_id'] 		= 0;
		$data['created_by'] 		= $this->session->userdata ('member_id');
		$data['creation_date'] 		= time ();
		$data['type'] 				= $type;
		$data['marks'] 				= $marks;
		$data['time'] 				= $time;
		$data['negmarks'] 			= $negmarks;
		$data['parent_id'] 			= 0;		
		$data['question'] 			= '<p>'.$quest.'</p>';
			
		$this->db->insert('coaching_questions', $data);
		return $this->db->insert_id();
		
	}

	
	public function import_text_save_question ( $coaching_id=0, $course_id=0, $parent_id=0, $questions=[], $test_id=0 ) {
		$data = array ();
		if ($test_id > 0) {
			$this->load->model ('tests/tests_model');
		}
		if ( ! empty ($questions) ) {
			foreach ($questions as $question) {
				$data = $question;
				// Question type
				$count_choice = 0;
				$count_answer = 0;
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					if ( isset($question['choice_'.$i]) && $question['choice_'.$i] != "" ) {
						$question['choice_'.$i] = '<p>'.$question['choice_'.$i].'</p>';
						$count_choice++;
					}
					if ( isset($question['answer_'.$i]) ) {
						$question['answer_'.$i] = '<p>'.$question['answer_'.$i].'</p>';
						$count_answer++;
					}
				}
				
				if ($count_choice == 0) {
					$type = QUESTION_LONG;
				} else if ($count_choice == 2) {
					$type = QUESTION_TF;
				} else if ($count_choice >= 3) {
					if ($count_answer > 1) {
						$type = QUESTION_MCMC;
					} else {
						$type = QUESTION_MCSC;
					}
				} else {
					$type = QUESTION_MCSC;
				}
				
				if ( isset($question['answer_feedback']) ) {
					$question['answer_feedback'] = '<p>'.$question['answer_feedback'].'</p>';
				}
				if ( isset($question['question_feedback']) ) {
					$question['question_feedback'] = '<p>'.$question['question_feedback'].'</p>';
				}
					
				if ( ! isset ($data['clsf_id'])) {
					$data['clsf_id'] = 1;
				}
				if ( ! isset ($data['category_id'])) {
					$data['category_id'] = 2;
				}

				$data['question'] = '<p>'.$data['question'].'</p>';
				$data['type'] = $type;
				$data['coaching_id'] = $coaching_id; 
				$data['course_id'] = $course_id; 
				$data['chapter_id'] = 0; 
				$data['parent_id'] = $parent_id;
				$data['creation_date'] = time ();
				$data['created_by'] = $this->session->userdata ('member_id');
				$this->db->insert('coaching_questions', $data);			// to insert new question 
				$qid = $this->db->insert_id ();
				if ($test_id  > 0) {
					$this->tests_model->saveQuestionsSimple ($qid, $test_id);
				}
			}
		}
	}

	
	public function upload_questions_csv ($lesson_id=0, $parent_id=0, $data=[]) {
		$datum = array ();
		$question = array ();
		$default = array_fill (0, 15, '');
		$num = count($data);
		$i = 0;
		for ($c=0; $c < $num; $c++) {
			if ($data[$c] != '') {
				$i++;
				$datum[$c] = $data[$c];
			} else {
				//$datum[$c] = $default[$c];				
			}
		}
		$question['type'] 				= QUESTION_MCSC;
		$question['parent_id'] 			= $parent_id;
		$question['chapter_id'] 		= $lesson_id;
		$question['creation_date'] 		= time ();
		$question['created_by'] 		= $this->session->userdata ('member_id');
		if ($i >= 2 && $i <= 4 ) {
			// This is a question group
			$question['question'] 	= '<p>'.$datum[0].'</p>';
			$question['marks'] 		= $datum[1];		
			$question['negmarks'] 	= $datum[2];
			$question['time'] 		= $datum[3];
			$this->db->insert ('coaching_questions', $question);
			$id = $this->db->insert_id ();
		} else {
			// This is a question
			$answers = array ();
			$title 	= xss_clean (trim($datum[0]));
			$question['question'] 	= '<p>'.$title.'</p>';
			$question['marks'] 		= xss_clean (trim($datum[1]));
			$question['negmarks'] 	= xss_clean (trim($datum[2]));
			$question['time'] 		= xss_clean (trim($datum[3]));
			// Answer Choices
			$answers[1] = $question['choice_1'] 	= xss_clean (trim($datum[4]));
			$answers[2] = $question['choice_2'] 	= xss_clean (trim($datum[5]));
			$answers[3] = $question['choice_3'] 	= xss_clean (trim($datum[6]));
			$answers[4] = $question['choice_4'] 	= xss_clean (trim($datum[7]));
			$answers[5] = $question['choice_5'] 	= xss_clean (trim($datum[8]));
			$answers[6] = $question['choice_6'] 	= xss_clean (trim($datum[9]));
			// Correct Answer
			$correct_answer	= xss_clean (trim($datum[10]));
			$key = array_search ($correct_answer, $answers); 
			$question['answer_'.$key] = $key;
			//print_r ($answers);
			//$output .= '<br>';
			//exit;
			$question['answer_feedback']	= xss_clean (trim($datum[11]));
			$question['question_feedback']	= xss_clean (trim($datum[12]));
			// Category
			$category	= xss_clean (trim($datum[13]));
			$param = $this->common_model->sys_parameter_name (SYS_QUESTION_CLASSIFICATION, $category);
			if (is_array($param)) {
				$question['category_id'] = $param['paramkey'];
			} else {
				$question['category_id'] = 0;				
			}
			// Difficulty
			$difficulty	= xss_clean (trim($datum[14]));
			$param = $this->common_model->sys_parameter_name (SYS_QUESTION_DIFFICULTIES, $difficulty);
			if (is_array($param)) {
				$question['clsf_id'] = $param['paramkey'];
			} else {
				$question['clsf_id'] = 0;				
			}
			$this->db->insert ('coaching_questions', $question);
			$id = $parent_id;
		}
		return $id;
	}
	
	
	public function copy_temp () {
		
		$sql2 = $this->db->get ('lessons');
		foreach ($sql2->result_array () as $chapter) {
			$data = array ();
			$old_cid = $chapter['id'];
			$data['title'] = $chapter['title'];
			$data['parent_id'] = $chapter['subject_id'];
			$data['level'] = 5;
			$sql = $this->db->insert ('question_categories', $data);
			$new_cid = $this->db->insert_id ();
			
			$this->db->set ('chapter_id', $new_cid);
			$this->db->where ('chapter_id', $old_cid);
			$sql3 = $this->db->update ('coaching_questions');
		}
	}
	
	/*---=== Dashbaord Sidebar Items ===---*/
	public function dashboard_sidebar () {
		
		$output = '';
		return $output;
	}

	/*---=== Dashbaord Main Items ===---*/
	public function dashboard_main () {
		
	}
	
	public function text_for_export ($lesson_id=0, $cat_ids=0, $diff_ids=0, $exclude=0) {
		
		$results = $this->qb_model->getQuestions ($lesson_id, $cat_ids, $diff_ids, $exclude);		
		$member_id 	= $this->session->userdata ('member_id');
		$format 	= $this->input->post ('import_format');
		$print_opt 	= $this->input->post ('print_opt');
		$file_type 	= $this->input->post ('file_type');

		$content = '';
		if ( ! empty ($results)) {
			$h = 1;
			$num_questions = 0;
			$content .= '<ol>';
			foreach ($results as $question_heading=>$questions) { 
				/* ---=== QUESTION HEADING ===--- */
				$heading = $this->qb_model->getQuestionDetails ($question_heading);
				$num_questions = count ($questions);
				$question_type = $this->common_model->sys_parameter_name (SYS_QUESTION_CLASSIFICATION, $heading['type']);
				$qh = ($heading['question']); 
				/* Remove all formatting/HTML Tags for .txt file export
					Reason: TXT File displays tags as it is.
				*/ 
				
				$content .= '<li list-style-type="decimal">';
				
					if ($file_type == 'txt') {
						$qh = str_replace ('<br>', "\r\n", $qh);
						$qh = strip_tags ($qh);
					}
					if ($format == 'import') {
						// Prepare for import file
						if ($file_type == 'txt') {
							$content .= '#QH ' . $qh;
						} else {
							$content .= '<div class="pull-left">';
								$content .= '#QH ' . $qh;
							$content .= '</div>';
						}
					} else {
						// Prepare for print file
						if ($file_type == 'txt') {
							$content .= $h . ') ' . $qh;
						} else {
							$content .= $qh;
						}
					}
					if ($file_type == 'txt') {
						$content .= "\r\n";
					}
					
					/* ---=== MARKS ===--- */
					// Remove HTML tags for text file
					$ma = $heading['marks'];
					if ($format == 'import') {
						// Prepare for import file
						$content .= '@QM ' . $ma;
					} else {
						if ( isset ($print_opt['ma']) && $print_opt['ma'] == 'ma') {
							// Prepare for print file
							$content .= '[MARKS: ' . $ma . ']';
						}
					}
					$content .= "\r\n";
					
					/* ---=== TIME ===--- */
					// Remove HTML tags for text file
					$tm = $heading['time'];
					if ($format == 'import') {
						// Prepare for import file
						$content .= '@QT ' . $tm;
					} else {
						if ( isset ($print_opt['tm']) && $print_opt['tm'] == 'tm') {
							// Prepare for print file
							$content .= '[TIME: ' . $tm . ']';
						}
					}
					$content .= "\r\n";

					/* ---=== NEGATIVE MARKS ===--- */
					// Remove HTML tags for text file
					$nm = $heading['negmarks'];
					if ($format == 'import') {
						// Prepare for import file
						$content .= '@NM ' . $tm;
					} else {
						if ( isset ($print_opt['nm']) && $print_opt['nm'] == 'nm') {
							// Prepare for print file
							$content .= '[NEG MARKS: ' . $nm . ']';
						}
					}
					$content .= "\r\n";
					
					$numq = 1;
					$content .= "<ol>";
					
						foreach ($questions as $row ) {
							$content .= "<li>";							
								/* ---=== QUESTION ===--- */
								//$content .= $numq .') ';
								$question = $row['question'];
								// Remove HTML tags for text file
								if ($file_type == 'txt') {
									$question = str_replace ('<br>', "\r\n", $question);
									$question = strip_tags ($question);
								}
								$content .= $question;
								if ($file_type == 'txt') {
									$content .= "\r\n";
								}
								
								/* ---=== QUESTION FEEDBACK ===--- */
								// Remove HTML tags for text file
								$qf = $row['question_feedback'];
								if ($file_type == 'txt') {
									$qf = str_replace ('<br>', "\r\n", $qf);
									$qf = strip_tags ($qf);
								}
								if ($format == 'import') {
									// Prepare for import file
									$content .= '@QF ' . $qf;
								} else {
									if ( isset ($print_opt['qf']) && $print_opt['qf'] == 'qf') {
										// Prepare for print file
										$content .= '[QUESTION HINT: ' . $qf . ']';
									}
								}
								if ($file_type == 'txt') {
									$content .= "\r\n";
								}

								/* ---=== ANSWER CHOICES ===--- */
								$answers = $this->display_answer_choices ($row['type'], $row);
								// Remove HTML tags for text file
								if ($file_type == 'txt') {
									$answers = str_replace ('<br>', "\r\n", $answers);
									$answers = strip_tags ($answers);
								}
								$content .= $answers;
								if ($file_type == 'txt') {
									$content .= "\r\n";						
								}
								
								/* ---=== ANSWER FEEDBACK ===--- */
								// Remove HTML tags for text file
								$af = $row['answer_feedback'];
								if ($file_type == 'txt') {
									$qf = str_replace ('<br>', "\r\n", $af);
									$af = strip_tags ($af);
								}
								if ($format == 'import') {
									// Prepare for import file
									$content .= '@AF ' . $af;
								} else {
									if ( isset ($print_opt['af']) && $print_opt['af'] == 'af') {
										// Prepare for print file
										$content .= '[ANSWER FEEDBACK: ' . $af . ']';
									}
								}
								if ($file_type == 'txt') {
									$content .= "\r\n";
								}
								$numq++;
							$content .= '</li>';
						}
						$h++;
					$content .= '</ol>';
					
				$content .= '</li>';
			}
			$content .= '</ol>';
		}
		if ($file_type == 'txt') {
			$content = strip_tags ($content);
		}
		return $content;
	}
	
	public function display_answer_choices ($type=QUESTION_MCSC, $row=[]) {

		$format 	= $this->input->post ('import_format');
		$print_opt 	= $this->input->post ('print_opt');
		$file_type 	= $this->input->post ('file_type');

		$output = '';
		switch ($type) {

			case QUESTION_MCMC:
				$output .= '<ol type="a">';
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && ! isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
						$output .= '<li class="'.$class.'">';
							$output .= $choice;
						$output .= '</li>';
					}
				} 
				$output .= '</ol>';
			break;
			
			case QUESTION_TF: 
				$output .= '<ol type="a">';
					if ($row['answer_1'] == 1 && !isset ($hide_correct_answer) ) {
						$class = "text-success";
					} else {
						$class= "";
					}
					$output .= '<li class="'.$class.'">True</li>';
					
					if ($row['answer_2'] == 2 && !isset ($hide_correct_answer) ) {
						$class = "text-success text-bold";
					} else {
						$class= "";
					}
					$output .= '<li class="'.$class.'">False</li>';
				$output .= '</ol>';
			break;
			
			case QUESTION_LONG:
				for ($i=1; $i <= 2; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && !isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
					}
				} 
			break;
			
			case QUESTION_MATCH:												
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					$option = html_entity_decode ($row['option_'.$i]);
					if ($choice != '' || $option != '') {
						$output .= '<div class="row">';
							$output .= '<div class="col-md-6">';
								$output .= $choice;
							$output .= '</div>';
							$output .= '<div class="col-md-6">';
								$output .= $option;
							$output .= '</div>';
						$output .= '</div>';
					}
				} 													
			break;
			
			default :
				$a = 'a';
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						$output .= '<ul class="list-unstyled">';
							if ( ($row['answer_'.$i] == $i) &&
								 ((isset ($format) && $format == 'import') ||
								 (isset ($print_opt['ca']) && $print_opt['ca'] == 'ca'))) {
								$output .= "*";
							}
							if ($row['answer_'.$i] == $i && ! isset ($hide_correct_answer) ) {
								$class = "text-success";
							} else {
								$class= "";
							}
							$output .= '<li class="'.$class.'">';
								$output .= '<div class="media">';
									$output .= '<div class="media-left">';
										$output .=  $a++ . '.';
									$output .= '</div>';
									$output .= '<div class="media-body">';
										$output .= $choice;
									$output .= '</div>';
								$output .= '</div>';
							$output .= '</li>';
							
							if ($file_type == 'txt') {
								$output .= "\r\n";
							}
						$output .= '</ul>';
					}
				}
			break;
		}
		
		return $output;
	}
	
	public function pdf_answer_choices ($type=QUESTION_MCSC, $row=[]) {

		$format 	= $this->input->post ('import_format');
		$print_opt 	= $this->input->post ('print_opt');
		$file_type 	= $this->input->post ('file_type');

		$output = '';
		
		switch ($type) {

			case QUESTION_MCMC:
				$output .= '<ol type="a">';
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && ! isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
						$output .= '<li class="'.$class.'">';
							$output .= $choice;
						$output .= '</li>';
					}
				} 
				$output .= '</ol>';
			break;
			
			case QUESTION_TF: 
				$output .= '<ol type="a">';
					if ($row['answer_1'] == 1 && !isset ($hide_correct_answer) ) {
						$class = "text-success";
					} else {
						$class= "";
					}
					$output .= '<li class="'.$class.'">True</li>';
					
					if ($row['answer_2'] == 2 && !isset ($hide_correct_answer) ) {
						$class = "text-success text-bold";
					} else {
						$class= "";
					}
					$output .= '<li class="'.$class.'">False</li>';
				$output .= '</ol>';
			break;
			
			case QUESTION_LONG:
				for ($i=1; $i <= 2; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && !isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
					}
				} 
			break;
			
			case QUESTION_MATCH:												
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					$option = html_entity_decode ($row['option_'.$i]);
					if ($choice != '' || $option != '') {
						$output .= '<div class="row">';
							$output .= '<div class="col-md-6">';
								$output .= $choice;
							$output .= '</div>';
							$output .= '<div class="col-md-6">';
								$output .= $option;
							$output .= '</div>';
						$output .= '</div>';
					}
				} 													
			break;
			
			default :
				$a = 'a';
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
							$output .= '<p>';
							if ( ($row['answer_'.$i] == $i) &&
								 ((isset ($format) && $format == 'import') ||
								 (isset ($print_opt['ca']) && $print_opt['ca'] == 'ca'))) {
								$output .= "*";
							}
							$output .=  $a++ . '. ';
							$output .= strip_tags($choice);
							$output .= '</p>';
							
							if ($file_type == 'txt') {
								$output .= "\r\n";
							}
					}
				}
			break;
		}
		return $output;
	}

	public function print_answer_choices ($type=QUESTION_MCSC, $row=false, $format='PDF', $file_type='') {
		$print_opt 	= $this->input->post ('print_opt');

		$output = '';
		switch ($type) {

			case QUESTION_MCMC:
				$output .= '<ol type="a">';
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && !isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
						$output .= '<li class="'.$class.'">';
							$output .= $choice;
						$output .= '</li>';
					}
				} 
				$output .= '</ol>';
			break;
			
			case QUESTION_TF: 
				$output .= '<ol type="a">';
					if ($row['answer_1'] == 1 && !isset ($hide_correct_answer) ) {
						$class = "text-success";
					} else {
						$class= "";
					}
					$output .= '<li class="'.$class.'">True</li>';
					
					if ($row['answer_2'] == 2 && !isset ($hide_correct_answer) ) {
						$class = "text-success text-bold";
					} else {
						$class= "";
					}
					$output .= '<li class="'.$class.'">False</li>';
				$output .= '</ol>';
			break;
			
			case QUESTION_LONG:
				for ($i=1; $i <= 2; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && !isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
					}
				} 
			break;
			
			case QUESTION_MATCH:												
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					$option = html_entity_decode ($row['option_'.$i]);
					if ($choice != '' || $option != '') {
						$output .= '<div class="row">';
							$output .= '<div class="col-md-6">';
								$output .= $choice;
							$output .= '</div>';
							$output .= '<div class="col-md-6">';
								$output .= $option;
							$output .= '</div>';
						$output .= '</div>';
					}
				} 													
			break;
			
			default :
				$a = 'a';
				for ($i=1; $i <= QB_NUM_ANSWER_CHOICES; $i++) {
					$choice = html_entity_decode ($row['choice_'.$i]);
					if ($choice != '') {
						if ($row['answer_'.$i] == $i && ! isset ($hide_correct_answer) ) {
							$class = "text-success";
						} else {
							$class= "";
						}
						$output .= '<div class="'.$class.'">';
							if ( ($row['answer_'.$i] == $i) &&
								 ((isset ($format) && $format == 'import') ||
								 (isset ($print_opt['ca']) && $print_opt['ca'] == 'ca'))) {
								$output .= "*";								
							}
							$output .= '<span class="pull-left">'.$a++ .".</span> ";
							$output .= $choice;
							if ($file_type == 'txt') {
								$output .= "\r\n";
							}
						$output .= '</div>';
					}
				}
			break;
		}
		
		return $output;
	}
	
	
	/*---=== Page Specific Statistics ===---*/
	public function page_stats ($page='index', $lesson_id=0, $parent_id=0, $question_id=0) {
		$lesson_id = 0;
		$stats = array (); 
		switch ($page) {
			case 'question_group_create';
				$level = $this->common_model->node_details ($lesson_id, SYS_TREE_TYPE_QB);
				$subpage_title = $level['title'];
				// Questions in lesson
				$num_questions = $this->qb_model->questions_in_lesson ($lesson_id);
				// Group Questions
				$questions = $this->get_heading_questions ($lesson_id);
				$g_questions = count ($questions);
				$stats = array ('subpage_title'=>$subpage_title, 'pst1'=>'coaching_questions', 'psf1'=>$num_questions, 'pst2'=>'Question Groups', 'psf2'=>$g_questions ); 
			break;
			
			case 'question_create';
				$level = $this->common_model->node_details ($lesson_id, SYS_TREE_TYPE_QB);
				$subpage_title = $level['title'];
				// Questions in lesson
				$l_questions = $this->questions_in_lesson ($lesson_id);
				// Questions in selected group
				$questions = $this->get_sub_questions ($lesson_id, $parent_id);
				$g_questions = count ($questions);
				$stats = array ('subpage_title'=>$subpage_title, 'pst1'=>'coaching_questions', 'psf1'=>$l_questions , 'pst2'=>'Question Groups', 'psf2'=>$g_questions ); 
			break;
			
			default;
				$level = $this->common_model->node_details ($lesson_id, SYS_TREE_TYPE_QB);
				$subpage_title = $level['title'];
				$num_questions = $this->qb_model->questions_in_lesson ($lesson_id);
				// Group Questions
				$questions = $this->get_heading_questions ($lesson_id);
				$g_questions = count ($questions);
				$stats = array ('subpage_title'=>$subpage_title, 'pst1'=>'coaching_questions', 'psf1'=>$num_questions, 'pst2'=>'Question Groups', 'psf2'=>$g_questions ); 
			break;
			
		}
		
		return $stats;

	}
	
	
	public function reorder_questions ($lesson_id=0) {
		$questions = $this->input->post ('coaching_questions');
		foreach ($questions as $parent_id=>$row) {
			$data = json_decode ($row, true);
			foreach ($data as $item) {
				$this->db->set ('parent_id', $parent_id);
				$this->db->where ('question_id', $item['id']);
				$sql = $this->db->update ('coaching_questions');
			}
		}
	}

	public function move_questions ($lesson_id=0, $copy=false) {
		$data   = array ();
		$items  = array ();
		$result = array ();
		$sub_questions 	= array ();
		$questions 		= $this->input->post ('mycheck');
		$new_lesson_id 	= $this->input->post ('new_lesson_id');
		
		foreach ($questions as $id) {
			$this->db->where ('question_id', $id);
			$sql = $this->db->get ('coaching_questions');
			if ($sql->num_rows () > 0 ) {
				$row = $sql->row_array ();
				$parent_id = $row['parent_id'];
				$data[$parent_id][] = $id;
			}
		}
		
		if ( ! empty ($data) ) {
			foreach ($data as $parent_id=>$row_items) {
				$sd = array ();
				// Total number of questions in this parent
				$sub_questions = $this->get_sub_questions ($lesson_id, $parent_id);
				if ( ! empty ($sub_questions)) {
					foreach ($sub_questions as $sq) {
						$sd[] = $sq['question_id'];
					}
				}
				
				$result = array_diff_assoc ($sd, $row);
				if ( empty ($result) && $copy == false) {
					$this->db->set ('chapter_id', $new_lesson_id);
					$this->db->where ('question_id', $parent_id);
					$this->db->or_where ('parent_id', $parent_id);
					$sql = $this->db->update ('coaching_questions');
				} else {
					$this->db->where ('question_id', $parent_id);
					$sql = $this->db->get ('coaching_questions');
					if ($sql->num_rows () > 0 ) {
						$row = $sql->row_array ();
						$row['question_id'] = NULL;
						$row['parent_id']   = 0;
						$row['chapter_id']  = $new_lesson_id;
						$query = $this->db->insert ('coaching_questions', $row);
						$pid = $this->db->insert_id ();
						foreach ($row_items as $id) {
							if ($copy == true) {
								// get question details
								$this->db->where ('question_id', $id);
								$sql = $this->db->get ('coaching_questions');
								if ($sql->num_rows () > 0 ) {
									$row = $sql->row_array ();
									$row['question_id'] = NULL;
									$row['parent_id'] 	= $pid;
									$row['chapter_id'] 	= $new_lesson_id;
									$query = $this->db->insert ('coaching_questions', $row);
								}
							} else {
								$this->db->set ('chapter_id', $new_lesson_id);
								$this->db->set ('parent_id', $pid);
								$this->db->where ('question_id', $id);
								$result = $this->db->update ('coaching_questions');								
							}
						}
					}

				}
			}
		}
		
		return $new_lesson_id;
		
	}



	// Function that checks whether the data are the on-screen text.
	// It works in the following way:
	// an array arrfailAt stores the control words for the current state of the stack, which show that
	// input data are something else than plain text.
	// For example, there may be a description of font or color palette etc.
	public function rtf_isPlainText($s) {
	    $arrfailAt = array("*", "fonttbl", "colortbl", "datastore", "themedata");
	    for ($i = 0; $i < count($arrfailAt); $i++)
	        if (!empty($s[$arrfailAt[$i]])) return false;
	    return true;
	}

	public function rtf2text($filename) {
	    // Read the data from the input file.
	    $text = file_get_contents($filename);
	    if (!strlen($text))
	        return "";

	    // Create empty stack array.
	    $document = "";
	    $stack = array();
	    $j = -1;
	    // Read the data character-by- character…
	    for ($i = 0, $len = strlen($text); $i < $len; $i++) {
	        $c = $text[$i];

	        // Depending on current character select the further actions.
	        switch ($c) {
	            // the most important key word backslash
	            case "\\":
	                // read next character
	                $nc = $text[$i + 1];

	                // If it is another backslash or nonbreaking space or hyphen,
	                // then the character is plain text and add it to the output stream.
	                if ($nc == '\\' && $this->rtf_isPlainText($stack[$j])) $document .= '\\';
	                elseif ($nc == '~' && $this->rtf_isPlainText($stack[$j])) $document .= ' ';
	                elseif ($nc == '_' && $this->rtf_isPlainText($stack[$j])) $document .= '-';
	                // If it is an asterisk mark, add it to the stack.
	                elseif ($nc == '*') $stack[$j]["*"] = true;
	                // If it is a single quote, read next two characters that are the hexadecimal notation
	                // of a character we should add to the output stream.
	                elseif ($nc == "'") {
	                    $hex = substr($text, $i + 2, 2);
	                    if (rtf_isPlainText($stack[$j]))
	                        $document .= html_entity_decode("&#".hexdec($hex).";");
	                    //Shift the pointer.
	                    $i += 2;
	                // Since, we’ve found the alphabetic character, the next characters are control word
	                // and, possibly, some digit parameter.
	                } elseif ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
	                    $word = "";
	                    $param = null;

	                    // Start reading characters after the backslash.
	                    for ($k = $i + 1, $m = 0; $k < strlen($text); $k++, $m++) {
	                        $nc = $text[$k];
	                        // If the current character is a letter and there were no digits before it,
	                        // then we’re still reading the control word. If there were digits, we should stop
	                        // since we reach the end of the control word.
	                        if ($nc >= 'a' && $nc <= 'z' || $nc >= 'A' && $nc <= 'Z') {
	                            if (empty($param))
	                                $word .= $nc;
	                            else
	                                break;
	                        // If it is a digit, store the parameter.
	                        } elseif ($nc >= '0' && $nc <= '9')
	                            $param .= $nc;
	                        // Since minus sign may occur only before a digit parameter, check whether
	                        // $param is empty. Otherwise, we reach the end of the control word.
	                        elseif ($nc == '-') {
	                            if (empty($param))
	                                $param .= $nc;
	                            else
	                                break;
	                        } else
	                            break;
	                    }
	                    // Shift the pointer on the number of read characters.
	                    $i += $m - 1;

	                    // Start analyzing what we’ve read. We are interested mostly in control words.
	                    $toText = "";
	                    switch (strtolower($word)) {
	                        // If the control word is "u", then its parameter is the decimal notation of the
	                        // Unicode character that should be added to the output stream.
	                        // We need to check whether the stack contains \ucN control word. If it does,
	                        // we should remove the N characters from the output stream.
	                        case "u":
	                            $toText .= html_entity_decode("&#x".dechex($param).";");
	                            $ucDelta = @$stack[$j]["uc"];
	                            if ($ucDelta > 0)
	                                $i += $ucDelta;
	                        break;
	                        // Select line feeds, spaces and tabs.
	                        case "par": case "page": case "column": case "line": case "lbr":
	                            $toText .= "\n";
	                        break;
	                        case "emspace": case "enspace": case "qmspace":
	                            $toText .= " ";
	                        break;
	                        case "tab": $toText .= "\t"; break;
	                        // Add current date and time instead of corresponding labels.
	                        case "chdate": $toText .= date("m.d.Y"); break;
	                        case "chdpl": $toText .= date("l, j F Y"); break;
	                        case "chdpa": $toText .= date("D, j M Y"); break;
	                        case "chtime": $toText .= date("H:i:s"); break;
	                        // Replace some reserved characters to their html analogs.
	                        case "emdash": $toText .= html_entity_decode("&mdash;"); break;
	                        case "endash": $toText .= html_entity_decode("&ndash;"); break;
	                        case "bullet": $toText .= html_entity_decode("&#149;"); break;
	                        case "lquote": $toText .= html_entity_decode("&lsquo;"); break;
	                        case "rquote": $toText .= html_entity_decode("&rsquo;"); break;
	                        case "ldblquote": $toText .= html_entity_decode("&laquo;"); break;
	                        case "rdblquote": $toText .= html_entity_decode("&raquo;"); break;
	                        // Add all other to the control words stack. If a control word
	                        // does not include parameters, set &param to true.
	                        default:
	                            $stack[$j][strtolower($word)] = empty($param) ? true : $param;
	                        break;
	                    }
	                    // Add data to the output stream if required.
	                    if ($this->rtf_isPlainText($stack[$j]))
	                        $document .= $toText;
	                }

	                $i++;
	            break;
	            // If we read the opening brace {, then new subgroup starts and we add
	            // new array stack element and write the data from previous stack element to it.
	            case "{":
	                array_push($stack, $stack[$j++]);
	            break;
	            // If we read the closing brace }, then we reach the end of subgroup and should remove
	            // the last stack element.
	            case "}":
	                array_pop($stack);
	                $j--;
	            break;
	            // Skip “trash”.
	            case '\0': case '\r': case '\f': case '\n': break;
	            // Add other data to the output stream if required.
	            default:
	                if ($this->rtf_isPlainText($stack[$j]))
	                    $document .= $c;
	            break;
	        }
	    }
	    // Return result.
	    return $document;
	}	
} 