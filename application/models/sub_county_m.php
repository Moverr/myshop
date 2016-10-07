<?php
class Sub_county_m extends MY_Model
{
    public $_tablename = 'sub_counties';
    public $_primary_key = 'id';

    function __construct()
    {
        parent::__construct();
        $this->load->model('user_m');

        $this->load->model('district_m');


    }


    function get_sub_county_by_id($id = '', $param = '')
    {
        if ($id == '') {
            return NULL;
        } else {
            $query = $this->db->select()->from($this->_tablename)->where($this->_primary_key, $id)->get();
        }

        if ($query->result_array()) {
            foreach ($query->result_array() as $row) {
                switch ($param) {

                    case 'title':
                        $result = $row['title'];
                        break;
                    case 'dateupdated':
                        $result = $row['dateupdated'];
                        break;
                    case 'dateadded':
                        $result = $row['dateadded'];
                        break;
                    case 'district':
                        $result =$this->district_m->get_district_by_id($id=$row['district_id'],$param='title') ;
                        break;
                    case 'trash':
                        $result = $row['trash'];
                        break;
                    case 'author':
                        $result = $this->user_m->get_user_info($user_id = $row['author'], $param = 'fullname');
                        break;
                    default:
                        $result = $query->result_array();
                }
            }

            return $result;
        } else {
            return NULL;
        }


    }
}