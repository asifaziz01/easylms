<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once (APPPATH . 'third_party/payment_gateways/razorpay/Razorpay.php');
use Razorpay\Api\Api;

class Razorpay_model extends CI_Model {

	var $api = '';
	var $api_key = 'rzp_test_Xt7Zg20WZJvbad';
	var $api_secret = 'eDupGkemGnsPHasRxYueIfkq';

	public function init_api () {
		$details = $this->payment_model->get_api_details ();
		$api_key = $details['key_id'];
		$api_secret = $details['key_secret'];
		$api = new Api($api_key, $api_secret);

		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
		$this->api = $api;
	}

	public function create_order($coaching_id=0, $course_id=0, $batch_id=0, $member_id=0, $amount=0) {
		$api_key = $this->api_key;
		$api_secret = $this->api_secret;		
		$api = new Api($api_key, $api_secret);

		$data['amount'] = $amount;
		$data['currency'] = 'INR';
		$order  = $api->order->create($data);

		$order_id = $order->id;

		$data['order_id'] = $order_id;
		$data['coaching_id'] = $coaching_id;
		$data['course_id'] = $course_id;
		$data['batch_id'] = $batch_id;
		$data['member_id'] = $member_id;
		$this->payment_model->create_order ($data);
	}
}