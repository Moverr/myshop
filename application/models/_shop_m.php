<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class _shop_m extends MY_Model
{
   
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }

    function verify($instructions){

     # print_r($instructions['action']);

      switch ($instructions['action']) {
        case "delete":
          
          if(!empty($instructions['i']))
          {
            $id = decryptValue($instructions['i']);

           
            $data_array  = array(
              'table' => 'shop',
              'columnname' => 'id',
              'columnid' => $id
              );

           $response =  $this->Query_reader->run("delete_from_table",$data_array);

           if($response)
           return true;

            return false;


            

           }
           break;
           default:
           break;
        }

      //     # code...
      //     break;
        
      //   default:
      //     # code...
      //     break;
      // }

    }




    function save_shop($userid,$details) {

 

      $data['userdetails'] = $details;    
            $required_fields = array('shopname', 'address');
      $details = clean_form_data($details);
      $validation_results = validate_form('', $details, $required_fields);


    if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) 
      && empty($data['msg']) )
      {

          $data['status'] = "fail";
          if(!empty($validation_results['errormsgs']))
          {         
            $data['msg'] = "WARNING: ".end($validation_results['errormsgs']);
            $data['errormsgs'] = $validation_results['errormsgs'];
          }
          else
          {
            $data['msg'] = "WARNING: The highlighted fields are required.";
          }       
          
          $data['requiredfields'] = $validation_results['requiredfields'];      
      }
      else
      {
          //
        $data = array(
        'shopname'=> $details['shopname'],
        'location'=> $details['address'],
        'isactive'=> 'Y',
        'author'=> $userid
          );

          #When Editing 
          if(!empty($details['id']))
          {
              $data['id'] = $details['id'];

              $data_added =  $this->Query_reader->add_data('update_shop',$data);


                $data_added =1;
          }
          else
          {
                 $data_added =  $this->Query_reader->add_data('add_shop',$data);
             
          }
                  
        if($data_added > 0)
        {
         $data['msg'] = "SUCCESSS: The Record has been successfully saved ";
         $data['status'] = "success";
        }
        else
        {
           $data['msg'] = "WARNING:  The Record Has not been saved  ";
           $data['status'] = "fail";
        }





      }

      
      return $data;

    }

    function get_shops($instructions)
    {

      $query = "get_shops";
      $searchstring  = " 1=1 ";
      if(!empty($instructions)){
        //get_shops

        if(!empty($instructions['id']))
        {
          $searchstring .= " AND id = '".$instructions['id']."' ";
            
        }
      }

      

    $data_array  = array(
      'searchstring' =>  $searchstring,
      'orderby' =>  "",
      'limittext' => ""
       
       );

    #$response1 = $this->db->query("select * from shops")->result_array();



   # $response = $this->Query_reader->get_list('get_shops',$data_array);

   

    $response = paginate_list($this, $instructions, 'get_shops',$data_array,!empty($instructions['limit']) ? $instructions['limit']   : 10);  
   
 

    #exit($this->db->last_query());

    return $response;


 
    }
 

    

}