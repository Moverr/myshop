<?php
class Reports_export extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();
        $this->load->model('procurement_plan_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('contracts_m');
        $this->load->model('contract_price_m');
        $this->load->model('bid_invitation_m');
        $this->load->model('remoteapi_m');
        $this->load->model('disposal_m');
        $this->load->model('special_procurement_m');

    }

    /*
       INITILIZATION 
    */
    function index()
    {

        //if form is posted
        if($_POST){
                   #by default financial year is assumed empty
            $financial_year='';
            # switch logic based on form type
            switch($this->input->post('report_type')){



                # case of awarded contracts
                case 'awarded_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);


                    $results = unique_multidim_array($results_all,'id');




                    # add call off order count
                    $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                    foreach($results as $row){
                        if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                            foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                $call_off_order_bucket[]= $order;
                            }

                        }

                    }

                    $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');


                    $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                    $total_count=count($results) + count($sp_results)+count($call_off_order_bucket);



                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts (except micro procurements)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'Subject of procurement')
                        ->setCellValue('C5', 'Description')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Provider')
                        ->setCellValue('F5', 'Date of award of contract')
                        ->setCellValue('G5', 'Market price of the procurement')
                        ->setCellValue('H5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                   # exit(print_array($results));
                    $num=0;
                    $counter=array();



                    foreach($results as $row)
                    {
                       


                        # exchange rate included
                        $grand_total_actual_payments[] =$estimated_amount=$row['normalized_estimated_amount'];

                        # variation included
                        $grand_amount[] = $actual_amount=$row['normalized_actual_contract_price'];



                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }

                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$actual_amount;
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['providernames']);
                        # for call off orders use date of call off order
                        if($row['call_off_order_id']>0){
                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['date_of_calloff_orders']) );
                        }else{
                            # for other contracts
                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['date_signed']) );
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($estimated_amount) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($actual_amount) );


                        $x ++;
                        $counter[]=$row;

                    }




                    # ADD SPECIAL PROCUREMENT
                    $y = count($results)+count($sp_results);


                    #exit(print_array($sp_results));

                    $x=count($counter)+7;
                    #exit(print_array($x));
                    foreach($sp_results as $row)
                    {
                        

                        $grand_total_actual_payments[]=$estimated_amount= str_replace(',','',$row['normalized_estimated_amount']);
                        $grand_amount[]=$actual_amount= str_replace(',','',$row['normalized_actual_amount']);

                        # filter out completed contracts
                        $completed_contracts[]=$row;
                        $completed_contracts_amounts[]= $estimated_amount;



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'special procurement');
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['SP_provider_name']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['SP_contract_award_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimated_amount);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );

                        $x++;
                        $counter[]=$row;

                    }


                    #exit(print_array($call_off_order_bucket));
                    $call_off_order_amount=array();
                    foreach($call_off_order_bucket as $row)
                    {


                        $grand_total_actual_payments[] = $estimated_amount= $row['contract_value'];
                        $grand_amount[] = $actual_amount= $row['total_actual_payments'];


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['call_off_order_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,'-');
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'Call off order');
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['providernames']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['date_of_calloff_orders']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimated_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );
                        $call_off_order_amount[]=$row['total_actual_payments'];






                        # filter out completed contracts

                        $completed_contracts[]=$row;
                        $completed_contracts_amounts[]= $actual_amount;

                        $x++;


                    }


//                    print_array(count($grand_total_actual_payments));
                    # exit(print_array($grand_total_actual_payments));
//                    print_array($sp_results);
//                    print_array($call_off_order_bucket);
//
//                    exit(print_array($total_count));


                    # ADD LOTS

                   # exit(print_array($call_off_order_amount));
                    #$x=count($results)+count($sp_results);

//                    foreach($lots_bucket as $row)
//                    {
//                        #exchange rate included
//                        $grand_total_actual_payments[] =$estimated_amount=$row['normalized_estimated_amount'];
//
//                        # variation included
//                        $grand_amount[] = $actual_amount=$row['normalized_actual_contract_price'];
//
//
//
//                        # get procurement method id
//                        if($row['procurement_method_ifb']==0){
//                            $procurement_method_id=$row['procurement_method'];
//                        }else{
//                            $procurement_method_id=$row['procurement_method_ifb'];
//                        }
//
//                        # filter out completed contracts
//                        if($row['actual_completion_date']){
//                            $completed_contracts[]=$row;
//                            $completed_contracts_amounts[]=$actual_amount;
//                        }
//
//
//
//                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
//                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
//                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_details'] );
//                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
//                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['providernames']);
//                        # for call off orders use date of call off order
//                        if($row['call_off_order_id']>0){
//                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['date_of_calloff_orders']) );
//                        }else{
//                            # for other contracts
//                            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['date_signed']) );
//                        }
//
//                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($estimated_amount) );
//                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($actual_amount) );
//
//                        $x++;
//
//
//
//
//                    }

                     # exit(print_array($call_off_order_bucket));

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($grand_amount)) );

                    #exit(print_array($grand_amount));

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Awarded Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');


//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Awarded Contracts")
                        ->setDescription("Auto generated report on awarded contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts (except micro procurements)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');
                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of amended contracts (except micos)
                case 'amended_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_all_varied_awarded_contracts($from, $to, $pde,$financial_year);
                    $total_count=count($results);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Amended of varied contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'No')
                        ->setCellValue('B5', 'Procurement Reference Number')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Description')
                        ->setCellValue('E5', 'Amendment or Variation')
                        ->setCellValue('F5', 'Provider')
                        ->setCellValue('G5', 'Date of Amendment or Variation')
                        ->setCellValue('H5', 'Value of Amendment or Variation ')
                        ->setCellValue('I5', 'Revised Contract Value and Currency ');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('H:I')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();
                    $grand_variation_amount = array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    $num=0;

                    # exit(print_array($results));

                    foreach($results as $row)
                    {
                        $num++;

                        if($row['estimated_amount_exchange_rate']){
                            $estimated_amount=$row['estimated_amount']*$row['estimated_amount_exchange_rate'];
                        }else{
                            $estimated_amount=$row['estimated_amount'];
                        }

                        $actual_amount=$row['amount'] * $row['xrate'];





                        $grand_total_actual_payments[] = $estimated_amount;


                        if($row['price_variation_type']=='positive'){
                            $grand_amount[] = ($row['amount'] * $row['xrate'])+($row['variation_amount']*$row['variation_amount_rate']);
                        }else{
                            $grand_amount[] = ($row['amount'] * $row['xrate'])-($row['variation_amount']*$row['variation_amount_rate']);
                        }


                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }

                        if ($row['price_variation_type'] == 'positive') {
                            $grand_variation_amount[] = ($row['amount'] * $row['xrate']) + $row['amount'];
                        } else {
                            $grand_variation_amount[] = ($row['amount'] * $row['xrate']) - $row['amount'] ;
                        }



//                        ->setCellValue('A5', 'No')
//                        ->setCellValue('B5', 'Procurement Reference Number')
//                        ->setCellValue('C5', 'Amendment or Variation')
//                        ->setCellValue('D5', 'Provider')
//                        ->setCellValue('E5', 'Date of Amendment or Variation')
//                        ->setCellValue('F5', 'Value of Amendment or Variation ')
//                        ->setCellValue('G5', 'Revised Contract Value and Currency ');



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Planned Contract Completion date :'.my_date_diff($row['completion_date'], $row['new_planned_date_of_completion']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['variation_date']) );
                        if($row['price_variation_type']=='positive'){
                            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,'+ '.number_format($row['variation_amount']*$row['variation_amount_rate']) );
                            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,($row['amount'] * $row['xrate'])+($row['variation_amount']*$row['variation_amount_rate']) );
                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,'- '.number_format($row['variation_amount']*$row['variation_amount_rate']) );
                            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,($row['amount'] * $row['xrate'])-($row['variation_amount']*$row['variation_amount_rate']) );
                        }





                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }









                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Amended Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Amended or varied")
                        ->setDescription("Auto generated report on amended or varied contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Amended or varied contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts (amended or varied)');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );






                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of completed contracts
                case 'completed_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true,'','Y');
//                    $results =array();
//                    foreach($results_all as $row){
//                        if($row['call_off_order_id']==''){
//                            $results[]=$row;
//                        }else{
//                            # for call off orders filter out only awarded
//                            if($row['call_off_order_status']=='completed'){
//                                $results[]=$row;
//                            }
//                        }
//
//                    }

                    $results = unique_multidim_array($results,'id');


                    # add call off order count
                    # add call off order count
                    $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = 'completed')));


                            foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = 'completed') as $order){
                                $call_off_order_bucket[]= $order;
                            }





                    $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');

                    # exit(print_array($results));


                    $total_count=count($results) +count($call_off_order_bucket);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Completed Contracts (except micro procurements)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'No')
                        ->setCellValue('B5', 'Procurement Reference Number')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Description')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Provider')
                        ->setCellValue('G5', 'Date of completion')
                        ->setCellValue('H5', 'Total Amount paid (UGX)')
                        ->setCellValue('I5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    $num=0;

                    foreach($results as $row)
                    {
                        $num++;

                        $estimated_amount=$row['estimated_amount_at_ifb'];

                        $actual_amount=$row['normalized_actual_contract_price'];



                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                        # for call off orders use date of call off order
                        if($row['call_off_order_id']>0){
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['call_off_order_actual_completion_date']) );
                        }else{
                            # for other contracts
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['actual_completion_date']) );
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,number_format($estimated_amount) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,number_format($actual_amount) );


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$actual_amount;
                        }







                        $x ++;

                    }


                    # ADD CALL OFF ORDERS
                    $z = count($results)+1;
                    $num=count($results)+7;

                    foreach($call_off_order_bucket as $row)
                    {
                        $estimated_amount= $row['contract_value'];
                        $actual_amount= $row['total_actual_payments'];
                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;

                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num,$z);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num,$row['call_off_order_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num,'-');
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num,'Call off order');
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num,$row['providernames']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num,reformat_date('d M Y', $row['date_of_calloff_orders'])!=='01 Jan 1970'?reformat_date('d M Y', $row['date_of_calloff_orders']):'-' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$num,$estimated_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$num,$actual_amount );






                        # filter out completed contracts

                        $completed_contracts[]=$row;
                        $completed_contracts_amounts[]= $estimated_amount;

                        $num++;
                        $z++;


                    }

                    #exit(print_array($grand_amount));



                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Completed Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Completed Contracts")
                        ->setDescription("Auto generated report on Completed contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Completed Contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of micro procurement contracts
                case 'micro_procurement':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Micro Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'No')
                        ->setCellValue('B5', 'Procurement Reference Number')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Description')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Provider')
                        ->setCellValue('G5', 'Date of Contract Completion')
                        ->setCellValue('H5', 'Contract value (UGX)')
                        ->setCellValue('I5', 'Status');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:I')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    $num=0;

                    # exit(print_array($results));

                    foreach($results as $row)
                    {
                        $num++;

                        $estimated_amount=$row['normalized_estimated_amount'];
                        $actual_amount=$row['normalized_actual_contract_price'];

                        $grand_amount[]=$actual_amount;
                        $grand_total_actual_payments[]=$estimated_amount;


                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                        # for call off orders use date of call off order
                        if($row['call_off_order_id']>0){
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,'-' );
                        }else{
                            # for other contracts
                            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['actual_completion_date']==''?'-':reformat_date('d M Y', $row['actual_completion_date']) );
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,number_format($actual_amount) );

                        if($row['actual_completion_date']!=''){
                            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                        }



                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$actual_amount;
                        }








                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Micro Procurements '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Micro Procurements")
                        ->setDescription("Auto generated report on Micro Procurements")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Micro Procurement contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # monthly quartely reports
                case 'quarterly_procurements':
                   if($this->input->post('sub_report_type')=='micros'){
                       switch($this->input->post('quarter')){
                           # first quarter
                           case '1':

                               # exit(print_array($_POST));
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-09-30'));
                               $pde=$this->input->post('pde');

                               if($from&&$to){
                                   $financial_year=NULL;
                               }

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);


                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                    # call off orders
                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');

//                    $total_count=count($results) +count($call_off_order_bucket);
                               $total_count=count($results);



                               $objPHPExcel = new PHPExcel();

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT AND COMMUNITY PURCHASE) QUARTER 1 ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value')->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount  );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }

//                               # ADD CALL OFF ORDERS
//                               $z = count($results)+1;
//                               $num=count($results)+7;
//
//                               # print_array($z);
//
//
//                               foreach($call_off_order_bucket as $row)
//                               {
//                                   $estimated_amount= $row['contract_value'];
//                                   $actual_amount= $row['total_actual_payments'];
//                                   $grand_total_actual_payments[] = $estimated_amount;
//                                   $grand_amount[] = $actual_amount;
//
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num,$z);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num,$row['call_off_order_no']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num,$row['subject_of_procurement'] );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num,'-');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num,'Call off order');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num,$row['providernames']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num,reformat_date('d M Y', $row['date_of_calloff_orders']) );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$num,$estimated_amount );
//
//                                   if($row['actual_completion_date']!=''){
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );
//
//                                   }else{
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );
//
//                                   }
//
//
//
//
//
//
//
//                                   # filter out completed contracts
//
//                                   $completed_contracts[]=$row;
//                                   $completed_contracts_amounts[]= $estimated_amount;
//
//                                   $num++;
//                                   $z++;
//
//
//                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );




                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # second quarter
                           case '2':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-10-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-12-31'));
                               $pde=$this->input->post('pde');

                               if($from&&$to){
                                   $financial_year=NULL;
                               }

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);


                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');

                               # call off orders
                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');

                               $total_count=count($results);




                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT AND COMMUNITY PURCHASE) QUARTER 2 ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value')->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount  );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }

                               # ADD CALL OFF ORDERS
