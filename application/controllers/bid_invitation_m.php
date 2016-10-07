<?php

class Bid_invitation_m extends MY_Model
{
    public $_tablename = 'bidinvitations';
    public $_primary_key = 'id';

    function __construct()
    {

        parent::__construct();
    }

    // =======================================================================//
    // ! BEST EVALUATED BIDDERS
    // =======================================================================//

    function get_best_evaluated_bidders($from, $to, $pde = '')
    {

        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
receipts.receiptid AS id,
receipts.nationality,
receipts.beb,
receipts.reason,
receipts.joint_venture,
receipts.datereceived,
receipts.received_by,
BI.id AS bidinvitation_id,
BI.id AS bid_id,
BI.vote_no,
BI.initiated_by,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.cc_approval_date,
BI.bid_receipt_address,
BI.bid_openning_address,
BI.procurement_ref_no,
BI.procurement_id,
BI.date_approved,
BI.dateadded,
BI.approvedby,
BI.approval_comments,
BI.isactive,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.bidvalidityperiod,
BI.bidvalidity,
BI.quantity,
BI.haslots,
BI.dateadded AS bid_dateadded,
BEB.type_oem,
BEB.ddate_octhe,
BEB.num_orb,
BEB.date_oce_r,
BEB.nationality,
BEB.contractprice,
BEB.currency,
BEB.dateadded,
BEB.beb_expiry_date,
BEB.date_of_display,
BEB.ispublished,
BEB.isreviewed,
BEB.id AS BEB_id,
BEB.id as bid_id,
BEB.seerialnumber,
BEB.review_level,
BEB.contractprice,
BEB.currency,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.procurement_method,
PPE.pde_department,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PP.pde_id,
IF(receipts.providerid > 0,receipts.providerid,JV.providers) as providers,
 (SELECT  CONCAT(users.firstname, \' \', users.lastname)
  AS approver_fullname FROM users
  WHERE users.userid =  BEB.author  LIMIT 1) AS author_fullname

',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('joint_venture AS JV', 'JV.jv = receipts.joint_venture', 'left');
        $this->db->join('evaluation_methods AS EM ', 'EM.evaluation_method_id = BEB.type_oem');

        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('receipts.beb', 'Y');

        # if ranges are provided
        if($from&&$to){
            $this->db->where('BI.dateadded >=',$from);
            $this->db->where('BI.dateadded <=',$to);
        }

        $this->db->where('PP.isactive', 'Y');

        $this->db->order_by('BEB.dateadded','desc');
        if ($pde) {
            $this->db->where('pdes.pdeid =',$pde);
        }




        $query = $this->db->get();

        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//        exit;
        return  $query->result_array();
    }


    // =======================================================================//
    // ! BEST EVALUATED BIDDERS
    // =======================================================================//

    function get_best_evaluated_bidders_without_contracts($from, $to, $pde = '',$financial_year,$expired='')
    {

        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

receipts.receiptid,
receipts.nationality,
receipts.beb,
receipts.reason,
receipts.joint_venture,
BI.id AS bidinvitation_id,
BI.id AS bid_id,
BI.vote_no,
BI.initiated_by,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.cc_approval_date,
BI.bid_receipt_address,
BI.bid_openning_address,
BI.procurement_ref_no,
BI.procurement_id,
BI.date_approved,
BI.dateadded,
BI.approvedby,
BI.approval_comments,
BI.isactive,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.bidvalidityperiod,
BI.bidvalidity,
BI.quantity,
BI.haslots,
BI.estimated_amount,
BI.dateadded AS bid_dateadded,
IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount) as normalized_estimated_amount,
(SELECT providernames from providers WHERE providers.providerid=receipts.providerid  AND receipts.beb="Y" ) AS providernames,
BEB.type_oem,
 IF(C.final_contract_value > 0, C.final_contract_value,
            IF(CVP.amount >0,IF(CVP.price_variation_type LIKE "positive",
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) + IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) - IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount)),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))) as normalized_actual_contract_price,

BEB.ddate_octhe,
BEB.num_orb,
BEB.date_oce_r,
BEB.nationality,
BEB.contractprice,
BEB.currency,
BEB.dateadded,
BEB.beb_expiry_date,
BEB.date_of_display,
BEB.ispublished,
BEB.isreviewed,
BEB.id,
BEB.id as bid_id,
BEB.seerialnumber,
BEB.review_level,
BEB.contractprice,
BEB.currency,
BEB.isactive AS BEB_ISACTIVE,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.procurement_method,
PPE.pde_department,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PP.pde_id,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.status,
IF(receipts.providerid > 0,receipts.providerid,JV.providers) as providers,
 (SELECT  CONCAT(users.firstname, \' \', users.lastname)
  AS approver_fullname FROM users
  WHERE users.userid =  BEB.author  LIMIT 1) AS author_fullname,
  C.id as contract_id,
PM.title,
PM.id AS procurement_method_id,
PT.title AS procurement_type_title,

',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('joint_venture AS JV', 'JV.jv = receipts.joint_venture', 'left');
        $this->db->join('evaluation_methods AS EM ', 'EM.evaluation_method_id = BEB.type_oem');
        $this->db->join('contracts AS C', 'C.bidinvitation_id = BI.id','left');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('call_of_orders AS CO', 'CO.contractid = C.id', 'left');

        $this->db->join('contracts_variations AS CV', 'CV.contractid = C.id', 'left');
        $this->db->join('contracts_variations_prices AS CVP', 'CVP.contract_variation_id = CV.id', 'left');



        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('receipts.beb', 'Y');
        $this->db->where('BI.display_of_beb_notice !=','0000-00-00 00:00:00');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('C.receiptid !=', 'receipts.id');
        $this->db->where('C.actual_completion_date IS NULL', null);


        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }
        # if ranges are provided
        if($from&&$to){
            $this->db->where('BEB.dateadded >=',$from);
            $this->db->where('BEB.dateadded <=',$to);
        }

        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }else{
            if($from&&$to){
                $this->db->where('BEB.dateadded  >=', $from);
                $this->db->where('BEB.dateadded  <=', $to);
            }
        }






        $this->db->order_by('BI.dateadded','desc');
        $query = $this->db->get();

        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count(unique_multidim_array($query->result_array(),'id')));
