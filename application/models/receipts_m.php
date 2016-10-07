<?php

class Receipts_m extends MY_Model {

	public $_tablename = 'receipts';
	public $_primary_key = 'receiptid';

	#Constructor
	function Receipts_m()
	{
		parent::__construct();
		$this->load->model('Query_reader', 'Query_reader', TRUE);
		$this->load->model('Ip_location', 'iplocator');
	}




	// =======================================================================//
	// ! RECEIPTS SUMMARY BY BID
	// =======================================================================//
	public function get_receipts_summary_by_bid($bid)
	{
		$this->db->cache_on();
		$this->db->select('receipts.*,
count(*) as sm,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.pre_bid_meeting_date,
bidinvitations.invitation_to_bid_date,
bidinvitations.procurement_ref_no,
bidinvitations.description_of_works '
		);
		$this->db->from('receipts');
		$this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
		$this->db->where('receipts.bid_id', $bid);

		$query = $this->db->get();
		$this->db->cache_off();
        print_array($this->db->last_query());
        print_array($this->db->_error_message());
        print_array(count($query->result_array()));

        print_array($query->result_array());
        exit;

		return $query->result_array();

	}

	// =======================================================================//
	// ! GET RECEIPTS BY BID
	// =======================================================================//

	function get_receipts_by_bid($bid_id){

		$this->db->cache_on();
		$this->db->select('receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.details,
receipts.received_by,
receipts.datereceived,
receipts.approved,
receipts.nationality,
receipts.author,
receipts.dateadded,
receipts.beb,
receipts.reason,
receipts.isactive,
receipts.joint_venture,
receipts.readoutprice,
receipts.currence,
BI.procurement_id,
BI.procurement_ref_no,
BI.cost_estimate,
BI.subject_of_procurement,
BI.pde_id,
BI.id,
PPE.id,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.procurement_method,
PPE.pde_department,
PPE.funding_source,
PPE.funder_name,
PPE.procurement_ref_no,
PPE.estimated_amount,
PPE.procurement_plan_id,
PP.pde_id,
PP.title,
PP.financial_year,
PP.id,
pdes.pdeid,
pdes.pdename,
providers.providerid,
providers.providernames'

		);
		$this->db->from('receipts');
		$this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
		$this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
		$this->db->join('procurement_plans AS PP', 'PP.id = PPE.procurement_plan_id');
		$this->db->join('pdes', 'pde.id = procurement_plans.pde_id');
		$this->db->join('providers', 'providers.providerid = receipts.providerid');
		$this->db->where('receipts.bid_id', $bid_id);

		$query = $this->db->get();
		$this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

		return $query->result_array();



		$results=$this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.details,
receipts.received_by,
receipts.datereceived,
receipts.approved,
receipts.nationality,
receipts.author,
receipts.dateadded,
receipts.beb,
receipts.reason,
receipts.isactive,
receipts.joint_venture,
receipts.readoutprice,
receipts.currence,
bidinvitations.procurement_id,
bidinvitations.procurement_ref_no,
bidinvitations.cost_estimate,
bidinvitations.subject_of_procurement,
bidinvitations.pde_id,
bidinvitations.id,
procurement_plan_entries.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.procurement_plan_id,
procurement_plans.pde_id,
procurement_plans.title,
procurement_plans.financial_year,
procurement_plans.id,
pdes.pdeid,
pdes.pdename,
providers.providerid,
providers.providernames
FROM
receipts
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN providers ON receipts.providerid = providers.providerid
WHERE
receipts.bid_id = " . $bid_id . " AND
receipts.isactive = 'Y'");

		return $results;
	}




//fetch providers
	/*
the
	*/
	#procurement providers
	function fetchproviders($bidid =0)
	{

		$data = array('SEARCHSTRING' => ' and  receipts.bid_id = '.$bidid,'approved'=>'Y');
		$query = $this->Query_reader->get_query_by_code('fetch_receipted_providers',$data);
		#print_r($query); exit();
		$result = $this->db->query($query)->result_array();
		return $result;

	}


	#fetdch Providers with lots
	function fetchproviders_lots($bidid =0,$searchstring = ' ')
	{

		$data = array('SEARCHSTRING' => ' and  receipts.bid_id = '.$bidid.$searchstring,'approved'=>'Y');
		$query = $this->Query_reader->get_query_by_code('fetch_receipted_providers_with_lots',$data);
		# print_r($query); exit();
		$result = $this->db->query($query)->result_array();
		return $result;

	}



	#When Lots
	function findlottedproviders($post){
		if(!empty($post)){
			$lotid = $post['lotid'];
			$bidid = $post['bidid'];
			#  print_r($_POST);
			#  exit();

			$data = array('SEARCHSTRING' => '     receipts.bid_id = '.$bidid.' and  received_lots.lotid = '.$lotid);
			$query = $this->Query_reader->get_query_by_code('fetch_receipts_with_lots',$data);
			#print_r($query);
			#exit();
			$result = $this->db->query($query)->result_array();
			$st = "";
			$providers = "";

			if(!empty($result))
			{
				$st .="<option data-readoutprice=''  data-country='' value=0>Select Provider </option>";
				foreach ($result as $key => $value) {
					if(((strpos($value['providerid'] ,",")!== false)) &&  (preg_match('/[0-9]+/', $value['providerid'] )))
					{
						$providers  = rtrim($value['providerid'],",");
						$query = mysql_query("select * from providers where providerid in (".$providers.") ");
						$row = mysql_query("SELECT * FROM `providers` where providerid in ($providers) ") or die("".mysql_error());
						$provider = "";

						while($vaue = mysql_fetch_array($row))
						{
							$provider  .=strpos($vaue['providernames'] ,"0") !== false ? '' : $vaue['providernames'].' , ';
						}

						$prvider = rtrim($provider,' ,');
						$st .="<option  data-readoutprice=".(!empty($value['read_price']) ? $value['read_price']: '0' )."  data-country=".$value['nationality']."   value=".$value['receiptid'].">".$prvider." &nbsp [JV]  </option>";
						#print_r($prvider);
					}

					else
					{
						$query = mysql_query("select * from providers where providerid = ".$value['providerid']);
						$records = mysql_fetch_array($query);
						$st .= "<option   data-readoutprice=".(!empty($value['read_price']) ? $value['read_price']: '0' )."  data-country=".$value['nationality']."   value=".$value['receiptid']."   >".$records['providernames']."</option>";
					}

				}

			}
			else {
				# code...
				$st = "<option value='0'>No Providers Added </option>";
			}

			return $st;

		}



	}


	#FETCH BIDS ON A GIGEN PROCUREMENT
	function fetchreceipts($bidid = 0)
	{



		if($bidid == 0)
		{

			$data = array('SEARCHSTRING' => ' 1 and 1 and bidinvitations.id ='.$bidid );
			$query = $this->Query_reader->get_query_by_code('view_all_bidders_list',$data);
			#print_array($query); exit();
			$result = $this->db->query($query)->result_array();
			return $result;
		}
		else
		{


			$data = array('SEARCHSTRING' => ' 1 and 1 and bidinvitations.id ='.$bidid );
			$query = $this->Query_reader->get_query_by_code('view_all_bidders_list',$data);
			#print_r($query); exit();
			$result = $this->db->query($query)->result_array();
			return $result;
		}


	}
	function fetctpdereceipts($bidid=0,$approved='Y'){
		$data = array('approved' =>  $approved,'searchstring'=>' and bidinvitations.id = '.$bidid.'  ORDER BY  receipts.dateadded  DESC');
		$query = $this->Query_reader->get_query_by_code('fetch_receipted_providers',$data);
		# print_r($query); exit();
		$result = $this->db->query($query)->result_array();
		return $result;
	}

	#get bid information
	function fetchbidinformation($bidid=0)
	{
		$data = array('SEARCHSTRING' => ' 1=1 and bidinvitations.id='.$bidid);
		$query = $this->Query_reader->get_query_by_code('fetchbidinfo',$data);
		#$q = mysql_query($query) or die("".mysql_error());
		#print_r($query); exit();
		$result = $this->db->query($query)->result_array();;
		return $result;
	}
	function count_bids($nationality ='local',$bidid = 0)
	{
		if($nationality == 'uganda'){
			$query = mysql_query("SELECT  COUNT(*) as sm FROM  receipts WHERE bid_id = " .$bidid." AND nationality  ='".$nationality."' ");

		}
		else
		{
			$query = mysql_query("SELECT  COUNT(*) as sm FROM  receipts WHERE bid_id = " .$bidid." AND nationality != 'uganda' ");

		}

		$result  = mysql_fetch_array($query);
		return $result;
	}

	function clean($string) {
		// Replaces all spaces with hyphens.
		$html_escape =  htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		return $html_escape;
	}

