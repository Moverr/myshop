<?php
/**
 * .
 * User: mover
 * 
 */
class Pdetypes extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();
        //load Models
        $this->load->model('pde_m');
        $this->load->model('Pdetypes_m');          
        $this->load->model('Usergroups_m'); 
        $this->load->model('validation_m');  
        $this->load->model('Pdetypes_m');    
        access_control($this);   

    }


    /*
       INITILIZATION 
    */
    function index()
    {        

        $data['active'] = $this-> pde_m -> fetch_pdes('in');    
        $data['archived'] = $this-> pde_m -> fetch_pdes('out'); 
         $this->load->view('pde/manage_pda_v',$data);
         access_control($this);
    }
    function fetchpdes($status)
    {
         $data['active'] = $this-> pde_m -> fetch_pdes('out');    
        
          $this->load->view('pde/manage_pda_v');
    }

    #fetch details for pdes 
    function fetchdetails()
    {        
         $pdeid =  $this->uri->segment(3);        
         $data['pdes'] = $this-> pde_m -> fetchpdedetails($pdeid);        
         $this->load->view('pde/pde_detail',$data);         
    }
   
   #PERFORM DELETE FUNCTIONALITY ON PDES & RESTORE ON PDES 
   function delpdetype_ajax()
   {
    $deltype =  $this->uri->segment(3);  
    $pdeid =  $this->uri->segment(4);  
    $result  = $this-> Pdetypes_m -> remove_restore_pdetype($deltype,$pdeid);        
    echo  $result;    
   }
   function ajax_pde_validation(){
     $datatype =  $this->uri->segment(3); 
     if(!empty($_POST))
        {
        # searchpde   
        #$searchstring = "pde_name like '".$_POST['pdename']."' "  ; 
        $pdeid =  $this->uri->segment(4);

         
        $result  = $this-> pde_m -> validate_pdes($_POST,$pdeid,$datatype);

        print_r($result);
        }                 
             
     
   
   }
   function ajax_formsubmit()
   {

    #fetch the results
    // *pdename<>*pdeabbreviation<>*pdecategory<>*pdetype<>*pdecode<>pderollcat<>pdeaddress<>pdetel<>pdefax<>pdeemail<>pdewebsite<>pdeao<>pdeaophone<>pdecc<>pdeccphone<>pdeccemail<>pdeheadpduphone<>pdeheadpduemail
  $step = $this->uri->segment(3);  
  $datatype =  $this->uri->segment(4);    
  $pdetype  =mysql_real_escape_string($_POST['pdetype']);
  $pdetypedetails  = mysql_real_escape_string($_POST['pdetypedescription']);



   
                switch ($datatype) {
                    case 'insert':
                     //  $result  = $this-> pdetypes_m -> insertpdetype();
                       //-> insertpdetype($pdetype,$pdetypedetails);  
                     $result = $this-> Pdetypes_m -> insertpdetype ($pdetype,$pdetypedetails);    
                 
                       print_r($result);
                        break;
                   case 'update':
                        # fetch pdeid
                        $pdetypeid =  $this->uri->segment(5);

                        $result  = $this-> Pdetypes_m -> updatepdetype($pdetype,$pdetypedetails,$pdetypeid); 
                      
                        print_r($result);
                        break;
                    
                    default:
                        # code...
                        break;
                }

   
  }

   function load_edit_pde_form()
   {

    // load initial data 
     
   
    #fetch pde details
    $pdetypeid = base64_decode($this->uri->segment(3));

    #load pde types from db    
    $data['pdetypes'] = $this-> Pdetypes_m -> fetchpdetype($pdetypeid); 

      

    
    #form type
    $data['formtype'] = 'edit';
    $data['page_title'] = 'Edit PDE ';
    $data['current_menu'] = 'manage_pdes';
    $data['view_to_load'] = 'pde/pdetype_form_v';
    $this->load->view('dashboard_v', $data);

    
     //$this->load->view('pde/pde_form_v',$data);
   }
   function load_pdeform(){
     // load initial data 
     #load pde types from db    

    $data['pdetypes'] = $this-> Pdetypes_m -> fetchpdetypes($status='Y');    
    #form type
    $data['formtype'] = 'insert';   
     $this->load->view('pde/pdeform_v',$data);
   }

   function ajax_validate_usergroups()
   {
   
    $step = $this->uri->segment(3);  
    $datatype =  $this->uri->segment(4); 
    // $ao = $_POST['AO'];
    // $aoid =  $_POST['AOID'];
    // $cc =  $_POST['CC'];
    // $ccid =  $_POST['CCID'];
    // $pdu = $_POST['PDU'];
    // $pduid =  $_POST['PDUID'];

   // print_r($_POST);
    $post = $_POST;
   // exit();
    $pdeid = $this->session->userdata('insertid');
   
    $rest  = $this-> validation_m -> validatepderole($pdeid,$post);
    
 
    print_r($rest);
  //  print_r($_POST) ;

   }

    function ajax_fetchpdetypes()
    {
      $data  = array();
       $status =  $this->uri->segment(3); 
        switch ($status) {
          case 'active':
            # fetchcontent
          $data['status'] = 'active';
          $data = $this-> Pdetypes_m -> fetchpdetypes($status='Y',$data); 
           // $data  = $this-> pde_m -> fetch_pdes('in',$data); 
            $this->load->view('pde/adons_pdetypes',$data); 
           
            break;
          case 'archive':
            # fetch content
           $data['status'] = 'archive';
           $data = $this-> Pdetypes_m -> fetchpdetypes($status='N',$data); 
           // $data  = $this-> pde_m -> fetch_pdes('out',$data); 
            $this->load->view('pde/adons_pdetypes',$data); 
            break;
          
          default:
            # do nothing ...
            break;
        }

       
    }

}