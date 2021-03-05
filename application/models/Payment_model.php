<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Payment_model extends CI_Model {


	public function get_api_details ($short_name='razorpay')	{
		$this->db->where ('short_name', $short_name);
		$sql = $this->db->get ('payment_gateways');
		$row = $sql->row_array ();
		return $row;
	}
	
	public function create_order ($data=[])	{ 
		$order_id = 0;
		if (! empty ($data)) {
			$this->db->where ($data);
			$sql = $this->db->get ('payment_orders');
			if ($sql->num_rows () == 0) {
				$data['transaction_date'] = time ();
				$sql = $this->db->insert ('payment_orders', $data);
				$order_id = $this->db->insert_id ();
			}

		}
		return $order_id;
	}



}