//        print_array(unique_multidim_array($query->result_array(),'id'));
//        exit;
        $res=$query->result_array();

        if(strtoupper($count)=='Y') {
            $res = $query->num_rows();
        }



        return $res;
    }



    // =======================================================================//
    // ! ACTIVE BEST EVALUATED BIDDERS
    // =======================================================================//

    function get_active_best_evaluated_bidders($from, $to, $pde = '',$financial_year='',$count='')
    {

        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

receipts.receiptid,
receipts.nationality,
receipts.beb,
receipts.reason,
receipts.joint_venture,
BI.id AS bidinvitation_id,
BI.id AS bid_id,
BI.vote_no,
BI.initiated_by,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.cc_approval_date,
BI.bid_receipt_address,
BI.bid_openning_address,
BI.procurement_ref_no,
BI.procurement_id,
BI.date_approved,
BI.dateadded,
BI.approvedby,
BI.approval_comments,
BI.isactive,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.bidvalidityperiod,
BI.bidvalidity,
BI.quantity,
BI.haslots,
BI.estimated_amount,
BI.dateadded AS bid_dateadded,
BEB.type_oem,
BEB.ddate_octhe,
BEB.num_orb,
BEB.date_oce_r,
BEB.nationality,
BEB.contractprice,
BEB.currency,
BEB.dateadded,
BEB.beb_expiry_date,
BEB.date_of_display,
BEB.ispublished,
BEB.isreviewed,
BEB.id,
BEB.id as bid_id,
BEB.seerialnumber,
BEB.review_level,
BEB.contractprice,
BEB.currency,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.procurement_method,
PPE.pde_department,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PP.pde_id,
IF(receipts.providerid > 0,receipts.providerid,JV.providers) as providers,
 (SELECT  CONCAT(users.firstname, \' \', users.lastname)
  AS approver_fullname FROM users
  WHERE users.userid =  BEB.author  LIMIT 1) AS author_fullname

',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('joint_venture AS JV', 'JV.jv = receipts.joint_venture', 'left');
        $this->db->join('evaluation_methods AS EM ', 'EM.evaluation_method_id = BEB.type_oem');
        $this->db->join('contracts AS C', 'C.bidinvitation_id = BI.id','left');

        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');



        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('receipts.beb', 'Y');



        $this->db->where('PP.isactive', 'Y');

        $this->db->order_by('BI.dateadded','desc');
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }
        # if ranges are provided
        if($from&&$to){
            $this->db->where('BI.dateadded >=',$from);
            $this->db->where('BI.dateadded <=',$to);
        }

        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }else{
            if($from&&$to){
                $this->db->where('BI.dateadded >=', $from);
                $this->db->where('BI.dateadded <=', $to);
            }
        }

        $this->db->where('C.bidinvitation_id', null);




        $query = $this->db->get();

        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//        exit;
        $res=$query->result_array();

        if(strtoupper($count)=='Y') {
            $res = $query->num_rows();
        }



        return $res;
    }


    // =======================================================================//
    // ! ARCHIVED BEST EVALUATED BIDDERS
    // =======================================================================//

    function get_archived_best_evaluated_bidders($from, $to, $pde = '',$financial_year='',$count='')
    {

        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
receipts.receiptid,
receipts.nationality,
receipts.beb,
receipts.reason,
receipts.joint_venture,
BI.id AS bidinvitation_id,
BI.id AS bid_id,
BI.vote_no,
BI.initiated_by,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.cc_approval_date,
BI.bid_receipt_address,
BI.bid_openning_address,
BI.procurement_ref_no,
BI.procurement_id,
BI.date_approved,
BI.dateadded,
BI.approvedby,
BI.approval_comments,
BI.isactive,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.bidvalidityperiod,
BI.bidvalidity,
BI.quantity,
BI.haslots,
BI.dateadded AS bid_dateadded,
BEB.type_oem,
BEB.ddate_octhe,
BEB.num_orb,
BEB.date_oce_r,
BEB.nationality,
BEB.contractprice,
BEB.currency,
BEB.dateadded,
BEB.beb_expiry_date,
BEB.date_of_display,
BEB.ispublished,
BEB.isreviewed,
BEB.id,
BEB.id as bid_id,
BEB.seerialnumber,
BEB.review_level,
BEB.contractprice,
BEB.currency,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.procurement_method,
PPE.pde_department,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PP.pde_id,
IF(receipts.providerid > 0,receipts.providerid,JV.providers) as providers,
 (SELECT  CONCAT(users.firstname, \' \', users.lastname)
  AS approver_fullname FROM users
  WHERE users.userid =  BEB.author  LIMIT 1) AS author_fullname

',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('joint_venture AS JV', 'JV.jv = receipts.joint_venture', 'left');
        $this->db->join('evaluation_methods AS EM ', 'EM.evaluation_method_id = BEB.type_oem');
        $this->db->join('contracts AS C', 'C.bidinvitation_id = BI.id','left');


        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('receipts.beb', 'Y');

        # if ranges are provided
        if($from&&$to){
            $this->db->where('BI.dateadded >=',$from);
            $this->db->where('BI.dateadded <=',$to);
        }

        $this->db->where('PP.isactive', 'Y');

        $this->db->order_by('BI.dateadded','desc');
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }
        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }else{
            if($from&&$to){
                $this->db->where('BI.dateadded >=', $from);
                $this->db->where('BI.dateadded <=', $to);
            }
        }

        $this->db->where('C.bidinvitation_id IS NOT NULL', null);




        $query = $this->db->get();

        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//        exit;
        $res=$query->result_array();

        if(strtoupper($count)=='Y') {
            $res = $query->num_rows();
        }



        return $res;
    }

    // =======================================================================//
    // ! ACTIVE BEST EVALUATED BIDDERS
    // =======================================================================//

    function get_procurements_scheduled_to_commence($from, $to, $pde = '',$financial_year='',$count='')
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
        bidinvitations.procurement_ref_no,
        bidinvitations.haslots,
        bidinvitations.subject_details,
        bidinvitations.display_of_beb_notice,
        IF(bidinvitations.estimated_amount_exchange_rate > 0, bidinvitations.estimated_amount * bidinvitations.estimated_amount_exchange_rate, bidinvitations.estimated_amount) as normalized_estimated_amount,
        procurement_plan_entries.subject_of_procurement,
        procurement_plan_entries.framework,
        IF(bidinvitations.procurement_method_ifb > 0, PM.title , procurement_methods.title) AS procurement_method_title,
        procurement_plans.financial_year AS financial_year,
        procurement_plans.title AS procurement_pan ,
        bestevaluatedbidder.type_oem,
        bestevaluatedbidder.ddate_octhe,
        bestevaluatedbidder.num_orb,
        bestevaluatedbidder.date_oce_r,
        bestevaluatedbidder.nationality,
        bestevaluatedbidder.currency,
        bestevaluatedbidder.dateadded,
        bestevaluatedbidder.date_of_display,
        evaluation_methods.evaluation_method_name,
        bestevaluatedbidder.ispublished,
        bestevaluatedbidder.isreviewed,
        bestevaluatedbidder.id,
        bidinvitations.id as bid_id,
        bestevaluatedbidder.contractprice,
        bestevaluatedbidder.seerialnumber,
        bestevaluatedbidder.review_level,
        bestevaluatedbidder.contractprice,
        bestevaluatedbidder.authorized_by,
        bestevaluatedbidder.signature_date,
        bestevaluatedbidder.reason_for_cancellation,
        bestevaluatedbidder.date_cancelled,
        bestevaluatedbidder.who_cancelled,
        IF(receipts.providerid > 0,receipts.providerid,joint_venture.providers) as providers,
        bestevaluatedbidder.beb_expiry_date , receipts.receiptid,
        (SELECT CONCAT(users.firstname, \' \', users.lastname) AS approver_fullname FROM users WHERE users.userid = bestevaluatedbidder.author LIMIT 1) AS author_fullname,
        (SELECT CONCAT(users.firstname, \' \', users.lastname) AS approver_fullname FROM users WHERE users.userid = bestevaluatedbidder.who_cancelled LIMIT 1) AS author_canceled,
        pdes.pdename,
        procurement_types.title AS procurement_type_title


',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('bidinvitations', 'bidinvitations.id = receipts.bid_id');
        $this->db->join('procurement_plan_entries', 'bidinvitations.procurement_id = procurement_plan_entries.id');
        $this->db->join('bestevaluatedbidder', 'receipts.receiptid = bestevaluatedbidder.pid');
        $this->db->join('procurement_methods AS PM', 'PM.id = bidinvitations.procurement_method_ifb','left');
        $this->db->join('procurement_plans', 'procurement_plan_entries.procurement_plan_id = procurement_plans.id');
        $this->db->join('procurement_methods', 'procurement_methods.id = procurement_plan_entries.procurement_method','left');
        $this->db->join('pdes', 'procurement_plans.pde_id = pdes.pdeid');
        $this->db->join('users', 'pdes.pdeid = users.pde ');
        $this->db->join('joint_venture', 'joint_venture.jv = receipts.joint_venture', 'left');
        $this->db->join('evaluation_methods', 'evaluation_methods.evaluation_method_id = bestevaluatedbidder.type_oem');
        $this->db->join('procurement_types', 'procurement_plan_entries.procurement_type = procurement_types.id','left');


        #=====================
        # START THE WHERES
        #=====================
        $this->db->where('bestevaluatedbidder.isactive', 'Y');
        $this->db->where('bidinvitations.isactive', 'Y');
        $this->db->where('procurement_plan_entries.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('bestevaluatedbidder.beb_expiry_date <', database_ready_format(date('m/d/Y')));
        $this->db->where('bidinvitations.id NOT IN ( select bidinvitation_id FROM contracts where isactive="Y")', NULL, FALSE);





        if($from&&$to){
            $this->db->where('bidinvitations.contract_award_date >=',$from);
            $this->db->where('bidinvitations.contract_award_date <=',$to);
        }

        if($financial_year){
            $this->db->where('procurement_plans.financial_year LIKE','%'.$financial_year.'%');

        }



        if ($pde) {
            $this->db->where('pdes.pdeid',$pde);
        }

        $this->db->order_by('bestevaluatedbidder.dateadded','desc');




        $query = $this->db->get();

        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count(unique_multidim_array($query->result_array(),'id')));
//        print_array(unique_multidim_array($query->result_array(),'id'));
//        exit;
        return  $query->result_array();

    }





    // =======================================================================//
    // ! BEST EVALUATED BIDDERS UNDER ADMIN REVIEW
    // =======================================================================//
    function get_best_evaluated_bidders_under_admin_review($from, $to, $pde = '', $lots = 'N')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->distinct();
        $this->db->select('
        IF(contracts.lotid > 0, (SELECT lot_number FROM lots WHERE id = contracts.lotid) , "") as lot_title,
receipts.receiptid,
receipts.nationality,
receipts.beb,
receipts.reason,
receipts.joint_venture,
bidinvitations.id,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.dateadded AS bid_dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds,
bidinvitations.bidvalidityperiod,
bidinvitations.bidvalidity,
bidinvitations.quantity,
bidinvitations.haslots,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.procurement_plan_id,
procurement_plans.pde_id,
pdes.pdename,
pdes.pdeid,
if(receipts.providerid > 0, receipts.providerid , joint_venture.providers ) as providernames,
procurement_plans.title AS procurement_plan_title,
procurement_types.title AS procurement_type_name,
procurement_types.evaluation_time,
contracts.lotid,
contract_prices.amount,
contract_prices.xrate',false);
        $this->db->from('receipts');
        $this->db->join('bidinvitations', 'bidinvitations.id = receipts.bid_id');
        $this->db->join('procurement_plan_entries', 'bidinvitations.procurement_id = procurement_plan_entries.id');
        $this->db->join('procurement_plans', 'procurement_plan_entries.procurement_plan_id = procurement_plans.id');
        $this->db->join('pdes', 'procurement_plans.pde_id = pdes.pdeid');
        $this->db->join('procurement_types', 'procurement_plan_entries.procurement_type = procurement_types.id');
        $this->db->join('contracts', 'contracts.bidinvitation_id = bidinvitations.id');
        $this->db->join('contract_prices', 'contract_prices.contract_id = contracts.id');
        $this->db->join('joint_venture', 'joint_venture.jv = receipts.joint_venture', 'left');
        $this->db->join('beb_review_details', 'bidinvitations.id = beb_review_details.bidid');

        $this->db->where('bidinvitations.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('receipts.beb', 'Y');
        $this->db->where('bidinvitations.dateadded >=',$from);
        $this->db->where('bidinvitations.dateadded <=',$to);
        $this->db->where('procurement_plans.isactive', 'Y');

        $this->db->order_by('contracts.date_signed','desc');
        if ($pde) {
            $this->db->where('pdes.pdeid =',$pde);
        }
        $query = $this->db->get();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//        exit;
        return  $query->result_array();
    }


    // =======================================================================//
    // ! ALL INVITATION FOR BIDS
    // =======================================================================//
    function get_invitation_for_bids($from, $to, $pde = '')
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
         IF(BI.procurement_method_ifb > 0, PM.title , PM.title) as procurement_method_title,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type',false
        );
        $this->db->from('bidinvitations AS BI');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');

//published identifier (published bids should be approved)

        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        $this->db->where('BI.isactive', 'Y');

        $this->db->where('PP.financial_year', substr($from,0,4).'-'.substr($to,0,4));


        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }

        $this->db->order_by("BI.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();
    }



    // =======================================================================//
    // ! ACTIVE INVITATION FOR BIDS
    // =======================================================================//
    function get_active_invitation_for_bids($from, $to, $pde = '')
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');

        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        IF(BI.procurement_method_ifb > 0, PM.title , PM.title) as procurement_method_title,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
(SELECT  CONCAT(users.firstname, \' \', users.lastname)
  AS approver_fullname FROM users
  WHERE users.userid =  BI.approvedby  LIMIT 1) AS approver_fullname,
',false
        );
        $this->db->from('bidinvitations AS BI');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');

        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');

        $this->db->join('bestevaluatedbidder AS BEB', 'BEB.bidid = BI.id', 'left');


//published identifier (published bids should be approved)

        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('BEB.bidid', null);
        $this->db->where('BI.isactive', 'Y');

        $this->db->where('PP.financial_year', substr($from,0,4).'-'.substr($to,0,4));


        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }

        $this->db->order_by("BI.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();
    }


    // =======================================================================//
    // ! ARCHIVED INVITATION FOR BIDS
    // =======================================================================//
    function get_archived_invitation_for_bids($from, $to, $pde = '')
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
         IF(BI.procurement_method_ifb > 0, PM.title , PM.title) as procurement_method_title,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type',false
        );
        $this->db->from('bidinvitations AS BI');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        # at this level it is archived
        $this->db->join('bestevaluatedbidder AS BEB', 'BEB.bidid = BI.id');


        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        $this->db->where('BI.isactive', 'Y');
        $this->db->join('receipts AS RCPT', 'RCPT.bid_id = BI.id');

        $this->db->where('PP.financial_year', substr($from,0,4).'-'.substr($to,0,4));


        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }

        $this->db->where('RCPT.beb', 'Y');

        $this->db->order_by("BI.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();
    }




    // =======================================================================//
    // ! PUBLISHED BEB
    // =======================================================================//

    function get_published_best_evaluated_bidders($from, $to, $pde = '',$financial_year='',$count='',$expired='',$execeeding_timelines='')
    {
        $this->db->cache_on();

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
 BEB.date_oaoterbt_cc,
 IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
 IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

  BEB.nationality AS beb_nationality,
  BEB.contractprice AS beb_contractprice,
  BEB.exchange_rate,
  IF(BEB.exchange_rate > 0,BEB.exchange_rate*BEB.contractprice,BEB.contractprice) as normalized_contractprice,
  BEB.currency,
  BEB.ispublished,
  BEB.lotid,
  BEB.date_of_display,
  BEB.final_evaluation_report_approval_date,
  BEB.evaluation_commencement_date,
  BEB.type_oem,
  BEB.date_oce_r,
  BEB.ddate_octhe,
  BEB.ddate_octhe,
  BEB.beb_expiry_date,
  receipts.nationality,
  receipts.reason,
  receipts.joint_venture,
  receipts.readoutprice,
  receipts.currence,
  receipts.datereceived,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
BEB.date_of_display AS display_of_beb_notice,
BI.bid_evaluation_from,
BI.bid_evaluation_to,
BI.cc_approval_date,
EM.evaluation_method_name,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
BI.estimated_amount,
BI.estimated_amount_currency,
BI.subject_details,
PP.financial_year,
BI.estimated_amount_exchange_rate,
IF(BI.estimated_amount_exchange_rate >0,BI.estimated_amount_exchange_rate * BI.estimated_amount,BI.estimated_amount) as normalized_estimated_amount,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
PT.id AS procurement_type_id ,
PT.title AS procurement_type_title ,
(SELECT providernames from providers WHERE providerid=receipts.providerid) AS providernames
  ',false
        );
        $this->db->from('bestevaluatedbidder AS BEB');
        $this->db->join('receipts', 'BEB.pid  = receipts.receiptid');
        $this->db->join('bidinvitations AS BI', 'receipts.bid_id  = BI.id');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');
        $this->db->join('evaluation_methods AS EM', 'EM.evaluation_method_id = BEB.type_oem', 'left');



        $this->db->where('receipts.beb', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('BEB.isactive', 'Y');

        # if ranges are provided
        if($from&&$to){
            $this->db->where('BI.dateadded >=',$from);
            $this->db->where('BI.dateadded <=',$to);
        }

        # if financial years are set
        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }else{
            if($from&&$to){
                $this->db->where('BI.dateadded >=', $from);
                $this->db->where('BI.dateadded <=', $to);
            }
        }


        # if pde is set
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }

        # if expired is set
        if($expired){
            $this->db->where('BI.display_of_beb_notice <', database_ready_format(date('m/d/Y')));

        }

        if($execeeding_timelines=='Y'){
            $this->db->where('BI.cc_approval_date >=', 'BI.bid_evaluation_to');

        }



        $query = $this->db->get();
        $this->db->cache_off();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        $res = $query->result_array();

        if (strtoupper($count) == 'Y') {
            $res = $query->num_rows();
        }


        return $res;


    }


    // =======================================================================//
    // ! ATTEMPTED BEB AWARD TO SUSPENDED PROVIDERS
    // =======================================================================//

    function get_attempted_beb_award_to_suspended_provider($from, $to, $pde = '',$financial_year='',$count='',$expired='',$execeeding_timelines='')
    {
        $this->db->cache_on();

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
 IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

attempted_beb_award.bidid,
BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
receipts.datereceived AS bid_dateadded,
BI.display_of_beb_notice,
BI.bid_evaluation_from,
BI.bid_evaluation_to,
BI.cc_approval_date,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
BI.estimated_amount,
BI.estimated_amount_currency,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
BI.estimated_amount,
BI.estimated_amount_currency,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
PT.id AS procurement_type_id ,
PT.title AS procurement_type_title,
receipts.providerid,
attempted_beb_award.providernames AS providernames


  ',false
        );
        $this->db->from('attempted_beb_award');

        $this->db->join('bidinvitations AS BI', 'BI.id  = attempted_beb_award.bidid');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');
        $this->db->join('receipts', 'receipts.bid_id = BI.id', 'left');



        $query = $this->db->get();
        $this->db->cache_off();




