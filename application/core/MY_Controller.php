<?php
/**
 * 
 */
class MY_Controller extends CI_Controller
{
	
    //create public varieble data
    public $data = array();
	public function __construct() 
        { 
            parent::__construct();      
        //add some stuph into data array
        $this->data['errors'] = array();
        $this->data['site_name'] = 'my awesome site';//site name






        }
}
