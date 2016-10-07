<?php
// ******************************* INFORMATION ***************************//

// ***********************************************************************//
//
// ** contracts model - Handles all database requests concerning contracts
// **
// ** @author   name <mcengkuru@newwavetech.co.ug>
// ** @date     4/jan/2016
// ** @access   private
//
// ***********************************************************************//

// ********************************** START ******************************//

class Contracts_m extends MY_Model
{
    //properties
    public $_tablename = 'contracts';
    public $_primary_key = 'id';

    //constructor
    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');
    }

// =======================================================================//
// ! GET CONTRACT BY LOT
// =======================================================================//

    function get_contract_amount_by_lot_id($id)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
        CP.amount
        ');

        $this->db->from('contract_prices AS CP');

        $this->db->join('contracts AS C', 'C.id = CP.contract_id');

        $this->db->where('C.lotid', $id);

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;
        $results = $query->result_array();

        return $results[0]['amount'];

    }
// =======================================================================//
// ! ALL AWARDED CONTRACTS
// =======================================================================//

    function get_contracts_all_awarded($from, $to, $pde = '', $financial_year = '', $completed = '', $count = '', $nomicros = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->select('
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
        C.total_actual_payments,

        IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount) as estimated_amount_at_ifb,
        IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount) as normalized_estimated_amount,

        C.final_contract_value_exchange_rate,
        C.total_actual_payments_exchange_rate,
        IF(C.total_actual_payments_exchange_rate>0,C.total_actual_payments_exchange_rate*C.total_actual_payments,C.total_actual_payments) as contract_value,
        IF(C.final_contract_value > 0, C.final_contract_value,
            IF(CVP.amount >0,IF(CVP.price_variation_type LIKE "positive",
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) + IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) - IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount)),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))) as normalized_actual_contract_price,

        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        BI.estimated_amount,
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

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
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
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        if ($completed) {
            $this->db->where('C.actual_completion_date IS NOT NULL', null);
        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        # by default exclude micros
        if ($nomicros == 'Y') {
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 10);

        }


        $this->db->order_by("C.date_signed", "desc");

        $query = $this->db->get();
        $this->db->cache_off();


        $res = $query->result_array();

        if (strtoupper($count) == 'Y') {
            $res = $query->num_rows();
        }


        return $res;

    }


