 <a class="pull-right  btn btn-sm btn-danger"
                       href="<?= base_url() . 'page/published_lbas/level/export' ?>">Export This Page</a> 

  <?php

       
      if(!empty($page_list['page_list']))
      {

            $current_date = '';

              $dated = '';

          foreach ($page_list['page_list'] as $key => $row) {
            # code...


             if($current_date != custom_date_format('d M, Y', $row['dateadded']))
              {
					  if($dated == custom_date_format('d M, Y', $row['dateadded']))
					  {
						  print '<div class="row"><div class=" tender_date">'.
						   '</div>';
					  }
					else{
								  print '<div class="row"><div class=" tender_date">' .
										'<b style="font-size:30px; col-md-6">Posted on '. custom_date_format('d M, Y', $row['dateadded']).
										'</b>   </div>';
						}
                      $dated =   custom_date_format('d M, Y', $row['dateadded']);
                }

      ?>

 


				  <div class="row">
				  <div class="col-md-3 procurement_pde"><?=$row['pdename']; ?> </div>
				  <div class="col-md-3 procurement_subject"><?=$row['subject_of_disposal']; ?> &nbsp;<br>
					
				  </div>
				  <div class="col-md-3 procurement_pde"><strong><?=$row['providernames']; ?></strong>  </div>
				  <div class="col-md-3"><strong><?=custom_date_format('d M, Y', $row['dateadded']); ?> </strong> </div> 
				 


				 </div>

      <?php

        $current_date = custom_date_format('d M, Y', $row['dateadded']);
    }

      print '<div class="pagination pagination-mini pagination-centered">'.
                       pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().
                       "page/published_lbas/p/%d")
                       .'</div>';

}
else
 print format_notice("ERROR: There are no Published LBAs ");



?>



  
 
