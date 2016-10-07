<?php
class Role_m extends MY_Model
{
    public $_tablename='roles';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();


    }


    //get roles of currently logged in user
    function get_my_roles()
    {

        $where=array(
            'userid'=>$this->session->userdata('userid'),
            'isactive'=>'Y'
        );
        //the assumption is on user one group
        $result_set=$this->get_where_with_limit($where);

        //print_array(get_all_session_data());
        //echo $this->db->last_query();
        //exit;

        //print_array($result_set);


        if($result_set)
        {
            //array to hold the groups
            $groups=array();
            foreach($result_set as $row)
            {
                //insert group into array
                $groups[]=$row['groupid'];
            }

            //print_array($groups);

            //return groups
            return $groups;

        }
        else
        {
            return FALSE;
        }

    }

}
 