// =======================================================================//
// ! ALL AWARDED CONTRACTS
// =======================================================================//

    function get_contracts_all_awarded_not_commenced($from, $to, $pde = '', $financial_year = '', $completed = '', $count = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,

         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
         IF(C.lotid > 0, (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contracts.lotid=C.lotid  ) , (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )) as amount,
         IF(C.lotid > 0, (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contracts.lotid=C.lotid  ) , (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )) as xrate,
        C.id ,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        BI.estimated_amount,
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
        lots.lot_title,
        lots.lot_number,
        lots.lot_details,
        joint_venture.jv,
        PT.title AS procurement_type_title,
        BEB.beb_expiry_date,

        ', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');

        $this->db->join('lots', 'lots.id = C.lotid', 'left');
        $this->db->join('joint_venture', 'joint_venture.jv = RCPT.joint_venture', 'left');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');
        $this->db->join('bestevaluatedbidder AS BEB', 'BEB.bidid = BI.id', 'left');


        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('C.commencement_date IS NULL', null);

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        if ($completed) {
            $this->db->where('C.actual_completion_date IS NOT NULL', null);
        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }


        $this->db->order_by("C.date_signed", "desc");

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
// ! ALL AWARDED CONTRACTS EXCEPT MICRO PROCUREMENTS
// =======================================================================//
    function get_contracts_awarded_except_micro_procurements($from, $to, $pde = '', $financial_year, $onlymicro = '', $completed = '', $count = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,

         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
         IF(C.lotid > 0, (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contracts.lotid=C.lotid  ) , (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )) as amount,
         IF(C.lotid > 0, (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contracts.lotid=C.lotid  ) , (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  )) as xrate,


        C.id ,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        BI.estimated_amount,
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
        BI.procurement_id,
        BI.bidvalidityperiod,
        BI.bidvalidity,
        BI.quantity,
        BI.procurement_method_ifb,
        BI.subject_details,
        BI.estimated_amount_exchange_rate,
        IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount) as normalized_estimated_amount,
        lots.lot_title,
        lots.lot_number,
        lots.lot_details,
        joint_venture.jv,
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
        (SELECT COUNT(*) FROM call_of_orders WHERE contractid=C.id AND isactive="Y") AS call_off_order_count,
        (SELECT providernames from providers WHERE providers.providerid=RCPT.providerid) AS providernames

        ', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->join('lots', 'lots.id = C.lotid', 'left');
        $this->db->join('joint_venture', 'joint_venture.jv = RCPT.joint_venture', 'left');
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
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        if ($completed) {
            $this->db->where('C.actual_completion_date IS NOT NULL', null);
        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        # by default exclude micros
        if (!$onlymicro) {
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 10);

        } else {
            # if set get only micros
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) =', 10);
        }


        $this->db->order_by("C.date_signed", "desc");

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
// ! ALL TERMINATED CONTRACTS
// =======================================================================//

    // get all awarded contracts
    function get_contracts_all_terminated($from, $to, $pde = '', $financial_year = '', $completed = '', $count = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.receiptid');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        BI.id,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,


         IF(C.lotid > 0, (SELECT lot_number FROM lots WHERE id = C.lotid) , "") as lot_title,


         IF(C.lotid > 0, (SELECT amount FROM contract_prices WHERE contract_id = C.id) , CP.amount) as lot_amount,


        C.id ,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        CP.amount,
        CP.xrate,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        PPE.estimated_amount,
        PPE.procurement_plan_id,
        PT.title AS procurement_type_title,
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
        BI.procurement_method_ifb,
        (SELECT providernames from providers WHERE providerid=RCPT.providerid) AS providernames
        ', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');

        // a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');


        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'N');
        $this->db->where('PPE.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        if ($completed) {
            $this->db->where('C.actual_completion_date IS NOT NULL', null);
        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }


        $this->db->order_by("C.date_signed", "desc");

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
// ! ALL COMPLETED CONTRACTS EXCEPT MICRO PROCUREMENTS
// =======================================================================//
    function get_contracts_all_completed($from, $to, $pde = '', $financial_year)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        BI.id,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,


         IF(C.lotid > 0, (SELECT lot_number FROM lots WHERE id = C.lotid) , "") as lot_title,


         IF(C.lotid > 0, (SELECT amount FROM contract_prices WHERE contract_id = C.id) , CP.amount) as lot_amount,

        C.id ,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        CP.amount,
        CP.xrate,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        PPE.estimated_amount,
        PPE.procurement_plan_id,
        PT.title AS procurement_type_title,
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
        BI.procurement_method_ifb,
        (SELECT providernames from providers WHERE providerid=RCPT.providerid) AS providernames
        ', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');

        // a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->where('C.actual_completion_date IS NOT NULL', null);


        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');


        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }


        $this->db->order_by("C.date_signed", "desc");

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
// ! ALL AWARDED VARIED CONTRACTS
// =======================================================================//

    // get all varied awarded contracts
    function get_all_varied_awarded_contracts($from, $to, $pde = '', $financial_year, $completed)
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        BI.id,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,


         IF(C.lotid > 0, (SELECT lot_number FROM lots WHERE id = C.lotid) , "") as lot_title,


         IF(C.lotid > 0, (SELECT amount FROM contract_prices WHERE contract_id = C.id) , CP.amount) as lot_amount,

        C.id ,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        CP.amount,
        CP.xrate,
        CVP.amount AS variation_amount,
        CVP.xrate AS variation_amount_rate,
        CVP.price_variation_type,
        CV.initial_completion_date,
        CV.new_planned_date_of_completion,
        CV.dateadded AS variation_date,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        PPE.estimated_amount,
        PPE.procurement_plan_id,
        PT.title AS procurement_type_title,
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
        BI.procurement_method_ifb,
        BI.subject_details,
        (SELECT providernames from providers WHERE providerid=RCPT.providerid) AS providernames
        ', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');

        // a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'RCPT.bid_id = C.bidinvitation_id');
        $this->db->join('contracts_variations AS CV', 'CV.contractid = C.id');
        $this->db->join('contracts_variations_prices AS CVP', 'CVP.contract_variation_id = CV.id');


        $this->db->where('RCPT.beb', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('PP.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        if ($completed) {
            $this->db->where('C.actual_completion_date IS NOT NULL', null);
        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }


        $this->db->order_by("C.date_signed", "desc");

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
// ! CONTRACTS DUE FOR COMPLETION
// =======================================================================//

    function get_contracts_due_for_completion($from, $to, $pde = '', $financial_year = '', $count = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');

        $this->db->select('
 BEB.date_oaoterbt_cc,
 IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
 IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,

  BEB.nationality AS beb_nationality,
  BEB.contractprice AS beb_contractprice,
  BEB.exchange_rate,
  BEB.currency,
  BEB.ispublished,
  BEB.lotid,
  BEB.date_of_display,
  BEB.final_evaluation_report_approval_date,
  BEB.evaluation_commencement_date,
  BEB.type_oem,
  BEB.date_oce_r,
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
BI.display_of_beb_notice,
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
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type,
PT.id AS procurement_type_id ,
PT.title AS procurement_type_title ,
(SELECT providernames from providers WHERE providerid=receipts.providerid) AS providernames,
C.date_signed,
C.completion_date,
CP.amount,
CP.xrate,
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
        (SELECT COUNT(*) FROM call_of_orders WHERE contractid=C.id AND isactive="Y") AS call_off_order_count,
        IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate, BI.estimated_amount) as normalized_estimated_amount,

        C.final_contract_value_exchange_rate,
        C.total_actual_payments_exchange_rate,
        IF(C.total_actual_payments_exchange_rate>0,C.total_actual_payments_exchange_rate*C.total_actual_payments,C.total_actual_payments) as contract_value,
        IF(C.final_contract_value > 0, C.final_contract_value,
            IF(CVP.amount >0,IF(CVP.price_variation_type LIKE "positive",
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) + IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) - IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount)),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))) as normalized_actual_contract_price,
  ', false
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
        $this->db->join('contracts AS C', 'C.bidinvitation_id = BI.id', 'left');
        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id', 'left');
        $this->db->where('receipts.beb', 'Y');
        $this->db->where('receipts.isactive', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('C.actual_completion_date IS NULL', null);
        $this->db->join('call_of_orders AS CO', 'CO.contractid = C.id', 'left');
        $this->db->join('contracts_variations AS CV', 'CV.contractid = C.id', 'left');
        $this->db->join('contracts_variations_prices AS CVP', 'CVP.contract_variation_id = CV.id', 'left');


        # if ranges are provided
        if ($from && $to) {
            $this->db->where('BI.dateadded >=', $from);
            $this->db->where('BI.dateadded <=', $to);
        }

        # if financial years are set
        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('BI.dateadded >=', $from);
                $this->db->where('BI.dateadded <=', $to);
            }
        }


        # if pde is set
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }


        $this->db->order_by("C.actual_completion_date", "desc");

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


    // get all contracts awarded within original value
    function get_contracts_awarded_within_original_value($from, $to, $pde = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        BI.id,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
        BI.id,
        C.id,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        CP.amount,
        CP.xrate,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        PPE.estimated_amount,
        PPE.procurement_plan_id,
        PT.title AS procurement_type_title,
        PP.financial_year,
        PP.summarized_plan,
        PDE.pdeid,
        PDE.pdename,
        PDE.abbreviation,
        PDE.status,
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
        BI.procurement_method_ifb,
        PVD.providernames', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'C.procurement_ref_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->join('providers AS PVD', 'PVD.providerid = RCPT.providerid');


        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        # MUST BE LESS THAN ORIGINAL PRICE
        $this->db->where('C.final_contract_value <', 'PP.estimated_amount');
        $this->db->where('C.date_signed >=', $from);
        $this->db->where('C.date_signed <=', $to);
        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        $this->db->order_by("C.date_signed", "desc");

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();


    }


