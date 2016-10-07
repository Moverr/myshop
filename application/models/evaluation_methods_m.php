<?php

class Evaluation_methods_m extends CI_Model {
	
	#Constructor
	function Evaluation_methods_m()
	{
		parent::__construct();
		$this->load->model('Query_reader', 'Query_reader', TRUE);
		$this->load->model('Ip_location', 'iplocator');
	}


	#FETCH BIDS ON A GIGEN PROCUREMENT
	function fetchevaluationmethods($evaluationid = 0)
	{
		 
		 //
		// fetch_evaluation_methods
	 $data = array('SEARCHSTRING' => ' 1 and 1');	
	 $query = $this->Query_reader->get_query_by_code('fetch_evaluation_methods',$data);
	 # print_r($query); exit();
	 $result = $this->db->query($query)->result_array();
	 return $result;			

		 
	}
	
	# Disposal Methods
	function disposalfetchevaluationmethods($evaluationid = 0)
	{
		 
		 //
		// fetch_evaluation_methods
	 $data = array('SEARCHSTRING' => ' 1 and 1');	
	 $query = $this->Query_reader->get_query_by_code('fetch_disposal_evaluation_methods',$data);
	 # print_r($query); exit();
	 $result = $this->db->query($query)->result_array();
	 return $result;			

		 
	}
	

	
	
}

?>