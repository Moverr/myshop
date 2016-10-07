<?php

class MY_Model extends CI_Model
{

    public $_tablename;
    public $_primary_key;

    function __construct()
    {
        //load ci model
        parent::__construct();
    }

    //visible and trashed


    //get by id

    public function create($data, $message = '')
    {

        $this->db->insert($this->_tablename, $data);
        //print_array($this->db->last_query());
        //echo $this->db->_error_message();

        //generate message
        if ($message)
        {
            //generate notification
            generate_notification($message, 'alert alert-info');
        }

        //clear cache
        $this->db->cache_delete_all();

        return $this->db->insert_id();

    }





    public function delete($id, $message = '')
    {
        //security purposes
        if (!$id)
        {
            //if an id has not been passed
            return FALSE;
        }
        else
        {
            $this->db->where('id', $id);
            $data = array
            (
                'trash' => 'y'
            );
            $this->db->update($this->_tablename, $data);

            //generate message
            if ($message) {
                //generate notification
                generate_notification($message, 'alert alert-info');
            }

            //clear cache
            $this->db->cache_delete_all();

            return $this->db->affected_rows();
        }
    }







    //get paginated

    public function update_slugs()
    {
        foreach ($this->get_all() as $row) {
            if ($row['slug'] == '') {
                $data['slug'] = strtolower(seo_url($row['title']));
                $this->update($row['id'], $data);
            }
        }
    }


    //get latest

    public function get_all($order = 'DESC')//visible is either y or n
    {
        $query = $this->db->select()->from($this->_tablename)->where('trash', 'n')->order_by($this->_primary_key, $order)->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }


    //create default pic for empty profiles

    public function update($id, $data, $message = '')
    {
        $this->db->where('id', $id);

        $this->db->update($this->_tablename, $data);

        //print_array($this->db->last_query());
        //generate message
        if ($message) {
            //generate notification
            generate_notification($message, 'alert alert-info');
        }

        //clear cache
        $this->db->cache_delete_all();


        return $this->db->affected_rows();

    }


    //update empty slugs

    public function update_images()
    {
        foreach ($this->get_all() as $row)
        {
            if ($row['imageurl'] == '') {
                $data['imageurl'] = 'noimage.png';
                $this->update($row['id'], $data);
            }
        }
    }

    public function autocreate_avatar()
    {
        $users = $this->get_by_id($id = '');//get all untrashed users
        foreach ($users as $user) {
            //if there is no slug create one
            if ($user['avatar'] == '') {
                $data = array
                (
                    'avatar' => 'avatar.jpg'
                );
                $this->db->where('id', $user['id']);
                $this->db->update($this->_tablename, $data);
            }
        }
    }

    //delete

    public function get_by_id($id = '', $order = 'DESC')
    {
        //if its empty get all visible
        if ($id == '') {
            $query = $this->db->select()->from($this->_tablename)->where('trash', 'n')->order_by($this->_primary_key, $order)->limit(1)->get();
        } else {
            $data = array
            (
                'id' => $id,
                'trash' => 'n'
            );
            $query = $this->db->select()->from($this->_tablename)->where($data)->order_by($this->_primary_key, 'DESC')->get();

        }
        //print_array($this->db->last_query());

        return $query->result_array();
    }

    //update by

    function get_sum($column)
    {


        $query = $this->db->query("SELECT SUM($column) as $column FROM $this->_tablename");

        foreach ($query->result_array() as $totals_array) {
            foreach ($totals_array as $sum) {
                return $sum;
            }
        }


    }

    function custom_query($query)
    {
        $query = $this->db->query($query);
        //print_array($this->db->last_query());
        return $query->result_array();
    }

    public function hard_delete($id, $message = '')
    {
        //security purposes
        if (!$id) {
            //if an id has not been passed
            return FALSE;
        } else {


            $this->db->where('id', $id);
            $this->db->delete($this->_tablename);

            //generate message
            if ($message) {
                //generate notification
                generate_notification($message, 'alert alert-info');
            }


            //clear cache
            $this->db->cache_delete_all();

            return $this->db->affected_rows();
        }
    }

