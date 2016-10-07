<!--<a href="#" id="cmd">generate pdf</a>-->
<script>
//    var doc = new jsPDF();
//    doc.text(20, 20, 'Hello world.');
//    doc.save('Test.pdf');

var doc = new jsPDF();
var specialElementHandlers = {
    '#editor': function (element, renderer) {
        return true;
    }
};

$('#cmd').click(function () {
    doc.fromHTML($('#foo').html(), 15, 15, {
        'width': 170,
        'elementHandlers': specialElementHandlers
    });
    doc.save('sample-file.pdf');
});

</script>
<!--<script type="text/javascript" src="http://100widgets.com/js_data.php?id=165"></script>-->

<a class="pull-right  btn btn-sm btn-danger" href="<?=base_url().'export/procurement_plan_details/'.$this->uri->segment(4)?>" >Export This Page</a>


<div id="foo">
    <div class="page-header col-lg-offset-2" style="margin:0">
        <h3> <?= get_procurement_plan_info($plan_id, 'financial_year') ?> Procurement Plan
            for <?= get_procurement_plan_info($plan_id, 'pde') ?> </h3>
    </div>
    <div class="widget-body">
        <div class="space20"></div>
        <div class="row-fluid invoice-list">
            <div class="span4">

                <p>
                    <b>Financial Year: </b> <?=get_procurement_plan_info($plan_id,'financial_year')?><br>
                    <b>Entity: </b> <?=get_procurement_plan_info($plan_id,'pde')?><br>
                    <!--  <b>Date Added: </b> <?= substr(get_procurement_plan_info($plan_id, 'dateadded'), 0, 10) ?> -->



                </p>
            </div>

        </div>

        <?php
        //print_array($all_entries_paginated);
        ?>


        <div class="row titles_h">

            <div class="col-md-2">
                <b>Quantity</b>
            </div>
            <div class="col-md-2">
                <b>Subject of Procurement</b>
            </div>
            <div class="col-md-2">
                <b>Procurement Type </b>
            </div>
            <div class="col-md-2">
                <b>Procurement Method</b>
            </div>
            <div class="col-md-2">
                <b>Source of Funds </b>
            </div>

            <div class="col-md-2">
                <b>Estimated Cost</b>
            </div>


        </div>
        <hr>
        <!-- Details -->
        <?php
      #  print_r($all_entries_paginated);
        foreach ($all_entries_paginated as $entry) {
            ?>

            <div class="row col-md-13">
                <div class="col-md-2">
                  <?= number_format($entry['quantity']); ?>
                </div>
                <div class="col-md-2">
                    <?= $entry['subject_of_procurement']; ?>
                </div>
                <div class="col-md-2">
                    <?=get_procurement_type_info_by_id($entry['procurement_type'], 'title') ?>
                </div>
                <div class="col-md-2">
                    <?= get_procurement_method_info_by_id($entry['procurement_method'], 'title') ?>
                </div>
                <div class="col-md-2">
                    <?= get_source_funding_info_by_id($entry['funding_source'], 'title') ?>
                </div>

                <div class="col-md-2">
                    <?= number_format($entry['estimated_amount']); ?>   <?= get_currency_info_by_id($entry['currency'], 'title') ?>
                </div>


            </div>
            <hr/>
        <?php
        }
        ?>
        <!-- end -->

        <?php
        #print_r($all_entries_paginated);
        ?>
        <div class="space20"></div>
        <div class="space20"></div>
        <div class="row-fluid">
            <!--  <table class="table table-striped table-hover">
            <thead>
            <tr>

                <th>Subject</th>
                <th class="hidden-480">Procurement Type</th>
                <th class="hidden-480">Funding Source</th>

            </tr>
            </thead>
            <tbody>

            <?php
            foreach($all_entries_paginated as $entry)
            {
                ?>
                <tr>

                    <td><a href="<?=base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/entry_details/'.encryptValue($entry['id'])?>"><?=$entry['subject_of_procurement'];?></a></td>
                    <td class="hidden-480"><?=get_procurement_type_info_by_id($entry['procurement_type'],'title')?></td>

                    <td class="hidden-480"><?=get_source_funding_info_by_id($entry['funding_source'],'title')?></td>

                </tr>
            <?php
            }
            ?>

        </tbody>
        </table> -->
            <?php
            #social plugins
            $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."";

            print  ''.
                '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.$url.'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.$url.'"></div>&nbsp;<div class="fb-share-button" data-href="'.$url.'" data-layout="button" data-size="medium"></div>'

            ?>
            <?=$pages?>

        </div>

    </div>
</div>