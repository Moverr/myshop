<?php
class Procurement_plan_status_m extends CI_Model
{
    public $_tablename='procurement_plan_statuses';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();


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

    //update by
    public function update_by($key,$key_value,$data)
    {
        $this->db->where($key, $key_value);
        $this->db->update($this->_tablename, $data);
        $this->db->cache_delete_all();

        //echo $this->db->last_query();

        return $this->db->affected_rows();

    }



}