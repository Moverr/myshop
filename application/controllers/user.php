<?php 

#**************************************************************************************
# All user functions are passed through this controller
#**************************************************************************************

class User extends CI_Controller {
	
	# Constructor
	function User() 
	{	
		parent::__construct();	


		

		
		$this->load->library('form_validation'); 
		$this->load->model('users_m','user1');
		$this->load->model('_shop_m','shop');


		

		$this->load->model('sys_email','sysemail');
		$this->load->model('file_upload','libfileobj');

		

		$this->load->model('sys_file','sysfile');
		$this->load->model('Proc_m');
		
		date_default_timezone_set(SYS_TIMEZONE);
		
		access_control($this);
	}
		
	# Default to nothing
	function index()
	{
		#Do nothing
	}	
	
	#User dashboard
	function dashboard()
	{		

	# Get the passed details into the url data array if any
	$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		
	# Pick all assigned data
	$data = assign_to_data($urldata);
		
	#Get the paginated list of the news items
	$data = add_msg_if_any($this, $data);
		
	$current_financial_year = $current_financial_year = currentyear.'-'.endyear;
 

 
		
 
	$data['current_financial_year'] = $current_financial_year;
		
		
	$data['num_of_pdes'] =  "";
		
	$data['total_procurement_records'] =  "";

		
	$data['plans_submitted'] =  "";
 
		
	$data['ifbs_submitted'] = "";
	 
	
	$active_bebs = "";
	 
		
	$canceled_bebs = "";
	 
		
	$achived_bebs = "";
	 

	$total_bebs = "";
	 

	$data['bebs_published'] = "";	


	$data['contracts_awarded'] ="";
	$count_special_procurements =   "";
			
			
	$searchstr = '';

  
  


		 
	$data['page_title'] = 'Dashboard';
	$data['current_menu'] = 'dashboard';
	$data['incl_to_load'] = 'dashboard';
	#$data['view_to_load'] = 'admin/overview2';
	$data['view_to_load'] = 'admin/overview';

 		
	$this->load->view('dashboard_v', $data);
	}	
	
	
	# function to load dashboard stats
	function load_dashboard_stats()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
        				$current_financial_year = currentyear.'-'.endyear;
	
