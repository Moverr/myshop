<?php

class Contract_price_m extends MY_Model
{
    public $_tablename = 'contract_prices';
    public $_primary_key = 'id';

 	function __construct()
    {

        parent::__construct();
    }

    function get_contract_price_info($passed_id,$param)
    {

        //if NO ID
        if($passed_id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where($this->_primary_key,$passed_id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {
                        case 'contract_id':
                            $result=$row['contract_id'];
                            break;

                        case 'amount':
                            $result=$row['amount'];
                            break;

                        case 'xrate':
                            $result=$row['xrate'];
                            break;
                        case 'currency_id':
                            $result=$row['currency_id'];
                            break;

                        case 'currency':
                            $result=get_currency_info_by_id($row['currency_id'],'abbrv');
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

        }
    }


    function get_contract_price_info_by_contract($passed_id,$param)
    {

        //if NO ID
        if($passed_id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where('contract_id',$passed_id)->get();
            //echo $this->db->last_query();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {
                        case 'id':
                            $result=$row['id'];
                            break;

                        case 'amount':
                            $result=$row['amount'];
                            break;

                        case 'xrate':
                            $result=$row['xrate'];
                            break;
                        case 'rate':
                            $result=$row['xrate'];
                            break;
                        case 'currency_id':
                            $result=$row['currency_id'];
                            break;

                        case 'currency':
                            $result=get_currency_info_by_id($row['currency_id'],'abbrv');
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

        }
    }




}