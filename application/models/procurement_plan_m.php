<?php
class Procurement_plan_m extends MY_Model
{
    public $_tablename='procurement_plans';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');
        $this->load->model('pde_m');

    }

    //validate procurement plan form
    public $validate_plan=array
    (
        array
        (
            'field'   => 'start_year',
            'label'   => 'Start year',
            'rules'   => 'required'
        ),
        array
        (
            'field'   => 'end_year',
            'label'   => 'End year',
            'rules'   => 'required'
        )
    );

    //validate procurement plan form
    public $validate_edit_plan=array
    (

        array
        (
            'field'   => 'title',
            'label'   => 'title',
            'rules'   => 'required'
        ),
    );



// =======================================================================//
// ! ENTITIES WITH NO PROCUREMENT PLANS BY FINANCIAL YEAR
// =======================================================================//
    function get_pdes_with_no_procurement_plans($financial_year, $pde = '')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
PDE.pdeid,
PDE.pdename,
PDE.abbreviation,
PDE.`status`,
PDE.create_date,
PDE.created_by,
PDE.category,
PDE.type,
PDE.`code`,
PDE.address,
PDE.tel,
PDE.fax,
PDE.email,
PDE.isactive,
PDETPS.pdetype',false
        );

        $this->db->from('pdes AS PDE');
        $this->db->join('pdetypes AS PDETPS', 'PDETPS.pdetypeid = PDE.type');
        $this->db->where_not_in('PDE.pdeid', "SELECT
pdes.pdeid
FROM
procurement_plans
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid
WHERE
procurement_plans.financial_year = '.$financial_year.'
");
        $this->db->where('PDE.isactive', 'Y');
        $this->db->where('PDETPS.isactive', 'Y');

        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        $this->db->order_by("PDE.pdeid", "desc");

        $query = $this->db->get();

//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        exit;

        return $query->result_array();

    }

// =======================================================================//
// ! ENTITIES WITH PROCUREMENT PLANS BY FINANCIAL YEAR
// =======================================================================//
    function get_pdes_with_procurement_plans($financial_year, $pde = '')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->select('
PDE.pdeid,
PDE.pdename,
PDE.abbreviation,
PDE.`status`,
PDE.create_date,
PDE.created_by,
PDE.category,
PDE.type,
PDE.`code`,
PDE.address,
PDE.tel,
PDE.fax,
PDE.email,
PDE.isactive,
PDETPS.pdetype',false
        );

        $this->db->from('pdes AS PDE');
        $this->db->join('pdetypes AS PDETPS', 'PDETPS.pdetypeid = PDE.type');
        $this->db->where_in('PDE.pdeid', '(SELECT
pdes.pdeid
FROM
procurement_plans
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid
WHERE
procurement_plans.financial_year = '.$financial_year.'
)');
        $this->db->where('PDE.isactive', 'Y');
        $this->db->where('PDETPS.isactive', 'Y');

        if ($pde) {
            $this->db->where('PDE.pdeid', $pde);
        }

        $this->db->order_by("PDE.pdename", "asc");

        $query = $this->db->get();

        print_array($this->db->last_query());
        print_array($this->db->_error_message());
        print_array($query->result_array());
        exit;

        return $query->result_array();

    }


// =======================================================================//
// ! FINANCIAL YEARS BY PDE
// =======================================================================//
    function get_financial_years_by_pde($pde = '')
    {

        $this->db->query('SET SQL_BIG_SELECTS=1');



        $this->db->distinct('PP.fy');
        $this->db->select('
        PP.financial_year AS fy,
        PP.financial_year AS label'
        );

        $this->db->from('procurement_plans AS PP');


        if ($pde) {
            $this->db->where('PP.pde_id', $pde);
        }

        $this->db->order_by('PP.financial_year','desc');
        $query = $this->db->get();


//        print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//
//        exit();

        return $query->result_array();

    }



