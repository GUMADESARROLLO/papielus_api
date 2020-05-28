<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Servicio_controllers extends CI_Controller {

	public function index()
	{
		//$this->load->view('welcome_message');
	}


    public function update_sku()
    {
        $this->servicios_model->update_sku();
    }



}
