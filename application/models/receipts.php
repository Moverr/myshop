<?php

#*********************************************************************************
# All users have to first hit this class before proceeding to whatever section
# they are going to.
#
# It contains the login and other access control functions.
#*********************************************************************************

class Receipts extends CI_Controller {

	# Constructor
	function Receipts()
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




		#MOVER LOADED MODELS
		$this->load->model('Receipts_m');
		$this->load->model('Proc_m');
		$this->load->model('Evaluation_methods_m');
		$this->load->model('Remoteapi_m');
		#load bid invitation model
		$this->load->model('bid_invitation_m');
		$this->load->model('procurement_plan_entry_m');


		##END
		date_default_timezone_set(SYS_TIMEZONE);
		$data = array();
		access_control($this);
	}


	#Default to login
	function index()
	{
		redirect('page/home');
	}

	#fetch pfoviders
	function fetchproviders()
	{

		// $query = mysql_query("select * from providers ");
		// $st = "";
		// while($q = mysql_fetch_array($query)){
		// 	$st .=$q['providernames']."<>";
		// }
		$data =   $this-> Remoteapi_m -> fetchproviders();

		//print_r($st);
	}


	
	function filterbids(){

		#access_control($this, array('admin'));
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		//print_r($_POST);
		# fetch receipts Id ::
		$receiptid = $_POST['receiptid'];
		//$this->uri->segment(3);
		#Fetch Bid Id 
		$bidid =  $_POST['bidid'];
		//$this->uri->segment(4);
		$lotid = 0;
		if(!empty($_POST['lotid']))
		{
			$lotid = $_POST['lotid'];
		}
		# load model ::
		$data['unsuccesful_bidders'] =   $this-> Receipts_m -> fetch_unsuccesful_bidders($receiptid,$bidid,$lotid);
    #load data
		$this->load->view('receipts/unsuccesfulproviders_v', $data);

	}

	#manage Receipts
	function manage_receipts(){
  	//check_user_access($this, 'view_receipts', 'redirect');
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		# Pick all assigned data
		$data = assign_to_data($urldata);

		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		$isadmin= $this->session->userdata['isadmin'];
		$userid = $this->session->userdata['userid'];

		$data['receiptinfo'] =   $this-> Receipts_m -> pde_receipt_information($isadmin,$userid,$data);
		$data['page_title'] = 'Manage Receipts';
		$data['current_menu'] = 'view_bids_received';
		$data['view_to_load'] = 'receipts/manage_receipts_v';
		$data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'receipts/search_receipts';
		$this->load->view('dashboard_v', $data);
	}


	#search  Receipts
	function  search_receipts(){

		$urldata = $this->uri->uri_to_assoc(3, array('m'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		$data = handle_redirected_msgs($this, $data);
		$isadmin= $this->session->userdata['isadmin'];
		$userid = $this->session->userdata['userid'];

		if($this->input->post('searchQuery'))
		{
			$_POST = clean_form_data($_POST);
			$_POST['searchQuery'] = $searchstring = trim($_POST['searchQuery']);
			$_POST['searchQuery'] .'%"';
			$data = $this-> Receipts_m ->search_receipts($isadmin,$userid, $data,$searchstring);

		}
		else
		{
			 $data = $this-> Receipts_m ->pde_receipt_information($isadmin,$userid,$data);

		}

		#print_r($data); exit();
		$data['area'] = 'receipts_list';
		$this->load->view('includes/add_ons', $data);

	}


	  #add new Receipt to a given Procurment
  	function add_receipt(){
		
		#print_r($_POST);
	        # check_user_access($this, 'add_receipt', 'redirect');
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));

		# Pick all assigned data
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);

		# ADD  PAGE TITLE AND THE REST  ::
		$data['formtype'] = "insert";
		$data['active_procurements'] = $this-> Proc_m -> fetch_active_procurement_list2($idx=0);

		 


		$data['page_title'] = 'Add  Bid Receipt';
		$data['current_menu'] = 'receive_bids';
		$data['view_to_load'] = 'receipts/add_receipt_v';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['bidid'] = $_POST['bidid'];


		$data['rowa']=$this->procurement_plan_entry_m->get_procurement_entry_by_bid( $_POST['bidid']);

		foreach($data['rowa'] as $row){
			$data['procurement_ref_no']=$row['procurement_ref_no'];
		}



	       #print_r($data['rowa']);

               $data['countrylist'] = $this-> Proc_m -> fetchcountries();


		//fetch receipts
		$isadmin= $this->session->userdata['isadmin'];
		$userid = $this->session->userdata['userid'];
		$data['receiptinfo'] =   $this-> Receipts_m -> procurement_receipt_information($isadmin,$userid,$data,$_POST['bidid']);
			#exit("reached");
			//$data['receiptinfo_jv'] =   $this-> Receipts_m -> procurement_receipt_information_jv($isadmin,$userid,$data,$_POST			['procurementrefno']);

		#Fetch Lots if any for this Bid Invitation X 
              $data['lots'] = $this-> Receipts_m->fetchlots($_POST['bidid']);

	 