//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        $res = $query->result_array();

        if (strtoupper($count) == 'Y') {
            $res = $query->num_rows();
        }


        return $res;


    }



    // =======================================================================//
    // ! PUBLISHED INVITATION FOR BIDS
    // =======================================================================//
    //TODO $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
    function get_published_invitation_for_bids($from, $to, $pde = '',$financial_year='',$count='',$expired='',$micro='',$direct='')
    {
        $this->db->cache_on();
        $this->db->distinct('BI.id');
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
BI.estimated_amount,
BI.estimated_amount_currency,
BI.estimated_amount_exchange_rate,
BI.subject_details,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
PT.title AS procurement_type_title
',false
        );
        $this->db->from('bidinvitations AS BI');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id','left');



        //published identifier (published bids should be approved)
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('BI.invitation_to_bid_date !=', '0000-00-00 00:00:00');


        # if ranges are provided
        if($from&&$to){
            $this->db->where('BI.dateadded >=',$from);
            $this->db->where('BI.dateadded <=',$to);
        }

        # if financial years are set
        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }else{
            if($from&&$to){
                $this->db->where('BI.dateadded >=', $from);
                $this->db->where('BI.dateadded <=', $to);
            }
        }


        # if pde is set
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }

        # if expired is set
        if($expired){
            $this->db->where('BI.bid_submission_deadline <', NOW());

        }

        # when excluding micros
        if ($micro=='N') {
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 10);

        }

        # when excluding direct procurements
        if ($direct=='N') {
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 8);

        }

        $this->db->order_by("BI.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();



//
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        $res = $query->result_array();

        if (strtoupper($count) == 'Y') {
            $res = $query->num_rows();
        }


        return $res;


    }



    // =======================================================================//
    // ! PUBLISHED INVITATION FOR BIDS
    // =======================================================================//
    function get_search_published_invitation_for_bids($pde = '',$funding_source='',$proc_method,$proc_type,$proc_subj,$financial_year)
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
       IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,

         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
