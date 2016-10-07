<?php
#print_r($page_list);
?>
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
 
     
  

<div class="row clearfix current_tenders  search_results">
    <div class="column col-md-13">

        <?php
     #   if (isset($details)) {
            

 if(!empty($page_list['page_list']))
            {
          

            print ' <div class="col-md-3">
                                <b>Subject of Disposal </b>
                            </div>

                            <div class="col-md-3">
                                <b>Quantity</b>
                            </div>
                            
                            <div class="col-md-3">
                                <b>The Entity </b>
                            </div>

                            <div class="col-md-3">
                                <b>Disposal Method </b>
                            </div>


                            <div class="col-md-2"></div>
                        </div><hr>';

            #print_r($page_list['page_list']['disp_plan']);
                        
            foreach ($page_list['page_list'] as $row) {
            
            print '<div class="row">' .
                    '<div class="col-md-3">' . $row['subject_of_disposal'] . '</div>' .
                    '<div class="col-md-3 procurement_pde"> '.number_format($row['quantity'] > 0 ? $row['quantity'] : 0 ).' </div>' .
                    '<div class="col-md-3 ">' . $row['pdename'] . '</div>' .
                    '<div class="col-md-3">' . $row['method'] . '</div>' .
                    '</div>' .
                    '<hr>';
            }

            print '<div class="pagination pagination-mini pagination-centered">'.
                        pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().
                        "page/disposal_plans/p/%d/level/search")
                        .'</div>';



          }

  else
            {
                print format_notice("ERROR: There are no Disposal Plans");
            }
            
         
        ?>
    </div>
</div>