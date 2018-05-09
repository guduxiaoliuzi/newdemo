<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {


	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function  add(){
		$this->load->model('News_model');
		$data = $this->News_model->getUser();
		echo '<pre>';
		print_r($data);
	}
}
