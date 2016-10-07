<?php

class Users_m extends CI_Model {
    public $_tablename = 'users';
    public $_primary_key = 'userid';

    #Constructor

	function Users_m()
	{
		parent::__construct();
		$this->load->model('Query_reader', 'Query_reader', TRUE);
		$this->load->model('Ip_location', 'iplocator');
        $this->load->model('usergroups_m');
	}
	
	#Check whether active user with passed details exists in the database
	# Return user details from the database

	function validate_login_user($query_data, $ignore_active='')
	{	
		 ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		if($ignore_active == 'ignore_active_flag')
		{
			$query = $this->Query_reader->get_list('user_login_ignore', $query_data);
		}
		else
		{ 
ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
			$query = $this->Query_reader->get_list('user_login', $query_data);
 
			
		}  

	 
		 
		return $query;
	}
	
	
	# Save login trail based on result

    function get_dashboard()
    {
        #Admin
        if ($this->session->userdata('usertype') && $this->session->userdata('isadmin') == 'Y') {
            return base_url() . 'admin/dashboard';
        } #Normal user
        else {
            return base_url() . 'user/dashboard';
        }
    }


    #function to get a user location given their IP address

    function get_settings_page()
    {
        return base_url() . 'account/settings/i/' . encryptValue($this->session->userdata('userid'));
    }


    #Function to determine the user dashboard based on their rights

    function save_user($userdetails, $usertype)
    {
        if (trim($usertype) == "Basic") {
            $query = $this->Query_reader->get_query_by_code('save_basic_user', $userdetails);
        } else if (trim($usertype) == "Admin") {
            $query = $this->Query_reader->get_query_by_code('save_admin_user', $userdetails);
        }
        $result = $this->db->query($query);
        return $result;
    }


    #FUnction to get the user's settings page

    function approve_user($userid, $approvedby)
    {
        $query = $this->Query_reader->get_query_by_code('approve_user', array('approvedby' => $approvedby, 'id' => $userid));
        $result = $this->db->query($query);
        return $result;
    }

    #function to save user

    function populate_user_details($userdata)
    {
        $userdetails['userid'] = (empty($userdata[0]['userid']) && !empty($userdata[0]['id'])) ? $userdata[0]['id'] : $userdata[0]['userid'];

        $userdetails['username'] = $userdata[0]['username'];
        $userdetails['isadmin'] = $userdata[0]['isadmin'];
        $userdetails['usertype'] = $userdata[0]['usertype'];
        $userdetails['emailaddress'] = $userdata[0]['emailaddress'];
        $userdetails['userexpirydate'] = $userdata[0]['enddate'];
        $userdetails['names'] = $userdata[0]['firstname'] . " " . $userdata[0]['middlename'] . " " . $userdata[0]['lastname'];
        $userdetails['firstname'] = $userdata[0]['firstname'];
        $userdetails['lastname'] = $userdata[0]['lastname'];
        $userdetails['speciality'] = $userdata[0]['speciality'];
        $userdetails['changedpassword'] = $userdata[0]['changedpassword'];

        $this->log_access_trail(replace_bad_chars($userdetails['username']), 'Success');

        $this->session->set_userdata($userdetails);
        $this->session->set_userdata('alluserdata', $userdetails);
        setcookie("loggedin", "true", time() + $this->config->item('sess_time_to_update'));
    }

    #function approve expert user

