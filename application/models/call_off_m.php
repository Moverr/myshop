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

class call_off_m extends   MY_Model
{
   
    //constructor
    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');
        $this->load->model('Query_reader', 'Query_reader', TRUE);
        
    }

    function get_sum_of_contract_values($contract_id)
    {
        
        $contract = !empty($contract_id) ? $contract_id : 0;
        $sql = " SELECT SUM(contract_value)  as calloff_contract_value  FROM `call_of_orders` WHERE  contractid = ".$contract_id." AND isactive= 'Y'  GROUP BY  contractid ";
        $result = $this->db->query($sql)->result_array();
        return $result;
        

    }


    function get_sum_of_totalpayments($contract_id)
    {
        
        
        $contract = !empty($contract_id) ? $contract_id : 0;
        $sql = " SELECT SUM(total_actual_payments)  as total_amount_paid  FROM `call_of_orders` WHERE  contractid = ".$contract_id." AND isactive= 'Y'  GROUP BY  contractid ";
        $result = $this->db->query($sql)->result_array();

         
        return $result;

    }
    

 


}