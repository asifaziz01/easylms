<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Tests_model extends CI_Model {	

	public function __construct() {
		parent::__construct();
	} 
	
	public function generate_reference_id ($coaching_id=0, $course_id=0, $test_id=0) {
		$prefix = array ();
		$ref_prefix	= $this->common_model->get_sys_parameters (SYS_TST_ID_PREFIX);
		foreach ($ref_prefix as $row) {
			$prefix[] = $row['paramkey'];
		}
		
		if ($test_id > 0) {
			// This means member record is already inserted and the primary key is passed as test_id
			$num = $test_id;
		} else {
			// Member record is not yet inserted, show a 
			$this->db->select_max ('test_id');
			$sql = $this->db->get ('coaching_tests'); 
			if ($sql->num_rows () > 0) {
				$row = $sql->row_array ();
				$id = $row['test_id'];
			} else { 
				$id = 0;
			}
			$num = $id+1;
		}
		$suffix = str_pad($coaching_id, 3, "0", STR_PAD_LEFT);
		$suffix .= str_pad($course_id, 3, "0", STR_PAD_LEFT);
		$suffix .= str_pad($num, 3, "0", STR_PAD_LEFT);

		$ref_id = implode ('', $prefix);
		$ref_id = $ref_id . $suffix;
		return $ref_id;
	}
	
	/* Test Plan Categories */
	public function test_categories ($coaching_id=0, $plan_id=0, $status='-1') {
		if ($status > '-1') {
			$this->db->where ('status', $status);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('plan_id', $plan_id);
		$sql = $this->db->get ('coaching_test_categories');
		return $sql->result_array ();
	}

	public function coaching_courses ($coaching_id=0, $plan_id=0, $status='-1') {
		if ($status > '-1') {
			$this->db->where ('status', $status);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$sql = $this->db->get ('coaching_courses');
		return $sql->result_array ();
	}

	public function get_category ($course_id=0) {
		$this->db->where ('id', $course_id);
		$sql = $this->db->get ('coaching_test_categories');
		return $sql->row_array ();
	}

	public function create_category ($coaching_id=0, $course_id=0) {

		$data['title'] 				= $this->input->post ('title');
		
		if ($course_id > 0 ) {
			$this->db->where ('coaching_id', $coaching_id);
			$this->db->where ('id', $course_id);
			$this->db->update ('coaching_test_categories', $data);
		} else {
			$data['level'] 			= 0;
			$data['parent_id'] 		= 0;
			$data['status'] 		= 1;
			$data['coaching_id'] 	= $coaching_id;
			$data['creation_date'] = time ();
			$data['created_by'] = $this->session->userdata ('member_id');
			$this->db->insert ('coaching_test_categories', $data);
			$course_id = $this->db->insert_id ();
		}		
		return $course_id;		
	}

	// Add ITS Categories to a plan
	public function remove_category ($coaching_id=0, $course_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('id', $course_id);
		$sql = $this->db->delete ('coaching_test_categories');
	}

	/*--------------- // Category // ----------------------*/

	
	//=========== Model for list tests =======================
	public function get_all_tests ($coaching_id=0, $course_id=0, $status='-1', $type=0) {
		
		if ( $status > -1 ) {
			$this->db->where ('finalized', $status);
		}
		if ( $course_id > 0 ) {
			$this->db->where ('course_id', $course_id);
		}
		if ( $type > 0 ) {
			$this->db->where ('test_type', $type);
		}
		
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->order_by ('creation_date', 'DESC');
		
		$query = $this->db->get ("coaching_tests");
		$results = $query->result_array();	
		$data = [];
		if (! empty ($results))	{
			foreach ($results as $row) {
                $questions = $this->getTestQuestions ($coaching_id, $row['test_id']);
                $testMarks = $this->getTestQuestionMarks ($coaching_id, $row['test_id']);

                if (! empty ($questions)) {
                    $num_test_questions = count ($questions);
                } else {
                    $num_test_questions = 0;
                }

                $row['test_marks'] = $testMarks;
                $row['num_test_questions'] = $num_test_questions;

                $data[] = $row;
			}
		}
		return $data;
	}
	
	
	//=========== Model for search tests =======================
	public function search_tests ($coaching_id=0) {
		
		$status 	= $this->input->post ('status');
		$course_id = $this->input->post ('course_id');
		$search 	= $this->input->post ('search_text');
				
		if ($search != '') {
			$this->db->like ('title', $search, 'both');
		}
		if ($status > -1) {
			$this->db->where ('finalized', $status);
		}
		if ($course_id > 0) {
			$this->db->where ('course_id', $course_id);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->order_by ('creation_date', 'DESC');
		$query = $this->db->get ("coaching_tests");
		return $query->result_array();
	}	
	
	
	//=========== Model for View a details of test =====================
	public function view_tests ($tid=0) {
		//$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where ('test_id', $tid);
		$query = $this->db->get ('coaching_tests');
		if ($query->num_rows() > 0)	{
			// prepare the array with key value to be displayed
			$results = $query->row_array();
			return $results;
		} else {
			return false;
		}
	}
	
	//=========== Model for Create/Edit tests =======================
	public function create_test ($coaching_id, $course_id=0, $tid=0) {
		$now = time ();
		$test_type = $this->input->post ('test_type');
		$test_mode = TEST_MODE_ONLINE;
		$data = array(				
				'title'    		  	=>ascii_to_entities ($this->input->post('title')),
				'time_min'	      	=>$this->input->post('time_min'),
				'max_marks'  	  	=>0, 
				'num_takes'      	=>$this->input->post('num_takes'),
				'pass_marks'      	=>$this->input->post('pass_marks'),
				'release_result'	=>$this->input->post('release_result'),
				'course_id'      	=>($course_id==0)? null : $course_id,
				'unique_test_id' 	=> $this->generate_reference_id($coaching_id, $course_id, $tid),
				'test_mode'      	=>$test_mode,
				'test_type'      	=>$test_type,
            );
		
		if ($tid > 0) {
			$this->db->where ('test_id', $tid);
			$this->db->update ('coaching_tests', $data);
		} else {
			$data['coaching_id']	 	= $coaching_id;
			$data['created_by']	  		= intval ($this->session->userdata('member_id'));
			$data['creation_date']	  	= $now;
			$this->db->insert('coaching_tests', $data); 
			$tid = $this->db->insert_id();
		}

		$return = ['test_id'=>$tid, 'course_id'=>$course_id];
		return $return;
	}	
	
	
	//============ Model for ` test==============================
	public function delete_tests ($tid=0, $coaching_id=0) {
		
		$this->db->where('resource_id', $tid);
		$this->db->where('resource_type', 1);
		$this->db->where('coaching_id', $coaching_id);
		$this->db->delete('coaching_course_contents');

		$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where('test_id', $tid);
		$this->db->delete('coaching_test_questions');
		
		//========== delete fro test enrolment table
		$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where('test_id', $tid);
		$this->db->delete('coaching_test_enrolments');
		
		//========== delete from test attempts log table
		$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where('test_id', $tid);
		$this->db->delete('coaching_test_attempts');
		
		//========== delete from tests answers table
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where('test_id', $tid);
		$this->db->delete('coaching_test_answers');
		
		//========== delete from tests table
		$this->db->where('test_id', $tid);
		$this->db->delete('coaching_tests'); 	
	}
	
	public function delete_attempt ($coaching_id=0, $attempt_id=0, $member_id=0, $test_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('id', $attempt_id);
		$this->db->where ('member_id', $member_id);
		$this->db->where ('test_id', $test_id);
		$this->db->delete ('coaching_test_attempts');
		
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('attempt_id', $attempt_id);
		$this->db->where ('member_id', $member_id);
		$this->db->where ('test_id', $test_id);
		$this->db->delete ('coaching_test_answers');		
	}
	
	public function add_to_test ($coaching_id=0, $question_id=0, $test_id=0) {
		$data = array (	'test_id'		=>	$test_id,
						'coaching_id'	=>	$coaching_id,
						'question_id'	=>	$question_id,
					  );

		$this->db->where ($data);
		$query = $this->db->get ('coaching_test_questions');
		if ($query->num_rows () == 0) {
			$this->db->insert("coaching_test_questions", $data);
		}
	} 
	
	//============ Model for release results of a test==============================
	public function release_result ($coaching_id=0, $test_id=0) {
		$this->db->set('release_result', RELEASE_EXAM_IMMEDIATELY);
		$this->db->where('coaching_id', $coaching_id);
		$this->db->where('test_id', $test_id);
		$this->db->update('coaching_tests');
	}	


	// checks if a question is already present in a test
	public function questionInTest ($test_id, $question_id = 0) {
		$this->db->where ('coaching_id', $coaching_id);
		
		$query = $this->db->get_where('coaching_test_questions', array('question_id'=>$question_id, 'test_id'=>$test_id) );
		if ($query->num_rows() > 0)	{
			return  true;
		} else {
			return false;
		}
	}
	

	// returns an array of questions added in to a test
	public function getTestQuestions ($coaching_id=0, $test_id=0, $parent_id=0) {
		$this->db->select('CQ.*');
		$this->db->from('coaching_test_questions CTQ');
		$this->db->join('coaching_questions CQ', 'CTQ.question_id=CQ.question_id');
		$this->db->where ('CTQ.coaching_id', $coaching_id);
		$this->db->where ('CTQ.test_id', $test_id);
		if ($parent_id > 0) {
			$this->db->where ('CQ.parent_id', $parent_id);
		}
		$query = $this->db->get ();
		return $query->result_array();
	}	
	
	
	// gives total marks of the added questions in a test
	public function getTestQuestionMarks ($coaching_id=0, $test_id=0) {
		$marks = 0;
		$data = [];
		$questions = $this->getTestQuestions ($coaching_id, $test_id);
		//print_r ($questions);
		if ( ! empty ($questions) ) {
			foreach ($questions as $row) {
				$data[] = $row['question_id'];
			}
			$this->db->select_sum ('marks');
			$this->db->where ('coaching_id', $coaching_id);
			$this->db->where_in( 'question_id', $data);
			$query = $this->db->get( 'coaching_questions');
			$result = $query->row_array ();
			$marks = $result['marks'];		
		}

		return $marks;
	}


	// gives total time of added questions 
	public function getTestQuestionTime( $coaching_id, $questions ) {
		$this->db->select_sum('time');
		$this->db->where_in( 'question_id', $questions);
		$this->db->where ('coaching_id', $coaching_id);
		$query = $this->db->get( 'coaching_questions');
		foreach ( $query->result() as $row ) {
			return $row->time;
		}
	}
	
	
	public function finaliseTest($coaching_id=0, $test_id=0, $marks=0) {
		$time = $this->input->post ('set_exam_time');
		if ($time == 0) {
			$update =  array ('finalized'=>1, 'max_marks'=>$marks);
		} else {
			$t = date("H:i", mktime(0,0,$time,0,0,0));
			list ($h, $m) = explode (':', $t);
			$update =  array ('finalized'=>1, 'max_marks'=>$marks, 'time_min'=>$m);
		}
		$this->db->where ('coaching_id', $coaching_id);
		
		$this->db->where('test_id', $test_id);  
		return $this->db->update('coaching_tests', $update); 
	}
	
	public function unfinaliseTest($coaching_id=0, $test_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where('test_id', $test_id);  
		return $this->db->update('coaching_tests', array ('finalized'=>0)); 
	}
	
	
	public function resetTest($coaching_id=0, $test_id=0) {
		// delete all questions from test
		$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where ('test_id', $test_id);		
		$this->db->delete('coaching_test_questions'); 

		// set status as unfinalized
		$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where('test_id', $test_id);
		return $this->db->update ('coaching_tests', array ('finalized'=>0)); 	
	}
	
	// delete a question from test
	public function deleteTestQuestion($test_id=0, $id=0) {
		// delete all questions from test
		$this->db->delete('coaching_test_questions', array ('test_id'=>$test_id, 'question_id'=>$id)); 
	}
		
	
	// Model for enroling member	
	public function enrol_member ($coaching_id=0, $member_id=0, $test_id=0) {

		$attempts 	= $this->input->post ('num_takes');

		$start_date = $this->input->post ('start_date');
		list ($sy, $sm, $sd) = explode ("-", $start_date);
		$shh = $this->input->post ('start_time_hh');
		$smm = $this->input->post ('start_time_mm');
		$start_date = mktime ($shh, $smm, 0, $sm, $sd, $sy);

		$end_date = $this->input->post ('end_date');
		list ($ey, $em, $ed) = explode ("-", $end_date);
		$ehh = $this->input->post ('end_time_hh');
		$emm = $this->input->post ('end_time_mm');
		$end_date = mktime ($ehh, $emm, 0, $em, $ed, $ey);
		
		$result_release = $this->input->post ('result_release');
		$extra_time = intval ($this->input->post ('extra_time'));
		
		
		$data = array (	
						'start_date'=>	$start_date,
						'end_date'	=>	$end_date,
						'release_result' => $result_release,
						'attempts' => $attempts,
						'extra_time' => $extra_time,
					 );
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('test_id', $test_id);
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->get ('coaching_test_enrolments');
		if ($sql->num_rows () == 0 ) {
			$data['member_id']	= $member_id;
			$data['test_id']	= $test_id;
			$data['coaching_id']	= $coaching_id;
			$this->db->insert('coaching_test_enrolments', $data);
		} else {
			$this->db->where ('coaching_id', $coaching_id);
			$this->db->where ('test_id', $test_id);
			$this->db->where ('member_id', $member_id);
			$this->db->update ('coaching_test_enrolments', $data);
		}
	}
	
	// Get tests in which a user is enroled
	public function enroled_in_tests ($member_id) {
		$q = $this->db->get_where ("coaching_test_enrolments", array ("member_id"=>$member_id));
		if ($q->num_rows () > 0 ) {
			return $q->result_array();
		} else {
			return false;
		}
	}
	

	// Model for listing enrolled member
	public function enrolled_member ($test_id, $member_id) {
		$query = $this->db->get_where ("coaching_test_enrolments", array ('test_id'=>$test_id, 'member_id'=>$member_id));
		if ($query->num_rows() > 0)	{
			return  true;
		} else {
			return false;
		}	
	}
	
	// Model for listing enrolled member
	public function enrolled_student ($test_id) {
		$this->db->select ("member_id, test_id");
		$query = $this->db->get_where ("coaching_test_enrolments", array ("test_id"=>$test_id));
		if ($query->num_rows() > 0)	{
			return  $query->result_array();
		} else {
			return false;
		}
	
	}

	
	//Model for unenroll the enrolled member
	public function unenrol_member ($coaching_id=0, $member_id=0, $test_id=0){
		$this->db->where('member_id', $member_id);
		if ($test_id > 0) {
			$this->db->where('test_id', $test_id);
		}
		$this->db->where('coaching_id', $coaching_id);
		$sql = $this->db->delete('coaching_test_enrolments'); 
		
		//========== delete from test attempts log table		
		$this->db->where('member_id', $member_id);
		if ($test_id > 0) {
			$this->db->where('test_id', $test_id);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->delete('coaching_test_attempts');

		//========== delete from tests answers table		
		$this->db->where('member_id', $member_id);
		if ($test_id > 0) {
			$this->db->where('test_id', $test_id);
		}
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->delete('coaching_test_answers');
	}
	
	
	
	// Model for get details of enrolled member (Such as Date & Time)	
	public function getDate($member_id, $test_id) {	
		$query = $this->db->get_where ("coaching_test_enrolments", array ('test_id'=>$test_id, 'member_id'=>$member_id));
		if ($query->num_rows () > 0) {
			return  $query->row_array ();			
		} else {
			return false; 
		}
	}
	
	public function getAutoQuestions ($lesson_ids) {
		$lesson_ids = implode(',', $lesson_ids);
		$this->db->select ("question_id");
		$query = $this->db->where_in ("chapter_id", $lesson_ids);
		$query = $this->db->where ("parent_id >", 0);
		$this->db->where ('coaching_id', $coaching_id);
		
		$query = $this->db->get ('coaching_questions');
		if ($query->num_rows() > 0 ) {
			return $query->result_array();
		}
	}
	
	public function get_auto_questions ($lessons) { 
		$categories = $this->input->post ('categories');
		$difficulties = $this->input->post ('difficulties');
		$questions = array ();

		// Categories
		if ( ! empty ($categories)) {
			foreach ($categories as $cat_id=>$num) {
				$output = array ();
				$result = array ();
				$this->db->select ('question_id');
				$this->db->where ('course_id', $cat_id);
				$this->db->where ('parent_id >', 0);
				$this->db->where ('coaching_id', $coaching_id);
				
				$this->db->where_in ('chapter_id', $lessons);
				$sql = $this->db->get ('coaching_questions');
				if ($sql->num_rows () > 0) {
					foreach ($sql->result_array () as $row) {
						$result[] = $row['question_id'];
					}
				}
				shuffle ($result);
				if ($num == '-1') {
					$output = $result;
				} else if ($num == 0) {
					$output = array ();
				} else{
					$output = array_slice ($result, 0, $num);
				}
				$questions = array_merge ($questions, $output);
			}
		}
		
		// Difficulties
		if ( ! empty ($difficulties)) {
			foreach ($difficulties as $cat_id=>$num) {
				$output = array ();
				$result = array ();
				$this->db->select ('question_id');
				$this->db->where ('clsf_id', $cat_id);
				$this->db->where ('parent_id >', 0);
				$this->db->where ('coaching_id', $coaching_id);
				
				$this->db->where_in ('chapter_id', $lessons);
				$sql = $this->db->get ('coaching_questions');
				if ($sql->num_rows () > 0) {
					foreach ($sql->result_array () as $row) {
						$result[] = $row['question_id'];
					}
				}
				shuffle ($result);
				if ($num == '-1') {
					$output = $result;
				} else if ($num == 0) {
					$output = array ();
				} else{
					$output = array_slice ($result, 0, $num);
				}
				$questions = array_merge ($questions, $output);
			}
		}
		return $questions;
	}
	
	// get all enroled users in a test
	public function getMarks ($id=0) {
		$this->db->select('marks');
		$this->db->where('question_id', $id);
		$query = $this->db->get('coaching_questions');
		return $query->row('marks');
	}

	// get all enroled users in a test
	public function get_enrolment ($test_id) {		
		$this->db->where ('test_id', $test_id);
		$query = $this->db->get ('coaching_test_enrolments');
		if ($query->num_rows() > 0)	{
			$results = $query->result_array();			
			return $results;
		} else {
			// no members enroled
			return 0;
		}
	}
	
	
	// get all enroled users in a test
	public function get_users_in_test ($coaching_id=0, $test_id=0, $role_id=0, $class_id=0, $course_id=0, $batch_id=0, $status='-1') {

		$prefix = $this->db->dbprefix; 

  		$query = 'SELECT M.*, SR.description, TE.attempts, TE.start_date, TE.end_date FROM '.$prefix.'members M, '.$prefix.'coaching_test_enrolments TE, '.$prefix.'sys_roles SR ';
		if ($batch_id > 0) {
		    $query .= ', '.$prefix.'coaching_batch_users BU';
		}
  		$query .= ' WHERE TE.coaching_id='.$coaching_id.' AND TE.test_id='.$test_id.' AND SR.role_id=M.role_id';
		if ($batch_id > 0) {
		    $query .= ' AND M.member_id=BU.member_id AND BU.batch_id='.$batch_id;
		}
		if ($class_id > 0) {
		    $query .= ' AND M.class_id='.$class_id;
		}
		if ($status > '-1') {
		    $query .= ' AND M.status='.$status;
		}
		if ($role_id > 0) {
		    $query .= ' AND M.role_id='.$role_id;
		}
		
		$query .= ' AND TE.member_id=M.member_id AND TE.archived=0';
		
		$sql = $this->db->query ($query);
		return $sql->result_array ();
	}
	
	public function get_users_not_in_test ( $coaching_id=0, $test_id=0, $role_id=0, $class_id=0, $course_id=0, $batch_id=0, $status='-1') {

		$prefix = $this->db->dbprefix; 
		$query = 'SELECT M.*, SR.description FROM '.$prefix.'sys_roles SR, '.$prefix.'members M ';
		if ($batch_id > 0) {
		    $query .= ' INNER JOIN '.$prefix.'coaching_batch_users BU ON M.member_id=BU.member_id';
		}
		$query .= ' WHERE M.coaching_id='.$coaching_id;
		$query .= ' AND M.role_id=SR.role_id';
		if ($batch_id > 0) {
		    $query .= ' AND BU.batch_id='.$batch_id;
		}
		if ($class_id > 0) {
		    $query .= ' AND M.class_id='.$class_id;
		}
		if ($status > '-1') {
		    $query .= ' AND M.status='.$status;
		}
		if ($role_id > 0) {
		    $query .= ' AND M.role_id='.$role_id;
		}
		
		$query .= ' AND M.member_id NOT IN (SELECT TE.member_id FROM '.$prefix.'coaching_test_enrolments TE WHERE TE.test_id='.$test_id.') ';
        $sql = $this->db->query ($query);
		//echo $this->db->last_query ();
        return $sql->result_array (); 
	}
	
	// get all enroled users in a test
	public function get_archived_students ($coaching_id=0, $test_id=0, $role_id=0, $class_id=0, $course_id=0, $batch_id=0, $status='-1') {		
		
		$prefix = $this->db->dbprefix; 
		
  		$query = 'SELECT M.* FROM '.$prefix.'members M, '.$prefix.'coaching_test_enrolments TE';
		if ($batch_id > 0) {
		    $query .= ', '.$prefix.'coaching_batch_users BU';
		}
		$query .= ' WHERE M.coaching_id='.$coaching_id;
		if ($batch_id > 0) {
		    $query .= ' AND M.member_id=BU.member_id AND BU.batch_id='.$batch_id;
		}
		if ($class_id > 0) {
		    $query .= ' AND M.class_id='.$class_id;
		}
		if ($status > '-1') {
		    $query .= ' AND M.status='.$status;
		}
		if ($role_id > 0) {
		    $query .= ' AND M.role_id='.$role_id;
		}
		
		$query .= ' AND TE.member_id=M.member_id AND TE.archived=1';
		
		$sql = $this->db->query ($query);
        return $sql->result_array ();
	}

	// archive test users 
	public function archive_member ($test_id, $member_id) {
		$this->db->set ('archived', 1);
		$this->db->where ('member_id', $member_id);
		$this->db->where ('test_id', $test_id);
		$this->db->update ('coaching_test_enrolments');
	}
	
	
	public function get_parent_question($qid) {
		$this->db->select("parent_id");
		$this->db->where("question_id", $qid);
		$this->db->where ('coaching_id', $coaching_id);
		
		$query = $this->db->get('coaching_questions');
		if($query->num_rows()>0){
			$results = $query->row_array();
			return $results['parent_id'];
		} else {
			return false;
		}
	}
	
	public function all_question_group ($test_id) {
		$this->db->where ("test_id", $test_id);
		$this->db->where ('coaching_id', $coaching_id);
		
		$query = $this->db->get("coaching_test_questions");
		if ($query->num_rows() > 0) {
			$results = $query->result_array();
			return $results;
		} else {
			return false;
		}
	}
	
	
	//=========== Model for Delete lesson =====================
	public function delete_test_subject ($id) {
		
		//get all tests in this category
		$tests = $this->list_test (1, $id);
		if  ( is_array ($tests)) {
			foreach ($tests as $test) {
				$tid = $test['test_id'];
				$this->delete_tests ($tid);
			}
		}		
		// delete subject
		$this->db->where('id', $id);
		$this->db->where ('coaching_id', $coaching_id);
		
		$this->db->delete('coaching_test_categories');	
		
	}
	
	
	// returns an array of questions added in to the test
	public function test_category_name ($subject_id) {
		$test_subject = $this->test_subject_details ($subject_id);
		$subject = $test_subject['title'];
		$test_category = $this->get_test_categories ($test_subject['parent_id']);
		$category = $test_category['title'];
		$result = array ('category'=>$category, 'subject'=>$subject);
		return $result;
	}

	public function get_monthly_test ($year, $month) {

		// Last day of given month
		$str_start = $year . '-' . $month . '-01';		
		$last_day = date ('t', strtotime ($str_start));
		
		$plan_start_date = mktime (0,0,0, $month, 1, $year);
		$plan_end_date   = mktime (0,0,0, $month, $last_day, $year);
		
		//$this->db->where ('start_date >=', $plan_start_date);
		//$this->db->where ('end_date <=', $plan_end_date);	
	
		$this->db->group_by ('start_date');
		$sql = $this->db->get ('coaching_test_enrolments');
		if ($sql->num_rows () > 0 ) {
			return $sql->result_array ();
		} else {
			return false;
		}
	}
	
	// tests taken within range
	public function test_taken_between ($start_date=0, $end_date=0) {
		
		$result = array ();		
		
		// current timestamp
		$today = time ();
		if ($start_date == 0) {
			// 7 days tests from today
			$start_date = mktime (0, 0, 0, date ('m'), date ('d')-7, date ('Y'));
		}
		if ($end_date == 0) {
			$end_date = mktime (0, 0, 0, date ('m'), date ('d'), date ('Y'));
		}
		
		$this->db->where ('loggedon >= ', $start_date );
		$this->db->where ('loggedon <= ',  $end_date);
		$this->db->group_by ('test_id');
		$this->db->where ('coaching_id', $coaching_id);
		
		$sql = $this->db->get ('coaching_test_attempts');
		$count = $sql->num_rows ();
		$result[$end_date] = $count;
		
	}
	
	/* Active Tests */
	public function count_active_tests () { 
		$now = time ();		
		$this->db->group_by ('test_id');
		$this->db->where ('start_date <', $now );
		$this->db->where ('end_date >', $now );
		$query = $this->db->get ('coaching_test_enrolments');
		if ($query->num_rows() > 0 ) {
			$result = $this->db->count_all_results ();
			return $result;
		} else {
			return 0;
		}
	}
	
	public function get_all_active_tests () { 
		$now = time ();		
		$this->db->group_by ('test_id');
		$this->db->where ('start_date <', $now );
		$this->db->where ('end_date >', $now );
		$query = $this->db->get ('coaching_test_enrolments');
		if ($query->num_rows() > 0 ) {
			$result = $query->result_array ();
			return $result;
		} else {
			return false;
		}
	}
	
	/* Published Tests */
	public function published_tests () {		
		$this->db->where ('finalized', 1);
		$sql = $this->db->get ('coaching_tests');
		if ($sql->num_rows () > 0) {
			$result = $sql->result_array ();
			return $result;
		} else {
			return false;
		}
	}

	/* UnPublished Tests */
	public function unpublished_tests () {
		$this->db->where ('finalized', 0);
		$sql = $this->db->get ('coaching_tests');
		if ($sql->num_rows () > 0) {
			$result = $sql->result_array ();
			return $result;
		} else {
			return false;
		}
	}
	
		//=========== Model for list tests =======================
	public function get_related_tests ($test_type=TEST_TYPE_PUBLIC) { 
		$results = array (); 
		$this->db->where ('test_type', $test_type);
		$this->db->where ('finalized', 1);
		$this->db->where ('coaching_id', $coaching_id);
		
		$this->db->order_by ('RAND()', 'ASC');
		$query = $this->db->get ("coaching_tests");
		if ($query->num_rows() > 0)	{
			// prepare the array with key value to be displayed
			foreach ($query->result_array() as $row) {
				$results[] = $row['test_id'];
			}
			return $results;
		} else {
			return false;
		}
	}
	
	
	public function testTakenBy ($test_id) {
		$this->db->where("test_id", $test_id);
		$this->db->where ('coaching_id', $coaching_id);
		
		$query = $this->db->get("coaching_test_attempts");
		return $this->db->count_all_results();
	}
	

	public function page_stats ($member_id=0) { 

		// My Subscriptions 
		$data['subscriptions'] = $subscriptions = $this->tests_model->category_subscriptions ($member_id);
		$uri = $this->uri->segment (3);
		$role_id = $this->session->userdata ('role_id');

		$this->load->model ('tests_reports');
		
		if ($role_id == USER_ROLE_STUDENT ) {			
			if ($subscriptions == false && $uri != 'category_subscriptions') {
				//$this->message->set ('You have not subscribed to any test categories. Subscribe categories you are interested in before continuing', 'warning', true);
				//redirect ('tests/page/category_subscriptions/'.$member_id);
			}
		}
		
		if ( ! empty ($subscriptions) ) {
			$num_subscriptions = count ($subscriptions);
		} else {
			$num_subscriptions = 0;
		}
		
		// My Tests 
		$data['my_tests'] = $my_tests = $this->tests_reports->test_taken_by_member ($member_id);
		$om = array (0);
		$max_om = 0;
		if ( ! empty ($my_tests) ) {
			foreach ($my_tests as $test) {
				//$om[] = $this->tests_reports_model->obtained_marks ($test['test_id'], $test['id'], $member_id);
			}
			$max_om = max ($om);
			$num_my_tests = count ($my_tests);
		} else {
			$num_my_tests = 0;
		}
		
		// Last Login 
		$login = $this->common_model->user_details ($member_id);
		if ( ! empty ($last_login) ) {
			$last_login = date ('d-m-Y', $login['last_activity']);
		} else {
			$last_login = date ('d-m-Y');
		}
		
		$output = array ();
		$output['page_stats']['pst1'] = 'My Subscriptions';
		$output['page_stats']['psf1'] = $num_subscriptions;
		$output['page_stats']['pst2'] = 'Tests Taken';
		$output['page_stats']['psf2'] = $num_my_tests;
		//$output['page_stats'][] = array ('pst2'=>'My Subscriptions', 'psf2'=>$num_subscriptions);
		//$output['page_stats'][] = array ('pst3'=>'My Subscriptions', 'psf3'=>$num_subscriptions);
		//$output['page_stats'] = array ('My Subscriptions'=>$num_subscriptions, 'Tests Attempted'=>$num_my_tests, 'Maximum Score'=>$max_om, 'Last Login'=>$last_login);
		return $output;

	}
	
	public function tests_in_category ($coaching_id, $course_id, $type=0, $status='') {
		$ids = array ();
		$categories = $this->common_model->all_children ($course_id, SYS_TREE_TYPE_TEST);
		if ($type > 0) {
			$this->db->where ("test_type", $type);			
		}
		if ($status != '') {
			$this->db->where ("finalized", $status);			
		}
		
		if ( ! empty ($categories)) {		
			$this->db->where_in ("course_id", $categories); 
		} else {
			$this->db->where ("course_id", $course_id);
		}
		$this->db->where ('coaching_id', $coaching_id);
		//
		$query = $this->db->get("coaching_tests");
		return $query->result_array ();
	}
	
	// Tests created by a a user
	public function tests_created ($member_id) {
		$this->db->where ('created_by', $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		
		$sql = $this->db->get ('coaching_tests');
		if ($sql->num_rows () > 0 ) {
			return $sql->result_array ();
		} else {
			return false;
		}
	}
	
	
	public function ofline_tests_not_marked () {
		$tests = array ();
		$marked = array ();
		$notmarked = array ();
		$this->db->select ('test_id');
		$this->db->where ('test_type', TEST_MODE_OFLINE);
		$this->db->where ('finalized', 1);
		$sql = $this->db->get ('coaching_tests');
		if ($sql->num_rows () > 0 ) {
			foreach ($sql->result_array() as $row) {
				$tests[] = $row['test_id'];
			}
			$this->db->select ('test_id');
			$this->db->group_by ('test_id');
			$query = $this->db->get ('test_answers_offline');
			if ($query->num_rows () > 0 ) {
				foreach ($query->result_array() as $row) {
					$marked[] = $row['test_id'];
				}
			}			
		}
		
		$notmarked = array_diff ($tests, $marked);
		
		return $notmarked;
	}
		
	
	//=========== Model for list tests =======================
	public function get_enroled_tests ($coaching_id=0, $member_id=0, $test_type=TEST_TYPE_REGULAR) {
		$this->db->select ('T.*, TE.start_date, TE.end_date, TE.attempts');
		$this->db->from ('coaching_tests T');
		$this->db->join ('coaching_test_enrolments TE', 'T.test_id=TE.test_id');
		$this->db->where ('T.finalized', 1);
		$this->db->where ('T.test_type', $test_type);
		$this->db->where ('T.coaching_id', $coaching_id);
		$this->db->where ('TE.member_id', $member_id);
		$sql = $this->db->get ();
		return $sql->result_array ();
	}

	public function _get_enrolled_test ($coaching_id=0, $member_id=0, $test_type=TEST_TYPE_REGULAR, $filter=TEST_TYPE_ONGOING) {		
		
		$results = array ();
		
		if ($test_type == TEST_TYPE_REGULAR) {
			// get enrolment
			$enroled = $this->user_enroled_in_tests ($member_id, $test_type, $filter);
			if ( $enroled == false) {
				return false;
				exit;
			} else {
				foreach ($enroled as $item) {
					$row = $this->tests_model->view_tests ($item['test_id']);
					$res = array_merge ($row, $item);
					$results[$row['test_id']] = $res;
				}
				return array_combine(range(0, (count($results)-1)), array_values($results));
				exit;
			}
		}
		
		if ($test_type > 0) {
			$this->db->where ('test_type', $test_type);
		}
		
		$this->db->where ('coaching_id', $coaching_id);		
		$this->db->where ("finalized", 1);
		$query = $this->db->get ("coaching_tests");
		if ($query->num_rows() > 0)	{
			// prepare the array with key value to be displayed
			foreach ($query->result_array() as $row) {
				$results[ $row['test_id'] ] = $row;
			}
			return $results;
		} else {
			return false;
		}
	}
	
	// Model for get details of enrolled member(Such as Date & Time)	
	public function user_enroled_in_tests ($member_id, $test_type=0, $filter=0) {
		$time = time ();
		if ($filter == TEST_TYPE_ONGOING) {
			$this->db->where ('start_date <=', $time); 
			$this->db->where ('end_date >', $time);
		} else if ($filter == TEST_TYPE_UPCOMING) {
			$this->db->where ('start_date >', $time);
			$this->db->where ('end_date >', $time);
		} else if ($filter == TEST_TYPE_PREVIOUS) {
			$this->db->where ('start_date <', $time);
			$this->db->where ('end_date <', $time);
		}
		$this->db->where (array ('member_id'=>$member_id));
		$query = $this->db->get_where ("coaching_test_enrolments");
		if ($query->num_rows () > 0 ) {
			return  $query->result_array();			
		} else {
			return false;
		}
	}

	//check number of attempts of a test
	public function check_attempt ($coaching_id, $tid, $member_id) {
		$this->db->where ("test_id", $tid);
		$this->db->where ("member_id", $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		
		$this->db->from ("coaching_test_attempts");		
		return $this->db->count_all_results ();
	}
	
	//check for valid test session
	public function check_session ($coaching_id, $tid, $member_id) {
		$this->db->select_max ("loggedon");
		$this->db->where ("test_id", $tid);
		$this->db->where ("member_id", $member_id);
		$this->db->where ('coaching_id', $coaching_id);
		
		$sql = $this->db->get ("coaching_test_attempts");		
		
		if ($sql->num_rows () > 0) {
			$result = $sql->row();
			return $result->loggedon;
		} else {
			return 0;
		}
	}
	
	//insert the time stamp when student starts for test
	public function	save_attempt ($coaching_id, $tid, $member_id, $time){
		$data = array(
					   'test_id' 	=> $tid ,
					   'member_id' 	=> $member_id,
					   'loggedon'	=>	$time,
					   'coaching_id'	=>	$coaching_id,
					   'plan_id'	=>	intval ($this->session->userdata ('plan_id')),
					   'obtained'	=>	0
					);
		$this->db->insert('coaching_test_attempts', $data);		
		return $this->db->insert_id();
	}
	
	
	//model for get how many attempts done by a student.
	public function get_attempts ($member_id, $tid) {
		$this->db->where ("test_id", $tid);
		$this->db->order_by ("loggedon", 'DESC');
		$this->db->where ("member_id", $member_id);		
		$sql = $this->db->get ("coaching_test_attempts");
		if ($sql->num_rows () > 0 ) {
			return $sql->result_array ();
		} else {
			return false;
		}
	}

	//All tests taken by a user
	public function test_taken_by_member ($coaching_id=0, $member_id=0, $attempt=0) {
//		$this->db->select ('T.test_id, T.title, T.pass_marks, TA.loggedon, TA.submit_time, TA.id AS attempt_id, TE.start_date, TE.end_date, TE.attempts, T.release_result');
		$this->db->select ('T.test_id, T.title, T.pass_marks, T.num_takes AS attempts, T.release_result, TA.loggedon, TA.submit_time, TA.id AS attempt_id');
		$this->db->from ('coaching_test_attempts TA');
		$this->db->join ('coaching_tests T', 'T.test_id=TA.test_id');
		$this->db->where ('TA.member_id', $member_id);
		$this->db->where ('T.coaching_id', $coaching_id);
		$this->db->order_by ('TA.loggedon', 'DESC');
		$this->db->group_by ('TA.test_id');
		$sql = $this->db->get ();
		
		$result = [];
		if ($sql->num_rows () > 0 ) {
			foreach ($sql->result_array () as $row) {
				$attempt_id = $row['attempt_id'];
				// Submission
				$submitted = $this->tests_reports->test_submitted ($coaching_id, $row['test_id'], $attempt_id, $member_id);
				if ( ! empty ($submitted)) {
					$row['submitted'] = 1;
				} else {
					$row['submitted'] = 0;
				}
				// Num attempts 
				$attempts 		= $this->tests_model->get_attempts ($coaching_id, $member_id, $row['test_id']);
				$num_attempts   = 0;
				if (! empty($attempts)) {
					$num_attempts = count ($attempts);
				}
				$row['num_attempts'] = $num_attempts;

				// Obtained Marks
				$row['obtained_marks'] = $this->tests_reports->calculate_obtained_marks ($row['test_id'], $attempt_id, $member_id);

				// Test marks
				$row['test_marks'] = $this->getTestquestionMarks ($coaching_id, $row['test_id']);

				$result[] = $row;
			}
		}
		return $result;
	}

	
	//Insert the answer of a student given by a specific student
	public function insert_answers ($member_id, $test_id, $qid, $answer) {
		$question = $this->qb_model->getQuestionDetails ($qid);
		$type = $question['type'];
		$data = array(
					   'attempt_id' => $this->input->post('attempt_id'),
					   'test_id' 	=> $test_id ,
					   'question_id'=> $qid,
					   'member_id' 	=> $member_id
					);
		//save answer for Question type Multiple choice single correct			
		if($type == QUESTION_MCSC) {
			for($i=1; $i<=6; $i++){
				if($i == $answer){
					$data['answer_'.$i] = $answer;	
				}	
			}
		}
		//save answer for Question type Long answers
		if($type == QUESTION_LONG) {
			$data['answer_1'] = $answer;	
		}
		//save answer for Question type Multiple choice multi correct
		if( $type == QUESTION_MCMC ) {
			for($i=1; $i<=6; $i++) {
				if( isset ($answer[$i]) ) {
					$data['answer_'.$i] = $answer[$i];
				}
			}
		}
		//save answer for Question type True False
		if($type == QUESTION_TF){
			$data['answer_1'] = $answer;	
		}
		//save answer for Question type MATCH THE FOLLOWING
		if($type == QUESTION_MATCH) {
			for ($i=1; $i<=6; $i++) {
				$data['answer_'.$i] = $answer[$i];	
			}			
		}
		
		$this->db->insert('coaching_test_answers', $data); 
	}

	public function mark_for_demo ($test_id=0, $data=0) {
		$this->db->set ('for_demo', $data);
		$this->db->where ('test_id', $test_id);
		$this->db->update ('coaching_tests');
	}

	public function upload_from_text ($content='') {
		// echo $content;
		$heading_stack = [];
		$question_stack = [];
		$answer_stack = [];

		$heading_count = 0;
		$question_count = 0;
		$answer_count = 0;

		if ( ! empty ($content) ) {
			foreach ($content as $line) {
				//echo $line;
				//echo br ();
				if ( strlen ($line) > 0 ) {
					//echo $line;
					//echo '<br>';
					// This is a question group
					if ( preg_match ('/^#QH/', $line) == 1 ) {
						// Got a new question heading
						$heading_count++;
						$heading 	= substr ($line, 4);
						$heading_stack[$heading_count]['heading'] = $heading;
						// Reset question count
						$question_stack = [];
						$answer_stack = [];
						$question_count = 0;
						$answer_count = 0;

					} else if ( preg_match ('/^[\d]+[\.|\)]/', $line) == 1 ) {
						// Got a main question 
						$match =  preg_split ('/^[\d]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY );
						$question = $match[0];
						$question = htmlspecialchars(trim($question));
						if ($heading_count == 0) {
							$heading_count++;
							$heading_stack[$heading_count]['heading'] = 'Add question heading';
						}
						// Reset answer choice
						$answer_stack = [];
						$answer_count = 0;

						$question_count++;
						//$question_stack[$question_count]['question'] = $question;
						$heading_stack[$heading_count]['questions'][$question_count]['question'] = $question;

					} else if ( preg_match ('/^[a-fA-F]+[\.|\)]/', $line) == 1) {
						// Answer Choices
						preg_match ('/^[a-fA-F]+[\.|\)]/', $line, $match );
						// this is an answer choice
						if ( ! empty ( $match )) {				
							// answer text
							$answer_choice = preg_split ('/^[a-fA-F]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY);
							if ($question_count > 0) {
								$answer_count++;
								$answer_choice = htmlspecialchars(trim($answer_choice[0]));
								//$answer_stack[$question_count]['choices'][$answer_count] = $answer_choice;
								$heading_stack[$heading_count]['questions'][$question_count]['choices'][$answer_count] = $answer_choice;
							}
						}
					} else if ( preg_match ('/^\*+[a-fA-F]+[\.|\)]/', $line) == 1) {
						// Answer Choices
						preg_match ('/^\*+[a-fA-F]+[\.|\)]/', $line, $match );
						// this is an answer choice
						if ( ! empty ( $match )) {
							// answer text
							$answer_choice = preg_split ('/^\*+[a-fA-F]+[\.|\)]/', $line, 0, PREG_SPLIT_NO_EMPTY);
							if ($question_count > 0) {
								$answer_count++;
								$answer_choice = htmlspecialchars(trim($answer_choice[0]));
								$heading_stack[$heading_count]['questions'][$question_count]['choices'][$answer_count] = $answer_choice;
								$heading_stack[$heading_count]['questions'][$question_count]['answers'][$answer_count] = $answer_count;
							}
						}
					} 
				}
			}
		}



		return $heading_stack;
	}

	public function save_question ($coaching_id=0, $course_id=0, $test_id=0) {
		/*
			$headings = $this->input->post('headings');
			$questions = $this->input->post('questions');

			if (! empty ($headings)) {
				foreach ($headings as $heading) {
					// insert heading
					$parent_id = 0;
					$['question'] = $heading;
					$question_id = $this->tests_model->save_question ($data, $parent_id);
					if (! empty($questions)) {
						foreach ($questions as $question) {
						// insert question

						}
					}

				}
			}
		*/

	}
}