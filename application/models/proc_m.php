<?php

class Proc_m extends CI_Model {

	#Constructor
	function Proc_m()
	{
		parent::__construct();
		$this->load->model('Query_reader', 'Query_reader', TRUE);
		$this->load->model('Ip_location', 'iplocator');
	}

	function fetch_annual_procurement_plan($refid=0){
		$data = array('SEARCHSTRING' => ' 1 and 1  and bidinvitations.id="'.$refid.'"' );
		$query = $this->Query_reader->get_query_by_code('active_procurements',$data);
		#print_r($this->db->last_query());
		#print_array($this->db->_error_message());
		#exit;
		$result = $this->db->query($query)->result_array();;
		return $result;

	}

	#FETCH BIDS ON A GIGEN PROCUREMENT
	function feetch_active_procurement($idx = 0)
	{
		switch ($idx) {
			case 0:
				# code...

				$userid = $this->session->userdata['userid'];
				$pde =  $this->session->userdata['pdeid'];

				$data = array('SEARCHSTRING' => ' 1 = 1 and  users.userid = '.$userid );
				$query = $this->Query_reader->get_query_by_code('view_bidders_list',$data);
				$result = $this->db->query($query)->result_array();;
				return $result;


				break;

			default:
				# code...
				break;
		}
	}

