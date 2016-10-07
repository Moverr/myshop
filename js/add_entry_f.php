<?php if(empty($requiredfields)) $requiredfields = array();?>
<div class="widget-body form">
    <!-- BEGIN FORM-->
    <?php
  #  print_r($formdata);
    ?>
    <form action="<?=base_url() . 'procurement/save_procurement_entry' . ((!empty($i))? '/i/'.$i : '' ) . ((!empty($v))? '/v/'.$v : '' )?>" class="form-horizontal" method="post">
        <div class="accordion" id="accordion1">
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_1">
                        <i class=" fa fa-plus"></i>
                        Basic information
                    </a>
                </div>
                <div id="collapse_1" class="accordion-body collapse in">
                    <div class="accordion-inner">
                        <div class="ref_number_area">

                        </div>
                        <div class="control-group <?=(in_array('subject_of_procurement', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Subject of procurement <?= text_danger_template('*') ?></label>

                            <div class="controls">

                                <input id="subject_of_procurement" name="subject_of_procurement" type="text" value="<?=(!empty($formdata['subject_of_procurement'])? $formdata['subject_of_procurement'] : '')?>" class="span6">
                            </div>
                        </div>


                        <div class="control-group <?=(in_array('quantity', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Quantity <?= text_danger_template('*') ?></label>

                            <div class="controls">
                                  <input type="text" name="quantity" value="<?=(!empty($formdata['quantity'])? addCommas($formdata['quantity'], 0) : '' )?>" class="input-large numbercommas " />
                            
                            </div>
                        </div>

                        
                        <div class="control-group <?=(in_array('pde_department', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Responsible  department </label>

                            <div class="controls">
                                <input id="pde_department" name="pde_department" type="text" value="<?=(!empty($formdata['pde_department'])? $formdata['pde_department'] : '')?>" class="input-large">
                            </div>
                        </div>

                        <div class="control-group <?=(in_array('procurement_type', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Type of procurement <?= text_danger_template('*') ?></label>

                            <div class="controls">
                                <select id="procurement_type" name="procurement_type" class="input-large" data-placeholder="Choose a Category">
                                    <?=get_select_options(get_active_procurement_types(), 'id', 'title', (!empty($formdata['procurement_type'])? $formdata['procurement_type'] : '' ))?>
                                </select>
                            </div>
                        </div>
                        

                         


                        <div class="control-group <?=(in_array('estimated_amount', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Estimated amount <?= text_danger_template('*') ?>
                            </label>

                            <div class="controls">
                                <select id="currency" class="input-small m-wrap" name="currency">
                            <?=get_select_options(get_active_currencies(), 'id', 'title', (!empty($formdata['currency'])? $formdata['currency'] : '1' ))?>                            
                                </select>
                                <input style="display:none" class=" input-small  exchangerate"   value="<?=(!empty($formdata['exchange_rate'])? addCommas($formdata['exchange_rate'], 0) : '' )?>"   name="exchange_rate" placeholder="Exchange rate" type="text"  id="exchangerate"/>
                                <input type="text"   name="estimated_amount" value="<?=(!empty($formdata['estimated_amount'])? addCommas($formdata['estimated_amount'], 0) : '' )?>" class="input-medium  estimatedamount" id="estimatedamount" />
                            
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('funding_source', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Source of funding <?= text_danger_template('*') ?></label>

                            <div class="controls">
                                <select id="funding-source" class="input-large" name="funding_source" data-placeholder="Choose a Category" tabindex="1">
                                    <?=get_select_options(get_active_source_funding(), 'id', 'title', (!empty($formdata['funding_source'])? $formdata['funding_source'] : '' ))?>
                                </select>
                            </div>
                        </div>
						
						
						<!-- Procurement Method -->
                        <div class="control-group <?=(in_array('procurement_method', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Procurement method <?= text_danger_template('*') ?></label>

                            <div class="controls">
                                <select id="procurement_method " class="input-large procurement_method" name="procurement_method" data-placeholder="Choose a Category" tabindex="1">
                                    <?=get_select_options(get_active_procurement_methods(), 'id', 'title', (!empty($formdata['procurement_method'])? $formdata['procurement_method'] : '' ))?>
                                </select>
                            </div>
                        </div>						
					 

						
						<!-- Is it A Framework -->
						<div class="control-group">
                            <label class="control-label"> Is it a framework ? <span></span></label>
                            <div class="controls">
                            <input type="checkbox" value="n" <?php if(!empty($formdata['framework']) &&($formdata['framework'] =='Y' ) ){ ?> checked <?php } ?> name="framework" class="framework"/>
                            </div>
                        </div> 
						

                          
                      <!-- Justify Why You Chose that Procurement Type -->
                      <div style="display:none" class="justification_field control-group <?=(in_array('justification', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Add a Justification Why You Chose that   procurement Method <?= text_danger_template('*') ?></label>


                            <div class="controls">
                                 <textarea      class=" input-large " name="justification" id="justification"    >
                                 <?=(!empty($formdata['justification'])? $formdata['justification'] : '')?>
                                 </textarea>
                                 <input type="hidden" id="justified" name="justified" value="N">
                            </div>
                        </div>
                        <!-- END  -->


                   


                     
                    </div>
                </div>
            </div>

            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_2">
                        <i class=" fa fa-plus"></i>
                        Procurement Initiation Approvals
                    </a>
                </div>
                <div id="collapse_2" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <!--
                        <div class="control-group">
                            <label class="control-label">Pre-bid Events date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<? #date('m-d-Y') ?>"
                                     data-date-format="dd-mm-yyyy" data-date-viewmode="">
                                    <input id="pre_bid_events_date" class=" m-ctrl-medium ddatepicker" size="16"
                                           type="text" value=""><span class="add-on"><i
                                            class="fa fa-calendar"></i></span>
                                    <input required="" id="pre_bid_events_date_duration" placeholder="duration"
                                           type="text" class="span3 "> Days
                                </div>
                            </div>
                        </div>
                        -->
                        <div class="control-group <?=(in_array('contracts_committee_approval_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contracts committee approval date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['contracts_committee_approval_date'])? custom_date_format('Y-m-d', $formdata['contracts_committee_approval_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="contracts-committee-approval-date" name="contracts_committee_approval_date" class=" m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['contracts_committee_approval_date'])? $formdata['contracts_committee_approval_date'] : '')?>">
                                    <button   type="button" onClick="javascript:   $('#contracts-committee-approval-date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('contracts_committee_approval_of_shortlist_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contracts committee approval of shortlist & bidding
                                document</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['contracts_committee_approval_of_shortlist_date'])? custom_date_format('Y-m-d', $formdata['contracts_committee_approval_of_shortlist_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="contracts_committee_approval_of_shortlist_date" name="contracts_committee_approval_of_shortlist_date" class="m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['contracts_committee_approval_of_shortlist_date'])? $formdata['contracts_committee_approval_of_shortlist_date'] : '')?>">
                                    <button   type="button" onClick="javascript:   $('#contracts_committee_approval_of_shortlist_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        


                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('publication_of_pre_qualification_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Publication of pre-qualification notice date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['publication_of_pre_qualification_date'])? custom_date_format('Y-m-d', $formdata['publication_of_pre_qualification_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="publication_of_pre_qualification_date" class=" m-ctrl-medium ddatepicker" name="publication_of_pre_qualification_date" size="16" type="text" value="<?=(!empty($formdata['publication_of_pre_qualification_date'])? $formdata['publication_of_pre_qualification_date'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#publication_of_pre_qualification_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        <div class="control-group <?=(in_array('proposal_submission_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Closing date of pre-qualification proposal submission</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['proposal_submission_date'])? custom_date_format('Y-m-d', $formdata['proposal_submission_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="proposal_submission_date" name="proposal_submission_date" class=" m-ctrl-medium ddatepicker" size="16"
                                           type="text" value="<?=(!empty($formdata['proposal_submission_date'])? $formdata['proposal_submission_date'] : '')?>">
                                    <button type="button" onClick="javascript:   $('#proposal_submission_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        <!-- Request for Expression of Interest -->

                    <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_2a">
                        <i class=" fa fa-plus"></i>
                     Request for Expression of Interest 
                    </a>
                </div>
                <div id="collapse_2a" class="accordion-body collapse">
                    <div class="accordion-inner">
                    
                        <div class="control-group <?=(in_array('invitation_of_expression_of_interest', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Invitation of Expression of Interest Date </label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['invitation_of_expression_of_interest'])? custom_date_format('Y-m-d', $formdata['invitation_of_expression_of_interest']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="invitation_of_expression_of_interest" name="invitation_of_expression_of_interest" class=" m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['invitation_of_expression_of_interest'])? $formdata['invitation_of_expression_of_interest'] : '')?>">

                                     <button type="button" onClick="javascript:   $('#invitation_of_expression_of_interest').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('closing_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Closing Date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['closing_date'])? custom_date_format('Y-m-d', $formdata['closing_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="closing_date" name="closing_date" class="m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['closing_date'])? $formdata['closing_date'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#closing_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('approval_of_shortlist', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Approval of Shortlist Date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['approval_of_shortlist'])? custom_date_format('Y-m-d', $formdata['approval_of_shortlist']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="approval_of_shortlist" class=" m-ctrl-medium ddatepicker" name="approval_of_shortlist" size="16" type="text" value="<?=(!empty($formdata['approval_of_shortlist'])? $formdata['approval_of_shortlist'] : '')?>">
                                    <button type="button" onClick="javascript:   $('#approval_of_shortlist').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                         </div>


                         <div class="control-group <?=(in_array('notification_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label"> Notification Date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['notification_date'])? custom_date_format('Y-m-d', $formdata['notification_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="notification_date" name="notification_date" class=" m-ctrl-medium ddatepicker" size="16"
                                           type="text" value="<?=(!empty($formdata['notification_date'])? $formdata['notification_date'] : '')?>">
                                            <button type="button" onClick="javascript:   $('#notification_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>




        <!-- End Request for Expression of Interest -->

            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_3">
                        <i class=" fa fa-plus"></i>
                        Bidding period
                    </a>
                </div>
                <div id="collapse_3" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <div class="control-group <?=(in_array('bid_issue_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid invitation date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['bid_issue_date'])? custom_date_format('Y-m-d', $formdata['bid_issue_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="bid_issue_date" class="m-ctrl-medium ddatepicker" size="16" type="text" name="bid_issue_date" value="<?=(!empty($formdata['bid_issue_date'])? $formdata['bid_issue_date'] : '')?>">
                                    
                                    <button type="button" onClick="javascript:   $('#bid_issue_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        <div class="control-group <?=(in_array('bid_closing_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Bid closing date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['bid_closing_date'])? custom_date_format('Y-m-d', $formdata['bid_closing_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="bid_closing_date" class=" m-ctrl-medium ddatepicker" size="16" name="bid_closing_date" type="text" value="<?=(!empty($formdata['bid_closing_date'])? $formdata['bid_closing_date'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#bid_closing_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_6">
                        <i class=" fa fa-plus"></i>
                        Evaluation of bids
                    </a>
                </div>
                <div id="collapse_6" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <div class="control-group <?=(in_array('submission_of_evaluation_report_to_cc', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Submission of Evaluation Report to CC</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['submission_of_evaluation_report_to_cc'])? custom_date_format('Y-m-d', $formdata['submission_of_evaluation_report_to_cc']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="submission_of_evaluation_report_to_cc" name="submission_of_evaluation_report_to_cc" class=" m-ctrl-medium ddatepicker" size="16"
                                           type="text" value="<?=(!empty($formdata['submission_of_evaluation_report_to_cc'])? $formdata['submission_of_evaluation_report_to_cc'] : '')?>">

                                           <button type="button" onClick="javascript:   $('#submission_of_evaluation_report_to_cc').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('cc_approval_of_evaluation_report', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Approval of evaluation report by contracts committee</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['cc_approval_of_evaluation_report'])? custom_date_format('Y-m-d', $formdata['cc_approval_of_evaluation_report']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="cc_approval_of_evaluation_report" class="m-ctrl-medium ddatepicker" size="16" name="cc_approval_of_evaluation_report" type="text" value="<?=(!empty($formdata['cc_approval_of_evaluation_report'])? $formdata['cc_approval_of_evaluation_report'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#cc_approval_of_evaluation_report').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        


                                </div>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
            
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_7">
                        <i class=" fa fa-plus"></i>
                        Negotiations
                    </a>
                </div>
                <div id="collapse_7" class="accordion-body collapse">
                    <div class="accordion-inner">
                        <div class="control-group <?=(in_array('negotiation_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Negotiation date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['negotiation_date'])? custom_date_format('Y-m-d', $formdata['negotiation_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="negotiation_date" class="m-ctrl-medium ddatepicker" size="16" name="negotiation_date" type="text" value="<?=(!empty($formdata['negotiation_date'])? $formdata['negotiation_date'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#negotiation_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>

                        <div class="control-group <?=(in_array('negotiation_approval_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Approval of negotiations report contract committee </label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['negotiation_approval_date'])? custom_date_format('Y-m-d', $formdata['negotiation_approval_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="negotiation_approval_date" name="negotiation_approval_date" class="m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['negotiation_approval_date'])? $formdata['negotiation_approval_date'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#negotiation_approval_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_beb_window">
                        <i class=" fa fa-plus"></i>
                        BEB window and Administrative review
                    </a>
                </div>
                <div id="collapse_beb_window" class="accordion-body collapse">
                  <div class="accordion-inner">
                    <div class="control-group <?=(in_array('best_evaluated_bidder_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Best evaluated bidder notice date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['best_evaluated_bidder_date'])? custom_date_format('Y-m-d', $formdata['best_evaluated_bidder_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="best_evaluated_bidder_date" name="best_evaluated_bidder_date" class=" m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['best_evaluated_bidder_date'])? $formdata['best_evaluated_bidder_date'] : '')?>">

                                     <button type="button" onClick="javascript:   $('#best_evaluated_bidder_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_4">
                        <i class=" fa fa-plus"></i>
                        Contract finalization
                    </a>
                </div>
                <div id="collapse_4" class="accordion-body collapse">
                    <div class="accordion-inner">

                        <div class="control-group <?=(in_array('performance_security', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Performance security</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['performance_security'])? custom_date_format('Y-m-d', $formdata['performance_security']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="performance_security" class=" m-ctrl-medium ddatepicker" size="16" name="performance_security" type="text" value="<?=(!empty($formdata['performance_security'])? $formdata['performance_security'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#performance_security').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        


                                </div>
                            </div>
                        </div>
                                       
                        <div class="control-group <?=(in_array('solicitor_general_approval_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Solicitor general's approval date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['solicitor_general_approval_date'])? custom_date_format('Y-m-d', $formdata['solicitor_general_approval_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="solicitor_general_approval_date" class=" m-ctrl-medium ddatepicker" size="16" name="solicitor_general_approval_date" type="text" value="<?=(!empty($formdata['solicitor_general_approval_date'])? $formdata['solicitor_general_approval_date'] : '')?>">
                                    <button type="button" onClick="javascript:   $('#solicitor_general_approval_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        


                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('accounting_officer_approval_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Accounting officer's approval date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['accounting_officer_approval_date'])? custom_date_format('Y-m-d', $formdata['accounting_officer_approval_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="accounting_officer_approval_date" class=" m-ctrl-medium ddatepicker" size="16" name="accounting_officer_approval_date" type="text" value="<?=(!empty($formdata['accounting_officer_approval_date'])? $formdata['accounting_officer_approval_date'] : '')?>">
                                      <button type="button" onClick="javascript:   $('#accounting_officer_approval_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('contract_award', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contract award date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['contract_award'])? custom_date_format('Y-m-d', $formdata['contract_award']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="contract_award" class=" m-ctrl-medium ddatepicker" size="16" name="contract_award" type="text" value="<?=(!empty($formdata['contract_award'])? $formdata['contract_award'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#contract_award').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group <?=(in_array('contract_sign_date', $requiredfields)? 'error': '')?>">
                            <label class="control-label">Contract signature date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['contract_sign_date'])? custom_date_format('Y-m-d', $formdata['contract_sign_date']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="contract_sign_date" class=" m-ctrl-medium ddatepicker" size="16" name="contract_sign_date" type="text" value="<?=(!empty($formdata['contract_sign_date'])? $formdata['contract_sign_date'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#contract_sign_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            <div class="accordion-group">
                <div class="accordion-heading">
                    <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion1"
                       href="#collapse_8">                   <i class=" fa fa-plus"></i>
                        Contract implementation
                    </a>
                </div>
              
                <div id="collapse_8" class="accordion-body collapse">
                    <div class="accordion-inner">

  

                        <div class="control-group suppliescontrol <?=(in_array('opening_of_credit_letter', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && in_array($formdata['procurement_type'], array(1)) ? 'style="display:block;"' : '') ?> >
                            <label class="control-label">Opening of letter of credit</label>
 
                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['opening_of_credit_letter'])? custom_date_format('Y-m-d', $formdata['opening_of_credit_letter']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="opening_of_credit_letter" name="opening_of_credit_letter" class=" m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['opening_of_credit_letter'])? $formdata['opening_of_credit_letter'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#opening_of_credit_letter').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                         
                        <div class="control-group suppliescontrol <?=(in_array('arrival_of_goods', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && in_array($formdata['procurement_type'], array(1))? 'style="display:block;"' : '')?>>
                            <label class="control-label">Arrival of goods</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['arrival_of_goods'])? custom_date_format('Y-m-d', $formdata['arrival_of_goods']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="arrival_of_goods" name="arrival_of_goods" class=" m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['arrival_of_goods'])? $formdata['arrival_of_goods'] : '')?>">
                                    <button type="button" onClick="javascript:   $('#arrival_of_goods').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                                
                            </div>
                        </div>
                        
                        <div class="control-group suppliescontrol <?=(in_array('inspection_final_acceptance', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && in_array($formdata['procurement_type'], array(1))? 'style="display:block;"' : '')?>>
                            <label class="control-label">Final acceptance/delivery notes</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['inspection_final_acceptance'])? custom_date_format('Y-m-d', $formdata['inspection_final_acceptance']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="inspection_final_acceptance" name="inspection_final_acceptance" class=" m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['inspection_final_acceptance'])? $formdata['inspection_final_acceptance'] : '')?>">
                                      <button type="button" onClick="javascript:   $('#inspection_final_acceptance').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                             </div>
                        </div>
                    
                        <div class="control-group works_control nonconsultancycontrol <?=(in_array('mobilise_advance_payment', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && in_array($formdata['procurement_type'], array(3, 2))? 'style="display:block;"' : '')?>>
                            <label class="control-label">Mobilise advance payment date</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['mobilise_advance_payment'])? custom_date_format('Y-m-d', $formdata['mobilise_advance_payment']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="advanced_payment_date" name="mobilise_advance_payment" class=" m-ctrl-medium ddatepicker" size="16"
                                           type="text" value="<?=(!empty($formdata['mobilise_advance_payment'])? $formdata['mobilise_advance_payment'] : '')?>">
                                           
                                       <button type="button" onClick="javascript:   $('#advanced_payment_date').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        

                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group nonconsultancycontrol <?=(in_array('draft_report', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && $formdata['procurement_type'] == 2? 'style="display:block;"' : '')?>>
                            <label class="control-label">Draft report</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['draft_report'])? custom_date_format('Y-m-d', $formdata['draft_report']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="draft_report" name="draft_report" class="m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['draft_report'])? $formdata['draft_report'] : '')?>">
                                      <button type="button" onClick="javascript:   $('#draft_report').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>
                        
                        <div class="control-group nonconsultancycontrol <?=(in_array('final_report', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && $formdata['procurement_type'] == 2? 'style="display:block;"' : '')?>>
                            <label class="control-label">Final report</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['final_report'])? custom_date_format('Y-m-d', $formdata['final_report']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="final_report" name="final_report" class="m-ctrl-medium ddatepicker" size="16" type="text" value="<?=(!empty($formdata['final_report'])? $formdata['final_report'] : '')?>">
                                     <button type="button" onClick="javascript:   $('#final_report').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>

                        <div class="control-group workscontrol <?=(in_array('substantial_completion', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && $formdata['procurement_type'] == 3? 'style="display:block;"' : '')?>>
                            <label class="control-label">Substantial completion</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['substantial_completion'])? custom_date_format('Y-m-d', $formdata['substantial_completion']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="substantial_completion" name="substantial_completion" class=" m-ctrl-medium ddatepicker" size="16"
                                           type="text" value="<?=(!empty($formdata['substantial_completion'])? $formdata['substantial_completion'] : '')?>">
                                            <button type="button" onClick="javascript:   $('#substantial_completion').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        


                                </div>
                            </div>
                        </div>

                        <div class="control-group workscontrol <?=(in_array('final_acceptance', $requiredfields)? 'error': '')?>" <?=(!empty($formdata['procurement_type']) && $formdata['procurement_type'] == 3? 'style="display:block;"' : '')?>>
                            <label class="control-label">Final acceptance</label>

                            <div class="controls">
                                <div class="input-append date ddatepicker" data-date="<?=(!empty($formdata['final_acceptance'])? custom_date_format('Y-m-d', $formdata['final_acceptance']) : date('Y-m-d'))?>"
                                     data-date-format="dd/ mm/yyyy" data-date-viewmode="days">
                                    <input id="final_acceptance" class=" m-ctrl-medium ddatepicker" size="16" name="final_acceptance" type="text" value="<?=(!empty($formdata['final_acceptance'])? $formdata['final_acceptance'] : '')?>">

                                     <button type="button" onClick="javascript:   $('#final_acceptance').attr('value','');"><i class='fa fa-refresh'></i> </button>
                        
                                </div>
                            </div>
                        </div>  
                    
                    </div>
                </div>
            </div>

        </div>
        <label class="checkbox line">
            <div class="checker" id="uniform-undefined"><span class=""><input id="checkBox" type="checkbox" value=""
                                                                              style="opacity: 0;"></span></div>
            I confirm that data entered for annual procurement plan is correct
        </label>

        <div class="message_alerts">

        </div>
        <div class="form-actions">
            <button disabled="disabled" id="submit_plan" value="save_entry" name="save_entry" type="submit" class="btn">
                Save
            </button>
            <?php if(empty($i)): ?>
            &nbsp;
            <button disabled="disabled" id="submit_plan_add" value="save_entry" name="save_and_new" type="submit" class="btn ">
                Save and Add
            </button>
            <?php endif; ?>
            &nbsp;
            <button  type="reset" class="btn" >Clear</button>
        </div>
    </form>
    <!-- END FORM-->
</div>


</div>
<!-- END SAMPLE FORM widget-->

<script>

    $(function(){
	 $(".procurement_method").change(function(){

			console.log("Hello");
            procurementmethod = this.value;  
             formdata['procurement_method']   = procurementmethod;            
            console.log(threshold);
            fetchproc_method();
           
        
      });
	  

   function in_Array(needle, haystack) {  
     var length = haystack.length;    
      for(var i = 0; i < haystack.length; i++)
      {
      console.log(haystack[i]);  
        if(haystack[i] == needle) 
          return true; 
      }
      return false;    
}

/*
threshold = '';
           procurementtype = 0;
        exchange_rate = 0;
        estimated_amount = 0;  */
        formdata = {};      
       
 
       // $(".estimatedamount").change(function(){
//estimated amount
 /*procurementmethod =  0;
procumethod = 0;
status_level=0;  */
<?php 
if(!empty($formdata['procurement_method'])){
    ?>
	 status_level=1;
    procumethod = '<?= $formdata['procurement_method']; ?>';
    procurementmethod = procumethod;
    estimated_amount =  $(".estimatedamount").val().replace(/\D/g,'');
    formdata['estimated_amount']   = estimated_amount;
    procurementtype = $("#procurement_type").val();
    formdata['procurement_type']  = procurementtype;
    exchange_rate = $(".exchangerate").val().replace(/\D/g,'');
    formdata['exchange_rate']   = exchange_rate;
    console.log(formdata);
    
    //start
 console.log("THRESHOLD : Proccessing...");
    url = getBaseURL()+"procurement/fetch_procurement_methods_json";
    //fetch_procurement_methods";
    $.ajax({
        url:  url,
         type: 'POST',
        data: formdata,
        success: function(data, textStatus, jqXHR){
       threshold = JSON.parse(data); 
       console.log(threshold); 
       console.log("parser");
       fetchproc_method();
        },
        error:function(data , textStatus, jqXHR)
        {
             console.log(data);

        }
    });

   
    
 
 
    <?php
}

    ?>
 

//              estimated_amount =  $(".estimatedamount").val();
//              estimated_amount = estimated_amount.replace(/\D/g,'');
//              formdata['estimated_amount']   = estimated_amount;
// //procurement type        
//             procurementtype = $("#procurement_type").val();
//             formdata['procurement_type']  = procurementtype;
//             console.log(formdata);
             
//                 //exchange rate
//                  exchange_rate = $(".exchangerate").val();
//                  exchange_rate = exchange_rate.replace(/\D/g,'');
//                  formdata['exchange_rate']   = exchange_rate;
//                  console.log(formdata);

//                   $(".exchangerate").val(addCommas(exchange_rate));
                  
//             if(estimated_amount > 0)
//              {
//              $(".estimatedamount").val(addCommas(estimated_amount));
//              fetchproc_method();
//              }


        function showHideXrate()
        {
            if($('#currency').val() == 1)
            {
                $('input[name="exchange_rate"]').hide();
            }
            else
            {
                $('input[name="exchange_rate"]').show();
            }
        }

        showHideXrate();
        
        $("#currency").change(showHideXrate);
        
        //toggle button status depending on checkbox confirmation
        $('#checkBox').click(function(){

            //verify if checkbox is checked
            if($('#checkBox').attr('checked'))
            {
                $("#submit_plan, #submit_plan_add").removeAttr("disabled");
            }
            else
            {
                $("#submit_plan, #submit_plan_add").attr("disabled", "disabled");
            }

        });
        
        
        //hide reference number
        $('.ref_number_area').hide();
        //when a usertype is chosen
        $("#procurement_type").change(function(){
            if($("#procurement_type").val())
            {
                var procurement_type =$("#procurement_type").val();
                
                if($(this).val() == 1){
                    $('.non_consultancy_control').hide();
                    $('.works_control').hide();
                    $('.supplies_control').show();
                
                } else if($(this).val() == 2){
                    $('.supplies_control').hide();
                    $('.works_control').hide();     
                    $('.non_consultancy_control').show();           
                    
                } else if($(this).val() == 3){
                    $('.supplies_control').hide();
                    $('.non_consultancy_control').hide();
                    $('.works_control').show();
                    
                }
            }
        });
    //  $(function() {
    $( ".ddatepicker" ).datepicker({ dateFormat: 'mm/dd/yyyy' });
  
          // $('.ddatepicker').datepicker({ dateFormat: 'mm/dd/yyyy' }).val();
    });
</script>