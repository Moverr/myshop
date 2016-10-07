SELECT

 bidinvitations.*,
 bidinvitations.procurement_ref_no as procurement_ref_no ,
bidinvitations.id as bid_id, bestevaluatedbidder.pid 

 FROM bidinvitations 
  INNER  JOIN receipts
  ON bidinvitations.id = receipts.bid_id
  INNER JOIN bestevaluatedbidder
 ON bestevaluatedbidder.pid = receipts.receiptid
  INNER JOIN procurement_plan_entries 
  ON bidinvitations.procurement_id =  procurement_plan_entries.id
  	where  bestevaluatedbidder.id = 4554