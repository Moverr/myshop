<ol class="breadcrumb">
    <li>
        <a href="<?=base_url().'page/disposal_plans/details/'.$this->uri->segment(3)?>">Back to Disposal Plans</a>
    </li>

</ol>
<div class="widget widget-table">
    <div  class="btn-group pull-right">
        <button class="btn btn-danger dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i> Export Data
        </button>
        <ul class="dropdown-menu">


            <li><a href="#" onClick="$('#customers2').tableExport({type:'excel',escape:'false'});"><img
                        src='<?= base_url() ?>assets/img/icons/xls.png' width="24"/> XLS</a></li>
            <li><a href="#" onClick="$('#customers2').tableExport({type:'doc',escape:'false'});"><img
                        src='<?= base_url() ?>assets/img/icons/word.png' width="24"/> Word</a></li>

        </ul>
    </div>
    <div class="widget-header">
    
     </div>

    <div class="widget-content">

        <table id="customers2" class="table table-sorting table-striped table-hover datatable" cellpadding="0" cellspacing="0"
               width="100%">
            <thead>
            <tr>
            <th> Financial Year </th>
                <th>Subject of disposal</th>
                <th>Quantity</th>
                <th>Entity</th>
                <th>Disposal Method</th>
            </tr>

            </thead>
            <tbody>
            <?php
           
           # print_r($page_list['page_list']);
            foreach ($page_list['page_list'] as $key => $record) {
               # print_r($record);
                ?>
                <tr>
                <td> <?= $record['financial_year']; ?></td>


                    <td><?= $record['subject_of_disposal']; ?></td>
                    <td> <?= !empty($record['quantity']) ? number_format($record['quantity']) : '-'; ?> </td>
                    <td><?=$record['pdename']; ?></td>
                    <td><?=get_disposal_method_info_by_id($record['method_of_disposal'], 'title') ?></td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>