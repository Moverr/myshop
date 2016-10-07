<?php
set_time_limit(0);
ignore_user_abort(true);
while(ob_get_level())ob_end_clean();
ob_implicit_flush(true);
#**************************************************************************************
# All contract functions are passed through this controller
#**************************************************************************************


class Contracts extends CI_Controller {

    # Constructor
    function Contracts()
    {
        parent::__construct();
        $this->load->model('users_m','user1');
        $this->load->model('currency_m');
        $this->load->model('contracts_m');

        access_control($this);
    }

    # Default to view all contracts
    function index()
    {
        #Go view all bids
        redirect('contracts/view_contracts');
    }


    # load the contract award form
    function contract_award_form()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        # exit();
        $lottedbid = 0;

        $pdeid =  $this->session->userdata['pdeid'];
        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();

        #current Financial Year 
        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $app_select_str = '';
        /*
        if Lots are passed 
        */
        if(!empty($data['lotid']) && !empty($data['i']))
        {
            $data['level'] ='editlot';
            $editlot = decryptValue($data['lotid']);
        }


        /*
         If PDE User 
        */
        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND PP.pde_id ="'. $userdetails[0]['pde'] .'"';
        }

        #user is editing
        if(!empty($data['i']))
        {
            $contract_id = decryptValue($data['i']);
            $searchstringcontract = ' id="'. $contract_id .'" AND isactive="Y"';
            if(!empty($data['lotid']))
            {
                $data['formdata']['lotid'] = decryptValue($data['lotid']);
                $searchstringcontract .= ' AND  lotid="'. mysql_real_escape_string(decryptValue($data['lotid'])) .'"';
            }


            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'contracts', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=> $searchstringcontract));
            $data['formdata']['vi'] = $data['i'];
            #print_r($data['formdata']); exit();
            #get procurement plan details

            if(!empty($data['formdata']['procurement_ref_id']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=>' procurement_plan_entries.id="'. $data['formdata']['procurement_ref_id'] .'" AND bidinvitations.id = '. $data['formdata']['bidinvitation_id'].'' , 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded '));

                $app_select_str .= ' AND PPE.id ="'. $data['formdata']['procurement_ref_id'] .'" ';
                $data['formdata']['prefid'] = $data['formdata']['procurement_ref_id'];

                $data['procurement_plan_entries'][0]['id'] = $data['formdata']['procurement_ref_id'];
                $data['procurement_plan_entries'][0]['procurement_ref_no'] = $data['formdata']['procurement_details']['procurement_ref_no'];
            }

            #Get the contract prices
            $data['formdata']['contract_amount'] = $this->db->query($this->Query_reader->get_query_by_code('contract_price_detail', array('searchstring'=>' AND CP.contract_id="'. $contract_id .'"')))->result_array();
        }

        else
        {
            /*
             If Lotted BEBs
            */
            if(!empty($data['lottedbid']))
            {
                $lottedbid = decryptValue($data['lottedbid']) ;

                $app_select_str .= " OR ( C.id IS NOT NULL OR C.isactive = 'U')  AND  C.lotid NOT IN(SELECT received_lots.lotid FROM received_lots ".
                    "  INNER JOIN lots ON received_lots.lotid = lots.id ".
                    "  INNER JOIN receipts ON received_lots.receiptid = receipts.receiptid".
                    "  WHERE receipts.bid_id = '".$lottedbid."' ) " ;
            }

            $app_select_str .= " AND PP.financial_year like '".$current_financial_year."' AND PP.isactive ='Y' AND PPE.isactive ='Y' AND BI.isactive = 'Y' ";



            // AND BB.isactive='Y'

            //uncontracted_procurements_lots
            if(!empty($data['lottedbid']))
            {
                $lottedbid = decryptValue($data['lottedbid']) ;
                $app_select_str .= ' AND C.id = '.$lottedbid;
                $data['procurement_plan_entries'] = $this->db->query("SELECT    DISTINCT PPE.id, BI.procurement_ref_no,  BI.id AS bidinvitation   FROM contracts C  RIGHT OUTER JOIN procurement_plan_entries PPE ON C.procurement_ref_id = PPE.id   INNER JOIN procurement_plans PP ON PPE.procurement_plan_id = PP.id   INNER JOIN bidinvitations BI ON PPE.id = BI.procurement_id   INNER JOIN receipts  R ON R.bid_id = BI.id  INNER JOIN bestevaluatedbidder BB ON BB.pid = R.receiptid   WHERE PPE.isactive = 'Y' AND BB.beb_expiry_date <= CURDATE() AND BB.isactive='Y'   AND R.beb = 'Y'
                AND PP.financial_year like '".$current_financial_year."'  AND BI.id = ".$lottedbid." AND PP.isactive ='Y' AND PPE.isactive ='Y' AND BI.isactive = 'Y' ORDER BY PPE.procurement_ref_no")->result_array();
                #exit($this->db->last_query());

            }
            else {
                # code...

                $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('uncontracted_procurements', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' PPE.procurement_ref_no ' )))->result_array();

            }

        }




        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();

        $data['page_title'] = (!empty($data['i'])? 'Edit contract details' : 'Award contract');
        $data['current_menu'] = 'award_contract';
        $data['view_to_load'] = 'contracts/contract_award_form';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }


    #Function to load the contract completion form
    function contract_completion_form()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b', 'c'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #check user access
        #1: for editing
        if(!empty($data['i']))
        {
            check_user_access($this, 'edit_bid_invitation', 'redirect');
        }
        #2: for creating
        else
        {
            check_user_access($this, 'create_invitation_for_bids', 'redirect');
        }


        if(!empty($data['c']))
        {
            $contract_id = decryptValue($data['c']);
            $data['contract_details'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'contracts', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $contract_id .'" AND isactive="Y"'));

            #get the service provider
            $data['contract_details']['provider'] = $this->get_provider_names($data['contract_details']['bidinvitation_id']);

            $contracts_payments  = $this->db->query($this->Query_reader->get_query_by_code('view_contracts_payments', array('searchstring'=>' AND CP.contract_id = '.$contract_id.' ' )))->result_array();


       

            $data['formdata'] = $data['contract_details'];

          

            #get procurement plan details
            if(!empty($data['contract_details']['bidinvitation_id']))
            {
                $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=> ' bidinvitations.id="'. $data['contract_details']['bidinvitation_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }



        $contracts_payments = array();


        if($data['procurement_details']['framework'] == "Y")
         {
             $contracts_payments = get_sum_of_total_payments($contract_id);
         }
         else
         {
             $contracts_payments  = $this->db->query($this->Query_reader->get_query_by_code('view_contracts_payments', array('searchstring'=>' AND CP.contract_id = '.$contract_id.' ' )))->result_array();

         }




        $total_actual_payments = array();
        foreach ($contracts_payments as $key => $row) {
                # code...
                $total_actual_payments[] =  $row['total_amount_paid'];
        }

        $data['formdata']['total_actual_payments'] = array_sum($total_actual_payments);




        }

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();

        $data['page_title'] = 'Contract completion details';
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'contracts/contract_completion_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }


    private function get_provider_names($procurement_entry_id)
    {
        $provider_info = $this->Query_reader->get_row_as_array('get_IFB_BEB', array('searchstring'=> ' AND BI.id="'.
            $procurement_entry_id .'" AND beb="Y"'));

        $provider_name = (!empty($provider_info['providerid'])? $provider_info['providername'] : '');

        if(!empty($provider_info) && empty($provider_info['providerid'])):
            $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $provider_info['joint_venture'] .'"')->result_array();

            if(!empty($jv_info[0]['providers'])):
                $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();

                foreach($providers as $provider):
                    $provider_name .= (!empty($provider_name)? ', ' : '') . $provider['providernames'];
                endforeach;

            endif;
        endif;

        return $provider_name;
    }


    #validate contract completion
    function validate_contract_completion($formdata)
    {
        $result = 1;
        $msg = '';
        $error_fields = array();

        #Invitation to bid date
        if(strtotime($formdata['actual_completion_date']) > strtotime(date('Y-m-d')))
        {
            $result = 0;
            $msg = 'Actual Completion Date should not be  later than Current date';
            $error_fields = array('actual_completion_date');
        }

        if(strtotime($formdata['actual_completion_date']) < strtotime($formdata['date_signed']))
        {
            $result = 0;
            $msg = 'Actual Completion Date should not be before Contract Date Signed '.$formdata['date_signed'];
            $error_fields = array('actual_completion_date','date_signed');
        }




        return array('result'=>$result, 'msg'=>$msg, 'error_fields'=>$error_fields);

    }






    #Complete contract
    function complete_contract()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'c'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $data['validationmode'] =false;
        $data['status'] = 'Y';
        if($this->input->post('save'))
        {

            $_POST['actual_completion_date'] = custom_date_format("Y-m-d",$_POST['actual_completion_date']);
            $_POST['date_signed'] = custom_date_format("Y-m-d",$_POST['date_signed']);






            #total_actual_payments_currency
            $required_fields = array('final_contract_value', 'final_contract_value_currency', 'total_actual_payments', 'actual_completion_date', 'performance_rating','actual_completion_date');

            $data['formdata'] = $_POST;
            $_POST = clean_form_data($_POST);
            $validation_results = validate_form('', $_POST, $required_fields);

            $contracts_validation_results = $this->validate_contract_completion($data['formdata']);
            #print_r($contracts_validation_results['result']); exit();
            #Only proceed if the validation for required fields passes
            # $validation_result =  $this->validate_contract_completion_form($_POST);

            #echo $result;
            #print_r($contracts_validation_results['result']);
            #exit($contracts_validation_results['result']);
            if($validation_results['bool'] && ($contracts_validation_results['result']))
            {

                //  exit("pass");
                /*Determine problem with query
                echo $this->db->_error_message();
                */

                $_POST['final_contract_value'] = removeCommas($_POST['final_contract_value']);
                $_POST['total_actual_payments'] = removeCommas($_POST['total_actual_payments']);

                #format data keys
                foreach($_POST as $key => $value)
                    $_POST[str_replace('_', '', $key)] = $value;

                $_POST = array_merge($_POST, array('id'=>decryptValue($data['c']), 'completionauthor' => $this->session->userdata('userid')));
                $result = $this->db->query($this->Query_reader->get_query_by_code('complete_contract', $_POST));

                #exit($this->db->last_query());

                #Format and send the errors
                if(!empty($result) && $result){
                    $this->session->set_userdata('usave', "The contract has been marked as complete");
                    log_action('completed',' The contract has been marked as complete  ', ' The contract has been marked as complete  ');

                    redirect("contracts/manage_contracts/m/usave");
                }
                else if(empty($data['msg']))
                {
                    $data['msg'] = "ERROR: The contract could not be completed or was not completed correctly.";
                    log_action('ERROR',' The contract could not be completed or was not completed correctly ', ' The contract could not be completed or was not completed correctly');

                }

            }
            # End validation

            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) && empty($data['msg']) )
            {
                $data['validationmode'] = true;
                $data['msg'] = "WARNING: The highlighted fields are required.";
                $data['requiredfields'] = $validation_results['requiredfields'];

            }
            elseif(!$contracts_validation_results['result'] && empty($data['msg']))
            {
                $data['validationmode'] = true;
                $data['msg'] = "WARNING: " . $contracts_validation_results['msg'];
                $data['requiredfields'] = $contracts_validation_results['error_fields'];
            }
            else{}


            $data['contractdetails'] = $_POST;
        }

        if(!empty($data['c']))
        {
            $contract_id = decryptValue($data['c']);
            $data['contract_details'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'contracts', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $contract_id .'" AND isactive="Y"'));


            #get procurement plan details
            if(!empty($data['contract_details']['bidinvitation_id']))
            {
                $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=> ' bidinvitations.id="'. $data['contract_details']['procurement_ref_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));

                #get the service provider
                $data['contract_details']['provider'] = $this->get_provider_names($data['contract_details']['bidinvitation_id']);

                $contracts_payments  = $this->db->query($this->Query_reader->get_query_by_code('view_contracts_payments', array('searchstring'=>' AND CP.contract_id = '.$contract_id.' ' )))->result_array();

                $total_actual_payments = array();
                foreach ($contracts_payments as $key => $row) {
                    # code...
                    $total_actual_payments[] =  $row['total_amount_paid'];
                }


                #$data['formdata'] = $data['contract_details'];

                $data['formdata']['total_actual_payments'] = array_sum($total_actual_payments);


                #get procurement plan details
                if(!empty($data['contract_details']['bidinvitation_id']))
                {
                    $data['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=> ' bidinvitations.id="'. $data['contract_details']['bidinvitation_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
                }

            }
        }



        $data['currencies'] = $this->db->get_where('currencies', array('isactive' => 'Y'))->result_array();

        $data['page_title'] = 'Contract completion details';
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'contracts/contract_completion_form';
        $data['view_data']['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }




    #validate contract Award
    function validate_contract_award($formdata)
    {
        $result = 1;
        $msg = '';
        $error_fields = array();
        $date = Date('Y-m-d');

        #Date Signed should not be less than today Date
        /*
           You Can not Award in the Future :
        */
        if(strtotime($formdata['date_signed']) > strtotime($date))
        {
            $msg = 'Date Signed should not be  greater than today '.$date;
            $error_fields = array('date_signed','date_signed');
            $result = 0;

        }


        #Date Signed Can Not Be Less than Expiry Date at BEB. Must BE After  ::
        if(!empty($formdata['expiry_date']))
        {
            if(strtotime($formdata['date_signed']) <  strtotime($formdata['expiry_date']))
            {
                $msg = 'Date Signed should not be  before Expiry Date at BEB  '.$formdata['expiry_date'];
                $error_fields = array('date_signed','date_signed');
                $result = 0;
            }
        }


        #IDate Signed not Later than  COmmencement Date
        if(strtotime($formdata['date_signed']) > strtotime($formdata['commencement_date']))
        {
            $msg = 'Date Signed should not be  later than Date of Contract Commencement ';
            $error_fields = array('date_signed','commencement_date');
            $result = 0;
        }

        return array('result'=>$result, 'msg'=>$msg, 'error_fields'=>$error_fields);

    }



    #Save awarded contract
    #Save awarded contract
    function award_contract()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        if(($this->input->post('save')) || ($this->input->post('saveadd')) || ($this->input->post('savefinish')) )
        {



            $_POST['date_signed'] = custom_date_format('Y-m-d',$_POST['date_signed']);
            $_POST['commencement_date'] = custom_date_format('Y-m-d',$_POST['commencement_date']);
            $_POST['completion_date'] = custom_date_format('Y-m-d',$_POST['completion_date']);

            #Display and Expiry Date at BEB Level 3
            if(!empty($_POST['display_date']))
                $_POST['display_date'] = custom_date_format('Y-m-d',$_POST['display_date']);

            if(!empty($_POST['expiry_date']))
                $_POST['expiry_date'] = custom_date_format('Y-m-d',$_POST['expiry_date']);


            #print_r($_POST);
            # exit();


            $required_fields = array('date_signed', 'commencement_date', 'completion_date','contract_manager');

            $data['formdata'] = $_POST;
            if(empty($_POST['contract_amount']))
            {
                $_POST['amount'] = '';
                array_push($required_fields, 'amount');
            }


            $_POST = clean_form_data($_POST);
            $validation_results = validate_form('', $_POST, $required_fields);
            $validate_contract_award = $this->validate_contract_award($_POST);



            #Only proceed if the validation for required fields passes
            if(($validation_results['bool']) && ($validate_contract_award['result']) )
            {
                $searchstring = ' procurement_ref_id="' . $_POST['prefid'] . '" AND isactive="Y"';

                if(!empty($data['lotid']))
                {
                    $_POST['lotid'] = decryptValue($data['lotid']);
                    $lotid = $_POST['lotid'];
                }

                //&& (empty($data['lotid']))

                if (!empty($data['i']) ) {

                    $_POST['date_signed'] = custom_date_format('Y-m-d',$_POST['date_signed']);
                    $_POST['commencement_date'] = custom_date_format('Y-m-d',$_POST['commencement_date']);
                    $_POST['completion_date'] = custom_date_format('Y-m-d',$_POST['completion_date']);

                    $contract_id = decryptValue($data['i']);
                    $searchstring = '  id != ' . mysql_real_escape_string(decryptValue($data['i'])) . ' AND procurement_ref_id="' . $_POST['prefid'] . '" AND isactive="Y" ';
                }

                if(!empty($_POST['lotid']) )
                {
                    $searchstring .= ' AND  (contracts.bidinvitation_id = '.mysql_real_escape_string($_POST['bidinvitationid']).' AND contracts.lotid = '.mysql_real_escape_string($_POST['lotid']).' )';

                }
                else {
                    # code...
                    $searchstring .= ' AND  contracts.bidinvitation_id = '.mysql_real_escape_string($_POST['bidinvitationid']);

                }

                if(!empty($_POST['receiptid']))
                    $searchstring .= ' AND  contracts.receiptid = '.mysql_real_escape_string($_POST['receiptid']);



                #Check if a contract with a similar name already exists
                $contract_name_query = $this->Query_reader->get_query_by_code('search_table', array('table' => 'contracts', 'limittext' => '', 'orderby' => 'id', 'searchstring' => $searchstring));
                $contract_name_query_result = $this->db->query($contract_name_query);
                # exit($this->db->last_query());



                $contract_data=array(
                    'date_signed'=>  $_POST['date_signed'],
                    'days_duration'=>$this->input->post('duration'),

                    'date_signed'=>$_POST['date_signed'] ,
                    'commencement_date'=>$_POST['commencement_date']
                );

                if(!empty($_POST['date_signed']))
                    $date_signed = custom_date_format('Y-m-d',$_POST['date_signed']);
                else
                    $date_signed = '';


                if(!empty($_POST['days_duration']))
                    $days_duration = $_POST['days_duration'];
                else
                    $days_duration= '';


                if(!empty($_POST['months_duration']))
                    $months_duration = $_POST['months_duration'];
                else
                    $months_duration= '';


                if(!empty($_POST['years_duration']))
                    $years_duration = $_POST['years_duration'];
                else
                    $years_duration= '';



                if(!empty($_POST['commencement_date']))
                    $commencement_date = custom_date_format('Y-m-d',$_POST['commencement_date']);
                else
                    $commencement_date = '';



                if(!empty($_POST['completion_date']))
                    $completion_date = custom_date_format('Y-m-d',$_POST['completion_date']);
                else
                    $completion_date = '';

                $duration = mysql_real_escape_string($_POST['duration']);

                $lotid = 0;
                if(!empty($_POST['lotid']))
                    $lotid = mysql_real_escape_string($_POST['lotid']);



                //Receipt ID
                $receiptid = 0;
                if(!empty($_POST['receiptid']))
                    $receiptid = mysql_real_escape_string($_POST['receiptid']);





                if($contract_name_query_result->num_rows() < 1 || (!empty($data['i']))) {

                    if (!empty($data['i']))
                    {

                        $contractid = decryptValue($data['i']);
                        $_POST = array_merge($_POST, array('author' => $this->session->userdata('userid'), 'id' => $contract_id));


                        if(!empty($_POST['lotid']))
                        {
                            //begining end
                            $lotcount = $this->db->query("SELECT COUNT(*) FROM contracts where lotid = '".mysql_real_escape_string($_POST['lotid'])."'")->result_array();
                            #if lot does not exist. insert into db else update

                            if(!empty($lotcount))
                            {
                                //end
                                /* $result = $this->db->query("UPDATE contracts set receiptid = '".$receiptid."'   date_signed='".$date_signed."', commencement_date = '".$commencement_date."' , completion_date ='".$completion_date."',dateadded = 'NOW()',days_duration ='".$duration."',lotid = '".$lotid."',contract_manager='".$_POST['contract_manager']."' WHERE   lotid='".$lotid."' ");  */

                                $data  = $_POST;
                                $data['searchstring'] =' lotid = '.$lotid.'';
                                $result = $this->db->query($this->Query_reader->get_query_by_code('update_contracts', $data));



                            }
                            else
                            {
                                $_POST = array_merge($_POST, array('author' => $this->session->userdata('userid')));
                                $_POST['bidinvitation'] = mysql_real_escape_string($_POST['bidinvitationid']);
                                $result = $this->db->query($this->Query_reader->get_query_by_code('award_contract', $_POST));
                            }
                        }

                        else
                        {

                            /*  $result = $this->db->query("UPDATE contracts set  receiptid = '".$receiptid."'  date_signed='".$date_signed."', commencement_date = '".$commencement_date."' , completion_date ='".$completion_date."',dateadded = 'NOW()',days_duration ='".$days_duration."', months_duration='".$months_duration."',years_duration='".$years_duration."',contract_manager='".$_POST['contract_manager']."' WHERE  id =".$contractid." ");
                              */

                            $data  = $_POST;
                            $data['searchstring'] =' id = '.$contractid.'';

                            $result = $this->db->query($this->Query_reader->get_query_by_code('update_contracts', $data));


                        }
                        # exit($this->db->last_query());
                        # print_r($_POST); exit();
                        #contract amount
                        $query = $this->db->query("DELETE FROM contract_prices WHERE contract_id =" . $contract_id . " ");

                        foreach ($_POST['contract_amount'] as $contract_amount)
                        {
                            $amount_values = explode('__', $contract_amount);
                            $this->db->query("INSERT INTO contract_prices(contract_id,amount,xrate,currency_id,author)     VALUES('".$contract_id."',  '".removeCommas($amount_values[0])."'  ,'".removeCommas($amount_values[2])."'  ,'".removeCommas($amount_values[1])."'  ,'". $this->session->userdata('userid')."'     )") ;
                        }

                        #start
                        $this->session->set_userdata('usave', "The contract data has been successfully saved.");



                        if(isset($_POST['saveadd']))
                            redirect("contracts/contract_award_form/m/usave/i/".$data['i']);
                        else
                            redirect("contracts/manage_contracts/m/usave");

                    }
                    else
                    {
                        $_POST = array_merge($_POST, array('author' => $this->session->userdata('userid')));

                        $_POST['bidinvitation'] = mysql_real_escape_string($_POST['bidinvitationid']);

                        $result = $this->db->query($this->Query_reader->get_query_by_code('award_contract', $_POST));


                        #exit($this->db->last_query());
                        if($result)
                        {
                            $contract_id = $this->db->insert_id();
                            #Add the contract prices
                            foreach($_POST['contract_amount'] as $contract_amount)
                            {
                                $amount_values = explode('__', $contract_amount);
                                $this->db->query("INSERT INTO contract_prices(contract_id,amount,xrate,currency_id,author)     VALUES('".$contract_id."',  '".removeCommas($amount_values[0])."'  ,'".removeCommas($amount_values[2])."'  ,'".removeCommas($amount_values[1])."'  ,'". $this->session->userdata('userid')."'     )") ;

                            }
                        }



                    }

                }
                else
                {
                    $data['msg'] = "ERROR: A contract has already been awarded for the selected procurement ref number.";
                }

                if(!empty($result) && $result)
                {
                    $this->session->set_userdata('usave', "The contract data has been successfully saved.");
                    log_action('create','The contract data has been successfully saved', ' The contract data has been successfully saved');


                    if(isset($_POST['saveadd']))
                        redirect("contracts/contract_award_form/m/usave/lottedbid/".encryptValue($_POST['bidinvitationid']));
                    else
                        redirect("contracts/manage_contracts/m/usave");
                }
                else if(empty($data['msg']))
                {
                    $data['msg'] = "ERROR: The contract could not be saved or was not saved correctly.";
                    log_action('ERROR',' The contract could not be saved or was not saved correctly', ' The contract could not be saved or was not saved correctly');

                }

            }
            # End validation


            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) && empty($data['msg']) )
            {
                $data['validationmode'] = true;
                $data['msg'] = "WARNING: The highlighted fields are required.";
                $data['requiredfields'] = $validation_results['requiredfields'];

            }

            elseif(!$validate_contract_award['result'] && empty($data['msg']))
            {
                $data['validationmode'] = true;
                $data['msg'] = "WARNING: " . $validate_contract_award['msg'];
                $data['requiredfields'] = $validate_contract_award['error_fields'];
            }
            else
            {}






            if(!empty($_POST['prefid']))
            {
                $dataarray = explode("__", $_POST['prefid']);


                if(!empty($dataarray[1]))
                {
                    $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=>' procurement_plan_entries.id="'. $dataarray[0] .'" AND bidinvitations.id = '. $dataarray[1].'' , 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded '));

                    # $app_select_str .= ' AND PPE.id ="'. $dataarray[0] .'" ';
                    $data['formdata']['prefid'] = $dataarray[0];
                    $data['formdata']['bidinvitation_id'] = $dataarray[1];
                    $data['procurement_plan_entries'][0]['id'] = $dataarray[0];
                    $data['procurement_plan_entries'][0]['procurement_ref_no'] = $data['formdata']['procurement_details']['procurement_ref_no'];
                }



            }

            if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) && empty($data['msg']) )
            {
                $data['msg'] = "WARNING: The highlighted fields are required.";
            }

            $data['requiredfields'] = $validation_results['requiredfields'];
            $data['contractdetails'] = $_POST;





        }

        $app_select_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND PP.pde_id ="'. $userdetails[0]['pde'] .'"';
        }



        $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('uncontracted_procurements', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' PPE.procurement_ref_no ' )))->result_array();

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();
        if (!empty($data['i'])) {
            $data['vi'] = $data['i'];
        }


        $pdeid =  $this->session->userdata['pdeid'];
        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();

        #current Financial Year
        if(!empty($_POST['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $_POST['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }



        $data['page_title'] = (!empty($data['i'])? 'Edit contract details' : 'Award contract');
        $data['current_menu'] = 'award_contract';
        $data['view_to_load'] = 'contracts/contract_award_form';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }

    #manage Contracts
    function manage_contracts() {
        # Get the passed details into the url data array if any
        set_time_limit(0);
        ignore_user_abort(true);
        while(ob_get_level())ob_end_clean();
        ob_implicit_flush(true);

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        $search_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $search_str = ' AND PP.pde_id="'. $userdata[0]['pde'] .'"';
        }

        #procurement_plans.financial_year like "%'.$current_financial_year.'%"



        $pdeid =  $this->session->userdata['pdeid'];

        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();


        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];
        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;
        }

        $search_str .= ' AND PP.financial_year like "%'.$current_financial_year.'%"';



        //count the contracts
        $count_contracts = paginate_list($this, $data, 'count_contracts', array('orderby'=>'C.dateadded DESC', 'searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $search_str));
        $data['count_contracts']  = $count_contracts['page_list'][0]['num_contracts'];

        #Get the paginated list of users
        $data['contracts_page'] = paginate_list($this, $data, 'get_published_contracts', array('orderby'=>'C.dateadded DESC', 'searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $search_str),100);


        # exit($this->db->last_query());
        $data = handle_redirected_msgs($this, $data);
        $data = add_msg_if_any($this, $data);

        $data['page_title'] = 'Manage contracts';
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'contracts/manage_contracts';
        $data['search_url'] = 'contracts/search_contracts/level/active/financial_year/'.trim($current_financial_year).'/';

        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }



    #Search contracts
    function search_contracts()
    {
        #check_user_access($this, 'view_bid_invitations', 'redirect');

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);


        # print_r($data);
        # exit();
        $search_string = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $search_str = ' AND PP.pde_id="'. $userdata[0]['pde'] .'"';
        }



        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }

        $search_str .= ' AND PP.financial_year like "%'.$current_financial_year.'%"';






        if(!empty($_GET['search']['value']))
        {
            //  $_POST = clean_form_data($_POST);
            $searchQuery['searchQuery'] = trim(mysql_real_escape_string($_GET['search']['value']));

            $search_str .= ' AND (

                            BI.procurement_ref_no like "%'. $searchQuery['searchQuery'] .'%" '.
                //    '%" OR PPE.subject_of_procurement like "%' . $searchQuery['searchQuery'] . '%" '.
                //    'OR pdes.pdename like "%' . $searchQuery['searchQuery'] . '%" '.
                //    'OR C.contract_manager like "%' . $searchQuery['searchQuery'] . '%" '.
                // 'OR  PMS.title  like "%' . $searchQuery['searchQuery'] . '%" '.

                //    'OR C.date_signed =  "' . custom_date_format('Y-m-d',$searchQuery['searchQuery']) . '" '.
                //    'OR C.dateadded =  "' . contract_data('Y-m-d',$searchQuery['searchQuery']) . '" '.
                //    'OR C.commencement_date =  "' . contract_data('Y-m-d',$searchQuery['searchQuery']) . '" '.
                //    'OR C.contract_amount = "'.removeCommas($searchQuery['searchQuery']).'"'.
                //    'OR users.firstname like "%'. $searchQuery['searchQuery'] .'%" OR '.
                'users.lastname like "%' . $searchQuery['searchQuery'] . '%") ';



        }




        #Get the paginated list of users
        $data  = paginate_list($this, $data, 'get_published_contracts', array('orderby'=>'C.dateadded DESC', 'searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $search_str),100);




        #   $data = paginate_list($this, $data, 'get_published_contracts', array('orderby'=>'C.dateadded DESC', 'searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $search_string),100);




        $data['area'] = 'signed_contracts';

        $this->load->view('includes/add_ons', $data);

    }



    #Function to delete a contract
    function delete_contract()
    {
        #check user access
        check_user_access($this, 'delete_contract', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        if(!empty($data['i'])){
            $result = $this->db->query($this->Query_reader->get_query_by_code('deactivate_item', array('item'=>'contracts', 'id'=>decryptValue($data['i'])) ));
        }

        if(!empty($result) && $result){
            #deactivate the contract prices as well
            $this->db->update('contract_prices', array('isactive'=>'Y'), array('contract_id'=>decryptValue($data['i'])));

            $this->session->set_userdata('dbid', "The contract details have been successfully deleted.");

            log_action('delete','The contract details have been successfully deleted', ' The contract details have been successfully deleted');


        }
        else if(empty($data['msg']))
        {
            $this->session->set_userdata('dbid', "ERROR: The contract details could not be deleted or were not deleted correctly.");
        }

        redirect(base_url()."contracts/manage_contracts/m/dbid/");
    }

    #Function to terminate contract
    function terminate_contract()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $app_select_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND PP.pde_id ="'. $userdetails[0]['pde'] .'"';
        }


        #view if user is terminating a contract
        if(!empty($data['i']))
        {
            $contract_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'contracts', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $contract_id .'" AND isactive="Y"'));
            $data['formdata']['vi'] = $data['i'];
            # print_r($data['formdata']); exit();
            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_id']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=>' procurement_plan_entries.id="'. $data['formdata']['procurement_ref_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded '));

                $app_select_str .= ' AND PPE.id ="'. $data['formdata']['procurement_ref_id'] .'" ';
                $data['formdata']['prefid'] = $data['formdata']['procurement_ref_id'];

                $data['procurement_plan_entries'][0]['id'] = $data['formdata']['procurement_ref_id'];
                $data['procurement_plan_entries'][0]['procurement_ref_no'] = $data['formdata']['procurement_details']['procurement_ref_no'];
            }

            #Get the contract prices
            $data['formdata']['contract_amount'] = $this->db->query($this->Query_reader->get_query_by_code('contract_price_detail', array('searchstring'=>' AND CP.contract_id="'. $contract_id .'"')))->result_array();
        }
        else
        {
            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('uncontracted_procurements', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' PPE.procurement_ref_no ' )))->result_array();
        }

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();

        $data['page_title'] = (!empty($data['i'])? 'Contract Termination Details' : 'Terminate contract');
        $data['current_menu'] = 'award_contract';
        $data['view_to_load'] = 'contracts/contract_termination_form';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }

    #Function to terminate a contract
    function contract_termination()
    {
        #check user access
        check_user_access($this, 'delete_contract', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $termination_reason = mysql_real_escape_string($_POST['termination_reason']);
        $date_contract_terminated = '';
        if(!empty($_POST['date_contract_terminated']))
        {
            $date_contract_terminated = date('Y-m-d',strtotime($_POST['date_contract_terminated']));
        }

        # print_r($data);
        # print_r($_POST);
        # exit();
        $author = $this->session->userdata('userid');
        $dataarray = array(
            'terminated' =>'Y',
            'reason_for_termination' =>$termination_reason,
            'author' =>$author,
            'dateterminated'=>$date_contract_terminated,
            'id'=>decryptValue($data['i'])
        );


//print_r($this->Query_reader->get_query_by_code('terminate_contract',$dataarray));
        if(!empty($data['i'])){
            $result = $this->db->query($this->Query_reader->get_query_by_code('terminate_contract',$dataarray));

            #deactivate the contract prices as well
            $this->db->update('contract_prices', array('isactive'=>'N'), array('contract_id'=>decryptValue($data['i'])));

            $this->session->set_userdata('dbid', "The contract details has been successfully terminated.");
            $msg = "SUCCESS: The contract details has been successfully terminated.";

            log_action('create',' The contract details has been successfully terminated', ' The contract details has been successfully terminated ');


            $status = "1";
        }
        else if(empty($data['msg']))
        {
            $this->session->set_userdata('dbid', "ERROR: The contract details could not be terminated.");
            $msg = "ERROR: The contract details could not be terminated.";
            $status = "0";
        }
        $dataarray['msg'] = $msg;
        $dataarray['status'] = $status;
        echo ($status);
        exit();


        // redirect(base_url()."contracts/manage_contracts/m/dbid/");
    }

    #Show terminated contracts
    function terminated()
    {
        # Get the passed details into the url data array if any
        set_time_limit(0);
        ignore_user_abort(true);
        while(ob_get_level())ob_end_clean();
        ob_implicit_flush(true);
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

        $search_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdata = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $search_str = ' AND PP.pde_id="'. $userdata[0]['pde'] .'" AND C.terminated ="Y" ';
        }

        $pdeid =  $this->session->userdata['pdeid'];

        #fetch IFB Financial Years
        $financial_searchstring = ' AND procurement_plans.isactive ="Y"  AND procurement_plans.pde_id = '.$pdeid;
        $data['financial_years'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_financial_years', array('searchstring'=>$financial_searchstring)))->result_array();


        if(!empty($data['financial_year']))
        {
            $data['current_financial_year'] = $current_financial_year = $data['financial_year'];

        }
        else
        {
            $data['current_financial_year'] = $current_financial_year = currentyear.'-'.endyear;

        }
        $search_str .= ' AND PP.financial_year like "%'.$current_financial_year.'%"';


        #Get the paginated list of contracts
        $data = paginate_list($this, $data, 'get_terminated_contracts', array('orderby'=>'C.date_signed DESC', 'searchstring'=>' AND C.isactive="N"' . $search_str));

        //exit($this->db->last_query());

        $data = handle_redirected_msgs($this, $data);
        $data = add_msg_if_any($this, $data);

        $data['page_title'] = 'Manage contracts';
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'contracts/terminated_contracts';
        $data['search_url'] = 'contracts/search_contracts';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }





    #Callout Contracts
    function calloutcontracts()
    {
        #print_r($_POST);
        # exit("MOMOVR");
        //clean up data


        $call_off_order_no = mysql_real_escape_string($_POST['call_off_order_no']);

        $contract_value = removeCommas(mysql_real_escape_string($_POST['contract_value']));

        $date_of_calloff_orders = '';
        if(!empty($_POST['date_of_calloff_orders']))
            $date_of_calloff_orders = date('Y-m-d',strtotime($_POST['date_of_calloff_orders']));


        $planned_completion_date = '';
        if(!empty($_POST['planned_completion_date']))
            $planned_completion_date = date('Y-m-d',strtotime($_POST['planned_completion_date']));

        $actual_completion_date = '';
        if(!empty($_POST['actual_completion_date']))
            $actual_completion_date = date('Y-m-d',strtotime($_POST['actual_completion_date']));


        $subject_of_procurement = mysql_real_escape_string($_POST['subject_of_procurement']);
        $status_of_procurement = mysql_real_escape_string($_POST['status_of_procurement']);

        $total_actual_payments = removeCommas(mysql_real_escape_string($_POST['total_actual_payments']));
        $user_department = mysql_real_escape_string($_POST['user_department']);

        $contractid = $_POST['contractid'];

        $userid = $this->session->userdata('userid');
        $calloff_provider = mysql_real_escape_string($_POST['calloff_provider']);

        $search_query = "";

        if(!empty($_POST['call_off_id']))
        {
            $search_query = " AND call_of_orders.id NOT ".$_post['call_off_id'];
        }



        $check_call_off = $this->db->query("select * from call_of_orders INNER JOIN contracts ON call_of_orders.contractid = contracts.id  WHERE  contracts.bidinvitation_id = (SELECT C.bidinvitation_id FROM  contracts C WHERE C.id ='".$contractid."'  LIMIT 1 )    AND  call_of_orders.call_off_order_no LIKE '%".$call_off_order_no."%'  ".$search_query."   ") ->result_array();



        if(!empty($check_call_off))
        {
            echo "WARNING : Call Off Order Number  Exists in the System ".$call_off_order_no;

            exit();
        }



        $data = array(
            'CALLOFFORDERS' => $call_off_order_no,
            'SUBJECTOFPROCUREMENT' => $subject_of_procurement,
            'DATEOFCALLOFFORDERS' => $date_of_calloff_orders,
            'USERDEPARTMENT' => $user_department,
            'CONTRACTVALUE' => $contract_value,
            'PLANNEDCOMPLETIONDATE' => $planned_completion_date,
            'ACTUALCOMPLETIONDATE' => $actual_completion_date,
            'TOTALACTUALPAYMENT' => $total_actual_payments,
            'CONTRACTID' => $contractid,
            'AUTHOR' => $userid,
            'STATUS_OF_PROCUREMENT'=>$status_of_procurement,
            'RECEIPTID' => $calloff_provider

        );

        if(!empty($_POST['call_off_id']))
        {
            $data['ID']=mysql_real_escape_string($_POST['call_off_id']);
            $result = $this->db->query($this->Query_reader->get_query_by_code('update_call_off_order',$data ));
            log_action('update',' Call OFF Order '.$call_off_order_no.' Has Been Updated Succesfully ', '  Call OFF Order '.$call_off_order_no.' Has Been Updated Succesfully ');


            echo "1";
        }
        else
        {
            $result = $this->db->query($this->Query_reader->get_query_by_code('new_callout_order',$data ));
            log_action('create',' Call OFF Order '.$call_off_order_no.' Has Been Created Succesfully ', '  Call OFF Order '.$call_off_order_no.' Has Been Created Succesfully ');


            echo "1";

        }




    }


    #Delete Archive Call Off Orders
    function delete_calloutorders()
    {
        $data = array('status' => 'N' ,'id'=>mysql_real_escape_string($_POST['calloffid']) );
        $result = $this->db->query($this->Query_reader->get_query_by_code('update_calloff_order_status',$data ));
        log_action('delete',' Call OFF Order Has Been Deleted  Succesfully ', '  Call OFF Order  Has Been Deleted Succesfully ');

        #  exit($this->db->last_query());
        echo "1";

    }


    #Edit Call Off Orders
    function edit_calloutorders()
    {
        //print_r($_POST);
        $calloffid = $_POST['calloffid'];

        $data = array('searchstring' => ' call_of_orders.isactive="Y" AND call_of_orders.id = '.$calloffid );

        $qs = $this->Query_reader->get_query_by_code('fetch_call_off_orders',$data );
        //isactive ="Y" AND

        #print_r($qs);

        $data['callofforders'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_call_off_orders',$data )) -> result_array();
        #exit($this->db->last_query());


        $this->load->view('contracts/add_call_off_order',$data);

    }





    #View Call off Orders
    function viewcalloutcontracts()
    {
        //print_r($_POST);
        $bidinvitation_id = $_POST['bidinvitation_id'];
        $contractid = $_POST['contractid'];

        #VIEW ALL CALL OFF ASSOCIATED TO A BID INVITATION IN CONTRACTS
        $data = array('searchstring' => ' call_of_orders.isactive="Y" AND contracts.id = '.$contractid );

        $qs = $this->Query_reader->get_query_by_code('fetch_call_off_orders',$data );
        //isactive ="Y" AND

        #print_r($qs);

        $data['callofforders'] = $this->db->query($this->Query_reader->get_query_by_code('fetch_call_off_orders',$data )) -> result_array();
        #exit($this->db->last_query());
        #print_r("MOOEOE"); exit();

        $this->load->view('contracts/manage_callofforders', $data);

        //print_r($result);


    }








    function contract_variation_add()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i', 'b'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $app_select_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND PP.pde_id ="'. $userdetails[0]['pde'] .'"';
        }


        #user is editing
        if(!empty($data['i']))
        {
            $contract_id = decryptValue($data['i']);
            $data['formdata'] = $this->Query_reader->get_row_as_array('search_table', array('table'=>'contracts', 'limittext'=>'', 'orderby'=>'id', 'searchstring'=>' id="'. $contract_id .'" AND isactive="Y"'));
            $data['formdata']['vi'] = $data['i'];
            # print_r($data['formdata']); exit();
            #get procurement plan details
            if(!empty($data['formdata']['bidinvitation_id']))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=>' bidinvitations.id="'. $data['formdata']['bidinvitation_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded '));

                $app_select_str .= ' AND PPE.id ="'. $data['formdata']['procurement_ref_id'] .'" ';
                $data['formdata']['prefid'] = $data['formdata']['procurement_ref_id'];

                $data['procurement_plan_entries'][0]['id'] = $data['formdata']['procurement_ref_id'];
                $data['procurement_plan_entries'][0]['procurement_ref_no'] = $data['formdata']['procurement_details']['procurement_ref_no'];
            }

            #Get the contract prices
            //  $data['formdata']['contract_amount'] = $this->db->query($this->Query_reader->get_query_by_code('contract_variations_price_detail', array('searchstring'=>' AND CP.contract_id="'. $contract_id .'"')))->result_array();
        }
        else
        {

            $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('uncontracted_procurements', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' PPE.procurement_ref_no ' )))->result_array();
        }


        $data['contractvariations'] = $this->db->query("SELECT * FROM contracts_variations WHERE contracts_variations.contractid = '".$contract_id."' ORDER BY dateadded DESC limit 1 ")->result_array();
        #exit($this->db->last_query());

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();

        $data['page_title'] = (!empty($data['i'])? 'Edit contract details' : 'Award contract');
        $data['current_menu'] = 'award_contract';
        $data['view_to_load'] = 'contracts/contract_variation_form';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }


    #Save awarded contract variation
    function award_contract_variation()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        if($this->input->post('save'))
        {

            $statusamount = $_POST['statusamount'];

            $required_fields = array('contract_variation_extension_duration', 'new_completion_date', 'contract_variation_details');
            $data['formdata'] = $_POST;

            if(empty($_POST['contract_amount']))
            {
                $validation_results['bool'] = FALSE;
                $validation_results['requiredfields'] = 'contract_amount';
            }
            else
            {
                $_POST = clean_form_data($_POST);
                $validation_results = validate_form('', $_POST, $required_fields);
            }



            $_POST['commencement_date'] = custom_date_format('Y-m-d',$_POST['commencement_date']);
            $_POST['new_completion_date'] = custom_date_format('Y-m-d',$_POST['new_completion_date']);

            #Only proceed if the validation for required fields passes
            if($validation_results['bool'])
            {
                //commencement_date
                $initial_completion_date = $_POST['commencement_date'];

                if(!empty($initial_completion_date))
                    $initial_completion_date = custom_date_format('Y-m-d',$initial_completion_date);
                else
                    $initial_completion_date = '';



                $newcompletiondate = $_POST['new_completion_date'];

                if(!empty($newcompletiondate))
                    $newcompletiondate = custom_date_format('Y-m-d',$newcompletiondate);
                else
                    $newcompletiondate = '';

                $contract_variation_details = mysql_real_escape_string($_POST['contract_variation_details']);

                $contract_variation_extension_duration = mysql_real_escape_string($_POST['contract_variation_extension_duration']);
                $contractid = decryptValue($data['i']);

                $_POST = array_merge($_POST, array('author' => $this->session->userdata('userid')));

#print_r($_POST);exit();

                $contract_data=array(
                    'NEWPLANNEDDATEOFCOMPLETION'=>$newcompletiondate,
                    'duration'=>$contract_variation_extension_duration,
                    'contractvariationdetails'=>$contract_variation_details,
                    'contractid'=>$contractid,
                    'details' => $contract_variation_details,
                    'author' => $_POST['author'] ,
                    'INITIALCOMPLETIONDATE' => $initial_completion_date,
                );

                #add contract variation ::

                $result = $this->db->query($this->Query_reader->get_query_by_code('new_contract_variation',$contract_data ));






                // $result = $this->db->query($this->Query_reader->get_query_by_code('award_contract', $_POST));

                if($result)
                {

                    $contract_variation_id = $this->db->insert_id();

                    // $contract_variation_id = 1;
                    foreach($_POST['contract_amount'] as $contract_amount)
                    {
                        $amount_values = explode('__', $contract_amount);
                        $dataarray =   array('contract_variation_id'=> $contract_variation_id,
                            'amount'=>removeCommas($amount_values[0]),
                            'xrate'=>removeCommas($amount_values[2]),
                            'currency_id'=>removeCommas($amount_values[1]),
                            'author'=> $this->session->userdata('userid'),
                            'price_variation_type'=> $_POST['statusamount']

                        );


                        $this->db->insert('contracts_variations_prices',$dataarray);

                        #  print_r($this->db->last_query());
                        #  echo"<br/>";

                    }

                    #  exit("pass");

                }



            }


            #exit($this->db->_error_message());


            #Format and send the errors
            if(!empty($result) && $result){
                $this->session->set_userdata('usave', "The contract data has been successfully saved.");
                log_action('create',' A Contract Has been Created Successfully  ', '  A Contract Has been Created Successfully');


                redirect("contracts/manage_contracts/m/usave");
            }
            else if(empty($data['msg']))
            {
                $data['msg'] = "ERROR: The contract could not be saved or was not saved correctly.";
            }






        }
        # End validation

        #pick some data
        if(!empty($_POST['prefid']))
        {
            $dataarray = explode("__", $_POST['prefid']);

            //begining
            if(!empty($dataarray[1]))
            {
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_contracts', array('searchstring'=>' procurement_plan_entries.id="'. $dataarray[0] .'" AND bidinvitations.id = '. $dataarray[1].'' , 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded '));

