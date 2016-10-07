<div class="page-header col-lg-offset-2" style="margin:0">
    <h3>Current Addenda</h3>
    <div class="container">
	 
		<div class="col-md-13 column">
            <div class="row">
                <div class="col-md-8">
                    <label class="control-label" style="width:130px">Subject of procurement:</label>
                    &nbsp;
                    <div class="controls" style="display:inline-block">
                        <?=((!empty($bid_details['subject_of_procurement']))? $bid_details['subject_of_procurement'] : '<i>[N/A]</i>' )?>
                    </div>
                </div>          
            </div>
             
             <div class="row">
             	<div class="col-md-8">
                    <label class="control-label" style="width:130px">Procurement Ref. No.:</label>
                    &nbsp;
                    <div class="controls" style="display:inline-block">
                        <?=((!empty($bid_details['procurement_ref_no']))? $bid_details['procurement_ref_no'] : '<i>[N/A]</i>' )?>
                    </div>
                </div>           
            </div>	 
		</div>
	</div>
</div>
<div class="row clearfix current_tenders">
	<div class="column">
		<?php
            if(!empty($page_list))
            {
                print '<div class="row">
                            <div class="col-md-1">
                                <b>#</b>
                            </div>
							<div class="col-md-2">
                                <b>Date posted</b>
                            </div>
                            <div class="col-md-2">
                                <b>Title</b>
                            </div>
                            <div class="col-md-2">
								<b>&nbsp;</b>
							</div>
                        </div><hr>';
                        
				$current_date = '';
						
                foreach($page_list as $row)
                {					
                    print '<div class="row">'.
                            '<div class="col-md-1">'. $row['refno'] . '</div>'.	
                            '<div class="col-md-2">'. custom_date_format('d M, Y', $row['dateadded']). '</div>'.						
                            '<div class="col-md-2">'. $row['title'] . '</div>'.
                            '<div class="col-md-2"><a target="_blank" class="btn btn-xs btn-primary" href="'. base_url() .
							'uploads/documents/addenda/'. $row['fileurl'] .'">Download</a></div>'.
                            '</div>'.
                            '<hr>';
                }
            }
            else
            {
				print format_notice("ERROR: There are no addenda for this procurement");
            }
        ?>
	</div>
</div>