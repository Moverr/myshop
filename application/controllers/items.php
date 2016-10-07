 <?php 
ob_start();

 #Manage items, CRUD


class Items extends CI_Controller {
	
	# Constructor
	function __construct() 
	{	
		 
		
		parent::__construct();	
		$this->load->library('form_validation'); 
	    $this->load->model('_item_m','item');



		$this->load->model('users_m','user1');
		$this->load->model('sys_email','sysemail');			 	 
        $this->load->model('Remoteapi_m');  	
        $this->load->model('_item_m');  	
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

        	$response = $this->item->verify($instructions); 

        	if($response == true)
        	{
        		$data['msg'] = "SUCCESS: Record   Deleted   Succesfully ";
        	}
        	else
        	{
        		$data['msg'] = "WARNING: Record Not Deleted Or Was not Deleted Succesfully ";
        	}
        		 //manage_items_v
 	  
        }
        else
        {
              $data['list'] = $this->item->get_items($data);


          #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage items";        
        $data['current_menu'] = 'view_items';
        $data['view_to_load'] = 'items/manage_items_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);



        }

      


	}
    function get_item_detail(){
            # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        
        $item_id = !empty($_POST['item_id']) ? $_POST['item_id'] : '' ;
        $data['id'] = $item_id;
        $response = $this->item->get_items($data);
        if(!empty($response['page_list']))
        {
            echo json_encode($response['page_list']);
        }
        else
        {
             echo "0";
        }
        
    }


	function lists()
	{
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        
		 //manage_items_v
 		$data['list'] = $this->item->get_items($data);


		  #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage items";        
        $data['current_menu'] = 'view_items';
        $data['view_to_load'] = 'items/manage_items_v';
        $data['view_data']['form_title'] = $data['page_title'];
        	

        $this->load->view('dashboard_v', $data);

	}

    function listcategories()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
             //check permission
        check_user_access($this, 'manage_itemcategories', 'redirect');

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        
         //manage_items_v
        $data['list'] = $this->item->get_categories($data);


          #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage Categories";        
        $data['current_menu'] = 'manage_itemcategories';
        $data['view_to_load'] = 'items/manage_itemcategories_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);


    }



    function listitems(){
           # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
           //check permission
        check_user_access($this, 'manage_items', 'redirect');


        $current_financial_year = currentyear.'-'.endyear;

        
         //manage_items_v
        $data['list'] = $this->item->get_items($data);

        


          #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage Items";        
        $data['current_menu'] = 'manage_items';
        $data['view_to_load'] = 'items/manage_items_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);


    }



	function addcategory(){

  		  
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

       
        //check permission
        check_user_access($this, 'create_itemcategory', 'redirect');


        if(!empty($_POST))
        {
            $data['view_data']['formdata'] = $_POST;
	        $user_id = $this->session->userdata('userid');
	        $details = $_POST;


	        //When Edititng 
	        if(!empty($data['i']))
	        {
	        	  $details['id'] = decryptValue($data['i']);
	        }


	        $response = $this->item->save_category($user_id,$details);
 

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
	        	  $data['view_data']['formdata'] = $this->item->get_categories($details)['page_list'][0];
	        	 # $data['view_data']['formdata']['address'] =  $data['view_data']['formdata']['location'];
                $data['view_data']['formdata']['itemcategory'] = $data['view_data']['formdata']['category']; 


	        }


        }



	  #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i'])? 'Edit  Category Details ' : 'Add Item Category');
        $data['current_menu'] = 'create_itemcategory';
        $data['view_to_load'] = 'items/itemcategory_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
        	

        $this->load->view('dashboard_v', $data);

		 
	}




    function additem(){

          
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        $data['view_data']['formdata'] = $_POST;




        //check permission
        check_user_access($this, 'create_itemcategory', 'redirect');


        if(!empty($_POST))
        {
            $user_id = $this->session->userdata('userid');
            $details = $_POST;


            //When Edititng 
            if(!empty($data['i']))
            {
                  $details['id'] = decryptValue($data['i']);
            }


            $response = $this->item->save_item($user_id,$details);
 

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
                  $data['formdata'] = $this->item->get_items($details)['page_list'][0];
                  $data['formdata']['category'] =    $data['formdata']['category_id'];
                  $data['formdata']['item'] =    $data['formdata']['name'];
                  $data['formdata']['details'] =    $data['formdata']['details']; 

                  $data['view_data']['formdata'] = $data['formdata']; 

            }


        }


        $data['category'] = $this->item->get_categories($data);

      #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i'])? 'Edit  Item Details ' : 'Add Item ');
        $data['current_menu'] = 'create_items';
        $data['view_to_load'] = 'items/item_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);

         
    }


	

}