//                    $app_select_str .= ' AND PPE.id ="'. $dataarray[0] .'" ';
                $data['formdata']['prefid'] = $dataarray[0];
                $data['formdata']['bidinvitation_id'] = $dataarray[1];

                $data['procurement_plan_entries'][0]['id'] = $dataarray[0];
                $data['procurement_plan_entries'][0]['procurement_ref_no'] = $data['formdata']['procurement_details']['procurement_ref_no'];
            }


            //ending

        }




        if((empty($validation_results['bool']) || (!empty($validation_results['bool']) && !$validation_results['bool'])) && empty($data['msg']) )
        {
            $data['msg'] = "WARNING: The highlighted fields are required.";
        }

        $data['requiredfields'] = $validation_results['requiredfields'];
        $data['contractdetails'] = $_POST;







        $app_select_str = '';

        if($this->session->userdata('isadmin') == 'N')
        {
            $userdetails = $this->db->get_where('users', array('userid'=>$this->session->userdata('userid')))->result_array();
            $app_select_str .= ' AND PP.pde_id ="'. $userdetails[0]['pde'] .'"';
        }



        $data['procurement_plan_entries'] = $this->db->query($this->Query_reader->get_query_by_code('uncontracted_procurements', array('searchstring'=>$app_select_str, 'limittext'=>'', 'orderby'=>' PPE.procurement_ref_no ' )))->result_array();

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();
        if (!empty($data['i'])) {
            $data['vi'] = $data['i'];
        }



        $data['page_title'] = 'Contract Variations Details';
        $data['current_menu'] = 'award_contract';
        $data['view_to_load'] = 'contracts/contract_variation_form';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);

    }


    #fetch Contracted Framework Providers
    function fetch_contracted_framework_providers()
    {
        $contractid = mysql_real_escape_string($_POST['contractid']);
        $bid_invitation_calloff = mysql_real_escape_string($_POST['bid_invitation_calloff']);
        $searchstring  = array('searchstring' => '    AND   beb = "Y" AND   contracts.id = "'.$contractid.'" '  );

        $records =   $this->db->query($this->Query_reader->get_query_by_code('fetch_contracted_framework_providers', $searchstring))->result_array();

        if(!empty($records))
        {
            $options = '<option selected>Select Provider</option>';
            foreach ($records as $key => $row)
            {
                # code...
                $query = $this->db->query("select * from providers WHERE providers.providerid IN(".$row['providers'].")")->result_array();
                $options .='<option value="'.$row['receiptid'].'">'.$query[0]['providernames'].' </option>';
            }
        }
        else
        {
            $options = '<option>No Awarded Framework BEBs </option>';
        }

        print_r($options);
    }


    //Proccess Call off Orders Sequence Number
    function proccess_calloff_order_sequence_number()
    {
        //AND  contracts.isactive='Y'
        # print_r($_POST);

        #exit();
        $contract_id = mysql_real_escape_string($_POST['contractid']);
        $bid_invitation_calloff = mysql_real_escape_string($_POST['bid_invitation_calloff']);
        $query = $this->db->query("select * from call_of_orders INNER JOIN contracts ON call_of_orders.contractid = contracts.id  WHERE  contracts.bidinvitation_id =  ( SELECT C.bidinvitation_id FROM contracts C  WHERE C.id = '".$contract_id."'  LIMIT 1 )  AND call_of_orders.isactive ='Y'   ") ->result_array();
        $refrence_number = $this->db->query(" SELECT BI.procurement_ref_no FROM bidinvitations BI  INNER JOIN contracts C ON BI.id = C.bidinvitation_id WHERE C.id = '".$contract_id."' ")->result_array();

        $reference_no = $refrence_number[0]['procurement_ref_no'];


        $x_row = 0;
        $sequence_number = '';

        $sequence_numbers = array();
        if(!empty($query))
        {
            foreach ($query as $row => $record) {
                # code...
                #  print_r($record['call_off_order_no']);
                array_push( $sequence_numbers, $record['call_off_order_no']);

            }


        }
        else
        {

            $sequence_number = $reference_no.'/'.$x_row;
        }

        A:
        $x_row = $x_row + 1;
        $sequence_number = $reference_no.'/'.$x_row;
        if(in_array( $sequence_number, $sequence_numbers))
        {

            goto A;
        }
        else
        {

        }



        print_r($sequence_number);
        exit();


    }

    function call_off_orders_status()
    {
        $call_off_order_id = mysql_real_escape_string($_POST['call_off_order_id']);
        $status = mysql_real_escape_string($_POST['status']);

        $query = $this->db->query("UPDATE call_of_orders SET status = '".$status."' WHERE id='".$call_off_order_id."' ");

        print_r($status);

    }




    function contract_variation_view()
    {

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        #  print_r($data);
        if(!empty($data['i']))
        {

            $contractid = decryptValue($data['i']);
            $searchstring = ' AND  C.id =' .$contractid.' ';
            if(!empty($_POST['lotid']) && ($_POST['lotid'] > 0 ) )
            {
                $searchstring .= ' AND  C.lotid =' .$_POST['lotid'].' AND C.isactive ="Y" ';
            }
            $data['pagenetedlist'] = paginate_list($this, $data, 'viewcontractvariations',$data = array('searchstring' => $searchstring),100);




            $this->load->view('contracts/manage_contract_variations', $data);

        }
        else
        {
            echo "No Records";
        }
    }



    #This Function Add Del and Edit Contract Payments
    /*
        Payments Made Between Contract Award and Contract Completion :
    */
    function contract_payments(){

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's', 'i'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $author = $this->session->userdata('userid');

        $data = $_POST;

        $data['currencies'] = $this->db->get_where('currencies', array('isactive'=>'Y'))->result_array();
        $level = $_POST['level'];



        switch ($level)
        {
            case 'edit_payment':


                if(!empty($_POST['contract_amount']))
                {

                    $payment_id = $_POST['payment_id'];
                    #Add Details and get the id gotten  ::
                    $result  = $this->db->query($this->Query_reader->get_query_by_code('edit_contract_payments', array('contractid'=>$_POST['contractid'],'details'=>$_POST['payment_details'] , 'author' => $author,'datepaid'=> custom_date_format('Y-m-d',$_POST['dateadded']),'id'=>$payment_id) ) );



                    #Delete Contract Prices
                    $delete_st = $this->db->query(" DELETE FROM contract_payments_prices WHERE  payment_id = '".$payment_id."' ");



                    foreach($_POST['contract_amount'] as $contract_amount)
                    {
                        $amount_values = explode('__', $contract_amount);
                        #print_r($amount_values);

                        # exit();


                        $this->db->query("INSERT INTO contract_payments_prices(payment_id,amount,xrate,currency_id,author)     VALUES(".$payment_id.",  '".removeCommas($amount_values[0])."'  ,'".removeCommas($amount_values[2])."'  ,'".removeCommas($amount_values[1])."'  ,'". $this->session->userdata('userid')."'     )") ;



                    }
                    echo "1";


                }
                else
                {
                    echo "WARNING: The highlighted fields are required.";
                }



                break;
            case 'del_payment':
                $this->db->query("UPDATE  contract_payments SET isactive ='N' WHERE  contract_payments.id = '".$data['paymentid']."' ");

                echo "1";

                break;
            #View Payments
            case 'view_payments':
                #  view_contracts_payments
                $data['contracts_payments']  = $this->db->query($this->Query_reader->get_query_by_code('view_contracts_payments', array('searchstring'=>' AND CP.contract_id = '.$data['contractid'].' ' )))->result_array();



                $this->load->view('contracts/view_contracts_payments', $data);

                break;

            #Add Payment
            case 'add_payment':
                # code...
                if(!empty($_POST['save']))
                {


                    $required_fields = array('contract_variation_extension_duration', 'new_completion_date', 'contract_variation_details');
                    $validation_results = validate_form('', $_POST, $required_fields);


                    if(!empty($_POST['contract_amount']))
                    {


                        #Add Details and get the id gotten  ::
                        $result  = $this->db->query($this->Query_reader->get_query_by_code('save_contracts_payments', array('contractid'=>$_POST['contractid'],'details'=>$_POST['payment_details'] , 'author' => $author,'datepaid'=> custom_date_format('Y-m-d',$_POST['dateadded']) ) ) );



                        if($result){
                            $paymentid = $this->db->insert_id();

                            #Add the contract prices
                            foreach($_POST['contract_amount'] as $contract_amount)
                            {
                                $amount_values = explode('__', $contract_amount);
                                #print_r($amount_values);

                                $this->db->query("INSERT INTO contract_payments_prices(payment_id,amount,xrate,currency_id,author)     VALUES('".$paymentid."',  '".removeCommas($amount_values[0])."'  ,'".removeCommas($amount_values[2])."'  ,'".removeCommas($amount_values[1])."'  ,'". $this->session->userdata('userid')."'     )") ;

                            }
                            echo "1";

                        }
                        else
                        {
                            echo "0";
                        }
                    }
                    else{
                        echo "WARNING: The highlighted fields are required.";
                    }



                }
                else{

                    if(!empty($data['paymentid']))
                    {

                        $paymentid =   $data['paymentid'];

                        $data['formdata']  = $this->db->query($this->Query_reader->get_query_by_code('view_contracts_payments', array('searchstring'=>' AND CP.id = '.$paymentid.' ' )))->result_array();

                        $data['formdata']['contract_amount'] = $this->db->query($this->Query_reader->get_query_by_code('payment_price_details', array('searchstring'=>' AND CP.payment_id="'. $paymentid .'"')))->result_array();

                        $data['formdata']['level'] ='edit_payment';


                    }

                    $this->load->view('contracts/add_payment', $data);
                }

                break;

            default:
                #load form ;:
                print_r($_POST);
                exit("default");
                break;
        }



    }


    function contracts_due_to_expire($userid = '')
    {

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

        $search_str = '';

        if(!empty($userid))
        {
            $userdata = $this->db->get_where('users',$userid)->result_array();
            $search_str = ' AND PP.pde_id="'. $userdata[0]['pde'] .'"';
        }

        $search_str .= ' AND C.completion_date BETWEEN  CURDATE() AND DATE_ADD(CURDATE(), INTERVAL  10  DAY)';


        #Get the paginated list of users
        $data['contracts_page'] = paginate_list($this, $data, 'get_published_contracts', array('orderby'=>'C.date_signed DESC', 'searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $search_str));
        #print_r($data['contracts_page']); exit()
        #exit($this->db->last_query());

        $data = handle_redirected_msgs($this, $data);
        $data = add_msg_if_any($this, $data);




        //update data

        $str = '<style>.tablex tr th{text-align:left; background:#ccc; padding:20px; text-transform:uppercase;} .tablex tr td{border:1px solid #eee; padding:5px; font-size:15px;}</style> <div class="widget-body" id="results" style="width:100%;">';

        if(!empty($data['contracts_page']['page_list'])):

            $str .= '<h2 style="width:100%; text-align:center; padding:5px;">CONTRACTS  SOON EXPIRING THIS MONTH </h2> <table class="table tablex table-striped table-hover" style="width:100%; padding:5px;">'.
                '<thead>'.
                '<tr style="width:100%; padding:5px; border:1px solid #eee;" >'.
                '<th width="94px"></th>';
            if($this->session->userdata('isadmin') == 'N')
            {
                $str  .= '<th> Procuring And Diposing Entity </th>';
            }
            $str  .=   '<th>Date signed</th>'.
                '<th>Procurement Reference Number </th>'.
                '<th>Subject of procurement</th>'.
                '<th>Status</th>'.
                '<th style="text-align:right">Contract amount (UGX)</th>'.
                '<th class="hidden-480">Date added</th>'.
                '</tr>'.
                '</thead>'.
                '</tbody>';

            foreach($data['contracts_page']['page_list'] as $row)
            {
                $edit_str = '';
                $delete_str = '';
                if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
                {
                    $delete_str = '<a title="Delete contract details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'contracts/delete_contract/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this contract?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
                    $termintate_str = '';

                }else
                {
                    $termintate_str = '<a href="'. base_url() .'contracts/contract_termination/i/'.encryptValue($row['id']) .'" title="Click to terminate contract"><i class="fa fa-times-circle"></i></a>';
                    $edit_str = '<a title="Edit contract details" href="'. base_url() .'contracts/contract_award_form/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';
                }

                $status_str = '';
                $completion_str = '';

                if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
                {
                    $status_str = '<span class="label label-success label-mini">Completed</span>';
                    $completion_str = '<a title="Click to view contract completion details" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']).'/v/'. encryptValue('view') .'"><i class="fa fa-eye"></i></a>';
                }
                else
                {
                    $status_str = '<span class="label label-warning label-mini">Awarded</span>';
                    $completion_str = '<a title="Click to enter contract completion details"" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']) .'"><i class="fa fa-check"></i></a>';
                }


                $variations = ' <a class="view_variations" id="view_'.$row['id'].'" data-ref="'. base_url() .'contracts/contract_variation_view/i/'.encryptValue($row['id']) .'" title="Click to view Variations "><i class="fa fa-bars"></i></a> &nbsp; &nbsp; ';

                if(empty($row['actual_completion_date']) )
                {
                    $variations .= '<a href="'. base_url() .'contracts/contract_variation_add/i/'.encryptValue($row['id']) .'" title="Click to Add Variations "><i class="fa fa-plus-circle "></i></a> &nbsp; &nbsp;';

                }

                $more_actions = '<div class="btn-group" style="font-size:10px">
                                     <a href="#" class="btn btn-primary">more</a><a href="javascript:void(0);" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><span class="fa fa-caret-down"></span></a>
                                     <ul class="dropdown-menu">
                                         <li><a href="#"><i class="fa fa-times-circle"></i></a></li>
                                         <li class="divider"></li>
                                         <li>'. $completion_str .'</li>
                                     </ul>
                                  </div>';

                $str  .=  '<tr>'.
                    '<td>';
                if($this->session->userdata('isadmin') == 'N')
                    $str  .=   $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $termintate_str .' &nbsp; &nbsp; '.$completion_str.'&nbsp;&nbsp;'.$variations;;

                $str  .=  ' </td>';
                if($this->session->userdata('isadmin') == 'N')
                {
                    $str  .=    '<th> '.$row['pdename'].' </th>';
                }
                $str  .=   '<td>'. custom_date_format('d M, Y',$row['date_signed']) .'</td>'.
                    '<td>'. format_to_length($row['procurement_ref_no'], 30) .'</td>'.
                    '<td>'. format_to_length($row['subject_of_procurement'], 30).'';
                if($row['pmethod'] == 'Framework and Special Contracts' )
                {
                    $str .= '<br/><a href="#" id="'.$row['id'].'" class="togglecalloforders"  > Add Call off Order </a> | <a href="#" data-procurement="'.$row['procurement_ref_no'].'" id="'.$row['id'].'" class="viewlistcalloff" >View Call off Orders </a>  </br/>';
                }

                $str  .=  '</td>'.
                    '<td>'. $status_str .'</td>'.
                    '<td style="text-align:right; font-family:Georgia; font-size:14px">'. addCommas($row['total_price'], 0) .'</td>'.
                    '<td>'. custom_date_format('d M, Y', $row['dateadded']) .' by '. format_to_length($row['authorname'], 10) .'</td>'.
                    '</tr>';

            }

            $str  .=  '</tbody></table>';


            $str  .=  '<div class="pagination pagination-mini pagination-centered">'.
                pagination($this->session->userdata('search_total_results'), $data['contracts_page']['rows_per_page'], $data['contracts_page']['current_list_page'], base_url().
                    "contracts/manage_contracts/p/%d")
                .'</div>';
        else:
            $str  .=  format_notice('WARNING: No contracts have been signed in the system');
        endif;

        $str  .=  '</div>';

        print_r($str);

        exit();
        //data

        $data['page_title'] = 'Manage contracts';
        $data['current_menu'] = 'view_contracts';
        $data['view_to_load'] = 'contracts/manage_contracts';
        $data['search_url'] = 'contracts/search_contracts';
        $data['form_title'] = $data['page_title'];

        $this->load->view('dashboard_v', $data);
    }









}


?>