	function log_access_trail($username, $logresult, $action='login')
    {
		$browser = getBrowser();
		$country_code = '';
		$country_name = '';
		$flag = '';
		$city = '';
		$region = '';
		$isp = '';
		$latitude = '';
		$longitude = '';

		if(!$this->session->userdata('logstamp'))
		{
			$this->session->set_userdata('logstamp', strtotime('now').'-'.$this->session->userdata('userid'));
		}

		#If the user is logging in, save more data about the location
		if($action == 'login')
		{
			$ip = $this->input->ip_address();

			#Use backup server if cannot make a connection
            if (stripos(BASE_URL, '/localhost') === FALSE)
			{
            	$location = $this->get_ip_location($ip);
				$country_code = $location['countrycode'];
				$country_name = $location['country'];
				$flag = '';
				$city = $location['city'];
				$region = $location['region'];
				$isp = $location['isp'];
				$latitude = $location['latitude'];
				$longitude = $location['longitude'];
			}
		}

		#Fetch User Data
		$userid = $this->get_current_user_identifier('userid');
		$userid = !empty($userid) ? $userid : 0 ;

        $query = $this->db->query($this->Query_reader->get_query_by_code('save_access_trail', array('userid'=>$userid,'url' => current_url(), 'username' => replace_bad_chars($username), 'logresult' => $logresult, 'emailaddress' => replace_bad_chars($username), 'ipaddress' => $this->input->ip_address(), 'browser' => $browser['name'] . ',' . $browser['version'] . ',' . $browser['platform'], 'action' => $action, 'logstamp' => $this->session->userdata('logstamp'), 'countrycode' => $country_code, 'countryname' => $country_name, 'flag' => $flag, 'city' => $city, 'region' => $region, 'isp' => $isp, 'latitude' => $latitude, 'longitude' => $longitude)));

       # $query = $this->db->query($this->Query_reader->get_query_by_code('save_access_trail', array('url' => current_url(), 'username' => replace_bad_chars($username), 'logresult' => $logresult, 'emailaddress' => replace_bad_chars($username), 'ipaddress' => $this->input->ip_address(), 'browser' => $browser['name'] . ',' . $browser['version'] . ',' . $browser['platform'], 'action' => $action, 'logstamp' => $this->session->userdata('logstamp'), 'countrycode' => $country_code, 'countryname' => $country_name, 'flag' => $flag, 'city' => $city, 'region' => $region, 'isp' => $isp, 'latitude' => $latitude, 'longitude' => $longitude)));
	}


    #Function to populate the user details array

	function get_ip_location($ipaddress)
	{
		$location['browser'] = getBrowser();
		$location['countrycode'] = '';
		$location['country'] = '';
		$location['zipcode'] = '';
		$location['city'] = '';
		$location['region'] = '';
		$location['isp'] = '';
		$location['latitude'] = '';
		$location['longitude'] = '';

        #TODO: Upgrade IP address locator to be independent using:
		#http://pecl.php.net/package/geoip
		#http://www.maxmind.com/app/php

        #>>>http://ipinfodb.com/ip_location_api.php
		#Requires azziwa's API key with ipinfodb.com
		$this->iplocator->setKey('a0eb7ac4688fbf30a813868ddcbccb74106c43a54b192e5d30be0740a94e144d');

		//Get errors and locations
		$dlocation = $this->iplocator->getCity($ipaddress);
		$errors = $this->iplocator->getError();

		#Use backup server if cannot make a connection
        #stripos(BASE_URL, '/localhost') === FALSE &&
       	if (!empty($dlocation)) {
            $location['countrycode'] = $dlocation['countryCode'];
			$location['country'] = $dlocation['countryName'];
			$location['zipcode'] = $dlocation['zipCode'];
			$location['city'] = $dlocation['cityName'];
			$location['region'] = $dlocation['regionName'];
			$location['isp'] = $dlocation['ipAddress'];
			$location['latitude'] = $dlocation['latitude'];
			$location['longitude'] = $dlocation['longitude'];
		}


        return $location;
	}
	
	#Check the password strength

