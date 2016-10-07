<?php 

#**************************************************************************************
# All rdport actions directed from this controller
#**************************************************************************************

class Reports extends CI_Controller {






	
	# Constructor
    var $financial_years = array(array('fy' => '2017-2018', 'label' => '2017 - 2018'),
        array('fy' => '2016-2017', 'label' => '2016 - 2017'),
        array('fy' => '2015-2016', 'label' => '2015 - 2016'),
        array('fy' => '2014-2015', 'label' => '2014 - 2015'),
        array('fy' => '2013-2014', 'label' => '2013 - 2014'),
        array('fy' => '2012-2013', 'label' => '2012 - 2013'));

    function Reports()
    {
		parent::__construct();

		$this->load->model('users_m','users');
		$this->load->model('sys_email','sysemail');
		#date_default_timezone_set(SYS_TIMEZONE);
		$this->load->model('contracts_m');
		$this->load->model('bid_invitation_m');
        $this->load->model('receipts_m');
        $this->load->model('disposal_m');
        $this->load->model('disposal_record_m');
        $this->load->model('remoteapi_m');
        $this->load->model('procurement_plan_m');
        $this->load->model('contracts_m');
        $this->load->model('contract_price_m');



		access_control($this);
	}
	
	#Default

	function index()
	{
		#do nothing..
	}
	
	#Report panel
	function report_panel()
	{


		check_user_access($this, 'view_reports', 'redirect');
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$data = add_msg_if_any($this, $data);
		
		$data = handle_redirected_msgs($this, $data);
			
		$data['page_title'] = 'Report panel';
		$data['current_menu'] = 'view_reports';
		$data['view_to_load'] = 'reports/report_panel';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['search_url'] = '';
        
		$this->load->view('dashboard_v', $data);
	}
	
	
	function procurement_plan_reports()
	{
		check_user_access($this, 'view_reports', 'redirect');
		
		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
		
		# Pick all assigned data
		$data = assign_to_data($urldata);
		
		$data = add_msg_if_any($this, $data);
		
		$data = handle_redirected_msgs($this, $data);
        $data['page_title'] = 'Procurement plan reports';


        $data['current_menu'] = 'procurement_plan_reports';
        $data['view_to_load'] = 'reports/procurement_plans/procurement_plans_reports_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = '';
        $data['report_form']='reports/procurement_plans/forms/procurement_plans_f';
        $data['report_view']='reports/procurement_plans/procurement_plans_home';


        //if form is posted
		if($this->input->post('view'))
		{
            //print_array($_POST);
            $data['financial_year']=$this->input->post('financial_year');
            $where['isactive']='y';
            if($this->input->post('financial_year')){
                $where['financial_year']=$this->input->post('financial_year');
            }


            if($this->session->userdata('isadmin')=='Y'){
                if($this->input->post('pde')){
                    $where['pde_id']=$this->input->post('pde');
                }



            }else{
                //if pde is logged in get for current pde
                $where['pde_id']=$this->session->userdata('pdeid');

            }

            //run query
            $data['results']=$this->procurement_plan_m->get_where($where);



		}else{
            //if form is not posted
            $data['financial_year']=date('Y').'-'.(date('Y')+1);

            //if super admin get all pdes
            if($this->session->userdata('isadmin')=='Y'){
                $where=array(
                    'isactive' => 'y',
                    'financial_year' => date('Y').'-'.(date('Y')+1)

                );

            }else{
                //if pde is logged in get for current pde
                $where=array(
                    'isactive' => 'y',
                    'pde_id' => $this->session->userdata('pdeid'),
                    'financial_year' => date('Y').'-'.(date('Y')+1)

                );

            }



            //run query
            $data['results']=$this->procurement_plan_m->get_where($where);


        }





        $this->load->view('dashboard_v', $data);
	}

    function report_to_pdf($html, $report_title = 'PPDA_report')
	{
        // Load library
        $this->load->library('dompdf_gen');

        // Convert to PDF
        $this->dompdf->load_html($html);
        $this->dompdf->render();
        $this->dompdf->stream($report_title . ".pdf", array("Attachment" => false));
	}

