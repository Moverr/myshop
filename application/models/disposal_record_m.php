<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Disposal_record_m extends MY_Model
{
    public $_tablename = 'disposal_record';
    public $_primary_key = 'id';

    function __construct()
    {

        parent::__construct();
    }

    public function get_disposal_record_info($id='', $param='')
    {

        //if NO ID
        if($id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where('id',$id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {
                        case 'plan_id':
                            $result=$row['disposal_plan'];
                            break;
                        case 'plan':
                            $result=get_disposal_plan_info_by_id($row['disposal_plan'],'title');
                            break;
                        case 'serial_no':
                            $result = $row['disposal_serial_no'];
                            break;


                        case 'pde_id':
                            $result=$row['pde_id'];
                            break;

                        case 'pde':
                            $result=get_pde_info_by_id($row['pde_id'],'title');
                            break;

                        case 'title':
                            $result=$row['subject_of_disposal'];
                            break;


                        case 'isactive':
                            $result=$row['isactive'];
                            break;

                        case 'location':
                            $result=$row['asset_location'];
                            break;

                        case 'amount':
                            $result=$row['amount'];
                            break;

                        case 'currency':
                            $result=$row['currence'];
                            break;

                        case 'approval_date':
                            $result=$row['dateofaoapproval'];
                            break;



                        default:
                            $result=$query->result_array();
                    }

                }

                return $result;
            }

        }
    }

    public function get_disposal_record_info_by_disposal_plan($id = '', $param = '')
    {

        //if NO ID
        if ($id == '') {
            return NULL;
        } else {
            //get user info
            $query = $this->db->select()->from($this->_tablename)->where('disposal_plan', $id)->get();

            if ($query->result_array()) {
                foreach ($query->result_array() as $row) {
                    //filter results
                    switch ($param) {
                        case 'id':
                            $result = $row['id'];
                            break;
                        case 'plan':
                            $result = get_disposal_plan_info_by_id($row['disposal_plan'], 'title');
                            break;
                        case 'serial_no':
                            $result = $row['disposal_serial_no'];
                            break;

                        case 'serial':
                            $result = $row['disposal_serial_no'];
                            break;


                        case 'pde_id':
                            $result = $row['pde'];
                            break;

                        case 'pde':
                            $result = get_pde_info_by_id($row['pde'], 'title');
                            break;

                        case 'title':
                            $result = $row['subject_of_disposal'];
                            break;


                        case 'isactive':
                            $result = $row['isactive'];
                            break;

                        case 'location':
                            $result = $row['asset_location'];
                            break;

                        case 'amount':
                            $result = $row['amount'];
                            break;

                        case 'currency':
                            $result = $row['currence'];
                            break;

                        case 'approval_date':
                            $result = $row['dateofaoapproval'];
                            break;


                        default:
                            $result = $query->result_array();
                    }

                }

                return $result;
            }

        }
    }


    function get_disposal_plans_by_month($from,$to,$pde=''){

        if($pde){
            $results= $this->custom_query("SELECT
disposal_record.id,
disposal_record.disposal_plan,
disposal_record.disposal_serial_no,
disposal_record.pde,
disposal_record.subject_of_disposal,
disposal_record.asset_location,
disposal_record.amount,
disposal_record.currence,
disposal_record.strategic_asset,
disposal_record.date_of_approval,
disposal_record.method_of_disposal,
disposal_record.isactive,
disposal_record.author,
disposal_record.dateadded,
disposal_record.dateofaoapproval,
disposal_record.quantity,
disposal_plans.financial_year,
pdes.pdename,
disposal_method.method
FROM
disposal_record
INNER JOIN pdes ON disposal_record.pde = pdes.pdeid
INNER JOIN disposal_plans ON disposal_record.disposal_plan = disposal_plans.id
INNER JOIN disposal_method ON disposal_record.method_of_disposal = disposal_method.id
WHERE
disposal_record.isactive = 'Y' AND
disposal_record.dateofaoapproval >= '" . $from . "' AND
disposal_record.dateofaoapproval <= '" . $to . "' AND
pdes.pdeid = " . $pde . " AND
pdes.isactive = 'Y' AND
disposal_plans.isactive = 'Y'
ORDER BY
disposal_record.id DESC
");
        }
        else{
            $results= $this->custom_query("SELECT
disposal_record.id,
disposal_record.disposal_plan,
disposal_record.disposal_serial_no,
disposal_record.pde,
disposal_record.subject_of_disposal,
disposal_record.asset_location,
disposal_record.amount,
disposal_record.currence,
disposal_record.strategic_asset,
disposal_record.date_of_approval,
disposal_record.method_of_disposal,
disposal_record.isactive,
disposal_record.author,
disposal_record.dateadded,
disposal_record.dateofaoapproval,
disposal_record.quantity,
disposal_plans.financial_year,
pdes.pdename,
disposal_method.method
FROM
disposal_record
INNER JOIN pdes ON disposal_record.pde = pdes.pdeid
INNER JOIN disposal_plans ON disposal_record.disposal_plan = disposal_plans.id
INNER JOIN disposal_method ON disposal_record.method_of_disposal = disposal_method.id
WHERE
disposal_record.isactive = 'Y' AND
disposal_record.dateofaoapproval >= '" . $from . "' AND
disposal_record.dateofaoapproval <= '" . $to . "' AND

pdes.isactive = 'Y' AND
disposal_plans.isactive = 'Y'
ORDER BY
disposal_record.id DESC
");
        }

        //print_array($this->db->last_query());
        //print_array($this->db->last_query());


        return $results;
    }









}