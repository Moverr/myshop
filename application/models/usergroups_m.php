<?php

class Usergroups_m extends MY_Model
{
    public $_tablename = 'usergroups';
    public $_primary_key = 'id';
	
	#Constructor
	function Usergroups_m()
	{
		parent::__construct();
		$this->load->model('Query_reader', 'Query_reader', TRUE);
		$this->load->model('Ip_location', 'iplocator');
	}
	

	#FETCH USER GROUPS :: 
	function fetchusergroups()
	{
		$query = $this->Query_reader->get_query_by_code('fetchusergroups', array('searchstring' =>' 1 and 1'));
		$result = $this->db->query($query)->result_array();
		return $result;
	}

    //get user types
    function get_usergroup_info_by_id($id = '', $param = '')
    {
        if ($id == '') {
            return NULL;
        } else {
            $query = $this->db->select()->from($this->_tablename)->where($this->_primary_key, $id)->get();
        }

        if ($query->result_array()) {
            foreach ($query->result_array() as $row) {
                switch ($param) {
                    case 'groupname':
                        $result = $row['groupname'];
                        break;
                    case 'title':
                        $result = $row['groupname'];
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
