 <?php 
ob_start();

 #Manage items, CRUD


class Stock extends CI_Controller {
	
	# Constructor
	function __construct() 
	{	
		 

		
		parent::__construct();	

		$this->load->library('form_validation'); 
	    $this->load->model('_item_m','item');
        $this->load->model('_stock_m','stock');


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

        	$response = $this->stock->verify($instructions); 

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


    function  get_stock_ajax(){
            # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear; 
        
         //manage_items_v
        $data['list'] = $this->item->get_latest_stock_details($data);

        print_r($data['list']);

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
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        
         //manage_items_v
        $data['list'] = $this->item->get_categories($data);


          #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage Categories";        
        $data['current_menu'] = 'view_items';
        $data['view_to_load'] = 'items/manage_itemcategories_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);


    }

    function get_stock_detail()
    {
          $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data['level'] = !empty($data['level']) ? $data['level']  :  "overview";
        

       # $current_financial_year = currentyear.'-'.endyear;

        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];
        
        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;
        
        }
        
        $userid = $this->session->userdata('userid');
        $shopid = $this->session->userdata('shopid');
        $branch = $this->session->userdata('branch');
        
        $item_id = !empty($_POST['item_id']) ? $_POST['item_id'] : '' ;
        $data['item_id'] = $item_id;
         $response = $this->stock->get_stock($data);

        if(!empty($response['page_list']))
        {
            echo json_encode($response['page_list'][0]);
        }
        else
        {
             echo "0";
        }

    }



    function manage_stock(){
           # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data['level'] = !empty($data['level']) ? $data['level']  :  "overview";
        

       # $current_financial_year = currentyear.'-'.endyear;

        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];
        
        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;
        
        }
        
        $userid = $this->session->userdata('userid');
        $shopid = $this->session->userdata('shopid');
        $branch = $this->session->userdata('branch');



        $financial_searchstring = " 1=1   AND IT.shopid = ".$shopid." AND ST.isactive ='Y' AND IT.branch_id = ".$branch."  ";

        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();



         $data['limit'] = 10000;

        
         //manage_items_v
        $data['list'] = $this->stock->get_stock($data);

        


          #fetch_ifb_procurement_entries
        $data['page_title'] =  "Manage Stock";        
        $data['current_menu'] = 'manage_stock';
        $data['view_to_load'] = 'stock/manage_stock_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);


    }



	function add_stock(){

        
  		  
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        $data['view_data']['formdata'] = $_POST;

        //check permission
        check_user_access($this, 'add_stock', 'redirect');


        if(!empty($_POST))
        {
	        $user_id = $this->session->userdata('userid');
	        $details = $_POST;



	        //When Edititng 
	        if(!empty($data['i']))
	        {
	        	  $details['id'] = decryptValue($data['i']);
	        }


	        $response = $this->stock->save_stock($user_id,$details);
 

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
	        	  $data['view_data']['formdata'] = $this->item->get_items($details)['page_list'][0];
	        	  $data['view_data']['formdata']['address'] =  $data['view_data']['formdata']['location'];

	        	 

	        }


        }

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();


        $data['items'] = $this->item->get_items($data);




	  #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i'])? 'Edit  Stock  ' : 'Add Stock ');
        $data['current_menu'] = 'add_stock';
        $data['view_to_load'] = 'stock/stock_form_v';
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
                  $data['view_data']['formdata'] = $this->item->get_items($details)['page_list'][0];
                  $data['view_data']['formdata']['address'] =  $data['view_data']['formdata']['location'];

                 

            }


        }


          $data['category'] = $this->item->get_categories($data);

      #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i'])? 'Edit  Item Details ' : 'Add Item ');
        $data['current_menu'] = 'create_item';
        $data['view_to_load'] = 'items/item_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
            

        $this->load->view('dashboard_v', $data);

         
    }


	

}