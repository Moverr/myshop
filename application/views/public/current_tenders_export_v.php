<ol class="breadcrumb">
    <li>
        <a href="<?= base_url() . 'page/home' ?>">Back To  All Current Tenders</a>
    </li>

</ol>
<div class="widget widget-table">
    <div class="btn-group pull-right">
        <button class="btn btn-danger dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i> Export Data
        </button>
        <ul class="dropdown-menu">


            <li><a href="#" onClick="$('#customers2').tableExport({type:'excel',escape:'false'});"><img
                        src='<?= base_url() ?>assets/img/icons/xls.png' width="24"/> XLS</a></li>
            <li><a href="#" onClick="$('#customers2').tableExport({type:'doc',escape:'false'});"><img
                        src='<?= base_url() ?>assets/img/icons/word.png' width="24"/> Word</a></li>

        </ul>
    </div>


    <div class="widget-content">

        <table id="customers2" class="table table-sorting table-striped table-hover datatable" cellpadding="0"
               cellspacing="0"
               width="100%">
            <thead>
            <tr>
                <th>Date Posted</th>

                <th>Procuring / Disposing Entity</th>
                <th>Subject Of Procurement</th>
                <th>Procurement Type</th>
                <th>Deadline</th>
            </tr>

            </thead>
            <tbody>
            <?php
           # print_r($page_list);

            foreach ($page_list['page_list'] as $entry => $record) {
              #  print_r($record);
               # if (get_procurement_plan_entry_info_reference_number($record['procurement_ref_no'], 'pde') != '') {
                    ?>
                    <tr>

                        <td><?= custom_date_format('d / F / Y', $record['dateadded']); ?></td>
                        <td><?= $record['pdename']; ?></td>
                        <td><?= $record['subject_of_procurement']; ?></td>
                        <td><?= $record['procurement_type']; ?></td>
                        <td><?= custom_date_format('d / F / Y', $record['bid_submission_deadline']); ?></td>
                    </tr>
                <?php
               # }

            }
            ?>
            </tbody>
        </table>
    </div>
</div>