	#save bidreceipt :: Level 2 
	function savebidreceipt($post)
	{
		# Fetch Procurement Reference Number
		$procurementrefno = $post['procurementrefno'];
		$nationality = $post['nationality'];
		$servicep_lead  = 0;
		$bidid = $post['bidid'];


		
		#print_r($post);
		#exit();
		
		  //Bid and Procurement Information 		 
			$query = mysql_query("SELECT a.*,b.* FROM bidinvitations a inner join  procurement_plan_entries b on a.procurement_id = b.id  where a.id =   '".$bidid."' limit 1") or die("".mysql_error());

			$procurement_method = '';
			while ($record = mysql_fetch_array($query)) {
				# code...
			 
				$procurement_method = ($record['procurement_method_ifb'] > 0) ?  $record['procurement_method_ifb'] :  $record['procurement_method'] ;
			//	$procurementrefno = $record['procurement_ref_no'];
			}
			
 


		/*
		 * If Procurement Method is Not Micro Procurement 
		 * */
		if($procurement_method != 10)
		{
		
				/*
				Form Level Validation
				Check Dates to See if date submitted is between bidding period 
				
				*/

				   $post['datesubmitted'] =  custom_date_format('Y-m-d',$_POST['datesubmitted']);

					#Date Submitted
					$d1 =   strtotime($post['datesubmitted']);

					#IFB Bid Invitation to Bid Date
					if(!empty($post['invitation_to_bid_date']))
					{
						$d2= strtotime($post['invitation_to_bid_date']);
						
						if($d1 < $d2)
						{
								return "3: Date Submitted can not be before Invitation To BId Date " ;
					
						}
						
					}
				
				#IFB Bid Submission Deadline
				if(!empty($post['bid_submission_deadline']))
				{
					$d3  =  strtotime($post['bid_submission_deadline']);
					if($d1 >$d3)
					{
						return "3: Date Submitted can not be After Bid Submission Deadline " ;
				
					}
					
				}
	}
		
		
 


		/* IF POST IS JOINT VENTURE */
		if(!empty($post['jv']))
		{

	 
			$providers =  '';
			#print_r($post['jv']);
			$providerlead = $post['pr'];

			//BREAK THROUGH THE JV AND GET INDIVIDUAL PROVIDERS

			foreach($post['jv'] as $key => $value)
			{
				$serviceprovider = '';
				//print_r($key.'___');
				$prvider = explode('_',$key);
				$srvprov = $prvider[0];
				$serviceprovider = 0;

				if($srvprov =='serviceprovider')
				{
					$serviceprovider = $value;
					//print_r($serviceprovider); exit();
				}
				$query = 0;
				//if($serviceprovider > 0)

				/*
				FIND OUT IF THE PROVIDER EXISTS IN THE SYSTEMC 
				*/
				$query = mysql_query("SELECT * FROM providers where providernames  like '".$serviceprovider."'  limit 1") or die("".mysql_error());



				$providenms  ='';
				if(mysql_num_rows($query) > 0)
				{
					while ($res = mysql_fetch_array($query)) {
						# code...
						$providerid = $res['providerid'];
						$providenms = $res['providernames'];
						$providers .=  $providerid.',';
					}
					if($serviceprovider == $providerlead){
						$servicep_lead = $res['providerid'];
					}

				}
				else{
					if(($serviceprovider != 0) || ($serviceprovider != '') || ($serviceprovider != '0'))
					{
				  #iNSERT PROVIDER IN PROVIDERS TABLE
				    $query = mysql_query("INSERT INTO providers(providernames) values ('".mysql_real_escape_string($serviceprovider)."') ") or die("".mysql_error());
				  #pick the provider id 
						$providerid = mysql_insert_id();
						$providers .=  $providerid.',';

						#check if this is a project lead ::
						if($serviceprovider == $providerlead){
							$servicep_lead = 	$providerid;
						}

					}
				}

			}

			$datesubmitted = $post['datesubmitted'];
			//	$dd = explode("-", $datesubmitted);
			//	$ddat = $dd[2].'-'.$dd['1'].'-'.$dd[0];
			$datesubmitted = custom_date_format("Y-m-d",$post['datesubmitted']);
			$receivedby = $post['receivedby'];
			$readoutprice = isset($post['readoutprice']) ? $post['readoutprice']: 0 ;
			$currency = isset($post['currency']) ? $post['currency'] : '' ;

			

			$randl = rand(234789,90290);
			$rand2 = rand(90867,62726);

			$rand =  rand($randl,$rand2);

			$jv_number = "jv_".$rand;
			$nationality = $post['nationality'];

			/*
			Insert into Receipts while iterating through Joint veture: 
			and also add to received lots table :
			*/
			$query =mysql_query("INSERT INTO receipts(bid_id,received_by,datereceived,nationality,joint_venture,readoutprice,currence) values('".$bidid."','".$receivedby."','".$datesubmitted."','".$nationality."','".$jv_number."','".$readoutprice."','".$currency."') ") or die("sdsds".mysql_error());
			if($query)
			{
				//get the insert ID
				$insert_id = mysql_insert_id();

				//adding bid response to a given  lot number
				if($post['lots'] > 0)
				{
					#	$receiptid = 	$insert_id;
					$ifbslot = $post['ifbslot'];
					$query = mysql_query("INSERT INTO received_lots(receiptid,lotid) VALUES('".$insert_id."','".$ifbslot."')") or die("".mysql_error());
				}

				$query = mysql_query("insert into joint_venture(jv,providers,provider_lead) values ( (select joint_venture as jv  from receipts where receiptid = '".$insert_id."'),'".$providers."','".$servicep_lead."' )") or die(".....".mysql_error());

				if(!empty($post['pricing']))
				{
					$amount = '';
					$cutt = '';
					$exchangerate = '';

					#print_r($post['jv']);
					foreach($post['pricing'] as $key => $value)
					{


						//print_r($key.'___');
						$pp = explode('_',$key);
						$ppv = $pp[0];

						if(	$ppv =='currency')
						{
							$cutt .= $value.',';
						}
						if(	$ppv =='readoutprice')
						{
							$amount .= $value.',';
						}
						if(	$ppv =='exchangerate')
						{
							$exchangerate .= $value.',';
						}


					}
					$aty = explode(',',$cutt);
					$aty2 = explode(',',$amount);
					$aty3 = explode(',',$exchangerate);
					$length = count($aty2);
					$x = 0;
					while($x < $length)
					{
						$prvider = explode('_',$key);
						$query = mysql_query("INSERT INTO  readoutprices(readoutprice,currence,exchangerate,receiptid) values ('".removeCommas($aty2[$x])."','".$aty[$x]."','".removeCommas($aty3[$x])."','".$insert_id."') ")or die("".mysql_error());
						$x ++;
					}




				}
				else
				{

				//If readout Price is not set or is 0  then go the next roww but cant save:
				if($readoutprice <= 0)
				{
					//continue;
				}
				else
				{
				

					$exchangerate = '';
					if(!empty($post['exchangerate']))
						$exchangerate = $post['exchangerate'];
					$query = mysql_query("INSERT INTO  readoutprices(readoutprice,currence,exchangerate,receiptid) values ('".$readoutprice."','".$currency."','".$exchangerate."' , '".$insert_id."') ")or die("".mysql_error());
				}


				}

				// if lots get information about lots
				return 1;

			}else
			{
				return 0 ;
			}

#exit("REACHE1A");


			print_r($providers);
			exit();
		}
		else
		{
			#	exit("REACHE1B");
			$serviceprovider = mysql_real_escape_string($this->clean($post['serviceprovider']));
			$nationality = $post['nationality'];

			$datesubmitted = $post['datesubmitted'];

			$datesubmitted =  custom_date_format("Y-m-d",$post['datesubmitted']);
			$receivedby = $post['receivedby'];
			#$approved = $post['approved'];
			$readoutprice = isset($post['readoutprice']) ?$post['readoutprice'] : '' ;
			$currency = isset($post['currency']) ? $post['currency'] : 0;
			// save information
			// check if name exists in the  providers table

			$query = mysql_query("SELECT * FROM providers where providernames  like '".mysql_real_escape_string($serviceprovider)."'  limit 1") or die("".mysql_error());
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

				// usual procedure ::
				$query = mysql_query("INSERT INTO providers(providernames) values ('".mysql_real_escape_string($serviceprovider)."') ") or die("".mysql_error());

				$providerid = mysql_insert_id();
			}
			//get bid information ::
		
			//check to see if the provider already submitted
			//$query = mysql_query("SELECT * FROM receipts WHERE  providerid = ".$providerid." and  bid_id = ".$bidid."") or die("".mysql_error());
			if($post['lots'] > 0)
			{
				$query = mysql_query(" SELECT    receipts.*   FROM receipts   INNER JOIN received_lots  ON receipts.receiptid = received_lots.receiptid   WHERE  providerid = ".$providerid." AND  receipts.bid_id = ".$bidid." AND received_lots.lotid = ".mysql_real_escape_string($post['ifbslot'])." ") or die("".mysql_error());
				#  exit("SELECT receipts.* FROM receipts INNER JOIN bidinvitations  ON receipts.bid_id = bidinvitations.id   INNER JOIN lots ON bidinvitations.id = lots.bid_id WHERE  providerid = ".$providerid." AND  receipts.bid_id = ".$bidid." AND lots.id = ".mysql_real_escape_string($post['ifbslot'])." " );
			}
			else
			{
				$query = mysql_query("SELECT * FROM receipts WHERE  providerid = ".$providerid." and  bid_id = ".$bidid."") or die("".mysql_error());
			}


			if(mysql_num_rows($query) > 0){
				return "3:".$providenms."Have a bid proposal existing on  Procurement Ref No - ".$procurementrefno ;
			}
			else{

				$exchangerate = '';
				if(!empty($post['exchangerate']))
					$exchangerate = $post['exchangerate'];


				//save the bid
				$query =mysql_query("INSERT INTO receipts(bid_id,providerid,received_by,datereceived,nationality,readoutprice,currence) values('".$bidid."','".$providerid."','".$receivedby."','".$datesubmitted."','".$nationality."','".$readoutprice."','".$currency."') ") or die("".mysql_error());

				$insertid = mysql_insert_id();
				#	print_r($insertid);
				if($post['lots'] > 0)
				{

					$ifbslot = $post['ifbslot'];
					$query = mysql_query("INSERT INTO received_lots(receiptid,lotid) VALUES(".$insertid.",'".$ifbslot."')") or die("".mysql_error());
				}

				if(!empty($post['pricing']))
				{
					$amount = '';
					$cutt = '';
					$exchangerate ='';

					#print_r($post['jv']);
					foreach($post['pricing'] as $key => $value)
					{


						//print_r($key.'___');
						$pp = explode('_',$key);
						$ppv = $pp[0];

						if(	$ppv =='currency')
						{
							$cutt .= $value.',';
						}
						if(	$ppv =='readoutprice')
						{
							$amount .= $value.',';
						}
						if($ppv =='exchangerate')
						{
							$exchangerate .= $value.',';
						}


					}

					$aty = explode(',',$cutt);
					$aty2 = explode(',',$amount);
					$aty3 = explode(',',$exchangerate);

					$length = count($aty2);
					$x = 0;
					while($x < $length)
					{
						$prvider = explode('_',$key);


						$query =  mysql_query("INSERT INTO  readoutprices(readoutprice,currence,exchangerate,receiptid) values ('".$aty2[$x]."','".$aty[$x]."','".$aty3[$x]."','".$insertid."') ");
						#print_r($query); exit();

						$x ++;
					}



				}
				else
				{
			//if readout price not entered dont save just continue the proccess 
			if($readoutprice <= 0)
				return 1;

					$query = mysql_query("INSERT INTO  readoutprices(readoutprice,currence,exchangerate,receiptid) values ('".$readoutprice."','".$currency."','".$exchangerate."',  '".$insertid."') ")or die("".mysql_error());
				}
				if($query)
				{
 log_action('create','Receipt  Created  Successfully   ', '  A Bid Receipt for Rerefence Number  '.$procurementrefno.' Has Been Created  Successfully , Received By  '.$receivedby );  

					return 1;
				}else
				{
					return 0 ;
				}
			}
		}



	}


	#save disposal bidreceipt
	function savedisposalbidreceipt($post)
	{
		$disposalbidid = $post['disposalbid_id'];
		$nationality = $post['nationality'];
		$servicep_lead  = 0;

		if(!empty($post['jv']))
		{

			$providers =  '';
			#print_r($post['jv']);
			$providerlead = $post['pr'];

			foreach($post['jv'] as $key => $value)
			{
				$serviceprovider = '';
				//print_r($key.'___');
				$prvider = explode('_',$key);
				$srvprov = $prvider[0];
				$serviceprovider = 0;
				if(	$srvprov =='serviceprovider')
				{
					$serviceprovider = $value;
					//print_r($serviceprovider); exit();
				}
				$query = 0;
				//if($serviceprovider > 0)
				$query = mysql_query("SELECT * FROM providers where providernames  like '".$serviceprovider."'  limit 1") or die("".mysql_error());



				$providenms  ='';
				if(mysql_num_rows($query) > 0)
				{
					while ($res = mysql_fetch_array($query)) {
						# code...
						$providerid = $res['providerid'];
						$providenms = $res['providernames'];
						$providers .=  $providerid.',';
					}
					if($serviceprovider == $providerlead){
						$servicep_lead = $res['providerid'];
					}

				}
				else{
					if(($serviceprovider != 0) || ($serviceprovider != '') || ($serviceprovider != '0'))
					{
						// usual procedure ::
						$query = mysql_query("INSERT INTO providers(providernames) values ('".$serviceprovider."') ") or die("".mysql_error());
						$providerid = mysql_insert_id();
						$providers .=  $providerid.',';

						#check if this is a project lead ::
						if($serviceprovider == $providerlead){
							$servicep_lead = 	$providerid;
						}

					}
				}

			}

			$datesubmitted = $post['datesubmitted'];
			//	$dd = explode("-", $datesubmitted);
			//	$ddat = $dd[2].'-'.$dd['1'].'-'.$dd[0];
			$datesubmitted = custom_date_format("Y-m-d",$post['datesubmitted']);
			$receivedby = $post['receivedby'];
			$readoutprice = isset($post['readoutprice']) ? $post['readoutprice']: 0 ;
			$currency = isset($post['currency']) ? $post['currency'] : '' ;

			//get bid information ::
			$bidid  = 0;
			/*
    $query =   " select a.* from disposal_bid_invitation a    INNER JOIN disposal_record  b   ON a.disposal_record = b.id   where a.id '".$disposalbidid."' limit 1";
    print_r($query); exit(); */
			$query = mysql_query(" select a.* from disposal_bid_invitation a    INNER JOIN disposal_record  b   ON a.disposal_record = b.id   where a.id = '".$disposalbidid."' limit 1");




			while ($res = mysql_fetch_array($query)) {
				# code...
				$bidid = $res['id'];
			}

			$randl = rand(234789,90290);
			$rand2 = rand(90867,62726);
			$rand =  rand($randl,$rand2);
			$jv_number = "djv_".$rand;
			$nationality = $post['nationality'];


			$query =mysql_query("INSERT INTO disposal_receipts(bid_id,received_by,datereceived,nationality,joint_venture,readoutprice,currence) values('".$bidid."','".$receivedby."','".$datesubmitted."','".$nationality."','".$jv_number."','".$readoutprice."','".$currency."') ") or die("sdsds".mysql_error());

			if($query)
			{
				//get the insert ID
				$insert_id = mysql_insert_id();

				//adding bid response to a given  lot number
				if($post['lots'] > 0)
				{
					#$receiptid = 	$insert_id;
					$ifbslot = $post['ifbslot'];
					$query = mysql_query("INSERT INTO received_lots(receiptid,lotid) VALUES('".$insert_id."','".$ifbslot."')") or die("".mysql_error());
				}

				$query = mysql_query("insert into joint_venture(jv,providers,provider_lead) values ( (select joint_venture as jv  from disposal_receipts where receiptid = '".$insert_id." limit 1'),'".$providers."','".$servicep_lead."' )") or die(".....".mysql_error());

				if(!empty($post['pricing']))
				{
					$amount = '';
					$cutt = '';
					$exchangerate = '';

					#print_r($post['jv']);
					foreach($post['pricing'] as $key => $value)
					{


						//print_r($key.'___');
						$pp = explode('_',$key);
						$ppv = $pp[0];

						if(	$ppv =='currency')
						{
							$cutt .= $value.',';
						}
						if(	$ppv =='readoutprice')
						{
							$amount .= $value.',';
						}
						if(	$ppv =='exchangerate')
						{
							$exchangerate .= $value.',';
						}


					}
					$aty = explode(',',$cutt);
					$aty2 = explode(',',$amount);
					$aty3 = explode(',',$exchangerate);
					$length = count($aty2);
					$x = 0;
					while($x < $length)
					{
						$prvider = explode('_',$key);
						$query = mysql_query("INSERT INTO  disposalreadoutprices(readoutprice,currence,exchangerate,receiptid) values ('".$aty2[$x]."','".$aty[$x]."','".$aty3[$x]."','".$insert_id."') ")or die("".mysql_error());
						$x ++;
					}




				}
				else
				{

					$query = mysql_query("INSERT INTO  disposalreadoutprices(readoutprice,currence,receiptid) values ('".$readoutprice."','".$currency."','".$insert_id."') ")or die("".mysql_error());
				}

				// if lots get information about lots
				return 1;

			}else
			{
				return 0 ;
			}




			print_r($providers);
			exit();
		}
		else
		{
			#print_r($post); exit();
			$serviceprovider = $post['serviceprovider'];
			$nationality = $post['nationality'];

			$datesubmitted = $post['datesubmitted'];

			$datesubmitted =  custom_date_format("Y-m-d",$post['datesubmitted']);
			$receivedby = $post['receivedby'];
			#$approved = $post['approved'];
			$readoutprice = isset($post['readoutprice']) ?$post['readoutprice'] : '' ;
			$currency = isset($post['currency']) ? $post['currency'] : 0;
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

				// usual procedure ::
				$query = mysql_query("INSERT INTO providers(providernames) values ('".$serviceprovider."') ") or die("".mysql_error());
				$providerid = mysql_insert_id();
			}
			//get bid information ::
			$bidid  = 0;
			$query = mysql_query("select a.* from disposal_bid_invitation a    INNER JOIN disposal_record  b   ON a.disposal_record = b.id   where a.id ='".$disposalbidid."' limit 1");
			// $query = "select a.* from disposal_bid_invitation a    INNER JOIN disposal_record  b   ON a.disposal_record = b.id   where b.id = '".$disposalbidid."' limit 1";
			// print_r($query); exit();

			while ($res = mysql_fetch_array($query)) {
				# code...
				$bidid = $res['id'];
			}

			//check to see if the provider already submitted
			$query = mysql_query("SELECT * FROM disposal_receipts WHERE  providerid = ".$providerid." and  bid_id = ".$bidid."") or die("".mysql_error());

			if(mysql_num_rows($query) > 0){
				return "3:".$providenms."Have a bid proposal existing on  Procurement Ref No - ".$disposalbidid ;
			}
			else{
				//save the bid
				$query =mysql_query("INSERT INTO disposal_receipts(bid_id,providerid,received_by,datereceived,nationality,readoutprice,currence) values('".$bidid."','".$providerid."','".$receivedby."','".$datesubmitted."','".$nationality."','".$readoutprice."','".$currency."') ") or die("".mysql_error());

				$insertid = mysql_insert_id();
				#	print_r($insertid);
				if($post['lots'] > 0)
				{

					$ifbslot = $post['ifbslot'];
					$query = mysql_query("INSERT INTO received_lots(receiptid,lotid) VALUES(".$insertid.",'".$ifbslot."')") or die("".mysql_error());
				}

				if(!empty($post['pricing']))
				{
					$amount = '';
					$cutt = '';
					$exchangerate ='';

					#print_r($post['jv']);
					foreach($post['pricing'] as $key => $value)
					{


						//print_r($key.'___');
						$pp = explode('_',$key);
						$ppv = $pp[0];

						if(	$ppv =='currency')
						{
							$cutt .= $value.',';
						}
						if(	$ppv =='readoutprice')
						{
							$amount .= $value.',';
						}
						if($ppv =='exchangerate')
						{
							$exchangerate .= $value.',';
						}


					}

					$aty = explode(',',$cutt);
					$aty2 = explode(',',$amount);
					$aty3 = explode(',',$exchangerate);

					$length = count($aty2);
					$x = 0;
					while($x < $length)
					{
						$prvider = explode('_',$key);


						$query =  mysql_query("INSERT INTO  disposalreadoutprices(readoutprice,currence,exchangerate,receiptid) values ('".$aty2[$x]."','".$aty[$x]."','".$aty3[$x]."','".$insertid."') ");
						#print_r($query); exit();

						$x ++;
					}



				}
				else
				{

					$query = mysql_query("INSERT INTO  disposalreadoutprices(readoutprice,currence,receiptid) values ('".$readoutprice."','".$currency."','".$insertid."') ")or die("".mysql_error());
				}
				if($query)
				{


					return 1;
				}else
				{
					return 0 ;
				}
			}
		}



	}




	//update bid receipt
	function updatebidreceipt($post,$idx)
	{
		$procurementrefno = $post['procurementrefno'];
		$serviceprovider = $post['serviceprovider'];
		$nationality = $post['nationality'];
		$datesubmitted = $post['datesubmitted'];
		$dd = explode("-", $datesubmitted);
		$ddat = $dd[2].'-'.$dd['1'].'-'.$dd[0];
		$datesubmitted = $ddat;
		$receivedby = $post['receivedby'];
		#$approved = $post['approved'];

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
		$bidid  = 0;
		$query = mysql_query("SELECT * FROM bidinvitations where procurement_ref_no like '".$procurementrefno."' limit 1");

		while ($res = mysql_fetch_array($query)) {
			# code...
			$bidid = $res['id'];
		}

		#UPDATE BID
		$query =mysql_query("UPDATE  receipts set bid_id='".$bidid."',providerid='".$providerid."',received_by='".$receivedby."',datereceived='".$datesubmitted."',nationality='".$nationality."' where receiptid = ".$idx) or die("".mysql_error());
		if($query)
		{
			return 1;
		}else
		{
			return 0 ;
		}



	}


	// fetch receipt iformation in a given pde
	/*

	*/
	function pde_receipt_information($isadmin ='N',$userid = 0,$data){

		if($isadmin == 'Y'){
			//	$query = $this->Query_reader->get_query_by_code('fetchproviders_pde', array('SEARCHSTRING' => ' order by receipts.dateadded DESC '));
			//$result = $this->db->query($query)->result_array();
			$result = paginate_list($this, $data, 'fetchproviders_pde', array('SEARCHSTRING' => ' order by receipts.dateadded DESC'),10);
			return $result;
		}
		else
		{
			$issactive = "Y";
			$result = paginate_list($this, $data, 'fetchproviders_pde', array('SEARCHSTRING' => '  and  users.userid ='.$userid.' and receipts.isactive = "'.$issactive.'" order by receipts.dateadded DESC'),10);

			//$query = $this->Query_reader->get_query_by_code('fetchproviders_pde', array('SEARCHSTRING' => '  and  users.userid ='.$userid.' and receipts.isactive = "'.$issactive.'" order by receipts.dateadded DESC'));
			// print_r($query); exit();
			// $result = $this->db->query($query)->result_array();
			return $result;
		}
	}
	
	function procurement_receipt_information($isadmin ='N',$userid = 0,$data,$bidid = 0){

		/*
         $issactive = "Y";
                    $result = paginate_list($this, $data, 'fetchproviders_pde', array('SEARCHSTRING' => '  and  users.userid ='.$userid.' and receipts.isactive = "'.$issactive.'" order by receipts.dateadded DESC'),10);
                    */
		#  $query = $this->Query_reader->get_query_by_code('fetchproviders_pde', array('SEARCHSTRING1' => ' and procurement_plan_entries.procurement_ref_no ="'.$procurement.'"   ','SEARCHSTRING2' => ' and procurement_plan_entries.procurement_ref_no ="'.$procurement.'"   '));
		#print_r($query); exit();
		// $result = $this->db->query($query)->result_array();
		$result = paginate_list($this, $data, 'clean_fetch_providers_pde', array('SEARCHSTRING1' => ' and bidinvitations.id ="'.$bidid.'"   ','SEARCHSTRING2' => ' and bidinvitations.id ="'.$bidid.'"   '),100);
		#exit($this->db->last_query());
		return $result;

	}
	

	 

	function procurement_receipt_information_jv($isadmin ='N',$userid = 0,$data,$procurement = 0){

		// $result = $this->db->query($query)->result_array();
		$result = paginate_list($this, $data, 'fetchproviders_jv', array('SEARCHSTRING' => ' and bidinvitations.procurement_ref_no ="'.$procurement.'" order by receipts.dateadded DESC '),100);
		# print_r($result); exit();
		return $result;

	}




	function search_receipts($isadmin ='N',$userid = 0,$data,$search)
	{
		$search_string = 'and ( procurement_plan_entries.procurement_ref_no  like "%'. $search .'%" OR  providers.providernames  like "%'. $search .'%"   OR  receipts.datereceived  like "%'. $search .'%"   OR  receipts.received_by  like "%'. $search .'%" OR  receipts.dateadded  like "%'. $search .'%" OR  receipts.nationality  like "%'. $search .'%" )';

		if($isadmin == 'Y'){

			$result = paginate_list($this, $data, 'fetchproviders_pde', array('SEARCHSTRING' => ' '.$search_string.' order by receipts.dateadded DESC'),10);

			return $result;
		}
		else
		{
			$issactive = "Y";
			$result = paginate_list($this, $data, 'fetchproviders_pde', array('SEARCHSTRING' => ' '.$search_string.' and  users.userid ='.$userid.' and receipts.isactive = "'.$issactive.'" order by receipts.dateadded DESC'),10);
			return $result;
		}
	}
	/*
           array('SEARCHSTRING1' => ' and procurement_plan_entries.procurement_ref_no ="'.$procurement.'"   ','SEARCHSTRING2' => ' and bidinvitations.procurement_ref_no ="'.$procurement.'"   ') */
	function fetch_unsuccesful_bidders($receiptid=0,$bidid =0,$lotid = 0){
		if($lotid > 0)
		{
			$searchstring = ' AND if(procurement_plan_entries.framework ="Y",receipts.beb !="Y",1=1)  AND  receipts.bid_id ='.$bidid.' AND receipts.receiptid !='.$receiptid.' and lots.id ='.$lotid;
			$searchstring2 =  ' and  receipts.bid_id ='.$bidid.' and receipts.receiptid !='.$receiptid.' and lots.id ='.$lotid;
			$query = $this->Query_reader->get_query_by_code('fetchprovidersclean_withlots_pde', array('SEARCHSTRING1' => $searchstring ,'limittext' => '','SEARCHSTRING2' => $searchstring2));
			# print_r($query); exit();
			$result = $this->db->query($query)->result_array();
		}
		else{

			$searchstring = ' AND receipts.beb !="Y"  and  receipts.bid_id ='.$bidid.' and receipts.receiptid !='.$receiptid.'';
			$searchstring2 =  ' and  receipts.bid_id ='.$bidid.' and receipts.receiptid !='.$receiptid.'';
			$query = $this->Query_reader->get_query_by_code('clean_fetch_providers_pde', array('SEARCHSTRING1' => $searchstring ,'limittext' => '','SEARCHSTRING2' => $searchstring2));
			# print_r($query); exit();
			$result = $this->db->query($query)->result_array();
		}
		return $result;
	}

	# publish beb level 3
	function publishbeb($post){
		#print_r($post); exit();
		$lotid = 0;
		if(isset($post['ifbslot']))
		{
			$lotid =  $post['ifbslot'];
			if(empty($lotid))
			{
				return "3: Select Lot";
			}

		}

		/*random generator */
		$var1 = 1234567890;
		$var2 = 6723566799;
		$serialnumber = rand($var1,$var2);
		/* end */

		$pdeid = $this->session->userdata('pdeid');
		$pdes = $this->db->query("select * from pdes where pdeid=".$pdeid." limit 1 ")->result_array();
		$abbr = $pdes[0]['abbreviation'];
		$abbr .= $serialnumber;


		$pid = mysql_real_escape_string($post['pid']);
		$procurementrefno = mysql_real_escape_string($post['procurementrefno']);
		$evaluationmethod = mysql_real_escape_string($post['evaluationmethods']);


		if(!empty($post['dob_commencement']) && ($post['dob_commencement'] != '' ))
			$dob_commencement =  custom_date_format('Y-m-d',$post['dob_commencement']);
		else
			$dob_commencement = '';


		$num_bids = removeCommas(mysql_real_escape_string($post['num_bids']));


		if(!empty($post['dob_evaluation']) && ($post['dob_evaluation'] != '' ))
			$dob_evaluation =  custom_date_format('Y-m-d',$post['dob_evaluation']);
		else
			$dob_evaluation = '';


		#$dob_cc = $post['dob_cc'];

		if(!empty($post['dob_cc']) && ($post['dob_cc'] != '' ))
			$dob_cc =  custom_date_format('Y-m-d',$post['dob_cc']);
		else
			$dob_cc = '';



		//$dob_cc = $dss[2].'-'. $dss[1].'-'. $dss[0];
		$bebname = mysql_real_escape_string($post['bebname']);

		$beb_nationality = mysql_real_escape_string($post['beb_nationality']);

		$contractprice = removeCommas($post['contractprice']);

		$currency = mysql_real_escape_string($post['currence']);

		$bidid =   mysql_real_escape_string($post['bidid']);

		$ispublished = mysql_real_escape_string($post['btnstatus']) == 'publish' ? 'Y' : 'N';
		#$revviewed = mysql_real_escape_string($post['btnstatus']) == 'publish' ? 'Y' : 'N';
		$numorblocal = mysql_real_escape_string($post['num_bids_local']);

		if(!empty($post['date_beb_expires']))
			$date_beb_expires =  custom_date_format('Y-m-d',$post['date_beb_expires']);
		else
			$date_beb_expires = '';

		if(!empty($post['date_of_display']))
			$date_of_display =  custom_date_format('Y-m-d',$post['date_of_display']);
		else
			$date_of_display = '';


		#justification
		$justification   = mysql_real_escape_string($post['justification']);

		#Signature Date 
		if(!empty($post['signature_date']))
			$signature_date=  custom_date_format('Y-m-d',$post['signature_date']);
		else
			$signature_date = '';


		#Authorized By 
		$authorized_by =  mysql_real_escape_string($post['authorized_by']);

		$data = $this->session->all_userdata();
		$author = $data['userid'];
		$var = $this->session->userdata;


		$bebid ='';
		if((isset($var['bebid'] )) && (!empty($var['bebid'])))
		{
			$bebid = $var['bebid'];
		}

		$bebz = $this->session->userdata('bestevaluated');
		if(!empty($bebz))
		{
			$bebid = $bebz;
		}
		else
		{
			$bebid = 0;
		}

		$data = array(
				'pid' => $bebname,
				'bidid' => $bidid,
				//'create_date' => NOW(),// current date time stamp
				'evaluationmethod' => $evaluationmethod, // get the currently logged in session :: user id
				'dateofcommencement' => $dob_commencement,
				'numbids' => $num_bids,
				'dobevaluation' => $dob_evaluation,
				'datecc' => $dob_cc,
				'bebname' => $bebname,
				'bebnationality' => $beb_nationality,
				'contractprice' => $contractprice,
				'currency' => $currency,
				'author' => $author,
				'ispublished' => $ispublished,
				'bebid' => $bebid,
				'lot' => $lotid,
				'numorblocal' => $numorblocal,
				'serialnumber' => $abbr ,
				'BEBEXPIRYDATE' => $date_beb_expires,
				'DATEOFDISPLAY'=> $date_of_display,
				'justification' => $justification,
				'signature_date' =>$signature_date,
				'authorized_by'=>$authorized_by
		);
		
		
		/*
		 * Check to see if it an IFB lot and if so. this lot exist in the BEB else pass :
		 * 
		 */
		 
		  $update_ready = 1;
		 if(!empty($post['ifbslot']))
			{
				 $update_ready = 0; 
				 
				 /*
				  * Check to see that this lot has data in the BEB proccess if so. proceed to edit or if not insert the procedure
				  */ 
				$lotted_data =   $this->db->query("SELECT * FROM bestevaluatedbidder  INNER JOIN receipts ON  bestevaluatedbidder.pid =  receipts.receiptid  INNER JOIN received_lots ON  receipts.receiptid =  received_lots.receiptid   WHERE   received_lots.lotid = ".$post['ifbslot']."")->result_array();
				if(!empty($lotted_data))
				 	 $update_ready = 1;
				 
				
				 
			}
			
			
		 
		  #updatebeb
		if(((isset($var['bebid'] )) && (!empty($var['bebid'])))  &&  ($update_ready == 1) ){
			$query = $this->Query_reader->get_query_by_code('updatebeb',  $data);
			$result = $this->db->query($query);

			$query = $this->Query_reader->get_query_by_code('update_beb_by_bidid',  $data);
			$result = $this->db->query($query);

			

			$this->session->unset_userdata('bebid');
		}
		else 	if(!empty($post['ifbslot']))
			{

			
			$this->session->unset_userdata('bebid');
			
				#$this->session->unset_userdata('bebid');
				#exit("pass");
			 

				#print_r($post['framework']);exit("pass");

				$frmwk =   !empty($post['framework']) ? $post['framework']  :  'N' ;


				#print_r($frmwk);
				#echo"<br/>";
				#exit("pass");
				
				
				$msg_said = "";
					
				
				$msg_said = "";
				if(trim($frmwk) == 'Y')
				{

 
					$query = $this->Query_reader->get_query_by_code('search_beb_lots', array('SEARCHSTRING' => '   receipts.receiptid ="'.$bebname.'" AND receipts.bid_id ="'.$bidid.'" AND received_lots.lotid ="'.$lotid.'"     '));
$awarded_beb_with_lots = $this->db->query($query)->result_array();
				
				$msg_said  = "3: The Same BEB has been awarded to this framework lot";
				}
				else
				{
					 
				
					$query = $this->Query_reader->get_query_by_code('search_beb_lots', array('SEARCHSTRING' => '     receipts.bid_id ="'.$bidid.'" AND received_lots.lotid ="'.$lotid.'"    '));
$awarded_beb_with_lots = $this->db->query($query)->result_array();
					$msg_said  = "3: A BEB Has Been Awarded to this Lot";
			
				}
				
			 #  print_r($awarded_beb_with_lots); exit();
				if(!empty($awarded_beb_with_lots))
				{
					return "".$msg_said;
				}
				else
				{
					$query = $this->Query_reader->get_query_by_code('insertbeb',  $data);
					$result = $this->db->query($query);

				}
				
			} 
		 
		else
			{
				$query = $this->Query_reader->get_query_by_code('insertbeb',  $data);

				$result = $this->db->query($query);

			}

		//$query = mysql_query("INSERT INTO bestevaluatedbidder(pid,bidid,type_oem,ddate_octhe,num_orb,date_oce_r,date_oaoterbt_cc,bebid,nationality,contractprice,currency,author) values('".$pid."','".$bidid."','".$evaluationmethod."','".$dob_commencement."',
		//'".$num_bids."','".$dob_evaluation."','".$dob_cc."','".$bebname."','".$beb_nationality."','".$contractprice."','".$currency."','".$author."') ") or die("".mysql_error());
		// after saving update the receipts information ::
		//update receipts set beb =  N or Y depending ..
		$query = mysql_query("UPDATE receipts set beb ='Y'  where receiptid = $bebname  and bid_id = $bidid") or die("".mysql_error());

	
		if($lotid > 0)
		{
			$query = mysql_query("UPDATE receipts set beb ='N' where  receiptid in (SELECT receiptid  FROM received_lots where lotid = '".$lotid."'  ) AND receiptid != $bebname  and bid_id = $bidid AND beb != 'Y' ")       or die("".mysql_error());
		}
		else
		{
			$query = mysql_query("UPDATE receipts set beb ='N' where receiptid != $bebname  AND beb != 'Y' AND  bid_id = $bidid")   or die("".mysql_error());
		}
		// then get the list and update  the list



     
		//answerd: reason why not successful
		if(!empty($post['answered'])){
			$answerd = $post['answered'];
			foreach ($answerd as $key => $value) {
				# update the questionaire for everu single entry
				$query  = mysql_query("UPDATE receipts set reason = '".mysql_real_escape_string($value)."' where receiptid = ".$key) or die("".mysql_error());
			}
		}

		if(!empty($post['answered_detail'])){
			$answerd = $post['answered_detail'];
			foreach ($answerd as $key => $value) {
				# update the questionaire for everu single entry
				$query  = mysql_query("UPDATE receipts set reason_detail = '".mysql_real_escape_string($value)."' where receiptid = ".$key) or die("".mysql_error());
			}
		}


		return 1;

	}

	#fetch beb list to
	function fetch_lots_awarded_beb($data,$post)
	{
		#print_r($post);
		$bidid = $post['bidid'];
		//  $arrayName = array('' => , );
		//received_lots.lotid NOT IN (SELECT lotid FROM contracts) AND
		$searchstring = '    receipts.bid_id='.$bidid.' AND receipts.beb= "Y"';
		$query = $this->Query_reader->get_query_by_code('fetch_bebs_to_lots',array('SEARCHSTRING' => $searchstring.''));

		$result['page_list'] = $this->db->query($query)->result_array();		 
		return $result;

	}
	function fetch_lots_awarded_beb_notincontracts($data,$post)
	{
		#print_r($post);
		$bidid = $post['bidid'];
		$receiptid = $post['receiptid'];
	 

		$searchstring = '    receipts.bid_id='.$bidid.'  AND  receipts.receiptid  NOT IN (SELECT  DISTINCT contracts.receiptid FROM contracts   INNER JOIN receipts RS ON  contracts.receiptid =   RS.receiptid  WHERE  contracts.isactive ="Y"  )    ';
		#AND receipts.receiptid = '.$receiptid.' 
		
		$result = paginate_list($this, $data, 'fetch_bebs_to_lots', array('SEARCHSTRING' => $searchstring.''),10);

		#exit($this->db->last_query());

		return $result;
	}

	function fetch_beb_lots($data,$searchstring)
	{

		$result = paginate_list($this, $data, 'fetch_bebs_to_lots', array('SEARCHSTRING' => $searchstring.''),10);
		return $result;

	}





	//disposal function
	function disposalpublishbeb($post)
	{
		#print_r($post); exit();
		$lotid = 0;
		if(isset($post['ifbslot']))
		{
			$lotid =  mysql_real_escape_string($post['ifbslot']);
			if(empty($lotid))
			{
				return "3: Select Lot";
			}

		}


		$pid = mysql_real_escape_string($post['pid']);
		$disposaltrefno = mysql_real_escape_string($post['disposaltrefno']);
		$evaluationmethod = mysql_real_escape_string($post['evaluationmethods']);
		$dob_commencement = mysql_real_escape_string($post['dob_commencement']);
		$time = strtotime($dob_commencement);
		$dob_commencement = custom_date_format("Y-m-d",$dob_commencement);
		$num_bids = 0;
		$dob_evaluation = mysql_real_escape_string($post['dob_evaluation']);
		$time = strtotime($dob_evaluation);
		$dob_evaluation =  custom_date_format("Y-m-d",$dob_evaluation);
		$dob_cc = mysql_real_escape_string($post['dob_cc']);
		$time = strtotime($dob_cc);
		$dob_cc =  custom_date_format("Y-m-d",$dob_cc);
		$cc_award_date =$post['cc_award_date'];
		$cc_award_date =  custom_date_format("Y-m-d",$cc_award_date);
		
		$date_of_evaluation =    custom_date_format("Y-m-d",$post['date_of_evaluation']);
		/* !empty($post['date_of_evaluation']) ? date('Y-m-d',strtotime($post['date_of_evaluation'])) : ''; */
		$date_of_final_evaluation =   custom_date_format("Y-m-d",$post['date_of_final_evaluation']);
		// !empty($post['date_of_final_evaluation']) ? date('Y-m-d',strtotime($post['date_of_final_evaluation'])) : '';		
		$cc_approval_date =  custom_date_format("Y-m-d",$post['cc_approval_date']);
		// !empty($post['cc_approval_date']) ? date('Y-m-d',strtotime($post['cc_approval_date'])) : '';
		
		
		/*Check Dates */
		
		if(empty($date_of_evaluation))
		{
			return "Enter Date of Evaluation ";
		}
		
		if($date_of_final_evaluation < $date_of_evaluation )
		{
			return "Date of Final Evaluation Can Not Be Before Date of Evaluation ";
		}
		
			if($cc_approval_date < $date_of_final_evaluation )
		{
			return "Contracts Committee Approval Date can NOt be Before Date of Final Evaluation  ";
		}
		
		
		if($cc_award_date < $cc_approval_date )
		{
			return "Contracts Committee Award Date can NOt be Before  Committee Approval Date  ";
		}
		 
		
		
		$bebname = mysql_real_escape_string($post['bebname']);
		$beb_nationality = mysql_real_escape_string($post['beb_nationality']);
		$contractprice = removeCommas($post['contractprice']);
		$currency = mysql_real_escape_string($post['currence']);
		$bidid =   mysql_real_escape_string($post['bidid']);
		$ispublished = mysql_real_escape_string($post['btnstatus']) == 'publish' ? 'Y' : 'N';		
		$numorblocal = 0;


		$data = $this->session->all_userdata();
		$author = $data['userid'];
		
		//evaluationmethod
		$var = $this->session->userdata;

		$bebid ='';
		if((isset($var['editbeb_disposal'] )) && (!empty($var['editbeb_disposal'])))
		{
			$bebid = $var['editbeb_disposal'];
		}

		$data = array(
				'pid' => $bebname,
				'bidid' => $bidid,			    
				'evaluationmethod' => $evaluationmethod, 
				'dateofcommencement' => $dob_commencement,
				'numbids' => $num_bids,
				'dobevaluation' => $dob_evaluation,
				'datecc' => $dob_cc,
				'bebname' => $bebname,
				'bebnationality' => $beb_nationality,
				'contractprice' => $contractprice,
				'currency' => $currency,
				'author' => $author,
				'ispublished' => $ispublished,
				'bebid' => $bebid,
				'lot' => $lotid,
				'numorblocal' => $numorblocal,
				'cc_award_date' => $cc_award_date ,
				
				
				'date_of_evaluation'=>$date_of_evaluation,
				'date_of_final_evaluation'=>$date_of_final_evaluation,
				'cc_approval_date'=>$cc_approval_date
				
		);


 
		#updatebeb
		if(!empty($bebid)){
			$query = $this->Query_reader->get_query_by_code('update_beb_disposal',  $data);
			$result = $this->db->query($query);
			log_action('update',' A BEB  Disposal  Record has been Created ', '  A BEB  Disposal  Record has been Created');  
   
			#exit($this->db->last_query());
			$this->session->unset_userdata('editbeb_disposal');
		}
		else{
			$query = $this->Query_reader->get_query_by_code('insertbebdisposal',  $data);
			$result = $this->db->query($query);
			log_action('create',' A BEB  Disposal  Record has been Updated ', '  A BEB  Disposal  Record has been Updated ');  
   
		}

		 
		$query = mysql_query("UPDATE disposal_receipts set beb ='Y'  where receiptid = $bebname  and bid_id = $bidid") or die("".mysql_error());
		$query = mysql_query("UPDATE disposal_receipts set beb ='N' where receiptid != $bebname  and bid_id = $bidid") 	or die("".mysql_error());
	 

		//answerd: reason why not successful
		if(!empty($post['answered'])){
			$answerd = $post['answered'];
			foreach ($answerd as $key => $value) {
				# update the questionaire for everu single entry
				$query  = mysql_query("UPDATE receipts set reason = '".$value."' where receiptid = ".$key) or die("".mysql_error());
			}
		}
		return 1;

	}
	//end disposal

	//function fetch receipts based on receipt id

	function fetchreceiptid($receiptid){
		$query = mysql_query("SELECT a.*,b.providernames FROM receipts as a inner join providers  as b  on a.providerid  = b.providerid inner join bidinvitations as c on  a.bid_id = c.id   where a.receiptid = ".$receiptid) or die("".mysql_error());
		return $query;
	}

	function remove_restore_receipt($type,$receiptid){

		switch ($type) {
			case 'restore':
				# code...
				$query = $this->Query_reader->get_query_by_code('archive_restorereceipts', array('ID'=>$receiptid,'STATUS' => 'Y' ));
				$result = $this->db->query($query);
				if($result)
					return 1;

				break;
			case 'archive':
				$query = $this->Query_reader->get_query_by_code('archive_restorereceipts', array('ID'=>$receiptid,'STATUS' => 'N' ));
				// print_r($query);
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

	#mover codes
	function fetchbeb($data,$searchstring='')
	{
		# and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y" order by bestevaluatedbidder.dateadded DESC

		# $query = $this->Query_reader->get_query_by_code('fetchbebs', array('SEARCHSTRING' => $searchstring.' and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y" order by bestevaluatedbidder.dateadded DESC','limittext' => '' ));
        # print_r($query); exit(); 

		$result = paginate_list($this, $data, 'fetchbebs', array('SEARCHSTRING' => $searchstring.' and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y" order by bestevaluatedbidder.dateadded DESC'),10);
		return $result;

	/*	$issactive = "Y";
		$result = paginate_list($this, $data, 'fetchbebs', array('SEARCHSTRING' => 'and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y" order by bestevaluatedbidder.dateadded DESC'),1);
		return $result;   */

	}
	
	
	//take actions on bebs 
	function manage_beb_action($post)
	{
		$bebid  = 0;
		$action = mysql_real_escape_string($post['action']);
		if(!empty($post['dataid']))
			$bebid =  mysql_real_escape_string($post['dataid']);
		switch($action)
		{

			// Mark and Unmark Under Review
			case 'underreview':
				$status =  mysql_real_escape_string($post['status']);
				$query = $this->db->query("UPDATE bestevaluatedbidder SET isreviewed = '".$status."'  where id=".$bebid);
				log_action('Review',' A   BEB is Under Admin Review   '.$bebid, ' A   BEB is Under Admin Review   '.$bebid);  
   
				$msg = '1';
				return $msg;
				break;

			//cance BEB proccess @mover
			/*
			 * Cancle BEB Proccess Happens when You dont what to return back to the Selection of a new BEB but just stopping the proccess
			 *  At that Point  of course with reason and date cancelled  and who cancelled 
			 * 
			 * update the ENUM to C
			 */ 
			case 'cancelbeb':
				$databidid =  mysql_real_escape_string($post['id']);
				
				$termination_reason = mysql_real_escape_string($post['termination_reason']);  
				$date_contract_terminated = !empty($post['date_contract_terminated']) ?  custom_date_format("Y-m-d",$post['date_contract_terminated']): "" ;  
				$userid = $this->session->userdata['userid'];
				$query = $this->db->query("UPDATE   bestevaluatedbidder SET isactive='C' ,reason_for_cancellation='".$termination_reason."',date_cancelled='".$date_contract_terminated."',who_cancelled='".$userid."'  where id=".$databidid) or die("Manage Bebs : ".mysql_error());
				
					log_action('cancel',' A  BEB has been Cancelled ', ' A   BEB  has been cancelled ');  
   
				#print_r($this->db->last_query());
				#exit();
				
				//$query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where bid_id=".$databidid) or die("Manage Bebs : ".mysql_error());
				$msg = '1';
				return $msg;  
				
				return  1;
				break;
				
				
				
			//REvert BEB proccess @mover
			/*
			 *  Revert BEb Proccess to Active after Cancellation :
			   We Use Revert to Undo the Cancellation of a given Record XX 
			 * update the ENUM to C
			 */ 
			case 'revertbeb':
				$dataid =  mysql_real_escape_string($post['dataid']);
				
			 #   print_r($post);
				
				$termination_reason = mysql_real_escape_string($post['termination_reason']);  
				$date_contract_terminated = !empty($post['date_contract_terminated']) ? custom_date_format("Y-m-d",$post['date_contract_terminated']) : "" ;  
				$userid = $this->session->userdata['userid'];
				$query = $this->db->query("UPDATE   bestevaluatedbidder SET isactive='Y' where id=".$dataid) or die("Managex Bebs : ".mysql_error());
				
				log_action('revert',' A  BEB has been Reverted back to Active ', '  A  BEB has been Reverted back to Active ');  
   
				#print_r($this->db->last_query());
				#exit();
				
				//$query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where bid_id=".$databidid) or die("Manage Bebs : ".mysql_error());
				$msg = '1';
				return $msg;  
				
				return  1;
				break;
				
				


		case 'delbeblot':
		 $receiptid = mysql_real_escape_string($post['receiptid']);

		 $query = $this->db->query("DELETE FROM  bestevaluatedbidder   where pid = ".$receiptid." " ) or die("Manage Bebs LOTS  : ".mysql_error());
		  $query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where receiptid=".$receiptid) or die("Manage Bebs RECEIPTS : ".mysql_error());
		 log_action('delete',' A Lotted  BEB  Record has been Deleted ', '  A Lotted  BEB  Record has been Deleted  ');  

		 	$msg = '1';
				return $msg;  

		 break;


			//Delete BEB Proccess  @mover
			/*
			 * Deleting the BEB record so that you return the status quo before the decision was taken for the BEB 
			 */ 
			case 'delbeb':
				$databidid =  mysql_real_escape_string($post['databidid']);
				$query = $this->db->query("DELETE FROM  bestevaluatedbidder   where id=".$bebid) or die("Manage Bebs : ".mysql_error());
				$query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where bid_id=".$databidid) or die("Manage Bebs : ".mysql_error());
				$msg = '1';
			
			    log_action('delete',' A  BEB has been Deleted ', '  A  BEB has been Deleted  ');  
   
				return $msg;
				break;


			
			//Publish BEB
			/*Publish BEB, Is meant to  make visible an already saved BEB record to the front ENd by the Public
			 * 			 * 
			 */ 
			case 'publishbeb':
				$status =  mysql_real_escape_string($post['status']);
				$query = $this->db->query("UPDATE bestevaluatedbidder SET ispublished = '".$status."'  where id=".$bebid);
				log_action('delete',' A  BEB  Record has been Updated ', '  A  BEB  Record has been Updated  ');  
   
				$msg = '1';
				return $msg;
				break;

			 
				
				
				
			 
				

			default:
				break;

		}
	}

	#FETCH BEB WITH LOTS

	function manage_beb_wholelots_action($post)
	{
		$action = mysql_real_escape_string($post['action']);
		$bebid =  mysql_real_escape_string($post['dataid']);
		switch($action){

			// Mark and Unmark Under Review
			case 'underreview':
				$status =  mysql_real_escape_string($post['status']);
				$query = $this->db->query("UPDATE bestevaluatedbidder SET isreviewed = '".$status."'  where pid in (SELECT receiptid FROM receipts where bid_id =".$bebid.") ");
				log_action('update',' A Lotted  BEB  Record has been Updated ', '  A Lotted  BEB  Record has been Updated  ');  
   
				$msg = '1';
				return $msg;
				break;
				
			 	
				
				

			//cance BEB proccess
			case 'delbeb':
				$databidid =  mysql_real_escape_string($post['databidid']);
				$query = $this->db->query("DELETE FROM  bestevaluatedbidder   where pid in (SELECT receiptid FROM receipts where bid_id =".$databidid.") " ) or die("Manage Bebs : ".mysql_error());
				$query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where bid_id=".$databidid) or die("Manage Bebs : ".mysql_error());
				log_action('delete',' A Lotted  BEB  Record has been Deleted ', '  A Lotted  BEB  Record has been Deleted  ');  
   
				$msg = '1';
				return $msg;
				break;


				case 'delbeblot':
		 $receiptid = mysql_real_escape_string($post['receiptid']);

		 $query = $this->db->query("DELETE FROM  bestevaluatedbidder   where pid = ".$receiptid." " ) or die("Manage Bebs LOTS  : ".mysql_error());
		  $query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where receiptid=".$receiptid) or die("Manage Bebs RECEIPTS : ".mysql_error());
		 log_action('delete',' A Lotted  BEB  Record has been Deleted ', '  A Lotted  BEB  Record has been Deleted  ');  

		 	$msg = '1';
				return $msg;  
				
		 break;
		 
				
				
				//cance BEB proccess @mover
			/*
			 * Cancle BEB Proccess Happens when You dont what to return back to the Selection of a new BEB but just stopping the proccess
			 *  At that Point  of course with reason and date cancelled  and who cancelled 
			 * 
			 * update the ENUM to C
			 */ 
			case 'cancelbeb':
				$databidid =  mysql_real_escape_string($post['databidid']);
				
				$termination_reason = mysql_real_escape_string($post['termination_reason']);  
				$date_contract_terminated = !empty($post['date_contract_terminated']) ? custom_date_format("Y-m-d",$post['date_contract_terminated']) : "" ;  
				$userid = $this->session->userdata['userid'];
				$query = $this->db->query("UPDATE   bestevaluatedbidder SET isactive='C' ,reason_for_cancellation='".$termination_reason."',date_cancelled='".$date_contract_terminated."',who_cancelled='".$userid."'  WHERE  pid in (SELECT receiptid FROM receipts where bid_id =".$databidid.") " ) or die("Manage Bebs : ".mysql_error());
				#$query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where bid_id=".$databidid) or die("Manage Bebs : ".mysql_error());
				log_action('cancel',' A Lotted  BEB  Record has been Cancelled ', '  A Lotted  BEB  Record has been Cancelled');  
   
				#print_r($this->db->last_query());
				#exit();
				
				//$query = $this->db->query("UPDATE receipts SET beb ='P', reason='' where bid_id=".$databidid) or die("Manage Bebs : ".mysql_error());
				$msg = '1';
				return $msg;  
				
				return  1;
				break;
				
				
				
					//REvert BEB proccess @mover
				/*
				 * 
				   Revert BEb Proccess to Active after Cancellation :
				   We Use Revert to Undo the Cancellation of a given Record XX 
				 * update the ENUM to C  Ajax Function to a Javascript function in moverjs.js called  $(".revert_beb").click(function()
				 */ 
			case 'revertbeb':
			
			
						$databidid =  mysql_real_escape_string($post['databidid']);
						
						$termination_reason = mysql_real_escape_string($post['termination_reason']);  
						 
						$query = $this->db->query("UPDATE   bestevaluatedbidder SET isactive='Y'  WHERE  pid in (SELECT receiptid FROM receipts where bid_id =".$databidid.") " ) or die("Manage Bebs : ".mysql_error());
						log_action('revert',' A Lotted  BEB  Record has been Reverted Back to Active ', '  A Lotted  BEB  Record has been Updated Back to Active ');  
   
						$msg = '1';
						return $msg;  
				
				return  1;
				break;
				
				
				
			/*
			USED to publish a given BEB ; Simply changing its Status to Y "ispublished"
			*/
			case 'publishbeb':
				$status =  mysql_real_escape_string($post['status']);
				$query = $this->db->query("UPDATE bestevaluatedbidder SET ispublished = '".$status."' where pid in (SELECT receiptid FROM receipts where bid_id =".$bebid.")" );
				log_action('create',' A Lotted  BEB  Record has been Published ', '  A Lotted  BEB  Record has been Published');  
   
				$msg = '1';
				return $msg;
				break;



			default:
				break;

		}
	}



	#Fetch Lots on a given Reference Number ;
	function fetchlots($bidd)
	{
		$bidd = mysql_real_escape_string($bidd);
		$query = $this->Query_reader->get_query_by_code('fetch_lots', array('SEARCHSTRING' => ' a.bid_id ="'.$bidd.'"  AND b.haslots="Y"   AND a.isactive ="Y"         ','limittext' => '','orderby' => ''));
		#print_r($query); exit();
		$result = $this->db->query($query)->result_array();
		return $result;

	}


	function fetch_disposal_receipts($bidid)
	{

		#$procurement = mysql_real_escape_string($procurementrefno);
		$query = $this->Query_reader->get_query_by_code('fetch_disposal_receipts', array('SEARCHSTRING' => ' where disposal_bid_invitation.id = \''.$bidid.' \' ','orderby' => ''));
		# print_r($query); exit();
		$result = $this->db->query($query)->result_array();
		return $result;

	}

	function count_bids_disposal($nationality ='local',$bidid = 0)
	{
		if($nationality == 'uganda'){
			$query = mysql_query("SELECT  COUNT(*) as sm FROM  disposal_receipts WHERE bid_id = " .$bidid." AND nationality  ='".$nationality."' ");

		}
		else
		{
			$query = mysql_query("SELECT  COUNT(*) as sm FROM  disposal_receipts WHERE bid_id = " .$bidid." AND nationality != 'uganda' ");

		}

		$result  = mysql_fetch_array($query);
		return $result;
	}


	function fetch_bid_information($bidid){

		#$query = $this->Query_reader->get_query_by_code('fetch_disposal_receipts', array('SEARCHSTRING' => ' where disposal_bid_invitation.id = \''.$bidid.' \' ','orderby' => ''));
		# print_r($query); exit();
		$result = $this->db->query("SELECT * FROM disposal_bid_invitation where disposal_bid_invitation.id = ".$bidid)->result_array();
		return $result;
	}

	//fetch unsuccessful bidders
	function fetch_disposal_unsuccessful_bidders($receiptid,$bidid)
	{

		#$procurement = mysql_real_escape_string($procurementrefno);
		$query = $this->Query_reader->get_query_by_code('fetch_disposal_receipts', array('SEARCHSTRING' => ' where disposal_bid_invitation.id = \''.$bidid.' \'  and  disposal_receipts.receiptid != \''.$receiptid.'\' ' ,'orderby' => ''));
		#print_r($query); exit();
		$result = $this->db->query($query)->result_array();
		return $result;

	}

	function fetch_lots_awarded_beb_incontracts($data,$post)
	{
		#print_r($post);
		$bidid = $post['bidid'];
		$searchstring = '   receipts.bid_id  = "'.mysql_real_escape_string($_POST['bidid']).'" AND receipts.beb="Y" AND contracts.isactive="Y"  AND bestevaluatedbidder.isreviewed="N"';
		/*  $query = $this->Query_reader->get_query_by_code('fetch_contracts_lots', array('SEARCHSTRING' =>$searchstring ));
          print_r($query);
          exit(); */
		$this->db->query("SET OPTION SQL_BIG_SELECTS = 1");

		$result = paginate_list($this, $data, 'fetch_contracts_lots', array('SEARCHSTRING' => $searchstring.''),10);

		#exit($this->db->last_query());
		# print_r($result);

		return $result;
	}


	function get_receipts_by_procurement_method($procurement_method_id,$from='',$to=''){

		//ifranges are set
		if($from && $to)
		{
			$results=$this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.details,
receipts.received_by,
receipts.datereceived,
receipts.approved,
receipts.nationality,
receipts.author,
receipts.dateadded,
receipts.beb,
receipts.reason,
receipts.isactive,
receipts.joint_venture,
receipts.readoutprice,
receipts.currence,
bidinvitations.procurement_id,
bidinvitations.procurement_ref_no,
bidinvitations.cost_estimate,
bidinvitations.subject_of_procurement,
bidinvitations.pde_id,
bidinvitations.id,
procurement_plan_entries.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.procurement_plan_id,
procurement_plans.pde_id,
procurement_plans.title,
procurement_plans.financial_year,
procurement_plans.id,
pdes.pdeid,
pdes.pdename,
providers.providerid,
providers.providernames,
procurement_methods.title,
procurement_methods.id
FROM
receipts
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN procurement_methods ON procurement_methods.id = procurement_plan_entries.procurement_method
WHERE
procurement_methods.id = " . $procurement_method_id . " AND
receipts.datereceived >= '" . $from . "' AND
receipts.datereceived <= '" . $to . "'  AND
receipts.isactive = 'Y'");
		}
		else{
			$results=$this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.details,
receipts.received_by,
receipts.datereceived,
receipts.approved,
receipts.nationality,
receipts.author,
receipts.dateadded,
receipts.beb,
receipts.reason,
receipts.isactive,
receipts.joint_venture,
receipts.readoutprice,
receipts.currence,
bidinvitations.procurement_id,
bidinvitations.procurement_ref_no,
bidinvitations.cost_estimate,
bidinvitations.subject_of_procurement,
bidinvitations.pde_id,
bidinvitations.id,
procurement_plan_entries.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.procurement_plan_id,
procurement_plans.pde_id,
procurement_plans.title,
procurement_plans.financial_year,
procurement_plans.id,
pdes.pdeid,
pdes.pdename,
providers.providerid,
providers.providernames,
procurement_methods.title,
procurement_methods.id
FROM
receipts
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN procurement_methods ON procurement_methods.id = procurement_plan_entries.procurement_method
WHERE
procurement_methods.id = " . $procurement_method_id . " AND
receipts.isactive = 'Y'");
		}
		return $results;
	}



	function get_bid_receipts_by_contract($contract_id){
		$results=$this->custom_query("SELECT
receipts.receiptid,
receipts.bid_id,
receipts.nationality,
receipts.beb,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.approvedby,
bidinvitations.date_approved,
bidinvitations.isapproved,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds,
bidinvitations.quantity,
contracts.dateawarded,
contracts.performance_rating,
contracts.actual_completion_date,
contracts.total_actual_payments,
contracts.final_contract_value,
contracts.date_signed,
contracts.date_of_sg_approval,
contract_prices.amount,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.estimated_amount
FROM
receipts
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN contracts ON bidinvitations.procurement_id = contracts.procurement_ref_id
INNER JOIN contract_prices ON contract_prices.contract_id = contracts.id
INNER JOIN procurement_plan_entries ON contracts.procurement_ref_id = procurement_plan_entries.id
WHERE
receipts.isactive = 'Y' AND
bidinvitations.isactive = 'Y' AND
contracts.isactive = 'Y' AND
contracts.id = '".$contract_id."'
");

		return $results;
	}





}
