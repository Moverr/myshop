<?php
class Permission_m extends MY_Model
{
    public $_tablename='permissions';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();


    }

    function get_permission_info_by_id($id='',$param='')
    {
        if($id=='')
        {
            return NULL;
        }
        else
        {
            $query=$this->db->select()->from($this->_tablename) ->where($this->_primary_key,$id)->get();
        }

        if($query->result_array())
        {
            foreach($query->result_array() as $row)
            {
                switch($param)
                {
                    case 'section':
                        $result=$row['section'];
                        break;
                    case 'code':
                        $result=$row['code'];
                        break;
                    case 'permission':
                        $result=$row['permission'];
                        break;
                    case 'accessby':
                        $result=$row['accessby'];
                        break;
                    case 'otherschecked':
                        $result=$row['otherschecked'];
                        break;
                    case 'accesslink':
                        $result=$row['accesslink'];
                        break;
                    case 'accessfor':
                        $result=$row['accessfor'];
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



}
 