// =======================================================================//
// ! CALL OFF ORDERS SECTION
// =======================================================================//
# get call off orders by period
    function get_call_off_orders_by_period($from, $to, $status = 'awarded', $pde = '')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->distinct();
        $this->db->select('
CFO.call_off_order_no,
CFO.subject_of_procurement,
CFO.date_of_calloff_orders,
CFO.user_department,
CFO.contract_value,
CFO.planned_completion_date,
CFO.actual_completion_date,
CFO.total_actual_payments,
CFO.contractid,
CFO.receiptid,
CFO.dateadded,
CFO.author,
CFO.isactive,
CFO.status,
PVD.providernames,
PDE.pdename
'
        );
        $this->db->from('call_of_orders AS CFO');

        $this->db->join('contracts AS C', 'C.id = CFO.contractid');
        $this->db->join('receipts AS RCPT', 'RCPT.bid_id = C.bidinvitation_id');
        $this->db->join('providers AS PVD', 'PVD.providerid = RCPT.providerid');
        $this->db->join('procurement_plan_entries AS PPE', 'PPE.id = C.bidinvitation_id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');


        $this->db->where('CFO.isactive', 'Y');
        $this->db->where('CFO.date_of_calloff_orders >=', $from);
        $this->db->where('CFO.date_of_calloff_orders <=', $to);

        $this->db->where('CFO.status', $status);

        $this->db->where('RCPT.beb', 'Y');
        $this->db->order_by("CFO.dateadded", "desc");

        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        $query = $this->db->get();


