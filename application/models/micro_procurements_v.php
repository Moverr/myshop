<?php
//if there are errors
if (isset($errors)) {
    echo error_template($errors);
} else {

    if (isset($notes)) {
        echo info_template($notes);
    }
    //print_array($all_post_params);

    print_array($results);

    //get to contract value
    $contract_value = array();
    $pdes = array();
    $market_prices = array();
    $contracts_completed = array();
    $completed_micro_procurements = array();
    $completed_micro_procurements_value = array();
    $awarded_micro_procurements = array();
    $awarded_micro_procurements_value = array();

    foreach ($results as $row) {
        if ($row['actual_completion_date'] != '') {
            $completed_micro_procurements[] = $row;
            $completed_micro_procurements_value[] = $row['final_contract_value'] * $row['xrate'];
        } else {
            $awarded_micro_procurements[] = $row;
            $awarded_micro_procurements_value[] = $row['amount'] * $row['xrate'];
        }
        $contracts_completed[] = $row['id'];
        $contract_value[] = $row['amount'] * $row['xrate'];
        if (!in_array($row['pdeid'], $pdes)) {
            $pdes[] = $row['pdeid'];
        }
        $market_prices[] = $row['total_actual_payments'];


    }

    //print_array($completed_micro_procurements);


    //print_array($all_lots);
    $lot_contracts = array();
    $lot_amounts = array();
    foreach ($all_lots as $row) {
        $lot_amounts[] = $row['amount'] * $row['xrate'];
        if (!in_array($row['procurement_ref_no'], $lot_contracts)) {
            $lot_contracts[] = $row['procurement_ref_no'];
        }
    }


    //====================grand totals=============
    $total_number_of_contracts = count($lot_contracts) + count($results);
    $total_amounts = array_sum($contract_value) + array_sum($lot_amounts);





}
?>
<!-- TAB NAVIGATION -->
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#tab1" role="tab" data-toggle="tab">Summary</a></li>
    <li><a href="#tab2" role="tab" data-toggle="tab"> Micro procurements</a></li>



</ul>
<!-- TAB CONTENT -->
<div class="tab-content">
<div class="active tab-pane fade in" id="tab1">


    <div id="print_this">
        <?php
        if ($this->session->userdata('isadmin') == 'Y') {
            echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
        } else {
            echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

        }
        ?>
        <table class="table table-responsive " id="vertical-1">
            <h2><?= $report_heading ?> <br>
                <small>Financial Year : <?= $financial_year ?></small>
            </h2>
            <b>Reporting period : </b><?= $reporting_period ?>
            <thead>
            <th></th>
            <th>Number</th>
            <th>Percentage by number</th>
            <th>Amount (UGX)</th>
            <th>Percentage by Amount</th>
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



            <tr style="border-bottom: 1px solid #3e3e3e;">
                <th>All awarded Micro Procurements</th>
                <td><?= count($results) ?></td>
                <td>-</td>
                <td><?= number_format(array_sum($contract_value)) ?></td>
                <td>-</td>

            </tr>


                <tr >
                    <th>Completed Micro procurements</th>
                    <td><?= count($completed_micro_procurements) ?></td>
                    <td><?= (count($results)) > 0 ? round_up((count($completed_micro_procurements) / (count($results))) * 100, 3) : '0' ?>
                        %
                    </td>
                    <td><?= number_format(array_sum($completed_micro_procurements_value)) ?></td>
                    <td><?= array_sum($contract_value) > 0 ? round_up((array_sum($completed_micro_procurements_value) / (array_sum($contract_value))) * 100, 2) . '%' : '-' ?></td>

                </tr>


                <tr>
                    <th>Incomplete Micro procurements</th>
                    <td><?= count($results)-count($completed_micro_procurements) ?></td>
                    <td><?= 100- ((count($results)) > 0 ? round_up((count($completed_micro_procurements) / (count($results))) * 100, 3) : '0') ?>
                        %
                    </td>
                    <td><?= number_format(array_sum($awarded_micro_procurements_value)) ?></td>
                    <td><?= array_sum($contract_value) > 0 ? round_up((array_sum($awarded_micro_procurements_value) / ($total_amounts)) * 100, 2) . '%' : '-' ?></td>

                </tr>



            <hr>






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
            if ($this->session->userdata('isadmin') == 'Y') {
                echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
            }
            else {
                echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

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
                <th>Provider</td>
                <th>Date signed</th>
                <th>Status</th>
                <th>Date completed</th>
                <th>Contract value (UGX)</th>


            </tr>
            </thead>

            <tbody>
            <?php
            $grand_total_actual_payments = array();
            $grand_amount = array();
            //print_array($results);
            foreach ($results as $row) {
                $grand_total_actual_payments[] = $row['total_actual_payments'];
                $grand_amount[] = $row['amount'] * $row['xrate'];
                ?>
                <tr>
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
                    </td>

                    <td>
                        <?=$row['providernames']?>
                    </td>

                    <td>
                        <?= custom_date_format('d.F.Y', $row['date_signed']) ?>
                    </td>
                    <td>
                        <?php
                        if ($row['actual_completion_date'] != '') {
                            ?>
                            <span class="label label-success">Completed</span>
                            <?php
                        }else{

                            ?>
                            <span class="label label-danger">Awarded</span>
                            <?php
                        }
                        ?>
                    </td>

                    <td>
                        <?= custom_date_format('d.F.Y', $row['actual_completion_date']) ?>
                    </td>
                    <td style="text-align: right;">
                        <?= number_format($row['amount'] * $row['xrate']) ?>
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
                <td></td>

                <td style="border-top: 1px solid #000;text-align: right; ">
                    <b><?= number_format(array_sum($grand_amount)) ?></b></td>

            </tr>


            </tbody>
        </table>
        <b>Total results: <?= count($completed_micro_procurements) ?></b>
        </p>

        <hr>

        <p>

        <p>
            <b>Declaration</b>
        </p>

        <p>I hereby certify that the above information is a true and accurate record of the procurement and disposal
            contracts
            undertaken by the entity
        </p>

        <div class="row">
            <div  class="span6">
                <b>Name:......................................</b><br>
                <b>Signature:.................................</b>
            </div>

            <div class="span5 pull-right">
                <b>Title:......................................</b><br>
                <b>Date:.......................................</b>
            </div>
        </div>


    </div>
    <p >
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
        document.getElementById("printControls").style.visibility = "hidden";
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }

</script>