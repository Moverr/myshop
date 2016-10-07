<?php
#**************************************************************************************
# All bid actions directed from this controller
#**************************************************************************************

class Bids extends CI_Controller {

    # Constructor
    function Bids()
    {
        parent::__construct();

	
        $this->load->model('users_m','users');
        $this->load->model('sys_email','sysemail');
        #date_default_timezone_set(SYS_TIMEZONE);
	
        #MOVER LOADED MODELS
        $this->load->model('Receipts_m');
        $this->load->model('Proc_m');
		//exit("pass");
        $this->load->model('Evaluation_methods_m');
        $this->load->model('sys_file','sysfile');
        $this->load->model('Disposal_m','disposal');
        $this->load->model('bid_invitation_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('special_procurement_m');
        $this->load->model('procurement_method_m');


        access_control($this);
    }



    # Default to view all bids
    function index()
    {
        #Go view all bids
        redirect('bids/manage_bid_invitations');

         
    }




    
    
	# View bids
	# View bids
	function manage_bid_invitations(){
		check_user_access($this, 'view_bid_invitations', 'redirect');

		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		$urldata2 = $this->uri->uri_to_assoc(3, array('m', 'p'));

		# Pick all assigned data
		$data = assign_to_data($urldata);

				
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
			if(!empty($data['financial_year']))
		{
			$data['current_financial_year'] = $current_financial_year = $data['financial_year'];
		
		}
		else
		{
			$data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;
		
		}
		
		

		if(!empty($urldata2['notifyrop']))
		{
			$data['notifyrop'] = $urldata2['notifyrop'];
		}


		$data = add_msg_if_any($this, $data);
		#print_r($data); exit();

		$data = handle_redirected_msgs($this, $data);

		$search_str = '';

		#GET WHAT LEVEL WE ARE ON 
		$level = !empty($data['level']) ?$data['level']  :'active';		 
		$data['level'] = $level;
		
	 
		$financial_searchstring = ''; 
		$jo = array();
		
		$financial_searchstring .= '  AND procurement_plans.isactive ="Y" ';

		
		
		#IF USER IS NOT ADMIN 
		if($this->session->userdata('isadmin') == 'N')
		{
				$userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
				$search_str = ' AND procurement_plans.pde_id="'. $userdata[0]['pde'] .'"';
				$financial_searchstring .= '  AND procurement_plans.pde_id = '.$userdata[0]['pde'].''; 
		}

		#Adding Current FInancial Year :
		$search_str .= ' AND procurement_plans.financial_year like "%'.$current_financial_year.'%" ';
		
			
		#Get Number of Bids ACTIVE
 
		#activecount
		#activecount
		$data['activecount']  = $this->db->query($this->Query_reader->get_query_by_code('numbids', array('orderby'=>'bid_dateadded DESC', 'limittext'=>' limit 1','searchstring'=>' bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"   AND procurement_plans.isactive="Y" AND  bidinvitations.id  NOT IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r  INNER JOIN bestevaluatedbidder BE ON r.receiptid =  BE.pid  WHERE r.beb ="Y" AND BE.isactive ="Y" )    '.$search_str )))->result_array();
		#archivecount
        $data['archivecount'] = $this->db->query($this->Query_reader->get_query_by_code( 'numbids', array('orderby'=>'bid_dateadded DESC', 'limittext'=>' limit 1',  'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"   AND procurement_plans.isactive="Y"  AND bidinvitations.id  IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder BE ON r.receiptid =  BE.pid  WHERE r.beb ="Y" AND BE.isactive ="Y" )   '.$search_str )))->result_array();
		#activecount
		$data['canceledcount']  = $this->db->query($this->Query_reader->get_query_by_code('numbids', array('orderby'=>'bid_dateadded DESC', 'limittext'=>' limit 1','searchstring'=>' bidinvitations.isactive = "C"  AND procurement_plan_entries.isactive = "Y"   AND procurement_plans.isactive="Y" AND  bidinvitations.id  NOT IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder BE ON r.receiptid =  BE.pid  WHERE r.beb ="Y" AND BE.isactive ="Y")    '.$search_str )))->result_array();
		
 		$data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();
		



 		# Switch Levels Active and Archived 
		switch ($level) {
			case 'active':
				# code...

				# Get the paginated list of bid invitations
				if(!empty($data['p']))
                {
                    $search_string =  $this->session->userdata('searchstring_bid'); 
                    
                    if(!empty($search_string)){
                    	$search_str = $search_string;
                    }
                }
                else
                {
                	$this->session->unset_userdata('searchstring_bid');              
                }
			    $data['page_list'] = paginate_list($this, $data, 'active_ifb_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y" AND procurement_plan_entries.isactive = "Y"  AND     bidinvitations.id    NOT IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder b ON r.receiptid = b.pid WHERE r.beb ="Y" )  '. $search_str),10);
   
			
				#$data['page_list'] = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"  AND( receipts.bid_id < 0 ) '. $search_str),10);

				break;
				
				
				case 'cacnceled':
				# code...

				# Get the paginated list of bid invitations
				if(!empty($data['p']))
                {
                    $search_string =  $this->session->userdata('searchstring_bid'); 
                    
                    if(!empty($search_string)){
                    	$search_str = $search_string;
                    }
                }
                else
                {
                	$this->session->unset_userdata('searchstring_bid');              
                }
			      $data['page_list'] = paginate_list($this, $data, 'active_ifb_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "C" AND procurement_plan_entries.isactive = "Y"  AND     bidinvitations.id    NOT IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder b ON r.receiptid = b.pid WHERE r.beb ="Y" )  '. $search_str),10);
   
			
				#$data['page_list'] = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"  AND( receipts.bid_id < 0 ) '. $search_str),10);

				break;
				
				


			case 'archive':


			#If Paginated 
			if(!empty($data['p']))
                {
                    $search_string =  $this->session->userdata('searchstring_bid'); 
                    
                    if(!empty($search_string)){
                    	$search_str = $search_string;
                    }
                }
                else
                {
                	$this->session->unset_userdata('searchstring_bid');              
                }

				#Get the paginated list of bid invitations
				$data['page_list'] = paginate_list($this, $data, 'active_ifb_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y" AND procurement_plan_entries.isactive = "Y"  AND     bidinvitations.id     IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder b ON r.receiptid = b.pid WHERE r.beb ="Y" )  '. $search_str),10);
   
				#$data['page_list'] = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"  AND( receipts.bid_id IS NOT NULL AND  receipts.beb = "Y" ) '. $search_str),10);


				#exit($this->db->last_query());

				break;

			default:
				