    function check_password_strength($newpassword)
	{
		$error_msg = "";
		$bool = TRUE;
		$chars = '@#$%&!_';
		$uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$lowercase = 'abcdefghijklmnopqrstuvwxyz';
		$strike = 0;
		$newpassword = restore_bad_chars($newpassword);
		
		#password below length
		if(strlen($newpassword) < 6)
		{
			$error_msg = "Password is below minimum length.";
			$bool = FALSE;
			$strike++;
		}
		
		#contains number
		if(strcspn($newpassword, '0123456789') == strlen($newpassword))
		{
			$error_msg = "Password should to contain a number.";
			$bool = FALSE;
			$strike++;
		}
		
		#contains character
		/*
		if(strcspn($newpassword, $chars) == strlen($newpassword))
		{
			$error_msg = "Password should contain a character from ".$chars.".";
			$bool = FALSE;
			$strike++;
		}
		*/
		
		#contains lower case character
		if(strcspn($newpassword, $lowercase) == strlen($newpassword))
		{
			$error_msg = "Password should contain a lower case character.";
			$bool = FALSE;
			$strike++;
		}
		
		#contains upper case character
		if(strcspn($newpassword, $uppercase) == strlen($newpassword))
		{
			$error_msg = "Password should contain an upper case character.";
			$bool = FALSE;
			$strike++;
		}
		
		
		return array('bool'=>$bool, 'msg'=>$error_msg, 'strikecount'=>$strike);
	}
	
	
	#Function to determine the user's location
	function get_user_location($return_type = 'array', $default='')
	{
		$location_array = array('country'=>'', 'zipcode'=>'', 'city'=>'', 'region'=>'');
		
		#If the user is logged in
		if($this->session->userdata('emailaddress'))
		{ 
			#First check if the user specified another location
			$location = $this->Query_reader->get_row_as_array('get_saved_user_location', array('emailaddress'=>$this->session->userdata('emailaddress')));
			
			if(!empty($location))
			{
				$location_array =  array('country'=>$location['country'], 'zipcode'=>$location['zipcode'], 'city'=>$location['city'], 'region'=>$location['region']);
			}
			
			if(empty($location_array))
			{
				#Check the user's profile if the above was not met
				$userdetails = $this->Query_reader->get_row_as_array('get_user_by_email', array('emailaddress'=>$this->session->userdata('emailaddress'), 'isactive'=>'Y'));
				if(!empty($userdetails))
				{
					$location_array = array('country'=>$userdetails['country'], 'zipcode'=>$userdetails['zipcode'], 'city'=>$userdetails['city'], 'region'=>$userdetails['state']);
				}
			}
		}
		
		#If the user is not logged in
		else
		{
			#Get the user location
			$location = $this->get_ip_location($this->input->ip_address());
			if(!empty($location['zipcode']) && $location['zipcode'] != '-')
			{
				$location_array = array('country'=>$location['country'], 'zipcode'=>$location['zipcode'], 'city'=>$location['city'], 'region'=>$location['region']);
			}
			else
			{
				$this->session->set_userdata('resetlocation', 'Y');
			}
		}
		
		
		#return based on desired value
		if($return_type == 'locationid')
		{
			$loc = $this->Query_reader->get_row_as_array('get_region_id', array('condition'=>" country='".$location_array['country']."' AND region='".$location_array['region']."' "));
			if(!empty($loc))
			{
				return $loc['id'];
			}
			
			$loc = $this->Query_reader->get_row_as_array('get_region_id', array('condition'=>" country='".$location_array['country']."' AND region='' "));
			if(!empty($loc))
			{
				return $loc['id'];
			}
			
			$loc = $this->Query_reader->get_row_as_array('get_region_id', array('condition'=>" country='".$location_array['region']."' AND region='' "));
			if(!empty($loc))
			{
				return $loc['id'];
			}
			else
			{
				
				return $default;
			}
		}
		else
		{
			return $location_array;
		}
	}
	
	
	#get the current user identifier
	function get_current_user_identifier($identifier = "email")
	{
		$ipaddress = $this->input->ip_address();
		if($identifier == "email")
		{
			$id = ($this->session->userdata('emailaddress'))?$this->session->userdata('emailaddress'):$ipaddress;
			return $id;
		}
		else if($identifier == "userid")
		{
			$id = ($this->session->userdata('userid'))?$this->session->userdata('userid'):$ipaddress;
			return $id;
		}
		else if($identifier == "noip")
		{
			$id = ($this->session->userdata('userid'))?$this->session->userdata('userid'):"";
			return $id;
		}
	}
	#mover made
	#FETCH USERS FROM THE DATABASE ::
	function fetchusers($idx = 0){


		$query = $this->Query_reader->get_query_by_code('get_user_list', array('searchstring' =>' '));
		  
		$result = $this->db->query($query)->result_array();
		return $result;
	}