//                               $z = count($results)+1;
//                               $num=count($results)+7;
//
//                               # print_array($z);
//
//
//                               foreach($call_off_order_bucket as $row)
//                               {
//                                   $estimated_amount= $row['contract_value'];
//                                   $actual_amount= $row['total_actual_payments'];
//                                   $grand_total_actual_payments[] = $estimated_amount;
//                                   $grand_amount[] = $actual_amount;
//
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num,$z);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num,$row['call_off_order_no']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num,$row['subject_of_procurement'] );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num,'-');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num,'Call off order');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num,$row['providernames']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num,reformat_date('d M Y', $row['date_of_calloff_orders']) );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$num,$estimated_amount );
//
//                                   if($row['actual_completion_date']!=''){
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );
//
//                                   }else{
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );
//
//                                   }
//
//
//
//
//
//
//
//                                   # filter out completed contracts
//
//                                   $completed_contracts[]=$row;
//                                   $completed_contracts_amounts[]= $estimated_amount;
//
//                                   $num++;
//                                   $z++;
//
//
//                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );




                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 2")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # third quarter
                           case '3':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));
                               $pde=$this->input->post('pde');

                               if($from&&$to){
                                   $financial_year=NULL;
                               }

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);


                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');

                               # call off orders
                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');

                               #exit(print_array($call_off_order_bucket));

//                               $total_count=count($results) +count($call_off_order_bucket);
                               $total_count=count($results);




                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT AND COMMUNITY PURCHASE) QUARTER 3 ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value')->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount  );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }

                               # ADD CALL OFF ORDERS
//                               $z = count($results)+1;
//                               $num=count($results)+7;
//
//                               # print_array($z);
//
//
//                               foreach($call_off_order_bucket as $row)
//                               {
//                                   $estimated_amount= $row['contract_value'];
//                                   $actual_amount= $row['total_actual_payments'];
//                                   $grand_total_actual_payments[] = $estimated_amount;
//                                   $grand_amount[] = $actual_amount;
//
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num,$z);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num,$row['call_off_order_no']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num,$row['subject_of_procurement'] );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num,'-');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num,'Call off order');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num,$row['providernames']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num,reformat_date('d M Y', $row['date_of_calloff_orders']) );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$num,$estimated_amount );
//
//                                   if($row['actual_completion_date']!=''){
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );
//
//                                   }else{
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );
//
//                                   }
//
//
//
//
//
//
//
//                                   # filter out completed contracts
//
//                                   $completed_contracts[]=$row;
//                                   $completed_contracts_amounts[]= $estimated_amount;
//
//                                   $num++;
//                                   $z++;
//
//
//                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );




                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 3")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 3')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # fourth quarter
                           case '4':
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-4-01'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));

                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);


                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                               # call off orders
                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');

                               #exit(print_array($call_off_order_bucket));

//                               $total_count=count($results) +count($call_off_order_bucket);
                               $total_count=count($results);




                               $objPHPExcel = new PHPExcel();

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT AND COMMUNITY PURCHASE) QUARTER 4 ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value')->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount  );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }

                               # ADD CALL OFF ORDERS
//                               $z = count($results)+1;
//                               $num=count($results)+7;
//
//                               # print_array($z);
//
//
//                               foreach($call_off_order_bucket as $row)
//                               {
//                                   $estimated_amount= $row['contract_value'];
//                                   $actual_amount= $row['total_actual_payments'];
//                                   $grand_total_actual_payments[] = $estimated_amount;
//                                   $grand_amount[] = $actual_amount;
//
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num,$z);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num,$row['call_off_order_no']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num,$row['subject_of_procurement'] );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num,'-');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num,'Call off order');
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num,$row['providernames']);
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num,reformat_date('d M Y', $row['date_of_calloff_orders']) );
//                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$num,$estimated_amount );
//
//                                   if($row['actual_completion_date']!=''){
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );
//
//                                   }else{
//                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );
//
//                                   }
//
//
//
//
//
//
//
//                                   # filter out completed contracts
//
//                                   $completed_contracts[]=$row;
//                                   $completed_contracts_amounts[]= $estimated_amount;
//
//                                   $num++;
//                                   $z++;
//
//
//                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );




                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 4")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT) QUARTER 4')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # default
                           default:

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year,$micro_procurements=true);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');

                               # call off orders
                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');

                               #exit(print_array($call_off_order_bucket));

//                               $total_count=count($results) +count($call_off_order_bucket);
                               $total_count=count($results);



                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT AND COMMUNITY PURCHASE) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value')
                                   ->setCellValue('I5', 'Status');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount  );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }

                               # ADD CALL OFF ORDERS
                               $z = count($results)+1;
                               $num=count($results)+7;

                               # print_array($z);


                               foreach($call_off_order_bucket as $row)
                               {
                                   $estimated_amount= $row['contract_value'];
                                   $actual_amount= $row['total_actual_payments'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$num,$z);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$num,$row['call_off_order_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$num,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$num,'-');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$num,'Call off order');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$num,$row['providernames']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$num,reformat_date('d M Y', $row['date_of_calloff_orders']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$num,$estimated_amount );

                                   if($row['actual_completion_date']!=''){
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Completed' );

                                   }else{
                                       $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,'Awarded' );

                                   }







                                   # filter out completed contracts

                                   $completed_contracts[]=$row;
                                   $completed_contracts_amounts[]= $estimated_amount;

                                   $num++;
                                   $z++;


                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );




                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }

                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT & COMMUNITY PURCHASE)")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT & COMMUNITY PURCHASE) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (MICRO PROCUREMENT & COMMUNITY PURCHASE) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                       }

                   }else{
                       switch($this->input->post('quarter')){
                           # first quarter
                           case '1':

                            # exit(print_array($_POST));
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-09-30'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');


                               $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                               $total_count=count($results) + count($sp_results)+count($call_off_order_bucket);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT AND COMMUNITY PURCHASE) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];



                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );



                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }


                               $y = count($results)?count($results)+count($sp_results):7;



                               foreach($sp_results as $row)
                               {
                                   $estimated_amount= $row['normalized_estimated_amount'];
                                   $actual_amount= $row['normalized_actual_amount'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$y);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Special Procurement');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['SP_provider_name']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['SP_contract_award_date']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount>0?$actual_amount:'-' );


                                   # filter out completed contracts

                                   $completed_contracts[]=$row;
                                   $completed_contracts_amounts[]= $estimated_amount;


                                   $y++;
                                   $x++;





                               }



                               # ADD CALL OFF ORDERS
                               $z = count($results)+count($sp_results)+1;



                               foreach($call_off_order_bucket as $row)
                               {
                                   $estimated_amount= $row['contract_value'];
                                   $actual_amount= $row['total_actual_payments'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$z);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['call_off_order_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'-');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Call off order');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_of_calloff_orders']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );



                                   # filter out completed contracts
                                   if($row['status']=='completed'){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]= $estimated_amount;
                                   }



                                   $x++;
                                   $z++;


                               }



                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 1')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # second quarter
                           case '2':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-10-01'));
                               $to = date('Y-m-d', strtotime($start_year . '-12-31'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');


                               $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                               $total_count=count($results) + count($sp_results)+count($call_off_order_bucket);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT AND COMMUNITY PURCHASE)')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));
                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];


                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount);



                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }


                               $y = count($results)?count($results)+count($sp_results):7;



                               foreach($sp_results as $row)
                               {
                                   $estimated_amount= $row['normalized_estimated_amount'];
                                   $actual_amount= $row['normalized_actual_amount'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$y);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Special Procurement');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['SP_provider_name']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['SP_contract_award_date']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount>0?$actual_amount:'-' );

                                   # filter out completed contracts

                                   $completed_contracts[]=$row;
                                   $completed_contracts_amounts[]= $estimated_amount;


                                   $y++;
                                   $x++;





                               }



                               # ADD CALL OFF ORDERS
                               $z = count($results)+count($sp_results)+1;



                               foreach($call_off_order_bucket as $row)
                               {
                                   $estimated_amount= $row['contract_value'];
                                   $actual_amount= $row['total_actual_payments'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$z);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['call_off_order_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'-');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Call off order');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_of_calloff_orders']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount);


                                   # filter out completed contracts
                                   if($row['status']=='completed'){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]= $estimated_amount;
                                   }



                                   $x++;
                                   $z++;


                               }



                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 2")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # third quarter
                           case '3':

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');


                               $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                               $total_count=count($results) + count($sp_results)+count($call_off_order_bucket);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT AND COMMUNITY PURCHASE)')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];



                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }


                               $y = count($results)?count($results)+count($sp_results):7;



                               foreach($sp_results as $row)
                               {
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_amount'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$y);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Special Procurement');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['SP_provider_name']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['SP_contract_award_date']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount>0?$actual_amount:'-' );


                                   # filter out completed contracts

                                   $completed_contracts[]=$row;
                                   $completed_contracts_amounts[]= $estimated_amount;


                                   $y++;
                                   $x++;





                               }



                               # ADD CALL OFF ORDERS
                               $z = count($results)+count($sp_results)+1;



                               foreach($call_off_order_bucket as $row)
                               {
                                   $estimated_amount= $row['contract_value'];
                                   $actual_amount= $row['total_actual_payments'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$z);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['call_off_order_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'-');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Call off order');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d-M-Y', $row['date_of_calloff_orders'])!=='01-Jan-1970'?reformat_date('d M Y', $row['date_of_calloff_orders']):'-' );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount);



                                   # filter out completed contracts
                                   if($row['status']=='completed'){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]= $estimated_amount;
                                   }



                                   $x++;
                                   $z++;


                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );

                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 3")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 3')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # fourth quarter
                           case '4':
                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year + 1 . '-4-01'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));

                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }


                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');


                               $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                               $total_count=count($results) + count($sp_results)+count($call_off_order_bucket);


                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT AND COMMUNITY PURCHASE)')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];


                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['contract_award_date']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount);




                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$row['normalized_actual_contract_price'];
                                   }







                                   $x ++;

                               }


                               $y = count($results)?count($results)+count($sp_results):7;



                               foreach($sp_results as $row)
                               {
                                   $estimated_amount= $row['normalized_estimated_amount'];
                                   $actual_amount= $row['normalized_actual_amount'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$y);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Special Procurement');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['SP_provider_name']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['SP_contract_award_date']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount0?$actual_amount:'-' );

                                   # filter out completed contracts

                                   $completed_contracts[]=$row;
                                   $completed_contracts_amounts[]= $estimated_amount;


                                   $y++;
                                   $x++;





                               }



                               # ADD CALL OFF ORDERS
                               $z = count($results)+count($sp_results)+1;



                               foreach($call_off_order_bucket as $row)
                               {
                                   $estimated_amount= $row['contract_value'];
                                   $actual_amount= $row['total_actual_payments'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$z);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['call_off_order_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'-');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Call off order');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_of_calloff_orders']) );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );


                                   # filter out completed contracts
                                   if($row['status']=='completed'){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]= $estimated_amount;
                                   }



                                   $x++;
                                   $z++;


                               }



                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );


                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }


                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 4")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) QUARTER 4')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                               break;

                           # default
                           default:

                               $start_year = substr($this->input->post('financial_year'), 0, 4);
                               $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                               $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));
                               $pde=$this->input->post('pde');

                               if ($this->session->userdata('isadmin') != 'Y') {
                                   $pde = $this->session->userdata('pdeid');
                               }

                               $results_all= $this->contracts_m->get_contracts_awarded_except_micro_procurements($from, $to, $pde,$financial_year);

                               //filter out local government pdes
                               $results=array();
                               foreach($results_all as $row){
                                   if(strtolower(get_pde_info_by_id($row['pdeid'],'category'))=='local government'){
                                       $results[]=$row;
                                   }

                               }

                               $results = unique_multidim_array($results,'id');


                               # add call off order count
                               $call_off_order_bucket=array();