				break;
		}
		

		$data['page_title'] = 'Manage Bid Invitations';
		$data['current_menu'] = 'view_bid_invitations';
		$data['view_to_load'] = 'bids/manage_bid_invitations';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['search_url'] = 'bids/search_bid_invitations/level/'.$data['level'].'/financial_year/'.$current_financial_year;


		$this->load->view('dashboard_v', $data);

	}




	#DO action on a give ifb ajax functionality
	function ajax_ifbaction_action()
	{
		 #exit();
        check_user_access($this, 'view_bid_invitations', 'redirect');
		
		   # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);
		$formdata = $_POST;
		$databidid = mysql_real_escape_string($formdata['databidid']);
		
		 //Query : to Update Bid Invitation Back to Active 
		$query = $this->db->query("UPDATE bidinvitations SET  bidinvitations.isactive ='Y' WHERE id = '".$databidid."' ");
		
	     log_action('activate','IFB   Record has been Re-Activated  Successfully   ', 'IFB   Record has been Re-Activated  Successfully ');  
   
	
     	if($query)
			echo 1;
		 
		
	}
	



    # Searcg bid invitations
    function search_bid_invitations()
    {
        #exit();
        check_user_access($this, 'view_bid_invitations', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);




        $search_string = ' ';
        $level = !empty($data['level']) ?$data['level']  :'active';
        #print_r($level); exit();
        $data['level'] = $level;

        #print_r($_GET['search']['value']);
        $_POST['searchQuery'] = mysql_real_escape_string($_GET['search']['value']);


        if($this->session->userdata('isadmin') == 'N')
        {
            $userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $search_string .= ' AND procurement_plans.pde_id="'. $userdata[0]['pde'] .'"';
        }

        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $search_string .= ' AND procurement_plans.financial_year like "%'.$current_financial_year.'%" ';

        if($this->input->post('searchQuery'))
        {
            $_POST = clean_form_data($_POST);
            $_POST['searchQuery'] = trim($_POST['searchQuery']);

            $search_string .= ' AND (bidinvitations.procurement_ref_no like "%'. $_POST['searchQuery'] .
                '%" OR procurement_plan_entries.subject_of_procurement like "%' . $_POST['searchQuery'] . '%" '.
             //   'OR bidinvitations.bid_security_amount like "%'. $_POST['searchQuery'] .'%" '.
               ' OR  IF(bidinvitations.procurement_method_ifb > 0,PM2.title LIKE "%'.$_POST['searchQuery'] .'%",PM1.title LIKE "%'.$_POST['searchQuery'] .'%" )'.

                ' ) ';
        }
 


     	# Switch Levels Active and Archived 
		switch ($level) {
			case 'active':
				# code...

			 
			    $data['page_list'] = paginate_list($this, $data, 'active_ifb_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y" AND procurement_plan_entries.isactive = "Y"  AND     bidinvitations.id    NOT IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder b ON r.receiptid = b.pid WHERE r.beb ="Y" )  '. $search_string),10);
   
			
				#$data['page_list'] = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"  AND( receipts.bid_id < 0 ) '. $search_str),10);

				break;
				
				
				case 'cacnceled':
				# code...

			 
			      $data['page_list'] = paginate_list($this, $data, 'active_ifb_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "C" AND procurement_plan_entries.isactive = "Y"  AND     bidinvitations.id    NOT IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder b ON r.receiptid = b.pid WHERE r.beb ="Y" )  '. $search_string),10);
   
			
				#$data['page_list'] = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"  AND( receipts.bid_id < 0 ) '. $search_str),10);

				break;
				
				


			case 'archive':

		 
				#Get the paginated list of bid invitations
				$data['page_list'] = paginate_list($this, $data, 'active_ifb_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y" AND procurement_plan_entries.isactive = "Y"  AND     bidinvitations.id     IN ( SELECT DISTINCT(r.bid_id)  FROM receipts r INNER JOIN bestevaluatedbidder b ON r.receiptid = b.pid WHERE r.beb ="Y" )  '. $search_string),10);
   
				#$data['page_list'] = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND procurement_plan_entries.isactive = "Y"  AND( receipts.bid_id IS NOT NULL AND  receipts.beb = "Y" ) '. $search_str),10);


				#exit($this->db->last_query());

				break;

			default:
				
				break;
		}
	
        
		
        //Create a session for Search String
        $this->session->set_userdata('searchstring_bid', $search_string);

        $data['area'] = 'bid_invitations';

        $this->load->view('includes/add_ons', $data);


    }
    





    # View addenda
    function view_addenda()
    {
        check_user_access($this, 'view_bid_invitations', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

        $search_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $search_str = ' AND PP.pde_id="'. $userdata[0]['pde'] .'"';
        }

        if(!empty($data['b']))
        {
            $search_str .= ' AND A.bidid = "'. decryptValue($data['b']) .'" ';
        }

        #Get the paginated list of bid invitations
        $data = paginate_list($this, $data, 'search_addenda', array('orderby'=>'A.dateadded DESC', 'searchstring'=> $search_str));

        $data['page_title'] = 'Manage addenda';
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'bids/view_addenda';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'bids/search_addenda';

        $this->load->view('dashboard_v', $data);

    }
    




    #Function to load IFB addenda form
    function load_ifb_addenda_form()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #check user access
        #1: for editing
        if(!empty($data['i']))
        {
            check_user_access($this, 'edit_bid_invitation', 'redirect');
        }
        #2: for creating
        else
        {
            check_user_access($this, 'create_invitation_for_bids', 'redirect');
        }

        $app_select_str = ' procurement_plan_entries.isactive="Y" ';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'" ';
        }

        if(!empty($data['b']))
        {
            #the bid details
            $app_select_str .= ' AND bidinvitations.id ="'. decryptValue($data['b']) . '"';
            $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

            $data['bid_invitation_details'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. decryptValue($data['b'])  .'" AND isactive="Y"'));
        }

        #exit($this->db->last_query());

        #user is editing
        if(!empty($data['i']))
        {
            $addenda_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'addenda', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $addenda_id .'" AND isactive="Y"'));

            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.procurement_ref_no="'. $data['formdata']['procurement_ref_no'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }
        }

        $data['page_title'] = (!empty($data['i'])? 'Edit addenda' : 'Add IFB addenda');
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'bids/ifb_addenda_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }

    



    #Function to save an addenda
    function save_ifb_addenda()
    {

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #check user access
        #1: for editing
        if(!empty($data['i']))
        {
            check_user_access($this, 'edit_bid_invitation', 'redirect');
        }
        #2: for creating
        else
        {
            check_user_access($this, 'create_invitation_for_bids', 'redirect');
        }

        #IF POST 
        if(!empty($_POST['save_addenda']))
        {
            $required_fields = array('title', 'refno');

            $_POST = clean_form_data($_POST);

            $validation_results = validate_form('', $_POST, $required_fields);

             #Only proceed if the validation for required fields passes
            if($validation_results['bool'])
            {
                #check if a document with the specified reference number exists for this IFB
                $similar_ref_no = $this->db->query($this->Query_reader->get_query_by_code('search_table', array('table'=>'addenda', 'orderby'=>'bidid', 'limittext' =>'','searchstring' => ' bidid = "'.decryptValue($data['b']).'" AND refno ="'. $_POST['refno'].
                    '" AND isactive="Y"' . (!empty($data['i'])? ' AND id !="' . decryptValue($data['i']) . '"' : ''))))->result_array();


                if(!empty($similar_ref_no))
                {
                    $data['msg'] = "WARNING: An addenda for the selected IFB with a similar reference number exists";
                }
                else
                {
                    if(!empty($_FILES['addenda']['name']))
                    {
                        $this->session->set_userdata('local_allowed_extensions', array('.pdf'));
                        $extramsg = "";
                        $MAX_FILE_SIZE = 1000000;
                        $new_file_name = 'addenda_' . strtotime('now') . decryptValue($data['b']) . generate_random_letter();

                        $_POST['fileurl'] = (!empty($_FILES['addenda']['name'])) ? $this->sysfile->local_file_upload($_FILES['addenda'], $new_file_name , 'documents/addenda', 'filename') : '';

                    }

                    if(!empty($data['i']))
                    {
                        $_POST = array_merge($_POST, array('id'=>decryptValue($data['i'])));
                        $_POST['updatestr'] = '';

                        if(!empty($_FILES['addenda']['name']) && !empty($_POST['fileurl']))
                        {
                            $_POST['updatestr'] = ', fileurl = "'. $new_file_name .'" ';
                            $result = $this->db->query($this->Query_reader->get_query_by_code('update_addenda', $_POST));

                        }
                        elseif(!empty($_FILES['addenda']['name']) && empty($_POST['fileurl']))
                        {
                            $data['msg'] = 'ERROR: '.$this->sysfile->processing_errors;
                        }
                        else
                        {
                            $result = $this->db->query($this->Query_reader->get_query_by_code('update_addenda', $_POST));
                        }

                    }
                    else
                    {
                        $_POST['author'] = $this->session->userdata('userid');
                        $_POST['bidid'] = decryptValue($data['b']);

                        if(!empty($_POST['fileurl']))
                        {
                            $result = $this->db->query($this->Query_reader->get_query_by_code('create_addenda', $_POST));
                            $addenda_id = $this->db->insert_id();
                        }
                        elseif(empty($_FILES['addenda']['name']))
                        {
                            $data['msg'] = 'ERROR: Please select a file to upload';
                        }
                        else
                        {
                            $data['msg'] = 'ERROR: '.$this->sysfile->processing_errors;
                        }
                    }
                }

                #event has been added successfully
                if(!empty($result) && $result)
                {
                     
				    log_action('create','IFB Addenda  Record Created ', 'IFB Addended has been Created');
                    $data['msg'] = "SUCCESS: The addenda details have been saved.";
                    $this->session->set_userdata('sres', $data['msg']);


                    redirect('bids/view_addenda/m/sres' . ((!empty($data['b']))? "/b/".$data['b'] : ''));

                }
                else if(empty($data['msg']))
                {
                    $data['msg'] = "ERROR: The addenda details could not be saved or were not saved correctly.";
                }
            }

            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool']))
                && empty($data['msg']) )
            {
                $data['msg'] = "WARNING: The highlighted fields are required.";
                $data['requiredfields'] = $validation_results['requiredfields'];

            }


        }


        $data['formdata'] = $_POST;


        $app_select_str = ' procurement_plan_entries.isactive="Y" ';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'" ';
        }

         if(!empty($data['b']))
        {
            #the bid details
            $app_select_str .= ' AND bidinvitations.id ="'. decryptValue($data['b']) . '"';
            $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

            $data['bid_invitation_details'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. decryptValue($data['b'])  .'" AND isactive="Y"'));
        }


         #exit($this->db->last_query());

        #user is editing
        if(!empty($data['i']))
        {
            $addenda_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'addenda', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $addenda_id .'" AND isactive="Y"'));

            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.procurement_ref_no="'. $data['formdata']['procurement_ref_no'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }
        }

        $data['page_title'] = (!empty($data['i'])? 'Edit addenda' : 'Add IFB addenda');
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'bids/ifb_addenda_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data); 






    

    }

    




    #Function to delete an addenda
    function delete_addenda()
    {
        #check user access
        check_user_access($this, 'delete_invitation_for_bid', 'redirect');
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        if(!empty($data['i']) && !empty($data['b'])){
            $result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_item', array('item'=>'addenda', 'id'=>decryptValue($data['i'])) ));
        }


        if(!empty($result) && $result){
            $this->session->set_userdata('dbid', "The addenda details have been successfully deleted.");
        }

        else if(empty($data['msg']))
        {
            $this->session->set_userdata('dbid', "ERROR: The addenda details could not be deleted or were not deleted correctly.");
        }

	   log_action('delete','IFB Addenda  Record Deleted Successfully  ', 'IFB Addended has been Deleted  Successfully');
        redirect(base_url()."bids/view_addenda/m/dbid/b/".$data['b']);
    }
    





    #Function to load invitation for bid form
    function load_bid_invitation_form()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear.'-'.endyear;

        #check user access
        #1: for editing
        if(!empty($data['i']))
        {
            check_user_access($this, 'edit_bid_invitation', 'redirect');
        }
        #2: for creating
        else
        {
            check_user_access($this, 'create_invitation_for_bids', 'redirect');
        }

        $app_select_str = ' procurement_plan_entries.isactive="Y" ';
        #Is Person Admin?
        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            $data['current_pde'] =   $userdetails[0]['pde'];

        }
        #When Editing IFB Bid INvitation
        if(!empty($data['i']))
        {
            $app_select_str .= ' AND bidinvitations.id ="'. decryptValue($data['i']) .'" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' )))->result_array();
            $data['bid_info']=$this->bid_invitation_m->get_bid_invitation_info(decryptValue($data['i']));


        }
        #Directly Accessing New BId INvitation from Procurement Entry
        else if(!empty($data['v']))
        {
            $app_select_str .= ' AND procurement_plan_entries.id ="'. decryptValue($data['v']) .'" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' )))->result_array();

        }

        else
        {
            $app_select_str .=  ' AND  IF(
									  (
										(SELECT SUM(quantity) FROM bidinvitations A WHERE A.procurement_id = procurement_plan_entries.id AND A.isactive ="Y"  AND procurement_plan_entries.quantifiable ="Y"  )
											<
  										 procurement_plan_entries.quantity

									    ),
									  bidinvitations.id IS NOT NULL,
									  IF(procurement_plan_entries.quantifiable ="N", 1=1,  bidinvitations.id IS  NULL)


									  )  ';

           

        }


       
        $data['current_financial_year'] =  '';

        #When editing IFB
        if(!empty($data['i']))
        {
            $data['current_financial_year'] =  $current_financial_year;
            $bid_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $bid_id .'" AND isactive="Y"'));

            $data['current_financial_year']  = $data['procurement_plan_entries'][0]['financial_year'];

            if(!empty($data['formdata']['procurement_ref_no']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('ProcurementPlanDetails', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.procurement_ref_no="'. $data['formdata']['procurement_ref_no'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }
        }

        #Loading VIew from Procurement Entry under New Bid Invitation
        if(!empty($data['v']))
        {
            $data['current_financial_year'] =  $current_financial_year;
            $procurement_entry_id = decryptValue($data['v']);
            $data['current_financial_year']  = $data['procurement_plan_entries'][0]['financial_year'];

            $data['formdata']= 	$data['formdata']['procurement_details'] = $data['procurement_plan_entries'];
            $data['formdata']['procurement_id'] = decryptValue($data['v']);

        }


        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();

        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$userdetails[0]['pde'];
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();

        # PDE fetch branches
        $this->load->model('braches_m','branches_m');
        $searchstring = '';
        if($this->session->userdata('isadmin') == 'N')
        {
            $pde = $this->session->userdata('pdeid');
            $searchstring = ' AND U.pde = '.$pde.' ' ;
        }

        $data['branches'] =  $this->db->query($this->Query_reader->get_query_by_code('fetch_branches', array('searchstring'=>$searchstring,'limittext'=>'','orderby'=>'')))->result_array();

        #fetch pdes
        $data['pdes'] = $this->db->query("SELECT * FROM pdes WHERE isactive='Y' ")-> result_array();


        #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i'])? 'Edit Bid Invitation Details' : 'Add Bid Invitation Details');
        $data['current_menu'] = 'create_invitation_for_bids';
        $data['view_to_load'] = 'bids/bid_invitation_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }
    



     #Validate IFB Date 
    function validate_ifb_date(){


    	#############################################
    	#  FETCH ALL PUBLIC HOLIDAY DATES 
    	#############################################
    	$sql = "SELECT holiday_date  FROM public_holidays   WHERE  `isactive` =  'Y'  ORDER BY  holiday_date DESC ";
    	$query = $this->db->query($sql)->result_array();

    	 
		$public_holiday = array();
    	$dates  = "";

       foreach ($query as $key => $row) {  

    		array_push($public_holiday, $row['holiday_date'] );
    	 
    	}


       $dates = rtrim($dates, ",");
	   $skip_days = array("Saturday","Sunday");

        $procurement_method = !empty($_POST['ifb_proc_method']) ?  $_POST['ifb_proc_method'] : $_POST['entry_proc_method'] ; 

		 $days = 1;
        if($procurement_method == 1)
        	$days = 30;
         elseif($procurement_method == 2)
        	$days = 20;
         elseif($procurement_method == 4)
        	$days = 12;
         elseif($procurement_method == 3)
        	$days = 20;
         elseif($procurement_method == 7)
        	$days = 5;
         elseif($procurement_method == 8)
        	{
        		$_POST['invitation_to_bid_date'] = "";
        	}
         elseif($procurement_method == 11)
        	$days = 15;
         elseif($procurement_method == 12)
        	$days = 15;

         else
        	$days = 10;




		 //Reduce it by one day since counting starts on the same day 
		 $days = $days  -1 ;



	$_POST['invitation_to_bid_date'] = custom_date_format('Y-m-d',$_POST['invitation_to_bid_date']);	
     
		 if(!empty($_POST['invitation_to_bid_date']))
		 {


	   	$invitation_to_bid_date = $_POST['invitation_to_bid_date'];
		 

	         $last_date  =  addDays($invitation_to_bid_date, $days, $skipdays = array("Saturday", "Sunday"), $public_holiday) ;   
		     echo date('d/m/Y',$last_date);
		 }
		 else
		 {
		 	 echo "";
		 }


	 

    }
    





     #Validate BEB  Date 
    function validate_beb_date(){


    	#############################################
    	#  FETCH ALL PUBLIC HOLIDAY DATES 
    	#############################################
    	$sql = "SELECT holiday_date  FROM public_holidays   WHERE  `isactive` =  'Y'  ORDER BY  holiday_date DESC ";
    	$query = $this->db->query($sql)->result_array();

    	 
		$public_holiday = array();
    	$dates  = "";

       foreach ($query as $key => $row) {  

    		array_push($public_holiday, $row['holiday_date'] );
    	 
    	}


       $dates = rtrim($dates, ",");
	   $skip_days = array("Saturday","Sunday");
        
	   $days = 9;		 
  

     $_POST['date_of_display'] = custom_date_format('Y-m-d',$_POST['date_of_display']);	

	 
	 if(!empty($_POST['date_of_display']))
	 {
	 	
	 	$date_of_display = $_POST['date_of_display'];
        $last_date  =  addDays($date_of_display, $days, $skipdays = array("Saturday", "Sunday"), $public_holiday) ;   
	   echo date('d/m/Y',$last_date);
	 }
	 else
	 {
	 	 echo "";
	 }
 

    }
    






    #function to validate IFB form data
    function validate_ifb_form($formdata,$proc_method = '')
    {

    	
        
        $msg = '';
        $error_fields = array();
 		$error_fields  = array();
        $result = 1;


         if(!empty($formdata['dateofconfirmationbyao'])  && !empty($formdata['date_initiated'])   )
        {




            if(strtotime($formdata['dateofconfirmationbyao']) > strtotime($formdata['date_initiated']))
            {
            	$result = 0;
                $msg = ' CC Approval Date  Should BE Greater Than AO Approval Date ';
                $error_fields = array('dateofconfirmationbyao', 'date_initiated');
            }
            


        }



         if(!empty($formdata['invitation_to_bid_date'])  && !empty($formdata['date_initiated'])   )
        {
 

            if(strtotime($formdata['invitation_to_bid_date']) < strtotime($formdata['date_initiated']))
            {
            	 $result = 0;
                 $msg = ' Invitation to BId  Should BE Greater Than AO Approval Date ';
                 $error_fields = array('invitation_to_bid_date', 'date_initiated');
            }


        }

           



      if(!empty($formdata['pre_bid_meeting_date']))
        {


            if(strtotime($formdata['pre_bid_meeting_date']) < strtotime($formdata['invitation_to_bid_date']))
            {
            	 $result = 0;
                $msg = 'Pre  bid/EOI meeting date should be later than invitation to bid date';
                $error_fields = array('pre_bid_meeting_date', 'invitation_to_bid_date');
            }




            elseif(strtotime($formdata['bid_submission_deadline']) < strtotime($formdata['pre_bid_meeting_date']))
            {
            	 $result = 0;
                $msg = '  bid/EOI  submission deadline should be later than pre-bid meeting date';
                $error_fields = array('pre_bid_meeting_date', 'bid_submission_deadline');
            }
            
        }


        if(!empty($formdata['invitation_to_bid_date']) && !empty($formdata['date_initiated']))
        {
	        #Invitation to bid date
	        if(strtotime($formdata['invitation_to_bid_date']) < strtotime($formdata['date_initiated']))
	        {
	        	 $result = 0;
		            $msg = 'Invitation to  bid/EOI date should be later than  CC Approval Date ';
		            $error_fields = array('date_initiated', 'invitation_to_bid_date');
	        }
       }


  		if(!empty($formdata['invitation_to_bid_date']) && !empty($formdata['bid_submission_deadline']))
        {

				if(!empty($formdata['invitation_to_bid_date']) && !empty($formdata['bid_submission_deadline']))
				{

				 			#Invitation to bid date Vs Bid Submission Deadline
						        if(strtotime($formdata['invitation_to_bid_date']) >  strtotime($formdata['bid_submission_deadline']))
						        {
						        	 $result = 0;
						            $msg = 'Invitation to bid/EOI date should be later than  Bid/EOI Submition Deadline ';
						            $error_fields = array('invitation_to_bid_date', 'bid_submission_deadline');
						        }
						      
			    }
		}



	if(!empty($formdata['bid_openning_date']) && !empty($formdata['bid_submission_deadline']))
        {

		        #bid openning date
		        if(strtotime($formdata['bid_openning_date']) < strtotime($formdata['bid_submission_deadline']))
		        {
		        	 $result = 0;
		            $msg = '  bid/EOI  openning date should be later than bid submission deadline';
		            $error_fields = array('bid_openning_date', 'bid_submission_deadline');
	 	        }
       }

       if(!empty($formdata['bid_evaluation_from']) && !empty($formdata['bid_openning_date']))
        {
		        #bid evaluation start date
		        if(strtotime($formdata['bid_evaluation_from']) < strtotime($formdata['bid_openning_date']))
		        {
		        	 $result = 0;
		            $msg = '  Bid/EOI  evaluation start date should be later than bid openning date';
		            $error_fields = array('bid_openning_date', 'bid_evaluation_from');
		        }
       }


       if(!empty($formdata['bid_evaluation_to']) && !empty($formdata['bid_evaluation_from']))
        {

	        #bid evaluation end date
	       if(strtotime($formdata['bid_evaluation_to']) < strtotime($formdata['bid_evaluation_from']))
	        {
	        	 $result = 0;
	            $msg = '  bid/EOI  evaluation end date should be later than bid evaluation start date';
	            $error_fields = array('bid_evaluation_to', 'bid_evaluation_from');
	        }
   		 }

   		
   		if(!empty($formdata['display_of_beb_notice']) && !empty($formdata['bid_evaluation_to']))
        {


		        #display of BEB notice date
		     
			if(!empty($proc_method) && (($proc_method != 8) || ($proc_method != 'Direct Procurement') ) )
			{
		        #display of BEB notice date
		       if( strtotime($formdata['display_of_beb_notice']) < strtotime($formdata['bid_evaluation_to'])) 
		        {
		        	 $result = 0;
		            $msg = 'BEB notice display date should be later than bid evaluation close date';
		            $error_fields = array('bid_evaluation_to', 'display_of_beb_notice');
		        }
		    }

       }


	if(!empty($formdata['display_of_beb_notice']) && !empty($formdata['contract_award_date']))
	{
        #contract award date
       if( strtotime($formdata['display_of_beb_notice']) > strtotime($formdata['contract_award_date'])) 
        {
        	 $result = 0;
            $msg = 'BEB notice display date must not be  later than contract award date';
            $error_fields = array('contract_award_date', 'display_of_beb_notice');
        }
    }



    if(!empty($formdata['contract_award_date']) && !empty($formdata['contract_signature_date']))
	{
        #contract award date
       if( strtotime($formdata['contract_award_date']) > strtotime($formdata['contract_signature_date'])) 
        {
        	 $result = 0;
            $msg = 'Contract Award Date  must not be  later than contract Signature Date ';
            $error_fields = array('contract_award_date', 'contract_signature_date');
        }
    }

        
            

		$formdata = array('result'=>$result, 'msg'=>$msg, 'error_fields'=>$error_fields);
        #print_r($formdata);
    	#exit("testing ");
 


        return array('result'=>$result, 'msg'=>$msg, 'error_fields'=>$error_fields);

    } 

   



    #save bid invitation
    function save_bid_invitation()
    {
      
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);


 

 
        #check user access
        #1: for editing
        if(!empty($data['i']))
        {
            check_user_access($this, 'edit_bid_invitation', 'redirect');
        }
        #2: for creating
        else
        {
            check_user_access($this, 'create_invitation_for_bids', 'redirect');
        }


 

        if(!empty($_POST['save']) || !empty($_POST['approve']) || !empty($_POST['savealone']))
        {






            
           # IF IFB METHOD IS SET. IT WILL BE THE DEFAULT CHEKCING POINT OTHERWIZE WE'LL USE THE ONE AT ENTRY LEVEL
             

            $pm_method = !empty($_POST['procurement_method_ifb'] ) ? $_POST['procurement_method_ifb'] : $_POST['procurement_details']['procurement_method'];

            if(trim($pm_method) == 'Micro Procurement'  || $pm_method == 'Micro Procurement' || $pm_method == '10'  || ( trim($pm_method) == 'Direct Procurement' || $pm_method == 'Direct Procurement' || $pm_method  == '8')  )
            {
                $required_fields = array('estimated_amount','estimated_amount_currency','dateofconfirmationbyao');
            }
            else
            {
                #Validating IFBs
                // contract_award_date
                if((!empty($_POST['procurement_details_quantity'])) && (!empty($_POST['quantityifb'])) )
                    $required_fields = array('dateofconfirmationbyao','date_initiated','procurement_id', 'invitation_to_bid_date','bid_submission_deadline', 'display_of_beb_notice', 'bid_receipt_address', 'documents_inspection_address', 'documents_address_issue', 'bid_openning_address', 'bid_openning_date', 'bid_evaluation_from', 'bid_evaluation_to', 'display_of_beb_notice',  'initiated_by',  'dateofconfirmationbyao','sequencenumber','procurement_details_quantity','quantityifb','estimated_amount','estimated_amount_currency','contract_award_date','contract_signature_date');
                else if(!empty($_POST['procurement_details_quantity']))
                    $required_fields = array('dateofconfirmationbyao','date_initiated','procurement_id', 'invitation_to_bid_date','bid_submission_deadline', 'display_of_beb_notice', 'bid_receipt_address', 'documents_inspection_address', 'documents_address_issue', 'bid_openning_address', 'bid_openning_date', 'bid_evaluation_from', 'bid_evaluation_to', 'display_of_beb_notice',  'initiated_by',  'dateofconfirmationbyao','sequencenumber','quantityifb','estimated_amount','estimated_amount_currency','contract_award_date','contract_signature_date');
                else if(!empty($_POST['procurement_details_quantity']))
                    $required_fields = array('dateofconfirmationbyao','date_initiated','procurement_id', 'invitation_to_bid_date','bid_submission_deadline', 'display_of_beb_notice', 'bid_receipt_address', 'documents_inspection_address', 'documents_address_issue', 'bid_openning_address', 'bid_openning_date', 'bid_evaluation_from', 'bid_evaluation_to', 'display_of_beb_notice',  'initiated_by',  'dateofconfirmationbyao','sequencenumber','procurement_details_quantity','estimated_amount','estimated_amount_currency','contract_award_date','contract_signature_date');
                else
                    $required_fields = array('dateofconfirmationbyao','date_initiated','procurement_id', 'invitation_to_bid_date','bid_submission_deadline', 'display_of_beb_notice', 'bid_receipt_address', 'documents_inspection_address', 'documents_address_issue', 'bid_openning_address', 'bid_openning_date', 'bid_evaluation_from', 'bid_evaluation_to', 'display_of_beb_notice',  'initiated_by',  'dateofconfirmationbyao','sequencenumber','estimated_amount','estimated_amount_currency','contract_award_date','contract_signature_date');

            }


           #if has vailidity is a mandatory 
			if(!empty($_POST['hasbidvalididy']))
			{
				array_push($required_fields, 'bidvalidtity');
				$_POST['bidvalidtity'] = custom_date_format('Y-m-d',$_POST['bidvalidtity']);

			}


               $_POST['date_initiated'] = custom_date_format('Y-m-d',$_POST['date_initiated']);
 
              $_POST['display_of_beb_notice'] = custom_date_format('Y-m-d',$_POST['display_of_beb_notice']);

              $_POST['contract_award_date'] = custom_date_format('Y-m-d',$_POST['contract_award_date']);

              $_POST['contract_signature_date'] = custom_date_format('Y-m-d',$_POST['contract_signature_date']);


              $_POST['bid_evaluation_to'] = custom_date_format('Y-m-d',$_POST['bid_evaluation_to']);

            
              $_POST['bid_evaluation_from'] = custom_date_format('Y-m-d',$_POST['bid_evaluation_from']);

  			  $_POST['bidvalidtity'] = custom_date_format('Y-m-d',$_POST['bidvalidtity']);


            





            $custom_reference = 'N';
            if(!empty($_POST['custom_reference']) && ($_POST['custom_reference'] == 'Y' || $_POST['custom_reference'] == 'on'))
            {
                $custom_reference = 'Y';
            }

            $ifb_method_justification_status = '';
            if((!empty($_POST['ifb_method_justification_status'] )&& ($_POST['ifb_method_justification_status'] == 'Y') && (!empty($_POST['procurement_method_ifb'])) ))
            {
                array_push($required_fields, 'ifb_method_justification');
                $ifb_method_justification = mysql_real_escape_string($_POST['ifb_method_justification']);
            }
            else
            {
                $_POST['ifb_method_justification'] = '';
            }
            $_POST['ifb_method_justification'] = trim($_POST['ifb_method_justification']);
            
            //Source of Funding 
            $_PoST['source_of_funding'] = trim($_POST['source_of_funding']);



            if(trim($pm_method) == 'Expression Of Interest'  || $pm_method == 'Expression Of Interest' || $pm_method == '11'    )
            {
                array_push($required_fields, 'expression_of_interest');
            }

            #print_r($_POST); exit();
            $_POST = clean_form_data($_POST);

          

            $data['procurement_details_quantity'] = !empty($_POST['procurement_details_quantity']) ?$_POST['procurement_details_quantity'] :0;

            # validation for procurement format 

            #exit();
           #  end 

            //$data['sequence_n'] = $_POST['sequencenumber'];
            if($custom_reference == 'Y')
            {
                $_POST['procurement_ref_no'] = $_POST['procurement_ref_no'];
                $data['sequencenumber'] =  $_POST['sequencenumber'];

            }
            else
            {
                $_POST['procurement_ref_no'] = $_POST['sequencenumber'].$_POST['procurement_ref_no'];
            }


 



           // Records :: 


                    #format time dependent dates
                    #1. bid_submission_deadline_time
                      $_POST['bid_submission_deadline'] = !empty( $_POST['bid_submission_deadline']) ?  custom_date_format('Y-m-d',$_POST['bid_submission_deadline']) : '';





                    if(!empty($_POST['bid_submission_deadline_time']))
                    {
                    	 ;
                        $_POST['bid_submission_deadline'] = custom_date_format('Y-m-d',$_POST['bid_submission_deadline']).
                            ' ' .custom_date_format("H:i", $_POST['bid_submission_deadline_time']) . ':00';
                    }

                    #2. bid_submission_deadline_time
                    $_POST['bid_openning_date'] = !empty( $_POST['bid_openning_date']) ?  custom_date_format('Y-m-d',$_POST['bid_openning_date']) : '';

                    if(!empty($_POST['bid_openning_date_time']))
                    {
                    	 
                        $_POST['bid_openning_date'] = custom_date_format('Y-m-d',$_POST['bid_openning_date']).
                            ' ' .custom_date_format("H:i", $_POST['bid_openning_date_time']) . ':00';
                    }

                    #3. pre_bid_meeting_date_time
                     $_POST['pre_bid_meeting_date'] = !empty( $_POST['pre_bid_meeting_date']) ?  custom_date_format('Y-m-d',$_POST['pre_bid_meeting_date']) : '';

                    if(!empty($_POST['pre_bid_meeting_date_time']))
                        $_POST['pre_bid_meeting_date'] =custom_date_format('Y-m-d', $_POST['pre_bid_meeting_date']).
                            ' ' .custom_date_format("H:i", $_POST['pre_bid_meeting_date_time']) . ':00';


                    #4, bid validity finding::
                    if(!empty($_POST['hasbidvalididy']))
                    {
                        $_POST['validity']  =removeCommas($_POST['hasbidvalididy']);
                        $_POST['validityperiod']  = custom_date_format('Y-m-d',$_POST['bidvalidtity']);
                    }


                    #5, Procurment Reference Number Display Number Details
                    if(!empty($_POST['subject_details']))
                    {
                        $_POST['subject_details']  =mysql_real_escape_string($_POST['subject_details']);

                    }

                    #   print_r($_POST['validityperiod']);exit();
                    #date_initiated
                    $_POST['date_initiated'] = !empty( $_POST['date_initiated']) ?  custom_date_format('Y-m-d',$_POST['date_initiated']) : '';



                      #invitation_to_bid_date
                    $_POST['invitation_to_bid_date'] = !empty( $_POST['invitation_to_bid_date']) ?  custom_date_format('Y-m-d',$_POST['invitation_to_bid_date']) : '';


  					 #bid_evaluation_from
                    $_POST['bid_evaluation_from'] = !empty( $_POST['bid_evaluation_from']) ?  custom_date_format('Y-m-d',$_POST['bid_evaluation_from']) : '';


                    #bid_evaluation_to
                    $_POST['bid_evaluation_to'] = !empty( $_POST['bid_evaluation_to']) ?  custom_date_format('Y-m-d',$_POST['bid_evaluation_to']) : '';


  					#display_of_beb_notice
                    $_POST['display_of_beb_notice'] = !empty( $_POST['display_of_beb_notice']) ?  custom_date_format('Y-m-d',$_POST['display_of_beb_notice']) : '';


                    #contract_award_date
                    $_POST['contract_award_date'] = !empty( $_POST['contract_award_date']) ? custom_date_format('Y-m-d',$_POST['contract_award_date']) : '';

                       #contract_signature_date
                    $_POST['contract_signature_date'] = !empty( $_POST['contract_signature_date']) ?  custom_date_format('Y-m-d',$_POST['contract_signature_date']) : '';




 
                    



                    #print_r($_POST);
                    $_POST['bid_documents_price'] = removeCommas($_POST['bid_documents_price']);
                    $_POST['bid_documents_price_exchange_rate'] = !empty($_POST['bid_documents_price_exchange_rate']) ? removeCommas($_POST['bid_documents_price_exchange_rate']) : '';
					$_POST['bid_documents_currency'] = mysql_real_escape_string($_POST['bid_documents_currency']);


                    
                    $_POST['bid_security_amount'] = removeCommas($_POST['bid_security_amount']);
                    $_POST['bid_security_amount_exchange_rate'] = !empty($_POST['bid_security_amount_exchange_rate']) ? removeCommas($_POST['bid_security_amount_exchange_rate']) : '';


                    $_POST['dateofconfirmationbyao'] = !empty($_POST['dateofconfirmationbyao']) ?  custom_date_format('Y-m-d',$_POST['dateofconfirmationbyao']) :'';
                    #$_POST['procurement_ref_no'] = $_POST['sequencenumber'].$_POST['procurement_ref_no'];
                    $_POST['bid_security_currency'] = mysql_real_escape_string($_POST['bid_security_currency']);

                    #IFB QUANTITY
                    $quantity_ifb = removeCommas(mysql_real_escape_string($_POST['quantityifb']));

                    #has lots
                    $haslots = 'N';
                    
                    if(!empty($_POST['haslots']) )
                    {
                        $haslots = 'Y';
                        $_POST['haslots'] =  $haslots;

                    }
                    else
                    {
                    	$_POST['haslots']  ='';
                    }
                    

                    

                    $query = $this->db->query("SELECT * FROM procurement_plan_entries WHERE id='".$_POST['procurement_id']."' ") -> result_array();
                    if(!empty($query))
                    {
                        #procurement_details_quantity
                        #print_r($query);
                        $entryquantity = $query[0]['quantity'];
                        $total_ifb_quantity = $query[0]['total_ifb_quantity'];
                        $totall = removeCommas($_POST['quantityifb']) + $total_ifb_quantity;

                    }

                    #Additional Notes
                    $_POST['additional_notes'] = trim($_POST['additional_notes']);


                    #Estimated Amount
                    $estimated_amount = $_POST['estimated_amount'];

                    if(!empty($estimated_amount))
                    {
                        $_POST['estimated_amount'] = removeCommas($_POST['estimated_amount']);
                    }
                    else
                    {
                        $_POST['estimated_amount']  = '';
                    }

                    $_POST['estimated_amount_currency'] = mysql_real_escape_string($_POST['estimated_amount_currency']);


					//Exchange Rate  estimated_amount_exchange_rate
					 $_POST['estimated_amount_exchange_rate'] = !empty($_POST['estimated_amount_exchange_rate']) ? removeCommas($_POST['estimated_amount_exchange_rate']) : 1;





          $validation_results = validate_form('', $_POST, $required_fields);

            if( $pm_method  == 'Micro Procurement' || $pm_method  == '10'   )
            {

                //  ( trim($_POST['procurement_details']['procurement_method']) == 'Direct Procurement' || $_POST['procurement_method_ifb'] == 'Direct Procurement' || $_POST['procurement_method_ifb'] == '8')
                $error_fields  = array();
                $result = 1;
                $msg = '';
                $bid_validation_results = array('result'=>$result, 'msg'=>$msg, 'error_fields'=>$error_fields);
            }
            else{

                $bid_validation_results = $this->validate_ifb_form($_POST,$pm_method);
            }
 
 
            $data['formdata'] = $_POST;


            #Only proceed if the validation for required fields passes
            if($validation_results['bool'] && $bid_validation_results['result'])
            {
                #check if an active bid invitation already exists for selected procurement ref no
                $similar_bid_invitation = $this->db->query($this->Query_reader->get_query_by_code('search_bidinvitation', array( 'orderby'=>'A.procurement_ref_no', 'limittext' =>'','searchstring' => ' AND A.procurement_ref_no LIKE "%'.mysql_real_escape_string($_POST['procurement_ref_no']).'%" AND  A.procurement_id = "'.$_POST['procurement_id'].'"  AND A.isactive="Y"' . (!empty($data['i'])? ' AND A.id !="' . decryptValue($data['i']) . '"' : ''))))->result_array();
                //if($current_financial_year == '2014-2015')
                #exit($this->db->last_query());


                if(!empty($similar_bid_invitation))
                {
                    $data['msg'] = "WARNING: A bid invitation for the selected procurement reference number already exists.";
                }
                else
                {

					
           

                    //exit();
                    if(!empty($data['i']))
                    {
                        #remove the initially added IFB quantity then add the new IFB quantity :
                        $totall = $_POST['quantityifb'] + ($total_ifb_quantity - $_POST['ifb_quantity']);


                        #$total_ifb_quantity = $_POST['procurement_details_quantity'];
                        #$totall = $_POST['quantityifb'] + $total_ifb_quantity;

                        #exit('reached');
                        $_POST = array_merge($_POST, array('invitation_id'=>decryptValue($data['i'])));

                        $result = $this->db->query($this->Query_reader->get_query_by_code('update_bid_invitation', $_POST));
                       # exit($this->db->last_query());
                        $bid_invitation_id =  decryptValue($data['i']);
                        $query = mysql_query("UPDATE   procurement_plan_entries SET total_ifb_quantity ='".$totall."' WHERE  	id='".$_POST['procurement_id']."' limit 1 ") or die("".mysql_error()) ;

					
						 log_action('update',' IFB Updated Successfully   ', '  A Bid Invitation '.$_POST['procurement_ref_no'].' Has Been Updated Successfully ' );  

 
                    }
                    else
                    {
                        $_POST['author'] = $this->session->userdata('userid');
                        //$bidinviatns = $this->Query_reader->get_query_by_code('add_bid_invitation', $_POST);
                        $result = $this->db->query($this->Query_reader->get_query_by_code('add_bid_invitation', $_POST));
                        
                        #exit($this->db->last_query());

                        $bid_invitation_id = $this->db->insert_id();
                        $query = mysql_query("UPDATE   procurement_plan_entries SET total_ifb_quantity ='".$totall."' WHERE  	id='".$_POST['procurement_id']."' limit 1 ") or die("".mysql_error()) ;
						

						 log_action('create',' IFB Created  Successfully   ', '  A Bid Invitation '.$_POST['procurement_ref_no'].' Has Been Created  Successfully ' );  
  
  
                    }
                }

                #event has been added successfully
                if(!empty($result) && $result)
                {

                    $ifb_quantity_details = array(
                        'bid_id' => $bid_invitation_id,
                        'procurement_id' => mysql_real_escape_string($_POST['procurement_id']),
                        'ifb_quantity' => $quantity_ifb );


                    $bidinviatns_quantitties = $this->db->query($this->Query_reader->get_query_by_code('inload_bid_invitation_form(sert_update_ifb_quantity', $ifb_quantity_details));




                    $data['msg'] = "SUCCESS: The bid invitation details have been saved.";
                    $this->session->set_userdata('sres', $data['msg']);

                    #user clicked publish
                    if(!empty($_POST['approve']))
                    {
                        if($haslots == 'Y'  && (empty($_POST['savealone'])))
                        {
                            redirect('bids/add_lots/m/sres/i/' . encryptValue($bid_invitation_id). ((!empty($data['v']))? "/v/".$data['v'] : ''));
                        }
                        else{

                            redirect('bids/approve_bid_invitation/m/sres/i/' . encryptValue($bid_invitation_id). ((!empty($data['v']))? "/v/".$data['v'] : ''));
                        }
                    }
                    else
                    {
                        if(!empty($bid_invitation_id))
                        {
                            #all I need is credentials
                            $data['notifyrop'] =  $bid_invitation_id;
                        }
                        if($haslots == 'Y'  && (empty($_POST['savealone'])))
                        {
                            redirect('bids/add_lots/m/sres/i/' . encryptValue($bid_invitation_id). ((!empty($data['v']))? "/v/".$data['v'] : ''));
                        }
                        else
                        {
                            redirect('bids/manage_bid_invitations/m/sres' . ((!empty($data['v']))? "/v/".$data['v'] : ''));
                        }
                    }

                }
                else if(empty($data['msg']))
                {
                    $data['msg'] = "ERROR: The bid invitation details could not be saved or were not saved correctly.";
 
                }
            }


            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool']))
                && empty($data['msg']) )
            {
                $data['msg'] = "WARNING: The highlighted fields are required.";
                $data['requiredfields'] = $validation_results['requiredfields'];

            }
            elseif(!$bid_validation_results['result'] && empty($data['msg']))
            {
                $data['msg'] = "WARNING: " . $bid_validation_results['msg'];
                $data['requiredfields'] = $bid_validation_results['error_fields'];
            }

        }

        $financial_searchstring = '';



       

   

        if(isset($_POST['haslots']) && !empty($_POST['haslots']))
        {
            $data['formdata']['haslots'] = 'Y';
        }



        #$data['formdata'] = $_POST;
        #print_array($_POST); exit();
        $app_select_str = ' procurement_plan_entries.isactive="Y" ';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
        }


        #When Editing IFB Bid INvitation
        if(!empty($data['i']))
        {
            $app_select_str .= ' AND bidinvitations.id ="'. decryptValue($data['i']) .'" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' )))->result_array();

        }
        #Directly Accessing New BId INvitation from Procurement Entry
        else if(!empty($data['v']) )
        {

#procurement_id
        	$data['v'] = encryptValue($data['formdata']['procurement_id']);

            $app_select_str .= ' AND procurement_plan_entries.id ="'. decryptValue($data['v']) .'" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' )))->result_array();

        }

        else
        {

        	 $procurement_id = $data['formdata']['procurement_id'];

        	  $app_select_str .= ' AND procurement_plan_entries.id ="'.  $procurement_id.'" ';


              $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' )))->result_array();


        }


        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();
        #fetch pdes
        $data['pdes'] = $this->db->query("SELECT * FROM pdes WHERE isactive='Y' ")-> result_array();

        $data['current_financial_year']  = !empty($data['procurement_plan_entries'][0]['financial_year'])  ? $data['procurement_plan_entries'][0]['financial_year'] : '' ;
        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();

        $data['page_title'] = (!empty($data['i'])? 'Edit bid invitation' : 'Create bid invitation');
        $data['current_menu'] = 'create_invitation_for_bids';
        $data['view_to_load'] = 'bids/bid_invitation_form';
        $data['view_data']['form_title'] = $data['page_title'];
        //print_r($data);

        $this->load->view('dashboard_v', $data);

    }

    



    #Fetch Procurment Plan Entries
    function fetch_procurement_plan_entries(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);


        $app_select_str = '1 = 1';
        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
        }

        $app_select_str .=  ' AND  IF(
							   (SELECT SUM(quantity) FROM bidinvitations A WHERE A.procurement_id = procurement_plan_entries.id AND A.isactive ="Y"  AND procurement_plan_entries.quantifiable ="Y"  ) < procurement_plan_entries.quantity
							   ,1=1
							  ,

							   IF(procurement_plan_entries.quantifiable ="N",
								1=1,
							   procurement_plan_entries.id NOT IN (SELECT A.procurement_id FROM bidinvitations A WHERE A.procurement_id = procurement_plan_entries.id AND A.isactive ="Y")
								)
							   )  ';
        $app_select_str .=  ' AND procurement_plans.financial_year like "%'.$_POST['financial_year'].'%" AND procurement_plans.ISACTIVE = "Y" ';
        //LIMIT 20
        $QUERY = $this->Query_reader->get_query_by_code('ProcurementPlanEntriesList', array('searchstring'=>$app_select_str, 'limittext'=>'  ', 'orderby'=>' procurement_plan_entries.dateadded  DESC' ));


        #print_r($QUERY);
        #exit();
        $data['procurement_plan_entries'] = $this->db->query($QUERY)->result_array();

        $options = get_select_options($data['procurement_plan_entries'], 'procurement_id', 'subject_of_procurement', (!empty($formdata['procurement_id'])? $formdata['procurement_id'] : '' ));
        print_r( $options);

    }
    
    




    function fetch_procurement_plan_entries_beb(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);


        $userid = $this->session->userdata['userid'];

        $data['financial_year'] =  (!empty($_POST['financial_year'])) ? $_POST['financial_year'] : '';
        
        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

		// Active List from BEB 
      $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('active_procurement_list',  array('SEARCHSTRING' => 'AND procurement_plan_entries.isactive ="Y" AND bidinvitations.isactive ="Y" AND procurement_plans.isactive="Y"   AND  procurement_plans.financial_year LIKE "'.$current_financial_year.'" AND  IF(bidinvitations.procurement_method_ifb > 0,  if(bidinvitations.procurement_method_ifb IN("1","2","9","11"),bidinvitations.isapproved="Y",1=1),  IF(procurement_plan_entries.procurement_method   IN("1","2","9","11"),bidinvitations.isapproved="Y" ,1=1))
	 AND users.userid = '.$userid.'   AND bidinvitations.id not in (SELECT DISTINCT receipts.bid_id FROM   receipts     INNER JOIN bestevaluatedbidder b ON receipts.receiptid = b.pid   where receipts.beb="Y" AND b.isactive="Y"  )  '    ,'limittext'=>''   )))->result_array();
        #exit($this->db->last_query());
        $options = get_select_options($data['procurement_plan_entries'], 'id', 'procurement_ref_no', (!empty($formdata['procurement_id'])? $formdata['procurement_id'] : '' ));
        print_r( $options);

    }





    #add Lots
    function add_lots()
    {
        check_user_access($this, 'add_lots', 'redirect');

        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);


        if(!empty($data['i']))
        {
            $data['bid_id'] = decryptValue($data['i']);
        }
        if(!empty($data['lotid']))
        {
            $data['lotid'] = decryptValue($data['lotid']);
            #print_r($data['lotid']); exit();
            $data['formdata'] = $this->db->query("SELECT * FROM lots where id =".$data['lotid']."")->result_array();
        }
        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();


        $data['page_title'] =  'Add Lots';
        $data['current_menu'] = 'Add Lots';
        $data['view_to_load'] = 'bids/add_lots_form';
        $data['view_data']['form_title'] = $data['page_title'];
        //print_r($data);

        $this->load->view('dashboard_v', $data);



    }




    #save_lots
    function save_lots()
    {

        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        #print_r($_POST);
        $userid= $this->session->userdata('userid');
        $lotnumber = mysql_real_escape_string($_POST['lotnumber']);
        $lottitle = mysql_real_escape_string($_POST['lottitle']);
        $lotdetails = mysql_real_escape_string($_POST['lotdetails']);
        $bidid = mysql_real_escape_string(decryptValue($_POST['bidid']));
        $lotid = mysql_real_escape_string($_POST['lotid']);

        $bid_security_amount = removeCommas(mysql_real_escape_string($_POST['bid_security_amount']));
        $bid_security_currency = mysql_real_escape_string($_POST['bid_security_currency']);

        if($lotid > 0)
        {
            $data['lotid'] = $lotid;
            $query = $this->db->query("UPDATE  lots SET bid_id='".$bidid."',lot_number='".$lotnumber."',lot_title='".$lottitle."',isactive='Y',lot_details='".$lotdetails."',author='".$userid."',bid_security_amount = '".$bid_security_amount."',bid_security_currency='".$bid_security_currency."'  WHERE  id = '".$data['lotid']."'");
          log_action('update','IFB  Lot   '. $lottitle.'  Record Updated Successfully  ', 'IFB Lot  has been Updated  Successfully');
  
        }
        else
        {
            $data['bid_id'] = decryptValue($_POST['bidid']);
            $query = $this->db->query("INSERT INTO lots(bid_id,lot_number,lot_title,isactive,lot_details,author,bid_security_amount,bid_security_currency) 	VALUES('".$bidid."','".$lotnumber."','".$lottitle."','Y','".$lotdetails."','".$userid."','".$bid_security_amount."','".$bid_security_currency."')");
      
      	   log_action('create','IFB  Lot   '. $lottitle.'  Record Created Successfully  ', 'IFB Lot  has been Created  Successfully');
  
        }
        if(!empty($query))
        {
            print_r("1");
        }
        else{
            print_r("0");
        }

    }
    




    #Manage Lots
    function manage_lots(){
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);


        if(!empty($data['i']))
        {
            $data['bid_id'] = decryptValue($data['i']);
            $app_select_str = '   bidinvitations.id ="'. decryptValue($data['i']) .'" AND lots.isactive ="Y" ORDER BY lots.dateadded DESC ';
            //$data['bidinvation_lots'] = $this->db->query(
            $data['lots'] = paginate_list($this, $data, 'fetch_bidinvitation_lots', array('searchstring'=>$app_select_str),10);
            #	exit($this->db->last_query());

        }

        $data['page_title'] =  'Manage Lots';
        $data['current_menu'] = 'Manage Lots';
        $data['view_to_load'] = 'bids/manage_lots';
        $data['view_data']['form_title'] = $data['page_title'];
        //print_r($data);

        $this->load->view('dashboard_v', $data);
    }



 
    # Delete Lots
    function delete_lot()
    {
        //print_r($_POST);
        $lotid = mysql_real_escape_string($_POST['lotid']);
        $option = mysql_real_escape_string($_POST['option']);
        switch ($option) {
            case 'archive':
                $query = $this->db->query("UPDATE lots SET isactive ='N' WHERE id = '".$lotid."'") or die("".mysql_error());
                if($query)
                {
				    log_action('delete','IFB  Lot      Record Updated Deleted   ', 'IFB Lot  has been Deleted  Successfully');  
                    print_r("1");
                }
                break;

            default:
                # code...
                break;
        }

    }


 
 
    #approve bid invitation
    function approve_bid_invitation()
    {
        #check user access
        check_user_access($this, 'publish_invitation_for_bids', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        #print_r($data); exit();

        $data = add_msg_if_any($this, $data);


        if(!empty($_POST['save']) && !empty($data['i']))
        {
            //cc_approval_date
            //additional_notes
            $required_fields = array();

            $_POST = clean_form_data($_POST);
            $_POST['additional_notes'] = trim($_POST['additional_notes']);

            $validation_results = validate_form('', $_POST, $required_fields);

            #Only proceed if the validation for required fields passes
            if($validation_results['bool'])
            {
                if(!empty($data['i']))
                {
                    $_POST['approvedby'] = $this->session->userdata('userid');
                    $bid_invitation_id = 	$_POST['bidinvitation_id'] = decryptValue($data['i']);

                    $additional_notes  ='';
                    if(!empty($_POST['additional_notes']))
                    {	$additional_notes = trim(mysql_real_escape_string($_POST['additional_notes']));

                    }

                    $_POST['additionalnotes']  =  $additional_notes;

                    $query =$this->Query_reader->get_query_by_code('publish_bid_invitation', $_POST);
                    $result = mysql_query($query) or die("".mysql_error());
                    #print_r($query); exit();

                    #$result = $this->db->query($this->Query_reader->get_query_by_code('publish_bid_invitation', $_POST));
                    #exit($this->db->last_query());
                }

                #bid invitation has been approved successfully
                if(!empty($result) && $result)
                {

                    #all I need is credentials
                    $data['notifyrop'] =  $bid_invitation_id;

                      $refence_num = $this->bid_invitation_m->get_bid_invitation_info($bid_invitation_id,'procurement_ref_no');



                    $data['msg'] = "SUCCESS: The bid invitation has been published and is now visible to the public.";
                    $this->session->set_userdata('sres', $data['msg']);
                    
                      log_action('approve','IFB   '.$refence_num[0]['procurement_ref_no'].'    Record Aprroaved Successfully     ', 'IFB   '.$refence_num[0]['procurement_ref_no'].'    Record Aprroaved Successfully  ');  
          

                    redirect('bids/manage_bid_invitations/notifyrop/'.$bid_invitation_id.'/m/sres' . ((!empty($data['v']))? "/v/".$data['v'] : ''));

                }
                else if(empty($data['msg']))
                {
                    $data['msg'] = "ERROR: The bid invitation could not be published or was not published correctly.";
                }
            }

            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool']))
                && empty($data['msg']) )
            {
                $data['msg'] = "WARNING: The highlighted fields are required.";
            }

            $data['requiredfields'] = $validation_results['requiredfields'];
        }


        if(!empty($data['i']))
        {
            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

            $bid_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $bid_id .'" AND isactive="Y"'));

            #exit($this->db->last_query());
            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.procurement_ref_no="'. $data['formdata']['procurement_ref_no'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }

            $bid_id = decryptValue($data['i']);
            $bid_details = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $bid_id .'" AND isactive="Y"'));
            if(!empty($bid_details['additional_notes']))
                $data['formdata']['additional_notes'] = $bid_details['additional_notes'];


        }

        if(!empty($_POST['approval_comments']))
            $data['formdata']['approval_comments'] = $_POST['approval_comments'];

        $data['page_title'] = 'Approve bid invitation';
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'bids/approve_bid_invitation_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }



    #view bid invitation
    function view_bid_invitation()
    {
        #check user access
        check_user_access($this, 'publish_invitation_for_bids', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        if(!empty($data['i']))
        {
            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

            $bid_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('view_bid_invitations_ifb', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'BI.id', 'searchstring'=>' BI.id="'. $bid_id .'" '));
            /// AND isactive="Y"


            if(empty($data['formdata']) || empty($data['formdata']['cc_approval_date']))
            {
                $data['msg'] = "WARNING: The bid invitation has not been approved";
                $this->session->set_userdata('sres', $data['msg']);

                redirect('bids/manage_bid_invitations/m/sres');
            }

            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                #exit($this->db->last_query());
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_ifb', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.id="'. $data['formdata']['procurement_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }

            if(!empty($data['v']) && $data['v'] == 'doc'):
                $this->load->view('bids/invitation_for_bids_pdf', $data);

                $html = $this->output->get_output();
                report_to_pdf($this, $html, 'IFB_document_'.strtotime(date('Y-m-d h:i')));
                return;
            endif;

        }

        # get procurement method by title
        $my_data=array(
            'title'=>$data['formdata']['procurement_method']
        );
        $procurement_method=$this->procurement_method_m->get_where($my_data);



        $data['page_title'] = 'Preview bid invitation';
        $data['current_menu'] = 'view_bid_invitations';
        # use special view for EOI procurement method (11)
        $data['view_to_load'] = $procurement_method[0]['id']==11?'bids/view_EOI_bid_invitation':'bids/view_bid_invitation';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }

    #function to print IFB
    function ifb_to_pdf()
    {
        #check user access
        check_user_access($this, 'publish_invitation_for_bids', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        if(!empty($data['i']))
        {
            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

            $bid_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $bid_id .'" AND isactive="Y"'));

            if(empty($data['formdata']) || $data['formdata']['isapproved'] == 'N' || empty($data['formdata']['cc_approval_date']))
            {
                $data['msg'] = "WARNING: The bid invitation has not been approved";
                $this->session->set_userdata('sres', $data['msg']);

                redirect('bids/manage_bid_invitations/m/sres');
            }

            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.procurement_ref_no="'. $data['formdata']['procurement_ref_no'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }
        }

        $data['page_title'] = 'Preview bid invitation';
        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'bids/view_bid_invitation';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }



    #function to show procurement plan record details
    function procurement_recorddetails_contracts()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $notify = 0;

        if(!empty($data['notification'])){
            $notify = 1;
        }


        if($this->input->post('proc_id'))
        {
            $_POST = clean_form_data($_POST);

            $procurement_ref_no = trim($_POST['procurementrefno']);
            $bidinvitation_id = mysql_real_escape_string(trim($_POST['bidinvitationid']));
            $receiptid = mysql_real_escape_string($_POST['receiptid']);
            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            $data['ajax_data'] = $_POST;


            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

            $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=>$app_select_str . ' AND  receipts.beb="Y" AND bidinvitations.id ="'.$bidinvitation_id.'"  AND  receipts.receiptid = "'.$receiptid.'"  AND procurement_plan_entries.id="'. $_POST['proc_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

            #print_r(	$data['procurement_details']); exit();
            #get provider info
            if(!empty($data['b'])):
                $data['provider'] = $this->Query_reader->get_row_as_array('get_IFB_BEB', array('searchstring'=> ' AND BI.procurement_id="'.
                    $_POST['proc_id'] .'"  AND R.receiptid = "'.$receiptid.'"   AND beb="Y" '));

                if(!empty($data['provider']) && empty($data['provider']['providerid'])):
                    $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $data['provider']['joint_venture'] .'"')->result_array();
                    if(!empty($jv_info[0]['providers'])):
                        $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
                        $data['provider']['providernames'] = '';

                        foreach($providers as $provider):
                            $data['provider']['providernames'] .= (!empty($data['provider']['providernames'])? ', ' : '').
                                $provider['providernames'];
                        endforeach;

                    endif;
                endif;

                #exit($this->db->last_query());
            endif;
        }

        
        if($notify == 1)
        {

            #	print_r($data['provider']['providername']);


            if(!empty($data['provider']['providername'])){
                print_r($data['provider']['providerid']);
                $providerid = $data['provider']['providerid'];
                if(is_numeric($providerid))
                {

                    $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN ('.rtrim($data['provider']['providerid'],',').') ' ) -> result_array();

                }
                else
                {
                    #$query = 'SELECT * FROM providers WHERE providerid IN (SELECT  TRIM(TRAILING "," FROM providers) FROM  joint_venture	 WHERE jv = "'.$data['provider']['providername'].'" ) ';
                    #echo $query;
                    $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN (SELECT  TRIM(TRAILING "," FROM providers) FROM  joint_venture	 WHERE jv = "'.$data['provider']['providername'].'" ) ' ) -> result_array();

                }

                #print_r($procurementdetails);
                $providers = '<ul>';
                $xc = '';
                #$suspended = '';
                $status = 0;
                foreach ($procurementdetails as $key => $value) {

                    #check provider
                    $xc = searchprovidervalidity($value['providernames']);
                    if(!empty($xc))
                    {
                        $status =1;
                        $providers .= "<li> ".$value['providernames']." ".'</li>';
                        # $suspended .= $value['providernames'].',';
                    }
                }

                $providers .= '<ul>';
                $rand  = rand(23454,83938);
                $this->session->set_userdata('level','ppda');
                $userid = $this->session->userdata('userid');

                $query1 = $this->db->query("SELECT CONCAT(firstname,',',lastname) AS names FROM  users WHERE userid=".$userid ." limit 1")-> result_array();
                $level = "Disposal";

                $entity =  $this->session->userdata('pdeid');
                $query = $this->db->query("SELECT * FROM pdes WHERE pdeid=".$entity." limit 1")-> result_array();
                $entityname = $query[0]['pdename'];

                $titles = " Attemp to award a contract to    suspended provider(s) by ".$entityname."  -CO ".$rand." ";

                $body = "<h2> SUSPENDED  PROVIDER</H2> ";
                $body .="<table><tr><th> Organisation(S) </th><td>".$providers." </td></tr>";
                $body .="<tr><th>Admininstrator </th><td>".$query1[0]['names']." </td></tr>";
                $body .="<tr><th> Date </th><td>".Date('Y m-d')." </td></tr>";
                $body .= "</table>";
                $permission = "view_disposal_plans";

                push_permission($titles,$body,$level,$permission);



            }
        }
        else{
            $data['level'] = 'award_contracts';
            $data['area'] = 'procurement_record_details_contracts';
            $this->load->view('includes/add_ons', $data);
        }

    }




