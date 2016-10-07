<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class _stock_m extends MY_Model
{
   
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }

    function verify($instructions){

      

      switch ($instructions['action']) {

        case "deletestock":

            $id = $instructions['id'];

            $data_array  = array(
              'table' => 'stock',
              'statuscolumn' => 'isactive',
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



    function save_stock($userid,$details) {

  

      $data['userdetails'] = $details;    
            $required_fields = array('date_of_stock', 'item','unitselling_price','unitselling_price_currency','reserve_price','reserve_price_currency','number_of_items','purchase_no','unit_measure');
      
      #unitselling_price_exchange_rate
      #reserve_price_exchange_rate


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


 

        $financial_year = trim(date('Y',strtotime( $details['date_of_stock']))."-".(date('Y',strtotime( $details['date_of_stock']))+1));
        $item_id = $details['item'];
        $purchase_no = $details['purchase_no'];
        $unit_measure = $details['unit_measure'];

        $number_of_items = $details['number_of_items'];
        #Unit selling Price

        $unitselling_price = !empty($details['unitselling_price']) ?  removeCommas($details['unitselling_price']) : "";
 
  

        #Unit Exchange Rate  ::
        $unitselling_price_exchange_rate = !empty($details['unitselling_price_exchange_rate']) ?    removeCommas($details['unitselling_price_exchange_rate']) : "1" ;


        #Unit Selling Price Currency        
        $unitselling_price_currency = !empty($details['unitselling_price_currency']) ? $details['unitselling_price_currency'] : "1" ;

        #Resever Proce
        $reserve_price = !empty($details['reserve_price']) ? removeCommas($details['reserve_price']) : " ";

        #Reserve Price Exhange Rate 
        $reserve_price_exchange_rate = !empty($details['reserve_price_exchange_rate']) ? removeCommas($details['reserve_price_exchange_rate']) : "1";

        $reserve_price_currency = !empty($details['reserve_price_currency']) ? $details['reserve_price_currency'] : "1";
        
    
        $stock_no = "stock_".date('m-d')."_".(rand(12345,67890));
      
 
          //
          $data = array(
          'stock_no'=> $stock_no,
          'purchase_no'=> $purchase_no,
          'purchase_id'=>  "0",
          'item_id'=> $item_id,          
          'no_of_units'=> $number_of_items,
          'unit_measure'=>$unit_measure,
          'unit_selling_price' =>$unitselling_price,
          'unit_selling_price_exhange_rate' =>$unitselling_price_exchange_rate,
          'unit_selling_price_currency' =>$unitselling_price_currency,
          'reserve_price' =>$reserve_price,
          'reserve_price_exchange_rate' =>$reserve_price_exchange_rate,
          'reserve_price_currency' =>$reserve_price_currency,
          'date_added' =>date('Y-m-d'),
          'isactive' =>'Y',
          'added_by' =>$userid,
          'date_added' =>date('Y-m-d',strtotime( $details['date_of_stock'])),
          'financial_year' => trim($financial_year)
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
            
              $data_added =  $this->Query_reader->add_data('add_stock',$data);
             
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






  /*
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

          //
          $data = array(
          'category'=> mysql_real_escape_string($details['itemcategory']),
          'details'=> mysql_real_escape_string($details['details']),
          'isactive'=> 'Y',
          'added_by'=> $userid,
          'shopid'=>$shopid
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
    */

    /*

    function get_categories($instructions)
    {


       $userid = $this->session->userdata('userid');
       $shopid = $this->session->userdata('shopid');


      $query = "get_shops";
      $searchstring  = " 1=1  AND status ='Y' AND shopid = '".$shopid."' ";
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
    */



 
    function get_stock($instructions)
    {

       $userid = $this->session->userdata('userid');
       $shopid = $this->session->userdata('shopid');
       $branch = $this->session->userdata('branch');
       
       $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array()[0]; 

      

       switch ($instructions['level']) {
         case 'inventory':
           # code...
              {
                 $query = "get_stocks";
                 $searchstring  = " 1=1  AND ST.financial_year like '".$instructions['current_financial_year']."'  AND IT.shopid = ".$shopid."  AND IT.branch_id = ".$branch."   AND ST.isactive LIKE 'Y' "; 

                 if(!empty($instructions['item_id']))
                 {
                   $searchstring  = "  IT.id = ".$instructions['item_id']." "; 
                 }

                  $data_array  = array(
                  'searchstring' =>  $searchstring,
                  'orderby' =>  " order by date_added DESC"       
                   );



                $response = paginate_list($this, $instructions, 'get_stock',$data_array,!empty($instructions['limit']) ? $instructions['limit']   : 10);
 
                 return $response;  


              }
           break;
        case 'overview':
              {
                 $query = "overview_stock";
                
                $searchstring  = " 1=1  AND ST.financial_year like '".$instructions['current_financial_year']."'  AND IT.shopid = ".$shopid."  AND IT.branch_id = ".$branch."   AND ST.isactive LIKE 'Y' "; 

                 if(!empty($instructions['item_id']))
                 {
                   $searchstring  = "  IT.id = ".$instructions['item_id']." "; 
                 }


                  $data_array  = array(
                  'searchstring' =>  $searchstring,
                  'orderby' =>  ""       
                   );

                $response = paginate_list($this, $instructions, 'overview_stock',$data_array,!empty($instructions['limit']) ? $instructions['limit']   : 10);
 
                 return $response;  


              }

        break;
         
         default:
           # code...
              {
                 $query = "get_stocks";
                 $searchstring  = " 1=1  AND ST.financial_year like '".$instructions['current_financial_year']."'  AND IT.shopid = ".$shopid." AND ST.isactive LIKE 'Y' "; 

                  $data_array  = array(
                  'searchstring' =>  $searchstring,
                  'orderby' =>  ""       
                   );

                $response = paginate_list($this, $instructions, 'get_stock',$data_array,!empty($instructions['limit']) ? $instructions['limit']   : 10);
 
                 return $response;  


              }

           break;
       }

     

     
   
 

  

   


 
    }  
 

 

    

}