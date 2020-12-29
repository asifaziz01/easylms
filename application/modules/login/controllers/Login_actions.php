<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login_actions extends MX_Controller {


	public function __construct () {
		$config = ['config_login'];
	    $models = ['login_model', 'coaching/users_model', 'coaching/coaching_model', 'coaching/settings_model'];
	    $this->common_model->autoload_resources ($config, $models);
	    $this->load->helper ('string');
	}

    public function validate_login ($admin_login=false) {
	
		$this->form_validation->set_rules ('username', 'Username', 'required|trim');
		$this->form_validation->set_rules ('password', 'Password', 'required|trim');
		$this->form_validation->set_rules ('access_code', 'Access Code', 'required|trim');
		
		if ($this->form_validation->run () == true) {
			$captcha = $this->input->post('captcha');
			$captcha_key = $this->session->userdata('captcha_key');
			
		
		
			if($captcha != $captcha_key['word'] && $captcha_key['ip_address'] == $_SERVER['REMOTE_ADDR']){
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('INVALID_CAPTCHA', 'msg'))));
				
			}
			else
			{

				$response = $this->login_model->validate_login ($admin_login);

				if ($response['status'] == LOGIN_SUCCESSFUL) {
					$redirect = $this->session->userdata ('dashboard');
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array(
						'status'=>true, 
						'message'=>_AT_TEXT ('LOGIN_SUCCESSFUL', 'msg'), 
						'user_token'=>$this->session->userdata ('user_token'),
						'redirect'=>site_url($redirect),
					)));
				} else if ($response['status'] == INVALID_CREDENTIALS) {
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('INVALID_CREDENTIALS', 'msg'))));
				} else if ($response['status'] == ACCOUNT_DISABLED) {
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('ACCOUNT_DISABLED', 'msg'))));
				} else if ($response['status'] == MAX_ATTEMPTS_REACHED) {
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('MAX_ATTEMPTS_REACHED', 'msg'))));
				} else if ($response['status'] == INVALID_PASSWORD) {
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('INVALID_PASSWORD', 'msg'))));
				} else if ($response['status'] == INVALID_USERNAME) {
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('INVALID_USERNAME', 'msg'))));
				} else {
					unset($_SESSION['captcha_key']);
					unlink(base_url().'contents/captcha/'.$captcha_key['time'].'.jpg');
					$this->output->set_content_type("application/json");
					$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('LOGIN_ERROR', 'msg'))));
					
				}
			}
			
			
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('VALIDATION_ERROR', 'msg') )));			
		}
	}
	

	public function register () {
		$this->form_validation->set_rules('first_name', 'Name', 'required|trim', ['required'=>'Please enter Your Name.']); 
		$this->form_validation->set_rules ('primary_contact', 'Primary Contact', 'required|is_natural|trim|max_length[14]');
		$this->form_validation->set_rules('email', 'Email', 'valid_email|trim');
		$this->form_validation->set_rules ('password', 'Password', 'required|min_length[8]');
		$this->form_validation->set_rules('agree', 'Agree', 'required', ['required'=>'You must agree to the terms and conditions']); 
		$this->form_validation->set_rules ('access_code', 'Access Code', 'required|trim', ['required'=>'Please enter your access code which you recieved from your institution']);
		 
		if ( $this->form_validation->run() == true) {
			
			$ac = $this->input->post ('access_code');
			$coaching = $this->coaching_model->get_coaching_by_slug ($ac);
			$coaching_id = $coaching['id'];
			$email 	= $this->input->post ('email');
			$contact 	= $this->input->post ('primary_contact');

			$coaching_sub = $this->coaching_model->get_coaching_subscription ($coaching_id);			
			$max_users = $coaching_sub['max_users'];
			$num_users = $this->users_model->count_all_users ($coaching_id);

			$captcha = $this->input->post('captcha');
			$captcha_key = $this->session->userdata('captcha_key');

			if($captcha != $captcha_key['word'] && $captcha_key['ip_address'] == $_SERVER['REMOTE_ADDR']){
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>_AT_TEXT ('INVALID_CAPTCHA', 'msg'))));
				
			}
			
			else{
			if (! $coaching) {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'You have provided wrong access code' )));
			} else if ( $num_users >= $max_users) {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'Maximum user account limit for current subscription plan has been reached. Contact your coaching owner to upgrade their subscription plan.' )));
			} else if ($this->users_model->coaching_contact_exists ($contact, $coaching_id) == true) {
				// Check if already exists
				unset($_SESSION['captcha_key']);
					unlink(base_url().'contents/captcha/'.$captcha_key['time'].'.jpg');
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'You already have an account with this mobile number. Try Sign-in instead' )));
			} else {

				$user_role = $this->input->post ('user_role');
				$status = USER_STATUS_UNCONFIRMED;

				// Get coaching settings
				$settings = $this->settings_model->get_settings ($coaching_id);
				if ($user_role == USER_ROLE_STUDENT) {
					$approve = $settings['approve_student'];
					if ($approve == 1) {
						$status = USER_STATUS_ENABLED;
					}
				} else if ($user_role == USER_ROLE_TEACHER) {
					$approve = $settings['approve_teacher'];
					if ($approve == 1) {
						$status = USER_STATUS_ENABLED;
					}
				}

				// Save user details
				$member_id = $this->users_model->save_account ($coaching_id, 0, $status);

				// Get coaching details
				$coaching_name = $coaching['coaching_name'];
				$user_name = $this->input->post ('first_name');
				
				// Notification Email to coaching admin
				if ($coaching['email'] != '') {					
					$to = $coaching['email'];
					$subject = 'New Registration';
						$email_message = 'A new user <strong>'.$user_name.'</strong> has registered in your coaching <strong>'.$coaching_name. '</strong>. ';
					if ($status == USER_STATUS_UNCONFIRMED) {
						$email_message .= 'Account is pending for approval. Click here for details ' . anchor ('coaching/users/index/'.$coaching_id.'/'.$user_role.'/'.USER_STATUS_UNCONFIRMED);
					} 
					$this->common_model->send_email ($to, $subject, $email_message);
				}
			
				// Notification email to user
				if ($status == USER_STATUS_UNCONFIRMED) {
					// Email message for user
					$email_message = '<strong> Hi '.$user_name.',</strong><br>
					<p>You have created an account in <strong>'.$coaching_name.'</strong>. You can login with your registered email and password once your account is approved. You will receive another email regarding account approval.</p>';
					// Display message for user
					$message = 'Your account in '.$coaching_name.' has been created but pending for admin approval. You will be notified once your account is approved by your coaching admin';
					$this->message->set ($message, 'warning', true );
					// Send SMS to user
					$this->sms_model->send_sms ($contact, $message);
				} else {
					
					// Email message for user
					$email_message = '<strong> Hi '.$user_name.',</strong><br>
					<p>You have created an account in <strong>'.$coaching_name.'</strong>. Your account is active now. You can login with your registered email and password.</p>';
					
					// Display message for user
					$message = 'Your account has been created. You can log-in to your account';
					
					// Send SMS to user
					$data['name'] = $user_name;
					$data['coaching_name'] = $coaching['coaching_name'];
					$data['access_code'] = $coaching['reg_no'];
					$data['url'] = site_url ('login/login/index/?sub='.$data['access_code']);
					$data['login'] = $contact;
					$data['password'] = $this->input->post ('password');
					$message = $this->load->view (SMS_TEMPLATE . 'user_acc_created', $data, true);
					$this->sms_model->send_sms ($contact, $message);
					$this->message->set ($message, 'success', true );
				}
				if ($this->input->post ('email')) {
					$to = $this->input->post('email');
					$subject = 'Account Created';
					$this->common_model->send_email ($to, $subject, $email_message);				
				}

				
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>$message, 'redirect'=>site_url('login/user/index?sub='.$ac)) ));
				
			}
		}
	    } else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}

	public function reset_password () {
		$this->form_validation->set_rules('mobile', 'Mobile Number', 'required|numeric|max_length[15]|trim');
		$this->form_validation->set_rules('access_code', 'Access Code', 'required|trim');

		if ($this->form_validation->run () == true) {
			// check if contact exists
			$contact = $this->input->post ('mobile');
			$access_code = $this->input->post ('access_code');
			$coaching = $this->coaching_model->get_coaching_by_slug ($access_code);
			$user = $this->users_model->coaching_contact_exists ($contact, $coaching['id']);
			if ($user == false) {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'Please check your mobile number/access code and try again') ));
			} else {
				$password = $this->login_model->reset_password ($user['member_id']);
				$data['coaching_name'] = $coaching['coaching_name'];
				$data['otp'] = $password;

				// Send SMS
				$message = $this->load->view (SMS_TEMPLATE . 'reset_password', $data, true);
				$this->sms_model->send_sms ($contact, $message);
				
				// Send Email
				if ($user['email'] != '') {
					$email = $user['email'];
					$subject = 'Reset Password';
					$message = $this->load->view (EMAIL_TEMPLATE . 'reset_password', $data, true);
					$this->common_model->send_email ($email, $subject, $message);					
				}
				
				// Display Message
				$msg = 'Your password is reset. You will receive new password on your mobile and email. Use the password to sign-in to your account. After signing-in please change your password from "My Account" menu.';
				
				$this->message->set ($msg, 'success', true);
				
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>$msg, 'redirect'=>site_url('login/user/index') )));
			}
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}

	public function get_access_code () {
		$this->form_validation->set_rules('mobile', 'Mobile Number', 'required|numeric|max_length[15]|trim');

		if ($this->form_validation->run () == true) {
			// check if contact exists
			$contact = $this->input->post ('mobile');
			$user = $this->users_model->contact_exists ($contact);
			if ($user == false) {
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>false, 'error'=>'This mobile number is not registered with any coaching' )));
			} else {
				$sms_msg = '';
				$email_msg = '';
				$send_email = false;
				foreach ($user as $row) {

					$data = [];
					$coaching = $this->coaching_model->get_coaching ($row['coaching_id']);
					$data['coaching_name'] = $coaching['coaching_name'];
					$data['access_code'] = $coaching['reg_no'];
					
					// Send SMS
					$sms_msg .= $this->load->view (SMS_TEMPLATE . 'get_access_code', $data, true);
					
					// Send Email
					if ($row['email'] != '') {
						$email = $row['email'];
						$subject = 'Access Code';
						$email_msg .= $this->load->view (EMAIL_TEMPLATE . 'get_access_code', $data, true);
						$send_email = true;
					}					
				}
				
				// Send SMS
				$this->sms_model->send_sms ($contact, $sms_msg);

				// Send email
				if ($send_email == true) {
					$this->common_model->send_email ($email, $subject, $email_msg);
				}

				// Display Message
				$msg = 'We have sent Access Code on your mobile and email';
				
				$this->message->set ($msg, 'success', true);
				
				$this->output->set_content_type("application/json");
				$this->output->set_output(json_encode(array('status'=>true, 'message'=>$msg, 'redirect'=>site_url('login/user/index') )));
			}
		} else {
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'error'=>validation_errors() )));
		}
	}


	public function update_session ($user_token='') {

		$data = $this->login_model->get_user_token ($user_token);

		if (! empty ($data)) {
			// Update session
			$member_id = $data['member_id'];
			$role_id = $data['role_id'];
			$user_name = $data['user_name'];
			$coaching_id = $data['coaching_id'];
			$user_token = $data['user_token'];
			$this->login_model->save_login_session ($member_id, $role_id, $user_name, $coaching_id, $user_token);
			
			// Update menu
			$this->login_model->load_menu ($role_id);
			
			$dashboard = $this->session->userdata ('dashboard');
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>true, 'message'=>'Success', 'redirect'=>site_url ($dashboard))));
		} else {
			// Session cannot be updated due to user-token mismatch, so logout
			$logout = site_url ('login/login/logout');
			$this->output->set_content_type("application/json");
			$this->output->set_output(json_encode(array('status'=>false, 'message'=>'Error', 'redirect'=>$logout)));
		}

	}

	public function logout ($access_code='') {

		if ($this->session->userdata ('is_admin') == true) {
			$redirect = site_url ('admin/login/index');
		} else {
			$redirect = site_url ('login/user/index');
		}		
		
		$this->session->sess_destroy ();
		setcookie ("user_token", "", time () - 3600);

		$this->output->set_content_type("application/json");
		$this->output->set_output(json_encode(array('status'=>true, 'redirect'=>$redirect)));
	}
}