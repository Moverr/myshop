<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Pdetypes_m extends CI_Model
{
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }
    #Insert new record
    function insertpdetype($pdetype,$pdetypedetails,$issactive='Y'){
          $data = array(   
           'pdetype' => $pdetype,
           'typedetails' => $pdetypedetails,
           'status' => $issactive
           );
         // add_pdetype_data

    $query = $this->Query_reader->get_query_by_code('add_pdetype_data',  $data);
    $result = $this->db->query($query);
    return 1;
    }
    #FETCH PDES BASED ON STATUS
    function fetchpdetypes($status='Y',$data)
    {
        $result = paginate_list($this, $data, 'fetchpdetypes', array('STATUS'=>$status, 'orderby'=>' ORDER BY pdetypeid ' ,'searchstring'=>''),100);
		return $result;
    }


     #search pdetypes
     function search_pdetypes($s,$data,$searchstring='')
     {     
        $result = paginate_list($this, $data, 'fetchpdetypes', array('STATUS'=>$s, 'orderby'=>' ORDER BY pdetypeid ' ,'searchstring'=> $searchstring),200); 
        return $result;
    }



    #FETCH PDES BASED ON STATUS
    function fetchpdetypesa($status='Y')
    {
       $query = $this->Query_reader->get_query_by_code('fetchpdetypes', array('STATUS' => $status,'orderby'=>'','searchstring'=>'','limittext'=>'' ) );  
        #print_r($query); exit();   
        $result = $this->db->query($query)->result_array();
        return $result;
    }
    #FETCH ALL RECORDS
    function fetchallpdetypes(){

    }
    #FETCH PDETYPE BASED ON ID
    function fetchpdetype($id = 0)
    {
    	$query = $this->Query_reader->get_query_by_code('fetchpdetype', array('ID' => $id ) );      	 
		$result = $this->db->query($query)->result_array();
		return $result;
    }
    #edit PDE
    function updatepdetype($pdetype,$pdetypedetails,$pdetypeid){
         
            $data = array(   
           'pdetype' => $pdetype ,
           'details' => $pdetypedetails,           
            'id' => $pdetypeid        
           );
            $query = $this->Query_reader->get_query_by_code('updatepdetype',  $data);             
            $result = $this->db->query($query);

            return 1;
    }
    
    # 3 in one del archive and restore
     function remove_restore_pdetype($type,$pdetypeid){

        switch ($type) {
            case 'restore':
                # code...
          
             $query = $this->Query_reader->get_query_by_code('archive_restorepdetypes', array('ID'=>$pdetypeid,'STATUS' => 'Y' ));
         // print_r($query);
            $result = $this->db->query($query);
            if($result)          
                return 1;

                break;
            case 'archive':         
            $query = $this->Query_reader->get_query_by_code('archive_restorepdetypes', array('ID'=>$pdetypeid,'STATUS' => 'N' ));
                //print_r($query);
          $result = $this->db->query($query);
             if($result)             
                return 1;
            else
             return 0;          
            break;  
            case 'delete':
             $query = $this->Query_reader->get_query_by_code('deletepdetype', array('ID'=>$pdetypeid));
             $result = $this->db->query($query);
             if($result)             
                return 1;
            break;          
            default:
                # code...
                break;
        }

    }

}


?>