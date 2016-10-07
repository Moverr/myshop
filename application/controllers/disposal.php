<?php
ob_start();
#*********************************************************************************
# All users have to first hit this class before proceeding to whatever section
# they are going to.
#
# It contains the login and other access control functions.
#*********************************************************************************

class Disposal extends CI_Controller {

    # Constructor
    function Disposal()
    {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('users_m','user1');
        $this->load->model('sys_email','sysemail');
        $this->session->set_userdata('page_title','Login');

        #MOVER LOADED MODELS
        #   $this->load->model('Currencies_m');
        $this->load->model('Proc_m');
        $this->load->model('Evaluation_methods_m');
        $this->load->model('Remoteapi_m');
        $this->load->model('bidinvitations_m');
        $this->load->model('procurement_plan_m');

        #MOVER LOADED MODELS
        $this->load->model('Currency_m','currency');
        $this->load->model('Disposal_m','disposal');
        $this->load->model('sys_file', 'sysfile');
        $this->load->model('Proc_m');
        $this->load->model('Receipts_m');
        ##END
        date_default_timezone_set(SYS_TIMEZONE);
        $data = array();
        access_control($this);
    }


    #Default to login
    function index()
    {
        redirect('page/home');
    }


    /*
    Disposal Entry Form 
    */
    function ajax_fetch_disposal_entry()
    {

          $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));

            # Pick all assigned data
            $data = assign_to_data($urldata);

            #Get the paginated list of the news items
            $data = add_msg_if_any($this, $data);

            //Fetch Entry Details 
            $data['entry_details'] = $this -> disposal -> load_disposal_entry_details($_POST['disposal_entry_id']);

            // Load Addons entry Details 
             

                $data['datearea'] = 'disposaldetails';
                $this->load->view('disposal/add_ons', $data);                

             

            #exit("Pass");

    }


    function load_disposal_record_form(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        #print_r($data); exit();
        if(!empty($data['edit']))
        {
            $data['formtype'] ='edit';
            $diposal_id = base64_decode($data['edit']);
            $pde =  $this->session->userdata('pdeid');
            $userid =  $this->session->userdata('userid');
            $searchstring = "1 and 1 and users.userid=".$userid."  and users.pde=".$pde."";

            if(!empty($data['disposalplan']))
            {
                $searchstring .= " and disposal_record.disposal_plan=".base64_decode($data['disposalplan']);
            }

            $searchstring .= " and disposal_plans.isactive='Y' and disposal_record.id=".$diposal_id." order by   disposal_record.dateadded DESC";
            $data['disposal_records'] = $this -> disposal -> fetch_disposal_records($data,$searchstring);
        }

        $data['currency'] = $this -> currency -> get_all();
        #print_r($data['currency']); exit();
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $current_financial_year = currentyear.'-'.endyear;

        $searchstring = " 1 and 1  and  b.userid=".$userid." and a.isactive='Y'  and b.pde=".$pde."  AND a.financial_year like '%".$current_financial_year."%' ";
        /* Get the Serial Number :: */
        $data['serialnumber'] = $this ->disposal -> getserialnumber($pde);
        $data['disposal_plans'] = $this -> disposal -> fetchdisposal_plans($data,$searchstring);
        $searchstring1 = '';
        $limittext = 10;
        $data['disposal_methods'] = $this -> disposal -> fetch_disposal_methods($data,$searchstring1,$limittext);

        $data['page_title'] = 'Add Disposal Record';
        $data['current_menu'] = 'disposal_notice';
        $data['view_to_load'] = 'disposal/disposal_form_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $this->load->view('dashboard_v', $data);

    }
    
    /*
     * View Disposal Records 
     */ 
    function view_disposal_records(){
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        #print_r(decryptValue($data['disposalplan'])); exit();
        #Get the paginated list of the news items
        $data = add_msg_if_any($this, $data);

        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $isadmin = $this->session->userdata('isadmin');
        if($isadmin == 'Y')
        {
            $searchstring = " 1 = 1 ";
        }
        else
        {
            $searchstring = " 1 = 1 and users.userid=".$userid."  and users.pde=".$pde."";
        }

        if(!empty($data['disposalplan']))
        {
            $searchstring .= " and disposal_record.disposal_plan=".base64_decode($data['disposalplan']);
        }
        $searchstring .= " and disposal_plans.isactive='Y' and disposal_record.isactive='Y' order by   disposal_record.dateadded DESC";
        $data['disposal_records'] = $this -> disposal -> fetch_disposal_records($data,$searchstring);

        #exit(print_array($this->db->last_query()));

        $data['page_title'] = 'View Disposal Records';
        $data['current_menu'] = 'view_disposal_notices';
        $data['view_to_load'] = 'disposal/view_disposals_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_disposal_record';
        $this->load->view('dashboard_v', $data);
    }


    /*
     * Search Disposal Record 
     */ 
    function search_disposal_record()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata); 
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $isadmin = $this->session->userdata('isadmin');
        
         
        $searchstring = " 1=1 ";
        
        if($isadmin == 'N')
        {
            $searchstring .= " AND  users.userid=".$userid."  and users.pde=".$pde." ";
        }
     
         
        
        $_POST['searchQuery'] = !empty($_GET['search']['value']) ? mysql_real_escape_string($_GET['search']['value']) : '';     
        
        //Search Engine 
        if(!empty($_POST['searchQuery']))
        {
             $searchstring .= " AND 
             (
                disposal_plans.financial_year  LIKE '%".$_POST['searchQuery']."%'
                     OR
                disposal_record.disposal_serial_no LIKE '%".$_POST['searchQuery']."%'
                     OR            
                disposal_record.subject_of_disposal LIKE '%".$_POST['searchQuery']."%'
                     OR
                 disposal_record.asset_location  LIKE '%".$_POST['searchQuery']."%'
                     OR
                 pdes.pdename LIKE '%".$_POST['searchQuery']."%'
                     OR             
                 disposal_record.date_of_approval  LIKE '%".$_POST['searchQuery']."%'
                     OR
                 disposal_method.method LIKE '%".$_POST['searchQuery']."%'";
                 
                 // If Super Admin  :::: 
                 if($isadmin == 'Y')
                    {
                 
                    $searchstring .= "     
                           OR
                         disposal_method.method LIKE '%".$_POST['searchQuery']."%'";
                    }
                
                    $searchstring .= "    )  ";
            
            
        }   
        
        $searchstring .= " AND disposal_record.isactive='Y'  AND  disposal_plans.isactive='Y'  order by   disposal_record.dateadded DESC";
        
         
    #    print_r($searchstring);
         
        $data['disposal_records'] = $this -> disposal -> fetch_disposal_records($data,$searchstring);        
        
        #exit($this->db->last_query());
        
        $data['page_title'] = 'View Disposal Records';
        $data['current_menu'] = 'view_disposal_plan';
        $data['view_to_load'] = 'disposal/view_disposals_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_disposal_record';
        $data['area'] = 'search_disposal_record';
        $this->load->view('includes/add_ons', $data);

    }
    
    
    /*
     * Save Disposal Record 
     */ 
    function save_disposal_record(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        #print_r($data); exit();
        #Get the paginated list of the news items
        $data = add_msg_if_any($this, $data);
        #print_r($data); exit();
        if(!empty($data['update']))
        {
            $_POST['disposalrecordid'] = mysql_real_escape_string($data['update']);

            if($_POST['strategic_asset'] == 'Y')
            {
                $_POST['strategic_asset'] = 'Yes';
            }
            else
            {
                $_POST['strategic_asset'] = 'No';
            }
            
            #print_r($_POST); exit();
            log_action('update','Diposal Record Updated ', 'Diposal Record '.$_POST['disposal_serial_number'].' has been Updated Successfully ');
            $saved_disposal = $this -> disposal -> update_disposal($_POST);
            print_r($saved_disposal);

        }else
        {
            log_action('create','Diposal Record Added', 'Disposal Record '.$_POST['disposal_serial_number'].' been Created Successfully');
            $saved_disposal = $this -> disposal -> insert_disposal($_POST);
            print_r($saved_disposal);
            

        }


    }
    function archive_delete_restore_disposal_record(){}
    function loead_edit_disposal_form(){}

    /*
    load bid inviation on  disposal
    */