//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();


    }


    public function get_contract_info($contracts_id, $param)
    {
        //$this->db->cache_on();
        $query = $this->db->select()->from($this->_tablename)->where($this->_primary_key, $contracts_id)->get();

        //print_array($this->db->last_query());

        $info_array = $query->result_array();

        // print_array($info_array);

        //if there is a result
        if (count($info_array)) {

            foreach ($info_array as $row) {
                switch ($param) {
                    case 'emergency_procurement':
                        $result = $row['emergency_procurement'];
                        break;
                    case 'direct_procurement':
                        $result = $row['direct_procurement'];
                        break;
                    case 'procurement_ref_no':
                        $result = $row['procurement_ref_no'];
                        break;
                    case 'admin_review':
                        $result = $row['admin_review'];
                        break;
                    case 'date_of_sg_approval':
                        $result = $row['date_of_sg_approval'];
                        break;
                    case 'final_award_notice_date':
                        $result = $row['final_award_notice_date'];
                        break;
                    case 'commencement_date':
                        $result = $row['commencement_date'];
                        break;
                    case 'contract_amount':
                        $result = $row['contract_amount'];
                        break;
                    case 'amount_currency':
                        $result = $row['amount_currency'];
                        break;
                    case 'exchange_rate':
                        $result = $row['exchange_rate'];
                        break;
                    case 'author_id':
                        $result = $row['author'];
                        break;
                    case 'author':
                        $result = get_user_info_by_id($row['author'], 'fullname');
                        break;
                    case 'isactive':
                        $result = $row['isactive'];
                        break;
                    case 'dateawarded':
                        $result = $row['dateawarded'];
                        break;
                    case 'procurement_ref_id':
                        $result = $row['procurement_ref_id'];
                        break;
                    default:
                        //no parameter is passed display all user info
                        $result = $info_array;
                }
            }

            return $result;

        } else {
            return NULL;
        }

    }


    function get_timeliness_of_contract_completion($from, $to, $pde = '')
    {
        /*
         * percentage of contracts completed whose planned date of completion  is >=  to the actual completion date
         */
        $searchstring = "";
        if (!empty($pde)) {
            $searchstring = "AND pdes.pdeid = " . $pde . "";
        }


        if ($pde) {
            $results = $this->custom_query("
        SELECT
contracts.id,
contracts.procurement_ref_id,
contracts.isactive,
contracts.total_actual_payments,
contracts.final_contract_value,
contracts.date_signed,
contracts.completion_date,
contracts.commencement_date,
contracts.advance_payment_date,
contracts.completion_author,
contracts.actual_completion_date,
contracts.performance_rating,
contracts.contract_manager,
contracts.dateawarded,
contracts.dateadded,
bidinvitations.id AS bidinvitation_id,
bidinvitations.cc_approval_date,
providers.providernames,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.funder_name,
procurement_plans.pde_id,
pdes.pdename,
pdes.abbreviation,
pdetypes.pdetype,
pdes.pdeid
FROM
contracts
INNER JOIN bidinvitations ON contracts.procurement_ref_id = bidinvitations.procurement_id
INNER JOIN receipts ON bidinvitations.id = receipts.bid_id
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plans.id = procurement_plan_entries.procurement_plan_id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN procurement_methods ON procurement_methods.id = procurement_plan_entries.procurement_method
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid
WHERE


INNER JOIN receipts ON receipts.bid_id = bidinvitations.id
INNER JOIN providers ON providers.providerid = receipts.providerid
WHERE
receipts.beb = 'Y' AND
bidinvitations.isactive = 'Y' AND
contracts.isactive = 'Y' AND
contracts.bidinvitation_id = receipts.bid_id AND



contracts.completion_date >= contracts.actual_completion_date AND
 contracts.commencement_date >= '" . $from . "'   AND
 contracts.commencement_date  <=  '" . $to . "'  " . $searchstring . " AND
pdes.pdeid = '" . $pde . "'
ORDER BY
contracts.id DESC
");

        } else {
            $results = $this->custom_query("
         SELECT
contracts.id,
contracts.procurement_ref_id,
contracts.isactive,
contracts.total_actual_payments,
contracts.final_contract_value,
contracts.date_signed,
contracts.completion_date,
contracts.commencement_date,
contracts.advance_payment_date,
contracts.completion_author,
contracts.actual_completion_date,
contracts.performance_rating,
contracts.contract_manager,
contracts.dateawarded,
contracts.dateadded,
bidinvitations.id AS bidinvitation_id,
bidinvitations.cc_approval_date,
providers.providernames,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.funder_name,
procurement_plans.pde_id,
pdes.pdename,
pdes.abbreviation,
pdetypes.pdetype,
pdes.pdeid
FROM
contracts
INNER JOIN bidinvitations ON contracts.procurement_ref_id = bidinvitations.procurement_id
INNER JOIN receipts ON bidinvitations.id = receipts.bid_id
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plans.id = procurement_plan_entries.procurement_plan_id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN procurement_methods ON procurement_methods.id = procurement_plan_entries.procurement_method
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid


INNER JOIN receipts ON receipts.bid_id = bidinvitations.id
INNER JOIN providers ON providers.providerid = receipts.providerid
WHERE
receipts.beb = 'Y' AND
bidinvitations.isactive = 'Y' AND
contracts.isactive = 'Y' AND
contracts.bidinvitation_id = receipts.bid_id AND



 contracts.completion_date >= contracts.actual_completion_date AND
  contracts.commencement_date >= '" . $from . "'   AND
  contracts.commencement_date  <=  '" . $to . "'
 ORDER BY
 contracts.id DESC

 ");
        }


        return $results;
    }


    function get_contracts_due($from, $to, $pde = '', $lots = '')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
        contracts.id,
        contracts.advance_payment,
        contracts.advance_payment_date,
        contracts.completion_author,
        contracts.commencement_date,
        contracts.completion_date,
        contracts.days_duration,
        contracts.date_signed,
        contracts.final_contract_value,
        contracts.actual_completion_date,
        contracts.performance_rating,
        contracts.author,
        contracts.procurement_ref_id,
        contracts.date_signed,
        contracts.lotid,
        contracts.total_actual_payments,
        contract_prices.amount,
        contract_prices.xrate,
        procurement_plan_entries.subject_of_procurement,
        procurement_plan_entries.procurement_type,
        procurement_plan_entries.procurement_method,
        procurement_plan_entries.funding_source,
        procurement_plan_entries.pde_department,
        procurement_plan_entries.procurement_ref_no,
        procurement_plan_entries.funder_name,
        procurement_plan_entries.estimated_amount,
        procurement_plan_entries.procurement_plan_id,
        procurement_plans.financial_year,
        procurement_plans.summarized_plan,
        pdes.pdeid,
        pdes.pdename,
        pdes.abbreviation,
        pdes.status,
        bidinvitations.id AS bidinvitation_id,
        bidinvitations.vote_no,
        bidinvitations.initiated_by,
        bidinvitations.date_initiated,
        bidinvitations.bid_openning_date,
        bidinvitations.cc_approval_date,
        bidinvitations.bid_receipt_address,
        bidinvitations.procurement_ref_no,
        bidinvitations.bid_security_amount,
        bidinvitations.bid_submission_deadline,
        bidinvitations.bid_evaluation_to,
        bidinvitations.dateofconfirmationoffunds,
        bidinvitations.contract_award_date,
        bidinvitations.bidvalidityperiod,
        bidinvitations.bidvalidity,
        bidinvitations.quantity,
        bidinvitations.procurement_method_ifb,
        providers.providernames'
        );
        $this->db->from('contracts');

        $this->db->join('contract_prices', 'contracts.id = contract_prices.contract_id');
        $this->db->join('procurement_plan_entries', 'contracts.procurement_ref_id = procurement_plan_entries.id');
        $this->db->join('procurement_plans', 'procurement_plan_entries.procurement_plan_id = procurement_plans.id');
        $this->db->join('pdes', 'procurement_plans.pde_id = pdes.pdeid');
        $this->db->join('bidinvitations', 'contracts.bidinvitation_id = bidinvitations.id');
        $this->db->join('receipts', 'receipts.bid_id = bidinvitations.id');
        $this->db->join('providers', 'providers.providerid = receipts.providerid');

        $this->db->where('receipts.beb', 'Y');
        $this->db->where('bidinvitations.isactive', 'Y');
        $this->db->where('contracts.isactive', 'Y');
        $this->db->where('procurement_plan_entries.isactive', 'Y');
        $this->db->where('procurement_plans.isactive', 'Y');
        $this->db->where('contracts.actual_completion_date IS NULL', null);
        $this->db->where('contracts.completion_date >=', $from);
        $this->db->where('contracts.completion_date <=', $to);
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }
        if ($lots) {
            $this->db->where('contracts.lotid >', 0);
        } else {
            $this->db->where('contracts.lotid', 0);
        }
        $this->db->order_by("contracts.date_signed", "desc");

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//        exit;

        return $query->result_array();

    }


    //get signed contracts
    function get_signed_contracts_by_financial_year($from, $to, $pde = '')
    {

        if ($pde) {
            $results = $this->custom_query("
        SELECT
contracts.id,
contracts.procurement_ref_id,
contracts.isactive,
contracts.total_actual_payments,
contracts.contract_amount,
contracts.final_contract_value,
contracts.date_signed,
contracts.completion_date,
contracts.commencement_date,
contracts.advance_payment_date,
contracts.completion_author,
contracts.actual_completion_date,
contracts.performance_rating,
contracts.contract_manager,
contracts.dateawarded,
contracts.dateadded,
bidinvitations.id AS bidinvitation_id,
bidinvitations.cc_approval_date,
providers.providernames,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.funder_name,
procurement_plans.pde_id,
pdes.pdename,
pdes.abbreviation,
pdetypes.pdetype,
pdes.pdeid
FROM
contracts
INNER JOIN bidinvitations ON contracts.procurement_ref_id = bidinvitations.procurement_id
INNER JOIN receipts ON bidinvitations.id = receipts.bid_id
INNER JOIN providers ON receipts.providerid = providers.providerid
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plans.id = procurement_plan_entries.procurement_plan_id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN procurement_methods ON procurement_methods.id = procurement_plan_entries.procurement_method
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid


INNER JOIN receipts ON receipts.bid_id = bidinvitations.id
INNER JOIN providers ON providers.providerid = receipts.providerid
WHERE
receipts.beb = 'Y' AND
bidinvitations.isactive = 'Y' AND
contracts.isactive = 'Y' AND
contracts.bidinvitation_id = receipts.bid_id AND



contracts.commencement_date >= '" . $from . "' AND
contracts.commencement_date <= '" . $to . "' AND
pdes.pdeid = '" . $pde . "'
ORDER BY
contracts.id DESC
");
        } else {
            $results = $this->custom_query("
        SELECT
contracts.id,
contracts.procurement_ref_id,
contracts.isactive,
contracts.total_actual_payments,
contracts.contract_amount,
contracts.final_contract_value,
contracts.date_signed,
contracts.completion_date,
contracts.commencement_date,
contracts.advance_payment_date,
contracts.completion_author,
contracts.actual_completion_date,
contracts.performance_rating,
contracts.contract_manager,
contracts.dateawarded,
contracts.dateadded,
bidinvitations.id AS bidinvitation_id,
bidinvitations.cc_approval_date,
providers.providernames,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.funder_name,
procurement_plans.pde_id,
pdes.pdename,
pdes.abbreviation,
pdetypes.pdetype,
pdes.pdeid
FROM
contracts
INNER JOIN bidinvitations ON contracts.procurement_ref_id = bidinvitations.procurement_id

INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plans.id = procurement_plan_entries.procurement_plan_id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN procurement_methods ON procurement_methods.id = procurement_plan_entries.procurement_method
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid


INNER JOIN receipts ON receipts.bid_id = bidinvitations.id
INNER JOIN providers ON providers.providerid = receipts.providerid
WHERE
receipts.beb = 'Y' AND
bidinvitations.isactive = 'Y' AND
contracts.isactive = 'Y' AND
contracts.bidinvitation_id = receipts.bid_id AND



contracts.date_signed >= '" . $from . "' AND
contracts.date_signed <= '" . $to . "'
ORDER BY
contracts.id DESC

");


        }


        return $results;
    }


    //get completed and awarded contracts [SEALED]
    function get_contracts_published($from, $to, $pde = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
BI.id,
IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
BI.id,
C.id,
C.advance_payment,
C.advance_payment_date,
C.completion_author,
C.commencement_date,
C.days_duration,
C.date_signed,
C.final_contract_value,
C.completion_date,
C.actual_completion_date,
C.performance_rating,
C.author,
C.procurement_ref_id,
C.date_signed,
C.lotid,
C.total_actual_payments,
CP.amount,
CP.xrate,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.pde_department,
PPE.procurement_ref_no,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PT.title AS procurement_type_title,
PP.financial_year,
PP.summarized_plan,
PDE.pdeid,
PDE.pdename,
PDE.abbreviation,
PDE.status,
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
BI.procurement_method_ifb,
PVD.providernames', false
        );
        $this->db->from('contracts AS C');
        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');

// a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'C.procurement_ref_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');

// every procurement plan entry must have a method
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');

        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'RCPT.bid_id = BI.id');
        $this->db->join('providers AS PVD', 'PVD.providerid = RCPT.providerid');

        $this->db->where('RCPT.beb', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('PP.isactive', 'Y');


        $this->db->where('C.date_signed IS NOT NULL', null);
        $this->db->where('C.date_signed >=', $from);
        $this->db->where('C.date_signed <=', $to);


        $this->db->where('C.date_signed >=', $from);
        $this->db->where('C.date_signed <=', $to);
        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        $this->db->order_by("C.date_signed", "desc");

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();


    }


    // =======================================================================//
    // ! PAGINATED PUBLISHED CONTRACTS
    // =======================================================================//
    function get_paginated_published_contracts($limit, $start, $term)
    {
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->select('
IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
BI.id,
IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
BI.id,
C.id,
C.advance_payment,
C.advance_payment_date,
C.completion_author,
C.commencement_date,
C.days_duration,
C.date_signed,
C.final_contract_value,
C.completion_date,
C.actual_completion_date,
C.performance_rating,
C.author,
C.procurement_ref_id,
C.date_signed,
C.lotid,
C.total_actual_payments,
CP.amount,
CP.xrate,
PPE.subject_of_procurement,
PPE.procurement_type,
PPE.funding_source,
PPE.pde_department,
PPE.procurement_ref_no,
PPE.funder_name,
PPE.estimated_amount,
PPE.procurement_plan_id,
PT.title AS procurement_type_title,
PP.financial_year,
PP.summarized_plan,
PDE.pdeid,
PDE.pdename,
PDE.abbreviation,
PDE.status,
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
BI.procurement_method_ifb,
PVD.providernames', false
        );
        $this->db->from('contracts AS C');
        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');

// a bid invitation may or may not have a procurement method
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');

        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'C.procurement_ref_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');

// every procurement plan entry must have a method
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');

        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'RCPT.bid_id = BI.id');
        $this->db->join('providers AS PVD', 'PVD.providerid = RCPT.providerid');

        $this->db->where('RCPT.beb', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('PP.isactive', 'Y');


        $this->db->where('C.date_signed IS NOT NULL', null);


        $this->db->order_by("C.date_signed", "desc");


        if ($term) {
            $this->db->where('PPE.subject_of_procurement LIKE', '%' . $term . '%');
            $this->db->or_where('PDE.pdename LIKE', '%' . $term . '%');
            $this->db->or_where('PP.financial_year LIKE', '%' . $term . '%');
            $this->db->or_where('BI.procurement_ref_no LIKE', '%' . $term . '%');

        }

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



    //[SEALED]


    //bid invitations
    function get_bid_invitations($from, $to, $pde = '')
    {

        if ($pde) {
            $results = $this->custom_query("
        SELECT
bidinvitations.vote_no,
bidinvitations.procurement_id,
bidinvitations.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.procurement_plan_id,
pdes.pdename,
contracts.lotid

pdes.type
FROM
bidinvitations
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.id = pdes.pdeid
WHERE
bidinvitations.isactive = 'Y' AND

pdes.pdeid = '" . $pde . "' AND

bidinvitations.date_initiated >= '" . $from . "' AND
bidinvitations.date_initiated <= '" . $to . "'
ORDER BY
bidinvitations.id DESC
");
        } else {
            $results = $this->custom_query("
        SELECT
        bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.procurement_id,
bidinvitations.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.procurement_plan_id,
pdes.pdename,
contracts.lotid

pdes.type
FROM
bidinvitations
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.id = pdes.pdeid
WHERE
bidinvitations.isactive = 'Y' AND

bidinvitations.date_initiated >= '" . $from . "' AND
bidinvitations.date_initiated <= '" . $to . "'
ORDER BY
bidinvitations.id DESC

");
        }

        //get_contracts_due($this->db->last_query());


        return $results;
    }

    //bid invitations
    function get_responses_by_bid($bid, $from, $to, $pde = '')
    {
        $month = date('m', now());
        $year = date('y', now());

        if (!$from) {
            $from = database_ready_format(date("m/d/y", mktime(0, 0, 0, $month, 01, $year)));
        }
        if (!$to) {
            $to = database_ready_format(date("m/d/y", mktime(0, 0, 0, $month, 30, $year)));
        }

        if ($pde) {
            $results = $this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.nationality,
bidinvitations.vote_no,
bidinvitations.procurement_id,
bidinvitations.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.procurement_plan_id,
pdes.pdename,

pdes.type,
contracts.lotid
FROM
receipts
INNER JOIN bidinvitations ON bidinvitations.id = receipts.bid_id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.id = pdes.pdeid
WHERE
receipts.isactive = 'Y' AND
receipts.bid_id = '" . $bid . "' AND

pdes.pdeid = '" . $pde . "' AND

bidinvitations.dateadded >= '" . $from . "' AND
bidinvitations.dateadded <= '" . $to . "'
ORDER BY
bidinvitations.id DESC
");
        } else {
            $results = $this->custom_query("
        SELECT
receipts.receiptid,
receipts.bid_id,
receipts.providerid,
receipts.nationality,
bidinvitations.vote_no,
bidinvitations.procurement_id,
bidinvitations.id,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.pde_department,
procurement_plan_entries.funding_source,
procurement_plan_entries.funder_name,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.procurement_plan_id,
pdes.pdename,

pdes.type,
contracts.lotid
FROM
receipts
INNER JOIN bidinvitations ON bidinvitations.id = receipts.bid_id
INNER JOIN procurement_plan_entries ON bidinvitations.procurement_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.id = pdes.pdeid
WHERE
receipts.isactive = 'Y' AND
receipts.bid_id = $bid AND
bidinvitations.dateadded >= '" . $from . "' AND
bidinvitations.dateadded <= '" . $to . "'
ORDER BY
bidinvitations.id DESC

");
        }

        //print_array($this->db->last_query());


        return $results;
    }

    //contracts awarded by procurement method
    function get_contracts_by_contracts_by_procurement_method($method, $from, $to, $pde = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
        contracts.id,
        contracts.advance_payment,
        contracts.advance_payment_date,
        contracts.completion_author,
        contracts.commencement_date,
        contracts.days_duration,
        contracts.date_signed,
        contracts.final_contract_value,
        contracts.actual_completion_date,
        contracts.performance_rating,
        contracts.author,
        contracts.procurement_ref_id,
        contracts.date_signed,
        contracts.lotid,
        contracts.total_actual_payments,
        contract_prices.amount,
        contract_prices.xrate,
        procurement_plan_entries.subject_of_procurement,
        procurement_plan_entries.procurement_type,
        procurement_plan_entries.procurement_method,
        procurement_plan_entries.funding_source,
        procurement_plan_entries.pde_department,
        procurement_plan_entries.procurement_ref_no,
        procurement_plan_entries.funder_name,
        procurement_plan_entries.estimated_amount,
        procurement_plan_entries.procurement_plan_id,
        procurement_plans.financial_year,
        procurement_plans.summarized_plan,
        pdes.pdeid,
        pdes.pdename,
        pdes.abbreviation,
        pdes.status,
        bidinvitations.id AS bidinvitation_id,
        bidinvitations.vote_no,
        bidinvitations.initiated_by,
        bidinvitations.date_initiated,
        bidinvitations.bid_openning_date,
        bidinvitations.cc_approval_date,
        bidinvitations.bid_receipt_address,
        bidinvitations.procurement_ref_no,
        bidinvitations.bid_security_amount,
        bidinvitations.bid_submission_deadline,
        bidinvitations.bid_evaluation_to,
        bidinvitations.dateofconfirmationoffunds,
        bidinvitations.contract_award_date,
        bidinvitations.bidvalidityperiod,
        bidinvitations.bidvalidity,
        bidinvitations.quantity,
        bidinvitations.procurement_method_ifb,
        providers.providernames'
        );
        $this->db->from('contracts');

        $this->db->join('contract_prices', 'contracts.id = contract_prices.contract_id');
        $this->db->join('procurement_plan_entries', 'contracts.procurement_ref_id = procurement_plan_entries.id');
        $this->db->join('procurement_plans', 'procurement_plan_entries.procurement_plan_id = procurement_plans.id');
        $this->db->join('pdes', 'procurement_plans.pde_id = pdes.pdeid');
        $this->db->join('bidinvitations', 'contracts.bidinvitation_id = bidinvitations.id');
        $this->db->join('receipts', 'receipts.bid_id = bidinvitations.id');
        $this->db->join('providers', 'providers.providerid = receipts.providerid');
        $this->db->join('procurement_methods as PM1', 'PM1.id = procurement_plan_entries.procurement_method');
        $this->db->join('procurement_methods as PM2', 'PM2.id = bidinvitations.procurement_method_ifb', 'left');
        $this->db->where('PM2.id !=', $method);
        $this->db->where('receipts.beb', 'Y');
        $this->db->where('bidinvitations.isactive', 'Y');
        $this->db->where('contracts.isactive', 'Y');
        $this->db->where('procurement_plan_entries.isactive', 'Y');
        $this->db->where('procurement_plans.isactive', 'Y');
        $this->db->where('contracts.date_signed IS NOT NULL', null);
        $this->db->where('contracts.date_signed >=', $from);
        $this->db->where('contracts.date_signed <=', $to);
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }

        $this->db->order_by("contracts.date_signed", "desc");

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();

    }

    //completed contracts [SEALED]
    function get_completed_contracts($from, $to, $pde = '', $lots = '')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
         IF(BI.procurement_method_ifb > 0, PM.id , PPE.procurement_method) as procurement_method,
        BI.id,
         IF(BI.procurement_method_ifb > 0, PM.title , PPM.title) as procurement_method_title,
        BI.id,
        C.id,
        C.advance_payment,
        C.advance_payment_date,
        C.completion_author,
        C.commencement_date,
        C.days_duration,
        C.date_signed,
        C.final_contract_value,
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,
        CP.amount,
        CP.xrate,
        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        PPE.estimated_amount,
        PPE.procurement_plan_id,
        PT.title AS procurement_type_title,
        PP.financial_year,
        PP.summarized_plan,
        PDE.pdeid,
        PDE.pdename,
        PDE.abbreviation,
        PDE.status,
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
        BI.procurement_method_ifb,
        PVD.providernames', false
        );
        $this->db->from('contracts AS C');
        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');


        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');


        $this->db->join('contract_prices AS CP', 'C.id = CP.contract_id');
        $this->db->join('procurement_plan_entries AS PPE', 'C.procurement_ref_id = PPE.id');
        $this->db->join('procurement_types AS PT', 'PPE.procurement_type = PT.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');


        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');
        $this->db->join('receipts AS RCPT', 'RCPT.bid_id = BI.id');
        $this->db->join('providers AS PVD', 'PVD.providerid = RCPT.providerid');

        $this->db->where('RCPT.beb', 'Y');
        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('C.actual_completion_date IS NOT NULL', null);

        // completed contracts except micro procurements
        // completed contracts except micro procurements
        $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 10);


        $this->db->where('C.actual_completion_date >=', $from);
        $this->db->where('C.actual_completion_date <=', $to);
        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }
        if ($lots) {
            $this->db->where('C.lotid >', 0);
        } else {
            $this->db->where('C.lotid', 0);
        }
        $this->db->order_by("C.actual_completion_date", "desc");

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();

    }


    function get_contract_by_ref_number($ref_number)
    {

        $results = $this->custom_query("SELECT
contracts.id,
contracts.advance_payment,
contracts.advance_payment_date,
contracts.completion_author,
contracts.commencement_date,
contracts.completion_date,
contracts.days_duration,
contracts.date_signed,
contracts.final_contract_value,
contracts.actual_completion_date,
contracts.performance_rating,
contracts.author,
procurement_plan_entries.subject_of_procurement,
procurement_plan_entries.procurement_type,
procurement_plan_entries.procurement_method,
procurement_plan_entries.funding_source,
procurement_plan_entries.pde_department,
procurement_plan_entries.procurement_ref_no,
procurement_plan_entries.funder_name,
procurement_plan_entries.estimated_amount,
procurement_plan_entries.procurement_plan_id,
procurement_plans.financial_year,
procurement_plans.summarized_plan,
pdes.pdeid,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
contracts.total_actual_payments,
bidinvitations.id AS bidinvitation_id,
bidinvitations.vote_no,
bidinvitations.initiated_by,
bidinvitations.date_initiated,
bidinvitations.bid_openning_date,
bidinvitations.cc_approval_date,
bidinvitations.bid_receipt_address,
bidinvitations.procurement_ref_no,
bidinvitations.bid_security_amount,
bidinvitations.bid_submission_deadline,
bidinvitations.bid_evaluation_to,
bidinvitations.dateofconfirmationoffunds,
bidinvitations.contract_award_date,
bidinvitations.bidvalidityperiod,
bidinvitations.bidvalidity,
bidinvitations.quantity,
contracts.procurement_ref_id,
contract_prices.amount,
contract_prices.xrate,
contracts.lotid
FROM
contracts
INNER JOIN contract_prices ON contracts.id = contract_prices.contract_id
INNER JOIN procurement_plan_entries ON contracts.procurement_ref_id = procurement_plan_entries.id
INNER JOIN procurement_plans ON procurement_plan_entries.procurement_plan_id = procurement_plans.id
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN bidinvitations ON contracts.bidinvitation_id = bidinvitations.id


INNER JOIN receipts ON receipts.bid_id = bidinvitations.id
INNER JOIN providers ON providers.providerid = receipts.providerid
WHERE
receipts.beb = 'Y' AND
bidinvitations.isactive = 'Y' AND
contracts.isactive = 'Y' AND
contracts.bidinvitation_id = receipts.bid_id AND



procurement_plan_entries.isactive = 'Y' AND
procurement_plans.isactive = 'Y' AND
bidinvitations.procurement_ref_no = '$ref_number'
ORDER BY
contracts.id DESC");

        //print_array($this->db->last_query());

        //print_array($this->db->last_query());


        //return $results;
    }


    # Get call off orders by contract
    function get_call_off_orders_by_contract($contract_id, $status = 'awarded')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
CFO.id,
CFO.call_off_order_no,
CFO.subject_of_procurement,
CFO.date_of_calloff_orders,
CFO.user_department,
CFO.contract_value,
CFO.planned_completion_date,
CFO.actual_completion_date,
CFO.total_actual_payments,
CFO.contractid,
CFO.receiptid,
CFO.dateadded,
CFO.author,
CFO.isactive,
CFO.status,
(SELECT providernames FROM providers WHERE providerid=RCPT.providerid AND RCPT.beb="Y" AND RCPT.isactive="Y") as providernames
'
        );
        $this->db->from('call_of_orders AS CFO');

        $this->db->join('contracts AS C', 'C.id = CFO.contractid');
        $this->db->join('receipts AS RCPT', 'RCPT.receiptid = CFO.receiptid');

        $this->db->where('CFO.isactive', 'Y');
        $this->db->where('CFO.contractid', $contract_id);
        $this->db->where('CFO.status', $status);
        $this->db->where('RCPT.beb', 'Y');
        $this->db->order_by("CFO.dateadded", "desc");

        $query = $this->db->get();


