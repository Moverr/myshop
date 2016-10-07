<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Disposal_m extends MY_Model
{
	function __construct()
	{
		$this->load->model('Query_reader', 'Query_reader', TRUE);
		$this->load->model('sys_file', 'sysfile', TRUE);
		parent::__construct();
		#$this->Query_reader->load_queries_into_cache();
	}
	public $_tablename='disposal_plans';
	public $_primary_key='id';


	// =======================================================================//
	// ! ACTIVE DISPOSAL RECORDS
	// =======================================================================//
	function get_active_disposal_records($pde = '',$financial_year='',$count='',$from='',$to='')
	{

		$this->db->cache_on();
		$this->db->distinct('DR.disposal_serial_no');
		$this->db->query('SET SQL_BIG_SELECTS=1');

		$this->db->select('
pdes.pdeid,
  DP.financial_year,
  pdes.pdename,
  DR.subject_of_disposal,
  DR.amount,
  DR.date_of_approval,
  DR.method_of_disposal,
  DR.dateofaoapproval,
  DR.quantity,
  DM.method,
  DM.status,
  DR.id,
  DR.disposal_plan,
  DR.disposal_serial_no,
  DR.currence,
  DR.asset_location,
  DR.strategic_asset,
  DR.dateadded,
  DR.author,
  DR.isactive,
  DC.beneficiary,
  DC.contractamount,
  DC.datesigned,
  DC.currency,
  providers.providernames

',false);
		#=====================
		# FROM TABLE
		#=====================

		$this->db->from('disposal_record AS DR');

		#=====================
		# START THE JOINS
		#=====================
		$this->db->join('disposal_plans AS DP', 'DP.id = DR.disposal_plan');
		$this->db->join('users', 'users.pde = DR.pde');
		$this->db->join('pdes', 'pdes.pdeid= users.pde');
		$this->db->join('disposal_method AS DM', 'DM.id = DR.method_of_disposal');
		$this->db->join('disposal_contract AS DC', 'DR.id = DC.disposalrecord','left');
		$this->db->join('providers', 'DC.beneficiary = providers.providerid','left');

		#=====================
		# START THE WHERES
		#=====================

		$this->db->where('DR.isactive', 'Y');
		$this->db->where('DP.isactive', 'Y');
		$this->db->where_not_in('DR.id','(SELECT disposal_bid_invitation.disposal_record FROM disposal_bid_invitation WHERE disposal_bid_invitation.isactive = \'Y\')');


		# if pde is provided
		if ($pde) {
			$this->db->where('pdes.pdeid', $pde);
		}

		# if financial year is provided
		if($financial_year){
			$this->db->where('DP.financial_year LIKE','%'.$financial_year.'%');
			//$this->db->where('DP.financial_year', $financial_year);

		}else{
			if($from&&$to){
				# use current financial year
				$this->db->where('DR.dateofaoapproval >=', $from);
				$this->db->where('DR.dateofaoapproval <=', $to);
			}else{
				# use current financial year
				$this->db->where('DP.financial_year LIKE ', '%'.$financial_year.'%');
			}


		}

		$this->db->order_by('DR.dateadded','desc');

		$query = $this->db->get();

		$this->db->cache_off();
   #     print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
      # 	print_array($query->result_array());
       # exit;

		$res=$query->result_array();

		if(strtoupper($count)=='Y') {
			$res = $query->num_rows();
		}



		return $res;
	}


	// =======================================================================//
	// ! DISPOSAL BID INVITATIONS
	// =======================================================================//
	function get_disposal_bid_invitations($pde = '',$financial_year='',$count='',$from='',$to='')
	{

		$this->db->cache_on();
		$this->db->distinct('DR.disposal_serial_no');
		$this->db->query('SET SQL_BIG_SELECTS=1');

		$this->db->select('
disposal_record.disposal_serial_no,
  disposal_bid_invitation.bid_document_issue_date,
  disposal_record.asset_location,
  disposal_bid_invitation.bid_opening_date,
  disposal_bid_invitation.bid_duration,
  disposal_record.subject_of_disposal,
  disposal_bid_invitation.isactive,
  disposal_bid_invitation.author,
  disposal_bid_invitation.bid_duration AS expr1,
  disposal_bid_invitation.date_of_approval_form28,
  disposal_bid_invitation.date_of_initiation_form28,
  disposal_bid_invitation.cc_approval_date,
  disposal_bid_invitation.dateadded,
  disposal_bid_invitation.disposal_ref_no,
  disposal_bid_invitation.id,
  disposal_bid_invitation.bid_openning_date_time,
  disposal_bid_invitation.bid_evaluation_from,
  disposal_bid_invitation.bid_evaluation_to,
  disposal_bid_invitation.inspect_openning_date,
  disposal_bid_invitation.inspect_openning_date_time,
  disposal_bid_invitation.inspect_close_date,
  disposal_bid_invitation.inspect_close_date_time,
  disposal_bid_invitation.deadline_for_submition,
  disposal_bid_invitation.documents_inspection_address,
  disposal_bid_invitation.contract_award_date,
  disposal_bid_invitation.display_of_beb_notice,
  disposal_bid_invitation.bid_opening_address,
  disposal_bid_invitation.documents_address_issue,
  disposal_bid_invitation.non_refundable_fee,
  pdes.pdename,
  pdes.abbreviation,
  (SELECT
    CONCAT(users.firstname, \' \', users.lastname) AS approver_fullname
  FROM users
  WHERE users.userid = disposal_bid_invitation.author
  LIMIT 1) AS author_fullname,
  disposal_record.currence,
  disposal_record.amount,
  disposal_record.dateofaoapproval,
  disposal_plans.financial_year,
  disposal_method.method
',false);
		#=====================
		# FROM TABLE
		#=====================

		$this->db->from('disposal_bid_invitation');

		#=====================
		# START THE JOINS
		#=====================
		$this->db->join('disposal_record', 'disposal_bid_invitation.disposal_record = disposal_record.id');
		$this->db->join('disposal_plans', 'disposal_record.disposal_plan = disposal_plans.id');
		$this->db->join('users', 'users.pde = disposal_plans.pde_id');
		$this->db->join('pdes', 'disposal_record.pde = pdes.pdeid');
		$this->db->join('disposal_method', 'disposal_record.method_of_disposal = disposal_method.id');
		#=====================
		# START THE WHERES
		#=====================

		$this->db->where('disposal_bid_invitation.isactive', 'Y');
		$this->db->where('disposal_record.isactive', 'Y');
		$this->db->where('disposal_plans.isactive', 'Y');
		# if pde is provided
		if ($pde) {
			$this->db->where('pdes.pdeid', $pde);
		}

		# if financial year is provided
		if($financial_year){
			$this->db->where('disposal_plans.financial_year LIKE','%'.$financial_year.'%');
			//$this->db->where('DP.financial_year', $financial_year);

		}else{
			if($from&&$to){
				# use current financial year
				$this->db->where('disposal_bid_invitation.dateadded >=', $from);
				$this->db->where('disposal_bid_invitation.dateadded <=', $to);
			}else{
				# use current financial year
				$this->db->where('disposal_bid_invitation.financial_year LIKE ', '%'.$financial_year.'%');
			}


		}

		$this->db->order_by('disposal_bid_invitation.dateadded','desc');

		$query = $this->db->get();

		$this->db->cache_off();
//		 print_array($this->db->last_query());
//         print_array($this->db->_error_message());
//         print_array(count($query->result_array()));
//		 print_array($query->result_array());
//		 exit;

		$res=$query->result_array();

		if(strtoupper($count)=='Y') {
			$res = $query->num_rows();
		}



		return $res;
	}


	// =======================================================================//
	// ! PAGINATED DISPOSAL NOTICES
	// =======================================================================//
	function get_paginated_disposal_notices($limit, $start, $term)
	{
		$this->db->cache_on();
		$this->db->distinct('DR.disposal_serial_no');
		$this->db->query('SET SQL_BIG_SELECTS=1');

		$this->db->select('
disposal_record.disposal_serial_no,
  disposal_bid_invitation.bid_document_issue_date,
  disposal_record.asset_location,
  disposal_bid_invitation.bid_opening_date,
  disposal_bid_invitation.bid_duration,
  disposal_record.subject_of_disposal,
  disposal_bid_invitation.isactive,
  disposal_bid_invitation.author,
  disposal_bid_invitation.bid_duration AS expr1,
  disposal_bid_invitation.date_of_approval_form28,
  disposal_bid_invitation.date_of_initiation_form28,
  disposal_bid_invitation.cc_approval_date,
  disposal_bid_invitation.dateadded,
  disposal_bid_invitation.disposal_ref_no,
  disposal_bid_invitation.id,
  disposal_bid_invitation.bid_openning_date_time,
  disposal_bid_invitation.bid_evaluation_from,
  disposal_bid_invitation.bid_evaluation_to,
  disposal_bid_invitation.inspect_openning_date,
  disposal_bid_invitation.inspect_openning_date_time,
  disposal_bid_invitation.inspect_close_date,
  disposal_bid_invitation.inspect_close_date_time,
  disposal_bid_invitation.deadline_for_submition,
  disposal_bid_invitation.documents_inspection_address,
  disposal_bid_invitation.contract_award_date,
  disposal_bid_invitation.display_of_beb_notice,
  disposal_bid_invitation.bid_opening_address,
  disposal_bid_invitation.documents_address_issue,
  disposal_bid_invitation.non_refundable_fee,
  pdes.pdename,
  pdes.abbreviation,
  (SELECT
    CONCAT(users.firstname, \' \', users.lastname) AS approver_fullname
  FROM users
  WHERE users.userid = disposal_bid_invitation.author
  LIMIT 1) AS author_fullname,
  disposal_record.currence,
  disposal_record.amount,
  disposal_record.dateofaoapproval,
  disposal_plans.financial_year,
  disposal_method.method
',false);
		#=====================
		# FROM TABLE
		#=====================

		$this->db->from('disposal_bid_invitation');

		#=====================
		# START THE JOINS
		#=====================
		$this->db->join('disposal_record', 'disposal_bid_invitation.disposal_record = disposal_record.id');
		$this->db->join('disposal_plans', 'disposal_record.disposal_plan = disposal_plans.id');
		$this->db->join('users', 'users.pde = disposal_plans.pde_id');
		$this->db->join('pdes', 'disposal_record.pde = pdes.pdeid');
		$this->db->join('disposal_method', 'disposal_record.method_of_disposal = disposal_method.id');
		#=====================
		# START THE WHERES
		#=====================

		$this->db->where('disposal_bid_invitation.isactive', 'Y');
		$this->db->where('disposal_record.isactive', 'Y');
		$this->db->where('disposal_plans.isactive', 'Y');

		if ($term) {
			$this->db->where('disposal_record.subject_of_disposal LIKE', '%'.$term.'%');
			$this->db->or_where('pdes.pdename LIKE', '%'.$term.'%');
			$this->db->or_where('disposal_plans.financial_year LIKE', '%'.$term.'%');
			$this->db->or_where('disposal_bid_invitation.disposal_ref_no LIKE', '%'.$term.'%');

		}

		$this->db->limit($limit, $start);



		$this->db->order_by('disposal_bid_invitation.dateadded','desc');

		$query = $this->db->get();
		$this->db->cache_off();




//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());


		return $query->result_array();


	}




	#new pde
	function insert_disposal($post){


		$currency = mysql_real_escape_string($post['currency']);
		$disposal_serial_number =  mysql_real_escape_string($post['disposal_serial_number']);
		$subject_of_disposal =  mysql_real_escape_string($post['subject_of_disposal']);
		$asset_location =  mysql_real_escape_string($post['asset_location']);
		$amount =  mysql_real_escape_string($post['amount']);
		$amount = str_replace(',', '', $amount);
		$disposal_plan = mysql_real_escape_string($post['disposal_plan']);
		$assetquantity = mysql_real_escape_string($post['assetquantity']);

		$strategic_asset  = mysql_real_escape_string($post['strategic_asset']);

		$strategic_asseta = 'N';

		$date_of_approval = '';

		if($strategic_asset == 'Yes' )
		{
			$strategic_asseta = 'Y';
			#date of Approval by PSST
			$date_of_approval = 	custom_date_format('Y-m-d',$post['date_of_approval']);
		}

		$date_of_aoapproval =custom_date_format('Y-m-d',$post['date_of_aoapproval']);
		$method_of_disposal = mysql_real_escape_string($post['method_of_disposal']);


		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');

		#print_r($strategic_asseta); exit();
		$data = array( 'pde' => $pde ,
				'disposalplan' => $disposal_plan,
				'disposalserialno' => $disposal_serial_number,
				'subjectofdisposal' =>$subject_of_disposal,
				'assetlocation' => $asset_location,
				'amount' => $amount,
				'currence' => $currency,
				'strategicasset' => $strategic_asseta,
				'dateofapproval' => $date_of_approval,
				'methodofdisposal' =>  $method_of_disposal,
				'isactive' => 'Y',
				'author' => $userid,
				'dateadded' => date("Y-m-d H:i:s"),
				'dateofaoapproval' => $date_of_aoapproval  ,
				'ASSETQUANTITY' => $assetquantity

		);



		//  ASSETQUANTITY

		$query = $this->Query_reader->get_query_by_code('add_disposal_record',  $data);
		# print_r($query); exit();
		$result = $this->db->query($query);
		# if uploaded detailed plan insert into the db::
		$dispsalplan =  $this->db->insert_id();
		$this->session->set_userdata('usave', 'You have successfully Saved a disposal record  '.$disposal_serial_number.' ' );
		return 1;

	}

	#update disposal record ::
	function update_disposal($post){

		$currency = mysql_real_escape_string($post['currency']);
		$disposal_serial_number =  mysql_real_escape_string($post['disposal_serial_number']);
		$subject_of_disposal =  mysql_real_escape_string($post['subject_of_disposal']);
		$asset_location =  mysql_real_escape_string($post['asset_location']);
		$amount =  mysql_real_escape_string($post['amount']);
		$disposal_plan = mysql_real_escape_string($post['disposal_plan']);
		$assetquantity = mysql_real_escape_string($post['assetquantity']);

		$strategic_asset  = mysql_real_escape_string($post['strategic_asset']);
		$strategic_asseta = 'N';

		if($strategic_asset == 'Yes'){
			$date_of_approval = 	custom_date_format('Y-m-d',$post['date_of_approval']);
			$strategic_asseta = 'Y';
		}
		else
			$date_of_approval = '';




		$method_of_disposal = mysql_real_escape_string($post['method_of_disposal']);
		$date_of_aoapproval = custom_date_format('Y-m-d',$post['date_of_aoapproval']);



		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');
		$disposalrecodid = $post['disposalrecordid'];

		$data = array( 'pde' => $pde ,
				'disposalplan' => $disposal_plan,
				'disposalserialno' => $disposal_serial_number,
				'subjectofdisposal' =>$subject_of_disposal,
				'assetlocation' => $asset_location,
				'amount' => $amount,
				'currence' => $currency,
				'strategicasset' => $strategic_asseta,
				'dateofapproval' => $date_of_approval,
				'methodofdisposal' =>  $method_of_disposal,
				'isactive' => 'Y',
				'author' => $userid,
				'dateadded' => date("Y-m-d H:i:s"),
				'dateofaoapproval' => $date_of_aoapproval ,
				'id'=> $disposalrecodid,
				'ASSETQUANTITY' => $assetquantity

		);

		$query = $this->Query_reader->get_query_by_code('update_disposal',  $data);
		#print_r($query); exit();
		$result = $this->db->query($query);
		// if uploaded detailed plan insert into the db::
		$dispsalplan =  $this->db->insert_id();
		$this->session->set_userdata('usave', 'You have successfully updated a disposal record  '.$disposal_serial_number.' ' );

		return 1;
	}


	#check if the disposal record already exists
	function check_disposal_record($post)
	{
		$disposal_ref_no  = $post['itemcheck'];
		$query = mysql_query("select * from disposal_record where disposal_ref_no = '".$disposal_ref_no."'");
		$rows = mysql_num_rows($query);
		return $rows;

	}
	#fetch disposal records
	function fetch_disposal_records($data,$searchstring)
	{
		# $query = $this->Query_reader->get_query_by_code('fetch_disposal_records',   array('searchstring'=>$searchstring,'limittext'=>''));
		# print_r($query); exit();
		$result = paginate_list($this, $data, 'fetch_disposal_records', array('searchstring'=>$searchstring),10);

		return $result;
	}
	function search_disposal_records($data,$search){
		$searchstring = "1 and 1 and disposal_record.disposal_ref_no like '%".$search."%' or disposal_record.subject_of_disposal  like '%".$search."%'  or  disposal_record.asset_location like '%".$search."%'  or   disposal_record.reserve_price='".$search."'  or disposal_record.currence  like '%".$search."%'  or  disposal_record.dateadded  like '%".$search."%'  or  pdes.abbreviation    like '%".$search."%'  or pdes.pdename    like '%".$search."%'  order by   disposal_record.dateadded DESC";
		#$searchstring = "1 and 1 order by   disposal_record.dateadded DESC";
		$result = paginate_list($this, $data, 'fetch_disposal_records', array('searchstring'=>$searchstring),10);
		return $result;
	}

	function fetch_count_disposal_records($data,$searchstring){
		#	 $query = $this->Query_reader->get_query_by_code('fetch_count_disposal_records',   array('searchstring'=>$searchstring,'limittext'=>''));
		#  print_r($query); exit();

		$result = paginate_list($this, $data, 'fetch_count_disposal_records', array('searchstring'=>$searchstring),10);

		return $result;
	}
	
	
	#Insert Bid Invitation 
	function insert_bid_invitation($post){

		#print_r($post); exit();
		$disposal_id = mysql_real_escape_string($post['disposal_id']);
		$disposal_reference_no =   mysql_real_escape_string($post['disposal_ref_no']);
		#$post['bid_document_issue_date'];

		$date_of_approval_form28 =  (!empty($post['date_of_approval_form28']) ? custom_date_format('Y-m-d',$post['date_of_approval_form28']) : '');
		$cc_date_of_approval =   (!empty($post['cc_approval_date']) ? custom_date_format('Y-m-d',$post['cc_approval_date']) : '' );

		$bid_document_issue_date =  (!empty($post['bid_document_issue_date']) ? custom_date_format('Y-m-d',$post['bid_document_issue_date']) : '' );
		$deadline_for_submition =  (!empty($post['deadline_for_submition']) ? custom_date_format('Y-m-d',$post['deadline_for_submition']) : '' );

		$documents_inspection_address = mysql_real_escape_string($post['documents_inspection_address']);
		$documents_address_issue = mysql_real_escape_string($post['documents_address_issue']);
		$bid_opening_address = mysql_real_escape_string($post['bid_opening_address']);
		$bid_openning_date = (!empty($post['bid_opening_date']) ? custom_date_format('Y-m-d',$post['bid_opening_date']) : '' );
		$bid_openning_date_time = (!empty($post['bid_opening_date_time']) ? custom_date_format('H:i:s',$post['bid_opening_date_time']) : '' );
		$bid_evaluation_from = (!empty($post['bid_evaluation_from']) ? custom_date_format('Y-m-d',$post['bid_evaluation_from']) : '' );
		$bid_evaluation_to = (!empty($post['bid_evaluation_to']) ? custom_date_format('Y-m-d',$post['bid_evaluation_to']) : '' );
		$display_of_beb_notice =  custom_date_format('Y-m-d',$_POST['display_of_beb_notice']);
		$contract_award_date = (!empty($post['contract_award_date']) ? custom_date_format('Y-m-d',$post['contract_award_date']) : '' );

		$non_refundable_fee =  !empty($post['non_refundable_fee'])? removeCommas($post['non_refundable_fee']) :'' ;
		
		$dateadded = Date('Y-m-d');
		$author =  $this->session->userdata('userid');
		# documentissuedate' => $days_between,
		
		#Inspection Opening Date 
		$inspect_openning_date =  (!empty($post['inspect_openning_date']) ? custom_date_format('Y-m-d',$post['inspect_openning_date']) : '');
		$inspect_openning_date_time = (!empty($post['inspect_openning_date_time']) ? custom_date_format('H:i:s',$post['inspect_openning_date_time']) : '' );
		
		#Inspection Close Date
		$inspect_close_date =  (!empty($post['inspect_close_date']) ? custom_date_format('Y-m-d',$post['inspect_close_date']) : '');
		$inspect_close_date_time = (!empty($post['inspect_close_date_time']) ? custom_date_format('H:i:s',$post['inspect_close_date_time']) : '' );
	
		#Non Refundable Fee
		$non_refundable_fee  = $post['non_refundable_fee'];
		#non Refundable Currency 
		$currency = $post['currency'];
		
		# Docuements Address Delivered TO 
		$documents_address_delivered_to = $post['documents_address_delivered_to'];
		
		/*
		  #Remaining Links
		  non_refundable_fee
		  currency		  
		
		*/
		
		
		
		$data = array(

					'disposal_record'=> $disposal_id,
					'disposalrefno'=>$disposal_reference_no,
					'dateofapprovalform28'=>$date_of_approval_form28,
					'ccapprovaldate' => $cc_date_of_approval,
					'documentissuedate'=>$bid_document_issue_date,
					'deadline_for_submition'=> $deadline_for_submition,
					'documents_inspection_address'=>$documents_inspection_address,
					'documents_address_issue'=>$documents_address_issue,
					'bid_opening_address' => $bid_opening_address,
					'bid_openning_date'=> $bid_openning_date,
					'bid_openning_date'=> $bid_openning_date,
					'bid_openning_date_time'=> $bid_openning_date_time,
					'bid_evaluation_from' => $bid_evaluation_from,
					'bid_evaluation_to' => $bid_evaluation_to,
					'display_of_beb_notice' =>$display_of_beb_notice,
					'contract_award_date' => $contract_award_date ,
					'INS_OPENNING_DATE'=> $inspect_openning_date,
					'INS_OPENNING_DATE_TIME' => $inspect_openning_date_time,
					'INS_CLOSE_DATE' =>  $inspect_close_date,
					'INS_CLOSE_DATE_TIME' => $inspect_close_date_time,
					'isactive'=>'Y',
					'dateadded'=>$dateadded,
					'author'=>$author,
					'non_refundable_fee' =>$non_refundable_fee,
					'documents_address_delivered_to'=>$documents_address_delivered_to

		);

		if(!empty($post['update']))
		{
			$data['id'] = $post['update'];
			$query = $this->Query_reader->get_query_by_code('update_disposal_bids',  $data);
			$result = $this->db->query($query);
			$this->session->set_userdata('usave', 'You have successfully updated   Bid Invitation ');
		}
		else{
			$query = $this->Query_reader->get_query_by_code('add_disposal_bids',  $data);
			#print_r($query);
			#exit();
			$result = $this->db->query($query);

			$this->session->set_userdata('usave', 'You have successfully Saved   Bid Invitation ');
		}
		
	 #	exit($this->db->last_query());


		return 1;

	}



	function fetch_active_disposal_records($data,$searchstring){
		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');
		// and disposal_record.isactive="Y" and disposal_plans.isactive="Y" and  users.pde = '.$pde.'
		#$query = $this->Query_reader->get_query_by_code('active_disposal_records',array('searchstring'=>$searchstring.'','limittext'=>'limit 10',));
		#print_r($query); exit();
		$result = paginate_list($this, $data, 'active_disposal_records', array('searchstring'=> $searchstring.' '),1000);

		return $result;
	}

	#disposal contract
	function fetchdisposalrecords_contract($data,$searchstring)
	{
		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');
		#$query = $this->Query_reader->get_query_by_code('active_disposal_records',array('searchstring'=>$searchstring,'limittext'=>'limit 10'));
		#print_r($query); exit();
		$result = paginate_list($this, $data, 'active_disposal_records', array('searchstring'=>$searchstring),1000);

		return $result;
	}


	#fetch disposal records
	function fetch_disposal_bid_invitations($data,$searchstring)
	{
		#$query = $this->Query_reader->get_query_by_code('view_disposal_bid_invitations',array('searchstring'=>$searchstring,'limittext'=>' limit 10','ORDERBY'=>''));
		#print_r($query); exit();
		$result = paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring,'ORDERBY'=>'  disposal_bid_invitation.dateadded DESC '),10);

		return $result;
	}

	#SEARCH BID INVITATIONS
	function search_disposal_bid_invitations($data,$search){
		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');
		$searchstring = "1 and 1 and disposal_record.disposal_ref_no like '%".$search."%' or disposal_record.subject_of_disposal  like '%".$search."%'  or   disposal_bid_invitation.bid_document_issue_date like '%".$search."%'  or    disposal_bid_invitation.bid_opening_date like'%".$search."%'  or
	disposal_bid_invitation.subject_of_disposal like'%".$search."%'  or
		disposal_bid_invitation.description like'%".$search."%'  or
		   disposal_bid_invitation.bid_duration ='".$search."'  or  disposal_bid_invitation.dateadded  like '%".$search."%'  and users.pde ='".$pde."' and users.userid='".$userid."'   order by   disposal_bid_invitation.dateadded DESC";
		#$searchstring = "1 and 1 order by   disposal_record.dateadded DESC";
		$result = paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring),10);
		return $result;
	}

	#SAVE BID RESPONSE
	function savebidresponse($post){
		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');
		$dispossalrefno = mysql_real_escape_string($post['dispossalrefno']);
		$serviceprovider = mysql_real_escape_string($post['serviceprovider']);
		$nationality = mysql_real_escape_string($post['nationality']);
		$readoutprice = mysql_real_escape_string($post['readoutprice']);
		$datesubmitted =  date('Y-m-d',mysql_real_escape_string(strtotime($post['datesubmitted'])));
		$receivedby = mysql_real_escape_string($post['receivedby']);
		//provider id
		$providerid = 0;

		// save information
		// check if name exists in the  providers table

		$query = mysql_query("SELECT * FROM providers where providernames  like '".$serviceprovider."'  limit 1") or die("".mysql_error());
		$providenms  ='';
		if(mysql_num_rows($query) > 0)
		{
			while ($res = mysql_fetch_array($query)) {
				# code...
				$providerid = $res['providerid'];
				$providenms = $res['providernames'];
			}
		}
		else{
			$query = mysql_query("INSERT INTO providers(providernames) values ('".$serviceprovider."') ") or die("".mysql_error());
			$providerid = mysql_insert_id();
		}
		//get bid information ::
		//	$bidid  = 0;
		$query = mysql_query("SELECT a.* FROM disposal_bid_invitation   as a  inner join disposal_record as b on a.disposal_ref_no = b.id where b.disposal_ref_no like '".$dispossalrefno."' limit 1") ;
		# print_r($query);
		while ($res = mysql_fetch_array($query)) {
			# code...
			$bidid = $res['id'];
		}
		# exit( '3:'.$dispossalrefno);
		//check to see if the provider already submitted
		$query = mysql_query("SELECT * FROM bid_response WHERE  provider_id = ".$providerid." and  bid_invitation = ".$bidid."") or die("".mysql_error());

		if(mysql_num_rows($query) > 0){
			return "3:".$providenms." Have a bid proposal existing on  Disposal Ref No - ".$dispossalrefno ;
		}
		else{
			//save the bid
			$query =mysql_query("INSERT INTO bid_response(bid_invitation,provider_id,nationality,readoutprice,receivedby,author,datesubmitted) values('".$bidid."','".$providerid."','".$nationality."','".$readoutprice."','".$receivedby."','".$userid."','".$datesubmitted."') ") or die("".mysql_error());
			// echo $query;
			// exit();
			if($query)
			{
				return 1;
			}else
			{
				return 0 ;
			}




			//bid response information //
			return $post;
		}


	}
	#UPDATE BID RESPONSE
	function update_bid_response(){}
	function save_disposal_plan($post){


		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');

 
		$data = array( 'pde' => $pde,
				'financialyear' =>   str_replace(' ', '',trim($post['start_year']).'-'.trim($post['end_year'])),
				'author' => $userid 
		);


		$count = $this->db->query("SELECT COUNT(*) as num  FROM `disposal_plans` WHERE  `financial_year` like '".trim($post['start_year']).'-'.trim($post['end_year'])."' AND isactive = 'Y' ") -> result_array();
		#print_r($count); exit();
		if(!empty($count) && $count['num'] > 0 )
		{
			return 0;
		}
		else
		{
			$query = $this->Query_reader->get_query_by_code('add_disposal_plan',  $data);
			$result = $this->db->query($query);
			$this->session->set_userdata('usave', 'You have successfully created the   ' .  $post['start_year'].'-'.$post['end_year'] . ' Disposal Plan');
			$insertid = $this->db->insert_id();
			return $insertid;
		}

	}

	#update disposal plan
	function update_disposal_plan($post,$id){


		$pde =  $this->session->userdata('pdeid');
		$userid =  $this->session->userdata('userid');


		$data = array( 'pde' => $pde,
				'financialyear' =>   str_replace(' ', '',trim($post['start_year']).'-'.trim($post['end_year'])),

				'author' => $userid,

				'id'=> $id
		);


		$query = $this->Query_reader->get_query_by_code('update_disposal_plan',  $data);
		#print_r($query); exit();
		$result = $this->db->query($query);
		$this->session->set_userdata('usave', 'You have successfully updated  the '.$post['title'].' ' .  $post['start_year'].'-'.$post['end_year'] . ' Disposal Plan');

		return 1;

	}



	function fetch_disposal_plans($data,$searchstring)
	{
		# $data = array('searchstring' => $searchstring,'limittext'=>' limit 10');
		# $query = $this->Query_reader->get_query_by_code('view_disposal_plans',  $data);
		# print_r($query);
		# exit();
		$result = paginate_list($this, $data, 'view_disposal_plans', array('searchstring'=>$searchstring),10);
		# print_r($result); exit();
		return $result;
	}
	function fetchdisposal_plans($data,$searchstring)
	{

		$datar = array('searchstring' => $searchstring,'limittext'=>' ');
		$query = $this->Query_reader->get_query_by_code('view_disposal_plans',  $datar);
		#print_r($query);
		#exit();
		$result = $this->db->query($query)->result_array();
		return $result;

	}
	#fetch Dipsosal Response
	function fetch_disposal_reference($data,$searchstring)
	{
		$result = paginate_list($this, $data, 'fetch_bid_responses', array('searchstring'=>$searchstring),10);

		return $result;
	}
	function fetch_disposal_methods($data,$searchstring,$limittext)
	{
		$result = paginate_list($this, $data, 'fetch_disposal_methods', array('searchstring'=>$searchstring),$limittext);
		#$query = $this->Query_reader->get_query_by_code('fetch_disposal_methods',  array('searchstring'=>$searchstring,'limittext'=>''));
		#print_r($query); exit();
		return $result;

	}
	function fetch_disposal_details($data,$searchstring,$limittext){
		$result = paginate_list($this, $data, 'active_disposal_reference_numbers', array('searchstring'=>$searchstring),$limittext);
		# $query = $this->Query_reader->get_query_by_code('active_disposal_reference_numbers',  array('searchstring'=>$searchstring,'limittext'=>''));
		# print_r($query); exit();
		return $result;
	}

	# FETCH Best Evaluated Bidder  LIST
	function fetch_beb_list($idx = 0,$data=array()){

		switch ($idx) {
			case 0:

				$searchstring = '';
				$userid = $this->session->userdata['userid'];
				if ($this->session->userdata('isadmin') == 'N') {
					 $pde =  $this->session->userdata('pdeid');		
					$searchstring  = ' AND 	users.pde = '.$pde.' AND disposal_plans.isactive = "Y" AND disposal_bid_invitation.isactive = "Y"   ';
				}



				$result = paginate_list($this, $data, 'view_disposal_bebs',  array('SEARCHSTRING' => '  1 and 1  '.$searchstring.'' ),10);

				#$query = $this->Query_reader->get_query_by_code('view_disposal_bebs', array('SEARCHSTRING' => '  1 and 1 and 	users.userid = '.$userid.' ORDER BY bestevaluatedbidder.dateadded DESC ' ,'limittext' => ' '));
				#print_r($query); exit();
				#$result = $this->db->query($query)->result_array();
				return $result;


				return $result;

				break;

			default:
				# code...
				break;
		}
	}
	function checkfinancialyears($post)
	{
		$yeart = $string = preg_replace('/\s+/', '', mysql_real_escape_string($post['financialyear']));
		$decission = $this->uri->segment(3);
		$userid = $this->session->userdata['userid'];
		$pde = mysql_query("select * from  users where userid =".$userid);
		$q = mysql_fetch_array($pde);
		$pdeid = $q['pde'];

		#  print_r($decission); exit();
		$query = '';
		switch ($decission) {
			case 'update':
				$id = $this->uri->segment(4);
				$datar = array('year' => $yeart.'-','SEARCHSTTRING'=>' AND disposal_plans.isactive ="Y" AND disposal_plans.id != '.$id,'PDE'=>$pdeid);
				$query = $this->Query_reader->get_query_by_code('checkfinancial_year',  $datar);
				$result = $this->db->query($query)->result_array();

				if(count($result) > 0 )
				{
					return 1;
				}
				else{
					return 0;
				}
				break;

			default:
				$datar = array('year' => $yeart.'-','SEARCHSTTRING'=>' AND disposal_plans.isactive ="Y" ','PDE'=>$pdeid);
				$query = $this->Query_reader->get_query_by_code('checkfinancial_year',  $datar);
				# print_r($query); exit();
				$result = $this->db->query($query)->result_array();

				if(count($result) > 0 )
				{
					return 1;
				}
				else{
					return 0;
				}
				break;
		}



	}

	function getserialnumber($pde)
	{
		$pdeinfo = $this->db->query("SELECT * FROM pdes where pdeid = ".$pde." limit 1 ")->result_array();
		$code = '';
		$code = $pdeinfo[0]['abbreviation'];
		$serial = $code.'/disposal/';
		return $serial;

	}

	# 3 in one del archive and restore
	function remove_restore_disposalplan($type,$pdetypeid){

#print_r($type); exit();
		switch ($type) {

			case 'delete':
				$query = $this->Query_reader->get_query_by_code('archive_diposal_plans', array('ID'=>$pdetypeid,'ISACTIVE'=>'N'));
				# print_r($query); exit();
				$result = $this->db->query($query);
				if($result)
					return 1;
				break;
			default:
				# code...
				break;
		}

	}

	# 3 in one del archive and restore
	function remove_restore_disposalplan_record($type,$pdetypeid){

		switch ($type) {

			case 'delete':
				$query = $this->Query_reader->get_query_by_code('archive_diposal_plans_records', array('ID'=>$pdetypeid,'ISACTIVE'=>'N'));
				$result = $this->db->query($query);
				if($result)
					return 1;
				break;
			default:
				# code...
				break;
		}

	}



	public function get_disposal_plan_info($id='', $param='')
	{

		//if NO ID
		if($id=='')
		{
			return NULL;
		}
		else
		{
			//get user info
			$query=$this->db->select()->from($this->_tablename)->where('id',$id)->get();

			if($query->result_array())
			{
				foreach($query->result_array() as $row)
				{
					//filter results
					switch($param)
					{
						case 'pde_id':
							$result=$row['pde_id'];
							break;
						case 'pde':
							$result=get_pde_info_by_id($row['pde'],'title');
							break;
						case 'financial_year':
							$result = $row['financial_year'];
							break;


						case 'description':
							$result=$row['description'];
							break;


						case 'isactive':
							$result=$row['isactive'];
							break;

						case 'title':
							$result=$row['title'];
							break;


						default:
							$result=$query->result_array();
					}

				}

				return $result;
			}

		}

	}


	# FETCH DISPOSAL CONTRACTS ::
	function manage_disposal_contracts($idx = 0,$data=array()){

		switch ($idx) {
			case 0:
				$userid = $this->session->userdata['userid'];
				$pde = mysql_query("select * from  users where userid =".$userid);
				$q = mysql_fetch_array($pde);
				#$query = $this->Query_reader->get_query_by_code('manage_disposal_contracts', array('SEARCHSTRING' => ' AND  E.pde_id = '.$q['pde'].' ' ,'limittext' => ' '));
				# print_r($query); exit();
				if ($this->session->userdata('isadmin') == 'N') {
					$result = paginate_list($this, $data, 'manage_disposal_contracts',  array('SEARCHSTRING' => '    AND   B.isactive ="Y" AND E.isactive="Y"   AND  E.pde_id = '.$q['pde'].' AND  A.isactive ="Y"' ),10);
				}
				else
				{
					$result = paginate_list($this, $data, 'manage_disposal_contracts',  array('SEARCHSTRING' => '   AND   B.isactive ="Y" AND E.isactive="Y"   AND  A.isactive ="Y" ' ),10);
				}

				#$result = $this->db->query($query)->result_array();
				return $result;


				break;
# view_disposal_contracts
			#view_disposal_contracts
			default:
				# code...
				break;
		}
	}






	#Remove Restore Disposal Contract
	function remove_restore_contract($type,$receiptid){

		switch ($type) {
			case 'restore':
				# code...
				$query = $this->Query_reader->get_query_by_code('archive_restorediposal_contract', array('ID'=>$receiptid,'STATUS' => 'Y' ));
				# print_r($query); exit();
				$result = $this->db->query($query);
				if($result)
					return 1;

				break;
			case 'archive':
				$query = $this->Query_reader->get_query_by_code('archive_restorediposal_contract', array('ID'=>$receiptid,'STATUS' => 'N' ));
				#    print_r($query); exit();
				$result = $this->db->query($query);
				if($result)
					return 1;
				else
					return 0;
				break;
			case 'del':
				#echo "0"; exit();
				$query = $this->Query_reader->get_query_by_code('delete_receipt', array('ID'=>$receiptid));

				$result = $this->db->query($query);
				if($result)
					return 1;
				break;
			default:
				# code...
				break;
		}

	}

	#load disposal record snap in

	function load_disposal_entry_details($entry_id){

		#disposal_entry_id
		if($entry_id <= 0)
			return null;

		$query = $this->Query_reader->get_query_by_code('fetch_disposal_entry', array('searchstring'=>' disposal_record.id = '.$entry_id.'','limittext'=>''));
		$result = $this->db->query($query)->result_array();

		return($result);

	}



}