/*
    load bid inviation on  disposal
    */
    function load_bid_invitation_form(){


        //  check_user_access($this, 'disposal_invitation_for_bids', 'redirect');
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $current_financial_year = '';


        if(!empty($data['edit']))
        {
            check_user_access($this, 'edit_disposal_notice', 'redirect');
        }
        #2: for creating
        else
        {
            check_user_access($this, 'add_disposal_notice', 'redirect');
        }





        if(!empty($data['edit']))
        {
            $bidid = base64_decode($data['edit']);
            $data['bid_id'] =$bidid;
            //fetch_disposal_bid_invitations
            $searchstring = "       disposal_bid_invitation.id=".$bidid." ";
            $data['bid_inviation'] = $this -> disposal -> fetch_disposal_bid_invitations($data,$searchstring);
            $data['formtype'] = 'edit';
            
             

        }
         

            $data['current_financial_year'] =  '';

        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $pdeid =  $this->session->userdata['pdeid'];
        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->procurement_plan_m->get_financial_years_by_pde($pdeid);

        #Get All Currencies 
        $data['cur'] = $this -> currency -> get_all();

        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        
        //if not Editing MOde 
        if(empty($data['edit']))
        {
        $searchstring = "    AND  users.userid=".$userid."  and users.pde=".$pde."  ";
        $searchstring .= " AND disposal_plans.isactive ='Y'  AND disposal_plans.financial_year like '%".$current_financial_year."%' ";

        $searchstring .=" AND (disposal_record.id not in(SELECT disposal_record FROM disposal_bid_invitation where isactive='Y'))";
        $searchstring .=" order by   disposal_record.dateadded DESC";
        
        #fetch disposal Record       
        $data['disposal_records'] =  $this->db->query($this->Query_reader->get_query_by_code('active_disposal_records',array('searchstring'=>$searchstring,'limittext'=>'')))->result_array();

        # exit($this->db->last_query());

        
        //$this->disposal-> get_active_disposal_records($pde,$current_financial_year,$count='',$data);


        }else
        {
            $searchstring = "    AND  users.userid=".$userid."  and users.pde=".$pde."  ";
            $searchstring .= " AND disposal_plans.isactive ='Y'  AND disposal_plans.financial_year like '%".$current_financial_year."%' ";

            $searchstring .=" AND (disposal_record.id  in(SELECT disposal_record FROM disposal_bid_invitation where disposal_bid_invitation.id =".base64_decode($data['edit'])."))";
            $searchstring .=" order by   disposal_record.dateadded DESC";
            
        
            #fetch disposal Record       
        $data['disposal_records'] =  $this->db->query($this->Query_reader->get_query_by_code('active_disposal_records',array('searchstring'=>$searchstring,'limittext'=>'')))->result_array();

       

        
        }
        
        $data['page_title'] = 'Add Bid Invitation ';
        $data['current_menu'] = 'disposal_invitation_for_bids';
        $data['view_to_load'] = 'disposal/bid_invitation_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $this->load->view('dashboard_v', $data);
    }





    #Save Bid Invitation
    function save_bid_invitation(){



        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        #check user access
        #1: for editing
        if(!empty($data['edit']))
        {
            check_user_access($this, 'edit_disposal_notice', 'redirect');
            $data['formtype'] = 'edit';
        }
        #2: for creating
        else
        {
            check_user_access($this, 'add_disposal_notice', 'redirect');
        }

        $required_fields = array('disposal_id','disposal_ref_no','date_of_approval_form28','cc_approval_date','bid_document_issue_date','deadline_for_submition' ,'documents_inspection_address','documents_address_issue','bid_opening_address','bid_opening_date','bid_openning_date_time','inspect_openning_date','inspect_openning_date_time','contract_award_date','bid_evaluation_from','bid_evaluation_to','display_of_beb_notice');



        $_POST['date_of_approval_form28']  =  custom_date_format('d-m-Y',$_POST['date_of_approval_form28']);
        $_POST['cc_approval_date']         =  custom_date_format('d-m-Y',$_POST['cc_approval_date']);
        $_POST['bid_document_issue_date']  =  custom_date_format('d-m-Y',$_POST['bid_document_issue_date']);
        $_POST['deadline_for_submition']   =  custom_date_format('d-m-Y',$_POST['deadline_for_submition']);
        $_POST['bid_opening_date']         =  custom_date_format('d-m-Y',$_POST['bid_opening_date']);
        $_POST['inspect_openning_date']    =  custom_date_format('d-m-Y',$_POST['inspect_openning_date']);
        $_POST['contract_award_date']      =  custom_date_format('d-m-Y',$_POST['contract_award_date']);
        $_POST['bid_evaluation_from']      =  custom_date_format('d-m-Y',$_POST['bid_evaluation_from']);
        $_POST['bid_evaluation_to']        =  custom_date_format('d-m-Y',$_POST['bid_evaluation_to']);
        $_POST['display_of_beb_notice']    =  custom_date_format('d-m-Y',$_POST['display_of_beb_notice']);


        #validate Results based on Required or Not 
        $validation_results = validate_form('', $_POST, $required_fields);
     
        $validation_results = validate_form('', $_POST, $required_fields);
        
        
        
        if($validation_results['bool'] )
        {



                /*
                IF IN EDIT MODE. UPDATE THE RECORD 
                */

                    if(!empty($data['edit']))
                    {
                        
                        
                         
                        $_POST['update'] = base64_decode($data['edit']);
                        
                         //CALLING THE UPDATE ARGUMENT IN DISPOSAL MODEL
                         $update_bid_invitation = $this -> disposal -> insert_bid_invitation($_POST);
                        log_action('update',' Diposal Bid Invitation  Updated Successfully ', ' Diposal Bid Invitation Reference No : '.$_POST['disposal_ref_no'].' Updated  Successfully');

                    # print_r($this->db->last_query());
                        # exit("pass");
                        if(!empty($update_bid_invitation)){
                            redirect('disposal/view_bid_invitations/m/usave');

                        }
                    }
                    
                     

         

            #check if an active bid invitation already exists for selected procurement ref no
            $similar_bid_invitation = $this->db->query($this->Query_reader->get_query_by_code('search_disposal_bidinvitation', array('searchstring' => '  A.disposal_ref_no LIKE "%'.mysql_real_escape_string($_POST['disposal_reference_no']).'%" AND  B.id = "'.mysql_real_escape_string($_POST['disposal_id']).'" AND C.financial_year='.mysql_real_escape_string($_POST['ifb_financial_year']).'  AND A.isactive="Y"' . (!empty($data['update'])? ' AND A.id !="' . decryptValue($data['update']) . '"' : ''))))->result_array();


            if ($similar_bid_invitation) {
                $data['msg'] = 'ERROR: A Disposal Record already exists  ' . $financial_year;
            }
            else
            {
                 
                 //CALLING THE INSERT ARGUMENT 
                
                $saved_bid_invitation = $this -> disposal -> insert_bid_invitation($_POST);         
                log_action('create','Diposal Bid Invitation Created Successfully   ', ' Diposal Bid Invitation  Reference No : '.$_POST['disposal_ref_no'].' created Successfully ');

                 
                
                #print($this->db->last_query());
                #exit();

                //$saved_bid_invitation = $this -> disposal -> insert_bid_invitation($_POST);
                print_r($saved_bid_invitation);
                if(!empty($saved_bid_invitation))
                    redirect('disposal/view_bid_invitations/m/usave');



            }
        }

        if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool']))
                && empty($data['msg']) )
        {
            $data['msg'] = "WARNING: The highlighted fields are required.";
            $data['requiredfields'] = $validation_results['requiredfields'];

        }

        //print_r($data);

        /*Loading the Form Data */

        $data['financial_year'] = $data['current_financial_year'] =  !empty($_POST['ifb_financial_year']) ? mysql_real_escape_string($_POST['ifb_financial_year']) : '' ;

        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $pdeid =  $this->session->userdata['pdeid'];
        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();
         



         $data['formdata'] = $_POST;

         
        # print_r($_POST);
     #  exit();

        if(!empty($data['current_financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['current_financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $pdeid =  $this->session->userdata['pdeid'];
        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->procurement_plan_m->get_financial_years_by_pde($pdeid);


        #Get All Currencies 
        $data['cur'] = $this -> currency -> get_all();

        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $searchstring = "    AND  users.userid=".$userid."  and users.pde=".$pde."  ";
        $searchstring .= " AND disposal_plans.isactive ='Y'  AND disposal_plans.financial_year like '%".$current_financial_year."%' ";

        $searchstring .=" AND (disposal_record.id not in(SELECT disposal_record FROM disposal_bid_invitation where isactive='Y'))";
        $searchstring .=" order by   disposal_record.dateadded DESC";
        $data['disposal_records'] = $this->disposal-> get_active_disposal_records($pde,$data['current_financial_year'],$count='',$data);

        #print_r($this->db->last_query());
       # print_array($data['disposal_records']);
        #exit;


        $data['page_title'] = 'Add Bid Invitation ';
        $data['current_menu'] = 'disposal_invitation_for_bids';
        $data['view_to_load'] = 'disposal/bid_invitation_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $this->load->view('dashboard_v', $data);
        /* End of Data Forming */


    }

    //Delete BEB 
    function delete_beb()
    {
        //delete BEB based on receiptid
        
        //update receipts table 
        
        

        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        #check user access
        #1: for editing
        if(!empty($data['delete_invitation']))
        {
            check_user_access($this, 'delete_invitation', 'redirect');
        }

        $delid = base64_decode($data['i']);
        
        $this->db->query("UPDATE disposal_receipts SET disposal_receipts.beb = 'P' WHERE disposal_receipts.bid_id = " .$delid."");
        $this->db->query("DELETE FROM  bestevaluatedbidder_disposal WHERE bidid = ".$delid." ");
         
        log_action('delete','Deleted Diposal Best Evaluated Bidder  ', ' Diposal Best Evaluated Bidder Deleted  ');
         
        redirect('disposal/manage_bebs/m/usave');
        
        
        
        
    }

    
    #delete Records Simple Delete isactive status 
    //Archive Bid Invitation 
    function delete_invitation()
    {

        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        #check user access
        #1: for editing
        if(!empty($data['delete_invitation']))
        {
            check_user_access($this, 'delete_invitation', 'redirect');
        }

        $delid = base64_decode($data['i']);
        
        
        $this->db->query("UPDATE disposal_bid_invitation SET disposal_bid_invitation.isactive = 'N' WHERE disposal_bid_invitation.id = " .$delid."");
         log_action('delete','Deleted Diposal Bid Invitation  ', ' Diposal Bid Invitation deleted');
        #exit($this->db->last_query());
        redirect('disposal/view_bid_invitations/m/usave');
        print_r($delid);
     

    }
    

    #Delete Archive Restore Bid Invitation
    function delete_archive_restore_bid_invitation(){}

    function check(){
        $valueposted = $_POST;
        $status = $this -> disposal -> check_disposal_record($valueposted);
        print_r($status);
    }


    #View Bid Invitation Record
    function  view_bid_invitations(){
        
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        
        # Pick all assigned data        
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $isadmin = $this->session->userdata('isadmin');
        $searchstring = "1 = 1 ";


        if(!empty($data['financial_year'])){
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else{
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $searchstring .= "  AND disposal_plans.financial_year like '%".$current_financial_year."%' ";


        if($isadmin == 'N')
        {
          $searchstring .= "  AND   users.pde=".$pde."";
        }
          $searchstring .="  AND  disposal_bid_invitation.isactive  like 'Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' ";
    
         
        !empty($data['level'])  ? $level = $data['level']  :  $level = 'active';
        $pde='';
        if ($this->session->userdata('isadmin') != 'Y') {
            $pde = $this->session->userdata('pdeid');
        }

        switch ($level) {
            case 'active':
                # code...
                $data['disposal_bid_invitations'] = $this -> disposal -> fetch_disposal_bid_invitations($data,$searchstring);
                break;
            case 'archive':
                break;

            default:
                # code...
                break;
        }
    


     
        $data['page_title'] = 'View Disposal Notices ';
        $data['current_menu'] = 'view_disposal_bid_invitations';
        $data['view_to_load'] = 'disposal/view_bid_invitations_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_bid_invitation/level/'.$level.'/financial_year/'.$current_financial_year;
        $this->load->view('dashboard_v', $data);
    }
    
    
    
    #Search Bid Invitation
    function search_bid_invitation()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        #is Admin 
        $isadmin = $this->session->userdata('isadmin');
        $searchstring = "1 = 1 ";
        
        //$level
        $level = !empty($data['level']) ? $data['level']  :  'active';
        
        
        
        
        if($isadmin == 'N')
        {
          $searchstring .= "  AND  users.pde=".$pde."";
        }
        
        if(!empty($data['financial_year'])){
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else{
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $searchstring .= "  AND disposal_plans.financial_year like '%".$current_financial_year."%' ";


        
        
        
         
        
        #Search Query from Datatables 
        $_POST['searchQuery'] = !empty($_GET['search']['value']) ? mysql_real_escape_string($_GET['search']['value']) : '';     
        
        if(!empty($_POST['searchQuery']))
        {
             
            
           $searchstring .= " AND
            (
            disposal_record.asset_location  LIKE '%".$_POST['searchQuery']."%'
               OR
             disposal_record.disposal_serial_no  LIKE '%".$_POST['searchQuery']."%'
               OR
            disposal_bid_invitation.disposal_ref_no  LIKE '%".$_POST['searchQuery']."%'
               OR
              disposal_bid_invitation.bid_document_issue_date   LIKE '%".$_POST['searchQuery']."%'
               OR
             disposal_bid_invitation.deadline_for_submition   LIKE '%".$_POST['searchQuery']."%'
             
               OR
             disposal_record.subject_of_disposal  LIKE '%".$_POST['searchQuery']."%'        
             
                       
            )  ";
             
            
          }
        
         $searchstring .="  AND  disposal_bid_invitation.isactive  like 'Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' ";
    
         
         
         #Swtich View 
         switch ($level) {
            case 'active':
                # code...
                $data = $this -> disposal -> fetch_disposal_bid_invitations($data,$searchstring);
                break;
            case 'archive':
                break;

            default:
                # code...
                break;
        }
        
        
     #exit($this->db->last_query());
        
         
        
        $data['page_title'] = 'View Disposal Records';
        $data['current_menu'] = 'view_disposal_plan';
        $data['view_to_load'] = 'disposal/view_disposals_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_bid_invitation';
        $data['area'] = 'search_disposal_bid_invitations';
        $this->load->view('includes/add_ons', $data);

    }
    

    #View Bid Invitation Notice
    function view_bid_invitation_notice(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data

        $data = assign_to_data($urldata);
        #print_r($data);
        #exit();
        $data = add_msg_if_any($this, $data);
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $isadmin = $this->session->userdata('isadmin');
        $searchstring = "1 and 1 ";



        if($isadmin == 'N')
        {
            $searchstring .= " AND  users.userid=".$userid."  and users.pde=".$pde."";
        }
        $searchstring .="  AND disposal_bid_invitation.id = '".decryptValue($data['disposal_id'])."' AND disposal_bid_invitation.isactive ='Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' ";


        $data['bid_inviation'] = $this -> disposal -> fetch_disposal_bid_invitations($data,$searchstring);
        /*
        print_r($data['disposal_bid_invitaion']);
        exit();*/

        $data['page_title'] = 'DISPOSAL NOTICE ';
        $data['current_menu'] = 'view_disposal_bid_invitations';
        $data['view_to_load'] = 'disposal/view_bid_invitation_notice';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_bid_invitation';
        $this->load->view('dashboard_v', $data);



    }

//Add Bid Response Disposal 
    function add_bid_response(){
        check_user_access($this, 'bid_response', 'redirect');
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $current_financial_year = currentyear.'-'.endyear;
        
        /*
         * if in Edit Mode 
         */ 
         if(!empty($data['editbeb']))
         {
                 $editbeb = $data['editbeb'];
                 
                 $this->session->set_userdata('editbeb_disposal',$editbeb);
                 $searchstring = "  1 = 1  AND disposal_plans.isactive ='Y' AND disposal_record.isactive ='Y' and disposal_bid_invitation.isactive='Y' and disposal_bid_invitation.id  in (SELECT bid_id FROM disposal_receipts WHERE disposal_receipts.receiptid = '".$editbeb."' )    ";
         }
         else
         {
             $searchstring = "1 = 1 AND disposal_plans.isactive ='Y' AND disposal_record.isactive ='Y' and disposal_bid_invitation.isactive='Y' and disposal_bid_invitation.id not in(SELECT bid_id FROM disposal_receipts WHERE disposal_receipts.beb='Y'  ) AND disposal_plans.financial_year like '%".$current_financial_year."%' 
      AND users.pde = '".$pde."' ";
         }
        
     
        

        #$data['disposal_records'] = $this -> disposal -> fetch_disposal_records($data,$searchstring);
        $data['disposal_invitations'] = $this -> disposal -> fetch_disposal_bid_invitations($data,$searchstring);
        $data['countrylist'] = $this-> Proc_m -> fetchcountries();
        #$data['ropproviders'] =   $this-> Remoteapi_m -> fetchproviders();
        $data['page_title'] = 'Add Bid Response ';
        $data['current_menu'] = 'bid_response';
        $data['view_to_load'] = 'disposal/bid_response_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $this->load->view('dashboard_v', $data);

    }

    #function to save receipits
    function save_bid_response(){

        $segment = $this->uri->segment(3);
        $post = $_POST;

        switch ($segment) {
            case 'update':
                # code...                 
                $id = $this->uri->segment(4);
                $result = $this-> disposal -> updatebidresponse($post,$id);
              
                log_action('update','Diposal Bid Response  updated successfully  ', 'Diposal Bid Response  on '.$_POST['dispossalrefno'].' updated Successfully');


                print_r($result);
                break;
            case 'insert':                
                $result = $this-> disposal -> savebidresponse($post);

                 log_action('create','Created Diposal Bid Response  ', 'Diposal Bid Response  '.$_POST['dispossalrefno'].' has been created successfully ');

                print_r($result);

                break;

            default:
                # code...

                break;
        }




    }

    function view_bid_responses()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $searchstring = "1 and 1  and  users.userid=".$userid."  and users.pde=".$pde." order by   bid_response.dateadded DESC";
        $data['disposal_bid_invitaion'] = $this -> disposal -> fetch_disposal_reference($data,$searchstring);

        $data['page_title'] = 'View Bid Responses ';
        $data['current_menu'] = 'view_bid_responses';
        $data['view_to_load'] = 'disposal/view_bid_responses_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_bid_invitation';
        $this->load->view('dashboard_v', $data);

    }


    function new_disposal_plan()
    {
        check_user_access($this, 'create_disposal_plan', 'redirect');
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $pde =  $this->session->userdata('pdeid');
        $data = assign_to_data($urldata);
        #print_r($data); exit();
        $data['formtype'] = '';
        if(!empty($data['edit']))
        {
            $data['disposalplan'] = $this-> db -> query("select * from disposal_plans where id = ".base64_decode($data['edit']))-> result_array();
            #print_r($query); exit();
            $data['page_title'] = 'Edit  Disposal Plan';
            $data['formtype'] ='edit';
            #print_r($data['formtype']); exit();
        }
        else
        {
            $data['page_title'] = 'New  Disposal Plan';
            $data['formtype'] ='insert';
        }

        #print_r($data['formtype']);exit();
        $userid =  $this->session->userdata('userid');

        $data['current_menu'] = 'create_disposal_plan';
        $data['view_to_load'] = 'disposal/new_disposal_plan_v';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }
    function save_disposal_plan()
    {
        $segment = $this->uri->segment(3);
        $post = $_POST;


        switch ($segment) {
            case 'update':
                # code...
                $id = $this->uri->segment(4);


                $result = $this-> disposal -> update_disposal_plan($post,$id);
                log_action('update','Diposal plan updated successfully  ', 'Diposal Plan '.trim($post['start_year']).'-'.trim($post['end_year']).'    updated  successfully');

                redirect('disposal/view_disposal_plan/m/usave');
                #print_r($result);
                break;
            case 'insert':
                #SETTING UP SOME ISSUES


            


                #first save disposal plan and get the insert id
                $insertid  = $this-> disposal -> save_disposal_plan($post);

                #Upload allowed xsl
                $this->session->set_userdata('local_allowed_extensions', array('.xls', '.xlsx'));
                $extramsg = "";
                $MAX_FILE_SIZE = 1000000;
                $MAX_FILE_ROWS = 1000;

                #detailed plan as well
                if (!empty($_FILES['detailed_plan']['name']))
                {
                    $new_plan_name = 'disposalplan' . 'Upload_' . strtotime('now') . generate_random_letter();

                    $_POST['disposalplan'] = (!empty($_FILES['detailed_plan']['name'])) ? $this->sysfile->local_file_upload($_FILES['detailed_plan'], $new_plan_name, 'documents', 'filename') : '';
                }






                if (!empty($_POST['disposalplan']))
                {



                    $file_url = UPLOAD_DIRECTORY . "documents/" . $_POST['disposalplan'];

                    #print_r($file_url);
                    #   exit();

                    $file_size = filesize($file_url);

                    #Break up file if it is bigger than allowed
                    if ($file_size > $MAX_FILE_SIZE) {
                        $data['file_siblings'] = $this->sysfile->break_up_file($file_url, $MAX_FILE_ROWS);
                        $this->session->set_userdata('file_siblings', $data['file_siblings']);
                        $msg = "WARNING: The uploaded file exceeded single processing requirements and was <br>broken up into " . count($data['file_siblings']) . " files. <br><br>Please click on each file, one at a time, to update the procurement plan and <br><a href='" . base_url() . "grades/manage_grades' class='bluelink' style='font-size:17px;'>click here</a> to refresh.";
                        print_r($msg);

                    } #Move the file data
                    else
                    {



                        $result_array = read_excel_data($file_url);

                        #    print_r($result_array); exit();
                        #Remove file after upload
                        #   print_r($_POST);
                        @unlink($file_url);
                        if(count($result_array)) {

                            #1. format insert string
                            #2. sheet 1 is supplies
                            if (!empty($result_array['Disposal']) && count($result_array['Disposal']) > 1) {
                                #$project_plan = $this->procurement_plan_entry_m->create_bulk($plan_data);
                                $sheet_info = $result_array['Disposal'];
                                $x = 0;

                                #exit("movest 11");
                                foreach ($sheet_info as $key => $value) {
                                    #   print_r($value);

                                    $x ++;

                                    if($x <= 5) continue;

                                    $disposal_serial_number ='';
                                    $subject_of_disposal ='';
                                    $method_of_disposal='';
                                    $asset_location = '';
                                    $amount ='';
                                    $currency = '';
                                    $strategic_asset = '';
                                    $date_of_approval ='';
                                    $date_of_aoapproval = '';
                                    $quantity = '';


                                    // information
                                    $subject_of_disposal  = $value['B'];
                                    if(empty($subject_of_disposal) || ($subject_of_disposal == ''))
                                        continue;

                                    //$subject_of_disposal = $value['B'];

                                    #Method of Disposal
                                    $method_of_disposal =   $value['C'];
                                    if(!empty($method_of_disposal))
                                    {
                                        #print_r($method_of_disposal);
                                        $record = $this->db->query(" SELECT * FROM `disposal_method` WHERE `method` LIKE '%".mysql_real_escape_string($method_of_disposal)."%' limit 1 ")->result_array();
                                        $method_ofdisposal = (!empty($record[0]))  ? $record[0]['id']  : 1;
                                    }


                                    #Asset Location
                                    $asset_location =  $value['D'];
                                    if(!empty($asset_location))
                                        $asset_location = mysql_real_escape_string($asset_location);
                                    else
                                        $asset_location = '';


                                    #Quantity
                                    $quantity =  $value['E'];
                                    if(!empty($quantity))
                                        $quantity = mysql_real_escape_string($quantity);
                                    else
                                        $quantity = '';

                                    #Reserve Price
                                    $amount = $value['F'];
                                    if(!empty($amount))
                                        $amount = mysql_real_escape_string(removeCommas($amount));
                                    else
                                        $amount = '';

                                    #Currency
                                    $currency = $value['G'];
                                    if(!empty($currency))
                                        $currency = mysql_real_escape_string($currency);
                                    else
                                        $currency = 'UGX';




                                    #Date of Accounting Officer Approval Date

                                    $date_of_aoapproval = $value['H'];
                                    if(!empty($date_of_aoapproval))
                                        $date_of_aoapproval = custom_date_format('Y-m-d',$date_of_aoapproval);
                                    else
                                        $date_of_aoapproval = '';


                                    #Strategic Asset
                                    $strategic_asset = mysql_real_escape_string($value['I']);
                                    if((!empty($strategic_asset)) && ($strategic_asset == 'Yes') )
                                        $strategic_asset = 'Yes';
                                    else
                                        $strategic_asset = 'No';


                                    $date_of_approval =  $value['J'];
                                    if(!empty($date_of_approval))
                                        $date_of_approval = custom_date_format('Y-m-d',$date_of_approval);
                                    else
                                        $date_of_approval = '';






                                    $_POST['disposal_plan'] = $insertid;

                                    $pde =  $this->session->userdata('pdeid');
                                    $userid =  $this->session->userdata('userid');
                                    //searchstring = "1 and 1  and  b.userid=".$userid." and a.isactive='Y'  and b.pde=".$pde."  ";
                                    /* Get the Serial Number :: */
                                    $uniquenum = rand(1234567890,0987654321);
                                    $serialnumber = $this ->disposal -> getserialnumber($pde);
                                    $serialnumber = $serialnumber."".$uniquenum;


                            #generate Disposal Serial Number 
                                    $_POST['disposal_serial_number'] = $serialnumber;

                                    $_POST['subject_of_disposal'] = $subject_of_disposal;
                                    $_POST['method_of_disposal'] = $method_ofdisposal;
                                    $_POST['asset_location'] = $asset_location;
                                    $_POST['assetquantity'] = removeCommas(number_format(removeCommas($quantity)));
                                    $_POST['amount'] = removeCommas(number_format(removeCommas($amount)));
                                    $_POST['currency'] = $currency;
                                    $_POST['strategic_asset'] = $strategic_asset;
                                    $_POST['date_of_aoapproval'] = custom_date_format('Y-m-d',$date_of_aoapproval);
                                    $_POST['date_of_approval'] = custom_date_format('Y-m-d',$date_of_approval);



                            #print_r($_POST);
                          #  exit();


                                    #insert new disposal record
                                
                                    $result  = $this-> disposal -> insert_disposal($_POST);


                                }
                            }
                        }
                    }
                }



                if($insertid > 0){
                    log_action('create','Disposal Plan Created successfully ', 'Disposal Plan '.$_POST['start_year'].'-'.$_POST['end_year'].'  has been created successfully ');

                    $this->session->set_userdata('usave', 'You have successfully Saved a disposal plan  '.$_POST['start_year'].'-'.$_POST['end_year'].' ' );
                    redirect('disposal/view_disposal_plan/m/usave');
                }
                else
                {
                    $this->session->set_userdata('usave', 'Disposal Plan '.$_POST['start_year'].'-'.$_POST['end_year'].' Was Not Saved ' );
                    redirect('disposal/new_disposal_plan/m/usave');
                }


                break;

            default:
                # code...

                break;
        }

    }




    /*
     * View Disposal Plans 
     */ 
    function view_disposal_plan()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);        
        $data = add_msg_if_any($this, $data);
         
        # Pick all assigned data
        $searchstring2 = '';
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');
        $isadmin = $this->session->userdata('isadmin');

 
        $searchstring = " 1=1 ";        
         
        if($isadmin == 'N')
        {
          $searchstring .= "  AND  b.userid=".$userid."  and b.pde=".$pde."   ";
        }
          $searchstring .= "   AND     a.isactive='Y' order by a.dateadded DESC ";

     
        $data['disposal_plans'] = $this -> disposal -> fetch_disposal_plans($data,$searchstring);

        #exit($this->db->last_query());

        
        $data['page_title'] = 'View Disposal Plans ';
        $data['current_menu'] = 'view_disposal_plans';
        $data['view_to_load'] = 'disposal/view_disposal_plans_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_disposal_plans';
        $this->load->view('dashboard_v', $data);
    }
    
    
    
    #Search Bid Invitation
    function search_disposal_plans()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');  
        $isadmin = $this->session->userdata('isadmin');

         
        
        $searchstring = "    a.isactive='Y' ";      
         
        if($isadmin == 'N')
        {
          $searchstring .= "  AND  b.userid=".$userid."  and b.pde=".$pde."   ";
        }
        
        #Search Query from Datatables 
        $_POST['searchQuery'] = !empty($_GET['search']['value']) ? mysql_real_escape_string($_GET['search']['value']) : '';     
        
        if(!empty($_POST['searchQuery']))
        {
             
            $searchstring .= "  AND ( a.financial_year like '%".mysql_real_escape_string($_POST['searchQuery'])."%'  ";
            if($isadmin == 'N')
            {
                $searchstring .= " ) ";
            }
            else
            {
                $searchstring .= "  OR   C.pdename like '%".mysql_real_escape_string($_POST['searchQuery'])."%' )  ";
            }
            
        }

        $searchstring .= "    order by a.dateadded DESC ";   
        
        #print_r($searchstring);
       # exit();
        
        $data['disposal_plans'] = $this -> disposal -> fetch_disposal_plans($data,$searchstring);
        
        #exit($this->db->last_query());
        
        $data['page_title'] = 'View Disposal Records';
        $data['current_menu'] = 'view_disposal_plan';
        $data['view_to_load'] = 'disposal/view_disposals_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_disposal_plans';
        $data['area'] = 'search_disposal_plans';
        $this->load->view('includes/add_ons', $data);

    }
    
    
    




    function ajax_fetch_disposal_details(){


        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #Get the paginated list of the news items
        $data = add_msg_if_any($this, $data);
        #print_r($data);  exit();
        # Pick all assigned data
        $pde =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');

        #$quyery = $this->db->query("select * from ");
        $searchstring = " and disposal_bid_invitation.id=".mysql_real_escape_string($_POST['disposalbid_id'])."  and  users.userid=".$userid."  and users.pde=".$pde."  ";
        $data['disposal_plans_details'] = $this -> disposal -> fetch_disposal_details($data,$searchstring,1);

        #echo "reached";
        $data['datearea'] = 'disposaldetails';
        $this->load->view('disposal/disposal_addons', $data);
        #print_r($_POST);

    }


    #add Disposal Receipt :: 
    function add_receipt(){
        # check_user_access($this, 'add_receipt', 'redirect');
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        # ADD  PAGE TITLE AND THE REST  ::
        $data['formtype'] = "insert";
        $data['page_title'] = 'Add  Bid Receipt';
        $data['current_menu'] = 'receive_bids';
        $data['view_to_load'] = 'disposal/add_disposal_receipt_v';
        $data['view_data']['form_title'] = $data['page_title'];

        #$data['ropproviders'] =   $this-> Remoteapi_m -> fetchproviders();
        //fatch countires ;;
        $data['countrylist'] = $this-> Proc_m -> fetchcountries();
        $data['disposalbid_id'] = $_POST['disposalbid_id'];

        //fetch receipts
        $isadmin= $this->session->userdata['isadmin'];
        $userid = $this->session->userdata['userid'];

        $data['receiptinfo'] =   $this-> Receipts_m -> fetch_disposal_receipts( $_POST['disposalbid_id']);

        $pde    =  $this->session->userdata('pdeid');
        $userid =  $this->session->userdata('userid');

        #$quyery = $this->db->query("select * from ");
        $searchstring = " and disposal_bid_invitation.id=".mysql_real_escape_string($_POST['disposalbid_id'])."  and  users.userid=".$userid."  and users.pde=".$pde."  ";
        $data['disposal_plans_details'] = $this -> db -> query("SELECT b.* FROM disposal_bid_invitation a INNER JOIN disposal_record b ON a.disposal_record = b.id  WHERE a.id =".$_POST['disposalbid_id'])->result_array();

        $this->load->view($data['view_to_load'], $data);

    }
    
    
    //delete Receipts
    function delreceipts_ajax()
    {
        #check_user_access($this, 'del_receipts', 'redirect');
        $deltype =  $this->uri->segment(3);
        $receiptid =  $this->uri->segment(4);
            $result = $this->db->query("DELETE FROM disposal_receipts WHERE receiptid = ".$receiptid." ");
                if($result)
                echo 1;
                else
                echo "0";
         
    }
    
    

    function filterbids(){

        #access_control($this, array('admin'));
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        # fetch receipts Id ::
        $receiptid = $this->uri->segment(3);
        $bidid = $this->uri->segment(4);
        #print_r($bidid); exit();
        # load model ::
        $data['unsuccesful_bidders'] =   $this-> Receipts_m -> fetch_disposal_unsuccessful_bidders($receiptid,$bidid);
        #load data
        $this->load->view('disposal/unsuccessfulproviders_v', $data);

    }

    #function to save receipits
    function savebeb(){
        //call model
        #print_r('bidere');
        $post = $_POST;
        $beb = $post['bebname'];
        $btnstatus = $post['btnstatus'];

        #check to see if the beb exists
        if($beb <= 0)
        {
            print_r("3:Select Best Evaluated Bidder");
            exit();
        }

        #check if to view or to save ::
        switch($btnstatus)
        {
            case 'view':
                #   $result = $this-> Receipts_m -> publishbeb($post);
                print_r("3:View Mode Not Yet Implemented ");
                break;
            default:
                $result = $this-> Receipts_m -> disposalpublishbeb($post);
                print_r($result);
                break;

        }




    }

    #MANAGE BEST EVALUATED BIDDER NOTICES BACKEND
    function manage_bebs(){      

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);     
        
        $data['manage_bes'] = $this-> disposal -> fetch_beb_list(0,$data);
        
        $data['page_title'] = 'Manage  Bidders';
        $data['current_menu'] = 'view_bid_responses';
        $data['view_to_load'] = 'disposal/manage_bebs';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_bebs';
        $this->load->view('dashboard_v', $data);  

    }
    
    /*
     * Search BEBS  
     * 
     */ 
    function search_bebs(){
        
        
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);
        
         
        # print_r($_GET['search']['value']);
        
        $searchstring = '';
        $userid = $this->session->userdata['userid'];
                
                
        #$current_financial_year = currentyear.'-'.endyear;
                    
        #if not super admin 
        if ($this->session->userdata('isadmin') == 'N') 
        {   
         $pde =  $this->session->userdata('pdeid');             
         $searchstring  .= ' AND    users.pde = '.$pde.' AND disposal_plans.isactive = "Y" AND disposal_bid_invitation.isactive = "Y"   ';
        }

        $search =  mysql_real_escape_string(trim($_GET['search']['value']) );
        
        if(!empty($search))
        {           
              $searchstring  .= ' AND (
              disposal_bid_invitation.disposal_ref_no LIKE "%'.$search.'%"
              OR
              providers.providernames LIKE "%'.$search.'%"
              OR
              pdes.pdename LIKE "%'.$search.'%"
              OR
              disposal_record.subject_of_disposal LIKE "%'.$search.'%"
              OR
               bestevaluatedbidder_disposal.contractprice LIKE "%'.$search.'%"
             )  ';   
    
        }   


     
 
        $data['manage_bes'] = paginate_list($this, $data, 'view_disposal_bebs',  array('SEARCHSTRING' => '  1 and 1  '.$searchstring.'' ),10);



 
         
        
        
        $data['page_title'] = 'View Disposal Records';
        $data['current_menu'] = 'view_disposal_plan';
        $data['view_to_load'] = 'disposal/view_disposals_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_disposal_plans';
        $data['area'] = 'search_disposal_bebs';
        $this->load->view('includes/add_ons', $data);
        
    
    }

    #check disposal financial years
    function checkfinancialyears()
    {

        $result = $this-> disposal -> checkfinancialyears($_POST);
        print_r($result);


    }

    #signing of a contract  ::
    function signcontract(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $current_financial_year = currentyear.'-'.endyear;



        #print_r($data); exit();
        if(!empty($data['i']))
        {

            $disposal_id = decryptValue($data['i']);
            $data['formdata'] = paginate_list($this, $data, 'manage_disposal_contracts',  array('SEARCHSTRING' => ' AND  A.id ='.$disposal_id.' ' ),10);

            #print_r( $data['formdata']);
            #exit();
            $data['formtype'] = 'edit';

            $pde =  $this->session->userdata('pdeid');
            $userid =  $this->session->userdata('userid');
            $searchstring = " AND disposal_contract.id = ".$disposal_id." ";

            $data['disposal_records'] =$result = paginate_list($this, $data, 'archived_contracts', array('searchstring'=>$searchstring),1000);


        }
        else
        {
            $pde =  $this->session->userdata('pdeid');
            $userid =  $this->session->userdata('userid');
            $searchstring = " AND  1 = 1  AND disposal_plans.isactive='Y' AND disposal_record.isactive ='Y'   AND disposal_plans.financial_year like '%".$current_financial_year."%'   and  users.userid=".$userid." and disposal_record.isactive='Y' and disposal_plans.isactive='Y' AND disposal_record.id NOT IN (SELECT disposalrecord FROM disposal_contract WHERE disposal_contract.isactive='Y')   and users.pde=".$pde." order by   disposal_record.dateadded DESC";

            $data['disposal_records'] = $this -> disposal -> fetchdisposalrecords_contract($data,$searchstring);

        }


        $data['currency'] = $this -> currency -> get_all();
        $data['page_title'] = 'Sign Disposal  Contract  ';
        $data['current_menu'] = 'bid_activity';
        $data['view_to_load'] = 'disposal/signcontract_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $this->load->view('dashboard_v', $data);



    }

    #PERFORM DELETE FUNCTIONALITY ON PDES & RESTORE ON DISPOSAL PLANS
    function delp_ajax()
    {
        $deltype =  $this->uri->segment(3);
        $pdeid =  $this->uri->segment(4);
        #   print_r($deltype); exit();
        $result  = $this-> disposal -> remove_restore_disposalplan($deltype,$pdeid);
        echo  $result;
    }
    #DELETE DISPOSAL RECORD AJAXCLY
    function deldisposalrecord_ajax()
    {
       
       log_action('delete','Deleted Diposal Record  ', 'Diposal Record has been deleted');
        $deltype =  $this->uri->segment(3);
        $pdeid =  $this->uri->segment(4);
        $result  = $this-> disposal -> remove_restore_disposalplan_record($deltype,$pdeid);
        echo  $result;
    }

    #DELETE DISPOSAL RECORD AJAXCLY
    function deldisposalbidinvitation_ajax()
    {
       log_action('delete','Deleted Diposal Bid Invitation  ', 'Diposal Bid Invitation has been deleted');
        $deltype =   $_POST['type'];
        $bidid =  $_POST['id'];
        #print_r($_POST);
        $result  = $this-> disposal -> remove_restore_disposalinvitation_archive($deltype,$bidid);
        echo  $result;
    }




    function fetchdisposal_buyerinfo(){
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);

        //$data = add_msg_if_any($this, $data);
        #print_r($_POST); exit();
        $record = mysql_real_escape_string($_POST['disposalrecord']);
        $st = "SELECT z.* FROM disposal_record a INNER JOIN disposal_method b ON a.method_of_disposal = b.id   INNER JOIN  disposal_bid_invitation c ON a.id = c.disposal_record INNER JOIN  disposal_receipts z ON c.id = z.bid_id INNER JOIN bestevaluatedbidder_disposal d ON d.pid = z.receiptid  where b.status='Y' and a.id=".$record." ";
        #print_r($st); exit();
        $query = $this->db->query($st)->result_array();
        if(!empty($query))
        {
            # print_r($query);
            $qs= '';
            $providers = $query[0]['providerid'];
            $qs =  $this->db->query("SELECT * FROM  providers where  providerid = ".$providers." ")->result_array();


            $providerids = '';

            # print_r($qs);
            //$this->session->set_userdata('receiptid',$query[0]['receiptid']);
            echo  "8:";
            foreach ($qs as $value) {
                echo "".$value['providernames']."";


            }
            echo " ";

            exit();
        }
        else
        {
            $query = $this->db->query("SELECT a.* FROM disposal_record a  INNER JOIN  disposal_method b ON a.method_of_disposal = b.id  where b.status='N' and a.id=".$record."   ")->result_array();
            $this->session-> unset_userdata('disposalproviders');

        }
        if(!empty($query))
        {
            print_r("7:s");
            /*
            foreach ($query as $key => $record) {
                # code...
                print_r($record);
            }
            */
        }
        else
        {
            echo "4:No Records Found, Publish LBA ";
        }


    }


    function save_contract()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        #print_r($data); exit();

        #print_r($_POST);
        #exit();
        $disposalitem = mysql_real_escape_string($_POST['disposalitem']);
        $beneficiary = mysql_real_escape_string($_POST['beneficiary']);

        $provider_details = mysql_query("SELECT * FROM providers WHERE providernames like '".trim($beneficiary)."' limit 1 ");
        if(mysql_num_rows($provider_details) > 0)
        {

            $row = mysql_fetch_array($provider_details);
            $beneficiary = $row['providerid'];
        }
        else
        {

            $st_query = mysql_query("INSERT INTO  providers(providernames)   VALUES('".$beneficiary."')");
            $beneficiary = mysql_insert_id();
        }



        $contractamount = removeCommas(mysql_real_escape_string($_POST['contractamounst']));
        $rate = removeCommas(mysql_real_escape_string($_POST['rate']));
        $currency = mysql_real_escape_string($_POST['currency']);
        $datesigned = custom_date_format('Y-m-d',$_POST['datesigned']);
        
        if($currency == "UGX")
        $rate = 0;
        
        #print_r($_POST);
        #exit();

        if( ($contractamount >0 || $currency !='NAN') &&  $contractamount >0)
        {
            if(!empty($data['update']))
            {

                $contractid = $data['update'];
                //  print_r($contractid); exit();
                $st = "UPDATE disposal_contract SET disposalrecord='".$disposalitem."',beneficiary='".$beneficiary."',contractamount='".$contractamount."',currency='".$currency."',datesigned='".$datesigned."',isactive='Y',rate='".$rate."' WHERE  id = ".$contractid."";
                #print_r($st);exit();
                
                $query = $this->db->query($st);


                log_action('update','Diposal Contract  Updated ', 'Diposal Contract has been updated to beneficiary  '.$_POST['beneficiary'].'   ');

                print_r("1");
            }
            else
            {
 
                $st = "INSERT IGNORE INTO disposal_contract(disposalrecord,beneficiary,contractamount,currency,datesigned,isactive,rate) values('".$disposalitem."','".$beneficiary."','".$contractamount."','".$currency."','".$datesigned."','Y','".$rate."') ";
                #print_r($st);exit();
                $query = $this->db->query($st);

                log_action('create','Diposal Contract  created ', 'Diposal Contract has been created  to beneficiary   '.$_POST['beneficiary'].'   ');

                print_r("1");
            }
        }
        else
        {
            print_r("") ;
        }

    }

        // Manage Contracts 
    function manage_contracts(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);
        $data['manage_bes'] = $this-> disposal -> manage_disposal_contracts(0,$data);
        #print_r($data['manage_bes']);
        $data['page_title'] = 'Manage Disposal Contracts ';
        $data['current_menu'] = 'view_bid_responses';
        $data['view_to_load'] = 'disposal/manage_contracts';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = 'disposal/search_contracts';
        $this->load->view('dashboard_v', $data);

    }

    //Server Side Search Contracts 
    function search_contracts(){


        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);


          # print_r($_GET['search']['value']);

          $search_string = '';

        if(!empty($_GET['search']['value']))
          {

            $search_input = mysql_real_escape_string(trim($_GET['search']['value']));

            $search_string = '  AND (
                B.disposal_serial_no LIKE "%'.$search_input.'%"
                OR
                 C.providernames LIKE "%'.$search_input.'%"

                  OR
                 B.subject_of_disposal LIKE "%'. $search_input.'%"

               ) ';
          }    

       
          
            $userid = $this->session->userdata['userid'];
            $pde =  $this->session->userdata['pdeid'];
               
            if ($this->session->userdata('isadmin') == 'N') 
               {
                    $result = paginate_list($this, $data, 'manage_disposal_contracts',  array('SEARCHSTRING' => '    AND   B.isactive ="Y" AND E.isactive="Y"   AND  E.pde_id = '.$pde.' AND  A.isactive ="Y"'.$search_string ),10);
                }
            else
                {
                    $result = paginate_list($this, $data, 'manage_disposal_contracts',  array('SEARCHSTRING' => '   AND   B.isactive ="Y" AND E.isactive="Y"   AND  A.isactive ="Y" '.$search_string ),10);
                } 


 

        $data['manage_bes'] =  $result;
        # exit($this->db->last_query());

         $data['area'] = 'search_disposal_contracts';

        
         $this->load->view('includes/add_ons', $data);      

    }




    function deldisposalcontract_ajax()
    {
        #check_user_access($this, 'del_receipts', 'redirect');
        #exit("reached");
        $deltype =  $this->uri->segment(3);
        $receiptid =  $this->uri->segment(4);

        $result  = $this-> disposal -> remove_restore_contract($deltype,$receiptid);
        echo  $result;
    }





}

?>
