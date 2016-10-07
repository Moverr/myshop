<div class="widget">
    <div class="widget-title">
        <h4><i class="icon-reorder"></i>&nbsp;Report panel</h4>
            <span class="tools">
                <a href="javascript:;" class="icon-chevron-down"></a>
                <a href="javascript:;" class="icon-remove"></a>
            </span>
    </div>
    <div class="widget-body">
        <!-- BEGIN FORM-->
        <form action="<?=base_url() . 'reports/invitation_for_bids_reports'?>" enctype="multipart/form-data" method="post" class="ifb_reports form-horizontal">
        	<div class="control-group">
                <label class="control-label">Report type<span>*</span></label>
                <div class="controls">
                    <select id="ifb-report-type" class="input-large m-wrap" name="ifb_report_type" tabindex="2">
                       <option selected="selected">-Select-</option>
                       <option value="PIFB" <?=(!empty($formdata['ifb_report_type']) && $formdata['ifb_report_type'] == 'PIFB'? 'selected="selected"' : '' )?>>Published IFBs</option>
                       <option value="IFBD" <?=(!empty($formdata['ifb_report_type']) && $formdata['ifb_report_type'] == 'IFBD'? 'selected="selected"' : '' )?>>Bid submission deadlines</option>
                       <option value="BER" <?=(!empty($formdata['ifb_report_type']) && $formdata['ifb_report_type'] == 'BER'? 'selected="selected"' : '' )?>>Bid exception reports</option>
                    </select>
                </div>
            </div>
            
            <div class="control-group exception_control" <?=(!empty($formdata['ifb_report_type']) && $formdata['ifb_report_type'] == 'BER'? 'style="display:block"' : '' )?>>
                <label class="control-label">Exception type<span>*</span></label>
                <div class="controls">
                    <select id="exception-type" class="input-large m-wrap" name="exception_type" tabindex="2">
                       <option selected="selected">-Select-</option>
                       <option value="GTT" <?=(!empty($formdata['exception_type']) && $formdata['exception_type'] == 'GTT'? 'selected="selected"' : '' )?>>Later than bidding period deadline</option>
                       <option value="LTT" <?=(!empty($formdata['exception_type']) && $formdata['exception_type'] == 'LTT'? 'selected="selected"' : '' )?>>Earlier than bidding period deadline</option>
                       <option value="ETT" <?=(!empty($formdata['exception_type']) && $formdata['exception_type'] == 'ETT'? 'selected="selected"' : '' )?>>On time</option>
                    </select>
                </div>
            </div>
            
            	<?php if($this->session->userdata('isadmin') == 'Y'): ?>
            	<div class="control-group">
                    <label class="control-label">Select PDE <span>*</span></label>
                    <div class="controls">
                        <select id="pde" name="pde" class="input-large m-wrap" tabindex="1">
                        	<?=get_select_options($pdes, 'pdeid', 'pdename', (!empty($formdata['pde'])? $formdata['pde'] : '' ))?>   
                        </select>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="control-group">
                    <label class="control-label">Financial year:</label>
                    <div class="controls">
                        <select id="financial-year" class="input-large m-wrap" name="financial_year" tabindex="2">
                            <?=get_select_options($financial_years, 'fy', 'label', (!empty($formdata['financial_year'])? $formdata['financial_year'] : '' ))?> 
                        </select>
                    </div>
                </div>
                
                <div class="control-group published_ifb_control" <?=(!empty($formdata['ifb_report_type']) && $formdata['ifb_report_type'] == 'PIFB'? 'style="display:block"' : '' )?>>
                    <label class="control-label">Date IFB published:</label>
                    <div class="controls">
                        <div class="input-append date date-picker" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                            <input name="ifb_from_date" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days" placeholder="From" class="m-ctrl-medium date-picker input-small" type="text" value="<?=(!empty($formdata['ifb_from_date'])? $formdata['ifb_from_date'] : '' )?>" />
                            <span class="add-on"><i class="icon-calendar"></i></span>
                        </div>
                        <div class="input-append date date-picker" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                            <input name="ifb_to_date"  placeholder="To" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium input-small date-picker" type="text" value="<?=(!empty($formdata['ifb_to_date'])? $formdata['ifb_to_date'] : '' )?>" />
                            <span class="add-on"><i class="icon-calendar"></i></span>
                        </div>
                    </div>
                </div> 
                
                <div class="control-group bid_submission_control" <?=(!empty($formdata['ifb_report_type']) && $formdata['ifb_report_type'] == 'IFBD'? 'style="display:block"' : '' )?>>
                    <label class="control-label">Bid submission deadline:</label>
                    <div class="controls">
                        <div class="input-append date date-picker" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                            <input name="bsd_from_date" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days" placeholder="From" class="m-ctrl-medium date-picker input-small" type="text" value="<?=(!empty($formdata['bsd_from_date'])? $formdata['bsd_from_date'] : '' )?>" />
                            <span class="add-on"><i class="icon-calendar"></i></span>
                        </div>
                        <div class="input-append date date-picker" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days">
                            <input name="bsd_to_date"  placeholder="To" data-date="2015-01-01" data-date-format="yyyy-mm-dd" data-date-viewmode="days" class="m-ctrl-medium input-small date-picker" type="text" value="<?=(!empty($formdata['bsd_to_date'])? $formdata['bsd_to_date'] : '' )?>" />
                            <span class="add-on"><i class="icon-calendar"></i></span>
                        </div>
                    </div>
                </div>                                           
            <div class="form-actions">
                <button type="submit" name="view" value="view-report" class="btn blue"><i class="icon-ok"></i> View report</button>
                <button type="submit" name="view_pdf" value="view-pdf" class="btn"><i class="icon-file"></i> View PDF</button>
			</div>
        </form>
        <!-- END FORM-->
        
        <div id="report-results">
        	<?php 
				if(!empty($page_list)):
					print '<div class="row-fluid">'.
						  '<div class="span" style="text-align:center"><h4>'. $report_heading .'</h4></div>'.
						  '</div>'.
						  (!empty($sub_heading)? 						  
						  '<div class="row-fluid">'.
						  '<div class="span" style="text-align:center"><h5><i>'. $sub_heading .'</i></h5></div>'.
						  '</div>'						  
						  : '' ).
						  '<div class="row-fluid">'.
						  '<div class="span2 pull-left" style="text-align:right; font-weight:bold">Financial year:</div>'.
						  '<div class="span6 pull-left" style="text-align:left">'. $financial_year .'</div>'.
						  '</div>'.
						  (!empty($report_period)?
						  '<div class="row-fluid">'.
						  '<div class="span2 pull-left" style="text-align:right; font-weight:bold">Reporting period:</div>'.
						  '<div class="span6 pull-left" style="text-align:left">'. $report_period .'</div>'.
						  '</div>'
						  : '');
					
					print '<table class="table table-striped table-hover">'.
						  '<thead>'.
						  '<tr>'.
						  '<th align="left">PDE name</th>'.
						  '<th class="hidden-480">Procurement ref. no.</th>'.
						  '<th class="hidden-480">Subject of procurement</th>'.						  
						  ($formdata['ifb_report_type'] == 'BER'? '<th>Procurement method <br /> (Threshhold)</th>' : '').
						  ($formdata['ifb_report_type'] == 'PIFB'? '<th style="text-align:right">Estimated cost(UGX)</th>' : '').	  
						  ($formdata['ifb_report_type'] == 'PIFB'? '<th>IFB Date</th>' : '').
						  '<th>Bid submission dead line</th>'.	
						  ($formdata['ifb_report_type'] == 'BER'? '<th>Bid submission duration</th>' : '').
						  ($formdata['ifb_report_type'] == 'PIFB'? '<th>No. of bids received</th>' : '').
						  '</tr>'.
						  '</thead>'.
						  '</tbody>';
					
					foreach($page_list as $row)
					{	
						print '<tr>'.
							  '<td>'. $row['pdename'] .'</td>'.
							  '<td>'. $row['procurement_ref_no'] .'</td>'.
							  '<td>'. $row['subject_of_procurement'] .'</td>'.							  
							  ($formdata['ifb_report_type'] == 'BER'? '<td>'. $row['procurement_method_title'] . ' (' . $row['biddingperiod'] .')</td>' : '').
							  ($formdata['ifb_report_type'] == 'PIFB'? '<td style="text-align: right">'. (is_numeric($row['estimated_amount'])? number_format(($row['estimated_amount'] * $row['exchange_rate']), 0, '.', ',') : $row['estimated_amount']) .'</td>' : '').
							  ($formdata['ifb_report_type'] == 'PIFB'? '<td style="white-space: nowrap">'. custom_date_format('d M, Y',$row['invitation_to_bid_date']). '</td>' : '').
							  '<td>'. custom_date_format('d M, Y',$row['bid_submission_deadline']) . ' at ' . custom_date_format('h:i A',$row['bid_submission_deadline']) . '</td>'.	
						      ($formdata['ifb_report_type'] == 'BER'? '<td>'. $row['proposal_submission_date_duration'] .'</td>' : '').
							  ($formdata['ifb_report_type'] == 'PIFB'? '<td>'. $row['numOfBids'] .'</td>' : '').
							  '</tr>';
					}
					
					print '</tbody></table>';
					
					print '<div class="pagination pagination-mini pagination-centered">'.
							pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().	
							"admin/manage_users/p/%d")
						.'</div>';
			
				elseif(!empty($formdata)):
					print format_notice('WARNING: Your search criteria does not match any results');
				endif; 
			?>
        </div>
        
    </div>
</div>