<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Braches_m extends MY_Model
{
  public $_tablename='branches';
  public $_primary_key='id';

 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }

    #new branch
    function _save($post,$branchid= '')
    {
      #print_r($post);
      $shop = $post['shop'];
      $shortcode = $post['shortcode'];
      $branchname = $post['branchname'];
      $branch_address = $post['branch_address'];
      $author = $this->session->userdata('userid');
      if($shop > 0)
      {
      
          if(!empty($shortcode) && !empty($branch_address) && !empty($branch_address) )
          {
            $data  = array(
              'shopid' => $shop ,
              'branchname' => $branchname ,
              'shortcode' => $shortcode ,
              'address' => $branch_address ,
              'author' => $author ,
              'isactive' => 'Y'
             );
            //execute query 
            if(empty($branchid))
            {
              $result = $this->db->query($this->Query_reader->get_query_by_code('new_branch', $data)); 

             # exit($this->db->last_query());

            }
            else if(!empty($branchid))
            {
              $branchid = decryptValue($branchid);
              $data['id'] = $branchid;
              //array_push($data, "id"=>$branchid);
              #print_r($this->Query_reader->get_query_by_code('update_branch', $data));
             // exit("ready");
              $result = $this->db->query($this->Query_reader->get_query_by_code('update_branch', $data));  
              $result = true;          
            }
           # exit($this->db->last_query());
            if($result)
            {
              $this->session->set_userdata('usave', 'You have successfully saved the Branch :'.$branchname);

              $data['msg']  = "SUCCESS:You have successfully saved the Branch";
              $data['status'] = "SUCCESS";

            }
            else
            {
              $data['msg']  =  "WARNING:Something Went Wrong, Contact Site Administrator";
              $data['status'] = "FAIL";
            }
            
              
            

          }
          else
          {
            $data['msg']  = "WARNING:Fill Blanks";
            $data['status'] = "FAIL";
              
          }

      }
       else
       {
           $data['msg']  = "WARNING:Select Shop";
           $data['status'] = "FAIL";
              
       }
      return $data;
    }

     

    #delete _ archive _restore Branch
    function _archive_restore()
    {

    }

    function _fetch_branches($searchstring,$data = array())
    {
    
      $result = paginate_list($this, $data, 'fetch_branches', array('orderby'=>'' ,'searchstring'=>$searchstring),10); 
      return $result;
    }

     

    

}