BI.bid_openning_date,
BI.pde_id,
BI.cost_estimate,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.description_of_works,
BI.bid_security_amount,
BI.bid_security_currency,
BI.bid_documents_price,
BI.author AS bid_author,
BI.isapproved AS bid_approved,
BI.dateadded AS bid_dateadded,
BI.approvedby AS bid_approvedby,
BI.isactive AS bid_isactive,
BI.date_approved AS bid_date_approved,
BI.approval_comments AS bid_approval_comments,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.haslots,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
FS.title AS funding_source,
(SELECT currencies.title FROM currencies  WHERE currencies.id = PPE.currency  LIMIT 1) AS  cost_estimate_currency,

(SELECT currencies.title FROM currencies  WHERE currencies.id =  BI.bid_security_currency  LIMIT 1) AS  bid_security_currency_title




',false
        );
        $this->db->from('bidinvitations AS BI');
        // a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('funding_sources AS FS', 'FS.id = PPE.funding_source');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');

        //published identifier (published bids should be approved)
        $this->db->where('BI.isapproved', 'Y');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        $this->db->where('BI.isactive', 'Y');

        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }


        if($proc_type){
            $this->db->where('PT.id', $proc_type);

        }



        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }



        if ($funding_source) {
            $this->db->where('FS.id', $funding_source);
        }

        if ($proc_method) {
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) =', $proc_method);

        }

        if ($proc_subj) {
            $this->db->where('PPE.subject_of_procurement LIKE', $proc_subj);

        }



        $this->db->order_by("BI.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();




//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();


    }


    // =======================================================================//
    // ! PAGINATED PUBLISHED INVITATION FOR BIDS
    // =======================================================================//
    function get_paginated_published_invitation_for_bids($limit, $start, $term)
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
       IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,

         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
BI.bid_openning_date,
BI.pde_id,
BI.cost_estimate,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.description_of_works,
BI.bid_security_amount,
BI.bid_security_currency,
BI.bid_documents_price,
BI.author AS bid_author,
BI.isapproved AS bid_approved,
BI.dateadded AS bid_dateadded,
BI.approvedby AS bid_approvedby,
BI.isactive AS bid_isactive,
BI.date_approved AS bid_date_approved,
BI.approval_comments AS bid_approval_comments,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.haslots,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
FS.title AS funding_source,
(SELECT currencies.title FROM currencies  WHERE currencies.id = PPE.currency  LIMIT 1) AS  cost_estimate_currency,

(SELECT currencies.title FROM currencies  WHERE currencies.id =  BI.bid_security_currency  LIMIT 1) AS  bid_security_currency_title




',false
        );
        $this->db->from('bidinvitations AS BI');
        // a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('funding_sources AS FS', 'FS.id = PPE.funding_source');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');

        //published identifier (published bids should be approved)
        $this->db->where('BI.isapproved', 'Y');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        $this->db->where('BI.isactive', 'Y');


        if ($term) {
            $this->db->where('PPE.subject_of_procurement LIKE', '%'.$term.'%');
            $this->db->or_where('pdes.pdename LIKE', '%'.$term.'%');
            $this->db->or_where('PP.financial_year LIKE', '%'.$term.'%');
            $this->db->or_where('BI.procurement_ref_no LIKE', '%'.$term.'%');

        }

        $this->db->limit($limit, $start);



        $this->db->order_by("BI.bid_submission_deadline", "desc");

        $query = $this->db->get();
        $this->db->cache_off();




