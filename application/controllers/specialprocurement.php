<?php
#**************************************************************************************
# All bid actions directed from this controller
#**************************************************************************************

class Specialprocurement extends CI_Controller
{

    # Constructor
    function Specialprocurement()
    {
        parent::__construct();

        $this->load->model('users_m', 'users');
        $this->load->model('sys_email', 'sysemail');
        #date_default_timezone_set(SYS_TIMEZONE);

        #MOVER LOADED MODELS
        $this->load->model('Receipts_m');
        $this->load->model('Proc_m');
        $this->load->model('Evaluation_methods_m');
        $this->load->model('sys_file', 'sysfile');
        $this->load->model('Disposal_m', 'disposal');
        $this->load->model('bid_invitation_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('special_procurement_m');

        access_control($this);
    }


    # Default to view all bids
    function index()
    {
        #Go view all bids
        redirect('specialprocurement/load_special_procurement_plan_form');
    }


    function ajax_fetch_procurement_details()
    {
        $post = $_POST;
        //  print_r($post); exit();
        $bidid = $post['bidid'];
        $data['procurementdetails'] = $this->Proc_m->fetch_annual_procurement_plan($bidid);
        $data['datearea'] = 'procurementdetails';
        $this->load->view('bids/bids_addons', $data);
        // print_r($data['procurementdetails']);
    }


#Function to load special procurement form
    function load_special_procurement_plan_form()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $current_financial_year = currentyear . '-' . endyear;

        #check user access
        #1: for editing
        if (!empty($data['i'])) {
            check_user_access($this, 'edit_bid_invitation', 'redirect');
        } #2: for creating
        else {
            check_user_access($this, 'create_invitation_for_bids', 'redirect');
        }

        $app_select_str = ' procurement_plan_entries.isactive="Y" ';
        #Is Person Admin?
        if ($this->session->userdata('isadmin') == 'N') {
            $userdetails = $this->db->get_where('users', array('userid' => $this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="' . $userdetails[0]['pde'] . '"';
            $data['current_pde'] = $userdetails[0]['pde'];

        }
        #When Editing IFB Bid INvitation
        if (!empty($data['i'])) {
            $app_select_str .= ' AND bidinvitations.id ="' . decryptValue($data['i']) . '" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring' => $app_select_str, 'limittext' => '', 'orderby' => ' procurement_plan_entries.dateadded ')))->result_array();

        } #Directly Accessing New BId INvitation from Procurement Entry
        else if (!empty($data['v'])) {
            $app_select_str .= ' AND procurement_plan_entries.id ="' . decryptValue($data['v']) . '" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring' => $app_select_str, 'limittext' => '', 'orderby' => ' procurement_plan_entries.dateadded ')))->result_array();

            //exit($this->db->last_query());

        } else {
            $app_select_str .= ' AND  IF(
                                      (
                                        (SELECT SUM(quantity) FROM bidinvitations A WHERE A.procurement_id = procurement_plan_entries.id AND A.isactive ="Y"  AND procurement_plan_entries.quantifiable ="Y"  )
                                            <
                                         procurement_plan_entries.quantity

                                        ),
                                      bidinvitations.id IS NOT NULL,
                                      IF(procurement_plan_entries.quantifiable ="N", 1=1,  bidinvitations.id IS  NULL)


                                      )  ';
        }

        $data['current_financial_year'] = '';

        #When editing IFB
        if (!empty($data['i'])) {
            $data['current_financial_year'] = $current_financial_year;
            $bid_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table' => 'bidinvitations', 'limittext' => '', 'orderby' => 'id', 'searchstring' => ' id="' . $bid_id . '" AND isactive="Y"'));

            $data['current_financial_year'] = $data['procurement_plan_entries'][0]['financial_year'];

            if (!empty($data['formdata']['procurement_ref_no'])) {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('ProcurementPlanDetails', array('searchstring' => $app_select_str . ' AND procurement_plan_entries.procurement_ref_no="' . $data['formdata']['procurement_ref_no'] . '"', 'limittext' => '', 'orderby' => ' procurement_plan_entries.dateadded '));
            }
        }

        #Loading VIew from Procurement Entry under New Bid Invitation
        if (!empty($data['v'])) {
            $data['current_financial_year'] = $current_financial_year;
            $procurement_entry_id = decryptValue($data['v']);
            $data['current_financial_year'] = $data['procurement_plan_entries'][0]['financial_year'];

            $data['formdata'] = $data['formdata']['procurement_details'] = $data['procurement_plan_entries'];
            $data['formdata']['procurement_id'] = decryptValue($data['v']);
        }


        $data['currencies'] = $this->db->get_where('currencies', array('isactive' => 'Y'))->result_array();

        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = ' . $userdetails[0]['pde'];
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring' => $financial_searchstring)))->result_array();

        # PDE fetch branches
        $this->load->model('braches_m', 'branches_m');
        $searchstring = '';
        if ($this->session->userdata('isadmin') == 'N') {
            $pde = $this->session->userdata('pdeid');
            $searchstring = ' AND U.pde = ' . $pde . ' ';
        }

        $data['branches'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_branches', array('searchstring' => $searchstring, 'limittext' => '', 'orderby' => '')))->result_array();

        #fetch pdes
        $data['pdes'] = $this->db->query("SELECT * FROM pdes WHERE isactive='Y' ")->result_array();


        #fetch_ifb_procurement_entries
        $data['page_title'] = (!empty($data['i']) ? 'Edit Special Procurement Details' : 'Add Special Procurement Details');
        $data['current_menu'] = 'create_invitation_for_bids';
        $data['view_to_load'] = 'bids/special_procurement_form';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['countrylist'] = $this->Proc_m->fetchcountries();

        $this->load->view('dashboard_v', $data);

    }

    #Save special procurements
    function save_special_procurement()
    {
        //print_array($_POST);
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $app_select_str = ' procurement_plan_entries.isactive="Y" ';

        #Is Person Admin?
        if ($this->session->userdata('isadmin') == 'N') {
            $userdetails = $this->db->get_where('users', array('userid' => $this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND procurement_plans.pde_id ="' . $userdetails[0]['pde'] . '"';
            $data['current_pde'] = $userdetails[0]['pde'];
        }

        #When Editing IFB Bid INvitation
        if (!empty($data['i'])) {
            $app_select_str .= ' AND bidinvitations.id ="' . decryptValue($data['i']) . '" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring' => $app_select_str, 'limittext' => '', 'orderby' => ' procurement_plan_entries.dateadded ')))->result_array();
        } #Directly Accessing New BId INvitation from Procurement Entry
        else if (empty($data['v'])) {
            $app_select_str .= ' AND procurement_plan_entries.id ="' . $this->input->post('procurement_id') . '" ';
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('ProcurementPlanDetails', array('searchstring' => $app_select_str, 'limittext' => '', 'orderby' => ' procurement_plan_entries.dateadded ')))->result_array();
        }

        $data['current_financial_year'] = '';

        #Loading VIew from Procurement Entry under New Bid Invitation
        if (empty($data['v'])) {
            $data['current_financial_year'] = $current_financial_year;
            $procurement_entry_id = $this->input->post('procurement_id');;
            $data['current_financial_year'] = $data['procurement_plan_entries'][0]['financial_year'];

            $data['formdata'] = $data['formdata']['procurement_details'] = $data['procurement_plan_entries'];
            $data['formdata']['procurement_id'] = decryptValue($data['v']);
        }

        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = ' . $userdetails[0]['pde'];
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring' => $financial_searchstring)))->result_array();

        # PDE fetch branches
        $this->load->model('braches_m', 'branches_m');
        $searchstring = '';
        if ($this->session->userdata('isadmin') == 'N') {
            $pde = $this->session->userdata('pdeid');
            $searchstring = ' AND U.pde = ' . $pde . ' ';
        }

        $data['branches'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_branches', array('searchstring' => $searchstring, 'limittext' => '', 'orderby' => '')))->result_array();

        #fetch pdes
        $data['pdes'] = $this->db->query("SELECT * FROM pdes WHERE isactive='Y' ")->result_array();

        if ($this->input->post('save')) {
            //print_array($_POST);
            $required_fields = array('ifb_method_justification', 'initiated_by', 'dateofconfirmationbyao', 'estimated_amount', 'country_registration', 'provider_name', 'contract_award_date', 'contract_value');

           $contract_date =  $_POST['contract_award_date'] =  !empty($_POST['contract_award_date']) ? custom_date_format('Y-m-d',$_POST['contract_award_date']) : ''; 

            $confirmation_date =  $_POST['dateofconfirmationbyao'] =  !empty($_POST['dateofconfirmationbyao']) ? custom_date_format('Y-m-d',$_POST['dateofconfirmationbyao']) : ''; 


            $data['formdata'] = $_POST;
            //print_array($data['formdata']);
            //exit;
            if (empty($_POST['sequencenumber'])) {
                $validation_results['bool'] = FALSE;
                $validation_results['requiredfields'] = 'sequencenumber';
            } else {
                $_POST = clean_form_data($_POST);
                $validation_results = validate_form('', $_POST, $required_fields);
            }

            #Only proceed if the validation for required fields passes
            if ($validation_results['bool']) {
                #Check for duplicates in the system
                $duplicate_special_procurement = $this->Query_reader->get_query_by_code('search_special_procurements', array('table' => 'special_procurements', 'limittext' => '', 'orderby' => 'id', 'searchstring' => ' custom_reference_no="' . $_POST['custom_reference_no'] . '" AND isactive="Y"'));
                $specialprocurement_query_result = $this->db->query($duplicate_special_procurement);
                //exit($this->db->last_query());

             
                //Check if Dates Match Criteria 
                if (strtotime($contract_date) < strtotime($confirmation_date) ){
                    $data['msg'] = "ERROR: Date of contract award cannot be less than date of confirmation of funds by accounting officer.";
                } else {


                    $es_rate = '';
                    if ($this->input->post('estimated_amount_currency') == 1):
                        $es_rate .= '';
                    else:
                        $es_rate .= $this->input->post('rate');
                    endif;

                    $c_rate = '';
                    if ($this->input->post('bid-documents-currency') == 1):
                        $c_rate .= '';
                    else:
                        $c_rate .=str_replace(',', '', $this->input->post('crate'))  ;
                    endif;

                    $t_rate = '';
                    if ($this->input->post('payment_currency') == 1):
                        $t_rate .= '';
                    else:
                        $t_rate .=str_replace(',', '', $this->input->post('trate')) ;
                    endif;





                    if ($specialprocurement_query_result->num_rows() < 1) {
                        if ($this->input->post('custom_reference_no') != '') {
                            # commas have been stripped from the amounts. They should be numeric
                            $spdata = array
                            (
                                'procurement_id' => $this->input->post('procurement_id'),
                                'procurement_reference_no' => $this->input->post('sequencenumber'),
                                'custom_reference_no' => $this->input->post('custom_reference_no'),
                                'subject_details' => $this->input->post('subject_details'),
                                'financial_year'=>$this->input->post('financial_year'),
                                'justification' => $this->input->post('ifb_method_justification'),
                                'budget_code'=>$this->input->post('vote_no'),
                                'initiated_by' => $this->input->post('initiated_by'),
                                'confirmation_date' => $_POST['dateofconfirmationbyao'],
                                'estimated_amount' => str_replace(',', '', $this->input->post('estimated_amount')),
                                'estimated_amount_currency' => $this->input->post('estimated_amount_currency'),
                                'estimated_payment_rate' => str_replace(',', '', $es_rate) ,
                                'provider_name' => $this->input->post('provider_name'),
                                'country_registration' => $this->input->post('country_registration'),
                                'contract_award_date' => $_POST['contract_award_date'],
                                'contract_value' => str_replace(',', '', $this->input->post('contract_value')),
                                'contract_value_currency' => str_replace(',', '', $this->input->post('bid-documents-currency')),
                                'contract_payment_rate' => str_replace(',', '', $c_rate),
                                'total_payments' => str_replace(',', '', $this->input->post('total_payments')),
                                'total_payment_rate' => str_replace(',', '', $t_rate),
                                'total_payment_currency' => $this->input->post('payment_currency'),
                                'author' => $this->session->userdata('userid')
                            );

                            $result = $this->special_procurement_m->create($spdata);

                        } else {
                            $spdatas = array
                            (
                                'procurement_id' => $this->input->post('procurement_id'),
                                'procurement_reference_no' => $this->input->post('sequencenumber'),
                                'custom_reference_no' => $this->input->post('custom_reference_no'),
                                'subject_details' => $this->input->post('subject_details'),
                                'financial_year'=>$this->input->post('financial_year'),
                                'justification' => $this->input->post('ifb_method_justification'),
                                'budget_code'=>$this->input->post('vote_no'),
                                'initiated_by' => $this->input->post('initiated_by'),
                                'confirmation_date' => $this->input->post('confirmation_date'),
                                'estimated_amount' => str_replace(',', '', $this->input->post('estimated_amount')),
                                'estimated_amount_currency' => $this->input->post('estimated_amount_currency'),
                                'estimated_payment_rate' => str_replace(',', '', $es_rate) ,
                                'provider_name' => $this->input->post('provider_name'),
                                'country_registration' => $this->input->post('country_registration'),
                                'contract_award_date' => $this->input->post('contract_award_date'),
                                'contract_value' => str_replace(',', '', $this->input->post('contract_value')),
                                'contract_value_currency' => str_replace(',', '', $this->input->post('bid-documents-currency')),
                                'contract_payment_rate' => str_replace(',', '', $c_rate),
                                'total_payments' => str_replace(',', '', $this->input->post('total_payments')),
                                'total_payment_rate' => str_replace(',', '', $t_rate),
                                'total_payment_currency' => $this->input->post('payment_currency'),
                                'author' => $this->session->userdata('userid')


                            );

                            $result = $this->special_procurement_m->create($spdatas);

                        }

                    } else {
                        $data['msg'] = "ERROR: Special Procurement with reference number already exists.";
                    }
                }

                #Format and send the errors
                if (!empty($result) && $result) {
                    $this->session->set_userdata('usave', "Special Procurement details have been successfully saved.");
                    redirect("specialprocurement/list_special_procurements/m/usave");
                } elseif (empty($data['msg'])) {
                    $data['msg'] = "ERROR: Special Procurement details could not be saved or were not saved correctly.";
                }

            }
            # End validation

            if ((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) && empty($data['msg'])) {
                $data['msg'] = "WARNING: The highlighted fields are required.";
            }

            $data['requiredfields'] = $validation_results['requiredfields'];
        }

        $data['page_title'] = (!empty($data['i']) ? 'Edit bid invitation' : 'Add Special Procurement');
        $data['current_menu'] = 'create_invitation_for_bids';
        $data['view_to_load'] = 'bids/special_procurement_form';
        $data['form_title'] = $data['page_title'];
        $data['countrylist'] = $this->Proc_m->fetchcountries();

        $this->load->view('dashboard_v', $data);
    }

    function list_special_procurements()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

   
        $data['level'] = !empty($data['level']) ? $data['level'] : 'active';


        $pdeid =  $this->session->userdata['pdeid'];

       
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;       
        
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();


        if (!empty($data['financial_year'])) {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        } else {
            $data['current_financial_year'] = $current_financial_year = currentyear . '-' . endyear;

        }


        #IF USER IS NOT ADMIN
        if ($this->session->userdata('isadmin') == 'N') {
            $userdata = $this->db->get_where('users', array('userid' => $this->session->userdata('userid')))->result_array();
            $search_str = ' AND procurement_plans.pde_id="' . $userdata[0]['pde'] . '"';
            $financial_searchstring .= '  AND procurement_plans.pde_id = ' . $userdata[0]['pde'] . '';
        }

        
        $search_str .= ' AND procurement_plans.financial_year like "%' . $current_financial_year . '%" ';


        if($data['level'] == 'active')
        {

           $search_str .= '  AND special_procurements.isactive="Y" ';

        }
        else if ($data['level'] == 'terminated')
        {
           $search_str .= '  AND special_procurements.isactive="N" ';

        }



        $data = paginate_list($this, $data, 'get_special_procurements', array('orderby' => 'special_procurements.dateadded DESC', 'searchstring' => ' ' . $search_str));

 
        $data = handle_redirected_msgs($this, $data);
        $data = add_msg_if_any($this, $data);

        $data['page_title'] = 'Special Procurements';
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'bids/view_special_procurements';
        $data['search_url'] = 'specialprocurement/search_specialprocurements';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }


    //Delete special procurement
    function delete_specialprocurement()
    {
        #check user access
        //check_user_access($this, 'delete_help', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);


        if (!empty($data['i'])) {
            $result = $this->db->query($this->Query_reader->get_query_by_code('delete_specialprocurement', array('item' => 'special_procurements', 'id' => decryptValue($data['i']))));

            $this->session->set_userdata('dbid', "Special Procurement has been successfully deleted.");
        } else if (empty($data['msg'])) {
            $this->session->set_userdata('dbid', "ERROR: Special Procurement details could not be deleted or were not deleted correctly.");
        }

        redirect(base_url() . "specialprocurement/list_special_procurements/m/dbid/");
    }

    //Editing function
    function edit_specialprocurement()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'a', 't'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        if ($this->input->post('cancel')) {
            redirect("specialprocurement/list_special_procurements");
        } else if ($this->input->post('save')) {
            $es_rate = '';
            if ($this->input->post('estimated_amount_currency') == 1):
                $es_rate .= '';
            else:
                $es_rate .= $this->input->post('rate');
            endif;

            $c_rate = '';
            if ($this->input->post('bid-documents-currency') == 1):
                $c_rate .= '';
            else:
                $c_rate .= $this->input->post('crate');
            endif;

            $t_rate = '';
            if ($this->input->post('payment_currency') == 1):
                $t_rate .= '';
            else:
                $t_rate .= $this->input->post('trate');
            endif;

            $data['helpdetails'] = $_POST;
            $required_fields = array('ifb_method_justification', 'initiated_by', 'dateofconfirmationbyao', 'estimated_amount', 'country_registration', 'quantityifb', 'contractawarddate', 'contractvalue');
            $_POST = clean_form_data($_POST);
            $validation_results = validate_form('', $_POST, $required_fields);

            $data['editdata'] = $this->Query_reader->get_row_as_array('get_specialprocurement_id', array('id' => decryptValue($data['i'])));

            //validate dates
            $confirmation_date = $this->input->post('confirmation_date');
            $contract_date = $this->input->post('contract_award_date');

            if ($contract_date < $confirmation_date) {
                $data['msg'] = "ERROR: Date of contract award cannot be less than date of confirmation of funds by accounting officer.";
            } else {
                $custom_ref_no = $this->input->post('custom_reference_no');
                if ($data['editdata']['custom_reference_no'] == $custom_ref_no) {
                    $data['msg'] = '';

                    if (empty($data['msg'])) {
                        # commas have been stripped from the amounts. They should be numeric
                        //update query
                        $update = array
                        (
                            'subject_details' => $this->input->post('subject_details'),
                            'custom_reference_no' => $this->input->post('custom_reference_no'),
                            'justification' => $this->input->post('ifb_method_justification'),
                            'budget_code' => $this->input->post('budget_code'),
                            'initiated_by' => $this->input->post('initiated_by'),
                            'confirmation_date' => $this->input->post('confirmation_date'),
                            'estimated_amount' => str_replace(',', '', $this->input->post('estimated_amount')),
                            'estimated_amount_currency' => $this->input->post('estimated_amount_currency'),
                            'estimated_payment_rate' => str_replace(',', '', $es_rate) ,
                            'provider_name' => $this->input->post('provider_name'),
                            'country_registration' => $this->input->post('country_registration'),
                            'contract_award_date' => $this->input->post('contract_award_date'),
                            'contract_value' => str_replace(',', '', $this->input->post('contract_value')),
                            'contract_value_currency' => str_replace(',', '', $this->input->post('bid-documents-currency')),
                            'contract_payment_rate' => str_replace(',', '', $c_rate),
                            'total_payments' => str_replace(',', '', $this->input->post('total_payments')),
                            'total_payment_rate' => str_replace(',', '', $t_rate),
                            'total_payment_currency' => $this->input->post('payment_currency')
                        );

                        $result = $this->special_procurement_m->update(decryptValue($data['i']), $update);

                        $this->session->set_userdata('usave', "Special Procurement details have successfully been edited.");
                        redirect(base_url() . "specialprocurement/list_special_procurements/m/usave");
                    }

                    if (empty($result)) {
                        $data['msg'] = "ERROR: There was an error editing special procurement details.";
                    }

                } else {
                    $custom_ref_no = $this->input->post('custom_reference_no');
                    if ($custom_ref_no != $data['editdata']['custom_reference_no']) {
                        #Check for duplicates in the system
                        $duplicate_special_procurement = $this->Query_reader->get_query_by_code('search_special_procurements', array('table' => 'special_procurements', 'limittext' => '', 'orderby' => 'id', 'searchstring' => ' custom_reference_no="' . $custom_ref_no . '" AND isactive="Y"'));
                        $specialprocurement_query_result = $this->db->query($duplicate_special_procurement);
                        //exit($this->db->last_query());

                        if ($specialprocurement_query_result->num_rows() > 0) {
                            $data['msg'] = "ERROR: Special Procurement with reference number already exists.";
                        } else {
                            //update query
                            $update = array
                            (
                                'subject_details' => $this->input->post('subject_details'),
                                'custom_reference_no' => $this->input->post('custom_reference_no'),
                                'justification' => $this->input->post('ifb_method_justification'),
                                'budget_code' => $this->input->post('budget_code'),
                                'initiated_by' => $this->input->post('initiated_by'),
                                'confirmation_date' => $this->input->post('confirmation_date'),
                                'estimated_amount' => str_replace(',', '', $this->input->post('estimated_amount')),
                                'estimated_amount_currency' => $this->input->post('estimated_amount_currency'),
                                'estimated_payment_rate' => str_replace(',', '', $es_rate) ,
                                'provider_name' => $this->input->post('provider_name'),
                                'country_registration' => $this->input->post('country_registration'),
                                'contract_award_date' => $this->input->post('contract_award_date'),
                                'contract_value' => str_replace(',', '', $this->input->post('contract_value')),
                                'contract_value_currency' => str_replace(',', '', $this->input->post('bid-documents-currency')),
                                'contract_payment_rate' => str_replace(',', '', $c_rate),
                                'total_payments' => str_replace(',', '', $this->input->post('total_payments')),
                                'total_payment_rate' => str_replace(',', '', $t_rate),
                                'total_payment_currency' => $this->input->post('payment_currency')
                            );

                            $result = $this->special_procurement_m->update(decryptValue($data['i']), $update);

                            $this->session->set_userdata('usave', "Special Procurement details have successfully been edited.");
                            redirect(base_url() . "specialprocurement/list_special_procurements/m/usave");
                        }
                    }
                }

            }

        }

        if (!empty($data['i'])) {
            $sp_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table' => 'special_procurements', 'limittext' => '', 'orderby' => 'id', 'searchstring' => ' id="' . $sp_id . '" AND isactive="Y"'));

            #get help section details
            if (!empty($data['formdata']['id'])) {
                $id = decryptValue($data['i']);
                $data['formdata'] = $this->Query_reader->get_row_as_array('get_specialprocurement_id', array('id' => $id));
            } else {
                $data['msg'] = "ERROR: There was an error editing the special procurement.";
            }
        }

        //exit($this->db->last_query());
        $data['page_title'] = (!empty($data['i']) ? 'Edit special procurement details' : 'Special Procurement');
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'bids/edit_special_procurements';
        $data['form_title'] = $data['page_title'];
        $data['countrylist'] = $this->Proc_m->fetchcountries();

        $this->load->view('dashboard_v', $data);
    }

    #Search special procurements
    function search_specialprocurements()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);


        if (!empty($data['financial_year'])) {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        } else {
            $data['current_financial_year'] = $current_financial_year = currentyear . '-' . endyear;

        }


        #IF USER IS NOT ADMIN
        if ($this->session->userdata('isadmin') == 'N') {
            $userdata = $this->db->get_where('users', array('userid' => $this->session->userdata('userid')))->result_array();
            $search_str = ' AND procurement_plans.pde_id="' . $userdata[0]['pde'] . '"';
            $financial_searchstring .= '  AND procurement_plans.pde_id = ' . $userdata[0]['pde'] . '';
        }

        #Adding Current FInancial Year :
        $search_str .= ' AND procurement_plans.financial_year like "%' . $current_financial_year . '%" ';


        #print_r($_GET['search']['value']);


        $search_string = trim(mysql_real_escape_string($_GET['search']['value']));
        if (!empty($search_string)) {
            $search_str .= ' AND
                              (
                                   procurement_reference_no LIKE "%' . $search_string . '%"
                                      OR
                                   procurement_plan_entries.subject_of_procurement LIKE "%' . $search_string . '%"
                                         OR
                                    procurement_plan_entries.estimated_amount LIKE "%' . $search_string . '%"
                                        OR
                                    procurement_plans.financial_year LIKE "%' . $search_string . '%"

                               )';
        }


        $data = paginate_list($this, $data, 'get_special_procurements', array('orderby' => 'special_procurements.dateadded DESC', 'searchstring' => ' AND special_procurements.isactive="Y"' . $search_str));


        $data['area'] = 'specialprocurements_search';
        $this->load->view('includes/add_ons', $data);
    }


}

?>