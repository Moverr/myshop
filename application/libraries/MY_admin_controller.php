<?php
class MY_admin_controller extends MY_Controller {
    
   
    public function __construct() 
        { 
            parent::__construct();

            //load user model
            $this->load->model('user_m');

            //if the user is not logged in redirect them away
            if($this->session->userdata('logged_in')==FALSE)
            {
                redirect(base_url().'admin/login');

            }
        
        }
}
