<?php


//if there are errors
if (isset($errors)) {
    echo error_template($errors);
} else {

    if (isset($notes)) {
        echo info_template($notes);
    }

    $contract_value = array();
    $pdes = array();
    $market_prices = array();

    $result_call_off_orders=$call_offs;
    $result_call_off_amounts=array();
    foreach($call_offs as $call_off){
        $result_call_off_amounts[]=$call_off['total_actual_payments'];
    }

    foreach ($results as $row) {
        $contract_value[] = $row['amount'] * $row['xrate'];
        if (!in_array($row['pdeid'], $pdes)) {
            $pdes[] = $row['pdeid'];
        }


    }


    //=========all contracts in this period================
    //get to contract value
    $all_contract_value = array();
    $all_pdes = array();
    $all_market_prices = array();


    //print_array($all_lots);
    $lot_contracts = array();
    $lot_amounts = array();
    foreach ($all_lots as $row) {
        $lot_amounts[] = $row['amount'] * $row['xrate'];
        if (!in_array($row['procurement_ref_no'], $lot_contracts)) {
            $lot_contracts[] = $row['procurement_ref_no'];
        }
    }

    //print_array($lot_contracts);


    //====================grand totals=============
    $total_number_of_contracts = count($lot_contracts) + count($all_contracts);
    $total_amounts = array_sum($all_contract_value) + array_sum($lot_amounts);


}
?>
<!-- TAB NAVIGATION -->
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#tab1" role="tab" data-toggle="tab">Summary</a></li>
    <li><a href="#tab2" role="tab" data-toggle="tab">Details</a></li>


