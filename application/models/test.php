<?php// =======================================================================//
// ! AVERAGE NUMBER OF BIDS PER METHOD
// =======================================================================//
function get_bids_by_procurement_method($from, $to,$financial_year='', $pde = '')
{

    $this->db->cache_on();
    $this->db->distinct('BI.procurement_ref_no');

    # when filtering by month
    if($from&&$to){
        #if pde is selected
        if($pde){
            $this->db->select('

                                  pm.title,
  (SELECT COUNT(*) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b.dateadded >=\''.$from.'\' AND b.dateadded <=\''.$to.'\' AND p.pdeid=\''.$pde.'\' ) AS procurement_entries,


  (SELECT SUM(b.estimated_amount * IF(b.estimated_amount_exchange_rate>0,b.estimated_amount_exchange_rate,1)) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b.dateadded >=\''.$from.'\' AND b.dateadded <=\''.$to.'\' AND p.pdeid=\''.$pde.'\' ) AS procurement_entries_sum,

  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\' AND p.pdeid=\''.$pde.'\'  ) AS total_bids,


  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\' AND p.pdeid=\''.$pde.'\' ) AS local_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\' AND p.pdeid=\''.$pde.'\' ) AS responsive_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE  r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\'  AND p.pdeid=\''.$pde.'\' ) AS local_responsive_bids


', false
            );
        }else{
            $this->db->select('


                                  pm.title,
  (SELECT COUNT(*) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\'   AND b.dateadded >=\''.$from.'\' AND b.dateadded <=\''.$to.'\' ) AS procurement_entries,


  (SELECT SUM(b.estimated_amount * IF(b.estimated_amount_exchange_rate>0,b.estimated_amount_exchange_rate,1)) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b.dateadded >=\''.$from.'\' AND b.dateadded <=\''.$to.'\'  ) AS procurement_entries_sum,

  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\' ) AS total_bids,


  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\' ) AS local_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\' ) AS responsive_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE  r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND r.datereceived >=\''.$from.'\' AND r.datereceived <=\''.$to.'\'   ) AS local_responsive_bids


 ', false
            );
        }

    }else{
        # filter by financial year
        #if pde is selected
        if($pde){
            $this->db->select('

                                  pm.title,
  (SELECT COUNT(*) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'  AND p.pdeid=\''.$pde.'\' ) AS procurement_entries,


  (SELECT SUM(b.estimated_amount * IF(b.estimated_amount_exchange_rate>0,b.estimated_amount_exchange_rate,1)) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'  AND p.pdeid=\''.$pde.'\'   ) AS procurement_entries_sum,

  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\' AND p.pdeid=\''.$pde.'\') AS total_bids,

  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'  AND p.pdeid=\''.$pde.'\') AS local_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'  AND p.pdeid=\''.$pde.'\'  ) AS responsive_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE  r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\' AND p.pdeid=\''.$pde.'\'   ) AS local_responsive_bids


 ', false
            );
        }else{
            $this->db->select('
                  pm.title,
  (SELECT COUNT(*) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\'  AND  pp.financial_year=\''.$financial_year.'\' ) AS procurement_entries,


  (SELECT SUM(b.estimated_amount * IF(b.estimated_amount_exchange_rate>0,b.estimated_amount_exchange_rate,1)) FROM procurement_plan_entries ppe INNER JOIN procurement_types pt ON pt.id=ppe.procurement_type INNER JOIN bidinvitations b ON b.procurement_id=ppe.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'   ) AS procurement_entries_sum,

  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\') AS total_bids,

  (SELECT COUNT(*) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\' ) AS local_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'  ) AS responsive_bids,

  (SELECT SUM(b1.num_orb) FROM receipts r INNER JOIN bidinvitations b ON b.id=r.bid_id INNER JOIN procurement_plan_entries ppe ON ppe.id = b.procurement_id INNER JOIN bestevaluatedbidder b1 ON b1.bidid = b.id INNER JOIN procurement_plans pp ON pp.id=ppe.procurement_plan_id INNER JOIN pdes p ON p.pdeid=pp.pde_id WHERE  r.nationality=\'Uganda\' AND IF(b.procurement_method_ifb > 0, b.procurement_method_ifb , ppe.procurement_method) = pm.id AND r.isactive=\'Y\' AND ppe.isactive=\'Y\' AND b.isactive=\'Y\' AND b1.isactive=\'Y\' AND  pp.financial_year=\''.$financial_year.'\'  ) AS local_responsive_bids

        ', false
            );
        }
    }



    $this->db->from('procurement_methods AS pm');

    $query = $this->db->get();



    print_array($this->db->last_query());
    print_array($this->db->_error_message());
    print_array(count($query->result_array()));

    print_array($query->result_array());
    exit;

    return $query->result_array();


}



