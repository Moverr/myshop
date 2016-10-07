<?php
ob_start();

/**
 * .
 * User: mover
 * 
 */
class Branches extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();
        //load Models 
        $this->load->model('Usergroups_m'); 
        $this->load->model('validation_m');  
       
        access_control($this);   
        $this->load->model('braches_m','branches_m');

    }


    /*
       INITILIZATION 
    */
    function index()
    {    
       //do nothing
     }
     function add() {
       
        #  Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a', 't'));
        
            
               
        # Pick all assigned data
        $data = assign_to_data($urldata);
        
        #check user access
        if(!empty($data['i']))
        {
          check_user_access($this, 'add_shop_branch', 'redirect');
        }
        else
        {
          check_user_access($this, 'add_shop_branch', 'redirect');
        }



        $status  = 'Y';
         $searchstring = '';
         $shop = 0;
        if($this->session->userdata('isadmin') == 'N')
        {
          $shop = $this->session->userdata('shopid');
           $searchstring = ' AND id = '.$shop.' ' ;
        }

       $data['formdata']['shop'] = $shop;
       $data['active_shops'] =  $result = paginate_list($this, $data, 'fetchshops', array('STATUS'=>$status, 'orderby'=>'  ' ,'searchstring'=>$searchstring),1000000);


          if($this->input->post('cancel'))
          {   
            redirect("admin/manage_users");
          }
          else if($this->input->post('save'))
          {
 
                 if(!empty($data['i']))
                 {
                  $searchstring = ' AND BR.id = '.decryptValue($data['i']).'';
                  $data['formdata'] =  $this->branches_m-> _fetch_branches($searchstring,$data);
                  $data['formtype'] = 'edit';
                  $data['record'] = $data['i'];
                  #exit($this->db->last_query());
                 }  
            }

        
        

        $data['page_title'] = 'Add Branch';
        
        $data['current_menu'] = 'add_shop_branch';
        $data['view_to_load'] = 'shops/branch_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'admin/search_users';
            
        $this->load->view('dashboard_v', $data); 

     }

    

      #save Branches
     function save()
     {

       # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $status  = 'Y';
        $searchstring = '';
        $shop = 0;
        if($this->session->userdata('isadmin') == 'N')
        {
          $shop = $this->session->userdata('shopid');
           $searchstring = ' AND id = '.$shop.' ' ;
        }
 

             


          

          
          if(!empty($_POST))
          {

            $data['userdetails'] = $_POST;    
            $required_fields = array('shop','branchname', 'shortcode', 'branch_address', );
          $_POST = clean_form_data($_POST);
          $validation_results = validate_form('', $_POST, $required_fields);
         


            if($validation_results['bool'])
            {


                if(!empty($data['update']))
                {
                   
                   $result =   $this->branches_m->_save($_POST,$data['update']);
                }
                else
                {
                   $response =   $this->branches_m->_save($_POST); 

                    $data['msg'] =  $response['msg'];              


                          
                }
            }
            else
            {
               $data['msg'] = "WARNING: The highlighted fields are required.";
                $data['requiredfields'] = $validation_results['requiredfields']; 

            }


           
          }
          else
          {
             $data['view_to_load'] = 'shops/branch_form_v';
          }
          

   
          

       $data['formdata']['shop'] = $shop;
       $data['active_shops'] =  $result = paginate_list($this, $data, 'fetchshops', array('STATUS'=>$status, 'orderby'=>'  ' ,'searchstring'=>$searchstring),1000000);
       
        $data['page_title'] = 'Add Branch';
          $data['current_menu'] = 'add_shop_branch';
        $data['view_to_load'] = 'shops/branch_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'admin/search_users';
            
        $this->load->view('dashboard_v', $data); 

     
       
     }

     function lists(){
		# Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $searchstring = '';
        if($this->session->userdata('isadmin') == 'N')
        {
          $shop = $this->session->userdata('shopid');
           $searchstring = ' AND U.shop = '.$shop.' ' ;
        }
         
        
        $data['results'] =  $this->branches_m-> _fetch_branches($searchstring,$data);
        #$result = paginate_list($this, $data, 'fetch_branches', array('orderby'=>'' ,'searchstring'=>$searchstring),10);  
        
   

     // exit($this->db->last_query);

        $data['page_title'] = 'Manage Branches';
        $data['current_menu'] = 'manage_shop_branches';
        $data['view_to_load'] = 'shops/manage_branches';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'branches/search_branches';
            
        $this->load->view('dashboard_v', $data);

      
     }
	 
	 
     #Search Branches 
     function search_branches(){
       # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $searchstring = '';

         $_POST['searchQuery'] = '';
         if(!empty($_GET['search']['value']))
         $_POST['searchQuery'] = mysql_real_escape_string($_GET['search']['value']);

        if(!empty( $_POST['searchQuery']))
        {
           
          $searchstring .= ' AND ( BR.branchname like "%'. $_POST['searchQuery'].'%" OR BR.branchname like "%'. $_POST['searchQuery'].'%" '.
                           ' OR BR.shortcode like "%'. $_POST['searchQuery'].'%"  OR BR.address like "%'. $_POST['searchQuery'].'%"   OR P.pdename like "%'. $_POST['searchQuery'].'%"    )';
        }


        if($this->session->userdata('isadmin') == 'N')
        {
          $pde = $this->session->userdata('pdeid');
           $searchstring .= ' AND U.pde = '.$pde.' ' ;
        }
         
        



        $data =  $this->branches_m-> _fetch_branches($searchstring,$data);
        $data['area'] = 'branches';
        $this->load->view('includes/add_ons', $data);


     }

    function delete(){}

    
}