</ul>
<!-- TAB CONTENT -->
<div class="tab-content">
    <div class="active tab-pane fade in" id="tab1">


        <div id="print_this">
            <?php
            if($this->session->userdata('isadmin')=='Y'){
                echo $this->input->post('pde')?'<p><b>'.get_pde_info_by_id($this->input->post('pde'),'title').'</b></p>':'';
            }else{
                echo '<p><b>'.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</b></p>';

            }
            ?>

            <table class="table table-responsive " id="vertical-1">

                <?php

                ?>
                <h2><?= $report_heading ?> <br>
                    <small>Financial Year : <?= $financial_year ?></small>
                </h2>
                <b>Reporting period : </b><?= $reporting_period ?>
                <thead>
                <th></th>
                <th>Number</th>

                <th>Amount (UGX)</th>

                </thead>


                <?php
                if ($this->input->post('pde')) {
                    ?>
                    <tr>
                        <th>PDE</th>
                        <td><?= get_pde_info_by_id($this->input->post('pde'), 'title') ?></td>

                    </tr>
                <?php
                }
                ?>

                <tr>
                    <th>Total Contracts</th>
                    <td><?= count($results)+count($result_call_off_orders) ?></td>

                    <td><?= number_format(($total_amounts) + array_sum($result_call_off_amounts)) ?></td>

                </tr>






            </table>


        </div>


        <p>

            <a class="btn" href="#" onclick="printContent('print_this')"> PRINT </a>
        </p>


    </div>
    <div class="tab-pane fade" id="tab2">


        <div id="print_excel_area">
            <h3>
                Financial Year : <?= $financial_year ?>
            </h3>
            Reporting Period : <?= $reporting_period ?>


            <p>

            <div class="page-header text-center">
                <?php
                if($this->session->userdata('isadmin')=='Y'){
                    echo $this->input->post('pde')?'<p><b>'.get_pde_info_by_id($this->input->post('pde'),'title').'</b></p>':'';
                }else{
                    echo '<p><b>'.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</b></p>';

                }
                ?>
                <?= $report_heading ?>
            </div>
            <table id="" class="display table table-hover dt-responsive table-mc-light-blue dt-responsive table-mc-light-blue dt-responsive ">
                <thead>
                <tr>
                    <th>Procurement Reference Number</th>
                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        ?>
                        <th>PDE</th>
                    <?php
                    }
                    ?>

                    <th>Subject of procurement</th>
                    <th>Method of procurement</th>
                    <th>Provider</th>
                    <th>Date of award of contract</th>
                    <th>Market price of the procurement</th>
                    <th>Contract value (UGX)</th>


                </tr>
                </thead>

                <tbody>
                <?php
                $grand_total_actual_payments = array();
                $grand_amount = array();
                //print_array($results);

                foreach ($call_offs as $call_off){
                    $grand_total_actual_payments[] = $call_off['total_actual_payments'];
                    $grand_amount[] = $call_off['contract_value'];
                    ?>

                    <tr >
                        <td>
                            <?= $call_off['call_off_order_no'] ?>
                        </td>
                        <?php
                        if ($this->session->userdata('isadmin') == 'Y') {
                            ?>
                            <td><?= $call_off['pdename'] ?></td>
                            <?php
                        }
                        ?>
                        <td>
                            <?= $call_off['subject_of_procurement'] ?>
                        </td>

                        <td>
                            <strong>Call off order</strong>
                        </td>

                        <td>
                            <?= $call_off['providernames'] ?>
                        </td>



                        <td>
                            <?= custom_date_format('d M Y', $call_off['date_of_calloff_orders']) ?>
                        </td>
                        <td style="text-align: right;">
                            -
                        </td>

                        <td style="text-align: right;">
                            <?= number_format($call_off['contract_value']) ?>
                        </td>

                    </tr>
                    <?

                }

                foreach ($results as $row) {

                    //print_array($this->contracts_m->contracts_with_call_off_orders($row['id']));
                    $grand_total_actual_payments[] = $row['estimated_amount'];
                    $grand_amount[] = $row['amount'] * $row['xrate'];

                    //get unified procurement method
                    $procurement_method_id='';
                    if($row['procurement_method_ifb']==0){
                        $procurement_method_id=$row['procurement_method'];
                    }else{
                        $procurement_method_id=$row['procurement_method_ifb'];
                    }


                    ?>
                    <tr <?=count(get_call_off_orders_by_contract($row['id'],'awarded'))>0?'class="info"':''?>>
                        <td>
                            <?= $row['procurement_ref_no'] ?>
                        </td>
                        <?php
                        if ($this->session->userdata('isadmin') == 'Y') {
                            ?>
                            <td><?= $row['pdename'] ?></td>
                        <?php
                        }
                        ?>
                        <td>
                            <?= $row['subject_of_procurement'] ?>
                            <p>
                                <?php
                                if($row['lotid']>0){
                                    ?>
                                    <strong class="text text-info"><small>LOT</small></strong>
                                    <?php
                                }
                                ?>
                            </p>

                        </td>
                        <td>
                            <?=count(get_call_off_orders_by_contract($row['id'],'awarded'))>0?'<strong>'.get_procurement_method_info_by_id($procurement_method_id,'title').'<br>(Framework Contract)</strong>':get_procurement_method_info_by_id($procurement_method_id,'title')?>

                        </td>
                        <td><?= $row['providernames'] ?></td>


                        <td>
                            <?= custom_date_format('d M Y', $row['date_signed']) ?>
                        </td>
                        <td style="text-align: right;">
                            <?= number_format($row['estimated_amount']) ?>
                        </td>

                        <td style="text-align: right;">
                            <?php
                            if($row['lotid']==0){
                                echo number_format($row['amount']*$row['xrate'] );
                            }else{
                                echo '-';
                            }
                            ?>

                        </td>


                    </tr>
                <?php
                }
                ?>
                <tr>
                    <td></td>
                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        ?>
                        <td></td>
                    <?php
                    }
                    ?>
                    <td></td>

                    <td></td>
                    <td></td>

                    <td></td>
                    <td style="border-top: 1px solid #000; text-align: right; ">
                        <b><?= number_format(array_sum($grand_total_actual_payments)) ?></b></td>
                    <td style="border-top: 1px solid #000; text-align: right;">
                        <b><?= number_format(array_sum($grand_amount)) ?></b></td>

                </tr>


                </tbody>
            </table>

            <b>Total results: <?= count($results)+count($result_call_off_orders) ?></b>
            </p>


            <hr>

            <p>

            <p>
                <b>Declaration</b>
            </p>

            <p>I hereby certify that the above information is a true and accurate record of the procurement and disposal
                contracts
                undertaken by the entity </p>

            <div  class="row">
                <div class="span6">
                    <b>Name:......................................</b><br>
                    <b>Signature:.................................</b>
                </div>

                <div class="span5 pull-right">
                    <b>Title:......................................</b><br>
                    <b>Date:.......................................</b>
                </div>
            </div>


        </div>

        <p>

            <a class="btn " href="#" onClick="$('.display').tableExport({type:'excel',escape:'false'});"> Export</a>
            <a class="btn" href="#" onclick="printContent_excel('print_excel_area')"> PRINT </a>
        </p>


    </div>
</div>
<script>
    function printContent_excel(el) {
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }

    function printContent(el){
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }

</script>