#Fetch Currencies 
 	      $data['recod'] = mysql_query("select * from currencies ") or die("".mysql_error()) ;

	      $data['ropproviders'] =   $this-> Remoteapi_m -> fetchproviders();
 


		$this->load->view($data['view_to_load'], $data);

	}

	#function to save receipits procurement 
	function save_bidreceipt(){


		$segment = $this->uri->segment(3);
		$post = $_POST;
		#print_r($post); exit();

		switch ($segment) {

		case 'update':
			 $id = $this->uri->segment(4);
			 $result = $this-> Receipts_m -> updatebidreceipt($post,$id);
			 print_r($result);
		break;

		case 'insert':
			  $result = $this-> Receipts_m -> savebidreceipt($post);
			  print_r($result);
		 break;

		default:
				# code...
		break;
		}



	}

	#function to handle disposal bid receipts
	function save_disposal_bidreceipt(){


		$segment = $this->uri->segment(3);
		$post = $_POST;
		#print_r($post); exit();

		switch ($segment) {

		case 'update':
			 $id = $this->uri->segment(4);
			 $result = $this-> Receipts_m -> updatebidreceipt($post,$id);
			 print_r($result);
		break;

		case 'insert':
			  $result = $this-> Receipts_m -> savedisposalbidreceipt($post);
			  print_r($result);
		 break;

		default:
				# code...
		break;
		}

	}
	function load_edit_receipt_form(){

//decryptValue
		//edit_receipts


		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		# Pick all assigned data
		$data = assign_to_data($urldata);

		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);


		$receiptid = decryptValue($this->uri->segment(3));
		$data['receiptinfo'] = mysql_fetch_array($this-> Receipts_m -> fetchreceiptid($receiptid));
		#print_r($result);
		$data['formtype'] = "edit";
		$data['active_procurements'] = $this-> Proc_m -> fetch_active_procurement_list2($idx=0);
		$data['page_title'] = 'Add  Bid Receipt';
		$data['current_menu'] = 'manage_bid_receipts';
		$data['view_to_load'] = 'receipts/add_receipt_v';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['ropproviders'] =   $this-> Remoteapi_m -> fetchproviders();
    $data['countrylist'] = $this-> Proc_m -> fetchcountries();

		$this->load->view('dashboard_v', $data);


	}
	function delreceipts_ajax()
	{
	    #check_user_access($this, 'del_receipts', 'redirect');
	    $deltype =  $this->uri->segment(3);
	    $receiptid =  $this->uri->segment(4);
	    $result  = $this-> Receipts_m -> remove_restore_receipt($deltype,$receiptid);
	    echo  $result;
	}

 	#MANAGE BEST EVALUATED BIDDER NOTICES BACKEND
	function manage_bebs(){
		
			$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
			$data = assign_to_data($urldata);
			$data = add_msg_if_any($this, $data);
			$data = handle_redirected_msgs($this, $data);
			


	 
			if(!empty($data['financial_year'])){
				$data['current_financial_year'] = $current_financial_year = $data['financial_year'];
			
			}
			else{
				$data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;		
			}


			$level = !empty($data['level']) ?$data['level']  :'active';
			#print_r($level); exit();
			$data['level'] = $level;

			
			$financial_searchstring = '  AND procurement_plans.isactive ="Y" ';
			if($this->session->userdata('isadmin') == 'N')
			{
			$pdeid = $this->session->userdata['pdeid'];
			$financial_searchstring .= ' AND procurement_plans.pde_id='.$pdeid.' ';
			}		
			$data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();
				
			#exit("<h2>Testing :</h2> Will back in 15 minutes")
			#exit("movers");
			$data['achived_bebs'] = 0;
			$data['manage_bes'] = 0;
			$data['active_bebs'] = $this-> Proc_m -> count_beb_list(0,array('level' => 'active','current_financial_year'=>$current_financial_year));
		    $data['canceled_bebs'] = $this-> Proc_m -> count_beb_list(0,array('level' => 'canceled','current_financial_year'=>$current_financial_year));
		
			$data['achived_bebs'] = $this-> Proc_m -> count_beb_list(0,array('level' => 'archive','current_financial_year'=>$current_financial_year));
			$data['manage_bes'] = $this-> Proc_m -> fetch_beb_list(0,$data);
		
			$data['page_title'] = 'Manage Best Evaluated Bidders';
			$data['current_menu'] = 'manage_bebs';
			$data['view_to_load'] = 'bids/manage_bebs';
			$data['view_data']['form_title'] = $data['page_title'];
			$data['search_url'] = 'receipts/search_bebs/'.$data['level'];
			$this->load->view('dashboard_v', $data);
 

		}
		
		
		function search_bebs()
		{
		
			$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
			$data = assign_to_data($urldata);
			$data = add_msg_if_any($this, $data);
			$data = handle_redirected_msgs($this, $data);
			$level =$status = $this->uri->segment(3);

			if(!empty($level))
			$data['level'] = $level;

			if(empty($data['level']))
			{
				$data['level'] ='active';
			}
			
			#SEARCH  Crieria
			$_POST['searchQuery'] = '';
			if(!empty($_GET['search']['value']))
			$_POST['searchQuery'] = mysql_real_escape_string($_GET['search']['value']);

			$searchstring = '';
			if(!empty($_POST['searchQuery']))
			{
				$searchstring = ' AND ( bidinvitations.procurement_ref_no LIKE "%'.$_POST['searchQuery'].'%" ';
				$searchstring .= ' ||  IF(receipts.providerid > 0,receipts.providerid IN (SELECT  providers.providerid FROM providers WHERE providernames like "%'.$_POST['searchQuery'].'%" ) ,joint_venture.providers  IN(SELECT  providers.providerid FROM providers WHERE providernames like "%'.$_POST['searchQuery'].'%" ))';
				$searchstring .= ' || procurement_plan_entries.subject_of_procurement like "%'.$_POST['searchQuery'].'%" ';

				$searchstring .= ' ) ';
			}
			
			//echo "Reached";
			$data['manage_bes'] = $this-> Proc_m -> fetch_beb_list(0,$data,$searchstring);
			$data['area'] = 'manage_bes';
			$this->load->view('includes/add_ons', $data);

		}



		
		function ajax_beb_action()
		{
		 #print_r($_POST);
		$result = $this -> Receipts_m -> manage_beb_action($_POST);
	 	print_r($result);
		 #print_r('Reached');
		}
		function ajax_beblots_action()
		{
		  #print_r($_POST);
		$result = $this -> Receipts_m -> manage_beb_wholelots_action($_POST);
	 	print_r($result);
		 #print_r('Reached');			
		}
		

     #fetch lotted responses
	  #fetch lotted responses
		function populatelots(){


		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
		# Pick all assigned data
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);


     $result = $this -> Receipts_m -> findlottedproviders($_POST);

		print_r($result);


	}
	