// =======================================================================//
// ! PDES WITH NO PROCUREMENT PLAN
// =======================================================================//

    // get all awarded contracts
    function get_pdes_with_no_procurement_plan($financial_year = '',$count = '')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.receiptid');
        $this->db->select('
        PDE.pdeid,
        PDE.pdename,
        PDE.abbreviation,
        pdetypes.pdetype AS type,
        PDE.category,
        PDE.status,'
        );

        $this->db->from('pdes AS PDE');
        $this->db->join('pdetypes','pdetypes.pdetypeid=PDE.type');

        if ($financial_year) {
            $this->db->where('PDE.pdeid NOT IN (SELECT `pde_id` FROM `procurement_plans` WHERE financial_year='.$financial_year.')', NULL, FALSE);
        }
        $this->db->order_by("PDE.pdename", "asc");

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
// ! PDES WITH NO PROCUREMENT PLAN
// =======================================================================//

    function get_pdes_with_procurement_plan($financial_year = '',$pde = '',$nonComplient='')
    {
        $this->db->query('SET SQL_BIG_SELECTS=1');

        $this->db->cache_on();
        $this->db->distinct('C.receiptid');
        $this->db->select('
        PDE.pdeid,
        PDE.pdename,
        PDE.abbreviation,
        pdetypes.pdetype AS type,
        PDE.category,
        PDE.status,
  (SELECT
  COUNT(*)
FROM procurement_plan_entries ppe
  INNER JOIN procurement_plans pp ON pp.id = ppe.procurement_plan_id
  WHERE ppe.isactive = "Y"
  AND pp.financial_year ="' .$financial_year. ' "
  AND pp.pde_id=PDE.pdeid) AS procurement_entries,
  (SELECT SUM(ppe.estimated_amount * ppe.exchange_rate)
FROM procurement_plan_entries ppe
  INNER JOIN procurement_plans pp ON pp.id = ppe.procurement_plan_id
  WHERE ppe.isactive = "Y"
  AND pp.financial_year ="' .$financial_year. '"
  AND pp.pde_id=PDE.pdeid) AS sum_of_entries
        '
        );

        $this->db->from('pdes AS PDE');
        $this->db->join('pdetypes','pdetypes.pdetypeid=PDE.type');

        if ($financial_year) {
            if($nonComplient=='Y'){
                $this->db->where('PDE.pdeid NOT IN (SELECT `pde_id` FROM `procurement_plans` WHERE financial_year="'.$financial_year.'")', NULL, FALSE);

            }else{
                $this->db->where('PDE.pdeid IN (SELECT `pde_id` FROM `procurement_plans` WHERE financial_year="'.$financial_year.'")', NULL, FALSE);

            }

        }


        if($pde){
            $this->db->where('PDE.pdeid',$pde);
        }
        $this->db->order_by("PDE.pdename", "asc");

        $query = $this->db->get();
        $this->db->cache_off();




        $res = $query->result_array();

//                print_array($this->db->last_query());
//        print_array($this->db->_error_message());
//        print_array(count($query->result_array()));
//        print_array($query->result_array());
//        exit();



        return $res;

    }



    // =======================================================================//
    // ! PAGINATED PROCUREMENT PLANS
    // =======================================================================//
    function get_paginated_procurement_plans($limit, $start, $term)
    {

        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('

PP.financial_year,
PP.title,
pdes.pdeid AS pde_id,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type

',false
        );
        $this->db->from('procurement_plans AS PP');

        $this->db->join('procurement_plan_entries AS PPE', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->where('PP.isactive', 'Y');
        $this->db->where('PP.title LIKE', '%'.$term.'%');

        if ($term) {
            $this->db->or_where('pdes.pdename LIKE', '%'.$term.'%');
            $this->db->or_where('PP.financial_year LIKE', '%'.$term.'%');
        }

        $this->db->limit($limit, $start);



        $this->db->order_by("PP.financial_year", "desc");

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
    // ! PAGINATED PLANS BY FINANCIAL YEAR
    // =======================================================================//
    function get_plans_by_financial_year($financial_year='',$pde=''){
        $this->db->cache_on();
        $this->db->distinct('BI.procurement_ref_no');
        $this->db->query('SET SQL_BIG_SELECTS=1');
        $this->db->select('

PP.financial_year,
PP.title,
PP.dateadded,
pdes.pdeid AS pde_id,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdes.type

',false
        );
        $this->db->from('procurement_plans AS PP');

        $this->db->join('procurement_plan_entries AS PPE', 'PPE.procurement_plan_id = PP.id');
        $this->db->join('pdes', 'PP.pde_id = pdes.pdeid');
        $this->db->where('PP.isactive', 'Y');

        if ($financial_year) {
            $this->db->where('PP.financial_year LIKE', '%'.$financial_year.'%');
        }
        if ($pde) {
            $this->db->where('pdes.pdeid',$pde);
        }
        $this->db->limit(1);

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







    //visible and trashed
    public function get_all_procurement_plans()//visible is either y or n
    {

        $query=$this->db->select()->from($this->_tablename)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }





    //get by passed parameters
    public function get_where($where)
    {
        $this->db->cache_on();
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key,'DESC')->get();


        return $query->result_array();
    }




    public function get_paginated($num=20,$start)
    {
        //echo $this->$_primary_key.'foo';
        //build query
        $this->db->cache_on();
        $q=$this->db->select()->from($this->_tablename)->limit($num,$start)->where('trash','n')->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();

        //return result
        return $q->result_array();

    }

    //get paginated
    public function get_paginated_by_criteria($num=20,$start,$criteria)
    {
        //build query
        $this->db->cache_on();
        $q=$this->db->select()->from($this->_tablename)->limit($num,$start)->where($criteria)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();

        //return result
        return $q->result_array();

    }
    //create
    public function create($data)
    {
        $this->db->insert($this->_tablename,$data);
        //echo $this->db->last_query();
        $this->db->cache_delete_all();
        return $this->db->insert_id();

    }

    public function get_procurement_plan_info($plan_id,$param)
    {
        $this->db->cache_on();
        $query=$this->db->select()->from($this->_tablename) ->where($this->_primary_key,$plan_id)->get();

        # print_array($this->db->last_query());
        # print_r($this->_tablename); exit();

        $info_array=$query->result_array();



        //if there is a result
        if(count($info_array))
        {

            foreach($info_array as $row)
            {
                switch ($param)
                {
                    case 'financial_year':
                        $result=$row['financial_year'];
                        break;
                    case 'title':
                        $result=$row['title'];
                        break;
                    case 'pde_id':
                        $result=$row['pde_id'];
                        break;
                    case 'pde':
                        $result=get_pde_info_by_id($row['pde_id'],'title');
                        break;
                    case 'description':
                        $result=$row['description'];
                        break;
                    case 'author_id':
                        $result=$row['author'];
                        break;
                    case 'author':
                        $result=get_user_info_by_id($row['author'],'fullname');
                        break;
                    case 'isactive':
                        $result=$row['active'];
                        break;
                    case 'dateadded':
                        $result=$row['dateadded'];
                        break;
                    default:
                        //no parameter is passed display all user info
                        $result=$info_array;
                }
            }

            return $result;

        }
        else
        {
            return NULL;
        }

    }


    //get by id
    public function get_by_id($id='')
    {
        //if its empty get all visible
        if($id=='')
        {
            return NULL;
        }
        else
        {
            $data=array
            (
                'id'        =>$id
            );
            $this->db->cache_on();
            $query=$this->db->select()->from($this->_tablename)->where($data)->order_by($this->_primary_key,'DESC')->get();

        }
        //echo $this->db->last_query();

        return $query->result_array();
    }

    public function update($id,$data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->_tablename, $data);

        //echo $this->db->last_query();

        return $this->db->affected_rows();
        $this->db->cache_delete_all();
    }

    //update by
    public function update_by($key,$key_value,$data)
    {
        $this->db->where($key, $key_value);
        $this->db->update($this->_tablename, $data);

        //echo $this->db->last_query();
        $this->db->cache_delete_all();

        return $this->db->affected_rows();

    }


    //pdes with procurement plans
    public function compliant_pdes($where=''){
        if(is_array($where)){
            $this->db->select('*');
            $this->db->from('pdes');
            $this->db->join('procurement_plans', 'pdes.pdeid = '.$this->_tablename.'.pde_id');
            $this->db->where($where);
            //$this->db->join('table3', 'table1.id = table3.id');


        }else{
            $this->db->select('*');
            $this->db->from('pdes');
            $this->db->join('procurement_plans', 'pdes.pdeid = '.$this->_tablename.'.pde_id');
            //$this->db->join('table3', 'table1.id = table3.id');


        }
        $query = $this->db->get();


        return $query->result_array();

    }


    function get_procurement_plans_by_financial_year($financial_year, $pde = '')
    {

        if ($pde) {
            $results = $this->custom_query("SELECT
procurement_plans.id,
procurement_plans.financial_year,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdetypes.pdetype,
pdes.pdeid
FROM
procurement_plans
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid
WHERE
procurement_plans.isactive = 'Y' AND
pdes.isactive = 'Y' AND
pdetypes.isactive = 'Y' AND
procurement_plans.financial_year = '$financial_year' AND
pdes.pdeid = $pde
ORDER BY
procurement_plans.id DESC");
        } else {
            $results = $this->custom_query("SELECT
procurement_plans.id,
procurement_plans.financial_year,
pdes.pdename,
pdes.abbreviation,
pdes.`status`,
pdes.category,
pdetypes.pdetype,
pdes.pdeid
FROM
procurement_plans
INNER JOIN pdes ON procurement_plans.pde_id = pdes.pdeid
INNER JOIN pdetypes ON pdes.type = pdetypes.pdetypeid
WHERE
procurement_plans.isactive = 'Y' AND
pdes.isactive = 'Y' AND
pdetypes.isactive = 'Y' AND
procurement_plans.financial_year = '$financial_year'
ORDER BY
procurement_plans.id DESC");
        }

        //print_array($this->db->last_query());
        //print_array($this->db->last_query());


        return $results;
    }


    function get_all_procurement_plans_by_year($financial_year){
        $results=$this->custom_query("SELECT
  procurement_plans.id,
  procurement_plans.pde_id,
  procurement_plans.financial_year,
  procurement_plans.title,
  procurement_plans.summarized_plan,
  procurement_plans.dateadded,
  procurement_plans.dateupdated,
  procurement_plans.author,
  procurement_plans.description,
  procurement_plans.public,
  pdes.pdename,
  pdes.abbreviation
FROM procurement_plans
  INNER JOIN pdes ON pdes.pdeid = procurement_plans.pde_id
WHERE procurement_plans.isactive =  'y'
      AND procurement_plans.financial_year =  '$financial_year' AND
    pdes.isactive = 'y'
ORDER BY procurement_plans.id DESC
");
        return $results;
    }


    function get_all_procurement_plans_paginated($offset,$financial_year){
        $limit=  NUM_OF_ROWS_PER_PAGE;
        //if no offset
        if(!$offset){
            $offset=0;
        }
        $results=$this->custom_query("SELECT
  procurement_plans.id,
  procurement_plans.pde_id,
  procurement_plans.financial_year,
  procurement_plans.title,
  procurement_plans.summarized_plan,
  procurement_plans.dateadded,
  procurement_plans.dateupdated,
  procurement_plans.author,
  procurement_plans.description,
  procurement_plans.public,
  pdes.pdename,
  pdes.abbreviation
FROM procurement_plans
  INNER JOIN pdes ON pdes.pdeid = procurement_plans.pde_id
WHERE procurement_plans.isactive =  'y'
      AND procurement_plans.financial_year =  '$financial_year' AND
    pdes.isactive = 'y'

LIMIT ".$offset.", ".$limit."
");
        return $results;
    }





}