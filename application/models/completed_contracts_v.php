<?php
/**
 * Created by PhpStorm.
 * User: EMMA
 * Date: 6/11/15
 * Time: 4:05 AM
 */
//print_array('foo');
?>

<div class="">

<!-------->
<div id="" class=" span12">
<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
    <?php
    if (isset($graph_view)) {
        ?>
        <li><a href="#report_graphic" data-toggle="tab">Report graphic</a></li>
    <?php
    }
    ?>
    <li class="active"><a href="#report_summary" data-toggle="tab">Report summary</a></li>

    <li><a href="#report_details" data-toggle="tab">Completed Contracts</a></li>



</ul>
<div id="my-tab-content" class="tab-content">
<div class="tab-pane active" id="report_summary">


    <div id="print_this">
        <?php

        //print_array(count($contracts_awarded));
        /*
         * [0] => Array
        (
            [completion_date] => 2015-06-03
            [contract_amount] =>
            [final_contract_value] =>
            [total_actual_payments] =>
            [actual_completion_date] =>
            [isactive] => Y
            [subject_of_procurement] => Procurement of office tables_2015
            [funding_source] => 2
            [id] => 24
            [pdename] => New Wave Technologies Ltd
            [title] => Open Domestic Bidding
        )
         */
        //print_array($results);
        $total_amounts = array();
        $total_actual_payments = array();
        $total_pdes = array();
        $_methods = array();
        $contracts_completed = array();

        foreach ($results as $row) {

            //echo $val['amount'].'<br>';
            $total_amounts[] = $row['amount'] * $row['xrate'];

            if (!in_array($row['pdename'], $total_pdes)) {
                $total_pdes[] = $row['pdename'];
            }

            if (!in_array($row['procurement_method'], $_methods)) {
                $_methods[] = $row['procurement_method'];
            }

            if ($row['actual_completion_date'] != '') {
                $contracts_completed[] = $row;
            }
        }


        $contracts_completed_total_amounts = array();
        $contracts_completed_payments = array();
        $contracts_completed_pdes = array();
        $contracts_completed_methods = array();

        foreach ($contracts_awarded as $key => $val) {
            $contracts_completed_total_amounts[] = $val['amount'] * $val['xrate'];

            if (!in_array($val['pdename'], $contracts_completed_pdes)) {
                $contracts_completed_pdes[] = $val['pdename'];
            }

            if (!in_array($val['procurement_method'], $contracts_completed_methods)) {
                $contracts_completed_methods[] = $val['procurement_method'];
            }
        }


        ?>
        <?php
        if ($this->session->userdata('isadmin') == 'Y') {
            echo $this->input->post('pde') ? '<p><b>' . get_pde_info_by_id($this->input->post('pde'), 'title') . '</b></p>' : '';
        } else {
            echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

        }
        ?>
        <table class="table table-responsive " id="vertical-1">

            <h3><?= $report_heading ?> </h3>
            <thead>
            <th></th>
            <th>Number</th>
            <th>Percentage by number</th>
            <th>Amount</th>
            <th>Percentage by amount</th>
            </thead>

            <tr>
                <th>Financial year</th>

                <td><?= $financial_year ?></td>
                <td></td>
                <td></td>
                <td></td>

            </tr>

            <tr>
                <th>Reporting Period</th>

                <td><?= $reporting_period ?></td>
                <td></td>
                <td></td>
                <td></td>

            </tr>
            <?php
            if ($this->input->post('pde')) {
                ?>
                <tr>
                    <th>PDE</th>

                    <td><?= get_pde_info_by_id($this->input->post('pde'), 'title') ?></td>
                    <td></td>
                    <td></td>
                    <td></td>

                </tr>
            <?php
            }
            ?>


            <tr>
                <th>Total Contracts </th>

                <td class="number"><?= count($contracts_completed) + count($contracts_awarded) ?></td>
                <td>-</td>
                <td><span
                        style="text-align: right;"><?= number_format(array_sum($total_amounts) + array_sum($contracts_completed_total_amounts)) ?> </span>
                </td>
                <td>-</td>
            </tr>

            <tr>
                <th>Contracts completed</th>

                <td><?= count($results) ?></td>
                <td><?= $results ? round_up(((count($results) / (count($results) + count($contracts_awarded)) * 100)), 2) : '0' ?>
                    %
                </td>
                <td><span
                        style="text-align: right;"><?= number_format(array_sum($total_amounts)) ?> </span>
                </td>
                <td><?= count($total_amounts) > 0 ? round_up((array_sum($total_amounts) / (array_sum($total_amounts) + array_sum($contracts_completed_total_amounts))) * 100, 2) : '0' ?>
                    %
                </td>
            </tr>


        </table>


    </div>
    <p>

        <a class="btn" href="#" onclick="printContent('print_this')"> Print </a>
    </p>