//                    $call_offs=$this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '');
//
//                    exit(print_array($call_offs));

                               foreach($results as $row){
                                   if(count($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = ''))){
                                       foreach($this->contracts_m->get_call_off_orders_by_range($from, $to, $pde , $financial_year, $completed = '') as $order){
                                           $call_off_order_bucket[]= $order;
                                       }

                                   }

                               }

                               $call_off_order_bucket=unique_multidim_array($call_off_order_bucket,'id');


                               $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);

                               $total_count=count($results) + count($sp_results)+count($call_off_order_bucket);



                               $objPHPExcel = new PHPExcel();

                               # Report columns

                               $objPHPExcel->setActiveSheetIndex(0)
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT AND COMMUNITY PURCHASE)')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                                   ->setCellValue('A5', 'No')
                                   ->setCellValue('B5', 'Procurement Reference Number')
                                   ->setCellValue('C5', 'Subject of procurement')
                                   ->setCellValue('D5', 'Details')
                                   ->setCellValue('E5', 'Method of procurement')
                                   ->setCellValue('F5', 'Provider')
                                   ->setCellValue('G5', 'Invoice No and Date of contract award')
                                   ->setCellValue('H5', 'Contract Value');


                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                               $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                               # data counter
                               $x = 7;
                               $num=0;

                               # totals containers
                               $grand_total_actual_payments=array();
                               $grand_amount=array();

                               # completed contracts container
                               $completed_contracts=array();
                               $completed_contracts_amounts=array();

                               # exit(print_array($results));

                               foreach($results as $row)
                               {
                                   $num++;
                                   $estimated_amount=$row['normalized_estimated_amount'];
                                   $actual_amount=$row['normalized_actual_contract_price'];

                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;


                                   # get procurement method id
                                   if($row['procurement_method_ifb']==0){
                                       $procurement_method_id=$row['procurement_method'];
                                   }else{
                                       $procurement_method_id=$row['procurement_method_ifb'];
                                   }





                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);

                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['date_signed']) );




                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );



                                   # filter out completed contracts
                                   if($row['actual_completion_date']){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]=$actual_amount;
                                   }







                                   $x ++;

                               }


                               $y = count($results)?count($results)+count($sp_results):7;



                               foreach($sp_results as $row)
                               {
                                   $estimated_amount= $row['normalized_estimated_amount'];
                                   $actual_amount= $row['normalized_actual_amount'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$y);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Special Procurement');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['SP_provider_name']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['SP_contract_award_date']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount>0?$actual_amount:'-' );
                                   # filter out completed contracts

                                   $completed_contracts[]=$row;
                                   $completed_contracts_amounts[]= $estimated_amount;


                                   $y++;
                                   $x++;





                               }



                               # ADD CALL OFF ORDERS
                               $z = count($results)+count($sp_results)+1;



                               #exit(print_array($call_off_order_bucket));
                               foreach($call_off_order_bucket as $row)
                               {
                                   $estimated_amount= $row['contract_value'];
                                   $actual_amount= $row['total_actual_payments'];
                                   $grand_total_actual_payments[] = $estimated_amount;
                                   $grand_amount[] = $actual_amount;

                                   $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$z);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['call_off_order_no']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                                   $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,'-');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,'Call off order');
                                   $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                                   $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,date('d M Y',strtotime($row['date_of_calloff_orders'])));
                                   $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$actual_amount );




                                   # filter out completed contracts
                                   if($row['status']=='completed'){
                                       $completed_contracts[]=$row;
                                       $completed_contracts_amounts[]= $estimated_amount;
                                   }



                                   $x++;
                                   $z++;


                               }


                               # display totals
                               # make first rows bald
                               $totals_row=($total_count+7);
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                               # total results

                               $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($grand_amount)) );




                               # format totals
                               $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                                   ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                               # generate range
                               $dynamic_title='';
                               if($financial_year){
                                   $dynamic_title=$financial_year;
                               }

                               $title='Quarterly report '.$dynamic_title;

                               # set print title
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                               $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                               $objPHPExcel->getActiveSheet()->setTitle($title);

                               $objPHPExcel->getProperties()
                                   ->setSubject("Report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT)")
                                   ->setDescription("Auto generated report on  QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ")
                                   ->setKeywords("Reports Monthly reports")
                                   ->setCategory("Monthly Reports");




                               // Create a new worksheet, after the default sheet
                               $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                               $objPHPExcel->setActiveSheetIndex(1);
                               $objPHPExcel->getActiveSheet()
                                   ->setCellValue('A1', 'PDE:')
                                   ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                                   ->setCellValue('A2', 'REPORT:')
                                   ->setCellValue('B2', 'QUARTERLY REPORT ON PROCUREMENT (EXCEPT MICRO PROCUREMENT) ')
                                   # generate range

                                   ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                                   ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                                   ->setCellValue('A4', 'CREATED BY:')
                                   ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                                   ->setCellValue('A7', '')
                                   ->setCellValue('B7', 'Number')
                                   ->setCellValue('C7', 'Percentage by number')
                                   ->setCellValue('D7', 'Amount (UGX)')
                                   ->setCellValue('E7', 'Percentage by amount');



                               # wrap text
                               $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                               # set default column width
                               $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                               # make first rows bald
                               $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                               #TOTAL CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                               $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               #TOTAL COMPLETED CONTRACTS
                               $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                               $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                               $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                               $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                               $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                               // Rename 2nd sheet
                               $objPHPExcel->getActiveSheet()->setTitle('Summary');

                               # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                               $objPHPExcel->setActiveSheetIndex(1);


                               # Redirect output to a client’s web browser (Excel5)
                               header('Content-Type: application/vnd.ms-excel');
                               header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                               header('Cache-Control: max-age=0');

                               # If you're serving to IE 9, then the following may be needed
                               header('Cache-Control: max-age=1');

                               # If you're serving to IE over SSL, then the following may be needed
                               header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                               header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                               header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                               header ('Pragma: public'); // HTTP/1.0


                               $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                               ob_end_clean();
                               ob_start();
                               $objWriter->save('php://output');
                       }
                   }
                    break;

                # case of micro procurement contracts
                case 'suspended_providers':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $suspended_providers = $this->remoteapi_m->all_providers_suspended();

                    $suspensions_in_range=array();
                    foreach ($suspended_providers as $row) {
                        if(check_in_range($from, $to, $row['sus_start'])){
                            $suspensions_in_range[]=$row;
                        }

                    }

                    //get all ever awarded providers in current contract result sey
                    $suspended_provs_in_result_set=array();
                    $all_contracts=$this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true);

                    foreach ($all_contracts as $res) {

                        foreach($suspensions_in_range as $row){

                            if(strtolower(trim(is_numeric($row['orgid'])?get_provider_info_by_id($row['orgid'],'title'):$row['orgid']))===strtolower(trim(get_provider_by_procurement($res['procurement_ref_id'])))){
                                $suspended_provs_in_result_set[]=$res;

                            }
                        }



                    }
                    $results=$all_contracts;



                    $results_suspended_provider = $suspended_provs_in_result_set;

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts awarded to suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Provider')
                        ->setCellValue('F5', 'Date of award of contract')
                        ->setCellValue('G5', 'Market price of the procurement')
                        ->setCellValue('H5', 'Contract value (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    # exit(print_array($results_suspended_provider));

                    foreach($results_suspended_provider as $row)
                    {
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        $grand_amount[] = $row['amount'] * $row['xrate'];



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,($row['estimated_amount']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,($row['amount'] * $row['xrate']) );


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+3);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($total_count+3),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($total_count+3),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='suspended providers '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Contracts awarded to suspended providers")
                        ->setDescription("Auto generated report on Contracts awarded to suspenede providers")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts awarded to suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # totals containers
                    $all_awarded_grand_total_actual_payments=array();
                    $all_awarded_grand_amount=array();



                    foreach($results as $row)
                    {
                        $all_awarded_grand_total_actual_payments[] = $row['estimated_amount'];
                        $all_awarded_grand_amount[] = $row['amount'] * $row['xrate'];




                    }



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($all_awarded_grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total contracts awarded to suspended providers');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($results_suspended_provider));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($results_suspended_provider)/count($results))*100,2).'%'  );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($grand_amount)/array_sum($all_awarded_grand_amount))*100,2).'%' );











                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # published best evaluated bidders
                case 'PBEBs':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year);


                    $results = array();

                    # restricted array
                    $restricted_methods=array('10','3','4','8');
                    # filter out restricted methods
                    foreach($results_all as $row){
                        if(!in_array($row['procurement_method'],$restricted_methods)){
                            $results[]=$row;
                        }

                    }


                     # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement Type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract Price')
                        ->setCellValue('J5', 'Invitation date')
                        ->setCellValue('K5', 'Deadline of bid submission')
                        ->setCellValue('L5', 'Date of bid receipt')
                        ->setCellValue('M5', 'Date of BEB notice display');





                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:M5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:M')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();
                    $total_estimated_amount=array();

//                    exit(print_array($results));

                    foreach($results as $row)
                    {
                        if($row['estimated_amount_exchange_rate']){
                            $estimate=$row['estimated_amount'] * $row['estimated_amount_exchange_rate'];
                        }else{
                            $estimate=$row['estimated_amount'] ;
                        }

                        $total_estimated_amount[]=$estimate;
                        $total_amount[] = ($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        $grand_total_actual_payments[] = $estimate;
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimate );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,reformat_date('d-M-Y', $row['datereceived']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,reformat_date('d-M-Y', $row['display_of_beb_notice']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:M$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Published BEBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Published Best Evaluated Bidders")
                        ->setDescription("Auto generated report on Published Best Evaluated Bidders")
                        ->setKeywords('Published Best Evaluated Bidders')
                        ->setCategory('Published Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # TOTAL BIDS WITH INCONSISTENT VALUATION
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with inconsistent evaluation times');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($inconsitent_evalution));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($inconsitent_evalution)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($inconsitent_evalution_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($inconsitent_evalution_amount)/array_sum($total_amount))*100,2).'%' );


                    # BIDS EXCEEDING TIME LINES
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids Exceeding time lines');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($expired_bids)/$total_count)*100,2).'%' );

                    # TODO expired percentages
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($expired_bids_amounts)/array_sum($total_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # published BEBS WHERE estimate is not normally allowed for the method
                case 'methods_not_allowed':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year);


                    $results = array();

                     # exit(print_array($results_all));
                    #print_array($results_all);

                    $test_works=array();

                    # filter out restricted methods
                    foreach($results_all as $row){

                        # FOR WORKS
                        if($row['procurement_type_id']==3)
                        {
                            switch($row['procurement_method']){
                                case '1':
                                    if($row['normalized_estimated_amount']>500000000){
                                        $results[]=$row;
                                    }
                                    break;

                                case '2':
                                    if($row['normalized_estimated_amount']>500000000){
                                        $results[]=$row;
                                    }
                                    break;

                                case '3':
                                    if(!testRange($row['normalized_estimated_amount'],200000000,500000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '4':
                                    if(!testRange($row['normalized_estimated_amount'],200000000,500000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '5':
                                    if(!testRange($row['normalized_estimated_amount'],200000000,500000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '6':
                                    if(!testRange($row['normalized_estimated_amount'],200000000,500000000)){
                                        $results[]=$row;
                                    }
                                    break;



                                case '7':
                                    if(!testRange($row['normalized_estimated_amount'],10000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '10':
                                    if($row['normalized_estimated_amount'] > 10000000){
                                        $results[]=$row;
                                    }
                                    break;
                            }


                        }


                        # FOR NON CONSULTANCY
                        if($row['procurement_type_id']==2)
                        {
                            switch($row['procurement_method']){
                                case '1':
                                    if($row['normalized_estimated_amount'] > 200000000 ){
                                        $results[]=$row;
                                    }
                                    break;

                                case '2':
                                    if($row['normalized_estimated_amount']> 200000000){
                                        $results[]=$row;
                                    }
                                    break;

                                case '3':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '4':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '5':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '6':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '7':
                                    if(!testRange($row['normalized_estimated_amount'],5000000,100000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '10':
                                    if($row['normalized_estimated_amount'] > 5000000){
                                        $results[]=$row;
                                    }
                                    break;
                            }


                        }


                        # FOR CONSULTANCY SERVICES
                        if($row['procurement_type_id']==4)
                        {
                            switch($row['procurement_method']){
                                case '11':
                                    if($row['normalized_estimated_amount'] > 200000000 ){
                                        $results[]=$row;
                                    }
                                    break;

                                case '12':
                                    if($row['normalized_estimated_amount']> 200000000){
                                        $results[]=$row;
                                    }
                                    break;


                                case '13':
                                    if($row['normalized_estimated_amount'] > 5000000){
                                        $results[]=$row;
                                    }
                                    break;
                            }


                        }




                        # FOR GOODS
                        if($row['procurement_type_id']==1)
                        {
                            switch($row['procurement_method']){
                                case '1':
                                    if($row['normalized_estimated_amount'] > 200000000 ){
                                        $results[]=$row;
                                    }
                                    break;

                                case '2':
                                    if($row['normalized_estimated_amount']> 200000000){
                                        $results[]=$row;
                                    }
                                    break;

                                case '3':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '4':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '5':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '6':
                                    if(!testRange($row['normalized_estimated_amount'],100000000,200000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '7':
                                    if(!testRange($row['normalized_estimated_amount'],5000000,100000000)){
                                        $results[]=$row;
                                    }
                                    break;

                                case '10':
                                    if($row['normalized_estimated_amount'] > 5000000){
                                        $results[]=$row;
                                    }
                                    break;
                            }


                        }


                    }


                     #exit(print_array($test_works));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Method')
                        ->setCellValue('E5', 'Procurement Type')
                        ->setCellValue('F5', 'Estimated Value (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Contract Price');





                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();
                    $total_estimated_amount=array();

                     #exit(print_array($results));

                    foreach($results as $row)
                    {
                        $total_estimated_amount[]=$row['normalized_estimated_amount'];
                        $total_amount[] = $row['normalized_actual_contract_price'];
                        $grand_total_actual_payments[] = $row['normalized_estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$row['normalized_actual_contract_price'];
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,number_format($row['normalized_estimated_amount'] ));
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,number_format($row['normalized_contractprice'] ));

                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Unallowed Estimates '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate")
                        ->setDescription("Auto generated report on Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate")
                        ->setKeywords('Published Best Evaluated Bidders')
                        ->setCategory('Published Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Entities attempting to publish contracts where the procurement method used is not normally allowed for the procurement estimate');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # published best evaluated bidders
                case 'exceeding_timelines':


                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year,$count='',$expired='');


                    $results = array();


                    # filter out restricted methods
                    foreach($results_all as $row){

                        $datetime1 = new DateTime($row['ddate_octhe']);
                        $datetime2 = new DateTime($row['date_oaoterbt_cc']);
                        $interval = $datetime1->diff($datetime2);
                        $days = $interval->format('%a%');

                        # FOR OPEN DOMESTIC SUPPLIES
                        if($row['procurement_method']==2 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR OPEN DOMESTIC WORKS
                        if($row['procurement_method']==2 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR OPEN DOMESTIC NON CONSULTANCY
                        if($row['procurement_method']==2 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }

                        # FOR OPEN DOMESTIC CONSULTANCY
                        if($row['procurement_method']==2 &&$row['procurement_type ']==4 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------------------------





                        # FOR OPEN INTERNATIONAL SUPPLIES
                        if($row['procurement_method']==1 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR OPEN INTERNATIONAL WORKS
                        if($row['procurement_method']==1 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR OPEN INTERNATIONAL non CONSULTANCY
                        if($row['procurement_method']==1 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }

                        # FOR OPEN INTERNATIONAL CONSULTANCY
                        if($row['procurement_method']==1 &&$row['procurement_type ']==4 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------

                        # FOR RESTRICTED DOMESTIC SUPPLIES
                        if($row['procurement_method']==4 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR RESTRICTED DOMESTIC WORKS
                        if($row['procurement_method']==4 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR RESTRICTED DOMESTIC NON CONSULTANCY
                        if($row['procurement_method']==4 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------

                        # FOR RESTRICTED international SUPPLIES
                        if($row['procurement_method']==3 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR RESTRICTED international WORKS
                        if($row['procurement_method']==3 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR RESTRICTED international NON CONSULTANCY
                        if($row['procurement_method']==3 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }



                        # ------------------------------------------------------------------------------------------------------------------------

                        # FOR RFQ supplies
                        if($row['procurement_method']==7 &&$row['procurement_type_id']==1 && $days >20)
                        {
                            $results[]=$row;

                        }

                        # FOR RFQ WORKS
                        if($row['procurement_method']==7 &&$row['procurement_type ']==3 && $days>40 ){
                            $results[]=$row;
                        }

                        # FOR RFQ NON CONSULTANCY
                        if($row['procurement_method']==7 &&$row['procurement_type ']==2 && $days>20 ){
                            $results[]=$row;
                        }


                    }

                    # exit(print_array($results));





                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Evaluations exceeding the  statutory timelines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement Type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract Price')
                        ->setCellValue('J5', 'Evaluation methodology')
                        ->setCellValue('K5', 'Date of commencement of evaluation')
                        ->setCellValue('L5', 'Date of combined evaluation report')
                        ->setCellValue('M5', 'No. of days for evaluation');





                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:M5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:M')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();
                    $total_estimated_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {
                        $datetime1 = new DateTime($row['ddate_octhe']);
                        $datetime2 = new DateTime($row['date_oaoterbt_cc']);
                        $interval = $datetime1->diff($datetime2);
                        $days = $interval->format('%a%');

                        if($row['estimated_amount_exchange_rate']>0){
                            $estimate = $row['estimated_amount'] * $row['estimated_amount_exchange_rate'];
                        }else{
                            $estimate = $row['estimated_amount'] ;
                        }



                        $total_estimated_amount[]=$estimate;
                        $total_amount[] = ($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        $grand_total_actual_payments[] = $estimate;



                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimate );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,$row['evaluation_method_name'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,$row['ddate_octhe']!=='0000-00-00 00:00:00'?reformat_date('d-M-Y', $row['ddate_octhe']):'' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,$row['date_oaoterbt_cc']!=='0000-00-00 00:00:00'?reformat_date('d-M-Y', $row['date_oaoterbt_cc']):'' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,$days );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:M$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:M$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Exceeding Timelines '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Evaluations exceeding the  statutory timelines")
                        ->setDescription("Auto generated report on Evaluations exceeding the  statutory timelines")
                        ->setKeywords('Published Best Evaluated Bidders')
                        ->setCategory('Published Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Evaluations exceeding the  statutory timelines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Published bids with Evaluations exceeding the  statutory timelines');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # expired best evaluated bidders
                case 'EBEBs':

                    //exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year,'',$expired='Y');
                    $results=array();
                    foreach($results_all as $row){
                        if($row['display_of_beb_notice']!='0000-00-00 00:00:00'){
                            $results[]=$row;
                        }
                    }

                    //filter out BEBs with display notices

                    #exit(print_array($results));
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Expired Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement Type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract Price')
                        ->setCellValue('J5', 'Invitation date')
                        ->setCellValue('K5', 'Submission deadline')
                        ->setCellValue('L5', 'Date of bid receipt')
                        ->setCellValue('M5', 'Date of BEB notice display');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:M5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:M')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings


                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();

                    $total_estimated_amount=array();

                     # exit(print_array($results));

                    foreach($results as $row)
                    {

                        $total_estimated_amount[]=$estimate = $row['normalized_estimated_amount'];



                        $total_amount[] = $row['normalized_contractprice'];
                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['beb_expiry_date'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$row['normalized_contractprice'];
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=$row['normalized_contractprice'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['normalized_estimated_amount']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,$row['normalized_contractprice']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,reformat_date('d-M-Y', $row['datereceived']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,reformat_date('d-M-Y', $row['display_of_beb_notice']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:M$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($total_estimated_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:M$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Expired BEBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





                    # $objPHPExcel->getProperties()->setCreator($creator);
                    # $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Expired Best Evaluated Bidders")
                        ->setDescription("Auto generated report on Expired Best Evaluated Bidders")
                        ->setKeywords('Expired Best Evaluated Bidders')
                        ->setCategory('Expired Best Evaluated Bidders');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Expired Best Evaluated Bidders')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL Expired BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Expired contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # case of bid awarded to suspened providers
                case 'SBEB':
                    //if online get suspended providers
                    $financial_year=$this->input->post('financial_year');

                    if($financial_year){
                        $from = substr($financial_year, 0, 4);
                        $to = substr($financial_year, 5, 4);
                    }else{
                        $from=$this->input->post('from_date');
                        $to=$this->input->post('to_date');
                    }




                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_attempted_beb_award_to_suspended_provider($from, $to, $pde,$financial_year);

                    $results=unique_multidim_array($results,'id');


                    # exit(print_array($results));

                    $total_results_amount = array();
                    $total_results_actual_payments=array();



                    $total_count=count($results);

                    # exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs attempting to add bids for suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement method')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Procurement value')
                        ->setCellValue('G5', 'Bidder name');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();

                    $inconsitent_evalution=array();
                    $inconsitent_evalution_amount=array();

                    //exit(print_array($results));



                    # loop through suspended provider bis
                    foreach($results as $row)
                    {

                        $grand_total_actual_payments[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        //procurement types
                        if($row['procurement_type']){
                            $procurement_types[]=get_procurement_plan_entry_info($row['procurement_id'],'procurement_type');
                        }

                        //expired bids
                        if(strtotime($row['beb_expiry_date'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }

                        if ((date('d', strtotime($row['bid_submission_deadline']) - strtotime($row['invitation_to_bid_date']))) != $row['evaluation_time']) {
                            $inconsitent_evalution[]=$row['id'];
                            $inconsitent_evalution_amount[]=($row['exchange_rate']>0?$row['exchange_rate']*$row['beb_contractprice']:$row['beb_contractprice']);
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames']==''?'-':$row['providernames'] );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.($totals_row),(array_sum($grand_total_actual_payments)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:G$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Suspended BEBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on PDEs attempting to add bids for suspended providers")
                        ->setDescription("Auto generated report on PDEs attempting to add bids for suspended providers")
                        ->setKeywords('PDEs attempting to add bids for suspended providers')
                        ->setCategory('PDEs attempting to add bids for suspended providers');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs attempting to add bids for suspended providers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total attempted BEB awards');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_results_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # published Invitation for evaluated bidders
                case 'published_ifbs':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year,$count='',$expired='',$micro='N');

                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Invitation For bids')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Invitation date')
                        ->setCellValue('I5', 'Submission deadline')
                        ->setCellValue('J5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:J')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $expired_bids=array();
                    $expired_bids_amounts=array();


                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_within_period=array();
                    $bid_notices_within_period_amount=array();

                    # exit(print_array($results));

                    foreach($results as $row)
                    {

                        if($row['estimated_amount_exchange_rate']){
                            $estimate=$row['estimated_amount'] * $row['estimated_amount_exchange_rate'];
                        }else{
                            $estimate=$row['estimated_amount'];
                        }

                        $total_amount[] = $estimate;
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        # calculate thresholds depending on method
                        if($row['procurement_method']==7||$row['procurement_method']==8){
                            # for RFQ and Direct Procurements
                            $threshold=date_to_seconds(get_end_date_from_working_days(5)); // 5 DAYS
                        }else{
                            $threshold=date_to_seconds(get_end_date_from_working_days(10)); // 10 DAYS
                        }


                        // within threshold
                        if (strtotime($row['bid_submission_deadline']) < $threshold) {
                            $bid_notices_within_period[] = $row;
                            $bid_notices_within_period_amount[] = $estimate;

                        }


                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > $threshold) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $estimate;

                        }


                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$estimate;
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimate );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d-M-Y', $row['bid_dateadded']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Published IFBS '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Published Invitation For bids")
                        ->setDescription("Auto generated report on Published Invitation For bids")
                        ->setKeywords('Published Published Invitation For bids')
                        ->setCategory('Published Published Invitation For bids');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Invitation For Bids')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );


                    # IFBS WITHIN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids with notices within required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($bid_notices_within_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',round_up((count($bid_notices_within_period)/$total_count)*100,2).'%' );


                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($bid_notices_within_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($bid_notices_within_period_amount)/array_sum($total_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;


                # bid submission deadlines
                case 'bid_submission_deadlines':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year);

                    #exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bid Submission Deadlines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Value')
                        ->setCellValue('E5', 'Invitation date')
                        ->setCellValue('F5', 'Submission deadline')
                        ->setCellValue('G5', 'Date Published')
                        ->setCellValue('H5', 'Status');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:H5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $expired_bids=array();
                    $expired_bids_amounts=array();


                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_within_period=array();
                    $bid_notices_within_period_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        # calculate thresholds depending on method
                        if($row['procurement_method']==7||$row['procurement_method']==8){
                            # for RFQ and Direct Procurements
                            $threshold=date_to_seconds(get_end_date_from_working_days(5)); // 5 DAYS
                        }else{
                            $threshold=date_to_seconds(get_end_date_from_working_days(10)); // 10 DAYS
                        }


                        // within threshold
                        if (strtotime($row['bid_submission_deadline']) < $threshold) {
                            $bid_notices_within_period[] = $row;
                            $bid_notices_within_period_amount[] = $row['estimated_amount'];

                        }


                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > $threshold) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $row['estimated_amount'];

                        }


                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$row['estimated_amount'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d-M-Y', $row['bid_dateadded']) );
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,'Expired' );



                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,'Active' );
                        }


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='IFB Deadlines '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bid Submission Deadlines")
                        ->setDescription("Auto generated report on Bid Submission Deadlines")
                        ->setKeywords('Bid Submission Deadlines')
                        ->setCategory('Bid Submission Deadlines');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bid Submission Deadlines')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );


                    # IFBS WITHIN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids with notices within required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($bid_notices_within_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($bid_notices_within_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($bid_notices_within_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($bid_notices_within_period_amount)/array_sum($total_amount))*100,2).'%' );



                    # EXPIRED IFBS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A12','Expired Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B12',count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C12',round_up((count($expired_bids)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D12',number_format(array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E12',round_up((array_sum($expired_bids_amounts)/array_sum($total_amount))*100,2).'%' );


                    # ACTIVE IFBS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A13','Active Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B13',$total_count-count($expired_bids));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C13',(round_up((($total_count-count($expired_bids))/$total_count)*100,2)).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D13',number_format(array_sum($total_amount)-array_sum($expired_bids_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E13',100-round_up((array_sum($expired_bids_amounts)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # IFBS with notices greater than required period
                case 'bid_notices_greater':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }


                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year,$count='',$expired='',$micro='N',$direct='N');

                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices greater than required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Invitation date')
                        ->setCellValue('I5', 'Submission deadline')
                        ->setCellValue('J5', 'Date Published');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:I')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();



                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    //exit(print_array($results));

                    $thirty_days=array(1);
                    $twenty_days=array(2,3,5);
                    $fifteen_days=array(11,12);
                    $twelve_days=array(4,6);
                    $ten_days=array(13);
                    $five_days=array(7);


                    # group A
                    $group_A=array(1,2,3);//procurement types

                    $group_B=array(4);


                    # exit(print_array(date('d-M-Y',addDays('01-Jan-2016',29))));

                    foreach($results as $row)
                    {

                        $total_amount[] = $row['estimated_amount'];



                        $stripped_date = substr($row['invitation_to_bid_date'],0,10);


                        # split into 2 groups of procurement types (supplies, works and non consultancy services)
                        if(in_array($row['procurement_type'],$group_A)){
                            if(in_array($row['procurement_method'],$thirty_days)){

                                $threshold = addDays($stripped_date,31,array("Saturday", "Sunday"),$skipdates = array());


                            }


                            if(in_array($row['procurement_method'],$twenty_days)){
                                $threshold = addDays($stripped_date,21,array("Saturday", "Sunday"),$skipdates = array());

                            }


                            if(in_array($row['procurement_method'],$twelve_days)){
                                $threshold = addDays($stripped_date,13,array("Saturday", "Sunday"),$skipdates = array());

                            }

                            if(in_array($row['procurement_method'],$five_days)){
                                $threshold = addDays($stripped_date,6,array("Saturday", "Sunday"),$skipdates = array());

                            }


                        }



                        if(in_array($row['procurement_type'],$group_B)){
                            if(in_array($row['procurement_method'],$fifteen_days)){

                                $threshold = addDays($stripped_date,16,array("Saturday", "Sunday"),$skipdates = array());


                            }

                            if(in_array($row['procurement_method'],$ten_days)){
                                $threshold = addDays($stripped_date,11,array("Saturday", "Sunday"),$skipdates = array());

                            }
                        }





                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > ($threshold)) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $row['estimated_amount'];

                        }





                    }


                    $bids_greater_total_amount=array();
                    foreach($bid_notices_greater_period as $row)
                    {

                        if($row['estimated_amount_exchange_rate']){
                            $estimate= $row['estimated_amount'] * $row['estimated_amount_exchange_rate'];
                        }else{
                            $estimate= $row['estimated_amount'] ;

                        }

                        $bids_greater_total_amount[] = $estimate;
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimate );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d-M-Y', $row['bid_dateadded']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=(count($bid_notices_greater_period)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($bids_greater_total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='IFB Notice Periods '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bids with notices greater than required period")
                        ->setDescription("Auto generated report on Bids with notices greater than required period")
                        ->setKeywords('Bids with notices greater than required period')
                        ->setCategory('Bids with notices greater than required period');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices greater than required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # Bids with notices withing required period
                case 'bid_notices_shorter':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }


                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->bid_invitation_m->get_published_invitation_for_bids($from, $to, $pde,$financial_year,$count='',$expired='',$micro='N',$direct='N');
                    #exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices shorter required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Invitation date')
                        ->setCellValue('I5', 'Submission deadline')
                        ->setCellValue('J5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:J')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();



                    $pdes=array();

                    # BIDS WITH NOTICES SHORTER REQUIRED PERIOD
                    $bid_notices_shorter_period=array();
                    $bid_notices_shorter_period_amount=array();

                    $thirty_days=array(1);
                    $twenty_days=array(2,3,5);
                    $fifteen_days=array(11,12);
                    $twelve_days=array(4,6);
                    $ten_days=array(13);
                    $five_days=array(7);


                    # group A
                    $group_A=array(1,2,3);//procurement types

                    $group_B=array(4);


                    # exit(print_array(date('d-M-Y',addDays('01-Jan-2016',29))));

                    foreach($results as $row)
                    {


                        $total_amount[] = $row['estimated_amount'];

                        $date = $row['invitation_to_bid_date'];

                        $createDate = new DateTime($date);

                        $stripped_date = $createDate->format('Y-m-d');


                        # split into 2 groups of procurement types (supplies, works and non consultancy services)
                        if(in_array($row['procurement_type'],$group_A)){
                            if(in_array($row['procurement_method'],$thirty_days)){

                                $threshold = addDays($stripped_date,29,array("Saturday", "Sunday"),$skipdates = array());


                            }


                            if(in_array($row['procurement_method'],$twenty_days)){
                                $threshold = addDays($stripped_date,19,array("Saturday", "Sunday"),$skipdates = array());

                            }


                            if(in_array($row['procurement_method'],$twelve_days)){
                                $threshold = addDays($stripped_date,11,array("Saturday", "Sunday"),$skipdates = array());

                            }

                            if(in_array($row['procurement_method'],$five_days)){
                                $threshold = addDays($stripped_date,4,array("Saturday", "Sunday"),$skipdates = array());

                            }


                        }



                        if(in_array($row['procurement_type'],$group_B)){
                            if(in_array($row['procurement_method'],$fifteen_days)){

                                $threshold = addDays($stripped_date,14,array("Saturday", "Sunday"),$skipdates = array());


                            }

                            if(in_array($row['procurement_method'],$ten_days)){
                                $threshold = addDays($stripped_date,9,array("Saturday", "Sunday"),$skipdates = array());

                            }
                        }


                        # within threshold

                        if (strtotime($row['bid_submission_deadline']) < ($threshold)) {
                            $bid_notices_shorter_period[] = $row;
                            $bid_notices_shorter_period_amount[] = $row['estimated_amount'];

                        }





                    }

                    $bids_total_amount=array();


                    foreach($bid_notices_shorter_period as $row)
                    {
                        if($row['estimated_amount_exchange_rate']){
                            $estimate= $row['estimated_amount'] * $row['estimated_amount_exchange_rate'];
                        }else{
                            $estimate= $row['estimated_amount'] ;

                        }


                        $bids_total_amount[] = $estimate;
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimate );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d-M-Y', $row['bid_dateadded']) );

                        $x ++;

                    }

                    # exit (print_array($total_amount));

                    # display totals
                    # make first rows bald
                    $totals_row=(count($bid_notices_shorter_period)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($bids_total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='BID Notice Periods '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bids with notices within required period")
                        ->setDescription("Auto generated report on Bids with notices within required period")
                        ->setKeywords('Bids with notices within required period')
                        ->setCategory('Bids with notices within required period');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids with notices shorter required period')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total published Invitation For Bids');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES SHORTER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices shorter required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_shorter_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_shorter_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_shorter_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_shorter_period_amount)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;


                # published Invitation for evaluated bids due to close
                case 'bids_due':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');

                    $dOffsets = array("Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday");
                    $prevMonday = mktime(0,0,0, date("m"), date("d")-array_search(date("l"),$dOffsets), date("Y"));
                    $oneWeek = 3600*24*7;$toSunday = 3600*24*6;



                    for ($i=1;$i<= 1;$i++)
                    {

                        $from = date("Y-m-d",$prevMonday + $oneWeek*$i);
                        $to =date("Y-m-d",$prevMonday + $oneWeek*$i + $toSunday);


                    }

                    $pde=$this->input->post('pde');

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    echo ($to);



                     $results= $this->bid_invitation_m->get_bids_due_to_close($from, $to, $pde,$financial_year);

                    #exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Published Invitation For bids due to close')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement type')
                        ->setCellValue('G5', 'Estimated Value (at IFB)')
                        ->setCellValue('H5', 'Invitation date')
                        ->setCellValue('I5', 'Submission deadline')
                        ->setCellValue('J5', 'Date Published');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:J5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:J')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $expired_bids=array();
                    $expired_bids_amounts=array();


                    $pdes=array();

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $bid_notices_greater_period=array();
                    $bid_notices_greater_period_amount=array();

                    # BIDS WITH NOTICES WITHIN REQUIRED PERIOD
                    $bid_notices_within_period=array();
                    $bid_notices_within_period_amount=array();

                    //exit(print_array($results));

                    foreach($results as $row)
                    {
                        if($row['estimated_amount_exchange_rate']){
                            $estimate = $row['estimated_amount'] * $row['estimated_amount_exchange_rate'];
                        }else{
                            $estimate = $row['estimated_amount'];
                        }


                        $total_amount[] = $estimate;
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        # calculate thresholds depending on method
                        if($row['procurement_method']==7||$row['procurement_method']==8){
                            # for RFQ and Direct Procurements
                            $threshold=date_to_seconds(get_end_date_from_working_days(5)); // 5 DAYS
                        }else{
                            $threshold=date_to_seconds(get_end_date_from_working_days(10)); // 10 DAYS
                        }


                        // within threshold
                        if (strtotime($row['bid_submission_deadline']) < $threshold) {
                            $bid_notices_within_period[] = $row;
                            $bid_notices_within_period_amount[] = $estimate;

                        }


                        // greater threshold
                        if (strtotime($row['bid_submission_deadline']) > $threshold) {
                            $bid_notices_greater_period[] = $row;
                            $bid_notices_greater_period_amount[] = $estimate;

                        }


                        //expired bids
                        if(strtotime($row['bid_submission_deadline'])<now()){
                            $expired_bids[]=$row['id'];
                            $expired_bids_amounts[]=$estimate;
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimate );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d-M-Y', $row['bid_submission_deadline']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d-M-Y', $row['bid_dateadded']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Bids due '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Bids due to close")
                        ->setDescription("Auto generated report on Bids due to close")
                        ->setKeywords('Bids due to close')
                        ->setCategory('Bids due to close');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Bids due to close')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED BIDS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Bids due to close');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # BIDS WITH NOTICES GREATER THAN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Bids with notices greater than required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($bid_notices_greater_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($bid_notices_greater_period)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($bid_notices_greater_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($bid_notices_greater_period_amount)/array_sum($total_amount))*100,2).'%' );


                    # IFBS WITHIN REQUIRED PERIOD
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Bids with notices within required period');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',count($bid_notices_within_period));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($bid_notices_within_period)/$total_count)*100,2).'%' );


                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($bid_notices_within_period_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',round_up((array_sum($bid_notices_within_period_amount)/array_sum($total_amount))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # published Invitation for evaluated bids due to close
                case 'late_procurements':

                    # exit(print_array($_POST));

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    # If user is not super admin
                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->procurement_plan_entry_m->get_active_procurements($from, $to, $pde,$financial_year,$count='',$pending_proc='Y');

                    # exit(print_array($results));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Late Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Procurement Value')
                        ->setCellValue('E5', 'Invitation to bid date')
                        ->setCellValue('F5', 'Actual Invitation to bid date');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:G5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:G')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7; // start 2 rows after column headings

                    $total_amount = array();

                    $late_procurements=array();
                    $late_procurements_amounts=array();


                    $pdes=array();


                    foreach($results as $row)
                    {
                        if($row['exchange_rate']>0){
                            $amount=$row['estimated_amount']*$row['exchange_rate'];
                        }else{
                            $amount=$row['estimated_amount'];
                        }

                        $total_amount[] = $amount;


                        //late procurements
                        if(strtotime($row['invitation_to_bid_date'])>strtotime($row['bid_issue_date']) || strtotime($row['bid_issue_date']) >now()|| !$row['bidinvitation_id']){
                            $late_procurements[]=$row;

                        }


                    }

                    foreach($late_procurements as $row)
                    {
                        if($row['exchange_rate']>0){
                            $amount=$row['estimated_amount']*$row['exchange_rate'];
                        }else{
                            $amount=$row['estimated_amount'];
                        }

                        $late_procurements_amounts[]=$amount;

                        $total_amount[] = $row['estimated_amount'];
                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$amount);
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,reformat_date('d-M-Y', $row['bid_issue_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,reformat_date('d-M-Y', $row['invitation_to_bid_date']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=(count($late_procurements)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:F$totals_row")->getFont()->setBold(true);

                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.($totals_row),(array_sum($total_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:F$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Late Procurements '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Late Procurements")
                        ->setDescription("Auto generated report on Late Procurements")
                        ->setKeywords('Late Procurements')
                        ->setCategory('Late Procurements');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Late Procurements')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total procurements');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($total_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    # LATER PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Late Procurements');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($late_procurements));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($late_procurements)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($late_procurements_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($late_procurements_amounts)/array_sum($total_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');


                    break;

                # PDEs with no procurement plan
                case 'non_compliant':

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');


                    $results= $this->procurement_plan_m->get_pdes_with_procurement_plan($financial_year,$pde,$nonComplient='Y');

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs without procurement plans')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'PDE');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:C5")->getFont()->setBold(true);



                    # data counter
                    $x = 7; // start 2 rows after column headings


                    $pdes=array();


                    foreach($results as $row)
                    {
                        $amounts=$this->procurement_plan_entry_m->get_all_procurements_estimates_by_year($row['pdeid'],$financial_year) ;


                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);


                        $x ++;

                    }


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='PDEs-No plan'.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on PDEs with no Procurement Plan")
                        ->setDescription("Auto generated report on PDEs with no Procurement Plan")
                        ->setKeywords('PDEs with no Procurement Plan')
                        ->setCategory('PDEs with no Procurement Plan');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs with no Procurement Plan')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total PDEs');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8','-');
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # PDEs with  procurement plan
                case 'compliant':

                    # grab form dates
                    $financial_year=$this->input->post('financial_year');
                    $pde=$this->input->post('pde');


                    $results_all= $this->procurement_plan_m->get_pdes_with_procurement_plan($financial_year,$pde);

                    # filter out the nulls
                    $results=array();

                    foreach($results_all as $row){
                        if($row['sum_of_entries']){
                            $results[]=$row;
                        }
                    }

//                    exit(print_array($_POST));

                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs with procurement plans')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        # START REPORT COLUMNS
                        ->setCellValue('A5', 'PDE')
                        ->setCellValue('B5', 'Number of procurement entries')
                        ->setCellValue('C5', 'Total value of procurement entries')
                        ->setCellValue('D5', 'Date Created');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:C5")->getFont()->setBold(true);



                    # data counter
                    $x = 7; // start 2 rows after column headings


                    $pdes=array();

                    $grand_total= array();



                    foreach($results as $row)
                    {



                        if (!in_array($row['pde_id'], $pdes)) {
                            $pdes[] = $row['pde_id'];
                        }

                        $proc_plan=$this->procurement_plan_m->get_plans_by_financial_year($financial_year,$row['pdeid']);


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_entries']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['sum_of_entries'] );
                        foreach($proc_plan as $plan){
                            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x, reformat_date('d M Y',$plan['dateadded']));

                        }

                        $grand_total[]=$row['sum_of_entries'];


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:C$totals_row")->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.($totals_row),(array_sum($grand_total)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='PDEs-with plan'.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on PDEs with  Procurement Plan")
                        ->setDescription("Auto generated report on PDEs with Procurement Plan")
                        ->setKeywords('PDEs with  Procurement Plan')
                        ->setCategory('PDEs with Procurement Plan');




                    # SUMMARY WORKSHEET
                    $objPHPExcel->createSheet();
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'PDEs with Procurement Plan')
                        # generate range

                        ->setCellValue('A3', $financial_year)
                        ->setCellValue('B3', $financial_year)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows for the summary bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);




                    #TOTAL PUBLISHED PROCUREMENTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total PDEs');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8','-');
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # case of amended contracts
                case 'all_awarded_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year);
                    $results= unique_multidim_array($results,'id');


                    $sp_results = $this->special_procurement_m->get_active_special_procurements($from, $to, $pde,$financial_year);
                    $total_count=count($results) + count($sp_results);




                    //exit(print_array($sp_results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Details')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Procurement Type')
                        ->setCellValue('G5', 'Estimated value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract price (UGX)')
                        ->setCellValue('J5', 'Date of Contract Award')
                        ->setCellValue('K5', 'Planned Contract Completion Date')
                    ;


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:K5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();


                    # print_array($results);exit();


                    #exit(print_array($results));
                    foreach($results as $row)
                    {
                        # this amount takes into account the exchange rate
                        $grand_total_actual_payments[] = $estimated_amount= $row['normalized_estimated_amount'];

                        # this actual amount takes into account variation
                        $grand_amount[]=$actual_amount=$row['normalized_actual_contract_price'];

                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimated_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,$actual_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d M Y', $row['completion_date']) );





                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$actual_amount;
                        }

                        $x ++;

                    }

                     # exit(print_array($sp_results));

                    foreach($sp_results as $row)
                    {
                        # this amount accounts for exchange rate a well
                        $grand_total_actual_payments[] =$estimated_amount= $row['normalized_estimated_amount'] ;

                        # this amount takes into account exchange rate
                        $grand_amount[]=$actual_amount= $row['normalized_actual_amount'];

                        $completed_contracts[]=$row;
                        $completed_contracts_amounts[]= $actual_amount;




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['SP_procurement_reference_no'].' '.$row['custom_reference_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimated_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['SP_provider_name']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,$actual_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y', $row['SP_contract_award_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,'-' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=$total_count+7;
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:K$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($grand_amount)) );


                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='All Awarded Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on All Awarded Contracts")
                        ->setDescription("Auto generated report on all awarded contracts")
                        ->setKeywords("Signed contract reports")
                        ->setCategory("Signed Contract Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Awarded Contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results)+count($sp_results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                # case of not_commenced
                case 'not_commenced':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    #$results= $this->contracts_m->get_contracts_all_awarded_not_commenced($from, $to, $pde,$financial_year);

                    $results= array();

                    $bebs_without_contracts=$this->bid_invitation_m->get_procurements_scheduled_to_commence($from, $to, $pde,$financial_year);

                    $results=unique_multidim_array($results,'id');
                    $bebs_without_contracts=unique_multidim_array($bebs_without_contracts,'id');

                     #exit(print_array($bebs_without_contracts));

                    $total_count=count($results)+count($bebs_without_contracts);

                    //exit(print_array($sp_results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Procurements scheduled to commence (awarded) but have not yet commenced')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)


                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Subject details')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Procurement Type')
                        ->setCellValue('G5', 'Estimated value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract price (UGX)')
                        ->setCellValue('J5', 'Date of display of BEB Notice')
                        ->setCellValue('K5', 'Date of removal of BEB Notice')
                    ;


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:K5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:K')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();


                    foreach($results as $row)
                    {
                        $grand_total_actual_payments[] = $row['normalized_estimated_amount'] ;
                        $grand_amount[] = $row['normalized_actual_contract_price'];


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }


                        $x ++;

                    }



                    # for bebs without contracts
                    foreach($bebs_without_contracts as $row)
                    {
                        $grand_total_actual_payments[] = $row['normalized_estimated_amount'] ;
                        $grand_amount[] = $row['contractprice'];


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['normalized_estimated_amount'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,get_provider_info_by_id($row['providers'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,$row['contractprice'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y',$row['date_of_display'])=='30 Nov -0001'?'-':reformat_date('d M Y',$row['date_of_display']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d M Y',$row['beb_expiry_date'])=='30 Nov -0001'?'-':reformat_date('d M Y',$row['beb_expiry_date']) );





                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:J$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($grand_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($grand_total_actual_payments)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Not Commenced '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report Procurements scheduled to commence (awarded) but have not yet commenced")
                        ->setDescription("Auto generated report on Procurements scheduled to commence (awarded) but have not yet commenced")
                        ->setKeywords("Signed contract reports")
                        ->setCategory("Signed Contract Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Procurements scheduled to commence (awarded) but have not yet commenced')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Procurements scheduled to commence (awarded) but have not yet commenced');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );






                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of all completed contracts
                case 'all_completed_contracts':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true);
                    $results = unique_multidim_array($results,'id');
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'All Completed Contract')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Subject details')
                        ->setCellValue('E5', 'Procurement Method')
                        ->setCellValue('F5', 'Procurement Type')
                        ->setCellValue('G5', 'Estimated value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract Price')
                        ->setCellValue('J5', 'Date Of Contract Award')
                        ->setCellValue('K5', 'Planned date of contract completion')
                        ->setCellValue('L5', 'Actual Contract Completion date');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:L5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('F:L')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    foreach($results as $row)
                    {


                        $grand_total_actual_payments[]=$row['normalized_estimated_amount'] ;

                        $grand_amount[] = $row['normalized_actual_contract_price'] ;




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['normalized_estimated_amount']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,number_format($row['normalized_actual_contract_price']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d.F.Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d.F.Y', $row['completion_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,reformat_date('d.F.Y', $row['actual_completion_date']) );



                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }







                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:L$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);





                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Completed Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on All Completed Contracts")
                        ->setDescription("Auto generated report on All Completed contracts")
                        ->setKeywords("Reports Monthly reports")
                        ->setCategory("Monthly Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'All Completed Contracts')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);


                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');


                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of contracts ue for completion
                case 'contracts_due':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->contracts_m->get_contracts_due_for_completion($from, $to, $pde,$financial_year);
                    $results=unique_multidim_array($results,'id');
                    # $results_all= $this->bid_invitation_m->get_published_best_evaluated_bidders($from, $to, $pde,$financial_year,'',$expired='Y');
                    $total_count=count($results);

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts due for completion ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'PDE')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Subject details')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Procurement type')
                        ->setCellValue('G5', 'Estimated value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Contract Price')
                        ->setCellValue('J5', 'Date of Contract Award')
                        ->setCellValue('K5', 'Planned Contract Completion Date');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:K5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:J')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();


                    foreach($results as $row)
                    {
                        # this amount takes into account the exchange rate
                        $grand_total_actual_payments[] = $estimated_amount= $row['normalized_estimated_amount'];

                        # this actual amount takes into account variation
                        $grand_amount[]=$actual_amount=$row['normalized_actual_contract_price'];



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['pdename']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title'));
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['normalized_estimated_amount']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,$actual_amount );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d M, Y', $row['completion_date']) );

                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$row['total_actual_payments'];
                        }


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:K$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($grand_total_actual_payments)) );

                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$totals_row,(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:K$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Contracts due '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Contracts due for completion ")
                        ->setDescription("Auto generated report on Contracts due for completion ")
                        ->setKeywords("Signed contract reports")
                        ->setCategory("Signed Contract Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts due for completion ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total awarded contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($grand_amount) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($completed_contracts_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );

                    #TOTAL COMPLETED CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A11','Total incomplete contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B11',$total_count-count($completed_contracts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C11',100-round_up((count($completed_contracts)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D11',number_format(array_sum($grand_amount) -array_sum($completed_contracts_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E11',100-round_up((array_sum($completed_contracts_amounts)/array_sum($grand_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of disposal in first quarter
                case 'first_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year . '-07-01'));
                    $to = date('Y-m-d', strtotime($start_year . '-09-30'));

                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (First Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,reformat_date('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on First Quarter Disposals")
                        ->setDescription("Auto generated report on First Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'First Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of disposal in second quarter
                case 'second_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                    $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));

                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (Second Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,reformat_date('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Second Quarter Disposals")
                        ->setDescription("Auto generated report on Second Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Second Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # case of disposal in third quarter
                case 'third_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year + 1 . '-01-03'));
                    $to = date('Y-m-d', strtotime($start_year + 1 . '-3-31'));
                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (Third Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,reformat_date('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Third Quarter Disposals")
                        ->setDescription("Auto generated report on Third Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Third Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                # case of disposal in fourth quarter
                case 'fourth_quarter':
                    $financial_year=$this->input->post('financial_year');
                    $start_year = substr($financial_year, 0, 4);

                    $from = date('Y-m-d', strtotime($start_year + 1 . '-4-01'));
                    $to = date('Y-m-d', strtotime($start_year + 1 . '-6-30'));
                    $pde=$this->input->post('pde');

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }

                    $results= $this->disposal_m->get_active_disposal_records($pde,'',$count='',$from,$to);
                    $total_count=count($results);

                    //exit(print_array($results));

                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Disposals (Fourth Quarter) ')
                        # generate range

                        ->setCellValue('A3', 'REPORTING PERIOD')
                        ->setCellValue('B3', 'From: '.$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A7', 'PDE')
                        ->setCellValue('B7', 'Disposal Reference Number')
                        ->setCellValue('C7', 'Subject of disposal')
                        ->setCellValue('D7', 'Method of disposal')
                        ->setCellValue('E7', 'Date approval')
                        ->setCellValue('F7', 'Name of buyer')
                        ->setCellValue('G7', 'Reserve price (UGX)')
                        ->setCellValue('H7', 'Contract price (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:H7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 9;

                    # totals containers
                    $disposal_values=array();


                    $contract_value=array();

                    $pdes=array();


                    foreach($results as $row)
                    {
                        $disposal_values[] = $row['amount'];
                        if($row['beneficiary']>0){
                            $contract_value[]=$row['contractamount'];
                        }


                        if (!in_array($row['pdename'], $pdes)) {
                            $pdes[] = $row['pdename'];
                        }




                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['disposal_serial_no']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_disposal']  );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['method'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,reformat_date('d / F / Y', $row['dateadded']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']!==''?$row['providernames']:'No Contract Awarded' );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($row['amount']) );

                        # get contract prices
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['contractamount']!==''?$row['contractamount']:'No Contract Awarded' );



                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,(array_sum($disposal_values)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,(array_sum($contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Disposal Report '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Fourth Quarter Disposals")
                        ->setDescription("Auto generated report on Fourth Quarter Disposals")
                        ->setKeywords("Quarterly Disposals reports")
                        ->setCategory("Quarterly Disposals Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Fourth Quarter Disposals')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);





                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Disposal Records');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',$total_count);
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($disposal_values) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL COMPLETED DISPOSAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total disposal contracts awarded');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($contract_value));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',round_up((count($contract_value)/$total_count)*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($contract_value) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',round_up((array_sum($contract_value)/array_sum($disposal_values))*100,2).'%' );

                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                # contracts completed within market value
                case 'completion_with_market_value':



                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }


                    $results_outside_mp=array();
                    $results_outside_mp_amounts=array();
                    $all_completed= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed=true,$count=false,$nomicros='',$within_market_value='');

                    $results=$all_completed = unique_multidim_array($all_completed,'id');





                    foreach($all_completed as $row){
                        if($row['normalized_actual_contract_price']>$row['estimated_amount_at_ifb']){
                            $results_outside_mp[]=$row;
                            $results_outside_mp_amounts[]=$row['normalized_actual_contract_price'];
                        }
                    }



                    #exit(print_array($results));



                    $total_count=count($results);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Completed Contracts (within market value)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A5', 'No')
                        ->setCellValue('B5', 'Procurement Reference Number')
                        ->setCellValue('C5', 'Subject of procurement')
                        ->setCellValue('D5', 'Description')
                        ->setCellValue('E5', 'Method of procurement')
                        ->setCellValue('F5', 'Procurement type')
                        ->setCellValue('G5', 'Estimated value (at IFB)')
                        ->setCellValue('H5', 'Provider')
                        ->setCellValue('I5', 'Date of approval by AO')
                        ->setCellValue('J5', 'Date signed')
                        ->setCellValue('K5', 'Procurement status')
                        ->setCellValue('L5', 'Planned compledtion date')
                        ->setCellValue('M5', 'Date of completion')
                        ->setCellValue('N5', 'Contract value (UGX)')
                        ->setCellValue('O5', 'Variation amount (UGX)')
                        ->setCellValue('P5', 'Total Amount paid (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:P5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:P')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    $num=0;

                     # exit(print_array($results));
                    $contract_values=array();
                    $amount_differences=array();

                    foreach($results as $row)
                    {
                        $num++;

                        $estimated_amount=$row['estimated_amount_at_ifb'];

                        $actual_amount=$row['normalized_actual_contract_price'];
                        $contract_values[]=$row['total_actual_payments'];
                        $amount_differences[]=$row['difference_in_amount'];



                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$num);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['procurement_type_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$estimated_amount);
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,$row['providernames']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d M Y',$row['dateofconfirmationoffunds']));
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y',$row['date_signed']));
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,'Completed');
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,reformat_date('d M Y',$row['completion_date']));
                        # for call off orders use date of call off order
                        if($row['call_off_order_id']>0){
                            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,reformat_date('d M Y', $row['call_off_order_actual_completion_date']) );
                        }else{
                            # for other contracts
                            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,reformat_date('d M Y', $row['actual_completion_date']) );
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$x,number_format($actual_amount) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$x,number_format($row['difference_in_amount']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('P'.$x,number_format($row['total_actual_payments']) );


                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $completed_contracts[]=$row;
                            $completed_contracts_amounts[]=$actual_amount;
                        }







                        $x ++;

                    }


                    $actual_amounts=array();
                    foreach($all_completed as $row)
                    {
                        $all_amounts[] = $row['normalized_actual_contract_price'];
                    }





                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:P$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('N'.($totals_row),(array_sum($grand_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('O'.($totals_row),(array_sum($amount_differences)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('p'.($totals_row),(array_sum($contract_values)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:P$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Completed Contracts '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Completed Contracts")
                        ->setDescription("Auto generated report on Completed contracts within market price")
                        ->setKeywords("Reports Performance reports")
                        ->setCategory("Performance Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Completed Contracts (within market price)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','All Completed contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($all_completed));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($all_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    #TOTAL CONTRACTS WITHIN MARKET VALUE
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total Completed contracts within market value');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($all_completed)-count($results_outside_mp));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',number_format(((count($all_completed)-count($results_outside_mp))/count($all_completed))*100,2) .'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($grand_amount) - array_sum($results_outside_mp_amounts),2 ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',number_format(100-((array_sum($results_outside_mp_amounts)/array_sum($all_amounts))*100),2) .'%');


                    #TOTAL CONTRACTS ABOVE MARKET VALUE
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total Completed contracts above market value');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($results_outside_mp));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',number_format((count($results_outside_mp)/count($all_completed))*100,2) .'%'  );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($results_outside_mp_amounts),2));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',number_format(((array_sum($results_outside_mp_amounts)/array_sum($all_amounts))*100),2) .'%' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;


                # local vs foreign
                case 'local_foreign':



                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }
                    # print_array($financial_year);

                    #exit(print_array($_POST));



                    #exit(print_array($results_foreign));
                    $all_awarded= unique_multidim_array($this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed = '', $count = '', $nomicros = '',$within_market_value='',$nationality=''),'id');

                    $results_local= array();
                    $results_foreign= array();


                    foreach($all_awarded as $row){
                            if(!$row['nationality']||strtolower($row['nationality'])=='uganda'){
                                $results_local[]=$row;
                            }else{
                                $results_foreign[]=$row;
                            }
                        }





                    $results_local = unique_multidim_array($results_local,'id');
                    $results_foreign = unique_multidim_array($results_foreign,'id');

//                    print_array(count($results_local));
//                    print_array(count($results_foreign));
//
//                    print_array(unique_multidim_array($all_awarded,'id'));
//
//                    exit(print_array(count(unique_multidim_array($all_awarded,'id'))));
                    #exit(print_array($results_foreign));
                    #(print_array($results_local));



                    $total_count=count($results);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Local Vs Foreign')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A9', 'Procurement Type')
                        ->setCellValue('B9', 'Number of Entries')
                        ->setCellValue('C9', 'Value of Entries')

                        ->setCellValue('A7', 'LOCAL SUPPLIERS')


                        ->setCellValue('A18', 'Procurement Type')
                        ->setCellValue('B18', 'Number of Entries')
                        ->setCellValue('C18', 'Value of Entries')

                        ->setCellValue('A16', 'FOREIGN SUPPLIERS');

                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:C5")->getFont()->setBold(true);


                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A9:C9")->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle("A18:C18")->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle("A16:C16")->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle("A7:C7")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('B:C')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    $total_entries=array();
                    $total_values=array();


                    # data counter
                    $x = 10;



                    $objPHPExcel->getActiveSheet()
                    ->getStyle("A7:C7")
                    ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'E05CC2')
                            )
                        )
                    );

                    $objPHPExcel->getActiveSheet()
                        ->getStyle("A8:C8")
                        ->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'E05CC2')
                                )
                            )
                        );

                    $total_entries_local=array();
                    $total_values_local=array();

                    $proc_types=get_active_procurement_types();

                    foreach($proc_types as $row){
                        $procurement_types[$row['title']]=array();
                    }

                   # exit(print_array($procurement_types));

                    foreach($proc_types as $row)
                    {


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['title']);

                        $entries_bucket=array();
                        $entries_value_bucket=array();


                        $all_entries_value_bucket=array();
                        foreach($results_local as $local){
                            $all_entries_value_bucket[]=$local['normalized_actual_contract_price'];
                            if($local['procurement_type_title']==$row['title']){
                                $entries_bucket[]=$local;
                                $entries_value_bucket[]=$local['normalized_actual_contract_price'];
                            }
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,count($entries_bucket) );


                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,array_sum($entries_value_bucket));


                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A'.$x.':C'.$x)
                            ->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'E05CC2')
                                    )
                                )
                            );


                        $x ++;

                    }


                    # data counter
                    $x = 19;
                    $objPHPExcel->getActiveSheet()
                        ->getStyle("A16:C16")
                        ->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'CCFFFF')
                                )
                            )
                        );

                    $objPHPExcel->getActiveSheet()
                        ->getStyle("A17:C17")
                        ->applyFromArray(
                            array(
                                'fill' => array(
                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                    'color' => array('rgb' => 'CCFFFF')
                                )
                            )
                        );


                    $total_entries_foreign=array();
                    $total_values_foreign=array();

                    foreach($proc_types as $row)
                    {


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['title']);

                        $entries_bucket=array();
                        $entries_value_bucket=array();


                        $all_foreign_entries_value_bucket=array();
                        foreach($results_foreign as $foreign){
                            $all_foreign_entries_value_bucket[]=$foreign['normalized_actual_contract_price'];
                            if($foreign['procurement_type_title']==$row['title']){
                                $entries_bucket[]=$foreign;
                                $entries_value_bucket[]=$foreign['normalized_actual_contract_price'];
                            }
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,count($entries_bucket) );


                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,array_sum($entries_value_bucket));


                        $objPHPExcel->getActiveSheet()
                            ->getStyle('A'.$x.':C'.$x)
                            ->applyFromArray(
                                array(
                                    'fill' => array(
                                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                        'color' => array('rgb' => 'CCFFFF')
                                    )
                                )
                            );


                        $x ++;

                    }

                    #exit(print_array($all_foreign_entries_value_bucket));





                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A14:C14")->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle("A23:C23")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('B14',(count($results_local)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('C14',(array_sum($all_entries_value_bucket)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('B23',(count($results_foreign)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('C23',(array_sum($all_foreign_entries_value_bucket)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("B$totals_row:C$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);










                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Local_vs_foreign '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on local vs Foreign suppliers")
                        ->setDescription("Auto generated report on Local Vs Foreign Suppliers")
                        ->setKeywords("Reports Performance reports")
                        ->setCategory("Performance Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Local Vs Foreign Suppliers')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Awarded Contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results_foreign) + count($results_local));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($all_entries_value_bucket)+array_sum($all_foreign_entries_value_bucket)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #exit(print_array($all_entries_value_bucket));

                    #TOTAL CONTRACTS LOCAL SUPPLIERS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Total Contracts (awarded to local suppliers)');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($results_local));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',number_format(((count($results_local)/(count($results_local) + count($results_foreign)))*100),2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($all_entries_value_bucket)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',number_format((array_sum($all_entries_value_bucket)/(array_sum($all_entries_value_bucket)+array_sum($all_foreign_entries_value_bucket))*100),2).'%' );


                    #TOTAL CONTRACTS LOCAL SUPPLIERS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total Contracts (awarded to foreign suppliers)');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($results_foreign));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',number_format(((count($results_foreign)/(count($results_local) + count($results_foreign)))*100),2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($all_foreign_entries_value_bucket)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',number_format(array_sum($all_foreign_entries_value_bucket)/(array_sum($all_entries_value_bucket)+array_sum($all_foreign_entries_value_bucket))*100).'%' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;




                # average number of bids per contract
                case 'average_bids':



                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }
                    # print_array($financial_year);

                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed='',$count=false,$nomicros='',$within_market_value='');


                    $bids= $this->bid_invitation_m->get_all_bids($from, $to, $pde, $financial_year, $local = '',$foreign = '', $resposnsive = '');


                    $results=unique_multidim_array($results,'id');
                    $local= array();
                    $responsive=array();
                    $local_responsive=array();
                    $all_responses=array();

//                    print_array($bids);
//                    exit();

                    //get local bids fron all bids
                    foreach($bids as $bid){
                        $all_responses[]=$bid['total_responses'];
                        $local[]=$bid['total_local_responses'];
                        $responsive[]=$bid['total_responsive_bids'];
                        $local_responsive[]=$bid['total_local_responsive_bids'];
                    }

                    //get local responsive bids
                    //print_array($all_responses);





//                    echo 'IFBs<br>';
//                    (print_array(count($bids)));
//                    echo '<hr>';
//
//                    echo 'Total bids<br>';
//                    (print_array(array_sum($all_responses)));
//                    echo '<hr>';
//
//                    echo 'local bids<br>';
//                    (print_array(array_sum($local)));
//                    echo '<hr>';
//
//                    echo 'Responsive bids<br>';
//                    print_array(array_sum($responsive));
//                    echo '<hr>';
//
//                    echo 'Local Responsive bids<br>';
//                    print_array(array_sum($local_responsive));
//
//
//
//                    exit;



                    $total_count=count($results);



                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Average number of bids per contract')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A9', 'Procurement Method')
                        ->setCellValue('B9', 'Number of Procurements')
                        ->setCellValue('C9', 'Percentage of procurements')
                        ->setCellValue('D9', 'Value of procurements')
                        ->setCellValue('E9', 'Percentage of the value')
                        ->setCellValue('F9', 'Total Bids')
                        ->setCellValue('G9', 'Local Bids')
                        ->setCellValue('H9', 'Technically responsive bids')
                        ->setCellValue('I9', 'Local Technically responsive bids');

                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A9:I9")->getFont()->setBold(true);

//                    $objPHPExcel->getActiveSheet()->getStyle('C:H')->getNumberFormat()
//                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # data counter
                    $x = 10;



                    $proc_methods=get_active_procurement_methods();

                    foreach($proc_methods as $row){
                        $procurement_methods[$row['title']]=array();
                    }

//                    print_array(count($results));
//
//                     exit(print_array($procurement_methods));
                    #exit(print_array($results));

//                    print_array($this->bid_invitation_m->responsive_bids_by_contract(3856));
//
//                    exit(print_array($this->bid_invitation_m->bids_by_contract(3856,$local='',$foreign='')));

                    $all_bids=array();
                    $all_local=array();
                    $all_responsive=array();
                    $all_total_local_responsive=array();
                    foreach($proc_methods as $row)
                    {


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['title']);

                        $contracts_bucket=array();
                        $all_entry_values=array();
                        $all_method_entry_values=array();

                        $total_bids=array();
                        $total_bids_local=array();
                        $total_bids_responsive=array();
                        $total_local_responsive=array();

                        foreach($results as $contract){
                            $all_entry_values[]=$contract['normalized_actual_contract_price'];

                            if($contract['procurement_method_title']==$row['title']){
                                $all_method_entry_values[]=$contract['normalized_actual_contract_price'];
                                $contracts_bucket[]=$contract;
                                #print_array($contract['id']);
                            }
                        }
                        #filter out duplicates
                        foreach($bids as $bid){


                            if($row['title']==$bid['procurement_method_title']){
                                $total_bids[]=$bid['total_responses'];
                                $total_bids_local[]=$bid['total_local_responses'];
                                $total_local_responsive[]=$bid['total_local_responsive_bids'];
                                $total_bids_responsive[]=$bid['total_responsive_bids'];
                            }
                        }



                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,count($contracts_bucket) );


                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,number_format((count($contracts_bucket)/count($results))*100,2).'%' );

                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,number_format(array_sum($all_method_entry_values)) );

                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,number_format((array_sum($all_method_entry_values)/array_sum($all_entry_values))*100,2).'%' );


                        #print_array($total_bids);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,array_sum($total_bids));
                        $all_bids[]=array_sum($total_bids);

                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,array_sum($total_bids_local) );
                        $all_local[]=array_sum($total_bids_local);

                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,array_sum($total_bids_responsive) );
                        $all_responsive[]=array_sum($total_bids_responsive);
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,array_sum($total_local_responsive) );
                        $all_total_local_responsive[]=array_sum($total_local_responsive);


                        if($x%2==0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:I$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );
                        }

                        $x ++;

                    }
