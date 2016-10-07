<?php
 
	class Remoteapi_m extends CI_Model {
		
		#Constructor

		function Remoteapi_m()
		{
		
		 
			parent::__construct();
			$this->load->model('Query_reader', 'Query_reader', TRUE);
			$this->load->model('Ip_location', 'iplocator');		
		}

        function getppmsconnection()
        {
        }

        function fetchproviders()
        {
            //connection issues
            // $this->session->set_userdata($userdetails);
            // @$providerlist = $this->session->userdata['providerlist'];
            //$this->session->userdata['logged_in']['id'];
            $con = $this->getropconnection();
            // print_r($con); exit();
            $query = mysqli_query($con, "SELECT recordid, orgname from providers where is_active='Y' ") or die("" . mysql_error());
            $daa = array();
            //adding this information to the  userdata
            while ($row = mysqli_fetch_array($query)) {
                # code...
                $orgname = $row['orgname'];
                $daa[$row['recordid']] = $orgname;


                // exit();
            }
            //print_r($daa);
            mysqli_close($con);


            return (json_encode($daa));

        }

		function  getropconnection()
		{
			 // connection credentials //
			  $db = "ppdaprov_rop";
			  $server = "69.89.13.148";
			  $user = "ppdaprov_nwt";
			  $pass = "U*OL*ZHH}oH3";

			  $con=mysqli_connect($server,$user,$pass,$db) or die("".mysql_error());
			// Check connection
			if (mysqli_connect_error())
			  {

			   return 0;
              } else {
			 return $con;
			}
			  // connection string

		}

		//fetch providers and store em in a list which will be used for other includes and searches..

		function provider_details($providernames){
            $con = $this->getropconnection();
		 $query = mysqli_query($con,"SELECT orgname,corearea,tin,vat,legalstatus,regdate,street,plotno,floorno,town,district,country,tel,fax,address,email,website,bankname  from  providers where  orgname ='".$providernames."' ") or die("".mysql_error());
			mysqli_close($con);
			return ($query);
		}

	function un_confirmed_providers(){
        $con = $this->getropconnection();
		 $query = mysqli_query($con,"SELECT orgname,corearea,tin,vat,legalstatus,regdate,street,plotno,floorno,town,district,country,tel,fax,address,email,website,bankname  from  providers where confirmed = 0 limit 100  ") or die("".mysql_error());

			mysqli_close($con);
			return ($query);

		}

	function suspended_providers($start=0,$end=100){
	   $con = $this->getropconnection();
        //dateadded
		 $query = mysqli_query($con,"SELECT if(a.orgid>0,b.orgname,a.orgid) as orgname ,b.corearea,b.tin,b.vat,b.legalstatus,b.regdate,b.street,b.plotno,b.floorno,b.town,b.district,b.country,b.tel,b.fax,b.address,b.email,b.website,b.bankname,a.sus_start as datesuspended , a.reason , a.sus_end endsuspension,a.dateadded as dateadded,a.recordid,a.orgid,a.indefinite from  susprov as a  left outer join providers as b  on  a.orgid = b.recordid where a.sus_end > curDate() or a.indefinite='Y' order by a.dateadded DESC limit ".$start.", ".$end."") or die("error" );
		# print_r($query); exit();
			mysqli_close($con);
        return ($query);
		}

        function suspended_providers2($searchstring)
        {
	   $con = $this->getropconnection();
            //dateadded
		 $query = mysqli_query($con,"SELECT if(a.orgid>0,b.orgname,a.orgid) as orgname ,b.corearea,b.tin,b.vat,b.legalstatus,b.regdate,b.street,b.plotno,b.floorno,b.town,b.district,b.country,b.tel,b.fax,b.address,b.email,b.website,b.bankname,a.sus_start as datesuspended , a.reason , a.sus_end endsuspension,a.dateadded as dateadded,a.recordid,a.orgid,a.indefinite from  susprov as a  left outer join providers as b  on  a.orgid = b.recordid where (a.sus_end > curDate() or a.indefinite='Y') ".$searchstring." order by a.dateadded DESC limit 10") or die("error" );
		# print_r($query); exit();
			mysqli_close($con);
            return ($query);
		}

	function fetch_suspended_provider($recordid)
		{
            $con = $this->getropconnection();
            $query = mysqli_query($con, "SELECT  if(a.orgid>0,b.orgname,a.orgid) as orgname  , a.orgid as decider , b.corearea,b.tin,b.vat,b.legalstatus,b.regdate,b.street,b.plotno,b.floorno,b.town,b.district,b.country,b.tel,b.fax,b.address,b.email,b.website,b.bankname,a.sus_start as datesuspended , a.reason , a.sus_end endsuspension,a.dateadded as dateadded,a.recordid,a.orgid from  susprov as a  left outer join providers as  b on a.orgid = b.recordid where a.recordid =" . $recordid . "") or die("error");
		# print_r($recordid);
		#$row = mysqli_fetch_array($query);
		#print_r($row); exit('mover');
		 mysqli_close($con);
            return ($query);

		}
		
		function fetchpdes(){

            $con = $this->getpdeconnection();
			// print_r($con); exit();
			$query = mysqli_query($con,"SELECT pde_id, pde_name from pdes where status ='IN' ") or die("".mysql_error());
			$daa = array();
			//adding this information to the  userdata
			while ($row = mysqli_fetch_array($query)) {
				# code...
				$pdename = $row['pde_name'];
                $pdeid[$row['pde_id']] = $orgname;
			}
			//print_r($daa);
			mysqli_close($con);

            return (json_encode($daa));
			//exit();

        }

        function  getpdeconnection()
        {
            // connection credentials //
            $db = "nwprojects_ppms";
            $server = "localhost";
            $user = "root";
            $pass = "123admin";
            $con = mysqli_connect($server, $user, $pass, $db) or die("" . mysql_error());
            // Check connection
            if (mysqli_connect_error()) {

                return 0;
            } else {
                return $con;
            }

		}
		
		function fetchproviders_list(){
		//connection issues 
			// $this->session->set_userdata($userdetails);
			// @$providerlist = $this->session->userdata['providerlist'];
			 //$this->session->userdata['logged_in']['id'];
			 $con = $this->getropconnection();
			// print_r($con); exit();
			$query = mysqli_query($con,"SELECT recordid, orgname from providers where is_active='Y' ") or die("".mysql_error());
			 mysqli_close($con);
			 return $query;
		
		}
		
		function fetchproviders_list_by_id($id){
		//connection issues 
			// $this->session->set_userdata($userdetails);
			// @$providerlist = $this->session->userdata['providerlist'];
			 //$this->session->userdata['logged_in']['id'];
			 $con = $this->getropconnection();
			// print_r($con); exit();
			$query = mysqli_query($con,"SELECT recordid, orgname from providers where  recordid = ".$id." ") or die("".mysql_error());
			 mysqli_close($con);
			 return $query;
		
		}
		
		
		function save_suspended_provider($post){
			#print_r($post); exit();
				 $con = $this->getropconnection();
				 $orgid = $post['provider'];
				 $orgs = $post['provider2'];
				 #print_r($orgs); exit();
				  
				 $orgs  = trim($post['provider2']);
				 if($orgs !="")
				 {
				 $orgid  = mysql_real_escape_string($post['provider2']);
				 }
				 			 
				 
				
				
				 $reason =  trim($post['reason']);
				 if($post['status'] == 0)
				 {
				  $date = explode('-', $post['suspensionduration']);
				 $start_date = date('Y-m-d',strtotime($date['0']));
				 $end_date = date('Y-m-d',strtotime($date['1']));
				  $query = mysqli_query($con,"INSERT INTO susprov(orgid,sus_start,sus_end,reason) values('".$orgid."','".$start_date."','".$end_date ."','".$reason."')" );
				 }
				 else
				 {
				  $start_date = date('Y-m-d',strtotime($post['indifintedatestart']));
				  $indefinate = 'Y';
				  $query = mysqli_query($con,"INSERT INTO susprov(orgid,sus_start,reason,indefinite,sus_end) values('".$orgid."','".$start_date."','".$reason."','Y','0000:00:00')" );
				 }

				
				# mysqli_close($con);
				 return 1;
		}

	function update_suspesion($post,$id){
	 $con = $this->getropconnection();
				 $orgid = $post['provider'];
				 $date = explode('-', $post['suspensionduration']);
				 $reason =  trim($post['reason']);
				 $start_date = date('Y-m-d',strtotime($date['0']));
				 $end_date = date('Y-m-d',strtotime($date['1']));

				 $query = mysqli_query($con," UPDATE susprov SET  sus_start='".$start_date."', sus_end='".$end_date ."', reason='".$reason."' where recordid = $id " ) or die("ERROR");
				 mysqli_close($con);
				 return 1;
				 
		print_r($post); 
			
	}
		
		 function remove_restore_provider($type,$recordid){
		  $con = $this->getropconnection();

	    	switch ($type) {
	    		case 'restore':
	    			# code...
	    		 $query = mysqli_query($con," UPDATE susprov SET  sus_start='".$start_date."' where recordid = $id " ) or die("ERROR");
				  mysqli_close($con);
				if($query )			 
				 	return 1;

	    			break;
	    	    case 'archive':    	    
	    	    
				 $query = mysqli_query($con," DELETE FROM susprov  where recordid = $recordid" ) or die("ERROR");
				  mysqli_close($con);
				if($query )				 
				 	return 1;
				else
				 return 0;			
	    	    break;  
	    	    case 'delete':
	    	     
				  $query = mysqli_query($con," UPDATE susprov SET  sus_start='".$start_date."' where recordid = $id " ) or die("ERROR");
				  mysqli_close($con);
				if($query )				 
				 	return 1;
	    	    break;  		
	    		default:
	    			# code...
	    			break;
	    	}

	    } 
	    
	    
	   
	  
	    function providers_suspended()
	    {
	        $con = $this->getropconnection();
	        $result = $con->query("SELECT if(a.orgid>0,b.orgname,a.orgid) as orgname ,b.corearea,b.tin,b.vat,b.legalstatus,b.regdate,b.street,b.plotno,b.floorno,b.town,b.district,b.country,b.tel,b.fax,b.address,b.email,b.website,b.bankname,a.sus_start as datesuspended , a.reason , a.sus_end endsuspension,a.dateadded as dateadded,a.recordid,a.orgid,a.indefinite from  susprov as a  left outer join providers as b  on  a.orgid = b.recordid where a.sus_end > curDate() or a.indefinite='Y' order by a.dateadded DESC ");
	        
	        mysqli_close($con);

	        $data = array();
	        while($row = $result->fetch_assoc())
	        {
	            $data[] = $row;
	        }
	        return ($data);
	    }


	   function checkifsuspended($provider){
	   	$provider = trim($provider);
	    $con = $this->getropconnection();
	   //dateadded		 
		  $query = mysqli_query($con,"SELECT if(a.orgid>0,b.orgname,a.orgid) as orgname ,b.corearea,b.tin,b.vat,b.legalstatus,b.regdate,b.street,b.plotno,b.floorno,b.town,b.district,b.country,b.tel,b.fax,b.address,b.email,b.website,b.bankname,a.sus_start as datesuspended , a.reason , a.sus_end endsuspension,a.dateadded as dateadded,a.recordid,a.orgid,a.indefinite from  susprov as a  left outer join providers as b  on  a.orgid = b.recordid where (a.sus_end > curDate() or a.indefinite='Y') AND (b.orgname LIKE '".$provider."' OR a.orgid LIKE '".$provider."' )  order by a.dateadded DESC limit 1") or die("error".mysqli_error($con) );
		# print_r($query); exit();

			mysqli_close($con);
			return (mysqli_fetch_array($query));		 
		}

		 function emaillist_providers($sectors =''){

		$sector = rtrim($sectors,',');
	    $con = $this->getropconnection();
	   //dateadded		 
		  $query = mysqli_query($con,"SELECT  distinct providers.email,  providers.adminemail   FROM providersectors   INNER JOIN providers     ON providersectors.owner = providers.owner    INNER JOIN sectors     ON providersectors.sectortype = sectors.sectortype where providers.recordid  not in (SELECT orgid  FROM susprov  WHERE sus_end > curDate() or indefinite='Y'  )  and sectors.sectortype  in ('".$sectors."') limit 10 ") or die("error".mysqli_error($con) );
		 #$str = "SELECT  distinct providers.email,  providers.adminemail   FROM providersectors   INNER JOIN providers     ON providersectors.owner = providers.owner    INNER JOIN sectors     ON providersectors.sectortype = sectors.sectortype where sectors.sectortype  in ('".$sectors."') limit 100 ";
		
		 // print_r($str); exit();
		
		# print_r($query); exit();
		#  WHERE sus_end > curDate() or indefinite='Y' 
		# providers.recordid  not in (SELECT orgid  FROM susprov )  and 
			mysqli_close($con);
             return ($query);
         }


        #suspended_provider_betweendates
        function suspended_provider_betweendates($dateadded, $provider)
        {
            $con = $this->getropconnection();
            $str = "SELECT  if(a.orgid>0,b.orgname,a.orgid)    as orgname ,b.corearea,     b.tin,b.vat,b.legalstatus,     b.regdate,b.street,b.plotno,     b.floorno,b.town,b.district,     b.country,b.tel,b.fax,b.address,     b.email,b.website,b.bankname,     a.sus_start as datesuspended ,     a.reason ,     a.sus_end endsuspension,a.dateadded as dateadded,     a.recordid,a.orgid,a.indefinite       FROM  susprov as a  LEFT OUTER JOIN      providers as b  on  a.orgid = b.recordid      where  (b.orgname like '%" . $provider . "%' OR a.orgid  like '%" . $provider . "%')  AND  IF(a.indefinite ='N', a.sus_start <= '" . $dateadded . "'  AND a.sus_end >= '" . $dateadded . "' ,a.indefinite ='Y') ";

            $query = mysqli_query($con, $str) or die(mysqli_error($con));

            mysqli_close($con);
            return ($query);
        }







#Return all suspended providers 
		function provider_end_suspensions()
		{
	   		$con = $this->getropconnection();	 
		 	$query = mysqli_query($con,"
			SELECT 
				if(a.orgid > 0, b.orgname, a.orgid) AS orgname,
				b.corearea,
				b.tin,
				b.vat,
				b.legalstatus,
				b.regdate,
				b.street,
				b.plotno,
				b.floorno,
				b.town,
				b.district,
				b.country,
				b.tel,
				b.fax,
				b.address,
				b.email,
				b.website,
				b.bankname,
				a.sus_start as datesuspended,
				a.reason,
				a.sus_end endsuspension,
				a.dateadded as dateadded,
				a.recordid,
				a.orgid,
				a.indefinite
			FROM 
			susprov AS a LEFT OUTER JOIN providers AS b ON a.orgid = b.recordid WHERE a.sus_end > CURRENT_DATE() - INTERVAL 1 DAY OR a.indefinite = 'Y' ORDER BY a.dateadded DESC") or die("error");
			
		 	//print_array($query); exit();
			mysqli_close($con);
			return ($query);	

		}





    function all_providers_suspended($id='')
    {
        $con = $this->getropconnection();
		if($id){
			$result = $con->query("SELECT * FROM  susprov WHERE recordid=".$id." ORDER BY dateadded DESC ");

		}else{
			$result = $con->query("SELECT * FROM  susprov ORDER BY dateadded DESC ");

		}

        mysqli_close($con);

        $data = array();
        while($row = $result->fetch_assoc())
        {
            $data[] = $row;
        }
        return ($data);
    }




	function search_paginated_providers($searchstring){
		$con = $this->getropconnection();
		$query = mysqli_query($con,"SELECT if(a.orgid>0,b.orgname,a.orgid) as orgname ,b.corearea,b.tin,b.vat,b.legalstatus,b.regdate,b.street,b.plotno,b.floorno,b.town,b.district,b.country,b.tel,b.fax,b.address,b.email,b.website,b.bankname,a.sus_start as datesuspended , a.reason , a.sus_end endsuspension,a.dateadded as dateadded,a.recordid,a.orgid,a.indefinite from  susprov as a  left outer join providers as b  on  a.orgid = b.recordid where (a.sus_end > curDate() or a.indefinite='Y') ".$searchstring." order by a.dateadded DESC limit 10") or die("error" );

		mysqli_close($con);


		return ($query);
	}





 


		
	}
