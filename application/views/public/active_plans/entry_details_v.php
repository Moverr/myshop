<div class="widget-body print_area printarea" >

    <div class="space20"></div>
    <div class="row" >
        <div class="col-md-9">
            <h5><?=strtoupper($pagetitle)?></h5>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-2">
            <b>Procurement Reference Number:</b>
        </div>
        <div class="col-md-6">
            <?=get_procurement_plan_entry_info($entry_id,'procurement_ref_no')?>
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-md-2">
            <b>Financial Year:</b>
        </div>
        <div class="col-md-6">
            <?=get_procurement_plan_entry_info($entry_id,'procurement_plan')?>
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-md-2">
            <b>Procurement Type:</b>
        </div>
        <div class="col-md-6">
            <?=get_procurement_plan_entry_info($entry_id,'procurement_type')?>
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-md-2">
            <b>Procurement Method:</b>
        </div>
        <div class="col-md-6">
            <?=get_procurement_plan_entry_info($entry_id,'procurement_method')?>
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-md-2">
            <b>Department:</b>
        </div>
        <div class="col-md-6">
            <?=get_procurement_plan_entry_info($entry_id,'department')?>
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-md-2">
            <b>Source of Funding:</b>
        </div>
        <div class="col-md-6">
            <?=get_procurement_plan_entry_info($entry_id,'funding_source')?>
        </div>
    </div>
    

    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Pre-Qualification Period</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                    <tr>

                        <th>Activity</th>

                        <th>Start Date</th>

                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Pre-Bid Events</td>
                        <td><strong><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'pre_bid_events_date'),0,10))?> </strong>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'pre_bid_events_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'pre_bid_events_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'pre_bid_events_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'pre_bid_events_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contracts Committee Approval Date </td>
                        <td><strong><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'),0,10))?> </strong>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Publication of Pre Qualification</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contracts Committee Approval Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Proposal Submission</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'proposal_submission_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'proposal_submission_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'proposal_submission_date')))> 0 ? (get_procurement_plan_entry_info($entry_id,'pre_bid_events_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'proposal_submission_date')) : 'Expired' ?></span>
                        </td>
                    </tr>


                    <tr>
                        <td>Contracts Committee Approval of Shortlist Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'pre_bid_events_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date')) : 'Expired' ?></span>
                        </td>
                    </tr>







                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Pre-Qualification Period</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                    <tr>

                        <th>Activity</th>

                        <th>Start Date</th>

                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Pre-Bid Events</td>

                        <td><?=  custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'pre_bid_events_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'pre_bid_events_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'pre_bid_events_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'pre_bid_events_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'pre_bid_events_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contracts Committee Approval Date </td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Publication of Pre Qualification</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'publication_of_pre_qualification_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contracts Committee Approval Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Proposal Submission</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'proposal_submission_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'proposal_submission_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'proposal_submission_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'proposal_submission_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'proposal_submission_date')) : 'Expired' ?></span>
                        </td>
                    </tr>


                    <tr>
                        <td>Contracts Committee Approval of Shortlist Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contracts_committee_approval_of_shortlist_date')) : 'Expired' ?></span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Bidding Period</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                    <tr>

                        <th>Activity</th>

                        <th>Start Date</th>

                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>




                    <tr>
                        <td>Bid Submission Opening </td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'bid_submission_opening_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'bid_submission_opening_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'bid_submission_opening_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'bid_submission_opening_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'bid_submission_opening_date')) : 'Expired' ?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Bid Issue Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'bid_issue_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'bid_issue_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'bid_issue_date'))) > 0 ? (get_procurement_plan_entry_info($entry_id,'bid_issue_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'bid_issue_date')) : 'Expired' ?></span>
                        </td>
                    </tr>





                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Bid Evaluation Period</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                    <tr>

                        <th>Activity</th>

                        <th>Start Date</th>

                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>


                    <tr>
                        <td>Contracts Committee Approval of Evaluation Report</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'cc_approval_of_evaluation_report'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'cc_approval_of_evaluation_report_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'cc_approval_of_evaluation_report')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Negotiation Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'negotiation_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'negotiation_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'negotiation_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Negotiation Approval Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'negotiation_approval_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'negotiation_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'negotiation_approval_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Advanced Payment Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'advanced_payment_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'advanced_payment_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'advanced_payment_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Mobilise Advance Payment</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'mobilise_advance_payment'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'mobilise_advance_payment_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'mobilise_advance_payment')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Substantial Completion</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'substantial_completion'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'substantial_completion_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'substantial_completion')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Final Acceptance</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'final_acceptance'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'final_acceptance_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'final_acceptance')))?></span>
                        </td>
                    </tr>



                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Negotiation Period</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                    <tr>

                        <th>Activity</th>

                        <th>Start Date</th>

                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>


                    <tr>
                        <td>Secure Necessary Approval Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'secure_necessary_approval_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'secure_necessary_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'secure_necessary_approval_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contract Award</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contract_award'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contract_award_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contract_award')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Best Evaluated Bidder Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'best_evaluated_bidder_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'best_evaluated_bidder_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'best_evaluated_bidder_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contract Sign Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'contract_sign_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'contract_sign_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'contract_sign_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Contracts Committee Approval of Evaluation Report</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'cc_approval_of_evaluation_report'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'cc_approval_of_evaluation_report_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'cc_approval_of_evaluation_report')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Negotiation Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'negotiation_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'negotiation_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'negotiation_date')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Negotiation Approval Date</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'negotiation_approval_date'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'negotiation_approval_date_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'negotiation_approval_date')))?></span>
                        </td>
                    </tr>



                    </tbody>
                </table>
            </div>
        </div>

    </div>


    <div class="row-fluid">
        <div class="widget">
            <div class="widget-title">
                <h4></i>Contract Implementation Period</h4>

            </div>
            <div class="widget-body">
                <table class="table table-hover">
                    <thead>
                    <tr>

                        <th>Activity</th>

                        <th>Start Date</th>

                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>


                    <tr>
                        <td>Mobilise Advance Payment</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'mobilise_advance_payment'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'mobilise_advance_payment_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'mobilise_advance_payment')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Substantial Completion</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'substantial_completion'),0,10))?>                      
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'substantial_completion_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'substantial_completion')))?></span>
                        </td>
                    </tr>

                    <tr>
                        <td>Final Acceptance</td>
                        <td><?= custom_date_format('d M, Y',substr(get_procurement_plan_entry_info($entry_id,'final_acceptance'),0,10))?>
                        </td>
                        <td>
                            <span class=""><?=days_time_left((get_procurement_plan_entry_info($entry_id,'final_acceptance_duration')*60*60*60)+date_to_seconds(get_procurement_plan_entry_info($entry_id,'final_acceptance')))?></span>
                        </td>
                    </tr>



                    </tbody>
                </table>
            </div>
        </div>

    </div>



 <?php
         $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."";

            print  ''.
                    '&nbsp;<a href="https://twitter.com/share" class="twitter-share-button  " data-url="'.$url.'" data-size="small" data-hashtags="tenderportal_ug" data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" data-action="share" data-size="medium" data-annotation="none" data-height="24" data-href="'.$url.'"></div>&nbsp;<div class="fb-share-button" data-href="'.$url.'" data-layout="button" data-size="medium"></div>'
         
         ?>

</div>

<a id="print"  class="print" href="#">Download Test PDF</a>

<script>
    $(document).ready(function(){

        //when print is clicked
        $('#print').click(function(){
            //alert('foo');
            //$('.print_area').printElement();
            $("print_area").printThis();
        });

        var doc = new jsPDF();

// We'll make our own renderer to skip this editor
        var specialElementHandlers = {
            '#editor': function(element, renderer){
                return true;
            }
        };
// All units are in the set measurement for the document
// This can be changed to "pt" (points), "mm" (Default), "cm", "in"
        doc.fromHTML($('#render_me').get(0), 15, 15, {
            'width': 170,
            'elementHandlers': specialElementHandlers
        });
//doc.save('Test.pdf');

        $('a').click(function(){
            doc.save('TestHTMLDoc.pdf');
        });
    });
</script>