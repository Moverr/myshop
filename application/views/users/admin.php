<?php 
ob_start();

#*********************************************************************************
# All users have to first hit this class before proceeding to whatever section 
# they are going to.
# 
# It contains the login and other access control functions.
#*********************************************************************************

class Admin extends CI_Controller {
	
	# Constructor
	function Admin() 
	{	
		//**********  Back button will not work, after logout  **********//
			header("cache-Control: no-store, no-cache, must-revalidate");
			header("cache-Control: post-check=0, pre-check=0", false);
			// HTTP/1.0
			header("Pragma: no-cache");
			// Date in the past
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			// always modified
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
		//**********  Back button will not work, after logout  **********//
		
		parent::__construct();	
		$this->load->library('form_validation'); 
		$this->load->model('users_m','user1');
		$this->load->model('sys_email','sysemail');
		$this->session->set_userdata('page_title','Login');
		date_default_timezone_set(SYS_TIMEZONE);
		$data = array();

		#MOVER LOADED MODELS
        $this->load->model('pde_m');
        $this->load->model('Pdetypes_m');
        $this->load->model('Usergroups_m');	
         $this->load->model('Remoteapi_m');  	
	}
	
	
	#Default to login 
	function index()
	{
		redirect('page/home');
	}
	 function updatelist()
    {
        //central government
        $q = mysql_query("SELECT pde_name,abbreviation,category from pdess where pde_id > 30");
        while ($row = mysql_fetch_array($q)) {
            # code...
         
            $query = mysql_query("INSERT INTO pdes(pdename,abbreviation,category)values('".$row['pde_name']."','".$row['abbreviation']."','".$row['category']."')") or die("".mysql_error());
        }

    }

	
	#Handles login functionality
	function login()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		# If user has clicked login button
		if($this->input->post('login'))
		{
			$required_fields = array('acadusername', 'acadpassword');
			$_POST = clean_form_data($_POST);
			#print_r($_POST);
			$validation_results = validate_form('', $_POST, $required_fields);
			$username = trim($this->input->post('acadusername'));
			$password = trim($this->input->post('acadpassword'));
			
			# Enters here if there were no errors during validation.
			if($validation_results['bool'])
			{
				# Run the login details against the user's details stored in the database
				# Returns an array with the user details
				$chk_user = $this->user1->validate_login_user(array('emailaddress'=>$username, 'password'=>sha1($password)));				
				# No matching user details
				if(count($chk_user) == 0)
				{
					$data['msg'] = "WARNING: <b>Please re-enter your password.</b><br><br>The password entered is incorrect. Please try again (make sure your caps lock is off).";
					$this->user1->log_access_trail(replace_bad_chars($username), 'Fail');
				}
				else if(count($chk_user) > 0)	
				{ 
					# add session attributes
					# get the user id from the query results, since this is the unique ID for
					# the user
					$userdetails['userid'] = $chk_user[0]['userid'];
					$userdetails['username'] = $chk_user[0]['username'];
					$userdetails['isadmin'] = (!empty($chk_user[0]['groupid']) && $chk_user[0]['groupid'] == 14? 'Y' : 'N');
					$userdetails['pdeid'] = $chk_user[0]['pde'];
					#$userdetails['usertype'] = $chk_user[0]['usertype'];	
					$userdetails['usergroup'] = $chk_user[0]['groupid'];
					$userdetails['usergroupname'] = $chk_user[0]['groupname'];
					$userdetails['emailaddress'] = $chk_user[0]['emailaddress'];
					$userdetails['names'] = $chk_user[0]['firstname']." ".$chk_user[0]['middlename']." ".$chk_user[0]['lastname'];
					$userdetails['firstname'] = $chk_user[0]['firstname'];
					$userdetails['lastname'] = $chk_user[0]['lastname'];
					$userdetails['photo'] = $chk_user[0]['photo'];
															

					
					#print_r($userdetails);
					
					$this->session->set_userdata($userdetails);
					$this->session->set_userdata('alluserdata', $userdetails);
					setcookie("loggedin","true", time()+$this->config->item('sess_time_to_update'));
					
					#Determine if the user needs to change the password, then overide the redirection to the dashboard
					if(!empty($userdetails['changedpassword']) && $userdetails['changedpassword'] == "N")
					{
						redirect('admin/change_password');
					}
					else
					{
						#Persist user details if specified "remember me" for future login
						if(!empty($_POST['rememberme']))
						{
							#Create cookie for the user details
							if(SECURE_MODE){
								/*
								* setcookie() variables
								* -----------------------
								* name		#Cookie Name
								* value		#Cookie value
								* expire	#Keep active for only 1 week (7 x 24 x 60 x 60 seconds)
								* domain	#Domain
								* secure	#Whether it requires to be secure cookie - set if operating in secure mode (with HTTPS)
								*/
								setcookie(
									get_user_cookie_name($this), 
									encryptValue($this->session->userdata('username')."||".sha1($password)), 
									time() + 604800,  
									".".$_SERVER['HTTP_HOST'],
									TRUE 
								);
							}
							else
							{
								setcookie(
									get_user_cookie_name($this), 
									encryptValue($this->session->userdata('username')."||".sha1($password)),
									time() + 604800,
									".".$_SERVER['HTTP_HOST']
								);
							}
						}

						log_action('Log in','User log in','User successfully started session');
						
						redirect('admin/load_dashboard');
					}
				}#check user
			
				
			}
			#There were errors during validation
			else
			{
				$data['msg'] = "WARNING: Please enter the fields highlighted to continue.";

			}
			
			$data['formdata'] = $_POST;
			$data['requiredfields'] = $validation_results['requiredfields'];
		}
		
		
		$data = add_msg_if_any($this, $data);
		
