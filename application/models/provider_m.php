<?php

/*
@mover 1st jan Newwvetech
Manage Pde CRUD
*/
class Provider_m extends MY_Model
{
 	function __construct()
    {

        parent::__construct();
    }
    public $_tablename='providers';
    public $_primary_key='providerid';


    public function get_provider_info($id='', $param='')
    {

        //if NO ID
        if($id=='')
        {
            return NULL;
        }
        else
        {
            //get user info
            $query=$this->db->select()->from($this->_tablename)->where($this->_primary_key,$id)->get();

            if($query->result_array())
            {
                foreach($query->result_array() as $row)
                {
                    //filter results
                    switch($param)
                    {


                        case 'title':
                            $result=$row['providernames'];
                            break;


                        default:
                            $result=$query->result_array();
                    }

                }

                return $result;
            }

        }
    }


    public function get_provider_by_procurement($procurement_id){
        $results = $this->custom_query("SELECT
providers.providernames
FROM
procurement_plan_entries
INNER JOIN bidinvitations ON procurement_plan_entries.id = bidinvitations.procurement_id
INNER JOIN receipts ON bidinvitations.id = receipts.bid_id
INNER JOIN providers ON receipts.providerid = providers.providerid
WHERE
receipts.beb = 'Y' AND
receipts.isactive = 'Y' AND
bidinvitations.isactive = 'Y' AND
procurement_plan_entries.isactive = 'Y' AND
procurement_plan_entries.id = $procurement_id");

        $provider='';

        foreach($results as $row){
            $provider=$row['providernames'];
        }

        return $provider;
    }

    public function get_attempted_provider_by_procurement($procurement_id){
        $results = $this->custom_query("SELECT
providers.providernames
FROM
procurement_plan_entries
INNER JOIN bidinvitations ON procurement_plan_entries.id = bidinvitations.procurement_id
INNER JOIN receipts ON bidinvitations.id = receipts.bid_id
INNER JOIN providers ON receipts.providerid = providers.providerid
WHERE

receipts.isactive = 'Y' AND
bidinvitations.isactive = 'Y' AND
procurement_plan_entries.isactive = 'Y' AND
procurement_plan_entries.id = $procurement_id");

        $provider='';

        foreach($results as $row){
            $provider=$row['providernames'];
        }

        return $provider;
    }



    function get_provider_by_receipt_id($receipt_id = 0){

        #print_r($receipt_id);

          $sql = " SELECT r.receiptid,( SELECT GROUP_CONCAT(providernames) providernames   FROM providers  WHERE  providers.providerid IN (SELECT  IF(receipts.providerid  > 0,receipts.providerid, TRIM(TRAILING ',' FROM jv.providers) )  FROM receipts LEFT OUTER JOIN joint_venture jv ON receipts.joint_venture = jv.jv WHERE receipts.receiptid = r.receiptid  )) providers 
               FROM receipts r WHERE r.receiptid = ".$receipt_id."  LIMIT 1 ";

               #WHERE r.receiptid = ".$receiptid."

           $result =  $this->db->query($sql)->result_array();  
 

            return $result;

 

    }









}