//	get BEB lots
	function get_beb_lots()
	{
		$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		$data['results'] = $this->Receipts_m->fetch_lots_awarded_beb($data,$_POST);
		#print_r($data['results']);
		
		if(!empty($_POST['side']))
		{
			$data['side'] = $_POST['side'];
		}
		
         $data['page_title'] = 'View Best Evaluated BIdder Lots';
		$data['view_to_load'] = 'bids/manage_lots_beb';
		$data['view_data']['form_title'] = $data['page_title'];
		$this->load->view( $data['view_to_load'], $data);


	}
	
 

#get awarded contracts  lots
	function get_contracts_lots()
	{
		$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		$data['results'] = $this->Receipts_m->fetch_lots_awarded_beb_incontracts($data,$_POST);
		#print_r($data['results']);
    	$data['page_title'] = 'View Best Evaluated BIdder Lots';
		$data['view_to_load'] = 'contracts/manage_lots_contracts';
		$data['view_data']['form_title'] = $data['page_title'];
		$this->load->view( $data['view_to_load'], $data);

	}

	#get beb lots ajax
	function get_beb_lots_ajax(){
		$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		$result = $this->Receipts_m->fetch_lots_awarded_beb_notincontracts($data,$_POST);
		if(!empty($result['page_list']))
		{
		#	print_r($result);
			$st = '<option selected disabled="true"> Select Lot </option>';
			foreach ($result['page_list'] as $key => $row) {
			//	print_r($row);
			 	$st .= '<option value="'.$row['lotid'].'">'.$row['lot_title'].' </option>';
			}
		 	print_r($st);
		}
			else {
			print_r("0");
			}

	}
	function fetchbeb_lots_ajax()
	{
			$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
			$data = assign_to_data($urldata);
			$data = add_msg_if_any($this, $data);
			$data = handle_redirected_msgs($this, $data);
			$searchstring = "";
			if(!empty($_POST['lotid']))
			{
				$searchstring = " received_lots.lotid = '".mysql_real_escape_string($_POST['lotid'])."'";
	      $results = $this->Receipts_m->fetch_beb_lots($data,$searchstring);
			  $providers = rtrim($results['page_list'][0]['providers'],',');
			 $query = $this->db->query("SELECT * FROM providers WHERE providerid IN(".$providers.") ")->result_array();
		   $st = '<div><ul>';
			 foreach ($query as $key => $value) {
				 $st .="<li>".$value['providernames'].'</li>';
		   }
		 	$st .="</ul></div>";
		 	echo $st;
			}
			else {
				print_r('0');
			}
	}

	
		function updatbeboptions()
	{
		#print_r($_POST);
		$review_level = mysql_real_escape_string($_POST['options']);
		$bebid = mysql_real_escape_string($_POST['bebid']);
		$query = $this->db->query("UPDATE bestevaluatedbidder SET review_level='".$review_level."'  where id = ".$bebid."");
		 $this->session->set_userdata('usave', 'You have successfully Updated a BEB Admin Review Level  to  '.$review_level ); 

		print_r("1");
	}
	#search provider if exists or not :: 
	function searchproviders()
	{
		
		$providernames = mysql_real_escape_string($_POST['providernames']);
        	$result =   $this-> Remoteapi_m -> checkifsuspended($providernames );
        	if(count($result) >0)
        	{
        	 print_r("0");
        $rand  = rand(23454,83938);
        	
        	 $this->session->set_userdata('level','ppda');
        	 $userid = $this->session->userdata('userid');
        	 $query1 = $this->db->query("SELECT CONCAT(firstname,',',lastname) AS names FROM  users WHERE userid=".$userid ." limit 1")-> result_array();
        	 	  $level = "Disposal";
  			          $entity =  $this->session->userdata('pdeid');
				  $query = $this->db->query("SELECT * FROM pdes WHERE pdeid=".$entity." limit 1")-> result_array();
				  $entityname = $query[0]['pdename'];
				  $titles = " Attemp to add bid response of a Suspended provider by ".$entityname." CODE [ PR ".$rand."] ";
				  $body =  " <h2> SUSPENDED PROVIDER</H2> ";
				  $body .="<table><tr><th> Organisation </th><td>".$result['orgname']." </td></tr>";
				  $body .="<tr><th> Reason </th><td>".$result['reason']." </td></tr>";
				  $body .="<tr><th> Date Suspended</th><td>".$result['datesuspended']." </td></tr>";
				  $body .="<tr><th> End of Suspension </th><td>";
				  if($result['indefinite'] =='Y')
				  {
				   $body .= "Indefinite </td></tr>";
				  }else
				  {
				   $body .=  $result['endsuspension']." </td></tr>";
				  }
				  $body .="<tr><th>Admininstrator </th><td>".$query1[0]['names']." </td></tr>";
				  $body .="<tr><th> Date </th><td>".Date('Y m-d')." </td></tr>";
				  $body .= "</table>";
				  $permission = "view_disposal_plans";
				  
				  $this->session->set_userdata('level','ppda');
				   print_r($result);
 				  push_permission_ppda($titles,$body,$level,$permission);
 				  
 				  
 				  
        	}
        	else
        	{
        	
        	print_r("1");
        	}
		#print_r($result);
		 
	}
	
	
	#beb review details
	function add_review_details(){
		#print_r($_POST);

		$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		$data['bid'] = mysql_real_escape_string($_POST['review']);
		$bid = mysql_real_escape_string($_POST['review']);
		$data['information'] = $this->db->query("SELECT receipts.*,bidinvitations.procurement_ref_no FROM receipts    INNER JOIN bidinvitations  ON receipts.bid_id = bidinvitations.id WHERE bidinvitations.id = ".$bid." ")->result_array() or die("".mysql_error());
		 $data['type'] = 'add_review';
		$data['form_title'] = 'Add Best Evaluated Bidder  Review';
		
		$this->load->view('bids/add_bebreview', $data);
	}
	function savebeb_review()
	{
		#print_r($_POST);
		//insert_beb_review
		$bidid = mysql_real_escape_string($_POST['bidid']);
		$finalreview = mysql_real_escape_string($_POST['finalreview']);
		$dateofcomplaint = date('Y-m-d',strtotime($_POST['dateofcomplaint']));
		$dateofentityresponse = date('Y-m-d',strtotime($_POST['dateofentityresponse']));
		$finaldetails = mysql_real_escape_string($_POST['finaldetails']);
		$dateoffinaldecision = date('Y-m-d',strtotime($_POST['dateoffinaldecision']));
		$lotid = mysql_real_escape_string($_POST['lotid']);
 	    $userid = $this->session->userdata['userid'];
		$data = array(
			'bidid' =>$bidid,
			'finalreview' =>$finalreview,
			'dateofcomplaint' =>$dateofcomplaint,
			'dateofentityresponse' =>$dateofentityresponse,
			'finaldetails' =>$finaldetails,
			'dateoffinaldecision' =>$dateoffinaldecision,
			'author' =>$userid,
			'isactive' =>'Y',
			'lotid'=> $lotid
			 );
		$query = $this->db->query($this->Query_reader->get_query_by_code('insert_beb_review',$data ));

if($query)
	print_r("1");

			}
	function fetch_admin_review()
	{$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		#print_r($_POST);
		$_POST['bidid'] = mysql_real_escape_string($_POST['viewreview']);
		$data['information'] = $this-> Proc_m -> fetch_admin_review($_POST,$data);
		#print_r( $data['admin_reviews'] );

		$data['type'] = 'view_review';
		$data['form_title'] = 'View Best Evaluated Bidder  Reviews';
		
		$this->load->view('bids/add_bebreview', $data);
		
	}
	function delete_admin_review()
	{
		#print_r($_POST);
		$id = mysql_real_escape_string($_POST['id']);
		$str = "UPDATE beb_review_details SET isactive = 'N' WHERE id ='".$id."' ";
		#print_r($str);
		$query = $this->db->query("UPDATE beb_review_details SET isactive = 'N' WHERE id ='".$id."' ") or die("".mysql_error()) ;
		if($query)
		{
			return 1;
		}
	}
	
	
