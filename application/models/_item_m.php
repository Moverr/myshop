<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class _item_m extends MY_Model
{
   
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }

    function verify($instructions){

      

      switch ($instructions['action']) {

        case "deleteitem":

            $id = $instructions['id'];

            $data_array  = array(
              'table' => 'items',
              'statuscolumn' => 'status',
              'status' => 'N',
              'id' => $id
              );




           $response =  $this->Query_reader->run("update_table_status",$data_array);


       
           if($response)
           {
             echo "1";
           }
           else
           {
            echo  "0";            
           }
           
        break;


        case "deletecategory":
        

            $id = $instructions['id'];

            $data_array  = array(
              'table' => 'item_categories',
              'statuscolumn' => 'status',
              'status' => 'N',
              'id' => $id
              );




           $response =  $this->Query_reader->run("update_table_status",$data_array);


       
           if($response)
           {
             echo "1";
           }
           else
           {
            echo  "0";            
           }

          

        break;
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



    function save_item($userid,$details) {

      $data['userdetails'] = $details;    
            $required_fields = array('category','abbreviation','item', 'details');
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
       
       $userid = $this->session->userdata('userid');
       $shopid = $this->session->userdata('shopid');
       $branch = $this->session->userdata('branch');




        
          //
          $data = array(
          'category_id'=> $details['category'],
          'name'=> $details['item'],
          'abbreviation'=> $details['abbreviation'],
          'details'=> $details['details'],
          'status'=> 'Y',          
          'added_by'=> $userid,
          'date_added'=>date('Y-m-d'),
          'shopid' =>$shopid,
          'branch_id' =>$branch
            );

 
          #When Editing 
          if(!empty($details['id']))
          {
              $data['id'] = $details['id'];
              $data_added =  $this->Query_reader->add_data('update_item',$data);
              $data_added =1;
          }
          else
          {
           
              $data_added =  $this->Query_reader->add_data('add_items',$data);
              
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






    function save_category($userid,$details) {

      $data['userdetails'] = $details;    
            $required_fields = array('itemcategory', 'details');
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
        
        $userid = $this->session->userdata('userid');
        $shopid = $this->session->userdata('shopid');
        $branch = $this->session->userdata('branch');

          //
          $data = array(
          'category'=> $details['itemcategory'],
          'abbreviation'=> $details['abbreviation'],
          'details'=> $details['details'],
          'status'=> 'Y',
          'added_by'=> $userid,
          'shopid'=>$shopid,
          'branch_id'=> $branch
            );

 
          #When Editing 
          if(!empty($details['id']))
          {
              $data['id'] = $details['id'];

              $data_added =  $this->Query_reader->add_data('update_itemcategories',$data);


                $data_added =1;
          }
          else
          {
                 $data_added =  $this->Query_reader->add_data('add_itemcategory',$data);
             
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

    function get_categories($instructions)
    {


       $userid = $this->session->userdata('userid');
       $shopid = $this->session->userdata('shopid');
       $branch = $this->session->userdata('branch');



      $query = "get_shops";
      $searchstring  = " 1=1  AND status ='Y' AND shopid = '".$shopid."' ";

      if($branch > 0 )
      {
         $searchstring  .= "  AND branch_id = '".$branch."' ";

      }


      if(!empty($instructions)){
        //get_shops

        if(!empty($instructions['id']))
        {
          $searchstring .= " AND id = '".$instructions['id']."' ";
            
        }
      }

      

    $data_array  = array(
      'searchstring' =>  $searchstring,
      'orderby' =>  ""
       
       );


    $response = paginate_list($this, $instructions, 'get_itemcategories',$data_array,!empty($instructions['limit']) ? $instructions['limit']   : 10);
   # print_r($response);

    return $response;


 
    }



    function get_latest_stock_details($instructions)
    {

       $userid = $this->session->userdata('userid');
       $shopid = $this->session->userdata('shopid');
       $branch = $this->session->userdata('branch');


       
       $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array()[0]; 

     

      $query = "get_shops";
      $searchstring  = " 1=1  AND IT.status ='Y'  AND IT.shopid = ".$shopid." ";

      if($branch > 0 )
      $searchstring  .= "   AND IT.branch_id = ".$branch." ";


      if(!empty($instructions)){
        //get_shops

        if(!empty($instructions['id']))
        {
          $searchstring .= " AND IT.id = '".$instructions['id']."' ";
            
        }
      }

      

    $data_array  = array(
      'searchstring' =>  $searchstring,
      'orderby' =>  ""
       
       );


    $response = paginate_list($this, $instructions, 'get_items',$data_array,1);
   # print_r($response);

 

    return $response;


 
    }




    function get_items($instructions)
    {

       $userid = $this->session->userdata('userid');
       $shopid = $this->session->userdata('shopid');
       $branch = $this->session->userdata('branch');


       
       $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array()[0]; 

     

      $query = "get_shops";
      $searchstring  = " 1=1  AND IT.status ='Y'  AND IT.shopid = ".$shopid." ";

      if($branch > 0 )
      $searchstring  .= "   AND IT.branch_id = ".$branch." ";


      if(!empty($instructions)){
        //get_shops

        if(!empty($instructions['id']))
        {
          $searchstring .= " AND IT.id = '".$instructions['id']."' ";
            
        }
      }

      

    $data_array  = array(
      'searchstring' =>  $searchstring,
      'orderby' =>  ""
       
       );


    $response = paginate_list($this, $instructions, 'get_items',$data_array,!empty($instructions['limit']) ? $instructions['limit']   : 10);
  // print_r($response);
//exit("movers");
 

    return $response;


 
    }
 

 

    

}