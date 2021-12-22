<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Demo extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
    //Do your magic here
  }

  public function index()
  {
    $this->load->view('v_demo');
  }

}

/* End of file Demo.php */
/* Location: ./application/modules/test/controllers/Demo.php */
