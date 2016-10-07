<?php
class MY_frontend_controller extends MY_Controller {
    
   
    public function __construct() 
        { 
            parent::__construct();

            //load user model
            $this->load->model('user_m');

            //load user type model model
        }
}
