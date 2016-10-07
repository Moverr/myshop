<style>
.pagination ul{}
.pagination ul li{ list-style: none;   float: left;}
.pagination ul li a{text-decoration: none; padding:6px;display: block;border:2px solid #eee;}
.bet{background: none repeat scroll 0 0 black;border: medium none;border-radius: 2px;
color: white;display: inline-block;font-size: 14px;padding: 6px 10px;transition: all 0.2s ease-out 0s;
cursor: pointer;line-height: normal;}
</style>
<style type="text/css">
    .searchengine {margin:0; background:#efefef;border:1px solid #e1e1e1; }
    .searchengine > .searchengine-header {}
    .searchengine  > .content {display: none;}
</style>

<script>
 
</script>
<!-- start -->
    <div class="clearfix content-wrapper" style="padding-top:28px;">
        <div class="col-md-13" style="margin:0 auto">
            <div class="clearfix">
                
<div class="searchstatus">
</div>

<div class="row clearfix current_tenders">

    <a class="pull-right  btn btn-sm btn-danger"
                       href="<?= base_url() . 'page/home/level/export' ?>">Export This Page</a>


    <div class="column">
        <?php
      #  print_r($page_list);
#echo "RAC";
            if(!empty($page_list))
            { 
                print '<div class="row   ">
                            <div class="col-md-3">
                                <b>Procuring/Disposing Entity</b>
                            </div>
                            <div class="col-md-3">
                                <b>Subject of Procurement</b>
                            </div>
                            <div class="col-md-2">
                                <b>Procurement Type</b>
                                  <b> [Procurement Method] </b>
                            </div>

                            <div class="col-md-2">
                                <b>Deadline</b>
                            </div>
                            <div class="col-md-2">
                                <b>&nbsp;</b>
                            </div>
                               <div class="col-md-2">

                            </div>
                        </div><hr>';

                $current_date = '';

                foreach($page_list as $row)
                {
                    if($current_date != custom_date_format('d M, Y', $row['bid_date_approved']))
                    {
                        print '<div class="row"><div class=" tender_date">' .
                              '<b style="font-size:30px; col-md-6">Posted on '. custom_date_format('d M, Y', $row['bid_date_approved']).
                              '</b>   </div>';
                    }

                    print '<div class="row">'.
                        '<div class="col-md-3 procurement_pde">' . $row['pdename'] . '</div>' .
                        '<div class="col-md-3 procurement_subject">' . $row['subject_of_procurement'] . '&nbsp;' .
                            ($row['numOfAddenda'] > 0?'<span><a class="btn btn-xs btn-success" href="'. base_url() .
                            'page/addenda_list/a/'. encryptValue($row['bidinvitation_id']) .'">View addenda</a></span>' : '').
                            '<BR/><div class=""><a class="btn btn-xs btn-primary" href="'. base_url() .
                            'page/bid_details/i/'. encryptValue($row['bidinvitation_id']) .'">View details</a></div>'.
                        '</div>' .

                            '<div class="col-md-2 procurement_pde"><strong>'. $row['procurement_type'] . '</strong> ['. $row['procurement_method'].'] </div>'.

                        '<div class="col-md-2"><strong>'. custom_date_format('d M, Y', $row['bid_submission_deadline']) . '</strong> at <strong> ' . custom_date_format('h:i A', $row['bid_submission_deadline']) .'</strong></div>'.
                              '<div class="col-md-2">'.
                              '<ul style="list-style:none;"><li>'.
                            '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.base_url() .
                            'page/bid_details/i/'. encryptValue($row['bidinvitation_id']).'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a></li><li> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.base_url().'page/bid_details/i/'. encryptValue($row['bidinvitation_id']).'
"></div></li><li><div class="fb-share-button" data-href="'. base_url() .
                            'page/bid_details/i/'. encryptValue($row['bidinvitation_id']) .'" data-layout="button" data-size="medium"></div></li></ul>
 '.
                              '</div>'.
                             '</div>  '.


                        '<hr>';

                    $current_date = custom_date_format('d M, Y', $row['bid_date_approved']);
                }

                  print '<div class="pagination pagination-mini pagination-centered">'.
                       pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().
                       "page/home/p/%d/level/search")
                       .'</div>';
            }
            else
            {
                if(!empty($formdata)):
                    print format_notice("ERROR: Your search criteria did not match any records");
                else:
                    print format_notice("ERROR: There are no active tenders");
                endif;

            }
        ?>
    </div>
</div>