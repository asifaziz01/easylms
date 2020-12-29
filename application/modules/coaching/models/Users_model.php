<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users_model extends CI_Model {
    
    // Get all users
	public function get_users ($coaching_id=0, $role_id=0, $status='-1', $batch_id=0) {

	    $select = 'M.*, SR.description'; 
	    
	    $this->db->select ($select);

	    if ($status > '-1') {
	        $this->db->where ('M.status', $status);
	    }
	    $this->db->from ('members M');
	    
	    if ($role_id > 0) {
	        $this->db->where ('M.role_id', $role_id);
	    }
        $this->db->join ('sys_roles SR', 'M.role_id=SR.role_id');

	    if ($batch_id > 0) {
	        $this->db->join ('coaching_course_batch_users BU', 'M.member_id=BU.member_id AND BU.batch_id='.$batch_id);
	    }
	    $this->db->where ('M.coaching_id', $coaching_id);
	    $this->db->order_by ('M.first_name, M.second_name, M.last_name', 'ASC');
	    $sql = $this->db->get ();
	    return $sql->result_array ();
	}
	
    
    public function search_users ($coaching_id=0) {
		

		$role_id = $this->input->post ('search_role');
		$status = $this->input->post ('search_status');
		$batch_id = $this->input->post ('search_batch');		
		$search = $this->input->post ('search_text');
		$sort_type = $this->input->post ('filter_sort');
		

		$select = '';
		$select .= 'M.*';
		$select .= ',SR.description';
	    if ($batch_id > 0) {
		//	$select .= ', BU.batch_name';
		}
		$this->db->select ($select);

		if ( ! empty($search)) {
			$where = "(adm_no LIKE '%$search%' OR login LIKE '%$search%' OR first_name LIKE '%$search%' OR second_name LIKE '%$search%' OR last_name LIKE '%$search%' OR email LIKE '%$search%' OR primary_contact LIKE '%$search%')";
			$this->db->where ($where);
		} 
		if ($role_id > 0) {
			$this->db->where ('M.role_id', $role_id); 
		}
		if ($status > -1) {
			$this->db->where ('M.status', $status);
		}
	    $this->db->from ('members M');
        $this->db->join ('sys_roles SR', 'M.role_id=SR.role_id');

	    if ($batch_id > 0) {
	        $this->db->join ('coaching_course_batch_users BU', 'M.member_id=BU.member_id AND BU.batch_id='.$batch_id);
	    }
	    $this->db->where ('M.coaching_id', $coaching_id);

		if ($sort_type == SORT_ALPHA_DESC) {
		    $this->db->order_by ('M.first_name, M.second_name, M.last_name', 'DESC');
		} else if ($sort_type == SORT_CREATION_ASC) {
		    $this->db->order_by ('M.creation_date', 'ASC');
		} else if ($sort_type == SORT_CREATION_DESC) {
		    $this->db->order_by ('M.creation_date', 'DESC');
		} else {
		    $this->db->order_by ('M.first_name, M.second_name, M.last_name', 'ASC');
		}

		$sql = $this->db->get ();
		return $sql->result_array ();
		
	}
	
	public function get_user ($member_id=0) {		
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->get ('members');
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->row_array ();			
			return $result;
		} else {
			return false;
		}		
	}
	
	public function member_profile ($member_id=0) {
		$result = array ();
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->get ('coaching_user_profile');
		if  ($sql->num_rows () > 0 ) { 
			$result = $sql->row_array ();
		} else {
			foreach ($sql->list_fields() as $field) {
			   $result[$field] = '';
			} 
		}
		return $result;
	}


	// Save Batch
	public function save_batch ($coaching_id=0, $member_id=0, $batches=[]) {
		// Clear all batches
		$this->db->where ('coaching_id', $coaching_id);
		$this->db->where ('member_id', $member_id);
		$this->db->delete ('coaching_course_batch_users');

		// Insert fresh batch
		if (! empty ($batches)) {
			foreach ($batches as $batch_id) {
				$data['coaching_id'] = $coaching_id;
				$data['member_id'] = $member_id;
				$data['batch_id']  = $batch_id;
				$sql = $this->db->insert ('coaching_course_batch_users', $data);
			}
		}
	}


	// Save account
	public function save_account ($coaching_id=0, $member_id=0, $status=USER_STATUS_ENABLED) {
	    
	    if ($this->input->post ('dob')) {
			$date = $this->input->post ('dob'); 
		} else {
			$date = '';
		}
	    
	    if ($this->input->post ('gender')) {
	    	$gender = $this->input->post ('gender');
	    } else {
	    	$gender = 'n';
	    }

	    if ($this->input->post ('password')) {
	    	$password = $this->input->post ('password');
	    } else {
	    	$password = random_string ('numeric', 6);
	    }

    	$name = $this->input->post ('first_name');
		$name_array = explode (" ", $name);
		$f_name = $name_array[0];
		if (isset($name_array[1])) {
			$l_name = $name_array[1];
		} else {
			$l_name = '';
		}

		if ($this->input->post ('last_name')) {
			$l_name = $this->input->post ('last_name');
		}

		$s_name = '';
		if ($this->input->post ('second_name')) {
			$s_name = $this->input->post ('second_name');
		}

		$data = array (
			'role_id'	=>		$this->input->post ('user_role'),
			'sr_no'		=>		$this->input->post ('sr_no'), 
			'first_name'=>		$f_name, 
			'second_name'=>		$s_name, 
			'last_name'	=>		$l_name, 
			'primary_contact'=> $this->input->post ('primary_contact'),
			'email'		=>		$this->input->post ('email'),
			'login'		=>		'',
			'dob'		=>		$date,
			'gender'	=>		$gender,
			'status'  	=> 		$status,
		);
		
		$return = [];		
		if ($member_id > 0) {
			// update account
			$this->db->where ('member_id', $member_id);
			$this->db->update ('members', $data);		
		} else {
			// create profile
			$otp = $password;
			$data['password'] = password_hash ($password, PASSWORD_DEFAULT);
			$data['coaching_id']  = $coaching_id;
			$data['user_token'] =  '';
			$data['link_send_time']	= time();
			$data['creation_date'] = time ();
			$data['created_by'] = $this->session->userdata ('member_id');
			$sql = $this->db->insert ('members', $data);
			$member_id = $this->db->insert_id ();

			// User Id
			$user_id = $this->generate_reference_id ($member_id);

			// User Token
			$salt = random_string ('alnum', 4);
			$str = $user_id . $coaching_id . $member_id . $salt;
			$user_token = md5($str);

			// Update User-id, User-token and Login
			$this->db->set ('user_token', $user_token);
			$this->db->set ('adm_no', $user_id);
			$this->db->set ('login', $user_id);
			$this->db->where ('member_id', $member_id);
			$this->db->update ('members');
			$return = ['password'=>$otp, 'email'=>$this->input->post ('email'), 'member_id'=>$member_id];
		}
		if($member_id == $this->session->userdata('member_id')){
			$this->session->set_userdata (array (
				'user_name'		=> "$f_name $l_name",
				)
			);
		}
		return $return;
	}
	
	
	/* VIEW PROFILE IMAGE
	*/
	public function view_profile_image ($member_id=0, $coaching_id=0) { 
	
		$this->load->helper('file');
		$path_to_image_dir = $this->config->item('profile_picture_path');
		$file_name = 'pi_'.$member_id.'.gif';
		$file_path = $path_to_image_dir . $file_name;
		
		// if a profile_image of this user does not exists we can proceed normally
		if ( read_file ( $file_path ) == false ) {
			$profile_image = $path_to_image_dir . 'default.png';
		} else {
			$profile_image = $path_to_image_dir . $file_name . '?' . time ();
		}
		return $profile_image;	
	}
	
	public function upload_profile_picture ($member_id, $coaching_id=0) {
		
		$this->load->helper('directory');
		$this->load->helper('file');
		
		// Set upload path
		$upload_dir =  $this->config->item ('profile_picture_path');

		$file_name = 'pi_'.$member_id.'.gif';
		$file_path = $upload_dir . $file_name;		
		
		if ( ! is_dir ($upload_dir) ) {
			mkdir ($upload_dir, 0777, true);
		}
		
		// upload parameters
		$options['upload_path'] 	= $upload_dir;
		$options['allowed_types'] 	= 'gif|jpg|png';
		//$options['max_size']		= MAX_FILE_SIZE;
		$options['file_name']		= $file_name;
		$options['overwrite']		= true;
		
		// load upload library
		$this->load->library ('upload', $options); 		
		
		if ( ! $this->upload->do_upload () ) {
			$response = $this->upload->display_errors ();
		} else {   
			$upload_data = $this->upload->data();
			$config['image_library'] = 'gd2';
			$config['source_image']	= $file_path ;
			$config['create_thumb'] = false; 
			$config['maintain_ratio'] = false;
			$config['width']	 	= 240;
			$config['height']		= 240;
			//$config['master_dim']	= 'auto';
			
			$this->load->library('image_lib', $config);
		
			$this->image_lib->resize();
			$response = $upload_data;
		}
		
		return $response;;
	}
	
	/* REMOVE PROFILE IMAGE	*/
	public function remove_profile_image ($member_id) {
		$this->load->helper('file');
		$path_to_image_dir =  $this->config->item ('profile_picture_path');
		$file_name = 'pi_'.$member_id.'.gif';
		$file_path = $path_to_image_dir . $file_name;
		
		if ( file_exists($file_path)){
			delete_files ( './' . $file_path );
			unlink(($file_path));
			return true;
		} else{
			return false;
		}
	}
	
	public function get_user_by_login ($login_id=0) {
		$this->db->where ('login', $login_id);
		$sql = $this->db->get ('members');
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->row_array ();
			return $result;
		} else {
			return false;
		}
	}

	public function get_user_by_userid ($userid){
		$this->db->where ('login', $userid);
		$sql = $this->db->get ('members');
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->row_array ();
			return $result;
		} else {
			return false;
		}		
	}	
	
	public function get_user_roles ($admin_user=0, $level=0, $roles=[]) {
		if ($admin_user > 0) {
			$this->db->where ('admin_user', $admin_user);
		}
		if ($level > 0) {
			$this->db->where ('role_lvl >=', $level);
		}
		if (! empty ($roles)) {
			$this->db->where_in ('role_id', $roles);
		}
		$this->db->where ('status', 1);
		$this->db->order_by ('role_lvl', 'ASC');
		$sql = $this->db->get ('sys_roles');
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->result_array ();
			return $result;
		} else { 
			return false;
		}		
	}

	
	public function user_roles_by_level ($level=0) {
		if ($level > 0) {
			$this->db->where ('role_lvl >=', $level);			
		}
		$this->db->where ('status', 1);
		$this->db->order_by ('role_lvl', 'ASC');
		$sql = $this->db->get ('sys_roles');
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->result_array ();
			return $result;
		} else { 
			return false;
		}		
	}

	public function user_role_name ($role_id=0) {		
		$this->db->where ('role_id', $role_id);
		$sql = $this->db->get ('sys_roles');
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->row_array ();
			return $result;
		} else { 
			return false;
		}		
	}
	
	/*---=== MEMBER LOG FUNCTIONS ===---*/
	public function add_member_log ($log_id=0, $member_id=0) {
		$comment = $this->input->post("action_log");
		$values = array (
			"comment"	=>ascii_to_entities($comment),
			"member_id"	=>$member_id,
			"log_by"	=>$this->session->userdata("member_id")
		);
		if ($log_id > 0) {
			$this->db->where("id", $log_id);
			$this->db->update("member_log", $values);			
		} else {
			$this->db->insert("member_log", $values);
		}
		
		return true;
	}

	
	public function last_log ($member_id=false){
		$this->db->where(array("member_id"=>$member_id));
		$this->db->order_by("date", "DESC");
		$query = $this->db->get("member_log");
		if($query->num_rows()>0){
			$row = $query->row_array();
			return $row;
		}else{
			return false;
		}
	}
	 
	public function all_log ($member_id=false){
		$this->db->where(array("member_id"=>$member_id));
		$this->db->order_by("date", "DESC");
		$query = $this->db->get("member_log");
		if($query->num_rows()>0){
			$results = $query->result_array();
			return $results;
		}else{
			return false;
		}
	}
	
	public function get_action_log ($log_id=false){
		$this->db->where("id", $log_id);
		$query = $this->db->get("member_log");
		if ($query->num_rows()>0) {
			$row = $query->row_array();
			return $row; 
		} else {
			return false;
		}
	}
	
	/*---=== ===---*/
	public function delete_account ($member_id=0) {
		$this->db->where ("member_id", $member_id);
		$this->db->delete ("members");
		
		// Clear member log
		return true;
	}

	/*---=== ===---*/
	public function delete_member_log ($log_id=false){
		$this->db->where("id", $log_id);
		$this->db->delete("member_log");
		return true;
	}
	
	// Change account password
	public function update_password ($member_id, $password) {
		$password = password_hash($password, PASSWORD_DEFAULT);
		$data = array ('password'=> $password);
		$this->db->where ('member_id', $member_id);
		$query = $this->db->update ("members", $data );
	}

	/*---=== ===---*/	
	// Enable User
	public function enable_user ($member_id) {
		$data['status'] = 1;
		$this->db->where ("member_id", $member_id);
		$query = $this->db->update ('members', $data);
		if ($query)	{
			return true;
		} else {
			return false;
		}
	}

	// Disable User
	public function disable_user ($member_id) {
		$data['status'] = 0;
		$this->db->where ("member_id", $member_id);
		$query = $this->db->update ('members', $data);		
		if ($query)	{
			return true;
		} else {
			return false;
		}
	}	
	
	/*---=== ===---*/
	public function check_admno ($adm_no, $member_id) {
		$this->db->where ('adm_no', $adm_no);
		$this->db->where ('member_id <>', $member_id);
		$sql = $this->db->get ('members');
		if ($sql->num_rows () > 0 ) {
			return true;
		} else {
			return false;
		}
	}


	// tests taken within range
	public function user_registration_between ($coaching_id=0, $start_date=0, $end_date=0) {
		
		$result = array ();	
		
		if ($start_date == 0) {
			$start_date = mktime (0, 0, 0, date('m'), date('d'), date('Y'));			
		}
		
		$one_day = 24 * 3600;
		$one_week = 7 * $one_day;
		
		if ($end_date == 0) {
			$end_date = $start_date - $one_week;
		}
		
		for ($i=$end_date; $i<=$start_date; $i=$i+$one_day) {
			$this->db->select ('COUNT(member_id) AS member_id');
			$this->db->where ('creation_date >=', $i);
			$this->db->where ('creation_date <', $i+$one_day);
			$this->db->where ('coaching_id', $coaching_id);
			$sql = $this->db->get ('members');
			$row = $sql->row_array ();
			$num = $row['member_id'];
			$result[$i] = $num;
		}
		
		return $result;
		/*
		// current timestamp
		$day = date ('j');
		$now = time ();
		$day_before = mktime (0, 0, 0, date('n'), $day, date('Y'));
		$step_24 = 24 * 60 * 60; 		// Seconds in one day

		if ($start_date == 0) {
			$start_date = $now;
		}
		if ($end_date == 0) {
			// seven days from today
			$num_days = 7 * $step_24;
			$end_date = $start_date - $num_days;
		}		
		
		// 7 days tests from today
		$x = 7;
		for ($i=1; $i<=7; $i++) {
			$count = 0;
			$start_date = $now;
			$end_date = $day_before;
			
			$this->db->where ('creation_date >= ', $start_date );
			$this->db->where ('creation_date < ',  $end_date);
			$sql = $this->db->get ('members');
			if ($sql->num_rows () > 0) {
				$count = $sql->num_rows ();
			}
			$result[$end_date] = $count;
			$now = mktime (0, 0, -1, date('n'), $day, date('Y'));
			$day_before = mktime (0, 0, 0, date('n'), $day-1, date('Y'));
			$day--;
		}	
		$reversed = array_reverse($result, true);
		return $reversed;
		
		*/
	}
	
	/* Reporting Functions */
	public function count_all_users ($coaching_id=0) {
		$this->db->where ('coaching_id', $coaching_id);
		return $this->db->count_all_results ('members');
	}
	
	public function count_users ($status=USER_STATUS_ALL, $role_id=0, $coaching_id=0) {
		
		if ($status <> USER_STATUS_ALL) {
			$this->db->where ('status', $status);
		} 
		
		if ($role_id > 0) {
			$this->db->where ('role_id', $role_id);			
		}
		if ($coaching_id > 0) {
			$this->db->where ('coaching_id', $coaching_id);			
		}
		$sql = $this->db->get ('members');		
		return $sql->num_rows ();
	}
	
	// Get Batches
	public function get_batches ($coaching_id=0) {
	    $this->db->where ('coaching_id', $coaching_id);
	    $sql = $this->db->get ('coaching_course_batches');
	    return $sql->result_array ();
	}
	
	// Get single batch details
	public function get_batch_details ($batch_id=0) {
	    $this->db->where ('batch_id', $batch_id);
	    $sql = $this->db->get ('coaching_course_batches');
	    return $sql->row_array ();
	}
	
	// Check if user in batch
	public function user_in_batch ($coaching_id=0, $member_id=0, $batch_id=0) {
	    $this->db->where ('coaching_id', $coaching_id);
	    $this->db->where ('member_id', $member_id);
	    $this->db->where ('batch_id', $batch_id);
	    $sql = $this->db->get ('coaching_course_batch_users');
	    return $sql->row_array ();
	}
	
	// Save member batch ???????????
	public function save_member_batch ($member_id=0) { 
		$batch_id = $this->input->post ('batch');
		$profile = array (
						'member_id'=>$member_id,
						'batch_id'=>$batch_id,
					);
					
		$this->db->where ('member_id', $member_id);
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->get ('coaching_course_batch_users');
		if ($sql->num_rows () == 0) {
			// create profile
			if ($batch_id > 0) {
				$this->db->insert ('coaching_course_batch_users', $profile);				
			}
		}
	}
	
	public function member_batches ($member_id = 0) {
		$result = array ();		
		if($member_id > 0){			
			$this->db->where('member_id', $member_id);
		}					
		$sql = $this->db->get ('coaching_course_batch_users');		
		if($sql->num_rows() > 0){
			$result = $sql->result_array ();		
		}
		return $result;
	}	
	
	public function get_member_batches ($batch_id=0, $batch_type=0) {
		
		$time = time ();
		$result = false;
		if ($batch_type > 0) {			
			if ($batch_type == UPCOMING_BATCH) {
				$this->db->where ('start_date >', $time);
				$this->db->where ('end_date >', $time);
			} else if ($batch_type == ONGOING_BATCH) {
				$this->db->where ('start_date <=', $time);
				$this->db->where ('end_date >=', $time);
			} else {
				$this->db->where ('start_date <', $time);
				$this->db->where ('end_date <', $time);			
			}
		}
		
		if ($batch_id > 0) {
			$this->db->where ('batch_id', $batch_id);
		}
		
		$sql = $this->db->get ('coaching_course_batches');
		
		if ($batch_id > 0) {
			return $sql->row_array ();
		} else {
			return $sql->result_array ();				
		}
	
	}
	
	public function create_batch ($coaching_id=0, $batch_id=0) {
		
		$time = time ();
		$data = array ();
		$data['batch_code'] = $this->input->post ('batch_code');
		$data['batch_name'] = $this->input->post ('batch_name');
		$start_date = $this->input->post ('start_date');
		$end_date 	= $this->input->post ('end_date');
		if (! empty($start_date)) {
			list ($sy, $sm, $sd)	= explode ('-', $start_date);
			$sdate = mktime (0, 0, 0, $sm, $sd, $sy);			
			$data['start_date'] = ($sdate);
		}
		if (! empty($end_date)) {
			list ($ey, $em, $ed)	= explode ('-', $end_date);
			$edate = mktime (0, 0, 0, $em, $ed, $ey);
			$data['end_date'] = ($edate);
		}
		
		if ($batch_id > 0) {
			$this->db->where ('batch_id', $batch_id);
			$this->db->update ('coaching_course_batches', $data);			
		} else {
			$data['status'] = 1;
			$data['coaching_id'] = $coaching_id;
			$data['creation_date'] = $time;
			$data['created_by'] = intval ($this->session->userdata ('member_id'));
			$this->db->insert ('coaching_course_batches', $data);
		}
	}

	public function batch_users ($batch_id=0) {
		$this->db->select ('M.*, SR.description');
		$this->db->from ('coaching_course_batch_users MB, sys_roles SR');
		$this->db->join ('members M', 'MB.member_id=M.member_id');
		$this->db->where ('MB.batch_id', $batch_id);
		$this->db->where ('SR.role_id=M.role_id');
		$sql = $this->db->get ();
		if  ($sql->num_rows () > 0 ) {
			$result = $sql->result_array ();
		}else{
			$result = false;
		}		
		return $result;
	}	

	public function users_not_in_batch ($batch_id=0, $coaching_id=0) {
		// Get batch users
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->get ('coaching_course_batch_users');
		$users = $sql->result_array ();
		$data = [];
		if (! empty ($users)) {
			foreach ($users as $u) {
				$data[] = $u['member_id'];
			}
		}
		// Get all users not in batch
		$this->db->select ('M.*, R.description');
		$this->db->from ('members M');
		$this->db->join ('sys_roles R', 'M.role_id=R.role_id');
		if (! empty($data)) {
			$this->db->where_not_in ('M.member_id', $data);
		}
		$this->db->where ('M.coaching_id', $coaching_id);
		$sql = $this->db->get ();
		$users = $sql->result_array ();
		return $users;
	}	
	
	public function save_batch_users ($batch_id=0) {
		
		$users = $this->input->post ('users');
		foreach ($users as $member_id) {
			$this->db->where ('batch_id', $batch_id);
			$this->db->where ('member_id', $member_id);
			$sql = $this->db->get ('coaching_course_batch_users');
			if  ($sql->num_rows () == 0 ) { 
				$data['member_id'] = $member_id;
				$data['batch_id']  = $batch_id;
				$sql = $this->db->insert ('coaching_course_batch_users', $data);
				//echo $this->db->last_query ();
			}
		}
	}	

	public function remove_batch_user ($batch_id=0, $member_id=0) {		
		$this->db->where ('batch_id', $batch_id);
		$this->db->where ('member_id', $member_id);
		$sql = $this->db->delete ('coaching_course_batch_users');
	}

	public function delete_batch ($batch_id=0) {
		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->delete ('coaching_course_batches');

		$this->db->where ('batch_id', $batch_id);
		$sql = $this->db->delete ('coaching_course_batch_users');
	}
	
	/*
	EXPORT USERS
	*/
	public function upload_users_csv ($coaching_id=0, $batch_id=0, $data=[]) {

		$data['coaching_id']  	= $coaching_id;
		$data['link_send_time']	= time();
		$data['status']  		= USER_STATUS_ENABLED;
		$data['creation_date'] 	= time ();
		$data['created_by'] 	= intval($this->session->userdata ('member_id'));
		
		$sql = $this->db->insert ('members', $data);
		$member_id = $this->db->insert_id ();

		// Set Userid
		$user_id = $this->generate_reference_id ($member_id);
		$this->db->set ('login', $user_id);
		$this->db->set ('adm_no', $user_id);
		$this->db->where ('member_id', $member_id);
		$this->db->update ('members');

		// Save batch
		if ($batch_id > 0) {			
			$this->db->where ('batch_id', $batch_id);
			$this->db->where ('member_id', $member_id);
			$sql = $this->db->get ('coaching_course_batch_users');
			if  ($sql->num_rows () == 0 ) { 
				$data['member_id'] = $member_id;
				$data['batch_id']  = $batch_id;
				$sql = $this->db->insert ('coaching_course_batch_users', $data);
			}
		}
	}

	public function  import_cleanup () {
		$this->db->where ('login', '');
		$this->db->where ('adm_no', '');
		$this->db->or_where ('login', NULL);
		$this->db->or_where ('adm_no', NULL);
		$this->db->delete ('members');
	}
	
	public function export_to_csv ($coaching_id=0, $role_id=USER_ROLE_STUDENT, $members=array()) {
 
		$response = array(); 
		// Select record
		$select = array("login", "adm_no", "sr_no", "email", "first_name", "second_name", "last_name","dob", "gender", "address", "postal", "city", "province", "country", "primary_contact", "mobile", "fax");
		$this->db->select($select);
		if ( ! empty ($members)) {
			$this->db->where_in ('member_id', $members);			
		} else {
			$this->db->where ('role_id', $role_id);			
		}
		$q = $this->db->get('members');
		//$response = $q->result();
 
		return ($q);
    }
    


	// count members of specific role
	public function countMemberInRole ($role_id=0, $coaching_id=0 ){
        if ($coaching_id > 0) {
    		$this->db->where('coaching_id', $coaching_id);
        }
		$this->db->where('role_id', $role_id);
		$query	=	$this->db->get('members');
		if ($query->num_rows() > 0) {
			$result = $query->num_rows();
		} else {
			$result = 0;
		}
		return $result;
	}
	
	// count members of specific status
	public function countMemberByStatus($status_id=0, $role_id=0, $coaching_id=0) {
		
        if ($coaching_id > 0) {
    		$this->db->where('coaching_id', $coaching_id);
        }
        if ($role_id > 0) {
			$this->db->where('role_id', $role_id);			
		}
		$this->db->where('status', $status_id);
		$query	=	$this->db->get('members');
		if ($query->num_rows() > 0) {
			$result = $query->num_rows();
		} else {
			$result = 0;
		}
		return $result;
	}

	// count members of specific batch
	public function countMemberByBatch($batch_id){
		$this->db->where('batch_id', $batch_id);
		$query = $this->db->get ('coaching_course_batch_users');		
		if($query->num_rows() > 0){
			$result = $query->num_rows();
		}else{
			$result = 0;
		}
		return $result;
	}	
	

    
	/* Auto Generate Reference ID */
	public function generate_reference_id ($member_id=0) {
		$prefix = array ();
		$ref_prefix	= $this->common_model->get_sys_parameters (SYS_REF_ID_PREFIX);
		foreach ($ref_prefix as $row) {
			$prefix[] = $row['paramkey'];
		}
		
		if ($member_id > 0) {
			// This means member record is already inserted and the primary key is passed as member_id
			$num = $member_id;
		} else {
			// Member record is not yet inserted, show a 
			$this->db->select_max ('member_id');
			$sql = $this->db->get ('members');
			if ($sql->num_rows () > 0) {
				$row = $sql->row_array ();
				$id = $row['member_id'];
			} else { 
				$id = 0;
			}
			$num = $id+1;
		}
		$suffix = str_pad($num, 5, "0", STR_PAD_LEFT);		
		$ref_id = implode ('', $prefix);
		$ref_id = $ref_id . $suffix;
		return $ref_id;
	}
	
	public function email_exists ($email='', $coaching_id=0) {
		
		$this->db->where ('email', $email);
		$this->db->where ('coaching_id', $coaching_id);
		$sql = $this->db->get ('members');
		if ($sql->num_rows () > 0 ) {
			return true;
		} else {
			return false;
		}
	}	

	public function coaching_contact_exists ($contact='', $coaching_id=0, $member_id=0) {
		if ($member_id > 0) {
			$this->db->where ('member_id <>', $member_id);
		}
		$this->db->where ('primary_contact', $contact);
		$this->db->where ('coaching_id', $coaching_id);
		$sql = $this->db->get ('members');
		if ($sql->num_rows () > 0) {
			return $sql->row_array ();
		} else {
			return false;
		}
	}

	public function contact_exists ($contact='') {
		$this->db->where ('primary_contact', $contact);
		$sql = $this->db->get ('members');
		if ($sql->num_rows () > 0) {
			return $sql->result_array ();
		} else {
			return false;
		}
	}

	public function check_unique ($str='', $type='adm_no', $member_id=0) {
		
		$this->db->where ($type, $str);
		$sql = $this->db->get ('members');
		if ($member_id > 0){
			if ($sql->num_rows () > 1 ) {
				return true;
			} else{
				return false; 
			} 
		} else {
			if  ($sql->num_rows () > 0 ) {
				return true;
			} else {
				return false;
			} 			
		}
	}	

	public function file_download ($name) {
		$path = $this->config->item('temp_dir');
		$file = $path . $name;
		$data = read_file($file);
		if($data!== false){
			force_download($name, $data);
		}
	}
	
	public function send_confirmation_email ($member_id=0, $coaching_id=0) {
		$coaching 		= $this->coaching_model->get_coaching ($coaching_id);
		$coaching_name  = $coaching['coaching_name'];
		$user_details	=	$this->get_user ($member_id);
		$email 	= $user_details['email'];
		$userid = $user_details['login'];
		$crypt 	= md5 ($userid);
		$url	=	'student/login/create_password/?sub='.$crypt;
		$link	=	anchor($url, 'Click here to create your password');
		$name 	= $user_details['first_name'].' '.$user_details['last_name'];
		$to 	= $email;
		$subject = 'Create Password';
		$message = '<strong> Greetings '.$name.'! </strong> 
					  <p>Your email is registered in our coaching <b>'.$coaching_name.'</b><br>
					  <p>Your login-id is:<b>'.$userid.'</b><br> 
					  '.$link.'. <br>
					  Alternatively, you can also copy-paste the following url in your browser.</p>
					  <p><strong>'.site_url($url).'</strong></p><br><br>
					  <p style="text-align:center; "><a style="padding:8px 12px; background:#4caf50; border: 1px solid #4caf50; color: white; text-decoration: none" href="'.site_url($url).'">Create your password</a></p>';
		$this->common_model->send_email ($to, $subject, $message, CONTACT_EMAIL, $coaching_name);
		// Update mail sent time
		$time = time ();
		$this->db->set ('link_send_time', $time);
		$this->db->where ('member_id', $member_id);
		$this->db->update ('members');
	}
	
	public function reset_password ($member_id=0) {
		$otp = random_string ('numeric', 6);
		$hashed = password_hash ($otp, PASSWORD_DEFAULT);
		$this->db->set ('password', $hashed);
		$this->db->where ('member_id', $member_id);
		$this->db->update ('members');
		return $otp;
	}

}