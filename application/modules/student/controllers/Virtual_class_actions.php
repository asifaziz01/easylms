<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Virtual_class_actions extends MX_Controller {	


	public function __construct () {		
	    // Load Config and Model files required throughout Users sub-module
	    $config = ['config_student', 'config_virtual_class', 'config_course'];
	    $models = ['virtual_class_model', 'users_model', 'courses_model', 'enrolment_model'];
	    $this->common_model->autoload_resources ($config, $models);

        $cid = $this->uri->segment (4);        
        
        // Security step to prevent unauthorized access through url
        if ($this->session->userdata ('is_admin') == TRUE) {
        } else {
            if ($cid == true && $this->session->userdata ('coaching_id') <> $cid) {
                $this->message->set ('Direct url access not allowed', 'danger', true);
                redirect ('student/home/dashboard');
            }
        }
	}

    public function get_new_recordings  ($coaching_id=0, $class_id=0, $meeting_id=0, $course_id=0, $batch_id=0) {

    	$api_setting = $this->virtual_class_model->get_api_settings ();
		$class = $this->virtual_class_model->get_class ($coaching_id, $class_id);

		// Create call and query
		$api_url = $api_setting['api_url'];
		$shared_secret = $api_setting['shared_secret'];

		$call_name = 'getRecordings';
		$query_string = 'meetingID='.$class['meeting_id'];
		$final_string = $call_name . $query_string . $shared_secret;
		$checksum = sha1($final_string);

		$url = $api_url . $call_name . '?' . $query_string . '&checksum='.$checksum;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$xml_response = curl_exec($ch);
		curl_close($ch);

		$xml = simplexml_load_string($xml_response);

		//print_r ($xml);

		$response = $xml->returncode;
		$result = [];
		if ($response == 'SUCCESS') {
			$recordings = $xml->recordings;
			foreach ($recordings->recording as $recording) {
				$data = [];
				$start_time = floatval ($recording->startTime);
				$end_time = floatval ($recording->endTime);
				$duration = ($end_time - $start_time)/1000;
				$duration_mm = round ($duration / 60,  2);

				$url = $recording->playback->format->url;
				$preview = $recording->playback->format->preview;
				$thumb_url = '';
				if ( ! empty ($preview)) {
					foreach ($preview->images->image as $i=>$image) {
						if ($image && $image != '') {
							$thumb_url = $image;
						}
					}		
				}

				$data['coaching_id'] = $coaching_id;
				$data['class_id'] = $class_id;
				$data['meeting_id'] = $meeting_id;
				$data['course_id'] = $course_id;
				$data['batch_id'] = $batch_id;
				$data['recording_id'] = $recording->recordID;

				if ( $this->virtual_class_model->recording_exists ($data) == false) {
					$data['recording_name'] = $recording->name;
					$data['publish_date'] = $start_time/1000;
					$data['duration'] = $duration;
					$data['publish_url'] = $url;
					$data['thumb_url'] = $thumb_url;
					if ($recording->published == 'true') {
						$data['status'] = 1;
					} else {
						$data['status'] = 0;
					}
					$id = $this->virtual_class_model->add_recording_data ($data);
					$data['id'] = $id;
					$result[] = $data;
				}
			}
		}

		$output['coaching_id'] = $coaching_id;
		$output['class_id'] = $class_id;
		$output['meeting_id'] = $meeting_id;
		$output['course_id'] = $course_id;
		$output['batch_id'] = $batch_id;
		$output['class'] = $class;
		$output['result'] = $result;
		$html = $this->load->view ('virtual_class/inc/recordings', $output, true);

		$this->output->set_content_type("application/json");
        $this->output->set_output(json_encode(array('status'=>true, 'data'=>$html)));

    }

}