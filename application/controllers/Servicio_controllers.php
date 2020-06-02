<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Servicio_controllers extends CI_Controller {

	public function index()
	{
		//$this->load->view('welcome_message');
	}


 public function update_sku($operacion, $numFact)
    {
        $this->servicios_model->update_sku($operacion, $numFact);
    }

    // public function update_sku($numFact)
    // {
    //     $this->servicios_model->update_sku($numFact);
    // }

    // public function substract_sku($numFact){
    // 	$this->servicios_model->substract_sku($numFact);
    // }



}
