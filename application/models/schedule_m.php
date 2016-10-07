<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 5/19/2015
 * Time: 2:45 PM
 */
class Schedule_m extends MY_Model
{
    public $_tablename = '';
    public $_primary_key = '';
    function __construct()
    {
        parent::__construct();

        $this->add_user();
    }

    function add_user(){
        $this->_tablename ='users';
        $this->_primary_key='id';
        $where=array(
            'firstname'=>'foo'
        );

        $this->create($where);
    }
}