</div>

<?php
if (isset($graph_view)) {
    ?>
    <div class="tab-pane active" id="report_graphic">

        <?= $this->load->view($graph_view) ?>
    </div>
<?php
}
?>
<div class="tab-pane" id="yellow">
    <h1>Yellow</h1>
    <p>yellow yellow yellow yellow yellow</p>
</div>
<div class="tab-pane" id="report_details">
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
            } else {
                echo '<p><b>' . get_pde_info_by_id($this->session->userdata('pdeid'), 'title') . '</b></p>';

            }
            ?>
            <?= $report_heading ?>
        </div>
        <table id="" class="display table table-hover dt-responsive table-mc-light-blue dt-responsive ">
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
                <th>Procurement type</th>
                <?php
                if ($this->session->userdata('isadmin') == 'Y') {
                    ?>
                    <th>Provider</th>
                <?php
                }

                ?>
                <th>Planed date of completion</th>
                <th>Date of Actual completion</th>
                <th>Contract value (UGX)</th>
                <th>Completion status (DAYS)</th>


            </tr>
            </thead>

            <tbody>
            <tr>
                <th></th>

                <?php
                if ($this->session->userdata('isadmin') == 'Y') {
                    ?>
                    <th></th>
                <?php
                }

                ?>


                <th></th>
                <th></th>
                <th></th>
                <?php
                if ($this->session->userdata('isadmin') == 'Y') {
                    ?>
                    <th></th>
                <?php
                }

                ?>
                <th></th>
                <th></th>
                <th></th>
                <th></th>


            </tr>
            <?php
            $grand_total_actual_payments = array();
            $total = array();
            //print_array($all_contracts_in_this_year );
            foreach ($results as $row) {

                $grand_amount[] = $row['amount'] * $row['amount'];
                ?>
                <tr>
                    <td>
                        <?= $row['procurement_ref_no'] ?>
                    </td>

                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        ?>
                        <td> <?= $row['pdename'] ?></td>
                    <?php
                    }

                    ?>
                    <td>
                        <?= $row['subject_of_procurement'] ?>
                    </td>

                    <td>
                        <?= $row['procurement_method_title']?>
                    </td>
                    <td>
                        <?= $row['procurement_type_title']?>

                    </td>
                    <?php
                    if ($this->session->userdata('isadmin') == 'Y') {
                        ?>
                        <td>
                            <?= get_beb_by_bid($row['bidinvitation_id'],'title') ?>
                        </td>
                    <?php
                    }
                    ?>



                    <td>
                        <?= custom_date_format('d M, Y', $row['completion_date']) ?>
                    </td>

                    <td>
                        <?= $row['actual_completion_date'] == '' ? '-' : custom_date_format('d M, Y', $row['actual_completion_date']) ?>
                    </td>

                    <td>
                        <?php
                        $total[] = $row['amount'] * $row['xrate'];
                        ?>
                        <?= number_format($row['amount'] * $row['xrate']) ?>
                    </td>

                    <td><?= strtotime($row['completion_date']) >= strtotime($row['actual_completion_date']) ? '<div class="text-success">Completed in time : by <b>(' . seconds_to_days((-strtotime($row['actual_completion_date']) + strtotime($row['completion_date']))) . ')</b></div>' : '<div style="color: #E74955;">Completed late : by <br> <b>(' . seconds_to_days((strtotime($row['actual_completion_date']) - strtotime($row['completion_date']))) . ')</b></div>' ?></td>

                </tr>
            <?php
            }

            //print_array($grand_amount)
            ?>
            <tr>
                <td></td>

                <?php
                if ($this->session->userdata('isadmin') == 'Y') {
                    ?>
                    <td></td>
                    <td></td>
                <?php
                }
                ?>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="border-top: 1px solid #000; "><b><?= number_format(array_sum($total)) ?></b></td>

            </tr>
            </tbody>
        </table>

        <b>Total results: <?= count($results) ?></b>
        </p>

        <hr>

        <p>

        <p>
            <b>Declaration</b>
        </p>

        <p>I hereby certify that the above information is a true and accurate record of the procurement and disposal
            contracts
            undertaken by the entity </p>

        <div class="row">
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

<div class="tab-pane" id="blue">
    <h1>Blue</h1>
    <p>blue blue blue blue blue</p>
</div>
</div>
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