//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();


    }

    # get call off orders by receipt
    # Get call off orders by contract
    function get_call_off_orders_by_receiptid($receipt_id, $status = 'awarded')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('
CFO.id,
CFO.call_off_order_no,
CFO.subject_of_procurement,
CFO.date_of_calloff_orders,
CFO.user_department,
CFO.contract_value,
CFO.planned_completion_date,
CFO.actual_completion_date,
CFO.total_actual_payments,
CFO.contractid,
CFO.receiptid,
CFO.dateadded,
CFO.author,
CFO.isactive,
CFO.status
'
        );
        $this->db->from('call_of_orders AS CFO');

        $this->db->join('contracts AS C', 'C.id = CFO.contractid');
        $this->db->join('receipts AS RCPT', 'RCPT.receiptid = CFO.receiptid');

        $this->db->where('CFO.isactive', 'Y');
        $this->db->where('CFO.receiptid', $receipt_id);
        $this->db->where('CFO.status', $status);
        $this->db->where('RCPT.beb', 'Y');
        $this->db->order_by("CFO.dateadded", "desc");

        $query = $this->db->get();


//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();


    }

    function contracts_with_call_off_orders($contract_id, $status)
    {
        $results = $this->custom_query("SELECT *
FROM contracts
INNER JOIN call_of_orders ON contracts.id = call_of_orders.contractid
WHERE
contractid='" . $contract_id . "' AND
contractid='" . $status . "'
");

        return $results;
    }

    # Get call off orders by contract
    function get_call_off_orders_by_status($status = 'awarded', $from, $to)
    {

        $results = $this->custom_query("SELECT *
FROM

call_of_orders

WHERE

status = '" . $status . "' AND
date_of_calloff_orders >= '" . $from . "' AND
date_of_calloff_orders <= '" . $to . "'
ORDER BY
date_of_calloff_orders DESC");

        return $results;

    }


    // =======================================================================//
// ! ALL AWARDED CONTRACTS EXCLUDING CALL OFFS
// =======================================================================//
    function get_awarded_contracts_exclude_call_offs($from, $to, $pde = '', $financial_year = '', $completed = '', $count = '', $nomicros = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->select('
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
        C.completion_date,
        C.actual_completion_date,
        C.performance_rating,
        C.author,
        C.procurement_ref_id,
        C.date_signed,
        C.lotid,
        C.total_actual_payments,

        IF(BI.estimated_amount_exchange_rate > 0, BI.estimated_amount * BI.estimated_amount_exchange_rate , BI.estimated_amount) as estimated_amount_at_ifb,

        IF(C.final_contract_value > 0, IF(CVP.amount >0, IF(CVP.price_variation_type LIKE "positive",C.final_contract_value + IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount),
                    C.final_contract_value - IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount)),C.final_contract_value ),

            IF(CVP.amount >0,IF(CVP.price_variation_type LIKE "positive", (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) + IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount),
                    (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) - IF(CVP.xrate>0,CVP.amount*CVP.xrate,CVP.amount)),
                (SELECT amount FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ) * (SELECT xrate FROM contract_prices INNER JOIN contracts ON contracts.id=contract_prices.contract_id WHERE contract_prices.contract_id=C.id LIMIT 1  ))) as normalized_actual_contract_price,

        PPE.subject_of_procurement,
        PPE.procurement_type,
        PPE.funding_source,
        PPE.pde_department,
        PPE.procurement_ref_no,
        PPE.funder_name,
        BI.estimated_amount,
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
        lots.lot_title,
        lots.lot_number,
        lots.lot_details,
        joint_venture.jv,
        PT.title AS procurement_type_title,
        CV.new_planned_date_of_completion,
        CVP.amount AS variation_amount,
        CVP.price_variation_type,
        CVP.xrate AS CVP_rate,
        (SELECT providernames from providers WHERE providers.providerid=RCPT.providerid) AS providernames

        ', false
        );

        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');

        $this->db->join('lots', 'lots.id = C.lotid', 'left');
        $this->db->join('joint_venture', 'joint_venture.jv = RCPT.joint_venture', 'left');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');

        $this->db->join('contracts_variations AS CV', 'CV.contractid = C.id', 'left');
        $this->db->join('contracts_variations_prices AS CVP', 'CVP.contract_variation_id = CV.id', 'left');


        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        if ($completed) {
            $this->db->where('C.actual_completion_date IS NOT NULL', null);
        }


        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        # by default exclude micros
        if ($nomicros == 'Y') {
            $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 10);

        }


        $this->db->order_by("C.date_signed", "desc");

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
// ! ALL AWARDED CONTRACTS EXCLUDING CALL OFFS
// =======================================================================//
    function aggregate_completed_contracts_actual_price_exclude_call_offs($from, $to, $pde = '', $financial_year = '', $nomicros = '', $late_contracts = 'N', $select_type = 'sum')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.id');
        $this->db->from('contracts AS C');

        $this->db->join('bidinvitations AS BI', 'C.bidinvitation_id = BI.id');
        $this->db->join('receipts AS RCPT', 'C.receiptid = RCPT.receiptid', 'left');
        $this->db->join('procurement_plan_entries AS PPE', 'BI.procurement_id = PPE.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_methods AS PM', 'PM.id = BI.procurement_method_ifb', 'left');
        $this->db->join('pdes AS PDE', 'PP.pde_id = PDE.pdeid');

        $this->db->join('joint_venture', 'joint_venture.jv = RCPT.joint_venture', 'left');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');
        $this->db->join('lots', 'lots.id = C.lotid', 'left');


        switch ($select_type):

            case 'sum':
                $this->db->select('
        		SUM(CAST(C.final_contract_value AS UNSIGNED) * C.final_contract_value_exchange_rate) as sum_final_contract_value', false);

                $this->db->join('contracts_variations AS CV', 'CV.contractid = C.id', 'left');
                $this->db->join('contracts_variations_prices AS CVP', 'CVP.contract_variation_id = CV.id', 'left');

                break;

            case 'count':
                $this->db->select('COUNT(*) as numOfContracts', false);

                break;

        endswitch;

        $this->db->where('BI.isactive', 'Y');
        $this->db->where('C.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year', $financial_year);

        } else {
            if ($from && $to) {
                $this->db->where('C.date_signed >=', $from);
                $this->db->where('C.date_signed <=', $to);
            }

        }

        $this->db->where('C.actual_completion_date IS NOT NULL', null);

        if ($late_contracts == 'Y') $this->db->where('C.actual_completion_date > C.completion_date', null);

        if ($pde) $this->db->where('PDE.pdeid', $pde);

        # by default exclude micros
        if ($nomicros == 'Y') $this->db->where('IF(BI.procurement_method_ifb > 0, BI.procurement_method_ifb , PPE.procurement_method) !=', 10);

        $this->db->order_by("C.date_signed", "desc");

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
}