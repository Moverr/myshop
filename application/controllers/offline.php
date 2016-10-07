<?php
ob_start();
?>
<?php

class Offline extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('procurement_plan_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('procurement_type_m');
        $this->load->model('notification_m');
        $this->load->model('procurement_plan_status_m');
        $this->load->model('notification_m');
        $this->load->model('sys_file', 'sysfile');
        $this->load->model('currency_m');
        $this->load->model('source_funding_m');
        $this->load->model('offline_m');
        $this->load->model('bid_invitation_m');
        $this->load->model('receipts_m');
        $this->load->model('provider_m');
        $this->load->model('readoutprice_m');
        $this->load->model('bestevaluatedbidder_m');
        $this->load->model('contract_variation_price_m');
        $this->load->model('contract_price_m');
        $this->load->model('contracts_m');
        $this->load->model('contract_variation_m');

        access_control($this);
    }

    //procurement dashboard
    //admin home page
    function index()
    {
        //redirect to page
        redirect(base_url() . $this->uri->segment(1) . '/uploads');
    }

    #Page
    function uploads()
    {

        //if user has permission
        if (check_user_access($this, 'offline_uploads')) {

            $data['page_title'] = 'Upload Offline Templates';
            $data['current_menu'] = 'offline_uploads';
            $data['view_to_load'] = 'procurement/admin/offline_uploads_v';
            $data['view_data']['form_title'] = $data['page_title'];


            //if there is an upload
            if ($_POST) {
//                print_array($_POST);


                $errors = array();
                $activityLog = array();

                //ensure pde is selected
                if (!$_POST['pde']) {
                    $errors[] = 'Select a PDE';

                }

                if ($this->uri->segment(3)) {

                    //switch depending on the steps
                    switch ($this->uri->segment(3)) {
                        case 'step_2':
                            $upload_folder = 'uploads';
                            $config['upload_path'] = './' . $upload_folder . '/';
                            $config['allowed_types'] = 'xlsx|xls';
                            $new_name = time() . '_' . $_FILES["userfile"]['name'];
                            $config['file_name'] = $new_name;

                            $this->load->library('upload', $config);

                            //if file was not uploaded
                            if (!$this->upload->do_upload()) {

                                $errors[] = $this->upload->display_errors();

                            } else {
                                //if there is a file uploaded
                                //grab file url
                                $fileUrl = $upload_folder . '/' . $new_name;
                                $result_array = read_excel_data($fileUrl);


                                //if a template id not provided
                                if (!$result_array['Sheet1'][1]['B']) {
                                    $errors[] = 'No template ID provided';

                                }

                                //veriffy the template id
                                $entries = $this->offline_m->get_records_by_template($result_array['Sheet1'][1]['B'], '', $this->input->post('pde'));
                                if (!$entries) {
                                    $errors[] = 'No procurement plan template with ID <b>' . $result_array['Sheet1'][1]['B'] ? $result_array['Sheet1'][1]['B'] : 'Not provided' . '</b> detected. ';

                                }

                                //if there are no errors
                                if (!count($errors)) {

                                    $expected_entries = array();
                                    foreach ($entries as $entry) {
                                        $expected_entries[] = $entry['template_record'];
                                    }


                                    //cross referrence records against PP entries

                                    foreach ($result_array['Sheet1'] as $key => $value) {
                                        //get only IFBS and not extra template content
                                        if ($key >= 5) {

                                            //check if IFB correspons to record in plan
                                            if (in_array($result_array['Sheet1'][$key]['A'], $expected_entries)) {
                                                //check for mondatory fields
                                                if ($result_array['Sheet1'][$key]['J'] && $result_array['Sheet1'][$key]['C']) {

                                                    //clean info and save
                                                    $estimated_amount_currency = $value['K'] ? $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['K'] . '%"') : 1;


                                                    $procurement_method = $this->procurement_type_m->custom_query('SELECT id FROM procurement_methods WHERE title LIKE "%' . $value['D'] . '%"');
                                                    $bid_currency = $value['R'] ? $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['R'] . '%"') : 1;
                                                    foreach ($entries as $entry) {
                                                        if ($entry['template_record'] == $result_array['Sheet1'][$key]['A']) {
                                                            $template_id = $entry['template_id'];
                                                            $template_record = $entry['template_record'];
                                                            $procurement_id = $entry['entry_id'];
                                                            $ref_number = procurement_plan_ref_number_hint($entry['pde_id'], $entry['procurement_type'], $entry['financial_year'], $entry['procurement_plan'], $result_array['Sheet1'][$key]['C']);
                                                        }
                                                    }

                                                    //prevent duplicate entries for each record
                                                    $where = array(
                                                        'template_id' => $template_id,
                                                        'template_record' => $template_record

                                                    );

                                                    //print_array($_POST);

                                                    if (!$this->bid_invitation_m->get_where($where)) {

                                                        $procurement_plan_data = array
                                                        (
                                                            'subject_details' => $value['B'],
                                                            'procurement_ref_no' => $ref_number,
                                                            'procurement_id' => $procurement_id,
                                                            'entity_procured_for' => get_pde_info_by_id($this->input->post('pde'), 'title'),
                                                            'procurement_method_ifb' => $value['D'] ? $procurement_method[0]['id'] : '',
                                                            'source_of_funding' => removeCommas($value['E']),
                                                            'initiated_by' => $value['F'],
                                                            'quantity' => $value['G'] ? $value['G'] : '',
                                                            'dateofconfirmationoffunds' => $value['H'] ? database_ready_format($value['H']) : '',
                                                            'additional_notes' => $value['I'] ? $value['I'] : '',
                                                            'estimated_amount' => $value['J'] ? removeCommas($value['J']) : '',
                                                            'estimated_amount_exchange_rate' => removeCommas($value['L']),
                                                            'estimated_amount_currency' => $estimated_amount_currency[0]['id'],
                                                            'cc_approval_date' => $value['M'] ? database_ready_format($value['M']) : '',
                                                            'expression_of_interest' => $value['N'] ? $value['N'] : '',
                                                            'bid_security_amount' => $value['P'] ? removeCommas($value['P']) : '',
                                                            'bid_security_amount_exchange_rate' => $value['Q'] ? $value['Q'] : 0,
                                                            'bid_security_currency' => $bid_currency[0]['id'] ? $bid_currency[0]['id'] : '',
                                                            'pre_bid_meeting_date' => $value['S'] ? database_ready_format($value['S']) : '',
                                                            'bid_submission_deadline' => $value['T'] ? database_ready_format($value['T']) : '',
                                                            'bid_openning_address' => $value['AA'] ? database_ready_format($value['AA']) : '',
                                                            'start_hours' => $value['V'] ? $value['V'] : '',
                                                            'end_hours' => $value['W'] ? $value['W'] : '',
                                                            'documents_inspection_address' => $value['U'] ? $value['U'] : '',
                                                            'documents_address_issue' => $value['X'] ? database_ready_format($value['X']) : '',
                                                            'bid_receipt_address' => $value['Y'] ? database_ready_format($value['Y']) : '',
                                                            'bid_evaluation_from' => $value['AB'] ? database_ready_format($value['AC']) : '',
                                                            'bid_evaluation_to' => $value['AC'] ? database_ready_format($value['AD']) : '',
                                                            'display_of_beb_notice' => $value['AD'] ? database_ready_format($value['AE']) : '',
                                                            'contract_signature_date' => $value['AF'] ? database_ready_format($value['AG']) : '',
                                                            'contract_award_date' => $value['AE'] ? database_ready_format($value['AF']) : '',
                                                            'bidvalidity' => $value['AH'] ? database_ready_format($value['AI']) : '',
                                                            'template_id' => $template_id,
                                                            'template_record' => $template_record,
                                                            'temp_procurement_ref_no' => $result_array['Sheet1'][$key]['C']
                                                        );


                                                        $result = $this->bid_invitation_m->create($procurement_plan_data);


                                                        if ($result) {
                                                            $saved_records[] = $value;

                                                        }

                                                    }


                                                } else {
                                                    //if estimated amount is skipped
                                                    if (!$result_array['Sheet1'][$key]['J']) {
                                                        //if ifb entries to not correspond to procurement plan entries
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no estimated amount';


                                                    }

                                                    //IF REF NO
                                                    if (!$result_array['Sheet1'][$key]['C']) {
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No unique procurement reference number was provided';


                                                    }
                                                }


                                            } else {
                                                //if ifb entries to not correspond to procurement plan entries
                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no corresponding entry in the Offline procurement template';

                                            }
                                        }

                                    }

                                    //if upload successful and there are no errors
                                    if (!count($errors)) {
                                        $data['success_2'] = 'Template successfully saved';
                                    }

                                }

                            }
                            break;

                        case 'step_3':

                            switch ($this->uri->segment(4)) {
                                case 1:
                                    $upload_folder = 'uploads';
                                    $config['upload_path'] = './' . $upload_folder . '/';
                                    $config['allowed_types'] = 'xlsx|xls';
                                    $new_name = time() . '_' . $_FILES["userfile"]['name'];
                                    $config['file_name'] = $new_name;

                                    $this->load->library('upload', $config);

                                    //if file was not uploaded
                                    if (!$this->upload->do_upload()) {

                                        $errors[] = $this->upload->display_errors();

                                    } else {
                                        //if there is a file uploaded
                                        //grab file url
                                        $fileUrl = $upload_folder . '/' . $new_name;
                                        $result_array = read_excel_data($fileUrl);


                                        //print_array($result_array);


                                        //if a template id not provided
                                        if (!$result_array['Sheet1'][1]['B']) {
                                            $errors[] = 'No template ID provided';

                                        }

                                        //veriffy the template id
                                        $entries = $this->offline_m->get_records_IFBs_by_template($result_array['Sheet1'][1]['B'], '', $this->input->post('pde'));
                                        if (!$entries) {
                                            $errors[] = 'No procurement plan template with ID <b>' . $result_array['Sheet1'][1]['B'] ? $result_array['Sheet1'][1]['B'] : 'Not provided' . '</b> detected. ';

                                        }

//                                print_array($entries);
//
//                                print_array($entries);
//                                exit;

                                        //if there are no errors
                                        if (!count($errors)) {

                                            $expected_entries = array();
                                            foreach ($entries as $entry) {
                                                $expected_entries[] = $entry['temp_procurement_ref_no'];
                                            }


                                            //cross referrence records against PP entries

                                            foreach ($result_array['Sheet1'] as $key => $value) {
                                                //get only IFBS and not extra template content
                                                if ($key >= 4) {

                                                    //check if IFB correspons to record in plan
                                                    if (in_array($result_array['Sheet1'][$key]['A'], $expected_entries)) {
                                                        //check for mondatory fields
                                                        if ($result_array['Sheet1'][$key]['A'] && $result_array['Sheet1'][$key]['B']) {

                                                            //clean info and save
                                                            $amount_currency = $value['E'] ? $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['E'] . '%"') : 1;

                                                            $template_id = '';
                                                            $template_record = '';
                                                            $bid_id = '';
                                                            foreach ($entries as $entry) {
                                                                if ($entry['temp_procurement_ref_no'] == $result_array['Sheet1'][$key]['A']) {
                                                                    $template_id = $entry['template_id'];
                                                                    $template_record = $entry['template_record'];
                                                                    $bid_id = $entry['id'];
                                                                }
                                                            }

                                                            //prevent duplicate entries for each record
                                                            $where = array(
                                                                'template_id' => $template_id,
                                                                'template_record' => $template_record,
                                                                'bid_id' => $bid_id

                                                            );
//                                                    print_array($this->receipts_m->get_where($where));
////
//                                                    exit;

                                                            if (!$this->receipts_m->get_where($where)) {

                                                                //add provider and get ID if exists get the id
                                                                $provideInfo = $this->provider_m->custom_query('SELECT providerid FROM providers WHERE providers.providernames LIKE "%' . $value['B'] . '%" LIMIT 1');
//
//                                                        print_array($provideInfo);
//                                                        exit;

                                                                if ($provideInfo) {
                                                                    $provider = $provideInfo[0]['providerid'];

                                                                } else {
                                                                    $where = array(
                                                                        'providernames' => $value['B']
                                                                    );
                                                                    $provider = $this->provider_m->create($where);
                                                                }

//                                                        print_array($provider);
//                                                        exit;


                                                                $procurement_plan_data = array
                                                                (
                                                                    'bid_id' => $bid_id,
                                                                    'providerid' => $provider,
                                                                    'nationality' => $value['C'] ? ucwords($value['C']) : '',
                                                                    'readoutprice' => $value['D'] ? $value['D'] : '',
                                                                    'currence' => $value['E'] ? $value['E'] : '',
                                                                    'datereceived' => $value['G'] ? database_ready_format($value['G']) : '',
                                                                    'received_by' => $value['H'] ? $value['H'] : '',
                                                                    'template_id' => $template_id,
                                                                    'template_record' => $template_record
                                                                );


                                                                $result = $this->receipts_m->create($procurement_plan_data);


                                                                if ($result) {
                                                                    //save read out price info TODO
                                                                    $where = array(
                                                                        'readoutprice' => $value['E'] ? $value['E'] : '',
                                                                        'currence' => $value['E'] ? $value['E'] : '',
                                                                        'exchangerate' => $value['F'] ? $value['F'] : '',
                                                                        'receiptid' => $result
                                                                    );

                                                                    $this->readoutprice_m->create($where);


                                                                    $saved_records[] = $value;

                                                                }

                                                            } else {
                                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  is a duplicate entry';

                                                            }


                                                        } else {
                                                            //if reference is skipped
                                                            if (!$result_array['Sheet1'][$key]['A']) {
                                                                //if ifb entries to not correspond to procurement plan entries
                                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no procurement reference number';


                                                            }

                                                            //IF  NO PROVIDER
                                                            if (!$result_array['Sheet1'][$key]['B']) {
                                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No provider';


                                                            }
                                                        }


                                                    } else {
                                                        //if ifb entries to not correspond to procurement plan entries
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no corresponding bid invitation';

                                                    }
                                                }

                                            }

                                            //if upload successful and there are no errors
                                            if (!count($errors)) {
                                                $data['success_3_v1'] = 'Template successfully saved';
                                            }

                                        }

                                    }
                                    break;
                                case 2:
                                    $upload_folder = 'uploads';
                                    $config['upload_path'] = './' . $upload_folder . '/';
                                    $config['allowed_types'] = 'xlsx|xls';
                                    $new_name = time() . '_' . $_FILES["userfile"]['name'];
                                    $config['file_name'] = $new_name;

                                    $this->load->library('upload', $config);

                                    //if file was not uploaded
                                    if (!$this->upload->do_upload()) {

                                        $errors[] = $this->upload->display_errors();

                                    } else {
                                        //if there is a file uploaded
                                        //grab file url
                                        $fileUrl = $upload_folder . '/' . $new_name;
                                        $result_array = read_excel_data($fileUrl);


                                        //print_array($result_array);

                                        //if a template id not provided
                                        if (!$result_array['Sheet1'][1]['B']) {
                                            $errors[] = 'No template ID provided';

                                        }
                                        //verify the template id
                                        $entries = $this->offline_m->get_providers_by_template($result_array['Sheet1'][1]['B'], '', $this->input->post('pde'));

                                        //print_array($entries);
                                        if (!$entries) {
                                            $errors[] = 'No provider template ID <b>' . $result_array['Sheet1'][1]['B'] ? $result_array['Sheet1'][1]['B'] : 'Not provided' . '</b> detected. ';

                                        }

                                        //if there are no errors
                                        if (!count($errors)) {

                                            $expected_entries = array();
                                            foreach ($entries as $entry) {
                                                $expected_entries[] = $entry['template_record'];
                                            }

                                            //print_array($expected_entries);


                                            //cross referrence records against PP entries

                                            foreach ($result_array['Sheet1'] as $key => $value) {
                                                //get only IFBS and not extra template content

                                                if ($key >= 4) {

                                                    //check if IFB correspons to record in plan
                                                    if (in_array($result_array['Sheet1'][$key]['A'], $expected_entries)) {

                                                        //check for mondatory fields
                                                        if ($result_array['Sheet1'][$key]['A'] && $result_array['Sheet1'][$key]['D'] && $result_array['Sheet1'][$key]['E'] && $result_array['Sheet1'][$key]['L']) {

                                                            //STEP 1: update the receipts column BEB to ('Y')


                                                            foreach ($entries as $entry) {

                                                                $template_id = $entry['template_id'];
                                                                $template_record = $entry['template_record'];
                                                                $bid_id = $entry['bid_id'];
                                                                $receipt_id= $entry['receiptid'];
                                                                if ($entry['template_record'] == $result_array['Sheet1'][$key]['A']) {
                                                                    //if not yet awarded
                                                                    if ($entry['beb'] == 'P') {
                                                                        $where = array(
                                                                            'beb' => 'Y'
                                                                        );
                                                                        $this->receipts_m->update($entry['receiptid'], $where);
                                                                        $activityLog[] = 'Entry with Record No <b>' . $key . '</b> status has been updated to Best Evaluated Bidder.';

                                                                        //prevent duplicate entries for each record
                                                                        $where = array(
                                                                            'template_id' => $template_id,
                                                                            'template_record' => $template_record,
                                                                            'bidid' => $bid_id,
                                                                            'pid' => $receipt_id

                                                                        );


                                                                        //STEP 2 save into BEB table

                                                                        if (!$this->bestevaluatedbidder_m->get_where($where)) {

                                                                            //add provider and get ID if exists get the id
                                                                            $evaluationMethodInfo = $this->provider_m->custom_query('SELECT evaluation_method_id FROM evaluation_methods WHERE evaluation_methods.evaluation_method_name LIKE "%' . $value['B'] . '%" LIMIT 1');

                                                                            $evaluationMethod=0;
                                                                            if ($evaluationMethodInfo) {
                                                                                $evaluationMethod = $evaluationMethodInfo[0]['evaluation_method_id'];

                                                                            }

                                                                            $procurement_plan_data = array
                                                                            (
                                                                                'bidid' => $bid_id,
                                                                                'type_oem' => $evaluationMethod,
                                                                                'pid' => $bid_id,
                                                                                'num_orb' => $value['D'],
                                                                                'num_orb_local' => $value['E'],
                                                                                'date_oce_r' => $value['F'] ? database_ready_format($value['F']) : '',
                                                                                'ddate_octhe' => $value['C'] ? database_ready_format($value['C']) : '',
                                                                                'date_oaoterbt_cc' => $value['G'] ? database_ready_format($value['G']) : '',
                                                                                'template_id' => $template_id,
                                                                                'template_record' => $template_record,
                                                                                'nationality' => ucwords($value['I']),
                                                                                'date_of_display' => $value['J'] ? database_ready_format($value['J']) : '',
                                                                                'beb_expiry_date' => $value['K'] ? database_ready_format($value['K']) : '',
                                                                                'contractprice' => removeCommas($value['L']),
                                                                                'currency' => ($value['M']),
                                                                                'exchange_rate' => ($value['M']),
                                                                            );


                                                                            $result = $this->bestevaluatedbidder_m->create($procurement_plan_data);


                                                                            if ($result) {

                                                                                $saved_records[] = $value;

                                                                            }

                                                                        } else {
                                                                            $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  is a duplicate entry';

                                                                        }

                                                                    } else {
                                                                        $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It is has already been Best Evaluated Bidder status';
                                                                    }
                                                                }
                                                            }



                                                        } else {
                                                            //if reference is skipped
                                                            if (!$result_array['Sheet1'][$key]['A']) {
                                                                //if ifb entries to not correspond to procurement plan entries
                                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no procurement reference number';


                                                            }

                                                            //IF  NO PROVIDER
                                                            if (!$result_array['Sheet1'][$key]['B']) {
                                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No provider';


                                                            }
                                                        }


                                                    } else {
                                                        //if ifb entries to not correspond to procurement plan entries
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no corresponding provider';

                                                    }
                                                }

                                            }

                                            //if upload successful and there are no errors
                                            if (!count($errors)) {
                                                $data['success_3_v2'] = 'Template successfully saved';
                                            }

                                        }

                                    }
                                    break;
                            }
                            break;

                        case 'step_4':
                            $upload_folder = 'uploads';
                            $config['upload_path'] = './' . $upload_folder . '/';
                            $config['allowed_types'] = 'xlsx|xls';
                            $new_name = time() . '_' . $_FILES["userfile"]['name'];
                            $config['file_name'] = $new_name;

                            $this->load->library('upload', $config);

                            //if file was not uploaded
                            if (!$this->upload->do_upload()) {

                                $errors[] = $this->upload->display_errors();

                            } else {
                                //if there is a file uploaded
                                //grab file url
                                $fileUrl = $upload_folder . '/' . $new_name;
                                $result_array = read_excel_data($fileUrl);


                                //if a template id not provided
                                if (!$result_array['Sheet1'][1]['B']) {
                                    $errors[] = 'No template ID provided';

                                }

                                //veriffy the template id
                                $entries = $this->offline_m->check_for_BEB_template($result_array['Sheet1'][1]['B'], '', $this->input->post('pde'));
                                if (!$entries) {
                                    $errors[] = 'No template with ID <b>' . $result_array['Sheet1'][1]['B'] ? $result_array['Sheet1'][1]['B'] : 'Not provided' . '</b> detected. ';

                                }

//                                print_array($errors);

                                //if there are no errors
                                if (!count($errors)) {

                                    $expected_entries = array();
                                    foreach ($entries as $key=>$val) {
                                        $expected_entries[] = $val['temp_procurement_ref_no'];
                                    }



                                    foreach ($result_array['Sheet1'] as $key => $value) {
                                        //get only IFBS and not extra template content
                                        if ($key >= 4) {

//                                            print_array( $expected_entries);
//                                            print_array($result_array['Sheet1'][$key]['B']);
                                            //check if IFB correspons to record in plan
                                            if (in_array($result_array['Sheet1'][$key]['B'], $expected_entries)) {
//                                                print_array($result_array['Sheet1']);




                                                //check for mondatory fields
                                                if ($result_array['Sheet1'][$key]['B'] && $result_array['Sheet1'][$key]['C']&& $result_array['Sheet1'][$key]['A']&& $result_array['Sheet1'][$key]['D']&& $result_array['Sheet1'][$key]['E']) {

                                                    //clean info and save
                                                    $total_actual_payment_exchange_rate = $value['S'] ? $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['S'] . '%"') : 1;
                                                    $final_contract_value_currency=$value['O'] ? $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['O'] . '%"') : 1;

                                                    $amount_currency = $value['G'] ? $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['G'] . '%"') : 1;


                                                    foreach ($entries as $entry) {

//                                                        print_array($entry);
                                                        if ($entry['temp_procurement_ref_no'] == $result_array['Sheet1'][$key]['B']) {
                                                            $template_id = $entry['template_id'];
                                                            $template_record = $entry['template_record'];
                                                            $procurement_id = $entry['procurement_ref_no'];
                                                            $receipt_id = $entry['receipt_id'];
                                                            $bid_id = $entry['bidid'];
                                                        }
                                                    }

                                                    //prevent duplicate entries for each record
                                                    $where = array(
                                                        'template_id' => $template_id,
                                                        'template_record' => $template_record,
                                                        'procurement_ref_no' => $procurement_id,
                                                        'receiptid' => $receipt_id,
                                                        'bidinvitation_id'=>$bid_id

                                                    );




                                                    //print_array($this->contracts_m->get_where($where)));

                                                    if (!$this->contracts_m->get_where($where)) {

                                                        $procurement_plan_data = array
                                                        (
                                                            'offline_financial_year' => $value['A'],
                                                            'date_signed' => $value['C'] ? database_ready_format($value['C']) : '',
                                                            'commencement_date' => $value['D'] ? database_ready_format($value['D']) : '',
                                                            'completion_date' => $value['E'] ? database_ready_format($value['E']) : '',
                                                            'contract_amount' => removeCommas($value['F']),
                                                            'amount_currency' => $amount_currency[0]['id'],
                                                            'exchange_rate' => removeCommas($value['H']),
                                                            'contract_manager' => $value['I'],
                                                            'days_duration' => strtolower($value['J'])=='decrement'?'-':''.$value['J'],
                                                            'final_contract_value' => removeCommas($value['N']),
                                                            'final_contract_value_currency' => $final_contract_value_currency[0]['id'],
                                                            'final_contract_value_exchange_rate' => removeCommas($value['P']),
                                                            'advance_payment_date' =>  $value['Q'] ? database_ready_format($value['Q']) : '',
                                                            'total_actual_payments' => removeCommas($value['R']),
                                                            'total_actual_payments_currency' => $total_actual_payment_exchange_rate[0]['id'],
                                                            'total_actual_payments_exchange_rate' => $value['T'],
                                                            'actual_completion_date' => $value['U'] ? database_ready_format($value['U']) : '',
                                                            'performance_rating' =>  $value['V'],
                                                            'template_id' => $template_id,
                                                            'template_record' =>  $template_record,
                                                            'bidinvitation_id' =>  $bid_id,
                                                            'receiptid' =>  $receipt_id
                                                        );

//                                                        print_array($procurement_plan_data);

//                                                        exit;


                                                        $result = $this->contracts_m->create($procurement_plan_data);


                                                        if ($result) {
                                                            //save to contract prices table
                                                            $contract_price = array
                                                            (
                                                                'contract_id' => $result,
                                                                'amount' => removeCommas($value['F']),
                                                                'currency_id' => $amount_currency[0]['id'],
                                                                'xrate' => removeCommas($value['H']),
                                                                'author'=>$this->session->userdata('userid')
                                                            );


                                                            $CP = $this->contract_price_m->create($contract_price);


                                                            //save variations
                                                            if($CP){
                                                                //DO THIS FOR ONLY VARIED CONTRACTS
                                                                if($value['J']&&$value['K']&&$value['L']){
                                                                    $variationInfo = array
                                                                    (
                                                                        'contractid' => $result,

                                                                        'initial_completion_date' => $value['E'] ? database_ready_format($value['E']) : '',

                                                                        'duration' => strtolower($value['J'])=='decrement'?'-':''.$value['J'],
                                                                        'new_planned_date_of_completion' => $value['L'] ? database_ready_format($value['L']) : '',
                                                                        'details' => $value['M'],
                                                                        'author'=>$this->session->userdata('userid')
                                                                    );


                                                                    $CV= $this->contract_variation_m->create($variationInfo);

                                                                    //there is a price variation
                                                                    if($value['P']!==$value['R']){
                                                                        $CVP= array
                                                                        (
                                                                            'contract_variation_id' => $CV,
                                                                            'amount' => ($value['R']-$value['P']),
                                                                            'price_variation_type' => ($value['R']-$value['P'])<0?'NEGATIVE':'POSITIVE',

                                                                            'author'=>$this->session->userdata('userid')
                                                                        );


                                                                        $PV= $this->contract_variation_price_m->create($CVP);


                                                                    }

                                                                    $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was saved as a . <b>varied Contract</b> ';


                                                                }

                                                            }




                                                            $saved_records[] = $value;

                                                        }

                                                    }else{
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> Duplicate record';

                                                    }

                                                } else {
                                                    //if estimated amount is skipped
                                                    if (!$result_array['Sheet1'][$key]['A']) {
                                                        //if NO FY
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no financial year';


                                                    }

                                                    //IF REF NO
                                                    if (!$result_array['Sheet1'][$key]['B']) {
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No procurement reference number';


                                                    }

                                                    if (!$result_array['Sheet1'][$key]['C']) {
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No date signed';


                                                    }

                                                    if (!$result_array['Sheet1'][$key]['D']) {
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No planed commencement date';


                                                    }

                                                    if (!$result_array['Sheet1'][$key]['E']) {
                                                        $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> No planned contract completion date';


                                                    }
                                                }

                                            } else {
                                                //if contract entries to not correspond to beb
                                                $activityLog[] = 'Entry  Record No <b>' . $key . '</b> was skipped. <b>Issue: </b> It  has no corresponding Best Evaluated Bidder ';

                                            }

                                        }

                                    }

                                    //if upload successful and there are no errors
                                    if (!count($errors)) {
                                        $data['success_4'] = 'Template successfully saved';
                                    }


//                                   print_array($entries);

                                }

                            }
                            break;


                        default:
                            redirect(current_url());
                    }

                } else {
                    //if its step 1
                    //ensure pde is selected
                    if (!$_POST['financial_year']) {
                        $errors[] = 'Select a financial year';

                    }

                    $upload_folder = 'uploads';
                    $config['upload_path'] = './' . $upload_folder . '/';
                    $config['allowed_types'] = 'xlsx|xls';
                    $new_name = time() . '_' . $_FILES["userfile"]['name'];
                    $config['file_name'] = $new_name;

                    $this->load->library('upload', $config);

                    //if file was not uploaded
                    if (!$this->upload->do_upload()) {

                        $errors[] = $this->upload->display_errors();

                    } else {
                        //if there is a file uploaded
                        //grab file url
                        $fileUrl = $upload_folder . '/' . $new_name;
                        $result_array = read_excel_data($fileUrl);


                        // if wrong template is uploaded
                        if (strtolower($result_array['procurementplan'][1]['B']) !== strtolower('Offline Procurement Plan template')) {
                            $errors[] = 'Make sure the uploaded template is of type: <b>Offline Procurement Plan template</b>';
                        }


                        //if a template id not provided
                        if (!$result_array['procurementplan'][2]['B']) {
                            $errors[] = 'Provide a unique number in the <b>Template ID</b> slot <p>The template ID helps to uniquely bind the template to all its entries across the offline cycle</p>';

                        }

                        //prevent duplication of template records
                        $entries = $this->offline_m->get_records_by_template($result_array['procurementplan'][2]['B'], $this->input->post('financial_year'), $this->input->post('pde'));

                        //IF NOT FIRST UPLOAD OF THE SAME TEMPLATE
                        if ($entries) {
                            //TODO COME BACK TO THIS
                            $activityLog[] = 'This is <b>NOT</b>  the first upload attempt for this template';


                        } else {
                            $activityLog[] = 'This is  the first upload attempt for this template';
                        }

                        $where = array(
                            'financial_year' => $this->input->post('financial_year'),
                            'pde_id' => $this->input->post('pde'),
                            'isactive' => 'y'
                        );

                        $procurement_plan = $this->procurement_plan_m->get_where($where);


                        //resolve for procurement plan id
                        if ($procurement_plan) {

                            $procurement_plan_id = $procurement_plan[0]['id'];

                            $activityLog[] = 'Entries to be added to procurement plan for financial year <b>' . $this->input->post('financial_year') . '</b> ';

                        } else {
                            $activityLog[] = 'No Procurement plan for financial year <b>' . $this->input->post('financial_year') . '</b> was detected. ';

                            $plan_data = array
                            (
                                'pde_id' => $this->input->post('pde'),
                                'financial_year' => $this->input->post('financial_year'),
                                'title' => '',
                                'author' => $this->session->userdata('userid'),
                                'description' => '',
                                'isactive' => 'y'

                            );


                            $procurement_plan_id = $this->procurement_plan_m->create($plan_data);
                            $activityLog[] = 'Procurement plan for financial year <b>' . $this->input->post('financial_year') . '</b> was created';


                        }

                        //array to save records deteted

                        $saved_records = array();
                        $skipped_records = array();

                        $template_id = $result_array['procurementplan'][2]['B'];


                        foreach ($result_array['procurementplan'] as $key => $value) {
                            if ($key >= 8) {

                                // filter out authentic records
                                if (is_numeric($value['A']) && $value['B'] && $value['C']) {


                                    //grab all the data for inserttion

                                    //don not duplicate records
                                    $where = array(
                                        'template_id' => $template_id,
                                        'template_record' => $value['A'],
                                        'procurement_plan_id' => $procurement_plan_id
                                    );

                                    if (!$this->procurement_plan_entry_m->get_where($where)) {
                                        switch (strtolower($value['C'])) {
                                            case 'Quantifiable Procurement':
                                                $revenue = 'N';
                                                $quantifiable = 'Y';
                                                break;
                                            case 'Non-Quantifiable Procurement':
                                                $revenue = 'N';
                                                $quantifiable = 'N';
                                                break;
                                            case 'Revenue':
                                                $revenue = 'Y';
                                                $quantifiable = 'N';
                                                break;
                                            default:
                                                $revenue = 'N';
                                                $quantifiable = 'Y';


                                        }

                                        //if quantifiable and yet no quantity provided
                                        if (!$value['D'] && $quantifiable == 'Y') {
                                            $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was saved but with issues. <b>Issue: </b> It is a quantifiable entry but no quantity was provided';

                                        }

                                        if (!$value['E']) {
                                            $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was saved but with issues. <b>Issue: </b> No responsible department was provided';

                                        }

                                        if (!$value['F']) {
                                            $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was saved but with issues. <b>Issue: </b> Type of procurement was not provided';

                                        }

                                        if (!$value['G']) {
                                            $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was saved but with issues. <b>Issue: </b> A estimated value was not provided';

                                        }

                                        if (!$value['I']) {
                                            $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was saved with issues. <b>Issue: </b> Currency was not selected. UGX default assumed';

                                        }

                                        $type = $this->procurement_type_m->custom_query('SELECT title FROM procurement_types WHERE title LIKE "%' . $value['F'] . '%"');
                                        $currency = $this->procurement_type_m->custom_query('SELECT id FROM currencies WHERE title LIKE "%' . $value['I'] . '%"');
                                        $funding_source = $this->procurement_type_m->custom_query('SELECT id FROM funding_sources WHERE title LIKE "%' . $value['J'] . '%"');
                                        $procurement_method = $this->procurement_type_m->custom_query('SELECT id FROM procurement_methods WHERE title LIKE "%' . $value['K'] . '%"');

                                        if (!$procurement_method) {
                                            $activityLog[] = 'Entry with Record No <b>' . $key . '</b> was saved with issues. <b>Issue: </b> Could not resolve its procurement method.';

                                        }


                                        $procurement_plan_data = array
                                        (
                                            'subject_of_procurement' => $value['B'],
                                            'quantity' => removeCommas($value['D']),
                                            'pde_department' => $value['E'],
                                            'procurement_type' => $type[0]['title'],
                                            'estimated_amount' => removeCommas($value['G']),
                                            'exchange_rate' => removeCommas($value['H']),
                                            'currency' => $value['I'] ? $currency[0]['id'] : 1,
                                            'funding_source' => $value['J'] ? $funding_source[0]['id'] : '',
                                            'procurement_method' => count($procurement_method) ? $procurement_method[0]['id'] : '',
                                            'contracts_committee_approval_date' => $value['M'] ? database_ready_format($value['M']) : '',
                                            'contracts_committee_approval_of_shortlist_date' => $value['N'] ? database_ready_format($value['N']) : '',
                                            'publication_of_pre_qualification_date' => $value['O'] ? database_ready_format($value['O']) : '',
                                            'proposal_submission_date' => $value['P'] ? database_ready_format($value['P']) : '',
                                            'invitation_of_expression_of_interest' => $value['Q'] ? database_ready_format($value['Q']) : '',
                                            'closing_date' => $value['R'] ? database_ready_format($value['R']) : '',
                                            'approval_of_shortlist' => $value['S'] ? database_ready_format($value['S']) : '',
                                            'notification_date' => $value['T'] ? database_ready_format($value['T']) : '',
                                            'bid_issue_date' => $value['U'] ? database_ready_format($value['U']) : '',
                                            'bid_closing_date' => $value['V'] ? database_ready_format($value['V']) : '',
                                            'submission_of_evaluation_report_to_cc' => $value['W'] ? database_ready_format($value['W']) : '',
                                            'cc_approval_of_evaluation_report' => $value['X'] ? database_ready_format($value['X']) : '',
                                            'negotiation_date' => $value['Y'] ? database_ready_format($value['Y']) : '',
                                            'negotiation_approval_date' => $value['Z'] ? database_ready_format($value['Z']) : '',
                                            'best_evaluated_bidder_date' => $value['AA'] ? database_ready_format($value['AA']) : '',
                                            'performance_security' => $value['AB'] ? database_ready_format($value['AB']) : '',
                                            'solicitor_general_approval_date' => $value['AC'] ? database_ready_format($value['AC']) : '',
                                            'accounting_officer_approval_date' => $value['L'] ? database_ready_format($value['L']) : '',
                                            'contract_award' => $value['AD'] ? database_ready_format($value['AD']) : '',
                                            'contract_sign_date' => $value['AE'] ? database_ready_format($value['AE']) : '',
                                            'opening_of_credit_letter' => $value['AF'] ? database_ready_format($value['AF']) : '',
                                            'arrival_of_goods' => $value['AG'] ? database_ready_format($value['AG']) : '',
                                            'inspection_final_acceptance' => $value['AH'] ? database_ready_format($value['AH']) : '',
                                            'draft_report' => $value['AI'] ? database_ready_format($value['AH']) : '',
                                            'final_report' => $value['AJ'] ? database_ready_format($value['AJ']) : '',
                                            'substantial_completion' => $value['AK'] ? database_ready_format($value['AK']) : '',
                                            'final_acceptance' => $value['AL'] ? database_ready_format($value['AL']) : '',
                                            'procurement_plan_id' => $procurement_plan_id,
                                            'framework' => 'N',
                                            'mandatory' => 'N',
                                            'revenue' => $revenue,
                                            'quantifiable' => $quantifiable,
                                            'template_id' => $template_id,
                                            'template_record' => $value['A']
                                        );


                                        $procurement_plan_data['author'] = $this->session->userdata('userid');

                                        $result = $this->procurement_plan_entry_m->create($procurement_plan_data);


                                        if ($result) {
                                            $saved_records[] = $value;

                                        }


                                    } else {
                                        $skipped_records[] = $value;
                                    }


                                }


                            }

                        }

                        $activityLog[] = '<b>' . count($saved_records) . '</b> records have been added ';

                        $activityLog[] = '<b>' . count($skipped_records) . '</b> records have been skipped (DUPLICATE ENTRIES)';


                    }// end procurement plan upload


                    //if upload successful and there are no errors
                    if (!count($errors)) {
                        $data['success_1'] = 'Template successfully saved';
                    }
                }

                $data['errors'] = $errors;
                $data['activityLog'] = $activityLog;

                if (isset($fileUrl)) {
                    //set up the next step
                    unlink($fileUrl);
                }


            }


            $this->load->view('dashboard_v', $data);

        } else {
            //load access denied page
            load_restriction_page();

        }

    }


}
