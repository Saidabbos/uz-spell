<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';



class Welcome extends \Restserver\Libraries\REST_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->library('hunspell');
		if (!$this->post('action') || $this->post('lang') || $this->post('text')) {
			$this->response([
				'status' => FALSE,
				'message' => "Parametrlar noto'g'ri berilgan"
			], \Restserver\Libraries\REST_Controller::HTTP_BAD_REQUEST);
		}
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function spell_post()
	{
		
		$this->response([
			'status' => TRUE,
			'misspelled' => $this->hunspell->spell($this->post('text'))
		], 200);
	}
	public function suggest_post() {
		$this->response([
			'status' => TRUE,
			'misspelled' => $this->hunspell->suggest($this->post('text'))
		], 200);
	}
	
	public function translit_post() {
		
	}
}