		$data['view_to_load'] = 'public/includes/login';
		$this->load->view('public/home_v', $data);
	}
	
	
	# Shows the user's relevant dashboard with necessary infomation
	function load_dashboard()
	{	
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'x'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['m'])){
			$addn = "/m/".$data['m'];
		} else {
			$addn = "";
		}
		
		#Unset navigation session settings
		$this->session->unset_userdata(array('from_search_results'=>''));
		
		
		#checks if the user's session expired
		if($this->session->userdata('userid') || ($this->input->cookie('loggedin') && $this->input->cookie('loggedin') == 'true' && empty($data['x'])))
    	{
        	if($this->session->userdata('fwdurl')){exit($this->session->userdata('fwdurl'));
				redirect($this->session->userdata('fwdurl'));
			}
			else
			{
				redirect($this->user1->get_dashboard().$addn);
			}
   		}
		else 
		{
        	setcookie("loggedin","false", time()+$this->config->item('sess_time_to_update'));
			#Consider passing on some messages even if the user is automatically logged out.
			if(!empty($data['m']) && in_array($data['m'], array('nmsg')))
			{
				$url = base_url().'admin/logout'.$addn;
			}
			else
			{
				$this->session->set_userdata('exp', 'Your session has expired.');
				$url = base_url().'admin/logout/m/exp';
			}
			
			redirect($url);
		}	
	}
	
	
	#Shows the user's relevant settings page
	function get_settings()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['m'])){
			$addn = "/m/".$data['m'];
		} else {
			$addn = "";
		}
		
		redirect($this->user1->get_settings_page().$addn);
	}
		
	
	# Shows the admin dashboard
	function dashboard()
	{	
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['au']) && decryptValue($data['au']) == 'true')
		$data['adduser'] = 'true';
		
		#Get the paginated list of the schools
		$data = paginate_list($this, $data, 'search_schools_list', array('isactive'=>'Y', 'searchstring'=>' AND isactive ="Y"'));
		
		$data = add_msg_if_any($this, $data);
		$this->load->view('admin/admin_dashboard_view', $data);
	}	
	
	#Function to show when the user forgot their password
	function forgot_password()
	{
		$this->load->model('sys_email','sysemail');
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if($this->input->post('sendpassword'))
		{
			$required_fields = array('emailaddress*EMAILFORMAT');
			
			$validation_results = validate_form('', $_POST, $required_fields);
			
			#validate the passed email address before sending a new password
			if($validation_results['bool'])
			{
				$user_details = $this->Query_reader->get_row_as_array('get_user_by_email', array('emailaddress'=>$_POST['emailaddress'], 'isactive'=>'Y'));
				
				if(!empty($user_details))
				{
					$new_pass = generate_standard_password();
					$update_result = $this->db->query($this->Query_reader->get_query_by_code('update_user_password', array('emailaddress'=>$_POST['emailaddress'], 'newpass'=>sha1($new_pass) )));
					
					if($update_result)
					{
						#Send a welcome message to the user's email address
						$send_result = $this->sysemail->email_form_data(array('fromemail'=>SITE_ADMIN_MAIL), 
							get_confirmation_messages($this, array('emailaddress'=>$_POST['emailaddress'], 'newpass'=>$new_pass, 'firstname'=>$user_details['firstname']), 'changed_password_notify'));
					}


					$msg = (!empty($send_result) && $send_result)? "A new password has been sent to your email address.": "ERROR: The new password could not be sent. Please contact our support team by phone for help.";
					
					$this->session->set_userdata('sres',$msg);
					redirect(base_url()."admin/login/m/sres");
				}
				else
				{
					$data['msg'] = "WARNING: The emailaddress provided does not match any active user on the system.";
				}
			}
			else
			{
				$data['msg'] = "WARNING: The highlighted fields are required.";
			}
			
			$data['requiredfields'] = $validation_results['requiredfields'];
			$data['formdata'] = $_POST;	
		}
		
		$data = add_msg_if_any($this, $data);
		$this->load->view('admin/forgot_password_view', $data);
	}
	
	
	
	# Clears the current user's session and redirects to the login page
	function logout()
	{	
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		log_action('Log out','User logged out','User successfully ended session');

		

		$this->session->set_userdata('lmsg', 'You have logged out.');
		
		#Clear/reset tracking cookies if present
		setcookie(get_user_cookie_name($this), "", time()+0);
		setcookie("loggedin","false", time()+$this->config->item('sess_time_to_update'));
		
		# Clear key session variables
		$this->session->unset_userdata(array(
			'alluserdata'=>'',
			'isadmin'=>'',
			'trackerids'=>'',
			'fwdurl'=>'',
			'userid'=>'',
			'isadmin'=>''
			));
		
		if(empty($data['m'])){
			$data['m'] = "lmsg";
		}


	   $this->session->sess_destroy();
		redirect(base_url().'admin/login/m/'.$data['m']);
	}
	
	
	
	#Change Password before you proceed.
	function change_password()
	{
		access_control($this);
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if($this->input->post('updatepw'))
		{
			$required_fields = array('oldpassword', 'newpassword', 'repeatpassword*SAME<>newpassword');
			$validation_results = validate_form('', $_POST, $required_fields);
			$data['passwordmsg'] = $this->user1->check_password_strength($_POST['newpassword']);
			$pwstrength = 5 - $data['passwordmsg']['strikecount'];
			
			#Get the user details and compare with the entered password details
			$old_user_details = $this->Query_reader->get_row_as_array('user_login', array('username'=>$this->session->userdata('username'), 'password'=>sha1($_POST['oldpassword']) ));
			
			
			#Only proceed if the validation for required fields passes
			if(!empty($old_user_details) && $validation_results['bool'] && $pwstrength > 3)
			{
				$updateresult = $this->db->query($this->Query_reader->get_query_by_code('update_user_password', array('newpass'=>sha1($_POST['newpassword']), 'emailaddress'=>$this->session->userdata('emailaddress')) ));
				$flagupdateresult = $this->db->query($this->Query_reader->get_query_by_code('update_user_changedpassword_flag', array('flagvalue'=>'Y', 'emailaddress'=>$this->session->userdata('emailaddress')) ));
				
				
				if($updateresult && $flagupdateresult)
				{
					#Notify user of password change
					$send_result = $this->sysemail->email_form_data(array('fromemail'=>SECURITY_EMAIL), 
							get_confirmation_messages($this, array('emailaddress'=>$this->session->userdata('emailaddress'), 'firstname'=>$this->session->userdata('firstname')), 'password_change_notice'));


					log_action('update','Password update','User successfully updated their password');
					$this->session->set_userdata('changedpassword', 'Y');
					$this->session->set_userdata('umsg', 'Your password has been updated');
					redirect('admin/logout/m/umsg');
				}
				else
				{
					$data['msg'] = "ERROR: There were errors updating your password. <BR>Please contact the administrator.";
				}
			}
			else if(empty($old_user_details))
			{
				$data['msg'] = "WARNING: The password entered does not match the old password.";
			}
			else if(!$validation_results['bool'])
			{
				$data['msg'] = "WARNING: The highlighted fields are required.";
			}
			else 
			{
				$data['msg'] = "WARNING: The password strength is low. Please update the password based on the instructions given.";
			}
			$data['requiredfields'] = $validation_results['requiredfields'];
			$data['formdata'] = $_POST;
		}
		
		$data = add_msg_if_any($this, $data);
		$this->load->view('account/change_password', $data);
	}
	
	
	
	
	#Check password for strength
	function password_strength()
	{
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['newpassword']))
		{
			$data['passwordmsg'] = $this->user1->check_password_strength($data['newpassword']);
		}
		$data['area'] = "show_password_strength";
		$data = add_msg_if_any($this, $data);
		$this->load->view('incl/addons', $data);
	}
	function updatepassword(){
      $this->load->library('email');
	 $newpassword = sha1($_POST['newpassword']);
	 $accountid = base64_decode($_POST['accid']);
 
 // $str = "UPDATE users SET users ='".$newpassword."' WHERE userid = '".$accountid."'";
 // print_r($str); exit();
		  $query = $this->db->query("UPDATE users SET password ='".$newpassword."' WHERE userid = '".$accountid."' ");
		
		     #exit($accountid);
			//email New Password and Email :: 
			$query = $this->db->query("SELECT * FROM users WHERE userid  = '".$accountid."' ")->result_array();
		
		if(!empty($query))
		{

				//insert into 
				$userid = base64_encode($query[0]['userid']);
				$email = $query[0]['emailaddress'];
				$names = $query[0]['prefix']." ".$query[0]['firstname']." ".$query[0]['lastname'];
				$username = $query[0]['username'];

				$title = "LOGIN CREDENTIALS";
				$body = " Hello ".$names." <br/> Your Login Credentals <br/>";
				$body .="<ul> <li> User name : ".$query[0]['emailaddress']." </li> <li> New password :".$_POST['newpassword']." </li> </ul> ";
					

				$this->email->from('noreply@gpp.ppda.go.ug', ' Government Procurement Portal  Password Reset');
				$this->email->to(''.$email.'');		 

				$this->email->subject(''.$title.'');
				$this->email->message(''.$body.'');

				$this->email->send();

				echo "1";
				
				}
				else
				{
					echo "0";
				}
		

	}

	function reactivate_password(){
		$urldata = $this->uri->uri_to_assoc(3, array('accountid'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		$data['accountid'] = $data['accountid'];		
		#print_r($data);
       # print_r($data['accountid']);
       # exit();

		$data['view_to_load'] = 'public/includes/login';
		$this->load->view('public/home_v', $data);


		#echo "reached";
	}
	function forgotpassword()
	{
		$this->load->library('email');
		#print_r($_POST);
		//check user exists
		$emailaddress = trim(mysql_real_escape_string($_POST['emailaddress']));
		$query = $this->db->query("SELECT * FROM users WHERE emailaddress like '".$emailaddress."' ")->result_array();
		
		if(!empty($query))
		{

			//insert into 
		$userid = $query[0]['userid'];
		$email = $query[0]['emailaddress'];
		$names = $query[0]['firstname']." ";
		$title = "LOGIN CREDENTIALS";
		$body = " Hello ".trim($names).", <br/><br/> You have chosen to retrieve your lost password. To proceed, ";
		$body .="<a href='".base_url()."admin/reactivate_password/accountid/".base64_encode($userid)."' > click here</a>.  If you didn't request to reset your password, ignore this email. <br/><br/> Regards, <br/> GPP Support Team";
			

		$this->email->from('noreply@gpp.ppda.go.ug', 'Government Procurement Portal Password Reset ');
		$this->email->to(''.$email.'');		 

		$this->email->subject(''.$title.'');
		$this->email->message(''.$body.'');

		$this->email->send();

		echo "1";
		
		}
		else
		{
			echo "0";
		}
		 
	}
	
	
	#search through the users
	function search_users()
	{
		#check user access
		check_user_access($this, 'view_user_list', 'redirect');
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$data['usergroups'] = $this->db->get_where('usergroups', array('isactive'=>'Y'))->result_array();
		
		$search_string = '';
		
		if($this->session->userdata('isadmin') == 'N')
		{
			$userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
			$search_string = ' AND P.pdeid="'. $userdata[0]['pde'] .'"';
		}
		
		if(!empty($_GET['search']['value']))
		{
			$_POST['searchQuery'] = $_GET['search']['value'];
		}
		
		
		if($this->input->post('searchQuery'))
		{
			$_POST = clean_form_data($_POST);
			$_POST['searchQuery'] = trim($_POST['searchQuery']);
			
			$search_string .= ' AND (U.firstname like "%'. $_POST['searchQuery'] .'%" OR U.lastname like "%' . $_POST['searchQuery'] . '%"'.
							 'OR UG.groupname like "%'. $_POST['searchQuery'] .'%" OR P.pdename like "%' . $_POST['searchQuery'] . '%"'.
							 'OR U.emailaddress like "%'. $_POST['searchQuery'] .'%" OR U.usergroup like "%' . $_POST['searchQuery'] . '%"'.
							 'OR U.telephone like "%'. $_POST['searchQuery'] .'%" OR U.prefix like "%' . $_POST['searchQuery'] . '%"'.
							 ( $_POST['searchQuery'] == 'NONE'? ' OR U.usergroup = 0' : '') . ')';
			
			$data = paginate_list($this, $data, 'get_all_users', array('searchstring'=>$search_string, 'orderby'=>' ORDER BY U.firstname '),200);
			
						
		}
		else
		{
			$data = paginate_list($this, $data, 'get_all_users', array('searchstring'=>$search_string, 'orderby'=>' ORDER BY U.firstname '),200);
		}
		
		$data['area'] = 'users_list';
		
		$this->load->view('includes/add_ons', $data);
	}
	
	
	#search through the user groups
	function search_user_groups()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if($this->input->post('searchQuery'))
		{
			$_POST = clean_form_data($_POST);
			$_POST['searchQuery'] = trim($_POST['searchQuery']);
			
			$search_string = ' UG.isactive ="Y" AND UG.groupname like "%'. $_POST['searchQuery'] .
							 '%" OR users.firstname like "%'. $_POST['searchQuery'] .'%" OR users.firstname like "%'.
							 $_POST['searchQuery'] .'%"';
			
			$data = paginate_list($this, $data, 'get_user_group_list', array('searchstring'=>$search_string, 'orderby'=>'ORDER BY UG.groupname ASC'));
			
						
		}
		else
		{
			$data = paginate_list($this, $data, 'get_user_group_list', array('searchstring'=>'UG.isactive ="Y"', 'orderby'=>'ORDER BY UG.groupname ASC'));
		}
		
		$data['area'] = 'user_groups_list';
		
		$this->load->view('includes/add_ons', $data);
	}
	
        
    #Manage Users
	function manage_users()
	{
		#check user access
		check_user_access($this, 'view_user_list', 'redirect');
		
		/*
		#delete duplicate pdes
		$duplicate_pdes = $this->db->query('SELECT pdename, count(*) AS numOfPdes FROM `pdes` WHERE isactive = "Y" GROUP BY pdename HAVING count(*)>1')->result_array();
		
		foreach($duplicate_pdes as $pde)
		{
			$this->db->query('DELETE FROM pdes WHERE pdename ="'. $pde['pdename'] .'" ORDER BY pdeid LIMIT ' . ($pde['numOfPdes'] - 1));
		}
		*/
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
                
		#format user-groups
		$data['usergroups'] = $this->db->get_where('usergroups', array('isactive'=>'Y'))->result_array();
		
		$search_str = '';
		
		if($this->session->userdata('isadmin') == 'N')
		{
			$userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
			$search_str = ' AND P.pdeid="'. $userdata[0]['pde'] .'"';
		}
		
		#Get the paginated list of users
		$data = paginate_list($this, $data, 'get_all_users', array('searchstring'=>$search_str, 'orderby'=>' ORDER BY U.firstname '));
         
		$data = add_msg_if_any($this, $data);
		
		$data = handle_redirected_msgs($this, $data);
		
		$data['page_title'] = 'Manage users';
		$data['current_menu'] = 'view_user_list';
		$data['view_to_load'] = 'users/manage_users_v';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['search_url'] = 'admin/search_users';
        
		$this->load->view('dashboard_v', $data);
	}
        
    #New User Form
	function load_user_form()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
                
        #Get access groups                
        $accessGroupsResult = $this->db->query($this->Query_reader->get_query_by_code('get_user_group_list',array()));
                
        #user is editing
		if(!empty($data['i']))
		{
			$userid = decryptValue($data['i']);
			
			$data['userdetails'] = $this->Query_reader->get_row_as_array('get_user_by_id', array('id'=>$userid ));
            
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
		
		$this->load->view('user/user_form_v', $data);
	}
        
    #Save new User
	function save_user()
	{		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a', 't'));
		
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
		
		if($this->input->post('cancel'))
		{		
			redirect("admin/manage_users");
		}
		else if($this->input->post('save'))
		{
			$data['userdetails'] = $_POST;		
            $required_fields = array('firstname', 'lastname', 'gender', 'emailaddress*EMAILFORMAT', 'telephone', 'username');
			$_POST = clean_form_data($_POST);
			$validation_results = validate_form('', $_POST, $required_fields);
                                    
			
			#Only proceed if the validation for required fields passes
			if($validation_results['bool'])
			{
				#User's added by non admins have PDE of author
				if($this->session->userdata('isadmin') == 'N')
				{
					#should also NEVER try adding system admins, under any circumstances!
					if(!empty($_POST['roles']) && $_POST['roles'] == 14)
					{
						$this->session->set_userdata('usave', "ERROR: Invalid action");
						redirect("user/dashboard/m/usave");
					}
					
					$userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
					$_POST['pde'] = $userdata[0]['pde'];					
				}
				   
                if(!empty($data['i']))
                {
					$userid = decryptValue($data['i']);
					
					$data['msg'] = '';
					
					#test if email is unique to user being edited
					$user_details = $this->Query_reader->get_row_as_array('search_user_list', array('searchstring'=>'emailaddress="'. $_POST['emailaddress'] .'" AND userid != "'. $userid .'"', 'limittext'=>''));
					
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
						$result = $this->db->query($this->Query_reader->get_query_by_code('update_user_data', array_merge($_POST, array('updatecond'=>$update_string, 'editid'=>$userid))));
						
						#update the user's roles
						if(empty($_POST['roles']))
						{
							$this->db->update('roles', array('isactive'=>'N'), array('userid'=>$userid, 'isactive'=>'Y'));
						}
						else
						{
							#get the user's current roles
							$current_user_roles = $this->db->get_where('roles', array('userid'=>$userid, 'isactive'=>'Y'))->result_array();
															
							foreach($current_user_roles as $current_user_role)
							{
								if(in_array($current_user_role['groupid'], $_POST['roles']))
								{
									foreach($_POST['roles'] as $role_key=>$role_value)
									{
										if($role_value == $current_user_role['groupid'])
										{
											unset($_POST['roles'][$role_key]);
											break;
										}
									}		
								}
								else
								{
									$this->db->update('roles', array('isactive'=>'N'), array('id'=>$current_user_role['id']));
								}
							}
															
							if(!empty($_POST['roles']))
							{
								$this->db->insert('roles', array('userid'=>$userid, 'groupid'=>$_POST['roles'], 'author'=>$this->session->userdata('userid')));
							}
						}
						
						
						#echo $this->Query_reader->get_query_by_code('update_user_data', array_merge($_POST, array('updatecond'=>$update_string, 'editid'=>decryptValue($data['i']))));
						
						#exit();
					}
           	  	} 
			 
			 
			 	else 
             	{
					#check if a similar username already exists
               	 	$username_error = "";
                	$usernames = $this->db->query($this->Query_reader->get_query_by_code('get_existing_usernames', array('searchstring' => ' username = "'.$_POST['username'].'"')));

					
					#Check if adding a new user and the email added has already been used
					if(!empty($data['userdetails']['emailaddress']) && empty($data['i']))
					{
						$user_details  = $this->Query_reader->get_row_as_array('get_any_user_by_email', array('emailaddress'=>$data['userdetails']['emailaddress']));
					}
                                    
                	#determine password strength
                	$passwordmsg = $this->user1->check_password_strength($_POST['password']);
                                    
                	if(strlen($_POST['username']) < 5)
                	{
                   		$data['msg'] = "ERROR: The username must be at least 5 characters long";
						$data['errormsgs']['username'] = "The username must be at least 5 characters long";
				 		$data['requiredfields'] = array('username'); 
               		}
                	elseif(count($usernames->result_array()))
                	{
                    	$data['msg'] = "ERROR: The username is already being used by another user.";
						$data['errormsgs']['username'] = "The username is already being used by another user";
				 		$data['requiredfields'] = array('username'); 
                	}                                    
                	elseif(!$passwordmsg['bool']){
                    	$data['msg'] = "ERROR: ".$passwordmsg['msg'];
						$data['errormsgs']['password'] = $passwordmsg['msg'];
				 		$data['requiredfields'] = array('password'); 
						
                	}                                     
                	elseif($_POST['password'] == $_POST['repeatpassword'] && !empty($_POST['password']))
					{
						$result = $this->db->query($this->Query_reader->get_query_by_code('add_user_data', array_merge($_POST, array('password'=>sha1($_POST['password']), 'author'=>$this->session->userdata('userid')) )));
						#exit($this->db->last_query());
						$last_added_user = $this->db->insert_id();		
						
						#Add the user roles if specified
						if(!empty($_POST['roles']))
						{
							$this->db->insert('roles', array('userid'=>$last_added_user, 'groupid'=>$_POST['roles'], 'author'=>$this->session->userdata('userid')));
						}
					}
					else
					{
						$data['msg'] = "ERROR: The passwords provided do not match.";
						$data['errormsgs']['password'] = $data['errormsgs']['repeatpassword'] = "The passwords provided do not match.";
				 		$data['requiredfields'] = array('password', 'repeatpassword');
					}
					
        		}
				
           		#Format and send the errors
            	if(!empty($result) && $result)
				{
					#Notify user by email on creation of an account
					if(empty($data['editid']))
					{
						$send_result = $this->sysemail->email_form_data(array('fromemail'=>NOREPLY_EMAIL), 
						get_confirmation_messages($this, array('emailaddress'=>$_POST['emailaddress'], 'firstname'=>$_POST['firstname'], 'lastname'=>$_POST['lastname'], 'username'=>$_POST['username'], 'password'=>$_POST['password']), 'registration_confirm'));
					}
										
					$this->session->set_userdata('usave', "The user data has been successfully saved.");
					redirect("admin/manage_users/m/usave");
            	 }
            	 else if(empty($data['msg']))
            	 {
				   	$data['msg'] = "ERROR: The user could not be saved or was not saved correctly.";
             	 }
            }
            
			#Prepare a message in case the user already exists
			else if(empty($data['i']) && !empty($user_details))
			{
				/*
				 $addn_msg = (!empty($user_details['isactive']) && $user_details['isactive'] == 'N')? "<a href='".base_url()."admin/load_user_form/i/".encryptValue($user_details['id'])."/a/".encryptValue("reactivate")."' style='text-decoration:underline;font-size:17px;'>Click here to  activate and  edit</a>": "<a href='".base_url()."admin/load_user_form/i/".encryptValue($user_details['userid'])."' style='text-decoration:underline;font-size:17px;'>Click here to edit</a>";
				 */
				 
				 $data['msg'] = "ERROR: The emailaddress has already been used by another user"; 

				 $data['errormsgs']['emailaddress'] = "The emailaddress has already been used by another user";
				 $data['requiredfields'] = array('emailaddress');
			}
			             
            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) 
			&& empty($data['msg']) )
			{
				if(!empty($validation_results['errormsgs']))
				{					
					$data['msg'] = "WARNING: ".end($validation_results['errormsgs']);
					$data['errormsgs'] = $validation_results['errormsgs'];
				}
				else
				{
					$data['msg'] = "WARNING: The highlighted fields are required.";
				}				
				
				$data['requiredfields'] = $validation_results['requiredfields'];			
			}
			
		}		
		
		$data['page_title'] = (!empty($data['i'])? 'Edit user details' : 'Add user');
		$data['current_menu'] = 'add_users';
		$data['view_to_load'] = 'users/user_form_v';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['view_data']['formdata'] = $_POST;
		
		#Get access groups                
        $data['usergroups'] = $this->db->query($this->Query_reader->get_query_by_code('get_user_group_list',array('searchstring'=>'UG.isactive="Y" '.($this->session->userdata('isadmin') == 'N'? ' AND UG.id != 14 ' : ''), 'orderby'=>'ORDER BY UG.groupname', 'limittext'=>'')))->result_array();
		
		#Get pdes     
		$this->db->order_by("pdename", "asc");              
        	$data['pdes'] = $this->db->get_where('pdes', array('isactive'=>'Y', 'status'=>'in'))->result_array();
        
		$this->load->view('dashboard_v', $data);
	}
        
    #Function to delete the user
	function delete_user()
	{
		check_user_access($this, 'delete_user', 'redirect');
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 't'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['i'])){
			$result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_user', array('id'=>decryptValue($data['i'])) ));
		}
		
		if(!empty($result) && $result){
			$this->session->set_userdata('duser', "The user data has been successfully deleted.");
		}
		else if(empty($data['msg']))
		{
			$this->session->set_userdata('duser', "ERROR: The user could not be deleted or was not deleted correctly.");
		}
		
		if(!empty($data['t']) && $data['t'] == 'super'){
			$tstr = "/t/super";
		}else{
			$tstr = "";
		}
		redirect("admin/manage_users/m/duser".$tstr);
	}
	
        
    #Function to delete the user group
	function delete_user_group()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 't'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['i'])){
			$result = $this->db->query($this->Query_reader->get_query_by_code('delete_user_group', array('groupid'=>decryptValue($data['i'])) ));
		}
		
		if(!empty($result) && $result){
			$this->session->set_userdata('duser', "The access group has been successfully deleted.");
                        
			#Delete the group permissions
			$this->db->query($this->Query_reader->get_query_by_code('delete_group_permissions', array('groupid'=>decryptValue($data['i'])) ));
		}
		else if(empty($data['msg']))
		{
			$this->session->set_userdata('dusergroup', "ERROR: The access group could not be deleted or was not deleted correctly.");
		}
		
		if(!empty($data['t']) && $data['t'] == 'super'){
			$tstr = "/t/super";
		}else{
			$tstr = "";
		}
		redirect(base_url()."admin/manage_user_groups/m/duser".$tstr);
	}
        
    #Manage user groups
	function manage_user_groups()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		#Get the paginated list of users
		$data = paginate_list($this, $data, 'get_user_group_list', array('searchstring'=>'UG.isactive ="Y"', 'orderby'=>'ORDER BY UG.groupname ASC'));
		
		$data = handle_redirected_msgs($this, $data);
		$data = add_msg_if_any($this, $data);
		
		$data['page_title'] = 'Manage user groups';
		$data['current_menu'] = 'view_user_groups';
		$data['view_to_load'] = 'users/manage_user_groups_v';
		$data['search_url'] = 'admin/search_user_groups';
		$data['form_title'] = $data['page_title'];
        
		$this->load->view('dashboard_v', $data);
	}
        
    #Load User group form
	function user_group_form()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);                
                
        #user is editing
		if(!empty($data['i']))
		{
			$groupid = decryptValue($data['i']);
			$data['formdata'] = $this->Query_reader->get_row_as_array('get_group_by_id', array('id'=>$groupid ));
						
            #Check if the user is simply viewing
            if(!empty($data['a']) && decryptValue($data['a']) == 'view') $data['isview'] = "Y";
		}
		
		$data['page_title'] = (!empty($data['i'])? 'Edit user group' : 'New user group');
		$data['current_menu'] = 'add_user_group';
		$data['view_to_load'] = 'users/user_group_form_v';
		$data['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}
        
    #Insert user group
	function save_user_group()
	{		
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a', 't'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
			
		if($this->input->post('save'))
		{
			$required_fields = array('groupname');
			$data['formdata'] = $_POST;
			$_POST = clean_form_data($_POST);
			$validation_results = validate_form('', $_POST, $required_fields);

			#Only proceed if the validation for required fields passes
			if($validation_results['bool'])
			{
				if(!empty($data['i']))
				{ 
					$result = $this->db->query($this->Query_reader->get_query_by_code('update_user_group', array_merge($_POST, array('groupid'=>decryptValue($data['i'])))));
				} 
				else 
				{
					#Check if a group with a similar name already exists
					$groupNameQuery = $this->Query_reader->get_query_by_code('get_user_group_by_name', $_POST);
					$groupNameQueryResult = $this->db->query($groupNameQuery);
					
					if($groupNameQueryResult->num_rows() < 1){
						$_POST = array_merge($_POST, array('author' => $this->session->userdata('userid')));
						$result = $this->db->query($this->Query_reader->get_query_by_code('insert_user_group', $_POST));
					}
					else
					{
						$data['msg'] = "WARNING: An access group with a similar name already exists.";
					}
					
				}
			
			#Format and send the errors
			if(!empty($result) && $result){
				$this->session->set_userdata('usave', "The user group data has been successfully saved.");
				redirect("admin/manage_user_groups/m/usave");
			}
			else if(empty($data['msg']))
			{
				$data['msg'] = "ERROR: The access group not be saved or was not saved correctly.";
			}
                    }#VALIDATION end
			
			
                    if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) 
                    && empty($data['msg']) )
                    {
                    	$data['msg'] = "WARNING: The highlighted fields are required.";
                    }
			
                    $data['requiredfields'] = $validation_results['requiredfields'];
                    $data['groupdetails'] = $_POST;
                        
		}
		
		$data['page_title'] = (!empty($data['i'])? 'Edit user group' : 'New user group');
		$data['current_menu'] = 'add_user_group';
		$data['view_to_load'] = 'users/user_group_form_v';
		$data['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}
        
    #Function to update the permissions of a user group
	function update_permissions()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 't'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['i'])){
			$result = $this->db->query($this->Query_reader->get_query_by_code('get_group_permissions', array('groupid'=>decryptValue($data['i'])) ));
			$the_permissions_list = $result->result_array();
			$data['permissions_list'] = array();
			foreach($the_permissions_list AS $permission_row){
				array_push($data['permissions_list'], $permission_row['permissionid']);
			}
			
			
			$data['groupdetails'] = $this->Query_reader->get_row_as_array('get_group_by_id', array('groupid'=>decryptValue($data['i']) ));
			$usertype = ($this->session->userdata('isadmin') == 'Y')? "admin": "";
			$result = $this->db->query($this->Query_reader->get_query_by_code('get_all_permissions', array('accesslist'=>"'".$usertype."'") ));
			$data['all_permissions'] = $result->result_array();
			
			#put all permissions in a manageable array
			$data['all_permissions_list'] = array();
			foreach($data['all_permissions'] AS $thepermission){
				array_push($data['all_permissions_list'], $thepermission['id']);
			}
		}
		
		if(!empty($data['t']) && $data['t'] == 'super'){
			$tstr = "/t/super";
		}else{
			$tstr = "";
		}
		
		if($this->input->post('updatepermissions'))
		{
			if(!empty($_POST['permissions'])){
				$result_array = array();
				#First delete all permissions from the access table
				$delresult = $this->db->query($this->Query_reader->get_query_by_code('delete_group_permissions', array('groupid'=>$_POST['editid']) ));
				
				array_push($result_array, $delresult);
				
				foreach($_POST['permissions'] AS $permissionid)
				{
					$insresult = $this->db->query($this->Query_reader->get_query_by_code('add_group_permission', array('groupid'=>$_POST['editid'], 'permissionid'=>$permissionid, 'author'=>$this->session->userdata('userid')) ));
					array_push($result_array, $insresult);
				}
				
				if(get_decision($result_array)){
					$this->session->set_userdata('pgroup', "The Group permissions have been assigned.");
					redirect("admin/manage_user_groups/m/pgroup");
				}
			}
		}
		
		if(empty($result) || !$result)
		{
			if(empty($_POST['permissions']))
			{
				$this->session->set_userdata('puser', "WARNING: No permissions are assigned to the group.");
			}
			else
			{
				$this->session->set_userdata('puser', "ERROR: The group permissions could not be assigned.");
			}
			redirect(base_url()."admin/manage_user_groups/m/puser");
		}
		
		$data['view_to_load'] = 'users/user_group_permissions_v';		
		$data['page_title'] = 'User group permissions ' . (!empty($data['groupdetails']['groupname'])? 
							'for user group ['. $data['groupdetails']['groupname'] . ']' : '');
		$data['current_menu'] = 'user_groups';
		$data['search_url'] = '';
		$data['form_title'] = 'User group permissions ' . (!empty($data['groupdetails']['groupname'])? 
							'for user group <i>['. $data['groupdetails']['groupname'] . ']</i>' : '');;
		
		$this->load->view('dashboard_v', $data);
	
	}
	
	
	
	#Function to get user group permissions
	function user_group_permissions()
	{
		access_control($this, array('admin'));
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 't'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		if(!empty($data['i']))
		{
			#group details
			$data['groupdetails'] = $this->Query_reader->get_row_as_array('get_group_by_id', array('id'=>decryptValue($data['i'])));
			
			$result = $this->db->query($this->Query_reader->get_query_by_code('get_group_permissions', array('groupid'=>decryptValue($data['i'])) ));
						
			$the_permissions_list = $result->result_array();
			$data['permissions_list'] = array();
			
			foreach($the_permissions_list AS $permission_row){
				array_push($data['permissions_list'], $permission_row['permissionid']);
			}
			
			
			$data['groupdetails'] = $this->Query_reader->get_row_as_array('get_group_by_id', array('id'=>decryptValue($data['i']) ));
			$usertype = ($this->session->userdata('isadmin') == 'Y')? "admin": "";
			$result = $this->db->query($this->Query_reader->get_query_by_code('get_all_permissions', array('accesslist'=>"'".$usertype."'") ));
			$data['all_permissions'] = $result->result_array();
			
			#put all permissions in a manageable array
			$data['all_permissions_list'] = array();
			foreach($data['all_permissions'] AS $thepermission){
				array_push($data['all_permissions_list'], $thepermission['id']);
			}
		}
				
		$data['view_to_load'] = 'users/user_group_permissions_v';		
		$data['page_title'] = 'User group permissions ' . (!empty($data['groupdetails']['groupname'])? 
							'for user group <i>['. $data['groupdetails']['groupname'] . ']</i>' : '');
		$data['current_menu'] = 'view_user_groups';
		$data['search_url'] = '';
		$data['form_title'] = $data['page_title'];
		
		$this->load->view('dashboard_v', $data);
	}

	#Manage PDES
   	#Manage PDES
    function manage_pdes()
    {
        access_control($this, array('admin'));

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

  #print_r($data); exit();

        
        if(empty($data['level']))
        {
        	$level = 'active';
        }
        else
        {
        	$level =  $data['level'];
        }
        $data['level'] = $level;
          switch ($level) {
          case 'active':
            # fetchcontent
          $data['status'] = 'active';
            $data  = $this-> pde_m -> fetch_pdes('in',$data); 
          #  $this->load->view('pde/adons',$data); 
           
            break;
          case 'archive':
            # fetch content
            $data['status'] = 'archive';
            $data  = $this-> pde_m -> fetch_pdes('out',$data); 
          #  $this->load->view('pde/adons',$data); 
            break;
          
          default:
            # do nothing ...
            $data  = $this-> pde_m -> fetch_pdes('in',$data); 
            break;
        }

        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        $data['page_title'] = 'Manage PDE\'s';
        $data['current_menu'] = 'view_pdes';
        $data['view_to_load'] = 'pde/manage_pda_v';
        $data['view_data']['form_title'] = $data['page_title'];
	    $data['search_url'] = 'admin/search_pdes/level/'.$data['level'];
        $this->load->view('dashboard_v', $data);

    }
     
	#search pdes
    function search_pdes(){
    
		access_control($this, array('admin'));
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);		
		# Pick all assigned data
		$data = assign_to_data($urldata);		 

		$_POST['searchQuery']  = ' ';
		if(!empty($_GET['search']['value']))
		{
			$_POST['searchQuery'] = mysql_real_escape_string($_GET['search']['value']);
		}

			 if(empty($data['level']))
	        {
	        	$level = 'active';
	        }
	        else
	        {
	        	$level =  $data['level'];
	        }
        $data['level'] = $level;


        $_POST = clean_form_data($_POST);
		$searchstring = trim($_POST['searchQuery']);

		$search_string = ' AND 1=1 ';
		if(!empty($searchstring))
		{			
		   $search_string .= ' AND  ( pdes.pdename   like "%'. $searchstring .
              '%" OR pdes.abbreviation like "%'. $searchstring .'%" OR  pdes.code like "%'.
              $searchstring .'%")';    
		}
               

	 
          switch ($level) {
          case 'active':
            # fetchcontent
            $data['status'] = 'active';
            $data = $this->pde_m->search_pdes('in', $data,$search_string);
            break;
          case 'archive':
            # fetch content
         // exit("passed")
            $data['status'] = 'archive';
            $data = $this->pde_m->search_pdes('out', $data,$search_string);
		    break;          
          default:          
          	$data = $this->pde_m->search_pdes('in', $data,$search_string);
            break;
        }	 


			$data['area'] = 'pde_list';		
			$this->load->view('includes/add_ons', $data);							
    }

    //load pde form
    function load_pde_form()
    {

        access_control($this, array('admin'));
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        #Get access groups                
        $accessGroupsResult = $this->db->query($this->Query_reader->get_query_by_code('get_user_group_list', array()));

        $data['pdetypes'] = $this->Pdetypes_m->fetchpdetypesa($status = 'Y');
        $data['usergroups'] = $this->Usergroups_m->fetchusergroups();
        $data['users'] = $this->users_m->fetchusers();
         
        //	users_m
        #form type
        $data['formtype'] = 'insert';

        $data['page_title'] = 'CREATE PDE ';
        $data['current_menu'] = 'create_pde';
        $data['view_to_load'] = 'pde/manage_pda_v';

        $data['view_data']['form_title'] = $data['page_title'];
        $data['view_to_load'] = 'pde/pde_form_v';

        $this->load->view('dashboard_v', $data);
    }

	#MANAGE PDE TYPES 
    function manage_pdetypes()
    {
        access_control($this, array('admin'));

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
       // print_r($data); exit();
        if(empty($data['level']))
        	$data['level'] = 'active';

        if($data['level'] == 'active')
        	$status = 'Y';
        else
        	$status = 'N';


        //fetchpdetypes
        $data = $this->Pdetypes_m->fetchpdetypes($status = $status, $data);
        

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

        $data['page_title'] = 'Manage PDE Types';
        $data['current_menu'] = 'manage_pdetypes';
        $data['view_to_load'] = 'pde/manage_pdatype_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'admin/search_pdetypes/level/'.$data['level'];
        $this->load->view('dashboard_v', $data);


    }

     #search pdes
    function search_pdetypes(){

    	access_control($this, array('admin'));		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);

  if(empty($data['level']))
        	$data['level'] = 'active';

        if($data['level'] == 'active')
        	$status = 'Y';
        else
        	$status = 'N';

		$search = $_POST['searchQuery']  = ' ';
		if(!empty($_GET['search']['value']))
		{
			$search = $_POST['searchQuery'] = mysql_real_escape_string($_GET['search']['value']);
	    	$search_string = ' AND  ( pdetypes.pdetype  like "%'. $search .'%")';  
		}
		else
		$search_string =  ' ';
		
			$data = $this->Pdetypes_m->search_pdetypes($status, $data,$search_string);
		$data['area'] = 'pdetype_list';
		//print_r($data['page_list']);
		
		$this->load->view('includes/add_ons', $data);							
    }



    //load_pdetype_form
    function load_pdetype_form()
    {

        access_control($this, array('admin'));
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        #Get access groups                
        $accessGroupsResult = $this->db->query($this->Query_reader->get_query_by_code('get_user_group_list', array()));

        //  $data['pdetypes'] = $this-> Pdetypes_m -> fetchpdetypes($status='Y');
        $data['usergroups'] = $this->Usergroups_m->fetchusergroups();
        $data['users'] = $this->users_m->fetchusers();
        //	users_m
        #form type
        $data['formtype'] = 'insert';

        $data['page_title'] = 'New PDE ';
        $data['current_menu'] = 'add_pdetype';


        $data['view_data']['form_title'] = $data['page_title'];
        $data['view_to_load'] = 'pde/pdetype_form_v';

        $this->load->view('dashboard_v', $data);
    }
	
	
	  //Audti Trail 
    function audit_trail()
    {
    	#	$this->load->model('Query_reader', 'Query_reader', TRUE);
    
    	# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$userid =  $this->session->userdata('userid');
		#print_r($userid);
		$isadmin =  $this->session->userdata('isadmin');
		#print_r($isadmin);
     


		
       
         $searchstring = array();
		#If is Admin is = Y
		 if($isadmin == 'Y')
		 	  $searchstring = array( 'searchstring' => 'userid > 0  ORDER BY dateadded DESC ' ,'limittext'=>' ' );
		 else
		 	 $searchstring = array( 'searchstring' => ' userid = '.$userid.'   ORDER BY dateadded DESC  ' ,'limittext'=>' ' );


		#If You Have Access Rights for a GIven PDE
 		$query = $this->db->query($this->Query_reader->get_query_by_code('fetch_audit_trail',  $searchstring))->result_array();
		      
	    $data['page_list'] = paginate_list($this, $data, 'fetch_audit_trail', $searchstring,10);
    
		#fetch_audit_trail
		$data['page_title'] = 'AUDIT TRAIL';
		$data['current_menu'] = '';
		$data['view_to_load'] = 'users/audit_trail';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['search_url'] = 'admin/search_audit_trail';
		
		$this->load->view('dashboard_v', $data);
    }

   function get_user_by_pde()
   {


    	# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$userid =  $this->session->userdata('userid');
		#print_r($userid);
		$isadmin =  $this->session->userdata('isadmin');
		#print_r($isadmin);

		$pde = (!empty($_POST['pde_id'])) ? $_POST['pde_id'] : 0 ;



print_r($_POST);

		$users =  get_users_by_pde($pde);
		print_r($users);

		/*$details = '<option value="0">-- Select -- </option>';
		if(!empty($users))
		{
			 foreach ($pdes as $key => $row) 
		  	  { 
		  		     $details .= '<option value="'.$row['pdeid'].'">'.$row['pdename'].'</option>';
		      }				
		}   */

		 
 

   }
    

    
    function load_advanced_search_form()
    {

        $details = '<div style="width:80%;margin:auto; text-aligh:center;" >'.
            ' <div class="control-group"><h2>SEARCH AUDIT TRAIL  </h2></div>'.


            '<div class="container-fluid">'.
            '<div class="row">'.
            '<div class="col-md-12">'.
            '<form role="form">'.
            '<div class="form-group">'.


            '<label for="search_phrase">'.
            ' Search Phrase '.
            '</label>'.
            '<input type="text"  class="form-control search_phrase" id="search_phrase" style="width:100%" />'.
            '</div>'.


            '<div class="form-group">'.
            '<label for="select_pde">'.
            ' Select PDE.'.
            '</label>'.
            '<select  onChange="javascript:get_user(this.value);" style="width:100%" id="select_pde" name="select_pde"><option value="0">-- Select -- </option>';
                $pdes = get_pde_list();
	  		     foreach ($pdes as $key => $row) 
	  		     { 
	  		     $details .= '<option value="'.$row['pdeid'].'">'.$row['pdename'].'</option>';
	             }			 
             $details .=   '</select>'.
            '</div>'.



             '<div class="form-group">'.
            '<label for="select_user">'.
            ' Select User .'.
            '</label>'.
            '<select  style="width:100%" id="select_user" name="select_user"><option>Select</option></select>'.
            '</div>'.




            '<label for="event_date">'.
            ' Event Date '.
            '</label>'.
            '<input type="date"  class="form-control event_date" id="event_date" style="width:100%" />'.
            '</div>'.


 


            '<button type="button" onClick="javascript:$(`#lightbox_wrapper`).fadeOut(`slow`);" class="btn btn-default" data-dismiss="modal">Close</button>'.
            ' &nbsp;'.
            '<button onClick="javascript:search_audit_trail();" type="button" class="btn  search_audit_trail">Submit</button> '.


        '</form>'.
        '</div>'.
        '</div>'.
        '</div>'. '</div>';


        echo $details;
    }
    
    
    //Search Audit Trail 
    function search_audit_trail()
    { 
    
    	# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i','a'));
				
		# Pick all assigned data
		$data = assign_to_data($urldata);
				
		$userid =  $this->session->userdata('userid');		 
		$isadmin =  $this->session->userdata('isadmin');
		
        $searchstring = array();
        
    
        
		#If is Admin is = Y
		 if($isadmin == 'Y')
		 {
			 $searchstring_criteria = 'userid > 0 ';
		 }
		 else
		 {
			  $searchstring_criteria = ' userid = '.$userid.' ';
		 }
		 
		 
		 	 
		 	 
		 if(!empty($_GET['search']['value']))
		 {
			 $search = mysql_real_escape_string($_GET['search']['value']);			 
			 $searchstring_criteria .= ' AND  (
			      message like  "%'.$search.'%"
			      OR
			        context like  "%'.$search.'%"
			          OR
			        name like  "%'.$search.'%"			        
			            OR
			        pde like  "%'.$search.'%"
			      
			 )';	 
			  
		 }
		 
		 
		 $searchstring_criteria .= ' ORDER BY dateadded DESC ';
		 
		 $searchstring = array( 'searchstring' => $searchstring_criteria ,'limittext'=>' ' );
		 
		 


		#If You Have Access Rights for a GIven PDE
 		$query = $this->db->query($this->Query_reader->get_query_by_code('fetch_audit_trail',  $searchstring))->result_array();
		      
	    $data['page_list'] = paginate_list($this, $data, 'fetch_audit_trail', $searchstring,200);
	  
		#fetch_audit_trail
		$data['page_title'] = 'AUDIT TRAIL';
		$data['current_menu'] = '';
		$data['view_to_load'] = 'users/audit_trail';
		$data['view_data']['form_title'] = $data['page_title'];
		
		$data['area'] = 'audit_trail';
        $this->load->view('includes/add_ons', $data);
    }
    

	
	
	

}

/* End of file admin.php */
/* Location: ./system/application/controllers/admin.php */
?>