    function invitation_for_bids_reports()
	{
		check_user_access($this, 'view_reports', 'redirect');

		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

		# Pick all assigned data
		$data = assign_to_data($urldata);

		$data = add_msg_if_any($this, $data);

		$data = handle_redirected_msgs($this, $data);

        $data['financial_year'] = $data['financial_year']=date('Y').'-'.(date('Y')+1);
        $data['page_title'] = 'Invitation for bids reports';
        $data['current_menu'] = 'invitation_for_bid_reports';
        $data['view_to_load'] = 'reports/ifb/invitation_for_bids_reports_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['report_heading'] = 'PUBLISHED INVITATION FOR BIDS';
        $from=date('Y').'-'.date('m').'-01';
        $to=mysqldate();
        $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';


        $data['report_form']='reports/ifb/forms/ifb_f';
        $data['report_view']='reports/ifb/ifb_home';


        if($this->input->post('view')){

          //if financial year is selected
            if($this->input->post('financial_year')){
                $where['financial_year']=$this->input->post('financial_year');
            }


            if($this->session->userdata('isadmin')=='Y'){
                if($this->input->post('pde')){
                    $where['pde_id']=$this->input->post('pde');
                }



            }

            //switch by report type
            switch($this->input->post('ifb_report_type')){
                case 'PIFB':

                    $data['report_heading'] = 'PUBLISHED INVITATION FOR BIDS';
                    $data['financial_year']=$this->input->post('financial_year');
                    $from=$this->input->post('ifb_from_date');
                    $to=$this->input->post('ifb_to_date');
                    $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from) . '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';




                    //if its supper admin logged in
                    if($this->session->userdata('isadmin')=='Y'){

                        //when filtering by pde
                        if($this->input->post('pde')){

                            $pde=$this->input->post('pde');
                            $data['report_heading'] = 'PUBLISHED INVITATION FOR BIDS <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';

                            $data['results']=$this->bid_invitation_m->get_invitation_for_bids_by_month($from,$to,$pde);
                        }else{

                            $data['results']=$this->bid_invitation_m->get_invitation_for_bids_by_month($from,$to);
                        }


                    }else{

                        //if pde filer results by pde
                        $pde=$this->session->userdata('pdeid');
                        $data['report_heading'] = 'PUBLISHED INVITATION FOR BIDS <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                        $data['results']=$this->bid_invitation_m->get_invitation_for_bids_by_month($from,$to,$pde);

                    }

                    break;
                case 'IFBD':

                    $data['report_heading'] = 'BID SUBMISSION DEADLINES';
                    $data['financial_year']=$this->input->post('financial_year');
                    $from=$this->input->post('bsd_from_date');
                    $to=$this->input->post('bsd_to_date');
                    $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from) . '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';





                    //if its supper admin logged in
                    if($this->session->userdata('isadmin')=='Y'){

                        if($this->input->post('pde')){
                            $pde=$this->input->post('pde');

                            $data['report_heading'] = 'BID SUBMISSION DEADLINES<small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                            $data['results']=$this->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to,$pde);
                        }else{
                            $data['results']=$this->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to);
                        }


                    }else{

                        //if pde filer results by pde
                        $pde=$this->session->userdata('pdeid');
                        $data['report_heading'] = 'BID SUBMISSION DEADLINES<small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                        $data['results']=$this->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to,$pde);

                    }

                    break;

                case 'BER':

                    //print_array($_POST);



                    $data['financial_year']=$this->input->post('financial_year');
                    $from=$this->input->post('ber_from_date');
                    $to=$this->input->post('ber_to_date');

