<?php
class Procurement_plan_phase_m extends CI_Model
{
    public $_tablename='procurement_plan_phases';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();


    }



    public function get_procurement_plan_phase_info($id='',$param='')
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





    //create
    public function create($data)
    {
        $this->db->insert($this->_tablename,$data);
        //echo $this->db->last_query();
        $this->db->cache_delete_all();
        return $this->db->insert_id();

    }


    //get by passed parameters
    public function get_where($where)
    {
        $this->db->cache_on();
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key,'DESC')->get();


        return $query->result_array();
    }



}