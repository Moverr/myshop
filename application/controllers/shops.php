 <?php 
ob_start();

 #Manage Shops, CRUD


class Shops extends CI_Controller {
	
	# Constructor
	function __construct() 
	{	
		 
		
		parent::__construct();	
		$this->load->library('form_validation'); 
		$this->load->model('_shop_m','shop');
		$this->load->model('users_m','user1');
		$this->load->model('sys_email','sysemail');			 	 
        $this->load->model('Remoteapi_m');  	
        $this->load->model('_shop_m');  	
	}

	function verify()
	{
		 $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;


       

        if(!empty($data['action']))
        {

        	 

        	$instructions = $data; 
        	$response = $this->shop->verify($instructions); 

        	if($response == true)
        	{
        		$data['msg'] = "SUCCESS: Record   Deleted   Succesfully ";
        	}
        	else
        	{
        		$data['msg'] = "WARNING: Record Not Deleted Or Was not Deleted Succesfully ";
        	}
        		 //manage_shops_v
 	  
        }

        $data['list'] = $this->shop->get_shops($data);


		  #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage Shops";        
        $data['current_menu'] = 'view_shops';
        $data['view_to_load'] = 'shops/manage_shops_v';
        $data['view_data']['form_title'] = $data['page_title'];
        	

        $this->load->view('dashboard_v', $data);



	}


	function lists()
	{
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        
		 //manage_shops_v
 		$data['list'] = $this->shop->get_shops($data);


		  #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage Shops";        
        $data['current_menu'] = 'view_shops';
        $data['view_to_load'] = 'shops/manage_shops_v';
        $data['view_data']['form_title'] = $data['page_title'];
        	

        $this->load->view('dashboard_v', $data);

	}



	function add(){

  		  
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        $data['view_data']['formdata'] = $_POST;

        if(!empty($_POST))
        {

	        $user_id = $this->session->userdata('userid');
	        $details = $_POST;
	        //When Edititng 
	        if(!empty($data['i']))
	        {
	        	  $details['id'] = decryptValue($data['i']);
	        }


	        $response = $this->shop->save_shop($user_id,$details);
 

			$data['userdetails'] =  !empty($response['userdetails']) ? $response['userdetails']  : "" ; 

			$data['msg'] = !empty($response['msg']) ? $response['msg']  : "" ;
			$data['requiredfields'] = !empty($response['requiredfields']) ? $response['requiredfields']  : "" ;
 
			if(!empty($response['status']) && $response['status'] == 'success')
			{
			   $data['view_data']['formdata'] = "";			 	
			}

			  


        }
        else
        {
        	 if(!empty($data['i']))
	        {
	        	  $details['id'] = decryptValue($data['i']);
	        	  $data['view_data']['formdata'] = $this->shop->get_shops($details)['page_list'][0];
	        	  $data['view_data']['formdata']['address'] =  $data['view_data']['formdata']['location'];

	        	 

	        }


        }



	  #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i'])? 'Edit  Shop Details ' : 'Add New Shop ');
        $data['current_menu'] = 'create_shop';
        $data['view_to_load'] = 'shops/shop_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
        	

        $this->load->view('dashboard_v', $data);

		 
	}
	

}