    function get_user_info($id, $param)
    {
        $query_data=array
        (
            'id'=>$id
        );
        $this->db->cache_on();
        $query = $this->db->query($this->Query_reader->get_query_by_code('get_user_by_id', $query_data));

        $info_array=$query->result_array();



        //if there is a result
        if(count($info_array))
        {

            foreach($info_array as $row)
            {
                switch ($param)
                {
                    case 'firstname':
                        $result=$row['firstname'];
                        break;
                    case 'lastname':
                        $result=$row['lastname'];
                        break;
                    case 'middlename':
                        $result=$row['middlename'];
                        break;
                    case 'first name':
                        $result=$row['firstname'];
                        break;
                    case 'last name':
                        $result=$row['lastname'];
                        break;
                    case 'middle name':
                        $result=$row['middlename'];
                        break;
                    case 'fullname':
                        $result=$row['firstname'] .' '.$row['middlename'].' '.$row['lastname'];
                        break;
                    case 'gender':
                        $result=$row['gender'];
                        break;
                    case 'sex':
                        $result=$row['gender'];
                        break;
                    case 'prefix':
                        $result=$row['prefix'];
                        break;
                    case 'photo':
                        $result=$row['photo'];
                        break;
                    case 'imageurl':
                        $result=$row['photo'];
                        break;
                    case 'avatar':
                        $result=$row['photo'];
                        break;
                    case 'pde':
                        $result=$row['pde'];
                        break;
                    case 'pde_id':
                        $result=$row['pde'];
                        break;
                    case 'address1':
                        $result=$row['addressline1'];
                        break;
                    case 'address2':
                        $result=$row['addressline2'];
                        break;
                    case 'city':
                        $result=$row['city'];
                        break;
                    case 'username':
                        $result=$row['username'];
                        break;
                    case 'email':
                        $result=$row['emailaddress'];
                        break;
                    case 'emailaddress':
                        $result=$row['emailaddress'];
                        break;
                    case 'telephone':
                        $result=$row['telephone'];
                        break;

                    case 'usergroup':
                        $result=$row['usergroup'];
                        break;
                    case 'usergroup_id':
                        $result=$row['usergroup'];
                        break;

                    case 'is_admin':
                        $result=$row['isadmin'];
                        break;
                    case 'isadmin':
                        $result=$row['isadmin'];
                        break;

                    case 'user_group':
                        $result=$row['usergroup'];
                        break;

                    default:
                        //no parameter is passed display all user info
                        $result=$info_array;
                }
            }

            return $result;

        }
        else
        {
            return NULL;
        }

    }
    
    
    //get by passed parameters
    public function get_where($where)
    {
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();



        return $query->result_array();
    }




    public function get_paginated($num=20,$start)
    {
        //echo $this->$_primary_key.'foo';
        //build query
        $this->db->cache_on();
        $q=$this->db->select()->from($this->_tablename)->limit($num,$start)->where('trash','n')->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();

        //return result
        return $q->result_array();

    }

    //get paginated
    public function get_paginated_by_criteria($num=20,$start,$criteria)
    {
        //build query
        $this->db->cache_on();
        $q=$this->db->select()->from($this->_tablename)->limit($num,$start)->where($criteria)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();

        //return result
        return $q->result_array();

    }


}