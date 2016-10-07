<?php
ob_start();
?>
<?php 

#**************************************************************************************
# All bid actions directed from this controller
#**************************************************************************************

class Notifications extends CI_Controller {
	
	# Constructor
	function Notifications() 
	{	
		 
	 
		
		parent::__construct();	
		$this->load->library('form_validation'); 
		$this->load->model('users_m','user1');
		$this->load->model('Notification_m','notification');
		$this->session->set_userdata('page_title','Login');

		#MOVER LOADED MODELS
		$this->load->model('Receipts_m');
		$this->load->model('Proc_m');
		$this->load->model('Evaluation_methods_m');
		
        
		access_control($this);
		##END
		date_default_timezone_set(SYS_TIMEZONE);
		$data = array();		
	}
	
	function view_notification(){
		
		$urldata = $this->uri->uri_to_assoc(3, array('m'));

	# Pick all assigned data
	$data = assign_to_data($urldata);
	$notificationid = base64_decode($data['notification']);
 
 	$data['rslt'] = $this->notification->view_notification($notificationid);
	$this->notification->update_viewed_list($notificationid);
	
	 #form type
    $data['switch'] = 'single_notification';   
    $data['page_title'] = 'Notifications ';
    $data['current_menu'] = 'user-information';
    $data['view_to_load'] = 'notifications/notifications_v';
    $data['tabtitle'] = 'Notification Detail ';	
    $this->load->view('dashboard_v', $data);

	}
	function view_all_notifications()
	{ 
	 # Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));

		# Pick all assigned data
		 $data = assign_to_data($urldata);

 		 #print_r($data); exit();
		 $level = $data['level'];
		 if(empty($level))
		 {
		 $level = 'all';
		 }
		# print_r($level); exit();
		 $userid = base64_decode($data['member']); 
		 $data = $this->notification->fetch_notifications($userid,0,$data,$level);

//        print_array($this->db->last_query());
//
//        print_array($this->db->last_query());

        $data['level'] = $level;
		$data['switch'] = 'notifications';   
		$data['page_title'] = 'Notifications ';
		$data['current_menu'] = 'user-information';
		$data['view_to_load'] = 'notifications/notifications_v';
		$data['tabtitle'] = 'All Notifications';
        $this->load->view('dashboard_v', $data);


    }

    //Delete Archive Star Trash Notification
    function del_archive()
    {
        $result = $this->notification->del_archive($_POST);
        print_r($result);
	}
	
	 
 

	
}
?>