function add_review_details_lots(){
	 

	    $urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		$data = handle_redirected_msgs($this, $data);
		$data['bestbebid'] = mysql_real_escape_string($_POST['review']);
		$receiptid = mysql_real_escape_string($_POST['receiptid']);
		$data['information'] = $this->db->query("SELECT receipts.*,bidinvitations.procurement_ref_no FROM receipts    INNER JOIN bidinvitations  ON receipts.bid_id = bidinvitations.id WHERE receipts.receiptid = ".$receiptid." ")->result_array() or die("".mysql_error());
		if(!empty($data['information']))
		$data['bid'] = $data['information'][0]['bid_id'];
		 
	    $data['type'] = 'add_review';
		$data['level'] = 'lots';
		$data['lotid'] = mysql_real_escape_string($_POST['lotid']);
		$data['receiptid'] = mysql_real_escape_string($_POST['receiptid']);
		$data['form_title'] = 'Add Best Evaluated Bidder  Review';
		
		$this->load->view('bids/add_bebreview', $data);


}

function fetch_admin_review_lots()
	{
		#print_r($_POST);
		$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
		$data = assign_to_data($urldata);
		$data = add_msg_if_any($this, $data);
		#print_r($_POST);
		$_POST['lotid'] = mysql_real_escape_string($_POST['lotid']);
		$data['information'] = $this-> Proc_m -> fetch_admin_review($_POST,$data);
		#print_r( $data['admin_reviews'] );

		$data['type'] = 'view_review';
		$data['form_title'] = 'View Best Evaluated Bidder  Reviews';
		
	 	$this->load->view('bids/add_bebreview', $data);
		
	}

	




}


/* End of file admin.php */
/* Location: ./system/application/controllers/admin.php */
?>
