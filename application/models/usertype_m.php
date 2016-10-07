<?php
class Usertype_m extends MY_Model
{
    public $_tablename='usertypes';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();

        $this->autocreate_supeusertype();

        $this->update_slugs();

    }

    //get user types
    function get_usertypes_by_id($id='',$param='')
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
                    case 'usertype':
                        $result=$row['usertype'];
                        break;
                    case 'title':
                        $result=$row['usertype'];
                        break;
                    case 'trash':
                        $result=$row['trash'];
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

    //get user types
    function get_usertype_info_by_id($id='',$param='')
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
                    case 'usertype':
                        $result=$row['usertype'];
                        break;
                    case 'title':
                        $result=$row['usertype'];
                        break;
                    case 'trash':
                        $result=$row['trash'];
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

    public function autocreate_supeusertype()
    {
        $usertypes=$this->get_all();//get all active users
        //if there are no users create a superuser
        if(count($usertypes)==0)
        {
            $usertype_data=array
            (
                'usertype'     =>'superuser'
            );

            $this->create($usertype_data);
        }
    }


    public function update_slugs()
    {
        foreach($this->get_all()as $row)
        {
            if($row['slug']=='')
            {
                $data['slug']=strtolower(seo_url($row['usertype']));
                $this->update($row['id'],$data);
            }
        }
    }





}
 