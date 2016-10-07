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

class Variation_m extends   MY_Model
{
   
    //constructor
    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');
        $this->load->model('Query_reader', 'Query_reader', TRUE);
        
    }

    function get_variation($contract_id)
    {
        
        $contract = !empty($contract_id) ? $contract_id : 0;

        $data = array('SEARCHSTRING' => ' 1 = 1  AND  CV.contractid= '.$contract.' AND CV.isactive = "Y" ');
        $query = $this->Query_reader->get_query_by_code('fetch_contract_variations_grouped',$data);
           
        $result = $this->db->query($query)->result_array();
        return $result;
        

    }

 


}