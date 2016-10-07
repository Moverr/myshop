<ol class="breadcrumb">
    <li>
        <a href="<?=base_url().'page/procurement_plans/details/'.$this->uri->segment(3)?>">Back To Procurement Plans</a>
    </li>

</ol>
<div class="widget widget-table">
    <div  class="btn-group pull-right">
        <button class="btn btn-danger dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i> Export Data
        </button>
        <ul class="dropdown-menu">


            <li><?=$anchor; ?></li>
            
        </ul>
    </div>
    <div class="page-header col-lg-offset-2" style="margin:0">
        <h3> <?= get_procurement_plan_info($plan_id, 'financial_year') ?> Procurement Plan
            for <?= get_procurement_plan_info($plan_id, 'pde') ?> </h3>
    </div>

    <div class="widget-content">

        <table id="customers2" class="table table-sorting table-striped table-hover datatable" cellpadding="0" cellspacing="0"
               width="100%">
            <thead>
             <tr>
                <th>Quantity</th>
                <th>Subject Of Procurement</th>
                <th>Procurement Type</th>
                <th>Procurement Method</th>
                <th>Source of Funds</th>
                <th>Estimated Cost</th>
            </tr>

            </thead>
            <tbody>
            <?php
                foreach ($all_entries_paginated as $entry) {
                    ?>
                    <tr>
                        <td><?= number_format($entry['quantity']); ?></td>
                        <td><?= $entry['subject_of_procurement']; ?></td>
                        <td><?= get_procurement_type_info_by_id($entry['procurement_type'], 'title') ?></td>
                        <td><?= get_procurement_method_info_by_id($entry['procurement_method'], 'title') ?></td>
                        <td><?= get_source_funding_info_by_id($entry['funding_source'], 'title') ?></td>
                        <td style="text-align: right;"><?= number_format($entry['estimated_amount']); ?>   <?= get_currency_info_by_id($entry['currency'], 'title') ?></td>
                    </tr>
            <?php
                }
            ?>
            </tbody>
        </table>
    </div>
</div>