	#Fetch Active Procurement List
	function fetch_active_procurement_list($idx = 0,$data){

		$pde =  $this->session->userdata['pdeid'];
		$current_financial_year = currentyear.'-'.endyear;
		$userid = $this->session->userdata('userid');



		#edit beb chain proccess ...
		if(isset($data['editbeb']) && !empty($data['editbeb']))
		{
			$bebid = mysql_real_escape_string(base64_decode($data['editbeb']));
			$this->db->query('SET SQL_BIG_SELECTS=1');
			$results = paginate_list($this, $data, 'active_procurement_list',  array('SEARCHSTRING' => ' AND procurement_plan_entries.isactive ="Y" AND bidinvitations.isactive ="Y" AND procurement_plans.isactive="Y"    AND  IF(bidinvitations.procurement_method_ifb > 0,  if(bidinvitations.procurement_method_ifb IN("1","2","9","11"),bidinvitations.isapproved="Y",1=1),  IF(procurement_plan_entries.procurement_method   IN("1","2","9","11"),bidinvitations.isapproved="Y" ,1=1))
 AND users.userid = '.$userid.'   AND bidinvitations.id  in (SELECT receipts.bid_id  FROM receipts    INNER JOIN bestevaluatedbidder b ON receipts.receiptid = b.pid    where IF(procurement_plan_entries.framework = "Y",receipts.beb !="P", receipts.beb="Y" )  AND b.id = '.$bebid.'  )  '    ),300);

			#	exit($this->db->last_query());
			return $results;

		}else
		{

			$this->db->query('SET SQL_BIG_SELECTS=1');
			$result = paginate_list($this, $data, 'active_procurement_list',  array('SEARCHSTRING' => ' AND procurement_plan_entries.isactive ="Y" AND bidinvitations.isactive ="Y" AND procurement_plans.isactive="Y"   AND  procurement_plans.financial_year LIKE "'.$current_financial_year.'" AND  IF(bidinvitations.procurement_method_ifb > 0,  if(bidinvitations.procurement_method_ifb IN("1","2","9","11"),bidinvitations.isapproved="Y",1=1),  IF(procurement_plan_entries.procurement_method   IN("1","2","9","11"),bidinvitations.isapproved="Y" ,1=1))
	 AND users.userid = '.$userid.'   AND bidinvitations.id not in (SELECT DISTINCT receipts.bid_id FROM   receipts     INNER JOIN bestevaluatedbidder b ON receipts.receiptid = b.pid   where receipts.beb="Y" AND b.isactive="Y"  )  '    ),300);

			return $result;
		}

	}


	function fetch_active_procurement_list2($idx = 0)
	{


		switch ($idx) {
			case 0:
				$userid = $this->session->userdata['userid'];
				$pde = mysql_query("select * from  users where userid =".$userid);
				$q = mysql_fetch_array($pde);
				$query = $this->Query_reader->get_query_by_code('active_procurement_list', array('SEARCHSTRING' => '  and 	users.userid = '.$userid.' ','limittext' => ''));
				//	$result = paginate_list($this, $data, 'active_procurement_list',  array('SEARCHSTRING' => ' 1 and 1 and 	users.userid = '.$userid.' and procurement_plan_statuses.status_id = 3' ),10);
				$result = $this->db->query($query)->result_array();
				return $result;
				break;

			default:
				# code...
				break;
		}


	}
	function fetchcountries(){

		$query = $this->db->query('SELECT *  FROM countries');
		return $query->result_array();

	}

	# FETCH Best Evaluated Bidder  LIST

	function count_beb_list($idx = 0,$data=array(),$searchstring='')
	{


		$searchstring .= ' AND  procurement_plans.financial_year like "%'.trim($data['current_financial_year']).'%"';

		switch ($data['level']) {

			#fetching Archived BEBs
			case 'archive':

				#exit("pass");
				# code...
				$userid = $this->session->userdata('userid');
				$pdeid = $this->session->userdata('pdeid');

				if($this->session->userdata('isadmin') == 'N'){
					$result = $query_active = $this->Query_reader->get_count('view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y" and bidinvitations.id  in ( select bidinvitation_id FROM contracts where isactive="Y"  )	and users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>'' ));
					return $result;
				}
				else
				{

					$searchstring = $this->Query_reader->get_query_by_code('num_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y" and bidinvitations.id  in ( select bidinvitation_id FROM contracts where isactive="Y"  )	'.$searchstring.'	  ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>''));

					$result = $query_active = $this->db->query($searchstring) -> result_array();
					$result = $result[0]['num_bebs'];

					//  $result = $query_active = $this->db->query($this->Query_reader->get_query_by_code('count_beb_list',  array('SEARCHSTRING' => '  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y" and bidinvitations.id  in ( select bidinvitation_id FROM contracts where isactive="Y"  )	'.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>'' )))->result_array();

					return $result;
				}


				break;

			#fetch active BEBs
			case 'active':

				$userid = $this->session->userdata('userid');
				$pdeid = $this->session->userdata('pdeid');



				if($this->session->userdata('isadmin') == 'N'){

					$result = $query_active = $this->Query_reader->get_count('view_bebs',  array('SEARCHSTRING' => '  AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts  where isactive="Y") 	and users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>'' ));
					return $result;
				}
				else
				{

					$searchstring = $this->Query_reader->get_query_by_code('num_bebs',  array('SEARCHSTRING' => '  AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts where isactive="Y"   ) '.$searchstring.'	  ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>''));

					$result = $query_active = $this->db->query($searchstring) -> result_array();
					$result = $result[0]['num_bebs'];

					return $result;
				}

		

				break;


			#fetch active BEBs
			case 'canceled':

				$userid = $this->session->userdata('userid');
				$pdeid = $this->session->userdata('pdeid');



				if($this->session->userdata('isadmin') == 'N'){

					$result = $query_active = $this->Query_reader->get_count('view_bebs',  array('SEARCHSTRING' => '  AND bestevaluatedbidder.isactive="C"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts  where isactive="Y") 	and users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>'' ));
					return $result;
				}
				else
				{

					$searchstring = $this->Query_reader->get_query_by_code('num_bebs',  array('SEARCHSTRING' => '  AND bestevaluatedbidder.isactive="C"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts where isactive="Y"   ) '.$searchstring.'	  ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>''));

					$result = $query_active = $this->db->query($searchstring) -> result_array();
					$result = $result[0]['num_bebs'];

					return $result;
				}

				break;




			default:
				break;
		}



	}


	# FETCH Best Evaluated Bidder  LIST
	function fetch_beb_list($idx = 0,$data=array(),$searchstring=''){
		#exit('erere');
		$this->db->cache_on();
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$searchstring .= ' AND  procurement_plans.financial_year like "%'.trim($data['current_financial_year']).'%"';

		/*
		Happens when data has been passed in the url to be skipped.
		Reason: at bebs we fetch some lotted bebs and Bundle them at Runtime. this affects the pagination and also the 
		integrity of Counts. so we have to skip that IFB  to avoid duplicate IFB at paginated BEBS
		*/
		if(!empty($data['skipdata']))
		{

			$bids =  implode(",",$data['skipdata']);
		#	$searchstring .= ' AND bidinvitations.id not in("'.rtrim($bids,',').'") ';

		}


		/*
		print_r($searchstring);
		exit();  */



		switch ($idx) {
			case 0:
				switch ($data['level']) {

					#fetching Archived BEBs
					case 'archive':
						# code...
						$userid = $this->session->userdata('userid');
						$pde =  $this->session->userdata['pdeid'];

						#$query = $this->Query_reader->get_query_by_code('view_bebs',  array('SEARCHSTRING' => ' and 1 and 1    and bidinvitations.procurement_id   in ( select procurement_ref_id FROM contracts  ) 	and users.userid = '.$userid.' ORDER BY bestevaluatedbidder.dateadded DESC','limittext'=>'limit 10' ));
						#print_r($query); exit();

						if($this->session->userdata('isadmin') == 'N'){
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y" and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y" and bidinvitations.id  in ( select bidinvitation_id FROM contracts where isactive="Y"  )	and users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							return $result;
						}
						else
						{
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y" and bidinvitations.id  in ( select bidinvitation_id FROM contracts where isactive="Y"  )	'.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							return $result;
						}


						break;

					#fetch active BEBs
					case 'active':

						$userid = $this->session->userdata('userid');

						if($this->session->userdata('isadmin') == 'N'){
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts  where isactive="Y") 	and users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							$this->db->cache_off();

							return $result;
						}
						else
						{
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts where isactive="Y"   ) '.$searchstring.'	  ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							$this->db->cache_off();


							return $result;
						}



						break;

					#Canceled
					case 'cacnceled':

						$userid = $this->session->userdata('userid');

						if($this->session->userdata('isadmin') == 'N'){
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="C"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts  where isactive="Y") 	and users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							$this->db->cache_off();
							return $result;
						}
						else
						{
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="C"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts where isactive="Y"   ) '.$searchstring.'	  ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							$this->db->cache_off();
							return $result;
						}



						break;


					default:
						$userid = $this->session->userdata('userid');

						if($this->session->userdata('isadmin') == 'N'){
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"  and bidinvitations.id not in ( select bidinvitation_id FROM contracts  ) and 	users.userid = '.$userid.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							$this->db->cache_off();
							return $result;
						}

						else{
							$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' AND bestevaluatedbidder.isactive="Y"   and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"   and bidinvitations.id not in ( select bidinvitation_id FROM contracts  ) '.$searchstring.' ORDER BY bestevaluatedbidder.dateadded DESC' ),10);
							$this->db->cache_off();
							return $result;
						}

						break;
				}


				break;

			default:
				# code...
				break;
		}
	}


	function fetch_admin_review($post,$data,$searchstring=''){

		$searchstring = $searchstring;


		if(!empty($post['bidid']) && $post['bidid'] > 0 )
		{
			$querycode = 'fetch_beb_review_details';
			$bidid = mysql_real_escape_string($post['bidid']);
			$searchstring .= ' AND bidinvitations.id = "'.$bidid.'"  AND beb_review_details.isactive="Y"';
			$string = $this->Query_reader->get_query_by_code('fetch_beb_review_details', array('searchstring' => $searchstring,'limittext'=>'') );

		}
		if(!empty($post['lotid']) && $post['lotid'] > 0 )
		{

			$querycode = 'fetch_beb_review_details_lots';
			$lotid = mysql_real_escape_string($post['lotid']);
			$searchstring .= ' AND beb_review_details.lotid = "'.$lotid.'" ' ;
			$string = $this->Query_reader->get_query_by_code('fetch_beb_review_details_lots', array('searchstring' => $searchstring,'limittext'=>'') );

		}
		#  print_r($string);

		$results = $this->db->query($string)->result_array();
		#print_r($results);
		return $results;


	}



	function findoutifreviews($bidid)
	{
		$query = $this->db->query("SELECT COUNT(*) as NUM FROM beb_review_details WHERE beb_review_details.bidid = ".mysql_real_escape_string($bidid)." ") ->result_array();
		return($query);
	}





}

?>
