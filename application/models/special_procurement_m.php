<?php
class Special_procurement_m extends MY_Model
{
    public $_tablename='special_procurements';
    public $_primary_key='id';

    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');

    }

    // =======================================================================//
    // ! ACTIVE SPECIAL PROCUREMENTS
    // =======================================================================//

    function get_active_special_procurements($from, $to, $pde = '',$financial_year='',$count='')
    {

        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
        SP.id AS SP_id,
  SP.procurement_id AS SP_procurement_id,
  SP.procurement_reference_no AS SP_procurement_reference_no,
  SP.custom_reference_no ,
  SP.subject_details ,
  SP.financial_year AS SP_financial_year,
  SP.procurement_method AS SP_procurement_method,
  SP.justification AS SP_justification,
  SP.budget_code ,
  SP.initiated_by AS SP_initiated_by,
  SP.confirmation_date AS SP_confirmation,
  SP.estimated_amount AS SP_estimated_amount,
  SP.estimated_amount_currency AS SP_estimated_amount_currency,
  SP.estimated_payment_rate AS SP_estimated_payment_rate,
  IF(SP.estimated_payment_rate > 0,SP.estimated_payment_rate*SP.estimated_amount,SP.estimated_amount) as normalized_estimated_amount,
  SP.provider_name AS SP_provider_name,
  SP.country_registration ,
  SP.contract_award_date AS SP_contract_award_date,
  SP.contract_value AS SP_contract_value,
  SP.contract_value_currency AS SP_contract_value_currency,
  SP.contract_payment_rate AS SP_contract_payment_rate,
  SP.total_payments AS SP_total_payments,
  IF(SP.contract_payment_rate  > 0,SP.contract_payment_rate*SP.contract_value,SP.contract_value) as normalized_actual_amount,
  SP.total_payment_currency AS SP_total_payment_currency,
  SP.total_payment_rate AS SP_total_payment_rate,
  (SELECT title FROM procurement_methods WHERE id=SP.procurement_method) AS SP_procurement_method_title,
  PP.financial_year AS SP_financial_year,
  pdes.pdename,
  PPE.subject_of_procurement,
  PPM.title AS procurement_method_title,
  PT.title AS procurement_type_title
',false);
        #=====================
        # START THE JOINS
        #=====================

        $this->db->from('procurement_plan_entries AS PPE');
        $this->db->join('special_procurements AS SP', 'PPE.id = SP.procurement_id');
        $this->db->join('procurement_methods AS PM', 'PPE.procurement_method = PM.id');
        $this->db->join('procurement_plans AS PP', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->join('procurement_methods AS PPM', 'PPM.id = PPE.procurement_method');
        $this->db->join('procurement_types AS PT', 'PT.id = PPE.procurement_type', 'left');


        #=====================
        # START THE WHERES
        #=====================


        $this->db->where('SP.isactive', 'Y');
        $this->db->where('PPE.isactive', 'Y');

        $this->db->order_by('PPE.dateadded','desc');
        if ($pde) {
            $this->db->where('pdes.pdeid', $pde);
        }
        # if ranges are provided
        if($from&&$to){
            $this->db->where('PPE.dateadded >=',$from);
            $this->db->where('PPE.dateadded <=',$to);
        }

        if($financial_year){
            $this->db->where('PP.financial_year', $financial_year);

        }else{
            if($from&&$to){
                $this->db->where('PPE.dateadded >=', $from);
                $this->db->where('PPE.dateadded <=', $to);
            }
        }

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


    //visible and trashed
    public function get_all()//visible is either y or n
    {
        $this->db->cache_on();
        $query=$this->db->select()->from($this->_tablename)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }

    //get procurement method details
    function get_procurement_method_info($id='',$param='')
    {
        if($id=='')
        {
            return NULL;
        }
        else
        {
            $this->db->cache_on();
            $query=$this->db->select()->from($this->_tablename) ->where($this->_primary_key,$id)->get();
        }

        if($query->result_array())
        {
            foreach($query->result_array() as $row)
            {
                switch($param)
                {
                    case 'title':
                        $result=$row['title'];
                        break;
                    case 'description':
                        $result=$row['description'];
                        break;
                    case 'author_id':
                        $result=$row['author'];
                        break;
                    case 'author':
                        $result=get_user_info($row['author'],'fullname');
                        break;
                    case 'isactive':
                        $result=$row['active'];
                        break;
                    case 'dateadded':
                        $result=$row['dateadded'];
                        break;
                    default:
                        $result=$query->result_array();
                }
            }

            return $result;
        }
        else
        {
            return NULL;
        }




    }

    //automatically update slug field
    public function update_slugs()
    {
        foreach($this->get_all()as $row)
        {
            if($row['slug']=='')
            {
                $data['slug']=seo_url($row['title']);
                $this->update($row['id'],$data);
            }
        }
    }

    //get by passed parameters
    public function get_where($where)
    {
        $this->db->cache_on();
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key,'DESC')->get();

        return $query->result_array();
    }






}