#function to show procurement plan record details
    function procurement_recorddetails()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $notify = 0;

        if(!empty($data['notification'])){
            $notify = 1;
        }


        if($this->input->post('proc_id'))
        {
            $_POST = clean_form_data($_POST);
 
            $procurement_ref_no = trim($_POST['procurementrefno']);
            $bidinvitation_id = trim($_POST['bidinvitationid']);
            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

           
            $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str . ' AND  receipts.beb="Y" AND bidinvitations.id ="'.$bidinvitation_id.'"AND procurement_plan_entries.id="'. $_POST['proc_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

            #print_r(	$data['procurement_details']); exit();
            #get provider info
            if(!empty($data['b'])):
                $data['provider'] = $this->Query_reader->get_row_as_array('get_IFB_BEB', array('searchstring'=> ' AND BI.procurement_id="'.
                    $_POST['proc_id'] .'" AND beb="Y"'));

                if(!empty($data['provider']) && empty($data['provider']['providerid'])):
                    $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $data['provider']['joint_venture'] .'"')->result_array();
                    if(!empty($jv_info[0]['providers'])):
                        $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
                        $data['provider']['providernames'] = '';

                        foreach($providers as $provider):
                            $data['provider']['providernames'] .= (!empty($data['provider']['providernames'])? ', ' : '').
                                $provider['providernames'];
                        endforeach;

                    endif;
                endif;

                #exit($this->db->last_query());
            endif;
        }

     

        if($notify == 1){

            print_r($data['provider']['providername']);


            if(!empty($data['provider']['providername'])){
                print_r($data['provider']['providerid']);
                $providerid = $data['provider']['providerid'];
                if(is_numeric($providerid))
                {

                    $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN ('.rtrim($data['provider']['providerid'],',').') ' ) -> result_array();

                }
                else
                {
                    #$query = 'SELECT * FROM providers WHERE providerid IN (SELECT  TRIM(TRAILING "," FROM providers) FROM  joint_venture	 WHERE jv = "'.$data['provider']['providername'].'" ) ';
                    #echo $query;
                    $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN (SELECT  TRIM(TRAILING "," FROM providers) FROM  joint_venture	 WHERE jv = "'.$data['provider']['providername'].'" ) ' ) -> result_array();

                }

                #print_r($procurementdetails);
                $providers = '<ul>';
                $xc = '';
                #$suspended = '';
                $status = 0;
                foreach ($procurementdetails as $key => $value) {

                    #check provider
                    $xc = searchprovidervalidity($value['providernames']);
                    if(!empty($xc))
                    {
                        $status =1;
                        $providers .= "<li> ".$value['providernames']." ".'</li>';
                        # $suspended .= $value['providernames'].',';
                    }
                }

                $providers .= '<ul>';


                $rand  = rand(23454,83938);

                $this->session->set_userdata('level','ppda');
                $userid = $this->session->userdata('userid');

                $query1 = $this->db->query("SELECT CONCAT(firstname,',',lastname) AS names FROM  users WHERE userid=".$userid ." limit 1")-> result_array();
                $level = "Disposal";

                $entity =  $this->session->userdata('pdeid');
                $query = $this->db->query("SELECT * FROM pdes WHERE pdeid=".$entity." limit 1")-> result_array();
                $entityname = $query[0]['pdename'];

                $titles = " Attemp to award a contract to    suspended provider(s) by ".$entityname."  -CO ".$rand." ";

                $body = "<h2> SUSPENDED  PROVIDER</H2> ";
                $body .="<table><tr><th> Organisation(S) </th><td>".$providers." </td></tr>";
                $body .="<tr><th>Admininstrator </th><td>".$query1[0]['names']." </td></tr>";
                $body .="<tr><th> Date </th><td>".Date('Y m-d')." </td></tr>";
                $body .= "</table>";
                $permission = "view_disposal_plans";

                push_permission($titles,$body,$level,$permission);



            }
        }
        else{
            $data['area'] = 'procurement_record_details';

            $this->load->view('includes/add_ons', $data);
        }

    }


    #Jsen Encode Procurement Details
    function json_encode_procurement_entry()
    {

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'b'));
        # Pick all assigned data
        $data = assign_to_data($urldata);


        if($this->input->post('proc_id'))
        {
            $_POST = clean_form_data($_POST);


            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND pdes.pdeid ="'. $userdetails[0]['pde'] .'"';
            }
            if(!empty($_POST['branch_shortcode']))
                $app_select_str .= ' AND pde_branches.shortcode ="'.trim($_POST['branch_shortcode']).'"';

            $procurementdetails = $this->Query_reader->get_row_as_array('procurement_entry_details', array('searchstring'=>$app_select_str . '  AND procurement_plan_entries.id="'. $_POST['proc_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));


			$return_arr = array();

			$row_array = array();

         

            	    $row_array['subject_of_procurement'] = $procurementdetails['subject_of_procurement'];
				    $row_array['procurement_type'] = $procurementdetails['procurement_type'];
				    $row_array['procurement_method'] = $procurementdetails['procurement_method'];


            	    $row_array['pde_department'] = $procurementdetails['pde_department'];
				    $row_array['funding_source'] = $procurementdetails['funding_source'];
				    $row_array['funder_name'] = $procurementdetails['funder_name'];


            	    $row_array['procurement_ref_no'] = $procurementdetails['procurement_ref_no'];
				    $row_array['pre_bid_events_date'] = display_date($procurementdetails['pre_bid_events_date']);
				    $row_array['pre_bid_events_duration'] = $procurementdetails['pre_bid_events_duration'];



            	    $row_array['exchange_rate'] = $procurementdetails['exchange_rate'];
				    $row_array['procurement_type'] = $procurementdetails['procurement_type'];
				    $row_array['procurement_method'] = $procurementdetails['procurement_method'];




            	    $row_array['contracts_committee_approval_date'] = display_date($procurementdetails['contracts_committee_approval_date']);
				    $row_array['contracts_committee_approval_date_duration'] = $procurementdetails['contracts_committee_approval_date_duration'];
				    $row_array['publication_of_pre_qualification_date'] = display_date($procurementdetails['publication_of_pre_qualification_date']);




            	    $row_array['publication_of_pre_qualification_date_duration'] = $procurementdetails['publication_of_pre_qualification_date_duration'];
				    $row_array['proposal_submission_date'] = display_date($procurementdetails['proposal_submission_date']);
				    $row_array['proposal_submission_date_duration'] = $procurementdetails['proposal_submission_date_duration'];





            	    $row_array['contracts_committee_approval_of_shortlist_date'] = display_date($procurementdetails['contracts_committee_approval_of_shortlist_date']);
				    $row_array['contracts_committee_approval_of_shortlist_date_duration'] = $procurementdetails['contracts_committee_approval_of_shortlist_date_duration'];
				    $row_array['bid_issue_date'] = display_date($procurementdetails['bid_issue_date']);





            	    $row_array['bid_issue_date_duration'] = $procurementdetails['bid_issue_date_duration'];
				    $row_array['bid_submission_opening_date'] = display_date($procurementdetails['bid_submission_opening_date']);
				    $row_array['bid_submission_opening_date_duration'] = $procurementdetails['bid_submission_opening_date_duration'];




            	    $row_array['secure_necessary_approval_date'] = display_date($procurementdetails['secure_necessary_approval_date']);
				    $row_array['secure_necessary_approval_date_duration'] = $procurementdetails['secure_necessary_approval_date_duration'];
				    $row_array['contract_award'] = display_date($procurementdetails['contract_award']);



				    $row_array['contract_award_duration'] = $procurementdetails['contract_award_duration'];
				    $row_array['performance_security'] = $procurementdetails['performance_security'];
				    $row_array['best_evaluated_bidder_date'] = display_date($procurementdetails['best_evaluated_bidder_date']);




				    $row_array['best_evaluated_bidder_date_duration'] = $procurementdetails['best_evaluated_bidder_date_duration'];
				    $row_array['contract_sign_date'] = display_date($procurementdetails['contract_sign_date']);
				    $row_array['contract_sign_duration'] = display_date($procurementdetails['contract_sign_duration']);




				    $row_array['submission_of_evaluation_report_to_cc'] = display_date($procurementdetails['submission_of_evaluation_report_to_cc']);
				    $row_array['cc_approval_of_evaluation_report'] = display_date($procurementdetails['cc_approval_of_evaluation_report']);
				    $row_array['accounting_officer_approval_date'] = display_date($procurementdetails['accounting_officer_approval_date']);


				    $row_array['cc_approval_of_evaluation_report_duration'] = $procurementdetails['cc_approval_of_evaluation_report_duration'];
				    $row_array['negotiation_date'] = display_date($procurementdetails['negotiation_date']);
				    $row_array['negotiation_date_duration'] = display_date($procurementdetails['negotiation_date_duration']);



				    $row_array['negotiation_approval_date'] = display_date($procurementdetails['negotiation_approval_date']);
				    $row_array['negotiation_approval_date_duration'] = $procurementdetails['negotiation_approval_date_duration'];
				    $row_array['advanced_payment_date'] = display_date($procurementdetails['advanced_payment_date']);




				    $row_array['advanced_payment_date_duration'] = $procurementdetails['advanced_payment_date_duration'];
				    $row_array['mobilise_advance_payment'] = display_date($procurementdetails['mobilise_advance_payment']);
				    $row_array['mobilise_advance_payment_duration'] = $procurementdetails['mobilise_advance_payment_duration'];


				    $row_array['substantial_completion'] = display_date($procurementdetails['substantial_completion']);
				    $row_array['substantial_completion_duration'] = $procurementdetails['substantial_completion_duration'];
				    $row_array['final_acceptance'] = display_date($procurementdetails['final_acceptance']);


				    $row_array['final_acceptance_duration'] = $procurementdetails['final_acceptance_duration'];
				    $row_array['dateadded'] = display_date($procurementdetails['dateadded']);
				    $row_array['dateupdated'] = display_date($procurementdetails['dateupdated']);


				    $row_array['updated_by'] = $procurementdetails['updated_by'];
				    $row_array['isactive'] = $procurementdetails['isactive'];
				    $row_array['procurement_plan_id'] = $procurementdetails['procurement_plan_id'];



				    $row_array['solicitor_general_approval_date'] = display_date($procurementdetails['solicitor_general_approval_date']);
				    $row_array['contract_amount_in_ugx'] = $procurementdetails['contract_amount_in_ugx'];
				    $row_array['bid_closing_date'] = display_date($procurementdetails['bid_closing_date']);


				    $row_array['author'] = $procurementdetails['author'];
				    $row_array['quantity'] = $procurementdetails['quantity'];
				    $row_array['total_ifb_quantity'] = $procurementdetails['total_ifb_quantity'];



				    $row_array['draft_report'] = display_date($procurementdetails['draft_report']);
				    $row_array['inspection_final_acceptance'] = display_date($procurementdetails['inspection_final_acceptance']);
				    $row_array['arrival_of_goods'] = display_date($procurementdetails['arrival_of_goods']);




				    $row_array['opening_of_credit_letter'] = display_date($procurementdetails['opening_of_credit_letter']);
				    $row_array['final_report'] = display_date($procurementdetails['final_report']);
				    $row_array['quantifiable'] = $procurementdetails['quantifiable'];


				    $row_array['revenue'] = $procurementdetails['revenue'];
				    $row_array['branch_address'] = $procurementdetails['branch_address'];
				    $row_array['pde_address'] = $procurementdetails['pde_address']; 

				    $row_array['ifb_quantity_added'] = $procurementdetails['ifb_quantity_added'];
				  
           echo json_encode($row_array);

        }
        else
        {
            print "0";
        }


    }


    #function to show procurement plan record details
    function procurement_record_details()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $notify = 0;
        if(!empty($data['notification'])){
            $notify = 1;
        }

        if(!empty($_POST['ifbquantity']))
        {
            $data['ifbquantity'] = mysql_real_escape_string($_POST['ifbquantity']);
        }

        if($this->input->post('proc_id'))
        {
            $_POST = clean_form_data($_POST);

            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

            #$query = $this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.id="'. $_POST['proc_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            #print_r($query); exit();
            //procurement details

            #$data['procurement_details'] = $this->Query_reader->get_row_as_array('ProcurementPlanDetails', array('searchstring'=>$app_select_str . '  AND  receipts.beb="Y" AND procurement_plan_entries.id="'. $_POST['proc_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

            $data['procurement_details'] = $this->Query_reader->get_row_as_array('ProcurementPlanDetails', array('searchstring'=>$app_select_str . '  AND procurement_plan_entries.id="'. $_POST['proc_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

            $data['ifb_quantity'] = 0;

            if(!empty($data['bidid']))
            {
                $ifbquantity_edit = $this->db->query("SELECT quantity  FROM bidinvitations WHERE id=".decryptValue($data['bidid'])."")->result_array();
                $data['ifb_quantity'] = $ifbquantity_edit[0]['quantity'];
            }

            #get provider info
            if(!empty($data['b'])):
                $data['provider'] = $this->Query_reader->get_row_as_array('get_IFB_BEB', array('searchstring'=> ' AND BI.procurement_id="'.
                    $_POST['proc_id'] .'" AND beb="Y"'));

                if(!empty($data['provider']) && empty($data['provider']['providerid'])):
                    $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $data['provider']['joint_venture'] .'"')->result_array();
                    if(!empty($jv_info[0]['providers'])):
                        $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
                        $data['provider']['providernames'] = '';

                        foreach($providers as $provider):
                            $data['provider']['providernames'] .= (!empty($data['provider']['providernames'])? ', ' : '').
                                $provider['providernames'];
                        endforeach;

                    endif;
                endif;

                #exit($this->db->last_query());
            endif;
        }

        // $data['area'] = 'procurement_record_details';

        // $this->load->view('includes/add_ons', $data);


        if($notify == 1){

            print_r($data['provider']['providername']);


            if(!empty($data['provider']['providername'])){
                print_r($data['provider']['providerid']);
                $providerid = $data['provider']['providerid'];
                if(is_numeric($providerid))
                {

                    $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN ('.rtrim($data['provider']['providerid'],',').') ' ) -> result_array();

                }
                else
                {
                    #$query = 'SELECT * FROM providers WHERE providerid IN (SELECT  TRIM(TRAILING "," FROM providers) FROM  joint_venture	 WHERE jv = "'.$data['provider']['providername'].'" ) ';
                    #echo $query;
                    $procurementdetails = $this->db->query('SELECT * FROM providers WHERE providerid IN (SELECT  TRIM(TRAILING "," FROM providers) FROM  joint_venture	 WHERE jv = "'.$data['provider']['providername'].'" ) ' ) -> result_array();

                }

                #print_r($procurementdetails);
                $providers = '<ul>';
                $xc = '';
                #$suspended = '';
                $status = 0;
                foreach ($procurementdetails as $key => $value) {

                    #check provider
                    $xc = searchprovidervalidity($value['providernames']);
                    if(!empty($xc))
                    {
                        $status =1;
                        $providers .= "<li> ".$value['providernames']." ".'</li>';
                        # $suspended .= $value['providernames'].',';
                    }
                }

                $providers .= '<ul>';



                $rand  = rand(23454,83938);

                $this->session->set_userdata('level','ppda');
                $userid = $this->session->userdata('userid');
                $query1 = $this->db->query("SELECT CONCAT(firstname,',',lastname) AS names FROM  users WHERE userid=".$userid ." limit 1")-> result_array();
                $level = "Disposal";
                $entity =  $this->session->userdata('pdeid');
                $query = $this->db->query("SELECT * FROM pdes WHERE pdeid=".$entity." limit 1")-> result_array();
                $entityname = $query[0]['pdename'];

                $titles = " Attemp to award a contract to    suspended provider(s) by ".$entityname."  -CO ".$rand." ";

                $body =  " <h2> SUSPENDED  PROVIDER</H2> ";
                $body .="<table><tr><th> Organisation(S) </th><td>".$providers." </td></tr>";
                $body .="<tr><th>Admininstrator </th><td>".$query1[0]['names']." </td></tr>";
                $body .="<tr><th> Date </th><td>".Date('Y m-d')." </td></tr>";
                $body .= "</table>";
                $permission = "view_disposal_plans";

                push_permission($titles,$body,$level,$permission);



            }
        }
        else{

            $data['area'] = 'procurement_record_details';

            $this->load->view('includes/add_ons', $data);
        }

    }

    #Function to delete a provider's details
    function delete_bid_invitation()
    {
        #check user access
        check_user_access($this, 'delete_invitation_for_bid', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        if(!empty($data['i'])){
            $result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_item', array('item'=>'bidinvitations', 'id'=>decryptValue($data['i'])) ));
            $query = $this->db->query("UPDATE bidinvitations_quantity SET isactive ='N' WHERE bid_id =  ".decryptValue($data['i'])."");


            $refence_num = $this->bid_invitation_m->get_bid_invitation_info( decryptValue($data['i']) ,'procurement_ref_no');


           log_action('delete','IFB   Record Deleted  Successfully     ', 'IFB    '.$refence_num[0]['procurement_ref_no'].'    Record Deleted Successfully  ');  

          
        }

        if(!empty($result) && $result){
            $this->session->set_userdata('dbid', "The bid invitation details have been successfully deleted.");
        }
        else if(empty($data['msg']))
        {
            $this->session->set_userdata('dbid', "ERROR: The bid invitation details could not be deleted or were not deleted correctly.");
        }

        if(!empty($data['t']) && $data['t'] == 'super'){
            $tstr = "/t/super";
        }else{
            $tstr = "";
        }
        redirect(base_url()."bids/manage_bid_invitations/m/dbid".$tstr);
    }


   #cancel Bid Invitation 
	function cancel_bid_invitation()
	{
		#check user access
        check_user_access($this, 'delete_invitation_for_bid', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
		 
        
        $formdata = $_POST ;
		 
			
			$termination_reason = mysql_real_escape_string($_POST['termination_reason']);  
			$date_contract_terminated = !empty($_POST['date_contract_terminated']) ? date('Y-m-d',strtotime($_POST['date_contract_terminated'])) : Date('Y-m-d') ;  
			$userid = $this->session->userdata['userid'];
			
            #$result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_item', array('item'=>'bidinvitations', 'id'=>decryptValue($data['i'])) ));
            $query = $this->db->query("UPDATE bidinvitations SET isactive ='C' , reason_for_cancellation='".$termination_reason."',date_cancelled='".$date_contract_terminated."',who_cancelled='".$userid."'  WHERE id =  ".$formdata['bidid']."");


             $refence_num = $this->bid_invitation_m->get_bid_invitation_info( $formdata['bidid'] ,'procurement_ref_no');


           
            log_action('cancel','IFB     Record Cancelled  Successfully     ', 'IFB     '.$refence_num[0]['procurement_ref_no'].'   Record Cancelled Successfully  ');  
   
			
			#exit($this->db->last_query());
			if($query)
				echo  1;
			
		  
		
		
	}

	
    #Function to load invitation for bid form
    function load_approve_bid_invitation_form()
    {
        #check user access
        check_user_access($this, 'approve_invitation_for_bids', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $app_select_str = ' procurement_plan_entries.isactive="Y" ';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
        }

        $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('procurement_plan_details', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' )))->result_array();

        #exit($this->db->last_query());

        if(!empty($data['i']))
        {
            $app_select_str = ' procurement_plan_entries.isactive="Y" ';

            if($this->session->userdata('isadmin') == 'N')
            {
                $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
                $app_select_str .= ' AND procurement_plans.pde_id ="'. $userdetails[0]['pde'] .'"';
            }

            $bid_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $bid_id .'" AND isactive="Y"'));

            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.procurement_ref_no="'. $data['formdata']['procurement_ref_no'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }
        }

        if(!empty($data['formdata']['approval_comments']))
            $data['formdata']['approval_comments'] = $_POST['approval_comments'];

        $data['page_title'] = 'Approve bid invitation';
        $data['current_menu'] = 'manage_bid_invitations';
        $data['view_to_load'] = 'bids/approve_bid_invitation_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }

    


    /*****************************************************
    MOVER
     *****************************************************/


    function publish_bidder_lots()
    {


        $urldata = $this->uri->uri_to_assoc(4, array('m'));
        $data = assign_to_data($urldata);
        #print_r($_POST);
        #exit();


        $bidid = mysql_real_escape_string($_POST['bidid']);

        $searchstring = ' ';
        $data['bidinformation'] = $this-> Receipts_m -> fetchbidinformation($bidid , $searchstring);
        #get receipts information
        $data['receiptinfo'] = $this-> Receipts_m -> fetchreceipts($bidid);
        #count bids
        $data['localbids'] = $this-> Receipts_m -> count_bids('uganda',$bidid);
        $data['foreignbids'] = $this-> Receipts_m -> count_bids('Foreign',$bidid);
        #fetch evaluation methods
        $data['evaluation_methods'] = $this-> Evaluation_methods_m -> fetchevaluationmethods();
        #fetc
        $searchQuery = ' AND received_lots.lotid ='.mysql_real_escape_string($_POST['lot']).'';
        $data['providerslist'] = $this-> Receipts_m -> fetchproviders_lots($bidid,$searchQuery);
        $data['lott_id'] = $_POST['lot'];

        #Get the Session Variable
        $data['var'] = $this->session->userdata;

        # Pick all assigned data
        $data['bebresult'] = $this -> db -> query("SELECT bestevaluatedbidder.*, received_lots.lotid as received_lot FROM bestevaluatedbidder    INNER JOIN receipts    ON bestevaluatedbidder.pid = receipts.receiptid   left outer JOIN received_lots ON receipts.receiptid = received_lots.receiptid    WHERE  received_lots.lotid = '".mysql_real_escape_string($_POST['lot'])."'   AND receipts.beb = 'Y' ")->result_array();
        # exit($this->db->last_query());
        # print_r($data['bebresult']);
        $data['formtype']='editbeb';

        $data['bidid'] = $bidid;
        $data['page_title'] = 'Publish Best Evaluated Bidder ';
        #$data['level'] = $status;dd
        $data['view_to_load'] = 'bids/publish_bidder_v';



        $data['area'] = 'publish_bidder_lots';
        $this->load->view('includes/publish_bidder_lots_v', $data);




        #$this->load->view( $data['view_to_load'], $data);

    }



    #add new Receipt to a given Procurment
    function publish_bidder(){

        $status = $this->uri->segment(3);

        //unsertting editbeb session


        switch ($status) {


            case 'publish_bidder':


                $urldata = $this->uri->uri_to_assoc(4, array('m'));
                $data = assign_to_data($urldata);

                $selectedlotid = '';

                if(!empty($data['lotid']))
                {
                    $selectedlotid = base64_decode($data['lotid']);
                }



                $bidid= $data['bidid'];
                $procurement_ref_no = base64_decode($data['procrefno']);

                if(!empty($_POST['procurement_ref_no']))
                {
                    $procurement_ref_no = mysql_real_escape_string($_POST['procurement_ref_no']);
                    $bidid = mysql_real_escape_string($_POST['bidid']);
                }

                $data['procurementdetails'] = $this-> Proc_m -> fetch_annual_procurement_plan($bidid);
 
		
               
                #get bid information
                $data['bidinformation'] = $this-> Receipts_m -> fetchbidinformation($bidid);
                #get receipts information
                $data['receiptinfo'] = $this-> Receipts_m -> fetchreceipts($bidid);
	
                #count bids
                $data['localbids'] = $this-> Receipts_m -> count_bids('uganda',$bidid);
                $data['foreignbids'] = $this-> Receipts_m -> count_bids('Foreign',$bidid);
                #fetch evaluation methods
                $data['evaluation_methods'] = $this-> Evaluation_methods_m -> fetchevaluationmethods();
                
               
                #fetch providers
                $data['providerslist'] = $this-> Receipts_m -> fetchproviders($bidid);
                # $data['unsuccesful_bidders'] =   $this-> Receipts_m -> fetch_unsuccesful_bidders(0,$bidid);
                $data['lots'] = $this-> Receipts_m->fetchlots($bidid);
				
		 
				#fetch users in this pde:
				$pde =  $this->session->userdata['pdeid'];
				$data['authorizers'] = $this->db->query("select * from users where pde = $pde ")-> result_array();
 
                # Pick all assigned data

                $data['var'] = $this->session->userdata;
                #print_r($data['var']['bebid']);
                #exit();


                if(!empty($data['var']['bebid']) )
                {
 
                    $bebid = $data['var']['bebid'];
                    $data['bebresult'] = $this -> db -> query("SELECT bestevaluatedbidder.* FROM bestevaluatedbidder    INNER JOIN receipts    ON bestevaluatedbidder.pid = receipts.receiptid   left outer JOIN received_lots ON bestevaluatedbidder.lotid = received_lots.lotid   WHERE   IF(bestevaluatedbidder.lotid > 0, bestevaluatedbidder.bidid = ".$bidid.",bestevaluatedbidder.id = ".$bebid.")")->result_array();
                    if(!empty($selectedlotid))
                    {  $data['lots'] = $this -> db -> query(" SELECT DISTINCT lots.* FROM lots INNER JOIN received_lots      ON lots.id = received_lots.lotid INNER JOIN      receipts ON receipts.receiptid = received_lots.receiptid     INNER JOIN bestevaluatedbidder   ON lots.bid_id = bestevaluatedbidder.bidid WHERE bestevaluatedbidder.bidid = ".$bidid."  AND lots.id = ".$selectedlotid."")->result_array();
                        $data['selectedlotid'] = $selectedlotid;
                    }
                    else
                        $data['lots'] = $this -> db -> query(" SELECT DISTINCT lots.* FROM lots INNER JOIN received_lots      ON lots.id = received_lots.lotid INNER JOIN      receipts ON receipts.receiptid = received_lots.receiptid     INNER JOIN bestevaluatedbidder   ON lots.bid_id = bestevaluatedbidder.bidid WHERE bestevaluatedbidder.bidid = ".$bidid." ")->result_array();

                    $data['formtype']='editbeb';    

                }
 
                $data['bidid'] = $bidid;
                $data['page_title'] = 'Publish Best Evaluated Bidder ';
                $data['level'] = $status;
                $data['view_to_load'] = 'bids/publish_bidder_v';
            
 				$this->load->view( $data['view_to_load'], $data);
                break;



            case 'view_bidders_list':
                # code...
                #check user access
                #check_user_access($this, 'view_bidders_list', 'redirect');

                $bid_id = $this->uri->segment(4);
                $procurement_ref_no = base64_decode($this->uri->segment(5));
                $data['page_title'] = $procurement_ref_no;
                $data['bidderslist'] = $this-> Receipts_m -> fetctpdereceipts($bid_id);
                #print_r($data['bidderslist']); exit();
                $data['level'] = $status;
                break;




            case 'active_procurements':

                # check_user_access($this, 'active_procurements', 'redirect');

                $urldata = $this->uri->uri_to_assoc(4, array('m'));
                $data = assign_to_data($urldata);

                //$var = $this->session->userdata;
                if(!isset($data['editbeb']) || empty($data['editbeb']))	{
                    $this->session->unset_userdata('bebid');
                    #exit("Interesting .... ");
                }

                # Pick all assigned data
                $data = assign_to_data($urldata);
                $data = add_msg_if_any($this, $data);
                $data = handle_redirected_msgs($this, $data);

                $current_financial_year = currentyear.'-'.endyear;
                $from=substr($current_financial_year,0,4);
                $to=substr($current_financial_year,5,4);
                $pde =  $this->session->userdata['pdeid'];



                #$data['active_procurements']['page_list'] = $this->bid_invitation_m->get_active_invitation_for_bids($from, $to, $pde );

				$data['active_procurements'] = $this-> Proc_m -> fetch_active_procurement_list($idx=0,$data);




				#print_r($data['active_procurements']);
				#exit();

                if(isset($data['editbeb']) && !empty($data['editbeb']))
                {
                    

                    $bebid = mysql_real_escape_string(base64_decode($data['editbeb']));
                    $this->session->set_userdata(array('bebid'=>$bebid));
                    $this->db->query('SET SQL_BIG_SELECTS=1');
                    $query = $this->Query_reader->get_query_by_code('search_procurement_by_beb', array('SEARCHSTRING' => ' WHERE  1=1  and 	bestevaluatedbidder.id = '.$bebid.' ','limittext' => ''));

                  #  print_r($query);
                  #  exit();



                    //print_array($query); exit();
                    $result = $this->db->query($query)->result_array();
					//$data['active_procurements']['page_list']
                    #fetch Procurement Ref No :: --
                    $data['bidid'] = $data['procurement_refno'] = $result[0]['bid_id'];
                    $data['bebid'] = $bebid;
                    #$data['editbeb'] = $bebid;
                    #print_r($data['procurement_refno']); exit();

                }


                #print_r($data['active_procurements']);

                $pdeid =  $this->session->userdata['pdeid'];

                #fetch IFB Financial Years
                $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
                $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();


                if(!empty($data['financial_year']))
                {
                    $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

                }
                else
                {
                    $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

                }



                $data['page_title'] = 'Select procurement ';
                $data['level'] = $status;
                $data['current_menu'] = 'select_beb';
                $data['view_to_load'] = 'bids/overview3';
                // $data['view_to_load'] = 'bids/publish_bidder_v';
                $data['view_data']['form_title'] = $data['page_title'];
                $this->load->view('dashboard_v', $data);

                break;


            default:
                # code...
                break;
        }




    }

	
	#add new Receipt to a given Procurment
    #add new Receipt to a given Procurment
    #add new Receipt to a given Procurment
    function disposal_publish_bidder(){

        $status = $this->uri->segment(3);

        //unsertting editbeb session
        $urldata = $this->uri->uri_to_assoc(4, array('m'));
        $data = assign_to_data($urldata);

        switch ($status) {
            case 'publish_bidder':

                $pde =  $this->session->userdata('pdeid');
                $userid =  $this->session->userdata('userid');

                $searchstring = " and disposal_bid_invitation.id=".mysql_real_escape_string($_POST['disposalbid_id'])."  and  users.userid=".$userid."  and users.pde=".$pde."  ";
                $data['disposal_plans_details'] = $this -> disposal -> fetch_disposal_details($data,$searchstring,1);


                #get bid information
                #$data['bidinformation'] = $this-> Receipts_m -> fetch_bid_information($bidid);

                #get receipts information
                #$data['receiptinfo'] = $this-> Receipts_m -> fetchreceipts($bidid);

                #count bids
                $data['localbids'] = $this-> Receipts_m -> count_bids_disposal('uganda',$_POST['disposalbid_id']);
                $data['foreignbids'] = $this-> Receipts_m -> count_bids_disposal('Foreign',$_POST['disposalbid_id']);
                #fetch evaluation methods

                $data['evaluation_methods'] = $this-> Evaluation_methods_m -> disposalfetchevaluationmethods();
                #fetch providers
                $data['providerslist'] =   $this-> Receipts_m -> fetch_disposal_receipts( $_POST['disposalbid_id']);
                //data['providerslist'] = $this-> Receipts_m -> fetchproviders($bidid);
                # $data['unsuccesful_bidders'] =   $this-> Receipts_m -> fetch_unsuccesful_bidders(0,$bidid);

                # Pick all assigned data

                $var = $this->session->userdata;
                if(isset($var['editbeb_disposal']) && !empty($var['editbeb_disposal']))
                {
                    $receiptid = $var['editbeb_disposal'];
                    $data['bebresult'] = $this -> db -> query("SELECT * FROM bestevaluatedbidder_disposal WHERE pid=".$receiptid)-> result_array();
                    $data['formtype']='editbeb';
                  #  print_r($data['bebresult']);
                   # exit();
                }


                # $data['lots'] = $this-> Receipts_m->fetchlots($_POST['procurementrefno']);

                $data['bidid'] = $_POST['disposalbid_id'];
                $data['page_title'] = 'Publish Best Evaluated Bidder ';
                $data['level'] = $status;
                $data['view_to_load'] = 'disposal/disposal_bidder_v';
                $this->load->view( $data['view_to_load'], $data);
                break;



            case 'view_bidders_list':
                # code...
                #check user access
                #check_user_access($this, 'view_bidders_list', 'redirect');

                $bid_id = $this->uri->segment(4);
                $procurement_ref_no = base64_decode($this->uri->segment(5));
                $data['page_title'] = $procurement_ref_no;
                $data['bidderslist'] = $this-> Receipts_m -> fetctpdereceipts($bid_id);
                #print_r($data['bidderslist']); exit();
                $data['level'] = $status;
                break;




            case 'active_procurements':

                # check_user_access($this, 'active_procurements', 'redirect');

                $urldata = $this->uri->uri_to_assoc(4, array('m'));
                $data = assign_to_data($urldata);

                //$var = $this->session->userdata;
                if(!isset($data['editbeb']) || empty($data['editbeb']))	{
                    $this->session->unset_userdata('bebid');
                    #exit("Interesting .... ");
                }

                # Pick all assigned data
                $data = assign_to_data($urldata);
                $data = add_msg_if_any($this, $data);
                $data = handle_redirected_msgs($this, $data);
                $data['active_procurements'] = $this-> Proc_m -> fetch_active_procurement_list($idx=0,$data);

                if(isset($data['editbeb']) && !empty($data['editbeb']))
                {
                    $bebid = mysql_real_escape_string(base64_decode($data['editbeb']));
                    $this->session->set_userdata(array('bebid'=>$bebid));

                    $query = $this->Query_reader->get_query_by_code('search_procurement_by_beb', array('SEARCHSTRING' => ' WHERE  1=1  and 	bestevaluatedbidder.id = '.$bebid.' ','limittext' => ''));

                    #print_r($query); exit();
                    $result = $this->db->query($query)->result_array();
                    #fetch Procurement Ref No :: --
                    $data['procurement_refno'] = $result[0]['procurement_ref_no'];
                    #print_r($data['procurement_refno']); exit();

                }





                $data['page_title'] = 'Select procurement ';
                $data['level'] = $status;
                $data['current_menu'] = 'select_beb';
                $data['view_to_load'] = 'bids/overview3';
                // $data['view_to_load'] = 'bids/publish_bidder_v';
                $data['view_data']['form_title'] = $data['page_title'];
                $this->load->view('dashboard_v', $data);
                break;


            default:
                # code...
                break;
        }


    }



    #Manage PDES
    function view_published_bids()
    {
        //access_control($this, array('admin'));

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        # FETCH ACTIVE AND INACTIVE PDES
        $data  = $this-> pde_m -> fetch_pdes('in',$data);
        // = $this-> pde_m -> fetch_pdes('out',$data);
        // $this->load->view('pde/manage_pda_v',$data);

        #Get the paginated list of users
        //$query = $this->Query_reader->get_query_by_code('fetchpdes', array('STATUS' => $status ,'ORDERBY' => 'ORDER BY   pdeid','searchstring'=>'','LIMITTEXT'=>'LIMIT 10') );

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

        $data['page_title'] = 'Manage PDE\'s';
        $data['current_menu'] = 'manage_pdes';
        $data['view_to_load'] = 'pde/manage_pda_v';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);


    }



    #manage Receipts
    function manage_receipts(){

        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        $data['page_title'] = 'Manage Receipts';
        $data['current_menu'] = 'manage_bid_receipts';
        $data['view_to_load'] = 'receipts/manage_receipts_v';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }

  
    #function to save receipits
    function savebeb(){
        //call model
        //print_array($_POST);exit;
        #print_r('bidere');
        $post = $_POST;
        $beb = $post['bebname'];
        $btnstatus = $post['btnstatus'];

        #check to see if the beb exists
        if($beb <= 0)
        {
            print_r("Select Best Evaluated Bidder");
            exit();
        }

        #check if to view or to save ::
        switch($btnstatus)
        {
            case 'view':
                #	$result = $this-> Receipts_m -> publishbeb($post);
                print_r("3:View Mode Not Yet Implemented ");
                break;
            default:
                   $record = $this->session->userdata;
                
                $result = $this-> Receipts_m -> publishbeb($post);
                	
                if((isset($record['bebid'] )) && (!empty($record['bebid'])))
					{
						if($result == 1){
						   log_action('update','BEB  Record Updated  Successfully   ', ' A BEB has been updated on reference number   '.$post['procurementrefno'] );  
						}
    
					}
					else
					{
						if($result == 1){
						   log_action('create','BEB   Record Created  Successfully   ', ' A BEB has beem created successfully on reference number   '.$post['procurementrefno'] );  
						}
   
					}
                
                
              
				//echo $this->db->last_query();
                print_r($result);
                break;

        }




    }


  function ajax_fetch_procurement_details()
    {
        $post = $_POST;
        //	print_r($post); exit();
        $bidid = $post['bidid'];
        $data['procurementdetails'] = $this-> Proc_m ->fetch_annual_procurement_plan($bidid);
        $data['datearea'] = 'procurementdetails';
        $this->load->view('bids/bids_addons', $data);
        // print_r($data['procurementdetails']);
    }



    function loadprocurementrefno()
    {
        if((!empty( $_POST['proc_id'])) &&( $_POST['proc_id'] > 0 ))
        {
            #print_r($_POST); exit();


            $data = array('ID' => $_POST['proc_id'] );
            if(!empty($_POST['branch_shortcode']) && $_POST['branch_shortcode'] != '')
            {
                $data['BRANCHCODE'] =mysql_real_escape_string($_POST['branch_shortcode']).'/';
            }
            else
            {
                $data['BRANCHCODE'] = '';
            }
            //create_procurementref_no
            $query = $this->Query_reader->get_query_by_code('create_procurementref_no',  $data);
            # print_r($query); exit();
            $result = $this->db->query($query)->result_array();
            if(!empty($result)){
                print_r($result[0]['concateddate']);
                #print_r($_POST);
            }
            else
            {
                echo "0";
            }

        }
    }



    function search_refno(){
        #print_r($_POST);
        $refno = mysql_real_escape_string($_POST['refno']);
        $pdeid = $this->session->userdata('pdeid');

        $str = "SELECT BI.* FROM      bidinvitations BI     INNER JOIN procurement_plan_entries PPE   ON BI.procurement_id = PPE.id   INNER JOIN procurement_plans PP   ON PPE.procurement_plan_id = PP.id     WHERE  BI.isactive = 'Y'  ".

            " AND BI.procurement_ref_no LIKE '%".$refno."'".
            " AND PP.isactive= 'Y' ".
            " AND PPE.isactive = 'Y' AND  PP.pde_id = ".$pdeid."";


        # print_r($str);
        $result = $this->db->query($str)->result_array();
        if(!empty($result))
        {
            echo "1";
        }
        else
        {
            echo "0";
        }
    }




    function fetchunawardedlots()
    {
        #print_r($_POST);
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        $bidid = mysql_real_escape_string($_POST['bidid']);
        # print_r($bidid);
        # exit();
        #$searchstring = '    receipts.bid_id='.$bidid.' AND receipts.beb != "Y"';
        #$query = $this->Query_reader->get_query_by_code('fetch_bebs_to_lots');

        $lotdetails  =  $this->db->query(" SELECT *,IF(receipts.providerid > 0,receipts.providerid,joint_venture.providers) AS providers  FROM receipts LEFT OUTER JOIN joint_venture ON receipts.joint_venture = joint_venture.jv   INNER JOIN received_lots ON received_lots.receiptid = receipts.receiptid    INNER JOIN lots ON received_lots.lotid = lots.id   WHERE receipts.bid_id = $bidid AND beb = 'Y'")->result_array();



        $st = ' <table class="table table-stripped " style="width:50%;">';
        $st .= '<tr><th>Lot title</th>	<th> BEB </th> </tr>';
        foreach ($lotdetails as $key => $record) {
            # code...
            $query = $this->db->query("select * from providers where providerid in(".rtrim($record['providers'],',').")")->result_array();

            $st .= '<tr><td>'.$record['lot_title'].'</td>'.
                '<td> '.$query[0]['providernames'].' </td> </tr>';
        }
        $st .='</table>';
        echo $st;

        exit();
    }  

#Special procurements --------------------------------------









}
?>
