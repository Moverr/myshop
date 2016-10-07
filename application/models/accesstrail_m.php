<?php
class Accesstrail_m extends CI_Model
{
    public $_tablename='accesstrail';
    public $_primary_key='accessid';
    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');

    }

    //visible and trashed
    public function get_all()//visible is either y or n
    {

        $query=$this->db->select()->from($this->_tablename)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }



    public function log_action($action,$message,$context)
    {


        $data= array(
            'action'=>$action,
            'message'=>$message,
            'name'=>get_user_info_by_id($this->session->userdata('userid'),'fullname'),
            'userid'=>$this->session->userdata('userid'),
            'emailaddress'=>get_user_info_by_id($this->session->userdata('userid'),'email'),
            'url'=>current_url(),
            'context'=>$context
            
        );

        $this->db->insert($this->_tablename, $data) or $this->db->error();

 

        return TRUE;
    }






}