//                    exit();

                    # display totals
                    # make first rows bald
//                    print_array($all_local);
//                    print_array($all_bids);
//
//                    exit();
                    $totals_row=(count($proc_methods)+10);

                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getFont()->setBold(true);

//                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$totals_row,(array_sum($total_entries)) );
//                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$totals_row,'100%' );
//                    $objPHPExcel->getActiveSheet()->SetCellValue('D'.$totals_row,(array_sum($total_values)) );
//                    $objPHPExcel->getActiveSheet()->SetCellValue('E'.$totals_row,'100%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$totals_row,array_sum($all_bids) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.$totals_row,array_sum($all_local) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.$totals_row,array_sum($all_responsive) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.$totals_row,array_sum($all_total_local_responsive) );
//
//                    # format totals
//                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:H$totals_row")->getNumberFormat()
//                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
//

                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='average_bids '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Average number of bids")
                        ->setDescription("Auto generated report on average number of bids")
                        ->setKeywords("Reports Performance reports")
                        ->setCategory("Performance Reports");



                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(0);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                # local vs foreign
                case 'timeliness_completion':



                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }
                    # print_array($financial_year);

                    #exit(print_array($_POST));



                    #exit(print_array($results_foreign));
                    $results_all= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed = 'Y', $count = '', $nomicros = '',$within_market_value='',$nationality='');

                    $results = unique_multidim_array($results_all,'id');

                   # exit(print_array($results));



                    $total_count=count($results);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Timeliness of contract completion ')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))



                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'Subject of procurement')
                        ->setCellValue('C5', 'Details')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Cost estimate (at IFB)')
                        ->setCellValue('G5', 'Provider')
                        ->setCellValue('H5', 'Date of approval of FORM 5 by AO')
                        ->setCellValue('I5', 'Contract award date')
                        ->setCellValue('J5', 'Planned date of completion')
                        ->setCellValue('K5', 'Actual date of completion')
                        ->setCellValue('L5', 'Number of days of implementation')
                        ->setCellValue('M5', 'Contract value (UGX)')
                        ->setCellValue('N5', 'Completion status')
                        ->setCellValue('O5', 'Variation');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:N5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('K:N')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;
                    $num=0;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_in_time=array();
                    $completed_in_time_amounts=array();

                    # exit(print_array($results));

                    foreach($results as $row)
                    {
                        $num++;
                        $estimated_amount=$row['normalized_estimated_amount'];
                        $actual_amount=$row['normalized_actual_contract_price'];

                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;

                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,number_format($estimated_amount) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,$row['providernames'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,reformat_date('d M Y', $row['dateofconfirmationoffunds']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y', $row['completion_date']));
                        $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,reformat_date('d M Y', $row['actual_completion_date']));
                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x, seconds2days(strtotime($row['actual_completion_date'])- strtotime($row['date_signed']) ) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,$actual_amount  );
                       if(strtotime($row['actual_completion_date'])>strtotime($row['completion_date'])){
                            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$x,'Late' );
                        }
                        else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$x,'In time' );

                        }
                        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$x,my_date_diff($row['actual_completion_date'],$row['completion_date']));






                        # filter out completed contracts
                        if(strtotime($row['actual_completion_date'])<strtotime($row['completion_date'])){
                            $completed_in_time[]=$row;
                            $completed_in_time_amounts[]=$actual_amount;
                        }

                        $x ++;
                    }





                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:O$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('F'.$totals_row,number_format(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('M'.$totals_row,number_format(array_sum($grand_amount)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("F$totals_row:M$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='completion_timeliness '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Timeliness of Contract Completion")
                        ->setDescription("Auto generated report on Timeliness of Contract Completion")
                        ->setKeywords("Reports Performance reports")
                        ->setCategory("Performance Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Timeliness of Contract Completion')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','Total Completed Contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',array_sum($grand_amount));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );

                    #TOTAL CONTRACTS COMPLETED IN TIMEE
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Contracts Completed in time ');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($completed_in_time));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',number_format((count($completed_in_time)/count($results))*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',array_sum($completed_in_time_amounts));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',number_format((array_sum($completed_in_time_amounts)/array_sum($grand_amount))*100,2).'%' );



                    #TOTAL CONTRACTS COMPLETED LATE
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Contracts Completed late ');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($results)-count($completed_in_time));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',100-number_format((count($completed_in_time)/count($results))*100,2).'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($grand_amount)-array_sum($completed_in_time_amounts)));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',100-number_format((array_sum($completed_in_time_amounts)/array_sum($grand_amount))*100,2).'%' );



                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # procurement lead time
                case 'lead_time':

                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }
                    # print_array($financial_year);

                    #exit(print_array($_POST));



                    #exit(print_array($results_foreign));
                    $results_all= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed = '', $count = '', $nomicros = '',$within_market_value='',$nationality='');

                    $results = unique_multidim_array($results_all,'id');

                    #exit(print_array($results));



                    $total_count=count($results);


                    $objPHPExcel = new PHPExcel();

                    #  # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Procurement Lead times')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A6', 'Procurement Reference Number')
                        ->setCellValue('B6', 'Subject of procurement')
                        ->setCellValue('C6', 'Details')
                        ->setCellValue('D6', 'Method of procurement')
                        ->setCellValue('E6', 'Procurement Type')
                        ->setCellValue('F6', 'Provider')
                        ->setCellValue('G6', 'Date of approval of FORM 5 by AO')
                        ->setCellValue('H6', 'Date Signed')
                        ->setCellValue('I6', 'Planned completion date')
                        ->setCellValue('J6', 'Actual completion date')
                        ->setCellValue('K6', 'Procurement status')
                        ->setCellValue('L6', 'Contract value')
                        ->setCellValue('M6', 'Lead time (DAYS)')
                        ->setCellValue('N6', 'Estimated Amounts')
                        ->setCellValue('O6', 'Total paid amounts');

                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A6:O6")->getFont()->setBold(true);


                    # data counter
                    $x = 7;
                    $num=0;



                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();


                    $total_amount = array();



                    $expired_bids=array();
                    $expired_bids_amounts=array();

                    $procurement_types=array();

                    $pdes=array();


                    $total_estimated_amount=array();
                    $total_contract_value=array();

                    foreach($results as $row)
                    {

                        $num++;

                        $estimated_amount=$row['normalized_estimated_amount'];

                        $actual_amount=$row['normalized_actual_contract_price'];
                        $total_contract_value[]=$row['contract_value'];



                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;

                        # get procurement method id
                        if($row['procurement_method_ifb']==0){
                            $procurement_method_id=$row['procurement_method'];
                        }else{
                            $procurement_method_id=$row['procurement_method_ifb'];
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']!==''?$row['procurement_type_title']:get_procurement_method_info_by_id($row['procurement_method'],'title') );
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames'] );

                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,reformat_date('d M Y', $row['dateofconfirmationoffunds']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,reformat_date('d M Y', $row['date_signed']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,reformat_date('d M Y', $row['completion_date']) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$x,reformat_date('d M Y', $row['actual_completion_date']) );

                        # filter out completed contracts
                        if($row['actual_completion_date']){
                            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,'Completed' );
                        }else{
                            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$x,'Awarded' );
                        }


                        $objPHPExcel->getActiveSheet()->SetCellValue('L'.$x,number_format($row['normalized_actual_contract_price']));
                        $objPHPExcel->getActiveSheet()->SetCellValue('M'.$x,$row['lead_time'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('N'.$x,number_format($estimated_amount));
                        $objPHPExcel->getActiveSheet()->SetCellValue('O'.$x,number_format($row['contract_value']) );


                        $x ++;

                    }

                    # display totals
                    # make first rows bald
                    $totals_row=($total_count+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:O$totals_row")->getFont()->setBold(true);


                    # total results
                    $objPHPExcel->getActiveSheet()->SetCellValue('L'.($totals_row),(number_format(array_sum($grand_amount))) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('N'.($totals_row),number_format(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('O'.($totals_row),number_format(array_sum($total_contract_value)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:O$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='lead_time '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Procurement Lead time")
                        ->setDescription("Auto generated report on Procurement Lead Time")
                        ->setKeywords("Reports Performance reports")
                        ->setCategory("Performance Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Procurement Lead times')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))

                        ->setCellValue('A6', 'Procurement Method')
                        ->setCellValue('B6', 'Number of Contracts')
                        ->setCellValue('C6', 'Average Lead Time');

                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A6:C6")->getFont()->setBold(true);


                    # data counter
                    $x = 7;
                    $num=0;




                    $proc_methods=get_active_procurement_methods();

                    foreach($proc_methods as $row){
                        $procurement_methods[$row['title']]=array();
                    }

//                    print_array(count($results));
//
//                     exit(print_array($procurement_methods));
                    #exit(print_array($results));

                    foreach($proc_methods as $row)
                    {


                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['title']);

                        $contracts_bucket=array();
                        $lead_time=array();
                        $all_lead_times=array();
                        foreach($results as $contract){
                            $all_lead_times[]=$contract['lead_time'];

                            if($contract['procurement_method_title']==$row['title']){
                                $lead_time[]=$contract['lead_time'];


                                $contracts_bucket[]=$contract;
                            }
                        }

                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,count($contracts_bucket) );



                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x, array_sum($lead_time)==0?'-':number_format((array_sum($lead_time)/count($contracts_bucket)),2));


                        if($x%2==0){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:C$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );
                        }

                        $x ++;

                    }



                    # display totals
                    # make first rows bald
                    $totals_row=(count($proc_methods)+9);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:O$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('B'.$totals_row,count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C'.$totals_row,number_format((array_sum($all_lead_times)/count($results)),2));

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("B$totals_row:C$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');










                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;

                # implemented within_original value
                case 'implemented_within':
                    $financial_year=$this->input->post('financial_year');
                    $from=$this->input->post('from_date');
                    $to=$this->input->post('to_date');
                    $pde=$this->input->post('pde');

                    if($from&&$to){
                        $financial_year=NULL;
                    }

                    if ($this->session->userdata('isadmin') != 'Y') {
                        $pde = $this->session->userdata('pdeid');
                    }



                    $results= $this->contracts_m->get_contracts_all_awarded($from, $to, $pde,$financial_year,$completed='',$count=false,$nomicros='',$within_market_value='');

                    $results=unique_multidim_array($results,'id');

                    $contracts_within_original_value=array();
                    $all_contract_amounts=array();
                    foreach($results as $row){
                        $all_contract_amounts[]=$row['normalized_actual_contract_price'];
                        if($row['normalized_actual_contract_price']<=$row['estimated_amount_at_ifb']){
                            $contracts_within_original_value[]=$row;
                        }
                    }



                    # exit(print_array($contracts_within_original_value));



                    $total_count=count($contracts_within_original_value);


                    $objPHPExcel = new PHPExcel();

                    # Report columns

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts Implemented (within original value)')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A5', 'Procurement Reference Number')
                        ->setCellValue('B5', 'Subject of procurement')
                        ->setCellValue('C5', 'Description')
                        ->setCellValue('D5', 'Method of procurement')
                        ->setCellValue('E5', 'Procurement type')
                        ->setCellValue('F5', 'Provider')
                        ->setCellValue('G5', 'Estimated value (at IFB)')
                        ->setCellValue('H5', 'Final Contract Value')
                        ->setCellValue('I5', 'Variation amount (UGX)');


                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(30);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A5:I5")->getFont()->setBold(true);


                    $objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);




                    # data counter
                    $x = 7;

                    # totals containers
                    $grand_total_actual_payments=array();
                    $grand_amount=array();

                    # completed contracts container
                    $completed_contracts=array();
                    $completed_contracts_amounts=array();

                    $num=0;

                    # exit(print_array($results));
                    $contract_values=array();
                    $amount_differences=array();

                    $implemented_within_totals=array();

                    foreach($results as $row)
                    {
                        $num++;

                        $estimated_amount=$row['estimated_amount_at_ifb'];

                        $actual_amount=$row['normalized_actual_contract_price'];
                        $contract_values[]=$row['total_actual_payments'];
                        $amount_differences[]=$row['difference_in_amount'];



                        $grand_total_actual_payments[] = $estimated_amount;
                        $grand_amount[] = $actual_amount;



                        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['procurement_ref_no']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['subject_details'] );
                        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x,$row['procurement_type_title']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x,$row['providernames']);
                        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x,number_format($estimated_amount));
                        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x,number_format($actual_amount) );
                        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$x,number_format($row['difference_in_amount']) );

                        if($row['normalized_actual_contract_price']<=$row['estimated_amount_at_ifb']){
                            $objPHPExcel->getActiveSheet()
                                ->getStyle("A$x:I$x")
                                ->applyFromArray(
                                    array(
                                        'fill' => array(
                                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                            'color' => array('rgb' => 'CCFFFF')
                                        )
                                    )
                                );

                            $implemented_within_totals[]=$actual_amount;


                        }





                        $x ++;

                    }


                    # display totals
                    # make first rows bald
                    $totals_row=(count($results)+7);
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:P$totals_row")->getFont()->setBold(true);

                    $objPHPExcel->getActiveSheet()->SetCellValue('G'.($totals_row),number_format(array_sum($grand_total_actual_payments)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('H'.($totals_row),number_format(array_sum($grand_amount)) );
                    $objPHPExcel->getActiveSheet()->SetCellValue('I'.($totals_row),number_format(array_sum($amount_differences)) );

                    # format totals
                    $objPHPExcel->getActiveSheet()->getStyle("A$totals_row:I$totals_row")->getNumberFormat()
                        ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);


                    # generate range
                    $dynamic_title='';
                    if($financial_year){
                        $dynamic_title=$financial_year;
                    }


                    $title='Implemented Within '.$dynamic_title;

                    # set print title
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&H'.$title);
                    $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');





//                    $objPHPExcel->getProperties()->setCreator($creator);
//                    $objPHPExcel->getProperties()->setLastModifiedBy($creator);
                    $objPHPExcel->getActiveSheet()->setTitle($title);

                    $objPHPExcel->getProperties()
                        ->setSubject("Report on Contracts Implemented within original value")
                        ->setDescription("Auto generated report on Completed contracts original value")
                        ->setKeywords("Reports Performance reports")
                        ->setCategory("Performance Reports");




                    // Create a new worksheet, after the default sheet
                    $objPHPExcel->createSheet();

// Add some data to         the second sheet, resembling some different data types
                    $objPHPExcel->setActiveSheetIndex(1);
                    $objPHPExcel->getActiveSheet()
                        ->setCellValue('A1', 'PDE:')
                        ->setCellValue('B1', $this->input->post('pde')?get_pde_info_by_id($this->input->post('pde'),'title'):'ALL')

                        ->setCellValue('A2', 'REPORT:')
                        ->setCellValue('B2', 'Contracts Implemented within original value')
                        # generate range

                        ->setCellValue('A3', $financial_year?'FINANCIAL YEAR:':'REPORTING PERIOD:')
                        ->setCellValue('B3', $financial_year?'From: '.$financial_year:$from.'      To:     '.$to)

                        ->setCellValue('A4', 'CREATED BY:')
                        ->setCellValue('B4', get_user_info_by_id($this->session->userdata('userid'),'fullname'))


                        ->setCellValue('A7', '')
                        ->setCellValue('B7', 'Number')
                        ->setCellValue('C7', 'Percentage by number')
                        ->setCellValue('D7', 'Amount (UGX)')
                        ->setCellValue('E7', 'Percentage by amount');



                    # wrap text
                    $objPHPExcel->getDefaultStyle()->getAlignment()->setWrapText(true);

                    # set default column width
                    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(40);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A1:A4")->getFont()->setBold(true);

                    # make first rows bald
                    $objPHPExcel->getActiveSheet()->getStyle("A7:E7")->getFont()->setBold(true);



//                            $objPHPExcel->getActiveSheet()->getStyle('G:H')->getNumberFormat()
//                                ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);



                    #TOTAL CONTRACTS
                    $objPHPExcel->getActiveSheet()->SetCellValue('A8','All Contracts');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B8',count($results));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C8','-' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D8',number_format(array_sum($all_contract_amounts) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E8','-' );


                    #TOTAL CONTRACTS WITHIN MARKET VALUE
                    $objPHPExcel->getActiveSheet()->SetCellValue('A9','Contracts Implemented within original value');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B9',count($implemented_within_totals));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C9',number_format(((count($implemented_within_totals))/count($results))*100,2) .'%' );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D9',number_format(array_sum($implemented_within_totals) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E9',number_format(((array_sum($implemented_within_totals)/array_sum($all_contract_amounts))*100),2) .'%');


                    #TOTAL CONTRACTS ABOVE MARKET VALUE
                    $objPHPExcel->getActiveSheet()->SetCellValue('A10','Total Completed contracts above market value');
                    $objPHPExcel->getActiveSheet()->SetCellValue('B10',count($results)-count($implemented_within_totals));
                    $objPHPExcel->getActiveSheet()->SetCellValue('C10',number_format(100-((count($implemented_within_totals))/count($results))*100,2) .'%'  );
                    $objPHPExcel->getActiveSheet()->SetCellValue('D10',number_format(array_sum($grand_amount)-array_sum($implemented_within_totals) ));
                    $objPHPExcel->getActiveSheet()->SetCellValue('E10',number_format(100-((array_sum($implemented_within_totals)/array_sum($all_contract_amounts))*100),2) .'%' );
                    // Rename 2nd sheet
                    $objPHPExcel->getActiveSheet()->setTitle('Summary');




                    # Set active sheet index to the first sheet, so Excel opens this as the first sheet
                    $objPHPExcel->setActiveSheetIndex(1);


                    # Redirect output to a client’s web browser (Excel5)
                    header('Content-Type: application/vnd.ms-excel');
                    header('Content-Disposition: attachment;filename="'.$title.'.xls"');
                    header('Cache-Control: max-age=0');

                    # If you're serving to IE 9, then the following may be needed
                    header('Cache-Control: max-age=1');

                    # If you're serving to IE over SSL, then the following may be needed
                    header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                    header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
                    header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
                    header ('Pragma: public'); // HTTP/1.0


                    $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
                    ob_end_clean();
                    ob_start();
                    $objWriter->save('php://output');
                    break;



                default:
                    $data['errors']='Select a report type';
            }
        }else{
            redirect(base_url().'user/dashboard');
        }



    }




}