                    $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from) . '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';

                    switch($this->input->post('exception_type')){
                        case 'GTT':
                            $data['report_heading'] = 'EXCEPTION REPORT | BIDS GREATER THAN BIDDING PERIOD DEADLINE';






                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){

                                //when filtering by pde
                                if($this->input->post('pde')){

                                    $pde=$this->input->post('pde');
                                    $data['results']=$this->bid_invitation_m->get_bids_above_threshhold($from,$to,$pde);
                                }else{

                                    $data['results']=$this->bid_invitation_m->get_bids_above_threshhold($from,$to);
                                }


                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = 'EXCEPTION REPORT | BIDS GREATER THAN BIDDING PERIOD DEADLINE <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                                $data['results']=$this->bid_invitation_m->get_bids_above_threshhold($from,$to,$pde);

                            }



                            break;
                        case 'LTT':
                            $data['report_heading'] = 'BID EXCEPTION REPORT | BIDS LESS THAN BIDDING PERIOD DEADLINE';


                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){

                                //when filtering by pde
                                if($this->input->post('pde')){

                                    $pde=$this->input->post('pde');
                                    $data['results']=$this->bid_invitation_m->get_bids_below_threshhold($from,$to,$pde);
                                }else{

                                    $data['results']=$this->bid_invitation_m->get_bids_below_threshhold($from,$to);
                                }



                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = 'BID EXCEPTION REPORT | BIDS LESS THAN BIDDING PERIOD DEADLINE <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                                $data['results']=$this->bid_invitation_m->get_bids_below_threshhold($from,$to,$pde);

                            }
                            break;
                        case 'ETT':


                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){

                                //when filtering by pde
                                if($this->input->post('pde')){

                                    $pde=$this->input->post('pde');
                                    $data['results']=$this->bid_invitation_m->get_bids_below_threshhold($from,$to,$pde);
                                }else{

                                    $data['results']=$this->bid_invitation_m->get_bids_below_threshhold($from,$to);
                                }



                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = 'BID EXCEPTION REPORT | BIDS ON TIME <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                                $data['results']=$this->bid_invitation_m->get_bids_equal_to_threshhold($from,$to,$pde);

                            }
                            break;
                        case 'DTC':
                            //print_array($_POST);

                            $data['report_heading'] = 'BID DUE TO CLOSE NEXT WEEK';

                            $from=mysqldate();
                            $to=date('d-M-Y',strtotime(mysqldate())+604800) ;
                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from) . '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';





                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){

                                //when filtering by pde
                                if($this->input->post('pde')){
                                    $pde=$this->input->post('pde');

                                    $data['results']=$this->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to,$pde);
                                }else{
                                    $data['results']=$this->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to);
                                }



                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = 'BID DUE TO CLOSE NEXT WEEK <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                                $data['results']=$this->bid_invitation_m->get_bid_submission_deadlines_by_month($from,$to,$pde);;

                            }

                            break;
                        default:

                    }

                    break;



            }

        }else{
            //by default
            if($this->session->userdata('isadmin')=='N'){
                $data['report_heading'] = 'PUBLISHED INVITATION FOR BIDS <small>Filtered by PDE: '.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</small>';
                //if pde is logged in get for current pde
                $pde=$this->session->userdata('pdeid');
                $data['results']=$this->bid_invitation_m->get_invitation_for_bids_by_month($from,$to,$pde);

            }else{
                $data['results']=$this->bid_invitation_m->get_invitation_for_bids_by_month($from,$to);
            }







        }





        //print_array($this->db->last_query());
        $data['last_query']=$this->db->last_query();



		$this->load->view('dashboard_v', $data);
	}

    function best_evaluated_bidder_reports()
	{
		check_user_access($this, 'view_reports', 'redirect');

		# Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

		# Pick all assigned data
		$data = assign_to_data($urldata);

		$data = add_msg_if_any($this, $data);

		$data = handle_redirected_msgs($this, $data);

        $data['financial_years'] = $this->financial_years;

        //if online get suspended providers
        if(check_live_server()){
            $data['rop_suspended_providers'] = $this->remoteapi_m->providers_suspended();
        }

        $data['page_title'] = 'Best evaluated bidder reports';
        $data['current_menu'] = 'best_evaluated_bidder_reports';
        $data['view_to_load'] = 'reports/beb/beb_report_v';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = '';
        $data['report_form']='reports/beb/forms/beb_f';
        $data['report_view']='reports/beb/beb_home';

        $data['report_heading'] = 'BEST EVALUATED BIDDERS';
        $data['financial_year'] = $data['financial_year']=date('Y').'-'.(date('Y')+1);
        $from=date('Y').'-'.date('m').'-01';
        //$from=date('Y').'-'.'01'.'-01';
        $to=mysqldate();
        $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';

        $this->db->order_by("pdename", "asc");
        $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();
        $data['pdes'] = array_merge(array(array('pdeid'=>'ALL', 'pdename'=>'View All')), $data['pdes']);




        if($this->input->post('view')) {
            $data['financial_year']=$this->input->post('financial_year');

            switch($this->input->post('beb_report_type')){
                case 'PBEB':
                    $data['financial_year']=$this->input->post('financial_year');
                    $from=$this->input->post('beb_publish_from_date');
                    $to=$this->input->post('beb_publish_to_date');

                    $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from) . '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';

                    $data['report_heading'] = 'PUBLISHED BEST EVALUATED BIDDERS';







                    //if its supper admin logged in
                    if($this->session->userdata('isadmin')=='Y'){

                        //when filtering by pde
                        if($this->input->post('pde')){
                            $data['report_heading'] = 'PUBLISHED BEST EVALUATED BIDDERS <small>Filtered by PDE : '.get_pde_info_by_id($this->input->post('pde'),'title').'</small>';

                            $pde=$this->input->post('pde');
                            $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to,$pde);
                        }else{

                            $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to);
                        }

                    }else{

                        //if pde filer results by pde
                        $pde=$this->session->userdata('pdeid');
                        $data['report_heading'] = 'PUBLISHED BEST EVALUATED BIDDERS <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                        $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to,$pde);

                    }





                    break;


                case 'EBN':

                    $data['financial_year']=$this->input->post('financial_year');
                    $from=$this->input->post('beb_publish_from_date');
                    $to=$this->input->post('beb_publish_to_date');

                    $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from) . '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';

                    $data['report_heading'] = 'EXPIRED BEST EVALUATED BIDDER NOTICES';






                    //if its supper admin logged in
                    if($this->session->userdata('isadmin')=='Y'){

                        if($this->input->post('pde')){
                            $data['report_heading'] = 'EXPIRED BEST EVALUATED BIDDER NOTICES <small>Filtered by PDE : '.get_pde_info_by_id($this->input->post('pde'),'title').'</small>';

                            $pde=$this->input->post('pde');
                            $data['results']=$this->bid_invitation_m->get_expired_bids_by_month($from,$to,$pde);
                        }else{

                            $data['results']=$this->bid_invitation_m->get_expired_bids_by_month($from,$to);
                        }


                    }else{

                        //if pde filer results by pde
                        $pde=$this->session->userdata('pdeid');
                        $data['report_heading'] = 'EXPIRED BEST EVALUATED BIDDER NOTICES <small>Filtered by PDE : '.get_pde_info_by_id($pde,'title').'</small>';


                        $data['results']=$this->bid_invitation_m->get_expired_bids_by_month($from,$to,$pde);

                    }





                    break;
                default:


                    //if its supper admin logged in
                    if($this->session->userdata('isadmin')=='Y'){
                        $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to);


                    }else{

                        //if pde filer results by pde
                        $pde=$this->session->userdata('pdeid');
                        $data['report_heading'] = 'BEST EVALUATED BIDDERS: '.get_pde_info_by_id($pde,'title').'</small>';

                        $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to,$pde);

                    }

            }



		}else{






            //if its supper admin logged in
            if($this->session->userdata('isadmin')=='Y'){
                $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to);


            }else{

                //if pde filer results by pde
                $pde=$this->session->userdata('pdeid');
                $data['report_heading'] = 'BEST EVALUATED BIDDERS: '.get_pde_info_by_id($pde,'title').'</small>';

                $data['results']=$this->bid_invitation_m->get_published_bids_by_month($from,$to,$pde);

            }






        }








        //load view

        $this->load->view('dashboard_v', $data);


	}

    function contract_award_reports()
	{
		check_user_access($this, 'view_reports', 'redirect');

        # Get the passed details into the url data array if any
		$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
		$data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);

        $search_str = '';
        $where_arr = array();

        $data['sub_heading'] = '';

        #if NOT Admin..
        if ($this->session->userdata('isadmin') == 'N') {
            $userdata = $this->db->get_where('users', array('userid' => $this->session->userdata('userid')))->result_array();
            $where_arr = array_merge($where_arr, array('pdeid' => $userdata[0]['pde']));
            $search_str = ' AND PP.pde_id ="' . $userdata[0]['pde'] . '" ';

            $pde_info_result = $this->db->get_where('pdes', array('pdeid' => $userdata[0]['pde']))->result_array();
            $data['sub_heading'] = $pde_info_result[0]['pdename'];
        }

        $where_arr = array_merge($where_arr, array('isactive' => 'Y', 'status' => 'in'));

        $this->db->order_by("pdename", "asc");
        $data['pdes'] = $this->db->get_where('pdes', $where_arr)->result_array();
        $data['pdes'] = array_merge(array(array('pdeid' => 'ALL', 'pdename' => 'View All')), $data['pdes']);

        $data['financial_years'] = $this->financial_years;

        $data['page_title'] = 'Contracts due this month';
        $data['current_menu'] = 'contract_award_reports';
        $data['view_to_load'] = 'reports/contract_award/contract_award_reports_v';
		$data['view_data']['form_title'] = $data['page_title'];
		$data['search_url'] = '';
        $data['report_heading'] = 'Contracts due to completion in '.date('F');
        $data['financial_year'] = $data['financial_year']=date('Y').'-'.(date('Y')+1);

        $data['report_form']='reports/contract_award/forms/contract_award_f';
        $data['report_view']='reports/contract_award/contract_award_home';
        $month=date('m',now());
        $year=date('y',now());


        $from=database_ready_format(date("m/d/y", mktime(0, 0, 0, $month, 01, $year)));
        $to=database_ready_format(date("m/d/y", mktime(0, 0, 0, $month, 30, $year)));

        if($this->input->post('view')){
           //switch by report type
            switch($this->input->post('contracts_report_type')){
                case 'AC':
                    //print_array($_POST);
                    $from=database_ready_format($this->input->post('contract_award_from_date'));
                    $to= database_ready_format($this->input->post('contract_award_to_date'));

                    $data['results']=$this->contracts_m->get_signed_contracts_by_financial_year($from,$to,$pde='');

                   // print_array($data['results']);

                    break;
            }
        }

        $data['reporting_period']=custom_date_format('d M, Y', $from).' - '.custom_date_format('d M, Y', $to);

        if($this->session->userdata('isadmin')=='Y'){
            $data['results']=$this->contracts_m->get_contracts_due_by_month($from,$to,$pde='');

        }else{
            $pde=$this->session->userdata('pdeid');
            $data['results']=$this->contracts_m->get_contracts_due_by_month($from,$to,$pde);
        }

        $this->load->view('dashboard_v', $data);
	}
	
	
	#generate pdf report

    function view_report()
    {
        check_user_access($this, 'view_reports', 'redirect');

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $data = handle_redirected_msgs($this, $data);


        $data['page_title'] = 'Report panel';
        $data['current_menu'] = 'view_reports';
        $data['view_to_load'] = 'reports/report_panel';
        $data['view_data']['form_title'] = $data['page_title'];
        $data['search_url'] = '';

        $this->load->view('dashboard_v', $data);
	}
	
	
	//ppms related reports

    function ppms()
    {
        check_user_access($this, 'ppms_reports', 'redirect');


            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();

            $data['pdes'] = array_merge(array(array('pdeid' => 'ALL', 'pdename' => 'View All')), $data['pdes']);

            $data['financial_years'] = $this->financial_years;

            $data['page_title'] = 'PPMS reports';
            $data['current_menu'] = 'ppms_reports';
            $data['view_to_load'] = 'reports/ppms/ppms_reports_v';
            $data['view_data']['form_title'] = $data['page_title'];
            $data['search_url'] = '';

            $data['report_form']='reports/ppms/forms/ppms_f';
            $data['report_view']='reports/ppms/ppms_home';


        $data['financial_year'] = $data['financial_year']=date('Y').'-'.(date('Y')+1);
        $from=date('Y').'-'.'06'.'-30';//Fiscal year: 1 July - 30 June
        //$from=date('Y').'-'.'06'.'-30';
        $to=(date('Y')+1).'-'.'06'.'-30';
        $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';

        $data['report_heading'] = 'COMPLETED STATUSES FINANCIAL YEAR  '.date('Y').'-'.(date('Y')+1);



        //if form is posted
        if($this->input->post('generate_ppms')){
            //get_time_lines_of_contract_completion($from,$to,$pde='')
           // print_array($_POST);
            $data['report_type']=$this->input->post('report_type');

            //financial year is passed
            if($this->input->post('financial_year')){

                $start_year=substr($this->input->post('financial_year'),0,4);

                $end_year=substr($this->input->post('financial_year'),5,4);

                $from=$start_year.'-'.'06'.'-30';//Fiscal year: 1 July - 30 June
                $to=$end_year.'-'.'06'.'-30';

                $data['from']=$from;
                $data['to']=$to;

                $config = array(
                    array(
                        'field'   => 'report_type',
                        'label'   => 'Report type',
                        'rules'   => 'required'
                    ),

                );
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE)
                {
                    $data['errors']=validation_errors();
                }
                else
                {

                    switch($this->input->post('report_type')){
                        case 'timeliness_of_contract_completion':
                            //graph view
                            $data['graph_view']='reports/ppms/graphs/signed_contracts_g';

                            $data['page_title']='Timeliness of contract completion ';




                    $data['report_heading'] = $data['page_title'];
                    $data['financial_year'] = $data['financial_year']=$start_year.'-'.$end_year;
                  
                    $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';


                            //get all contracts to mark against time lines
                            if($this->session->userdata('isadmin')=='Y'){
                                $data['all_contracts_in_this_year']=$this->contracts_m->get_contracts_by_financial_year($from,$to);

                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = strtoupper(get_pde_info_by_id($pde,'title')).' COMPLETED CONTRACTS <small>Filtered by PDE';

                                $data['all_contracts_in_this_year']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde);
                            }



                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){
                                if($this->input->post('pde')){
                                    $data['results']=$this->contracts_m->get_timeliness_of_contract_completion($from,$to,$this->input->post('pde'));

                                }else  {
                                    # code...
                                    $data['results']=$this->contracts_m->get_timeliness_of_contract_completion($from,$to,$pde='');
                                }


                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = ''.strtoupper(get_pde_info_by_id($pde,'title')).'TIMELINESS FOR CONTRACT COMPLETION ';

                                $data['results']=$this->contracts_m->get_timeliness_of_contract_completion($from,$to,$pde);

                            }

                            break;


                        case 'procurement_lead_time_report':
                            $data['page_title']='PROCUREMENT LEAD TIME BY PROCUREMENT METHOD ';


                            $from=$start_year.'-'.'06'.'-30';//Fiscal year: 1 July - 30 June
                            $to=$end_year.'-'.'06'.'-30';

                            $data['from']=$from;
                            $data['to']=$to;



                            $data['report_heading'] = $data['page_title'];
                            $data['financial_year'] = $data['financial_year']=$start_year.'-'.$end_year;

                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';
                            //get_contracts_by_financial_year($from,$to,$pde='')


                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){
                                if($this->input->post('pde')){
                                    $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$this->input->post('pde'));


                                }else  {
                                    # code...
                                    $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde='');

                                }


                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = strtoupper(get_pde_info_by_id($pde,'title')).' PROCUREMENT LEAD TIME BY PROCUREMENT METHOD ';

                                $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde);

                            }

                            break;

                        case 'contracts_completed_within_original_value':
                            $data['page_title']='CONTRACTS COMPLETED WITHIN ORIGINAL VALUE ';
                            //graph view
                            $data['graph_view']='reports/ppms/graphs/signed_contracts_g';

                            $from=$start_year.'-'.'06'.'-30';//Fiscal year: 1 July - 30 June
                            $to=$end_year.'-'.'06'.'-30';

                            $data['report_heading'] = $data['page_title'];
                            $data['financial_year'] = $data['financial_year']=$start_year.'-'.$end_year;

                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';
                            //get_contracts_by_financial_year($from,$to,$pde='')

                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){
                                $data['all_contracts_in_this_year']=$this->contracts_m->get_contracts_by_financial_year($from,$to);

                                if($this->input->post('pde')){
                                    $data['results']=$this->contracts_m->get_contracts_completed_within_original_value($from,$to,$this->input->post('pde'));

                                }else  {
                                    # code...
                                    $data['results']=$this->contracts_m->get_contracts_completed_within_original_value($from,$to,$pde='');
                                }


                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = ucwords(get_pde_info_by_id($pde,'title')). 'CONTRACTS COMPLETED WITHIN ORIGINAL VALUE ';

                                $data['results']=$this->contracts_m->get_contracts_completed_within_original_value($from,$to,$pde);
                                $data['all_contracts_in_this_year']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde);

                            }






                            break;

                        case 'average_number_of_bids_per_contract':
                            $data['page_title']='Average number of bids per contract Financial year '.$this->input->post('financial_year');
                            $from=$start_year.'-'.'06'.'-30';//Fiscal year: 1 July - 30 June
                            $to=$end_year.'-'.'06'.'-30';

                            $data['report_heading'] = $data['page_title'];
                            $data['financial_year'] = $data['financial_year']=$start_year.'-'.$end_year;

                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';





                            //if its supper admin logged in
                            if($this->session->userdata('isadmin')=='Y'){

                                if($this->input->post('pde')){
                                    $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$this->input->post('pde'));
                                    $data['all_contracts_in_this_year']=$data['results'];

                                }else  {
                                    # code...
                                    $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde='');
                                    $data['all_contracts_in_this_year']=$data['results'];
                                }

                            }else{

                                //if pde filer results by pde
                                $pde=$this->session->userdata('pdeid');
                                $data['report_heading'] = strtoupper(get_pde_info_by_id($pde,'title')). ' AVERAGE NUMBER OF BIDS PER CONTRACT ';

                                $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde);
                                $data['all_contracts_in_this_year']=$data['results'];

                            }

                            break;




                    }
                }







            }


            //print_array($this->db->last_query());


        }else
        {

            //default reporting period
            $data['from']=$from;
            $data['to']=$to;

            //if its supper admin logged in
            if($this->session->userdata('isadmin')=='Y'){
                $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to);
                $data['all_contracts_in_this_year']=$data['results'];

            }else{

                //if pde filer results by pde
                $pde=$this->session->userdata('pdeid');
                $data['report_heading'] = get_pde_info_by_id($pde,'title').' COMPLETED CONTRACTS ';

                $data['results']=$this->contracts_m->get_contracts_by_financial_year($from,$to,$pde);
                $data['all_contracts_in_this_year']=$data['results'];
            }

            //graph view
            $data['graph_view']='reports/ppms/graphs/signed_contracts_g';



            //$data['last_query']=$this->db->last_query();


        }




        $this->load->view('dashboard_v', $data);


    }

    //disposal related reports
    function disposal()
    {
        check_user_access($this, 'disposal_reports', 'redirect');

            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();

            $data['pdes'] = array_merge(array(array('pdeid' => 'ALL', 'pdename' => 'View All')), $data['pdes']);

            $data['financial_years'] = $this->financial_years;

            $data['page_title'] = 'Disposal reports';
            $data['current_menu'] = 'disposal_reports';
            $data['view_to_load'] = 'reports/disposal/disposal_reports_v';
            $data['view_data']['form_title'] = $data['page_title'];
            $data['search_url'] = '';
            $data['report_form']='reports/disposal/forms/disposal_f';
            $data['report_view']='reports/disposal/disposal_home';

        $data['report_heading'] = 'DISPOSAL REPORTS';
        $data['financial_year'] = $data['financial_year']=date('Y').'-'.(date('Y')+1);
        $from=date('Y').'-'.date('m').'-01';
        //$from=date('Y').'-'.'01'.'-01';
        $to=mysqldate();
        $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';



        if($this->input->post('generate')){


                $data['financial_year']=$this->input->post('financial_year');



                $start_year=substr($this->input->post('financial_year'),0,4);

                //$end_year=substr($this->input->post('financial_year'),5,4);

                $config = array(
                    array(
                        'field'   => 'report_type',
                        'label'   => 'Quarter',
                        'rules'   => 'required'
                    ),

                );
                $this->form_validation->set_rules($config);

                if ($this->form_validation->run() == FALSE)
                {
                    $data['errors']=validation_errors();
                }
                else
                {
                    //switch by quarter
                    switch($this->input->post('report_type')){
                        case 'quarter_1':
                            $from=date('Y-m-d',strtotime($start_year.'-01-01'));
                            $to=date('Y-m-d',strtotime($start_year.'-04-01'));

                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';


                            if($this->session->userdata('isadmin')=='Y'){
                                if($this->input->post('pde')){
                                    $data['report_heading'] = 'DISPOSAL REPORTS IN THE FIRST QUARTER <small>Filtered by PDE: '.get_pde_info_by_id($this->input->post('pde'),'title').'</small>';
                                    $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->input->post('pde'));

                                }else{
                                    $data['report_heading'] = 'DISPOSAL REPORTS IN THE FIRST QUARTER ';
                                    $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to);
                                }

                            }
                            else{
                                $data['report_heading'] = 'DISPOSAL REPORTS IN THE FIRST QUARTER <small>Filtered by PDE: '.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</small>';

                                $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->session->userdata('pdeid'));

                            }



                            break;
                        case 'quarter_2':

                            $from=date('Y-m-d',strtotime($start_year.'-5-01'));
                            $to=date('Y-m-d',strtotime($start_year.'-08-01'));
                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';


                            if($this->session->userdata('isadmin')=='Y'){
                                if($this->input->post('pde')){
                                    $data['report_heading'] = 'DISPOSAL REPORTS IN THE SECOND QUARTER <small>Filtered by PDE: '.get_pde_info_by_id($this->input->post('pde'),'title').'</small>';
                                    $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->input->post('pde'));

                                }else{
                                    $data['report_heading'] = 'DISPOSAL REPORTS IN THE SECOND QUARTER ';
                                    $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to);
                                }

                            }
                            else{
                                $data['report_heading'] = 'DISPOSAL REPORTS IN THE SECOND QUARTER <small>Filtered by PDE: '.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</small>';

                                $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->session->userdata('pdeid'));

                            }
                            break;
                        case 'quarter_3':
                            $from=date('Y-m-d',strtotime($start_year.'-06-01'));
                            $to=date('Y-m-d',strtotime($start_year.'-12-01'));

                            $data['reporting_period'] = '<b>'.custom_date_format('d M, Y', $from ). '</b> &nbsp<i> to </i> <b>  &nbsp &nbsp' . custom_date_format('d M, Y', $to).'</b>';


                            if($this->session->userdata('isadmin')=='Y'){
                                if($this->input->post('pde')){
                                    $data['report_heading'] = 'DISPOSAL REPORTS IN THE THIRD QUARTER <small>Filtered by PDE: '.get_pde_info_by_id($this->input->post('pde'),'title').'</small>';
                                    $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->input->post('pde'));

                                }else{
                                    $data['report_heading'] = 'DISPOSAL REPORTS IN THE THIRD QUARTER ';
                                    $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to);
                                }

                            }
                            else{
                                $data['report_heading'] = 'DISPOSAL REPORTS IN THE THIRD QUARTER <small>Filtered by PDE: '.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</small>';

                                $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->session->userdata('pdeid'));

                            }


                            break;



                    }
                }


        }else{

            if($this->session->userdata('isadmin')=='Y'){
                $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$pde='');
            }
            else{
                $data['report_heading'] = strtoupper(get_pde_info_by_id($this->session->userdata('pdeid'),'title')).' DISPOSAL REPORTS ';

                $data['results']=$this->disposal_record_m->get_disposal_plans_by_month($from,$to,$this->session->userdata('pdeid'));

            }


        }


            $this->load->view('dashboard_v', $data);


    }


    //ppms related reports
    function suspended_providers()
    {
        check_user_access($this, 'suspended_provider_reports', 'redirect');


            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();

            $data['pdes'] = array_merge(array(array('pdeid' => 'ALL', 'pdename' => 'View All')), $data['pdes']);

            $data['financial_years'] = $this->financial_years;

            $data['page_title'] = 'Best Evaluated Bids awarded to suspended providers '.date('Y').'-'.(date('Y')+1);
            $data['current_menu'] = 'suspended_provider_reports';
            $data['view_to_load'] = 'reports/suspended_providers/suspended_provider_reports_v';
            $data['view_data']['form_title'] = $data['page_title'];
            $data['search_url'] = '';
            $data['rop_suspended_providers'] = $this->remoteapi_m->providers_suspended();

            $data['report_form']='reports/suspended_providers/forms/suspended_providers_f';
            $data['report_view']='reports/suspended_providers/suspended_providers_home';


        if($this->input->post('generate_ppms')){

            //print_array($_POST);
            $data['page_title'] = 'Best Evaluated Bids awarded to suspended providers '.$this->input->post('financial_year');


                    $start_year=substr($this->input->post('financial_year'),0,4);

                    $end_year=substr($this->input->post('financial_year'),5,4);

                    $where = array(
                        'isactive' => 'Y',
                        'datereceived >=' => date('Y-m-d', strtotime($start_year . '-01-01')),
                        'datereceived <=' => date('Y-m-d', strtotime($end_year . '-01-01')),
                        'beb' => 'Y'
                    );

            $data['month'] = $this->input->post('month');






                //if there are where variables
                if(isset($where)){

                    $data['results']= $this->receipts_m->get_where($where);

                }







            //print_array($this->db->last_query());


        }else {

            $data['month'] = custom_date_format('M',mysqldate());



            $where = array(
                'isactive' => 'Y',
                'datereceived >=' => date('Y-m-d', strtotime(date('Y') . '-01-01')),
                'datereceived <=' => date('Y-m-d', strtotime((date('Y')+1) . '-01-01')),
                'beb' => 'Y'
            );





        }


        $data['results']= $this->receipts_m->get_where($where);



            $this->load->view('dashboard_v', $data);


    }

}