		if(!empty($_POST['financial_year']))
		{
				$current_financial_year = $financial_year = $_POST['financial_year'];
					
				$search_str = ' PP.financial_year = "'. $financial_year .'" ';
				
				$search_str = ' PP.financial_year = "'. $current_financial_year .'" ';
		$financial_searchstring = ' AND procurement_plans.isactive ="Y" ';
		
		if($this->session->userdata('isadmin') == 'N')
		{
			$userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array(); 
			$search_str .=  ' AND PP.pde_id ="'. $userdetails[0]['pde'] .'"';
			$financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$userdetails[0]['pde'];
			
		}
		
			$search_str .= "  AND PP.financial_year like '".$current_financial_year."' ";
		
		
		/*
		#Get the paginated list of bid invitations
		$data = paginate_list($this, $data, 'procurement_plan_details_advanced', array('orderby'=>'bid_dateadded DESC', 'orderby'=>' procurement_plan_entries.dateadded ', 'searchstring'=>$app_select_str)); */
		$data['current_financial_year'] = $current_financial_year;
		
		
		$data['num_of_pdes'] = count($this->db->get_where('pdes', array('status'=>'IN', 'isactive'=>'Y'))->result_array());
		
		$data['total_procurement_records'] = end($this->Query_reader->get_row_as_array('count_procurement_records', array('searchstring'=>$search_str)));

		
		$data['plans_submitted'] = end($this->Query_reader->get_row_as_array('count_plans_submitted', array('searchstring'=>$search_str)));
		
		$data['ifbs_submitted'] = end($this->Query_reader->get_row_as_array('count_ifbs_published', array('searchstring'=>$search_str)));
		

		/*
		BEB Calculation for counts
		*/

			$active_bebs = $this-> Proc_m -> count_beb_list(0,array('level' => 'active','current_financial_year'=>$current_financial_year));
		    $canceled_bebs = $this-> Proc_m -> count_beb_list(0,array('level' => 'canceled','current_financial_year'=>$current_financial_year));
		
			$achived_bebs = $this-> Proc_m -> count_beb_list(0,array('level' => 'archive','current_financial_year'=>$current_financial_year));

			$total_bebs = $active_bebs +  $canceled_bebs + $achived_bebs;

		#print_r($active_bebs);

		/*
		End 
		*/
		#exit($this->db->last_query()
		$data['bebs_published'] = $total_bebs;
		//end($this->Query_reader->get_row_as_array('count_bebs_published', array('searchstring'=>$search_str)));
		
			
				/*Contracts Awarded Plus Special Procurement Contracts 
				 */
				 $count_special_procurements =  end($this->Query_reader->get_row_as_array('count_special_procurements', array('searchstring'=>$search_str)));			
		        
			
			
					$searchstr = '';

					if($this->session->userdata('isadmin') == 'N')
					{
						$userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
						$searchstr = ' AND PP.pde_id="'. $userdata[0]['pde'] .'"';
					}
		 
					$pdeid =  $this->session->userdata['pdeid'];

					#fetch IFB Financial Years
					$financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;       
					$data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();
					

					
				
					$searchstr .= ' AND PP.financial_year like "%'.$current_financial_year.'%"';
				
        
			     $count_contracts = paginate_list($this, $data, 'count_contracts', array('orderby'=>'C.dateadded DESC', 'searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $searchstr));
		         $data['count_contracts']  = $count_contracts['page_list'][0]['num_contracts'];		         
		   
				 $data['contracts_awarded'] = $count_special_procurements +  $data['count_contracts'] ;
				 
				 /*
				  */  
		}
						
				$this->load->view('includes/dashboard_stats', $data);
	}
	
        
    #New user form
	function load_user_form()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		#check user access
		if(!empty($data['i']))
		{
			check_user_access($this, 'edit_user_details', 'redirect');
		}
		else
		{
			check_user_access($this, 'add_users', 'redirect');
		}		
                
        #Get access groups                
        $data['usergroups'] = $this->db->query($this->Query_reader->get_query_by_code('get_user_group_list',array('searchstring'=>'UG.isactive="Y" '.($this->session->userdata('isadmin') == 'N'? ' AND UG.id != 14  AND UG.groupname not like "%PPDA Administrator%" AND UG.id != 26 ' : ''), 'orderby'=>'ORDER BY UG.groupname', 'limittext'=>'')))->result_array();
		
                
	 

         //manage_shops_v
        $data['limit'] = 1000000;
 		$data['shops'] = $this->shop->get_shops($data);

 		 





				
        #user is editing
		if(!empty($data['i']))
		{
			$userid = decryptValue($data['i']);
			$data['formdata'] = $this->Query_reader->get_row_as_array('get_user_by_id', array('id'=>$userid ));
			
			#get the user's roles
			$user_roles = $this->db->query($this->Query_reader->get_query_by_code('get_roles_by_user',array('userid'=>$userid)))->result_array();
			
			#format role IDs into simple array
			$data['formdata']['roles'] = array();
			
			foreach($user_roles as $user_role)
			{
				array_push($data['formdata']['roles'], $user_role['groupid']);
			}
			
            
			#If the user is to be reactivated
			if(!empty($data['a']) && decryptValue($data['a']) == 'reactivate' && $this->session->userdata('isadmin') == 'Y')
			{
				$result = $this->db->query($this->Query_reader->get_query_by_code('reactivate_user', array('id'=>$userid)));
				if($result)
				{
					$send_result = $this->sysemail->email_form_data(array('fromemail'=>NOREPLY_EMAIL), 
							get_confirmation_messages($this, $data['userdetails'], 'account_reactivated_notice'));
				}
				else
				{
					$data['msg'] = "ERROR: There was an error activating the user.";
				}
			}
			
                        
            #Check if the user is simply viewing
            if(!empty($data['a']) && decryptValue($data['a']) == 'view')
            {
                $data['isview'] = "Y";
                           
                #get the access group name
                $data['access_group_info'] = $this->Query_reader->get_row_as_array('get_group_by_id', array('groupid'=> $data['userdetails']['accessgroup'] ));
            }
		}


	

			if($this->session->userdata('isadmin') == 'N')
			{
				$userid = $this->session->userdata('userid');
				$shopid = $this->session->userdata('shopid');


				//Get me the branches that the person is trying to add user ::
				$data['shop_branches'] = $this->db->query($this->Query_reader->get_query_by_code('get_shop_branches', array('searchstring'=>" AND S.id = ".$shopid." AND S.isactive = 'Y' ")))->result_array();

			 	 

			}

				 




			
		
		$data['page_title'] = (!empty($data['i'])? 'Edit user details' : 'Add user');
		$data['current_menu'] = 'add_users';
		$data['view_to_load'] = 'users/user_form_v';
		$data['view_data']['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}
	
	
	#function to show a user their profile page
	function profile_form()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		# Get the user's current details
		$data['formdata'] = $this->Query_reader->get_row_as_array('get_user_by_id', array('id'=>$this->session->userdata('userid')));
		
		$usergroup = end($this->db->get_where('usergroups', array('isactive'=>'Y', 'id'=>$this->session->userdata('usergroup')))->result_array());
		
		$data['formdata']['usergroup'] = (($usergroup['groupname'])? $usergroup['groupname'] : '<i>undefined</i>' );
		
		$data['page_title'] = 'My Profile';
		$data['current_menu'] = '';
		$data['view_to_load'] = 'users/profile_form_v';
		$data['view_data']['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}
	
	
    #Save user profile
	function save_profile()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a', 't'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);		
		
		$userid = $this->session->userdata('userid');
				
		if($this->input->post('save'))
		{
			$data['formdata'] = $_POST;		
            $required_fields = array('firstname', 'lastname', 'gender', 'emailaddress*EMAILFORMAT', 'telephone', 'username');
			$_POST = clean_form_data($_POST);
			$validation_results = validate_form('', $_POST, $required_fields);
                                    
			
			#Only proceed if the validation for required fields passes
			if($validation_results['bool'])
			{           
                if(!empty($userid))
                {
					$data['msg'] = '';
					
					#test if email is unique to user being edited
					$user_details = $this->Query_reader->get_row_as_array('search_user_list', array('searchstring'=>'emailaddress="'. $_POST['emailaddress'] .'" AND userid != "'. $userid.'"', 'limittext'=>''));
					
					if(!empty($user_details))
					{
						$data['msg'] = "ERROR: A user with the specified email address already exists. <br />";
					}
					
					
                    if(!empty($_POST['password']) || !empty($_POST['repeatpassword']))
                    {   
                        $passwordmsg = $this->user1->check_password_strength($_POST['password']);
                        if(!$passwordmsg['bool'])
                        {
                            $data['msg'] .= "ERROR: " . $passwordmsg['msg'];
                        }
                        elseif($_POST['password'] == $_POST['repeatpassword'])
						{
							$update_string = ", password = '".sha1($_POST['password'])."'";
						}
						else
						{
							$data['msg'] .= "ERROR: The passwords provided do not match.";
						}
					}
					else
					{
						$update_string = "";
					}
				
					if(empty($data['msg'])){
						$result = $this->db->query($this->Query_reader->get_query_by_code('update_user_profile', array_merge($_POST, array('updatecond'=>$update_string, 'editid'=>$userid))));
					}
           	  	} 
				
           		#Format and send the errors
            	if(!empty($result) && $result)
				{
					$this->session->set_userdata('firstname', $_POST['firstname']);
					
					$this->session->set_userdata('lastname', $_POST['lastname']);
					
					$this->session->set_userdata('usave', "Your profile details have been successfully saved");
					
					redirect("user/dashboard/m/usave");
            	 }
            	 else if(empty($data['msg']))
            	 {
				   	$data['msg'] = "ERROR: Your profile details could not be saved or were not saved correctly.";
             	 }
            }
            
			             
            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) 
			&& empty($data['msg']) )
			{
				$data['msg'] = "WARNING: The highlighted fields are required.";
			}
			
			$data['requiredfields'] = $validation_results['requiredfields'];
		}		
		
		$data['page_title'] = 'My Profile';
		$data['current_menu'] = '';
		$data['view_to_load'] = 'users/profile_form_v';
		$data['view_data']['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}	
}
?>
