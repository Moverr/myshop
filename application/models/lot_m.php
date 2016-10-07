<?php

class Lot_m extends MY_Model
{
    public $_tablename = 'lots';
    public $_primary_key = 'id';

    function __construct()
    {
        parent::__construct();


    }

    function get_lot_info($lot_id)
    {
        $results = $this->custom_query("
        SELECT
  lots.id,
  lots.lot_title,
  lots.lot_details,
  lots.lot_number,
  lots.bid_id,
  providers.providernames
FROM
  contracts
  INNER JOIN lots ON lots.id = contracts.lotid
  INNER JOIN received_lots ON received_lots.lotid = lots.id
  INNER JOIN receipts ON receipts.receiptid = received_lots.receiptid
  INNER JOIN providers ON providers.providerid = receipts.providerid
WHERE
  contracts.isactive = 'Y' AND
  contracts.lotid > 0 AND
  lots.isactive = 'Y' AND
  lots.id = $lot_id AND receipts.beb ='Y'
");

        //echo print_array($this->db->last_query());
        //exit;

        return $results;
    }


}