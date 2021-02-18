 <?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Virtual_class_model extends CI_Model {

	public function get_class ($coaching_id=0, $class_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('class_id', $class_id);
		$sql = $this->db->get ('virtual_classroom');
		return $sql->row_array ();
	}

	public function join_class ($coaching_id=0, $class_id=0, $member_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('class_id', $class_id);
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->get ('virtual_classroom_participants');
		return $sql->row_array ();
	}
	
	public function get_all_classes ($coaching_id=0, $course_id=0, $batch_id=0, $start_date=0) {
		$result = [];
		$this->db->select ('VC.*');
		$this->db->from ('virtual_classroom VC');
		$this->db->where ('VC.coaching_id', $coaching_id);
		$this->db->where ('VC.course_id', $course_id);
		$this->db->where ('VC.batch_id', $batch_id);
		if ($start_date > 0) {
			$this->db->where ('VC.start_date', $start_date);
		}
		$sql = $this->db->get ();
		foreach ($sql->result_array () as $row) {
			//$row['running'] = $this->is_meeting_running ($coaching_id, $row['class_id']);
			/*
			$participants = $this->get_participants ($coaching_id, $row['class_id']);
			if (! empty($participants)) {
				$num_participants = count($participants);
			} else {
				$num_participants = 0;
			}
			$row['num_participants'] = $num_participants;
			$result[] = $row;
			*/
		}
		return $result;
	}


	public function my_classroom ($coaching_id=0, $member_id=0, $course_id=0, $batch_id=0, $new_only=0) {
		$result = [];
		$today = time ();
		$this->db->select ('VC.*, VCP.meeting_url, VCP.role');
		$this->db->from ('virtual_classroom VC');
		$this->db->join ('virtual_classroom_participants VCP', 'VC.class_id=VCP.class_id');
		$this->db->where ('VCP.coaching_id', $coaching_id);
		$this->db->where ('VCP.member_id', $member_id);
		if ($course_id > 0) {
			$this->db->where ('VC.course_id', $course_id);
		}
		if ($batch_id > 0) {
			$this->db->where ('VC.batch_id', $batch_id);
		}
		if ($new_only == 1) {
			$this->db->where ('VC.start_date >=', $today);
		}
		$this->db->order_by ('VC.creation_date', 'DESC');
		$sql = $this->db->get ();
		$sql_array = $sql->result_array ();
		if (! empty ($sql_array)) {
			foreach ($sql_array as $row) {
				$row['course'] = $this->courses_model->get_course_by_id ($row['course_id']);
				$row['running'] = $this->is_meeting_running ($coaching_id, $row['class_id']);
				$result[] = $row;
			}			
		}
		return $result;
	}

	public function get_api_settings ($param_name='') {
		$output = [];
		if ($param_name != '') {
			$this->db->where ('param_name', $param_name);
		}
		$sql = $this->db->get ('bigbluebutton_settings');
		if ($param_name != '') {
			$row = $sql->row_array ();
			$output[$row['param_name']] = $row['param_val'];
		} else {			
			foreach ($sql->result_array () as $row) {
				$output[$row['param_name']] = $row['param_val'];
			}
		}
		return $output;
	}

	public function get_api_details ($call_name='') {
		$this->db->where ('call_name', $call_name);
		$sql = $this->db->get ('bigbluebutton_api');
		return $sql->row_array ();
	}
	

	public function create_meeting ($coaching_id=0, $class_id=0) {
		
		$api_setting = $this->virtual_class_model->get_api_settings ();

		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		// Create call and query
		$api_url = $api_setting['api_url'];
		$call_name = $class['call_name'];
		$query_string = $class['query_string'];
		$checksum = $class['checksum'];

		$final_string = $api_url . $call_name .'?'.  $query_string . '&checksum='.$checksum;
		
		// Upload whiteboard slide
		$post_slide = '
						<modules>
					     <module name="presentation">
					      <document url="https://easycoachingapp.com/apps/whiteboard.pdf"/>
					   </module>
					</modules>
					';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $final_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "xmlRequest=" . $post_slide);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
		$xml_response = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($xml_response);

		$returncode = $xml->returncode;
		return $returncode;
	}

	public function is_meeting_running ($coaching_id=0, $class_id=0) {

		$api_setting = $this->virtual_class_model->get_api_settings ();
		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);
		
		$api_url = $api_setting['api_url'];
		$shared_secret = $api_setting['shared_secret'];
		$call_name = 'isMeetingRunning';
		$query_string = 'meetingID='.$class['meeting_id'];

		$checksum_string = $call_name . $query_string . $shared_secret;
		$checksum = sha1 ($checksum_string);
		$url = $api_url . $call_name . '?'. $query_string . '&checksum='.$checksum;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$xml_response = curl_exec($ch);
		curl_close($ch);
		$xml = simplexml_load_string($xml_response);
		$running = $xml->running;

		return $running;
	}

	public function get_batch_vc ($coaching_id=0, $course_id=0, $batch_id=0) {
		$users = $this->input->post ('users');
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('course_id', $course_id);
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->get ('virtual_classroom');
		return $sql->row_array ();
	}

	public function add_participant ($coaching_id=0, $class_id=0, $member_id=0) {
		
		// Add current user to virtual session
		$user = $this->users_model->get_user ($member_id);
		$class = $this->get_class ($coaching_id, $class_id);

		$api_setting = $this->get_api_settings ();
		$shared_secret = $api_setting['shared_secret'];
		$password = $class['moderator_pwd'];
	
		$call_name = 'join';

		$participant_role = VM_PARTICIPANT_ATTENDEE;

		$fullName = $user['first_name'] . '+' . $user['last_name'];
 
		$query_string = '';
		$query_string .= 'fullName='.$fullName;
		$query_string .= '&meetingID='.$class['meeting_id'];
		$query_string .= '&password='.$password;
		$query_string .= '&userID='.$member_id;

		$final_string = $call_name . $query_string . $shared_secret;
		$checksum = sha1 ($final_string);

		$meeting_url = '';		
		$meeting_url .= $query_string;
		$meeting_url .= '&checksum='.$checksum;

		$data = [];
		$data['coaching_id'] = $coaching_id;
		$data['class_id'] = $class_id;
		$data['member_id'] = $member_id;
		$data['role'] = $participant_role;
		$data['meeting_url'] = $meeting_url;
		// Insert only when not already inserted
		$this->db->where ($data);
		$sql = $this->db->get ('virtual_classroom_participants');
		if ($sql->num_rows () == 0) {
			$this->db->insert ('virtual_classroom_participants', $data);
		}
	}

	/* RECORDINGS */
	public function get_recording_data ($coaching_id=0, $class_id=0, $meeting_id=0, $course_id=0, $batch_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('class_id', $class_id);
		$this->db->where ('meeting_id', $meeting_id);
		$this->db->order_by ('publish_date', 'DESC');
		$sql = $this->db->get ('virtual_classroom_recordings');
		return $sql->result_array ();
	}


	public function get_recording ($id=0) {
		$this->db->select ('*');
		$this->db->where ('id', $id);
		$sql = $this->db->get ('virtual_classroom_recordings');
		return $sql->row_array ();
	}


	public function add_recording_data ($data=[]) {
		$sql = $this->db->insert ('virtual_classroom_recordings', $data);
		$id = $this->db->insert_id ();
		return $id;
	}

	public function recording_exists ($data=[]) {
		$this->db->select ('recording_id');
		$this->db->where ($data);
		$sql = $this->db->get ('virtual_classroom_recordings');
		if ($sql->num_rows () > 0) {
			return true;
		} else {
			return false;
		}
	}

}