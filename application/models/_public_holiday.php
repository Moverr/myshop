<?php

class _public_holiday extends CI_Model
{
	# Get list of public holidays
	function get_list($instructions=array())
	{
		$searchString = " 1=1 ";
		# If a search phrase is sent in the instructions
		if(!empty($instructions['searchstring']))
		{
			$searchString .= " AND ".$instructions['searchstring'];
		}
		
		$orderBy = (!empty($instructions['orderby']))? " ORDER BY ". $instructions['orderby'] : "ORDER BY PH.last_updated DESC";
		
		$count = !empty($instructions['pagecount'])? $instructions['pagecount']: NUM_OF_ROWS_PER_PAGE;
		$start = !empty($instructions['page'])? ($instructions['page']-1)*$count: 0;
		
		return $this->Query_reader->get_list('get_public_holidays', array('searchstring'=>$searchString, 'limittext'=>'LIMIT ' . $start.','.($count+1), 'orderby'=>$orderBy));
	}
	
	
	
	# add a public holiday
	function add($data)
	{
		$result = FALSE;
		$reason = '';
		
		$result = $this->Query_reader->run('add_public_holiday', array(
				'title'=>htmlentities($data['title'], ENT_QUOTES),  
				'description'=>htmlentities($data['description'], ENT_QUOTES),
				'holiday_date'=>date('Y-m-d', strtotime($data['holiday_date'])),
				'author'=>$this->session->userdata['userid']
		));
		
		return array('boolean'=>$result, 'reason'=>$reason);
	}
	
	
	# Update a public holiday
	function update($data)
	{
		$result = FALSE;
		$reason = '';
		
		if(!empty($data['editid'])):
			echo $this->Query_reader->get_query_by_code('update_public_holiday', array(
					'title'=>htmlentities($data['title'], ENT_QUOTES),  
					'description'=>htmlentities($data['description'], ENT_QUOTES),
					'holiday_date'=>date('Y-m-d', strtotime($data['holiday_date'])),
					'author'=>$this->session->userdata['userid'],
					'editid'=>$data['editid']
			));
			
			$result = $this->Query_reader->run('update_public_holiday', array(
					'title'=>htmlentities($data['title'], ENT_QUOTES),  
					'description'=>htmlentities($data['description'], ENT_QUOTES),
					'holiday_date'=>date('Y-m-d', strtotime($data['holiday_date'])),
					'author'=>$this->session->userdata['userid'],
					'editid'=>$data['editid']
			));
			
		endif;
		
		return array('boolean'=>$result, 'reason'=>$reason);
	}
	
}


?>