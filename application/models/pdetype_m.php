<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Pdetype_m extends CI_Model
{
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }
    function insertpdetype($der,$erere){
      return 1;
    }
    #new pde 
    function insert_pde($pdename,$pdeabbreviation,$pdecategory,$pdetype,$pdecode,$pderollcat,$pdeaddress,$pdetel,$pdefax,$pdeemail,$pdewebsite, $roles,$step){
    	//pdename<>*pdeabbreviation<>*pdecategory<>*pdetype<>*pdecode<>pderollcat<>pdeaddress<>pdetel<>pdefax<>pdeemail<>pdewebsite<>pdeao<>pdeaophone<>pdecc<>pdeccphone<>pdeccemail<>pdeheadpduphone<>pdeheadpduemail<>pdeheadpdu

    if($step == 1){
            $data = array(   
           'pdename' => $pdename ,
           'abbreviation' => $pdeabbreviation,
           'status' =>'in',
           //'create_date' => NOW(),// current date time stamp
           'createby' => 1, // get the currently logged in session :: user id 
           'category' => $pdecategory,
           'type' => $pdetype,
           'code' => $pdecode,
           'pderollcat' => $pderollcat,
           'address' => $pdeaddress,
           'tel' => $pdetel,
           'fax' => $pdefax,
           'email' => $pdeemail,
           'website' => $pdewebsite,
           
        	);

   $query = $this->Query_reader->get_query_by_code('insertnewpde',  $data);
   $result = $this->db->query($query);
  
   $insert_id = mysql_insert_id();
   $this->session->set_userdata('insertid',$insert_id);

   
   

  return 1;
          }
      
      else  if($step == 2){

            $insertid =   $this->session->userdata('insertid');
            
            $roles = $roles;
            
            foreach ($roles as $key => $value) {
              # code...
               //update
              $data  = array('role' => $key, 'pdeid' => $insertid,'userid' => $value,'issactive'=>'Y' ,'');
             $query = $this->Query_reader->get_query_by_code('assignrole',  $data);    
             //print_r($query); exit();          
             $result = $this->db->query($query);
            }
            
                        
              # UNSET THE SESSION ID AND CONTINUE
             // $this->session->unset_userdata('insertid');
              return 1;
          }
          else{
            return 0;
          }

    }
    #safe delete or archive /restore pde 
    /*
    ARCHIVE AND DELETE PDE
    */
    function remove_restore_pde($type,$pdetypeid){

    	switch ($type) {
    		case 'restore':
    			# code...
    		 $query = $this->Query_reader->get_query_by_code('archive_restorepdes', array('ID'=>$pdetypeid,'STATUS' => 'in' ));
			$result = $this->db->query($query);
			if($result)			 
			 	return 1;

    			break;
    	    case 'archive':    	    
    	    $query = $this->Query_reader->get_query_by_code('archive_restorepdes', array('ID'=>$pdetypeid,'STATUS' => 'out' ));
			   // print_r($query);
          $result = $this->db->query($query);
			 if($result)			 
			 	return 1;
			else
			 return 0;			
    	    break;  
    	    case 'delete':
    	     $query = $this->Query_reader->get_query_by_code('deletepdetype', array('ID'=>$pdetypeid));
			//print_r($query);
       $result = $this->db->query($query);
			 if($result)			 
			 	return 1;
    	    break;  		
    		default:
    			# code...
    			break;
    	}

    }

	#parmanently  delete pde
    function parmanentdel_pde(){

    }
   
    #edit PDE
    function updatepdetype($pdetype,$pdetypedetails,$pdeid){
    	 
            $data = array(   
           'pdename' => $pdetype ,
           'abbreviation' => $pdetypedetails,           
            'id' => $pdeid        
           );
            $query = $this->Query_reader->get_query_by_code('updatepde',  $data); 
          	$result = $this->db->query($query);
          	return 1;
    }

    #handle server side validation for Pdes :: 
    function validate_pdes($formdata,$id =0,$datatype=''){
       
       $pdeid =  $id;
       $str = 0;


	       	switch ($datatype) {
	       		case 'insert': 

	       			# code...
		       		if(!empty($formdata['pdename']))
			          {
			                   # searchpde   
			               $searchstring = "pdename like '".mysql_real_escape_string($formdata['pdename'])."' "  ; 
			               $result  = $this-> db_check_pdes($searchstring);
			               if(count($result) > 0){
			               $str = 'PDE Name Already  Exists in the Database ';
			               
			               }
			          }
                   return($str);  
			         
	       			break;
	       		case 'update':
	       			# code...

			       		if(!empty($formdata['pdename']))
					       {					       
					                # searchpde   
					               $searchstring = "pdename like '".mysql_real_escape_string($formdata['pdename'])."' and pdeid != ".$pdeid ;
					               $result  = $this-> db_check_pdes($searchstring);
					               if(count($result) > 0){
					               	 $str = 'PDE Name Exists in the Database  for a Different PDE Authority';
			               	 		return($str);	
					               } 
					                
					        }
	       			break;
	       		
	       		default:
	       			# code...
	       			break;
	       	}
       		return $str;
    }
    //check pde based on a given where clause  and return outcome:
    //majorly used for validation :: SS
    function db_check_pdes($searchstring = '')
    {

   	$query = $this->Query_reader->get_query_by_code('searchpde', array('SEARCHSTRING' => $searchstring ) );    	       	 
		$result = $this->db->query($query)->result_array();
		return $result;    	
    }
    //Fetch  on or Many depending :: 
    function fetch_pdes($status='in'){
    	  	
    	$query = $this->Query_reader->get_query_by_code('fetchpdes', array('STATUS' => $status ) ); 
      
      
		$result = $this->db->query($query)->result_array();
		return $result;
    }

    #Fetch Pde Details
    function fetchpdedetails($idx = 0)
    {    	
    	
    	$query = $this->Query_reader->get_query_by_code('pdedetails', array('id' => $idx) ); 
		   
      $result = $this->db->query($query)->result_array();
		  return $result;
    }
    //fetch roles associated to a given pde:

    # user assigned roles
    function fetchroles($pdeid = 0){
      $query = $this->Query_reader->get_query_by_code('assignedroles  ', array('searchstring' => 'pdeid = '.$pdeid) );  
      $result = $this->db->query($query)->result_array();
      
      return $result;
     
    }

    

}