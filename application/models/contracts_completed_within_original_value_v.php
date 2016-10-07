<?php
/**
 * Created by PhpStorm.
 * User: EMMA
 * Date: 6/2/15
 * Time: 1:14 PM
 */
//print_array($results);
//print_array($all_contracts_in_this_year);

//print_array($all_contracts_in_this_year);

$total_contract_value=array();

$total_annual_contract_value=array();

$total_pdes=array();
$total_annual_pdes=array();

$total_pdes_above_contract_value=array();

$required_ids=array();



//print_array($all_contracts_in_this_year);
$contracts_above_original_value=array();
$contracts_above_original_value_amounts=array();

$above_contract_value = array();
$within_original_value = array();
$contracts_with_no_variation = array();


$contracts_with_no_variation_amounts = array();
$within_original_value_amounts = array();
$total_pdes_above_contract_value = array(); 


    //final_contract_value
#$row['final_contract_value']

#total_price

     
 




    /*
        PDES  COUNT 
    */
   foreach( $all_contracts_in_this_year as $row){

 # print_r($row);
   
        $providers = get_provider_by_receipt_id('14283');

       

        $variation_records =   get_variation($row['id']);
        $variation_contract_value = 0;
        if(!empty($variation_records))
        {
            # print_r($variation_records);
            $variation_contract_value = $variation_records[0]['price'];
            $new_planned_date_of_completion = $variation_records[0]['new_planned_date_of_completion'];

            $row['completion_date'] =  $new_planned_date_of_completion;
                                
        }




        if(strtotime($row['actual_completion_date'])>strtotime($row['completion_date']))
        {
            $row['final_contract_value'] =  $row['final_contract_value'];
        }
        else
        {
             $row['final_contract_value'] = $row['total_price'] + $variation_contract_value;
        }



 $total_annual_contract_value[]=$row['final_contract_value'];



    if(!in_array($row['pdeid'],$total_annual_pdes)){
        $total_annual_pdes[]=$row['pdeid'];
    }


    if ($row['final_contract_value'] > $row['estimated_amount']) {
      //TODO CHECK THIS LATER
        $above_contract_value[]=$row;
        $contracts_above_original_value_amounts[] =$row['final_contract_value'];

        if(!in_array($row['pdeid'],$total_pdes_above_contract_value)){
            $total_pdes_above_contract_value[]=$row['pdeid'];
        }

    }


    if ($row['final_contract_value'] < $row['estimated_amount']) {//TODO CHECK THIS LATER
        $within_original_value[] = $row;
        $within_original_value_amounts[]=$row['final_contract_value'];

    }

    if ($row['final_contract_value'] == $row['estimated_amount']) {//TODO CHECK THIS LATER
       
            $contracts_with_no_variation[] = $row;
            $contracts_with_no_variation_amounts[]=$row['final_contract_value'];

        


    }


}


//print_array($results);


foreach ($within_original_value as $row) {




        $variation_records =   get_variation($row['id']);
        $variation_contract_value = 0;
        if(!empty($variation_records))
        {
            # print_r($variation_records);
            $variation_contract_value = $variation_records[0]['price'];
            $new_planned_date_of_completion = $variation_records[0]['new_planned_date_of_completion'];

            $row['completion_date'] =  $new_planned_date_of_completion;
                                
        }




        if(strtotime($row['actual_completion_date'])>strtotime($row['completion_date']))
        {
            $row['final_contract_value'] =  $row['final_contract_value'];
        }
        else
        {
             $row['final_contract_value'] = $row['total_price'] + $variation_contract_value;
        }


    $total_contract_value[] = $row['final_contract_value'];
    $required_ids[] = $row['id'];

    //get contracts completed within original congract value


    if (!in_array($row['pdeid'], $total_pdes)) {
        $total_pdes[] = $row['pdeid'];
    }


}

$results = $within_original_value;



