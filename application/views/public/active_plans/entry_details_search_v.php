


<?php
if(!empty($searchresult['page_list']))
{

 
?>
          <!-- Export Button --> 
          <div class="row ">
             <div class="col-md-2 pull-right ">
              <a href="<?=base_url().'page/search_procurement/level/export';?>" class="btn btn-default">Download</a> 
              </div>
         </div>

        <!-- Records --> 
        <div class="row ">

          
                              <div class="col-md-2">
                                <b>Procuring/Disposing Entity</b>
                            </div>

                            <div class="col-md-1">
                                <b>Financial Year </b>
                            </div>

                             <div class="col-md-1">
                                <b>Quantity</b>
                            </div>
                            
                              <div class="col-md-1">
                                <b>Subject of Procurement </b>
                            </div>
                            
                             <div class="col-md-2">
                                <b>Procurement Type</b>
                            </div>
                            <div class="col-md-1">
                                <b>Procurement Method</b>
                            </div>                           
                            <div class="col-md-2">
                                <b>Source of Funds</b>
                            </div>
                               

                              <div class="col-md-2">
                                <b>Estimated Cost</b>
                            </div>
                            <!--  <div class="col-md-1">
                             <b>Date Posted</b>
                            </div> -->
                            
            </div><hr> 

           
            <?php
           # print_r($searchresult['page_list']);
               foreach ($searchresult['page_list'] as $key => $row) {
           
                ?>
             

              <!-- Results -->
              <div class="row">
              <div class="col-md-2 procurement_pde" ><?=$row['pdename']; ?></div>
              <div class="col-md-1 "> <?=$row['financial_year']; ?></div>
              <div class="col-md-1"><?=number_format($row['quantity']); ?></div>
              <div class="col-md-1 procurement_pde"> <?=$row['subject_of_procurement']; ?> </div>
              <div class="col-md-2 procurement_subject"> <?=$row['procurementtype']; ?> </div>
              <div class="col-md-1"> <?=$row['procurementmethod']; ?> </div>
              <div class="col-md-2" > <?=$row['funding_source']; ?> </div>
              <div class="col-md-2"> <?=number_format($row['estimated_amount']).' '.$row['currency']; ?>  </div>

        
              </div>
              <!-- End Results -->
               
              <hr> 
            

            <?php
            }
            
             print '<div class="pagination pagination-mini pagination-centered">'.
                        pagination($this->session->userdata('search_total_results'), $searchresult['rows_per_page'], $searchresult['current_list_page'], base_url().
                        "page/procurement_plans/p/%d/level/search")
                        .'</div>';
        }
        else
        {
          ?>
          <div class="alert alert-error"><button class="close" data-dismiss="alert">×</button> There are no records found</div>
          <?php
        }
        ?>

 
     
 
    
    
 
    
 
    
    
 

  

     
     


     


     


     
 