//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();


    }


    // =======================================================================//
    // ! PAGINATED BEBS
    // =======================================================================//
    function get_paginated_bebs($limit, $start, $term)
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
receipts.receiptid,
receipts.nationality,
receipts.beb,
receipts.reason,
receipts.joint_venture,
BI.id AS bidinvitation_id,
BI.id AS bid_id,
BI.vote_no,
BI.initiated_by,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.pre_bid_meeting_date,
BI.cc_approval_date,
BI.bid_receipt_address,
BI.bid_openning_address,
BI.procurement_ref_no,
BI.procurement_id,
BI.date_approved,
BI.dateadded,
BI.approvedby,
BI.approval_comments,
BI.isactive,
BI.bid_submission_deadline,
BI.bid_evaluation_to,
BI.bid_evaluation_from,
BI.display_of_beb_notice,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.bidvalidityperiod,
BI.bidvalidity,
BI.quantity,
BI.haslots,
BI.dateadded AS bid_dateadded,
BEB.type_oem,
BEB.ddate_octhe,
BEB.num_orb,
BEB.date_oce_r,
BEB.nationality,
BEB.contractprice,
BEB.currency,
BEB.dateadded,
BEB.beb_expiry_date,
BEB.date_of_display,
BEB.ispublished,
BEB.isreviewed,
BEB.id,
BEB.id as bid_id,
BEB.seerialnumber,
BEB.review_level,
BEB.contractprice,
BEB.currency,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.procurement_method,
PPE.pde_department,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PP.pde_id,
pdes.pdename,
IF(receipts.providerid > 0,receipts.providerid,JV.providers) as providers,
 (SELECT  CONCAT(users.firstname, \' \', users.lastname)
  AS approver_fullname FROM users
  WHERE users.userid =  BEB.author  LIMIT 1) AS author_fullname

',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('bidinvitations AS BI', 'BI.id = receipts.bid_id');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('joint_venture AS JV', 'JV.jv = receipts.joint_venture', 'left');
        $this->db->join('evaluation_methods AS EM ', 'EM.evaluation_method_id = BEB.type_oem');

        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('receipts.beb', 'Y');

        if ($term) {
            $this->db->where('PPE.subject_of_procurement LIKE', '%'.$term.'%');
            $this->db->or_where('pdes.pdename LIKE', '%'.$term.'%');
            $this->db->or_where('PP.financial_year LIKE', '%'.$term.'%');
            $this->db->or_where('BI.procurement_ref_no LIKE', '%'.$term.'%');


        }
        //$this->db->where('BEB.beb_expiry_date >=', database_ready_format(date('d.m.y')));

        $this->db->limit($limit, $start);



        $query = $this->db->get();
        $this->db->cache_off();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();


    }




    // =======================================================================//
    // ! BIDS DUE TO CLOSE
    // =======================================================================//
    function get_bids_due_to_close($from, $to, $pde = '')
    {
        $this->db->cache_on();
        $this->db->distinct('BI.id');
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
BI.subject_details,
BI.estimated_amount_exchange_rate,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
BI.estimated_amount,
BI.estimated_amount_currency,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
PT.title AS procurement_type_title
',false
        );
        $this->db->from('bidinvitations AS BI');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id','left');



        //published identifier (published bids should be approved)
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('BI.invitation_to_bid_date !=', '0000-00-00 00:00:00');


        # if ranges are provided
        # if ranges are provided
        $this->db->where('BI.bid_submission_deadline >=',$from);
        $this->db->where('BI.bid_submission_deadline <=',$to);




        # if pde is set
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }


        $this->db->order_by("BI.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();




//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        $res = $query->result_array();


        return $res;


    }

    //get bid_invitation_id_by_procutement id
    function get_bid_invitation_by_procurement_id($procurement_id)
    {

        $id = '';

        $results = $this->custom_query("SELECT
bidinvitations.id
FROM
bidinvitations
WHERE
bidinvitations.isactive = 'Y' AND
bidinvitations.procurement_id = $procurement_id");

        foreach ($results as $row) {
            $id = $row['id'];
        }


        return $id;

    }



    // =======================================================================//
    // ! ALL INVITATION FOR BIDS
    // =======================================================================//
    function get_bid_invitation_info($bid_id)
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->select('
        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
         IF(BI.procurement_method_ifb > 0, PM.title , PM.title) as procurement_method_title,
        BI.id,
BI.id AS bidinvitation_id,
BI.vote_no,
BI.date_initiated,
BI.invitation_to_bid_date,
BI.procurement_id,
BI.isapproved,
BI.procurement_ref_no,
BI.bid_submission_deadline,
BI.contract_award_date,
BI.dateofconfirmationoffunds,
BI.dateadded AS bid_dateadded,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.funder_name,
PPE.estimated_amount,
PP.financial_year,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type',false
        );
        $this->db->from('bidinvitations AS BI');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');

//published identifier (published bids should be approved)

        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        $this->db->where('BI.id', $bid_id);

        $query = $this->db->get();
        $this->db->cache_off();
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//
//        print_array($query->result_array());
//        exit;

        return $query->result_array();
    }






    function get_contract_price_info_by_contract($passed_id, $param)
    {

        //if NO ID
        if ($passed_id == '') {
            return NULL;
        } else {
            //get user info
            $query = $this->db->select()->from($this->_tablename)->where('contract_id', $passed_id)->get();
            //echo $this->db->last_query();

            if ($query->result_array()) {
                foreach ($query->result_array() as $row) {
                    //filter results
                    switch ($param) {
                        case 'id':
                            $result = $row['id'];
                            break;

                        case 'amount':
                            $result = $row['amount'];
                            break;

                        case 'xrate':
                            $result = $row['xrate'];
                            break;
                        case 'rate':
                            $result = $row['xrate'];
                            break;
                        case 'currency_id':
                            $result = $row['currency_id'];
                            break;

                        case 'currency':
                            $result = get_currency_info_by_id($row['currency_id'], 'abbrv');
                            break;

                        case 'dateadded':
                            $result = $row['dateadded'];
                            break;

                        default:
                            $result = $query->result_array();
                    }

                }

                return $result;
            }

        }
    }

    function get_invitation_for_bids_by_month($from, $to, $pde = '', $lots = 'N')
    {
        if ($pde) {
            $results = $this->custom_query("SELECT
bidinvitations.id,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds
FROM
(bidinvitations)
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
bidinvitations.isactive = 'Y' AND
bidinvitations.isapproved = 'Y' AND
bidinvitations.invitation_to_bid_date >= '" . $from . "' AND
bidinvitations.invitation_to_bid_date <= '" . $to . "' AND
pdes.pdeid = " . $pde . " AND
bidinvitations.haslots = '$lots'
ORDER BY id DESC
");
        } else {
            //if pde is passed
            $results = $this->custom_query("SELECT
bidinvitations.id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds
FROM
(bidinvitations)
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
bidinvitations.isactive = 'Y' AND
bidinvitations.isapproved = 'Y' AND
bidinvitations.invitation_to_bid_date >= '" . $from . "' AND
bidinvitations.invitation_to_bid_date <= '" . $to . "' AND
bidinvitations.haslots = '$lots'
ORDER BY id DESC
");

        }



        return $results;

    }


    function get_bids_below_threshhold($from, $to, $pde = '', $threshold = 14)
    {
        if ($pde) {
            $results = $this->custom_query("SELECT
bidinvitations.id,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds
FROM
bidinvitations
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
bidinvitations.bid_submission_deadline - bidinvitations.invitation_to_bid_date < " . $threshold . " AND
bidinvitations.date_approved >= '" . $from . "' AND
bidinvitations.date_approved <= '" . $to . "'
pdes.pdeid = " . $pde . "
ORDER BY id DESC
");
        } else {
            //if no pde is passed
            $results = $this->custom_query("SELECT
bidinvitations.id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds
FROM
bidinvitations
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
bidinvitations.bid_submission_deadline - bidinvitations.invitation_to_bid_date < " . $threshold . " AND
bidinvitations.date_approved >= '" . $from . "' AND
bidinvitations.date_approved <= '" . $to . "'

");


        }
        return $results;
    }

    function get_bids_equal_to_threshhold($from, $to, $pde = '', $threshold = 14)
    {
        if ($pde) {
            $results = $this->custom_query("SELECT
bidinvitations.id,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds
FROM
bidinvitations
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
bidinvitations.bid_submission_deadline - bidinvitations.invitation_to_bid_date = " . $threshold . " AND
bidinvitations.date_approved >= '" . $from . "' AND
bidinvitations.date_approved <= '" . $to . "' AND
pdes.pdeid = " . $pde . "
ORDER BY id DESC
");
        } else {
            //if no pde is passed
            $results = $this->custom_query("SELECT
bidinvitations.id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds
FROM
bidinvitations
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
bidinvitations.bid_submission_deadline - bidinvitations.invitation_to_bid_date = " . $threshold . " AND
bidinvitations.date_approved >= '" . $from . "' AND
bidinvitations.date_approved <= '" . $to . "'

");


        }
        return $results;
    }


    function get_expired_bids_by_month($from, $to, $pde = '', $lots = 'N')
    {

        if ($pde) {
            $results = $this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.details,
receipts.received_by,
receipts.datereceived,
receipts.approved,
receipts.nationality,
receipts.author,
receipts.dateadded,
receipts.beb,
receipts.reason,
receipts.isactive,
receipts.joint_venture,
receipts.readoutprice,
receipts.currence,
providers.providerid,
providers.providernames,
bidinvitations.id,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds,
procurement_plan_entries.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.currency,
procurement_plan_entries.exchange_rate,
procurement_plan_entries.pre_bid_events_date,
procurement_plan_entries.pre_bid_events_duration,
procurement_plan_entries.contracts_committee_approval_date,
procurement_plan_entries.contracts_committee_approval_date_duration,
procurement_plan_entries.publication_of_pre_qualification_date,
procurement_plan_entries.publication_of_pre_qualification_date_duration,
procurement_plan_entries.proposal_submission_date,
procurement_plan_entries.proposal_submission_date_duration,
procurement_plan_entries.contracts_committee_approval_of_shortlist_date,
procurement_plan_entries.contracts_committee_approval_of_shortlist_date_duration,
procurement_plan_entries.bid_issue_date,
procurement_plan_entries.bid_issue_date_duration,
procurement_plan_entries.bid_submission_opening_date,
procurement_plan_entries.bid_submission_opening_date_duration,
procurement_plan_entries.secure_necessary_approval_date,
procurement_plan_entries.secure_necessary_approval_date_duration,
procurement_plan_entries.contract_award,
procurement_plan_entries.contract_award_duration,
procurement_plan_entries.performance_security,
procurement_plan_entries.best_evaluated_bidder_date,
procurement_plan_entries.best_evaluated_bidder_date_duration,
procurement_plan_entries.contract_sign_date,
procurement_plan_entries.contract_sign_duration,
procurement_plan_entries.submission_of_evaluation_report_to_cc,
procurement_plan_entries.cc_approval_of_evaluation_report,
procurement_plan_entries.accounting_officer_approval_date,
procurement_plan_entries.cc_approval_of_evaluation_report_duration,
procurement_plan_entries.negotiation_date,
procurement_plan_entries.negotiation_date_duration,
procurement_plan_entries.negotiation_approval_date,
procurement_plan_entries.negotiation_approval_date_duration,
procurement_plan_entries.advanced_payment_date,
procurement_plan_entries.advanced_payment_date_duration,
procurement_plan_entries.mobilise_advance_payment,
procurement_plan_entries.mobilise_advance_payment_duration,
procurement_plan_entries.substantial_completion,
procurement_plan_entries.substantial_completion_duration,
procurement_plan_entries.final_acceptance,
procurement_plan_entries.final_acceptance_duration,
procurement_plan_entries.dateadded,
procurement_plan_entries.dateupdated,
procurement_plan_entries.updated_by,
procurement_plan_entries.isactive,
procurement_plan_entries.procurement_plan_id,
procurement_plan_entries.solicitor_general_approval_date,
procurement_plan_entries.solicitor_general_approval_duration,
procurement_plan_entries.contract_amount_in_ugx,
procurement_plan_entries.bid_closing_date,
procurement_plan_entries.author,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.create_date,
pdes.created_by,
pdes.category,
pdes.type,
pdes.`code`,
pdes.pde_roll_cat,
pdes.address,
pdes.tel,
pdes.fax,
pdes.email,
pdes.website,
pdes.AO,
pdes.AO_phone,
pdes.AO_email,
pdes.CC,
pdes.CC_phone,
pdes.CC_email,
pdes.head_PDU,
pdes.head_PDU_phone,
pdes.head_PDU_email,
pdes.isactive,
procurement_plans.id,
procurement_plans.pde_id,
procurement_plans.financial_year,
procurement_plans.title,
procurement_plans.summarized_plan,
procurement_plans.dateadded,
procurement_plans.dateupdated,
procurement_plans.author,
procurement_plans.isactive,
procurement_plans.description,
procurement_plans.public,
procurement_types.id,
procurement_types.title,
procurement_types.`code`,
procurement_types.slug,
procurement_types.evaluation_time,
procurement_types.dateadded,
procurement_types.dateupdated,
procurement_types.isactive
FROM
receipts
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN procurement_types ON procurement_plan_entries.procurement_type = procurement_types.id
WHERE
receipts.beb = 'Y' AND
bidinvitations.bid_submission_deadline < '" . mysqldate() . "' AND
receipts.datereceived >= '" . $from . "' AND
receipts.datereceived <= '" . $to . "'  AND
pdes.pdeid = " . $pde . " AND
bidinvitations.haslots = '$lots' AND
  procurement_plans.isactive='Y'
ORDER BY
receipts.receiptid DESC
");
        } else {
            $results = $this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.details,
receipts.received_by,
receipts.datereceived,
receipts.approved,
receipts.nationality,
receipts.author,
receipts.dateadded,
receipts.beb,
receipts.reason,
receipts.isactive,
receipts.joint_venture,
receipts.readoutprice,
receipts.currence,
providers.providerid,
providers.providernames,
bidinvitations.id,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.pde_id,
bidinvitations.subject_of_procurement,
bidinvitations.cost_estimate,
bidinvitations.invitation_to_bid_date,
bidinvitations.pre_bid_meeting_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.documents_inspection_address,
bidinvitations.documents_address_issue,
bidinvitations.bid_openning_address,
bidinvitations.procurement_ref_no,
bidinvitations.procurement_id,
bidinvitations.description_of_works,
bidinvitations.bid_security_amount,
bidinvitations.bid_security_currency,
bidinvitations.bid_documents_price,
bidinvitations.bid_documents_currency,
bidinvitations.author,
bidinvitations.isapproved,
bidinvitations.date_approved,
bidinvitations.dateadded,
bidinvitations.approvedby,
bidinvitations.approval_comments,
bidinvitations.isactive,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.bid_evaluation_from,
bidinvitations.display_of_beb_notice,
bidinvitations.contract_award_date,
bidinvitations.dateofconfirmationoffunds,
procurement_plan_entries.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.currency,
procurement_plan_entries.exchange_rate,
procurement_plan_entries.pre_bid_events_date,
procurement_plan_entries.pre_bid_events_duration,
procurement_plan_entries.contracts_committee_approval_date,
procurement_plan_entries.contracts_committee_approval_date_duration,
procurement_plan_entries.publication_of_pre_qualification_date,
procurement_plan_entries.publication_of_pre_qualification_date_duration,
procurement_plan_entries.proposal_submission_date,
procurement_plan_entries.proposal_submission_date_duration,
procurement_plan_entries.contracts_committee_approval_of_shortlist_date,
procurement_plan_entries.contracts_committee_approval_of_shortlist_date_duration,
procurement_plan_entries.bid_issue_date,
procurement_plan_entries.bid_issue_date_duration,
procurement_plan_entries.bid_submission_opening_date,
procurement_plan_entries.bid_submission_opening_date_duration,
procurement_plan_entries.secure_necessary_approval_date,
procurement_plan_entries.secure_necessary_approval_date_duration,
procurement_plan_entries.contract_award,
procurement_plan_entries.contract_award_duration,
procurement_plan_entries.performance_security,
procurement_plan_entries.best_evaluated_bidder_date,
procurement_plan_entries.best_evaluated_bidder_date_duration,
procurement_plan_entries.contract_sign_date,
procurement_plan_entries.contract_sign_duration,
procurement_plan_entries.submission_of_evaluation_report_to_cc,
procurement_plan_entries.cc_approval_of_evaluation_report,
procurement_plan_entries.accounting_officer_approval_date,
procurement_plan_entries.cc_approval_of_evaluation_report_duration,
procurement_plan_entries.negotiation_date,
procurement_plan_entries.negotiation_date_duration,
procurement_plan_entries.negotiation_approval_date,
procurement_plan_entries.negotiation_approval_date_duration,
procurement_plan_entries.advanced_payment_date,
procurement_plan_entries.advanced_payment_date_duration,
procurement_plan_entries.mobilise_advance_payment,
procurement_plan_entries.mobilise_advance_payment_duration,
procurement_plan_entries.substantial_completion,
procurement_plan_entries.substantial_completion_duration,
procurement_plan_entries.final_acceptance,
procurement_plan_entries.final_acceptance_duration,
procurement_plan_entries.dateadded,
procurement_plan_entries.dateupdated,
procurement_plan_entries.updated_by,
procurement_plan_entries.isactive,
procurement_plan_entries.procurement_plan_id,
procurement_plan_entries.solicitor_general_approval_date,
procurement_plan_entries.solicitor_general_approval_duration,
procurement_plan_entries.contract_amount_in_ugx,
procurement_plan_entries.bid_closing_date,
procurement_plan_entries.author,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.create_date,
pdes.created_by,
pdes.category,
pdes.type,
pdes.`code`,
pdes.pde_roll_cat,
pdes.address,
pdes.tel,
pdes.fax,
pdes.email,
pdes.website,
pdes.AO,
pdes.AO_phone,
pdes.AO_email,
pdes.CC,
pdes.CC_phone,
pdes.CC_email,
pdes.head_PDU,
pdes.head_PDU_phone,
pdes.head_PDU_email,
pdes.isactive,
procurement_plans.id,
procurement_plans.pde_id,
procurement_plans.financial_year,
procurement_plans.title,
procurement_plans.summarized_plan,
procurement_plans.dateadded,
procurement_plans.dateupdated,
procurement_plans.author,
procurement_plans.isactive,
procurement_plans.description,
procurement_plans.public,
procurement_types.id,
procurement_types.title,
procurement_types.`code`,
procurement_types.slug,
procurement_types.evaluation_time,
procurement_types.dateadded,
procurement_types.dateupdated,
procurement_types.isactive
FROM
receipts
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN procurement_types ON procurement_plan_entries.procurement_type = procurement_types.id
WHERE
receipts.beb = 'Y' AND
bidinvitations.bid_submission_deadline < '" . mysqldate() . "' AND
receipts.datereceived >= '" . $from . "' AND
receipts.datereceived <= '" . $to . "' AND
bidinvitations.haslots = '$lots' AND
  procurement_plans.isactive='Y'
ORDER BY
receipts.receiptid DESC

");
        }


        return $results;
    }



//INNER JOIN beb_review_details ON bidinvitations.id = beb_review_details.bidid


    function get_bid_invitation_info_by_procurement($procurement_id, $param)
    {

        //if NO ID
        if ($procurement_id == '') {
            return NULL;
        } else {
            //get user info
            $query = $this->db->select()->from($this->_tablename)->where('procurement_id', $procurement_id)->get();

            if ($query->result_array()) {
                foreach ($query->result_array() as $row) {
                    //filter results
                    switch ($param) {
                        case 'id':
                            $result = $row['id'];
                            break;
                        case 'bid_invitation_id':
                            $result = $row['id'];
                            break;

                        case 'procurement':
                            $result = get_procurement_plan_entry_info($row['procurement_id'], 'title');
                            break;

                        case 'procurement_method':
                            $result = get_procurement_plan_entry_info($row['procurement_id'], 'procurement_method');
                            break;
                        case 'procurement_value':
                            $result = get_procurement_plan_entry_info($row['procurement_id'], 'estimated_amount');
                            break;

                        case 'amount':
                            $result = $row['amount'];
                            break;

                        case 'xrate':
                            $result = $row['xrate'];
                            break;
                        case 'currency_id':
                            $result = $row['currency_id'];
                            break;

                        case 'currency':
                            $result = get_currency_info_by_id($row['currency_id'], 'abbrv');
                            break;

                        case 'dateadded':
                            $result = $row['dateadded'];
                            break;

                        case 'date_confirmed':
                            $result = $row['dateofconfirmationoffunds'];
                            break;

                        case 'procurement_ref_no':
                            $result = $row['procurement_ref_no'];
                            break;
                        case 'bid_submission_deadline':
                            $result = $row['currency_id'];
                            break;

                        default:
                            $result = $query->result_array();
                    }

                }

                return $result;
            }

        }
    }

    function get_beb_by_procurement_ref_num($procurement_ref_num)
    {
        //print_array($procurement_ref_num);
        $results = $this->custom_query("SELECT
receipts.receiptid,
receipts.providerid,
receipts.approved,
receipts.nationality,
bidinvitations.vote_no,
providers.providernames,
contracts.final_contract_value,
contracts.total_actual_payments,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.estimated_amount,
pdes.type,
pdes.pdename,
pdes.pdeid,
procurement_plan_entries.id,
procurement_plans.financial_year,
procurement_plans.title,
procurement_plans.description,
procurement_plans.summarized_plan,
bidinvitations.id AS bidinvitation_id,
bidinvitations.dateofconfirmationoffunds,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.initiated_by,
contracts.dateawarded,
contracts.date_signed,
contracts.completion_date,
contracts.admin_review
FROM
receipts
INNER JOIN bidinvitations ON receipts.bid_id = bidinvitations.id
INNER JOIN contracts ON bidinvitations.procurement_id = contracts.procurement_ref_id
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN procurement_plan_entries ON procurement_plan_entries.id = contracts.procurement_ref_id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
WHERE
ppda.receipts.approved = 'Y' AND
receipts.beb = 'Y' AND
receipts.isactive = 'Y' AND
bidinvitations.isactive = 'Y' AND
bidinvitations.isapproved = 'Y' AND
procurement_plan_entries.procurement_ref_no = '$procurement_ref_num'
ORDER BY
receipts.receiptid DESC");


        return $results;
    }

    //get beb by bid
    function get_beb_by_id($bid_id){
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select(
            'bidinvitations.id',
            'contract_prices.amount',
            'contract_prices.xrate',
            'providers.providerid',
            'providers.providernames',
            'receipts.nationality',
            'receipts.joint_venture',
            'contracts.date_signed',
            'contracts.actual_completion_date',
            'contracts.lotid',
            'bidinvitations.haslots'
        );
        $this->db->from('bidinvitations');

        $this->db->join('bestevaluatedbidder','bidinvitations.id = bestevaluatedbidder.bidid');
        $this->db->join('contracts','bestevaluatedbidder.bidid = contracts.bidinvitation_id');
        $this->db->join('contract_prices','contracts.id = contract_prices.contract_id');
        $this->db->join('receipts','contracts.bidinvitation_id = receipts.bid_id');
        $this->db->join('providers','receipts.providerid = providers.providerid');

        $this->db->where('receipts.beb','Y');
        $this->db->where('bidinvitations.id',$bid_id);

        $query=$this->db->get();


        return $query->result_array();
    }


    function get_bid_responsiveness_by_bid($bid_id)
    {
        $result = $this->custom_query("SELECT
bestevaluatedbidder.id,
bestevaluatedbidder.pid,
bestevaluatedbidder.bidid,
bestevaluatedbidder.type_oem,
bestevaluatedbidder.ddate_octhe,
bestevaluatedbidder.num_orb,
bestevaluatedbidder.num_orb_local,
bestevaluatedbidder.date_oce_r,
bestevaluatedbidder.date_oaoterbt_cc,
bestevaluatedbidder.beb_expiry_date,
bestevaluatedbidder.final_evaluation_report_approval_date,
bestevaluatedbidder.evaluation_commencement_date,
bestevaluatedbidder.bebid,
bestevaluatedbidder.nationality,
bestevaluatedbidder.contractprice,
bestevaluatedbidder.currency,
bestevaluatedbidder.exchange_rate,
bestevaluatedbidder.author,
bestevaluatedbidder.dateadded,
bestevaluatedbidder.ispublished,
bestevaluatedbidder.isreviewed,
bestevaluatedbidder.lotid,
bestevaluatedbidder.seerialnumber,
bestevaluatedbidder.review_level
FROM
bestevaluatedbidder
WHERE
bestevaluatedbidder.bidid = $bid_id
ORDER BY
bestevaluatedbidder.id DESC
");

        return $result;
    }

    function get_beb_extra_details($bid_id)
    {
        $result = $this->custom_query("SELECT
bestevaluatedbidder.id,
bestevaluatedbidder.pid,
bestevaluatedbidder.bidid,
bestevaluatedbidder.type_oem,
bestevaluatedbidder.ddate_octhe,
bestevaluatedbidder.num_orb,
bestevaluatedbidder.num_orb_local,
bestevaluatedbidder.date_oce_r,
bestevaluatedbidder.date_oaoterbt_cc,
bestevaluatedbidder.beb_expiry_date,
bestevaluatedbidder.final_evaluation_report_approval_date,
bestevaluatedbidder.evaluation_commencement_date,
bestevaluatedbidder.bebid,
bestevaluatedbidder.nationality,
bestevaluatedbidder.contractprice,
bestevaluatedbidder.currency,
bestevaluatedbidder.exchange_rate,
bestevaluatedbidder.author,
bestevaluatedbidder.dateadded,
bestevaluatedbidder.ispublished,
bestevaluatedbidder.isreviewed,
bestevaluatedbidder.lotid,
bestevaluatedbidder.seerialnumber,
bestevaluatedbidder.review_level
FROM
bestevaluatedbidder
WHERE
bestevaluatedbidder.bidid = $bid_id
");

//print_array($this->db->last_query());
//print_array($this->db->last_query());

        return $result;
    }



    #Get Bid Invitation Info by Contract Id
    function get_bid_invitation_info_by_contract_id($contractid)
    {


        $record = $this->db->query("SELECT bidinvitations.procurement_ref_no FROM bidinvitations WHERE bidinvitations.id = (SELECT  contracts.bidinvitation_id
FROM contracts  WHERE contracts.id = ".$contractid." LIMIT 1 )")->result_array();

        return $record;
    }



    # get bids by contract
    function bids_by_contract($contract='',$local='',$foreign='',$responsive=''){
        $this->db->cache_on();

        $this->db->query('SET SQL_BIG_SELECTS=1');

        if($responsive){
            $this->db->select('
        BEB.num_orb AS total_bids
        ',false);
        }else{
            $this->db->select('
        COUNT(*) AS total_bids
        ',false);
        }


        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('contracts', 'contracts.bidinvitation_id=receipts.bid_id','left');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid','left');

        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('contracts.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('BEB.isactive', 'Y');

        if ($local) {
            $this->db->where('receipts.nationality','Uganda');
        }

        if ($foreign) {
            $this->db->where('receipts.nationality !=','Uganda');
        }

        if ($contract) {
            $this->db->where('contracts.id =',$contract);
        }




        $query = $this->db->get();

        $this->db->cache_off();
        foreach($query->result_array() as $row){
            $result=$row['total_bids'];
        }
//        print_array($contract);
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($result);
//        exit;
        return  $result;
    }


    # get bids by contract
    function get_receipts_by_bid($bid_id='',$local='',$foreign='',$responsive=''){
        $this->db->cache_on();

        $this->db->query('SET SQL_BIG_SELECTS=1');

        if($responsive){
            $this->db->distinct('bidinvitations.id');
            $this->db->select('
        BEB.num_orb AS total_bids
        ',false);
        }else{
            $this->db->distinct('bidinvitations.id');
            $this->db->select('
        COUNT(*) AS total_bids
        ',false);
        }


        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('contracts', 'contracts.bidinvitation_id=receipts.bid_id','left');
        $this->db->join('bidinvitations', 'bidinvitations.id=receipts.bid_id');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid','left');

        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('contracts.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('BEB.isactive', 'Y');

        if ($local) {
            $this->db->where('receipts.nationality','Uganda');
        }

        if ($foreign) {
            $this->db->where('receipts.nationality !=','Uganda');
        }

        if ($bid_id) {
            $this->db->where('receipts.bid_id =',$bid_id);
        }




        $query = $this->db->get();

        $this->db->cache_off();
        foreach($query->result_array() as $row){
            $result=$row['total_bids'];
        }
//        print_array($contract);
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($result);
//        exit;
        return  $result;
    }

    # get bids by contract
    function responsive_bids_by_contract($contract='',$local=''){
        $this->db->cache_on();

        $this->db->query('SET SQL_BIG_SELECTS=1');

        if ($local) {
            $this->db->select('
        BEB.num_orb_local AS total_bids
        ',false);
        }else{
            $this->db->select('
        BEB.num_orb AS total_bids
        ',false);
        }


        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('receipts');
        $this->db->join('contracts', 'contracts.receiptid=receipts.receiptid');
        $this->db->join('bestevaluatedbidder AS BEB', 'receipts.receiptid = BEB.pid');

        #=====================
        # START THE WHERES
        #=====================

        $this->db->where('contracts.isactive', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('BEB.isactive', 'Y');


        if ($contract) {
            $this->db->where('contracts.id =',$contract);
        }



        $query = $this->db->get();

        $this->db->cache_off();
        foreach($query->result_array() as $row){
            $result=$row['total_bids'];
        }
//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($result);
//        exit;
        return  $result;
    }

    // =======================================================================//
    // ! ALL BIDS
    // =======================================================================//

    function get_all_bids($from, $to, $pde = '', $financial_year = '', $local = '',$foreign = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('RCPT.receiptid');
        $this->db->select('
        RCPT.receiptid,
        RCPT.nationality,
        beb_review_details.id AS admin_review_id,
        IF((SELECT num_orb FROM bestevaluatedbidder WHERE bestevaluatedbidder.pid=RCPT.receiptid AND bestevaluatedbidder.isactive="Y" ) >0,(SELECT num_orb FROM bestevaluatedbidder WHERE bestevaluatedbidder.pid=RCPT.receiptid AND bestevaluatedbidder.isactive="Y"),"0") AS responsive_bids,
        IF((SELECT num_orb_local FROM bestevaluatedbidder WHERE bestevaluatedbidder.pid=RCPT.receiptid AND bestevaluatedbidder.isactive="Y") >0,(SELECT num_orb_local FROM bestevaluatedbidder WHERE bestevaluatedbidder.pid=RCPT.receiptid AND bestevaluatedbidder.isactive="Y"),"0") AS local_responsive_bids,

        IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,

         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
         (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) as amount,
         (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) as xrate,
        C.id ,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        IF(CV.id>0,CV.new_planned_date_of_completion,C.completion_date) as completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        IF(C.total_actual_payments_exchange_rate>0,C.total_actual_payments_exchange_rate*C.total_actual_payments,C.total_actual_payments) AS total_actual_payments,

        IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount) as estimated_amount_at_ifb,
        (BI.estimated_amount * IF(BI.estimated_amount_exchange_rate>0,BI.estimated_amount_exchange_rate,1)) as normalized_estimated_amount,

        C.final_contract_value_exchange_rate,
        C.total_actual_payments_exchange_rate,
        IF(C.total_actual_payments_exchange_rate>0,C.total_actual_payments_exchange_rate*C.total_actual_payments,C.total_actual_payments) as contract_value,
        IF(C.actual_completion_date IS NOT NULL,IF(C.final_contract_value_exchange_rate>0,C.final_contract_value*C.final_contract_value_exchange_rate,C.final_contract_value),

        IF(PPE.framework="Y", IF((SELECT COUNT(*) FROM call_of_orders WHERE contractid=C.id AND isactive="Y")>0,(SELECT SUM(contract_value) FROM call_of_orders WHERE contractid=C.id AND isactive="Y"),IF((SELECT COUNT(contractid) FROM contracts_variations WHERE contractid=C.id AND contracts_variations.isactive="Y" ) >0,(IF((SELECT

SUM(IF(contracts_variations_prices.price_variation_type="positive",contracts_variations_prices.amount,- contracts_variations_prices.amount) *contracts_variations_prices.xrate)

FROM `contracts_variations_prices`

inner join contracts_variations on  contracts_variations.id=contracts_variations_prices.contract_variation_id

WHERE contracts_variations_prices.isactive="Y"
AND contracts_variations.isactive="Y"
AND contracts_variations.contractid=C.id)>0,((SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )  * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )+ (SELECT

SUM(IF(contracts_variations_prices.price_variation_type="positive",contracts_variations_prices.amount,- contracts_variations_prices.amount) *contracts_variations_prices.xrate)

FROM `contracts_variations_prices`

inner join contracts_variations on  contracts_variations.id=contracts_variations_prices.contract_variation_id

WHERE contracts_variations_prices.isactive="Y"
AND contracts_variations.isactive="Y"
AND contracts_variations.contractid=C.id) ),(SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )  * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))),(SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))),





        IF((SELECT COUNT(contractid) FROM contracts_variations WHERE contractid=C.id AND contracts_variations.isactive="Y" ) >0,

IF((SELECT

SUM(IF(contracts_variations_prices.price_variation_type="positive",contracts_variations_prices.amount,- contracts_variations_prices.amount) *contracts_variations_prices.xrate)

FROM `contracts_variations_prices`

inner join contracts_variations on  contracts_variations.id=contracts_variations_prices.contract_variation_id

WHERE contracts_variations_prices.isactive="Y"
AND contracts_variations.isactive="Y"
AND contracts_variations.contractid=C.id)>0,((SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )  * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )+ (SELECT

SUM(IF(contracts_variations_prices.price_variation_type="positive",contracts_variations_prices.amount,- contracts_variations_prices.amount) *contracts_variations_prices.xrate)

FROM `contracts_variations_prices`

inner join contracts_variations on  contracts_variations.id=contracts_variations_prices.contract_variation_id

WHERE contracts_variations_prices.isactive="Y"
AND contracts_variations.isactive="Y"
AND contracts_variations.contractid=C.id) ),(SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )  * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))  ,((SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )))

        )

        ) as normalized_actual_contract_price,


        (SELECT COUNT(contractid) FROM contracts_variations WHERE contractid=C.id AND contracts_variations.isactive="Y" ) AS variation_count,


        ( IF(C.final_contract_value > 0, IF(C.final_contract_value_exchange_rate>0,C.final_contract_value*C.final_contract_value_exchange_rate,C.final_contract_value),
            IF(CVP.amount >0,IF(CVP.price_variation_type LIKE "positive",
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) + IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) - IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount)),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))) - IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount)) AS difference_in_amount,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.framework,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        BI.estimated_amount,
        (SELECT TIMESTAMPDIFF(DAY,BI.dateofconfirmationoffunds,C.date_signed)) AS lead_time,
        BI.dateofconfirmationoffunds,
        BI.estimated_amount_exchange_rate,
        BI.estimated_amount_currency,
        PPE.procurement_plan_id,
        PP.financial_year,
        PP.summarized_plan,
        PDE.pdeid,
        PDE.pdename,
        PDE.abbreviation,
        PDE.status,
        PM.title,
        PM.id AS procurement_method_id,
        BI.id AS bidinvitation_id,
        BI.vote_no,
        BI.initiated_by,
        BI.date_initiated,
        BI.bid_openning_date,
        BI.cc_approval_date,
        BI.bid_receipt_address,
        BI.procurement_ref_no,
        BI.bid_security_amount,
        BI.bid_submission_deadline,
        BI.bid_evaluation_to,
        BI.dateofconfirmationoffunds,
        BI.contract_award_date,
        BI.bidvalidityperiod,
        BI.bidvalidity,
        BI.quantity,
        BI.display_of_beb_notice,
        BI.procurement_method_ifb,
        BI.procurement_id,
        BI.subject_details,
        (SELECT COUNT(receiptid) FROM receipts WHERE bid_id=BI.id AND receipts.isactive="Y") AS total_bids,
        (SELECT COUNT(receiptid) FROM receipts WHERE bid_id=BI.id AND receipts.isactive="Y" AND nationality ="Uganda") AS total_local_bids,
        (SELECT COUNT(receiptid) FROM receipts WHERE bid_id=BI.id AND receipts.isactive="Y" AND nationality !="Uganda") AS total_foreign_bids,
        lots.lot_title,
        lots.lot_number,
        lots.lot_details,
        joint_venture.jv,
        PT.title AS procurement_type_title,
        CO.call_off_order_no,
        CO.date_of_calloff_orders,
        CO.contract_value AS call_off_contract_value,
        CO.id AS call_off_order_id,
        CO.planned_completion_date AS call_off_planned_completion_date,
        CO.actual_completion_date AS call_off_order_actual_completion_date,
        CO.total_actual_payments AS call_off_total_actual_payments,
        CO.status AS call_off_order_status,
        CV.new_planned_date_of_completion,
        CVP.amount AS variation_amount,
        CVP.price_variation_type,
        CVP.xrate AS CVP_rate,
        (SELECT providernames from providers WHERE providers.providerid=RCPT.providerid) AS providernames

        ', false
        );

        $this->db->from('receipts AS RCPT');

        $this->db->join('contracts AS C', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->join('bidinvitations AS BI', 'RCPT.bid_id = BI.id');
        $this->db->join('beb_review_details', 'BI.id = beb_review_details.bidid','left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');

        $this->db->join('lots', 'lots.id = C.lotid', 'left');
        $this->db->join('joint_venture', 'joint_venture.jv = RCPT.joint_venture', 'left');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');
        $this->db->join('call_of_orders AS CO', 'CO.contractid = C.id', 'left');

        $this->db->join('contracts_variations AS CV', 'CV.contractid = C.id', 'left');
        $this->db->join('contracts_variations_prices AS CVP', 'CVP.contract_variation_id = CV.id', 'left');


        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('RCPT.receiptid >=', $from);
                $this->db->where('RCPT.receiptid <=', $to);
            }

        }



        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        if ($local) {
            $this->db->where('RCPT.nationality=','Uganda');
        }

        if ($foreign) {
            $this->db->where('RCPT.nationality !=','Uganda');
        }




        $this->db->order_by("RCPT.receiptid", "desc");

        $query = $this->db->get();
        $this->db->cache_off();


//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count(unique_multidim_array($query->result_array(),'id')));
//
//        print_array(unique_multidim_array($query->result_array(),'id'));
//        exit;


        $res = $query->result_array();


        return $res;

    }


    // =======================================================================//
    // ! ALL RESPONSIVE BIDS
    // =======================================================================//

    function get_all_responsive_bids($from, $to, $pde = '', $financial_year = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
       
        $this->db->select('
        BEB.bidid

        ', false
        );

        $this->db->from('bestevaluatedbidder AS BEB');
        $this->db->join('receipts AS RCPT', 'RCPT.receiptid = BEB.pid');
        $this->db->join('bidinvitations AS BI', 'BEB.bidid = BI.id');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('RCPT.isactive', 'Y');
        $this->db->where('BEB.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('BEB.dateadded >=', $from);
                $this->db->where('BEB.dateadded <=', $to);
            }

        }



        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        $this->db->order_by("BEB.dateadded", "desc");

        $query = $this->db->get();
        $this->db->cache_off();


        print_array($this->db->last_query());
        print_array($this->db->_error_message());
        print_array(count(unique_multidim_array($query->result_array(),'id')));

        print_array(unique_multidim_array($query->result_array(),'id'));
        exit;


        $res = $query->result_array();


        return $res;

    }



}