<?php
/**
 * .
 * User: mover
 * 
 */
class Pdes extends CI_Controller
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
    function ajax_fetchpdes()
    {
      $data  = array();
       $status =  $this->uri->segment(3); 
        switch ($status) {
          case 'active':
            # fetchcontent
          $data['status'] = 'active';
            $data  = $this-> pde_m -> fetch_pdes('in',$data); 
            $this->load->view('pde/adons',$data); 
           
            break;
          case 'archive':
            # fetch content
           $data['status'] = 'archive';
            $data  = $this-> pde_m -> fetch_pdes('out',$data); 
            $this->load->view('pde/adons',$data); 
            break;
          
          default:
            # do nothing ...
            break;
        }

       
    }

    #fetch details for pdes 
    function fetchdetails()
    {        
         $pdeid =  $this->uri->segment(3);        
         $data['pdes'] = $this-> pde_m -> fetchpdedetails($pdeid);        
         $this->load->view('pde/pde_detail',$data);         
    }
   
   #PERFORM DELETE FUNCTIONALITY ON PDES & RESTORE ON PDES 
   function delpdes_ajax()
   {
    $deltype =  $this->uri->segment(3);  
    $pdeid =  $this->uri->segment(4);  
    $result  = $this-> pde_m -> remove_restore_pde($deltype,$pdeid);        
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
  if($step == 1 ){
    $pdename = mysql_real_escape_string($_POST['pdename']);
     $pdeabbreviation =  mysql_real_escape_string($_POST['pdeabbreviation']);
      $pdecategory =  mysql_real_escape_string($_POST['pdecategory']);
       $pdetype =  mysql_real_escape_string($_POST['pdetype']);
        $pdecode =  mysql_real_escape_string($_POST['pdecode']);
         $pderollcat =  mysql_real_escape_string($_POST['pderollcat']);
          $pdeaddress =  mysql_real_escape_string($_POST['pdeaddress']);
           $pdetel =  mysql_real_escape_string($_POST['pdetel']);
            $pdefax =  mysql_real_escape_string($_POST['pdefax']);
             $pdeemail =  mysql_real_escape_string($_POST['pdeemail']);
              $pdewebsite =  mysql_real_escape_string($_POST['pdewebsite']);


//create roles for the system 
$roles = '';


  }
  else if($step == 2){
      
  // $datatype =  $this->uri->segment(4); 
  $pdename =  '';
     $pdeabbreviation =  '';
      $pdecategory =  '';
       $pdetype =  '';
        $pdecode =  '';
         $pderollcat =  '';
          $pdeaddress = '';
           $pdetel =  '';
            $pdefax =  '';
             $pdeemail =  '';
              $pdewebsite =  '';

      $roles = $_POST;
      $pdeid =  $this->uri->segment(5); 
   
      }

                switch ($datatype) {
                    case 'insert':
                       $result  = $this-> pde_m -> insert_pde($pdename,$pdeabbreviation,$pdecategory,$pdetype,$pdecode,$pderollcat,$pdeaddress,$pdetel,$pdefax,$pdeemail,$pdewebsite, $roles,$step);       
         
                       print_r($result);
                        break;
                   case 'update':
                        # fetch pdeid
                        $pdeid =  $this->uri->segment(5);
                        $result  = $this-> pde_m -> updatepde($pdename,$pdeabbreviation,$pdecategory,$pdetype,$pdecode,$pderollcat,$pdeaddress,$pdetel,$pdefax,$pdeemail,$pdewebsite, $pdeid,$step,$roles ); 
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
     #load pde types from db    

    //$data['pdetypes'] = $this-> Pdetypes_m -> fetchpdetypesa($status='Y'); 
    
    #fetch pde details
    $pdeid = base64_decode($this->uri->segment(3));


     $data['pdetypes'] = $this-> Pdetypes_m -> fetchpdetypesa($status='Y'); 
     
     $data['usergroups'] =  $this-> Usergroups_m -> fetchusergroups(); 
    
     $data['users'] =  $this-> users_m -> fetchusers($pdeid); 
     

    $data['pdedetails'] = $this-> pde_m -> fetchpdedetails($pdeid); 
     
    $data['assignedroles'] = $this-> pde_m -> fetchroles($pdeid); 
  
    
    #form type
    $data['formtype'] = 'edit';
    $data['page_title'] = 'Edit PDE ';
    $data['current_menu'] = 'manage_pdes';
    $data['view_to_load'] = 'pde/pde_form_v';
    $this->load->view('dashboard_v', $data);
   }
   function load_pdeform(){
     // load initial data 
     #load pde types from db    

    $data['pdetypes'] = $this-> Pdetypes_m -> insertpdetype(1,2);    

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
 
        $post = $_POST;
         $pdeid = $this->uri->segment(5);
         if(!empty($pdeid)){
        $pdeid =  $this->uri->segment(5);

         }else{
       $pdeid = $this->session->userdata('insertid');
         }  
     
    // print_r($post); exit();

       $rest  = $this-> validation_m -> validatepderole($pdeid,$post,$datatype);
       print_r($rest);
        
    exit();
   
  //  print_r($_POST) ;

   }

}