    //when multiple parameters are given

    public function delete_by($key, $key_value, $message = '')
    {
        $this->db->where($key, $key_value);
        $this->db->delete($this->_tablename);

        echo $this->db->last_query();
        //generate message
        if ($message) {
            //generate notification
            generate_notification($message, 'alert alert-info');
        }

        //clear cache
        $this->db->cache_delete_all();


        return $this->db->affected_rows();

    }

    public function get_where_sort($where, $sort = 'DESC')
    {
//        if(!$where['trash']){
//            $where['trash']='n';
//        }
        $query = $this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key, $sort)->get();
        //echo $this->db->last_query();

        return $query->result_array();
    }


    //reorder table elements

    public function update_view_count($item_id)
    {
        $count = 1;


        $this->db->set('view_count', 'view_count+' . $count, FALSE);
        $this->db->where("id", $item_id);
        $this->db->update($this->_tablename);

        return true;

    }

    public function get_where_with_limits($where, $start, $end)
    {
        $this->db->select()->from($this->_tablename)->where($where);
        $dateone = date('Y-m-d', strtotime(str_replace('-', '/', $start)));
        $datetwo = date('Y-m-d', strtotime(str_replace('-', '/', $end)));
        $this->db->where('dateadded >=', $dateone);
        $this->db->where('dateadded <=', $datetwo);
        $query = $this->db->get();
        $results = $query->result();
        //echo $this->db->last_query();

        return $results;
    }

    function reorder($orderfield, $idfield, $id = null, $pos = null, $newpos = null)
    {
        if ($pos != $newpos) {
            if ($newpos > $pos) {
                mysql_query($sql = "UPDATE " . $this->_tablename . " SET " . $orderfield . "=" . $orderfield . "-1 WHERE " . $orderfield . "<= '" . $newpos . "' AND $idfield<>'" . $id . "'");
                mysql_query($sql = "UPDATE " . $this->_tablename . " SET " . $orderfield . "=" . $orderfield . "+1 WHERE " . $orderfield . "> '" . $newpos . "' AND $idfield<>'" . $id . "'");
            } else {
                mysql_query($sql = "UPDATE " . $this->_tablename . " SET " . $orderfield . "=" . $orderfield . "-1 WHERE " . $orderfield . "< '" . $newpos . "' AND $idfield<>'" . $id . "'");
                mysql_query($sql = "UPDATE " . $this->_tablename . " SET " . $orderfield . "=" . $orderfield . "+1 WHERE " . $orderfield . ">= '" . $newpos . "' AND $idfield<>'" . $id . "'");
            }
        }
        if ($pos != $newpos || ($pos == null && $newpos == null && $id == null)) {
            $rs = mysql_query($sql = "SELECT $orderfield,$idfield FROM " . $this->_tablename . " ORDER BY " . $orderfield . " ASC");
            $p = 0;
            while ($r = mysql_fetch_array($rs)) {
                $p++;
                mysql_query($sql = "UPDATE " . $this->_tablename . " SET " . $orderfield . "='" . $p . "' WHERE " . $idfield . "= '" . $r[$idfield] . "'");
            }
        }
        //clear cache
        $this->db->cache_delete_all();

        /*
         * http://www.barattalo.it/2013/12/18/reorder-records-table/
         *
         * usage
         * reorder("users","pos","id",9,3,1); 9=>iser id 3=>current position 1 is new position
         */
    }

    public function get_paginated($num = 20, $start, $order = 'DESC')
    {
        //echo $this->$_primary_key.'foo';
        //build query
        $q = $this->db->select()->from($this->_tablename)->limit($num, $start)->where('trash', 'n')->order_by($this->_primary_key, $order)->get();
        //echo $this->db->last_query();

        //return result
        return $q->result_array();

    }

    public function get_paginated_by_criteria($num = 20, $start, $criteria, $order = 'DESC')
    {
        //build query
        $q = $this->db->select()->from($this->_tablename)->limit($num, $start)->where($criteria)->order_by($this->_primary_key, $order)->get();
        //echo $this->db->last_query();

        //return result
        return $q->result_array();

    }

    public function get_by_slug($slug, $order = 'DESC')
    {
        if (!$slug) {
            return NULL;
        } else {
            $query = $this->db->select()->from($this->_tablename)->where('slug', $slug)->order_by($this->_primary_key, $order)->limit(1)->get();

            return $query->result_array();
        }


    }

    public function get_where($where, $order = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key, $order)->get();
        //print_array( $this->db->last_query());

        return $query->result_array();
    }

    //update by

    public function search($data, $order = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->like($data)->order_by($this->_primary_key, $order)->get();
        return $query->result_array();
    }

    //prevent duplicate editing

    function get_latest($limit = 20, $order = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->where('trash', 'n')->order_by($this->_primary_key, $order)->limit($limit)->get();
        return $query->result_array();
    }


    public function update_by($key, $key_value, $data, $message = '', $delete_cache = 'y')
    {
        $this->db->where($key, $key_value);
        $this->db->update($this->_tablename, $data);

        //echo $this->db->last_query();
        //generate message
        if ($message) {
            //generate notification
            generate_notification($message, 'alert alert-info');
        }

        if ($delete_cache == 'y') {
            //clear cache
            $this->db->cache_delete_all();
        }


        return $this->db->affected_rows();

    }

    function check_by_slug($slug = '', $sort = 'DESC')
    {
        if ($slug == '') {
            return NULL;
        } else {
            $data = array
            (
                'trash' => 'n',
                'slug' => $slug
            );
            //$this->output->cache(60); // Will expire in 60 minutes
            $query = $this->db->select()->from($this->_tablename)->where($data)->order_by($this->_primary_key, $sort)->get();


            if ($query->num_rows() > 0) {
                foreach ($query->result() as $row) {
                    return $row->id;

                }

            } else {
                return FALSE;
            }
        }
    }

    function verify_item($id, $item, $field, $sort = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->where($field, $item)->order_by($this->_primary_key, $sort)->get();

        //echo $this->db->last_query();
        if ($query->result_array()) {
            foreach ($query->result_array() as $row) {
                //echo $row['id'];
                if ($row['id'] == $id) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        } else {
            return TRUE;
        }


    }


    public function search_paginated_by_criteria($num = 20, $start, $criteria, $sort = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->like($criteria)->limit($num, $start)->order_by($this->_primary_key, $sort)->get();
        //echo $this->db->last_query();
        //echo $this->db->_error_message();
        return $query->result_array();
    }

    function get_popular()
    {
        $where=array
        (
            'trash'=>'n'
        );
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by('view_count','DESC')->get();
        //print_array($this->db->last_query());


        return $query->result_array();
    }

    public function get_popular_paginated($num = 20, $start, $sort = 'DESC')
    {

        $where = array
        (
            'trash' => 'n'
        );
        $query = $this->db->select()->from($this->_tablename)->limit($num, $start)->where($where)->order_by('view_count', $sort)->get();

        return $query->result_array();
    }

    public function get_where_order_by($where,  $key='id', $order = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->where($where)->order_by($key, $order)->get();
        //print_array( $this->db->last_query());

        return $query->result_array();
    }

    public function get_where_with_limit($where, $limit = '10', $order = 'DESC')
    {
        $query = $this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key, $order)->limit($limit)->get();
        //print_array( $this->db->last_query());

        return $query->result_array();
    }


    public function count_all()
    {

        return $this->db->count_all($this->_tablename);

    }

    public function count_all_active()
    {
        $where=array
        (
            'trash'=>'n'
        );

        return $this->db->count_all($this->_tablename)->where($where);

    }

    public function get_count_by_criteria($where=''){
        if(!is_array($where)){
            return $this->count_all();
        }else{
            return $this->db
                ->where($where)
                ->count_all_results($this->_tablename);
        }

    }





}