?>
<div class="">

    <!-------->
    <div id="">
        <ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
            <li class="active"><a href="#report_summary" data-toggle="tab">Report summary</a></li>

            <li><a href="#report_details" data-toggle="tab">Contracts implemented within original value</a></li>
            <li><a href="#report_details_above" data-toggle="tab">Contracts implemented above original value</a></li>
            <li><a href="#report_details_no_variation" data-toggle="tab">Contracts with No variation</a></li>
            <?php
          /*  if(isset($graph_view)){
                ?>
                <li><a href="#report_graphic" data-toggle="tab">Report graphic</a></li>
            <?php
            } */
            ?>
        </ul>
        <div id="my-tab-content" class="tab-content">
            <div class="tab-pane active" id="report_summary">


                <div id="print_this">
                    <?php
                    if($this->session->userdata('isadmin')=='Y'){
                        echo $this->input->post('pde')?'<p><b>'.get_pde_info_by_id($this->input->post('pde'),'title').'</b></p>':'';
                    }else{
                        echo '<p><b>'.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</b></p>';

                    }
                    ?>
                    <table class="table table-responsive " id="vertical-1">
                        <h3><?=$report_heading?> </h3>

                        <thead>
                        <th></th>
                        <th>Number</th>
                        <th>Percentage by number</th>
                        <th>Amount (UGX)</th>
                        <th>Percentage by amount</th>
                        </thead>

                        <tr>
                            <th>Financial year</th>
                            <td><?=$financial_year?></td>
                            <td></td>
                            <td></td>
                            <td></td>

                        </tr>

                        <tr>
                            <th>Reporting Period</th>
                            <td><?=$reporting_period?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <?php
                        if($this->input->post('pde')){
                            ?>
                            <tr>
                                <th>PDE</th>
                                <td><?=get_pde_info_by_id($this->input->post('pde'),'title')?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php
                        }
                        ?>
                        <tr>
                            <th>Total Contracts</th>
                            <td class="number"><?=count($all_contracts_in_this_year)?></td>
                            <td >-</td>
                            <td><?=number_format(array_sum($total_annual_contract_value))?></td>
                            <td>-</td>



                        </tr>

                    <!-- Contracts Within Original Value  Summary   -->
                        <tr>
                            <th>Contracts within original value</th>
                            <td ><?=count($within_original_value)?></td>
                            <td>
                                <?php
                                if(count($all_contracts_in_this_year)){
                                    ?>
                                    <span>
                                    <?= number_format(count($within_original_value)/ count($all_contracts_in_this_year) * 100,2); 
                                    ?>
                                 %</span>
                                <?php
                                }
                                ?>
                            </td>
                            <td >
                                <span >
                                <?= number_format(array_sum($within_original_value_amounts)); ?>
                                </span>
                          
                            </td>

                            <td>
                                 <span >
                                <?= number_format(array_sum($within_original_value_amounts)/ array_sum($total_annual_contract_value) * 100,2);
                                ?>
                                    %
                                </span>
                            </td>

                        </tr>


                        <!-- Contracts Above Origival Value  Summary  -->
                        <tr>
                            <th>Contracts above original value</th>
                            <td ><?=count($above_contract_value)?></td>
                            <td>

                                <?php
                                if(count($all_contracts_in_this_year)){
                                    ?>
                                    <span>
                                    <?=number_format(count($above_contract_value)/count($all_contracts_in_this_year) * 100,2);
                 
                                        ?> %</span>
                                <?php
                                }
                                ?>
                            </td>
                            <td >

                            <?=number_format(array_sum($contracts_above_original_value_amounts));
                             ?>
                               

                            </td>

                            <td>

                               <?= 
                               number_format( array_sum($contracts_above_original_value_amounts)/
                                array_sum($total_annual_contract_value) * 100,2);
                                ?> %
                            
                            </td>
                        </tr>



 <!-- Contracts Above Origival Value  Summary  -->
                        <tr>
                            <th>Contracts With No Variations </th>
                            <td ><?=count($contracts_with_no_variation)?></td>
                            <td>

                                <?php
                                if(count($all_contracts_in_this_year)){
                                    ?>
                                    <span>
                                    <?=number_format(count($contracts_with_no_variation)/count($all_contracts_in_this_year) * 100,2);
                 
                                        ?> %</span>
                                <?php
                                }
                                ?>
                            </td>
                            <td >

                            <?=number_format(array_sum($contracts_with_no_variation_amounts));
                             ?>
                               

                            </td>

                            <td>

                               <?= number_format(
                                array_sum($contracts_with_no_variation_amounts)/
                                array_sum($total_annual_contract_value) * 100,2);
                                ?> %
                            
                            </td>
                        </tr>




                    </table>

                </div>
                <p>

                    <a class="btn " href="#" onclick="printContent('print_this')">  PRINT </a>
                </p>



            </div>

            <?php
            if(isset($graph_view)){
                ?>
                <div class="tab-pane" id="report_graphic">

                    <?=$this->load->view($graph_view)?>
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
                        if($this->session->userdata('isadmin')=='Y'){
                            echo $this->input->post('pde')?'<p><b>'.get_pde_info_by_id($this->input->post('pde'),'title').'</b></p>':'';
                        }else{
                            echo '<p><b>'.get_pde_info_by_id($this->session->userdata('pdeid'),'title').'</b></p>';

                        }
                        ?>
                        <?= $report_heading ?>
                    </div>
                    <table  id=""  class="display table table-hover dt-responsive table-mc-light-blue dt-responsive ">
                        <thead>
                        <tr>
                            <?php
                            if($this->session->userdata('isadmin')=='Y'){
                                ?>
                                <th>PDE</th>
                            <?php
                            }

                            ?>
                            <th class="hidden-480">Procurement ref.no</th>
                            <th class="hidden-480">Subject of procurement</th>
                            <th class="hidden-480">Method of procurement</th>
                            <th>Procurement type</th>
                            <th>Provider</th>

                            <th class="hidden-480">Estimated Amount<br>(UGX)</th>

                            <th class="hidden-480">Final Contract value<br>(UGX)</th>

                            <th class="hidden-480">Variation<br>(UGX)</th>


                        </tr>
                        </thead>
                        <?php
                       // print_array($results);
                        $total_value=array();
                        $total_final_contract_value=array();
                        $total_variation = array();
                        foreach($results  as $row){

                            $total_value[] = $row['estimated_amount'];
                            $total_final_contract_value[] = $row['final_contract_value'];
                            $total_variation[] = ($row['final_contract_value'] - $row['estimated_amount']);

                            ?>
                            <tr>

                                <?php
                                if($this->session->userdata('isadmin')=='Y'){
                                    ?>
                                    <td><?= get_pde_info_by_id($row['pdeid'], 'title') ?></td>
                                <?php
                                }

                                ?>
                                <td><?= $row['procurement_ref_no'] ?></td>
                                <td><?= $row['subject_of_procurement'] ?></td>
                                <td><?= $row['procurement_method_title'] ?></td>
                                <td>
                                   <?= get_procurement_type_info_by_id($row['procurement_type'],'title') ?>
                                </td>

                                <td>
                                    <?= $row['providernames'] ?>
                                </td>

                                <td style="text-align: right;"><?= number_format($row['estimated_amount']) ?></td>
                                <td style="text-align: right;"><?= $row['final_contract_value'] == '' ? '0' : number_format($row['final_contract_value']) ?></td>


                                <td style="text-align: right;">


                                    <?= in_array($row['id'], $required_ids) ? '<div class="text-success">' . number_format($row['estimated_amount'] - $row['final_contract_value']) . '</div>' : '<div style="color: #E74955;">Above Contract Value</div>' ?></td>


                            </tr>
                        <?php
                        }

                        //print_array(array_sum($total_value));

                        //print_array(array_sum($total_final_contract_value))



                        ?>

                        <tr>
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

                            <th></th>
                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_value)) ?></th>

                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_final_contract_value)) ?></th>

                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_variation)) ?></th>


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

                    <div  class="row">
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
                <p>

                    <a class="btn " href="#" onClick="$('.display').tableExport({type:'excel',escape:'false'});"> Export</a>
                    <a class="btn" href="#" onclick="printContent_excel('print_excel_area')"> PRINT </a>
                </p>

            </div>
            <div class="tab-pane" id="report_details_above">
                <div id="print_excel_area_2">
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
                        CONTRACTS IMPLEMENTED ABOVE ORIGINAL VALUE
                    </div>
                    <table  id=""  class="display table table-hover dt-responsive table-mc-light-blue dt-responsive ">
                        <thead>
                        <tr>
                            <?php
                            if($this->session->userdata('isadmin')=='Y'){
                                ?>
                                <th>PDE</th>
                            <?php
                            }

                            ?>
                            <th class="hidden-480">Procurement ref.no</th>
                            <th class="hidden-480">Subject of procurement</th>
                            <th class="hidden-480">Method of procurement</th>
                            <th>Procurement type</th>

                            <th>Provider</th>
                            <th class="hidden-480">Estimated Amount<br>(UGX)</th>

                            <th class="hidden-480">Final Contract value<br>(UGX)</th>

                            <th class="hidden-480">Variation<br>(UGX)</th>


                        </tr>
                        </thead>
                        <?php
                        //print_array($all_contracts_in_this_year);
                        $total_value=array();
                        $total_final_contract_value=array();
                        $total_variation = array();
                        foreach($above_contract_value  as $row){

                            $total_value[] = $row['estimated_amount'];
                            $total_final_contract_value[] = $row['final_contract_value'];

                            in_array($row['id'], $required_ids) ? '' : $total_variation[] = ($row['final_contract_value'] - $row['estimated_amount']);

                            ?>
                            <tr>

                                <?php
                                if($this->session->userdata('isadmin')=='Y'){
                                    ?>
                                    <td><?= get_pde_info_by_id($row['pdeid'], 'title') ?></td>
                                <?php
                                }

                                ?>
                                <td><?= $row['procurement_ref_no'] ?></td>
                                <td><?= $row['subject_of_procurement'] ?></td>
                                <td><?= $row['procurement_method_title'] ?></td>
                                <td>
                                  <?= get_procurement_type_info_by_id($row['procurement_type'],'title') ?>
                                </td>

                                <td>
                                    <?= $row['providernames'] ?>
                                </td>

                                <td style="text-align: right;"><?= number_format($row['estimated_amount']) ?></td>
                                <td style="text-align: right;"><?= number_format($row['final_contract_value']) ?></td>


                                <td style="text-align: right;">


                                    <?= in_array($row['id'], $required_ids) ? '<div class="text-success">Within Contract Value</div>' : '<div style="color: #E74955;">' . number_format($row['final_contract_value'] - $row['estimated_amount']) . '</div>' ?></td>


                            </tr>
                        <?php
                        }

                        //print_array(array_sum($total_value));

                        //print_array(array_sum($total_final_contract_value))



                        ?>

                        <tr>
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

                            <th></th>
                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_value)) ?></th>

                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_final_contract_value)) ?></th>

                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_variation)) ?></th>


                        </tr>




                        </tbody>
                    </table>
                    <b>Total results: <?= count($above_contract_value) ?></b>
                    </p>

                    <hr>

                    <p>

                    <p>
                        <b>Declaration</b>
                    </p>

                    <p>I hereby certify that the above information is a true and accurate record of the procurement and disposal
                        contracts
                        undertaken by the entity</p>

                    <div  class="row">
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
                <p>

                    <a class="btn " href="#" onClick="$('.display').tableExport({type:'excel',escape:'false'});"> Export</a>
                    <a class="btn" href="#" onclick="printContent_excel('print_excel_area_2')"> PRINT </a>
                </p>

            </div>
            <div class="tab-pane" id="report_details_no_variation">
                <div id="print_excel_area_2_no_variation">
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
                        CONTRACTS IMPLEMENTED WITH ORIGINAL VALUE AND HAVE NO VARIATION IN PRICE
                    </div>
                    <table id="" class="display_no_variation table table-hover dt-responsive table-mc-light-blue dt-responsive ">
                        <thead>
                        <tr>
                            <?php
                            if ($this->session->userdata('isadmin') == 'Y') {
                                ?>
                                <th>PDE</th>
                            <?php
                            }

                            ?>
                            <th class="hidden-480">Procurement ref.no</th>
                            <th class="hidden-480">Subject of procurement</th>
                            <th class="hidden-480">Method of procurement</th>
                            <th>Procurement type</th>

                            <th>Provider</th>
                            <th class="hidden-480">Estimated Amount<br>(UGX)</th>

                            <th class="hidden-480">Final Contract value<br>(UGX)</th>

                            <th class="hidden-480">Variation<br>(UGX)</th>


                        </tr>
                        </thead>
                        <?php
                        //print_array($all_contracts_in_this_year);
                        $total_value = array();
                        $total_final_contract_value = array();
                        $total_variation = array();
                        foreach ($contracts_with_no_variation as $row) {

                            $total_value[] = $row['estimated_amount'];
                            $total_final_contract_value[] = $row['final_contract_value'];

                            in_array($row['id'], $required_ids) ? '' : $total_variation[] = ($row['final_contract_value'] - $row['estimated_amount']);

                            ?>
                            <tr>

                                <?php
                                if ($this->session->userdata('isadmin') == 'Y') {
                                    ?>
                                    <td><?= get_pde_info_by_id($row['pdeid'], 'title') ?></td>
                                <?php
                                }

                                ?>
                                <td><?= $row['procurement_ref_no'] ?></td>
                                <td><?= $row['subject_of_procurement'] ?></td>
                                <td><?= $row['procurement_method_title']?></td>
                                <td>
                                   <?= get_procurement_type_info_by_id($row['procurement_type'],'title') ?>
                                </td>

                                <td>
                                    <?= $row['providernames'] ?>
                                </td>

                                <td style="text-align: right;"><?= number_format($row['estimated_amount']) ?></td>
                                <td style="text-align: right;"><?= number_format($row['final_contract_value']) ?></td>


                                <td style="text-align: right;">


                                    <?= in_array($row['id'], $required_ids) ? '<div class="text-success">Within Contract Value</div>' : '<div style="color: #E74955;">' . number_format($row['final_contract_value'] - $row['estimated_amount']) . '</div>' ?></td>


                            </tr>
                        <?php
                        }

                        //print_array(array_sum($total_value));

                        //print_array(array_sum($total_final_contract_value))


                        ?>

                        <tr>
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

                            <th></th>
                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_value)) ?></th>

                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_final_contract_value)) ?></th>

                            <th style="text-align: right; border-top: 1px solid #000;"><?= number_format(array_sum($total_variation)) ?></th>


                        </tr>


                        </tbody>
                    </table>
                    <b>Total results: <?= count($contracts_with_no_variation) ?></b>
                    </p>

                    <hr>

                    <p>

                    <p>
                        <b>Declaration</b>
                    </p>

                    <p>I hereby certify that the above information is a true and accurate record of the procurement and
                        disposal
                        contracts
                        undertaken by the entity</p>

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

                    <a class="btn " href="#"
                       onClick="$('.display_no_variation').tableExport({type:'excel',escape:'false'});"> Export</a>
                    <a class="btn" href="#" onclick="printContent_excel('print_excel_area_2_no_variation')">
                        PRINT </a>
                </p>

            </div>
            <div class="tab-pane" id="blue">
                <h1>Blue</h1>
                <p>blue blue blue blue blue</p>
            </div>
        </div>
    </div>


</div> <!-- container -->
<script>
    function printContent_excel(el) {
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }

    function printContent(el) {
        var restorepage = document.body.innerHTML;
        var printcontent = document.getElementById(el).innerHTML;
        document.body.innerHTML = printcontent;
        window.print();
        document.body.innerHTML = restorepage;
    }


</script>