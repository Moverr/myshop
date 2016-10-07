<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>BEB NOTICE</title>
<script src="<?=base_url()?>js/jquery.PrintArea.js" type="text/JavaScript" language="javascript"></script>      
<link type="text/css" rel="stylesheet" href="<?=base_url()?>js/PrintArea.css" />
  <script type="text/javascript">
   $(function(){

    //print functionality in the header
    $('.print').click(function(){
        //alert('Ready to Print');
        w = window.outerWidth-10;
        $(".printarea").css("width", '100%');
        $(".printarea").printArea();
         });
})
    </script>

</head>

<body>

 <style> 
  body{padding:0px;}
  #wrappers {  width:900px; margin: auto; }
  #wrappers .headers{   text-align:center;  }
    #bebnotice-table {  border: solid thin;  border-collapse: collapse;   table-layout: fixed; }
#bebnotice-table caption {  padding-bottom: 0.5em;}#bebnotice-table th,#bebnotice-table td {  border: solid thin;
  padding: 0.5rem 2rem;}#bebnotice-table td {  }
#bebnotice-table tbody td:first-child::after {  content: leader(". "); '}
#bebnotice-table td{ word-wrap:break-word;
 text-overflow:ellipsis;
    overflow:hidden;
    white-space:nowrap;
	}
body {  padding: 1rem;} </style>
<div class = "wrappers printarea" id="wrappers">

  <?php
#  print_r($beb);
  ?>
<H1 class="headers"> BEST EVALUATED BIDDER NOTICE</H1>
<p>
The Bidder  name below  has been evaluated  as the best evaluated bidder  for the  procurement of  equipment detailed below.  It is the intention of  the Procuring  and Disposing Entity to place  a contract  with the bidder  named after ten working days from the date for  the display  given below

</p>
<table width="100%" border="1" id="bebnotice-table">
  <tr>
    <th width="56%" align="left" scope="row">Procurement Reference Number:</th>
    <td width="44%"><?=$beb['page_list'][0]['procurement_ref_no']; ?></td>
  </tr>
  <tr>
    <th align="left" scope="row">Subject of Procurement</th>
    <td><?=$beb['page_list'][0]['subject_of_procurement']; ?></td>
  </tr>
  <tr>
    <th align="left" scope="row">Method of Procurement</th>
    <td><?=$beb['page_list'][0]['procurementmethod']; ?> </td>
  </tr>
  <tr>
    <th align="left" scope="row">Best Evaluated Bidder Names</th>
    <td>
      <?php
      if(!empty($haslots) && $haslots == 'Y')
      {
              $recordquery  = $this ->db->query("SELECT lots.lot_number,lots.lot_title,IF(receipts.providerid > 0,receipts.providerid,joint_venture.providers) as providers  FROM receipts  INNER JOIN bestevaluatedbidder  ON receipts.receiptid = bestevaluatedbidder.pid  LEFT JOIN joint_venture ON receipts.joint_venture = joint_venture.jv   INNER JOIN received_lots ON receipts.receiptid = received_lots.receiptid    INNER JOIN lots ON lots.id = received_lots.lotid   WHERE beb = 'Y'  AND lots.bid_id = ".mysql_real_escape_string($bidid) )->result_array();
              ?>
              <table width="98%"    style="margin:auto;  border:none;  border:1px solid thin  #fff;">
              <tr>
              <th>LOT NUMBER</th>
              <TH>LOT TITLE</TH>
              <TH>PROVIDER NAMES </TH>
              </tr>
              <?php
                foreach ($recordquery as $key => $recorsd) {
                  # code...


            $provider = rtrim($recorsd['providers'],',');
            $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();

            $providerlist = '';
            $x = 0;
            foreach($result as $key => $record){
              $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
              $x ++ ;
            }

            $providerlists = ($x > 1 )? rtrim($providerlist,',').' <span class="label label-info">Joint Venture</span> ' : $providerlist;


                  ?>
                   <tr>
                    <td><?=$recorsd['lot_number']; ?></td>
                    <td><?=$recorsd['lot_title']; ?></td>
                    <td> 
                    <?php
                     print $providerlists;
                    ?> </td>
                    </tr>
                  <?php
                }
              ?>
              </table>
              <?php 
          
      }
//If this is a Framework BEB 
else if(!empty($formdata['framework']) && $formdata['framework'] == 'Y')
{
   
     $records = $this->db->query("SELECT receipts.receiptid, IF(receipts.providerid > 0,receipts.providerid,joint_venture.providers) as providers  FROM receipts  INNER JOIN bestevaluatedbidder  ON receipts.receiptid = bestevaluatedbidder.pid  LEFT JOIN joint_venture ON receipts.joint_venture = joint_venture.jv     WHERE receipts.beb = 'Y' AND receipts.bid_id ='".$formdata['bidid']."' ")->result_array();
   # print_r($result);
   ?>
      <ul>
        <?php
   foreach ($records as $key => $row) {
            $provider = rtrim($row['providers'],',');
            $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();

            $providerlist = '';
            $x = 0;
            foreach($result as $key => $record){
              $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
              $x ++ ;
            }

            $providerlists = ($x > 1 )? rtrim($providerlist,',').' <span class="label label-info">Joint Venture</span> ' : $providerlist;
          
            echo "<li> ".$providerlists."</li>";

     
   }
  
    ?>
      </ul>
      <?php
  
  
  
}
      else
      {
        

      
      $provider = rtrim($beb['page_list'][0]['providers'],',');
      $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();

      $providerlist = '';
      $x = 0;
      foreach($result as $key => $record){
        $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
        $x ++ ;
      }

      $providerlists = ($x > 1 )? rtrim($providerlist,',').' <span class="label label-info">Joint Venture</span> ' : $providerlist;
      print $providerlists;
    }




      ?></td>
  </tr>
  <tr>
  <th align="left" scope="row" >Contract Price </th>
  <td>
	<?php

			if(!empty($haslots) && $haslots == 'Y')
				  {
					  
					  $string = "(0";
			foreach ($beb['page_list'] as $key => $row) {
			  # code... 
			  $string .= ",".$row['receiptid'];
			  
			}
			$string  .= ")";


 
 
       $lots_contract_amount  = $this ->db->query("SELECT lots.lot_number,lots.lot_title,IF(receipts.providerid > 0,receipts.providerid,joint_venture.providers) as providers,bestevaluatedbidder.contractprice,bestevaluatedbidder.currency  FROM receipts INNER JOIN bestevaluatedbidder ON receipts.receiptid = bestevaluatedbidder.pid  LEFT JOIN joint_venture ON receipts.joint_venture = joint_venture.jv   INNER JOIN received_lots ON receipts.receiptid = received_lots.receiptid    INNER JOIN lots ON lots.id = received_lots.lotid   WHERE beb = 'Y'  AND receipts.receiptid  in ".$string." ")->result_array();
       #print_r($lots_contract_amount);
      ?>
       <table width="98%"    style="margin:auto;  border:none;  border:1px solid thin  #fff;">
        <tr>  
        <th>LOT NUMBER</th>
        <TH>LOT TITLE</TH>
        <TH>AMOUNT</TH>
        </tr>
        <?php
          foreach ($lots_contract_amount as $key => $amount_record) {
            ?>
              <tr>
              <td><?=$amount_record['lot_number']; ?></td>
              <td><?=$amount_record['lot_title']; ?></td>
              <td> 
              <?=($amount_record['contractprice'] > 0 ) ?  number_format($amount_record['contractprice']).''.$amount_record['currency'] : '';  ?> </td>
              </tr>
            <?php
            }?>
        </table>

      <?php
     }

//If this is a Framework BEB 
else if(!empty($formdata['framework']) && $formdata['framework'] == 'Y')
{
   
     $records = $this->db->query("SELECT receipts.receiptid, IF(receipts.providerid > 0,receipts.providerid,joint_venture.providers) as providers, bestevaluatedbidder.contractprice,bestevaluatedbidder.currency   FROM receipts  INNER JOIN bestevaluatedbidder  ON receipts.receiptid = bestevaluatedbidder.pid  LEFT JOIN joint_venture ON receipts.joint_venture = joint_venture.jv     WHERE receipts.beb = 'Y' AND receipts.bid_id ='".$formdata['bidid']."' ")->result_array();
    if(!empty($records))
    {
   ?>
     <table width="98%"    style="margin:auto;  border:none;  border:1px solid thin  #fff;">
      
       <tr> 
        <TH>PROVIDER </TH>
        <TH>AMOUNT</TH>
        </tr>
        <?php
   foreach ($records as $key => $row) {
            $provider = rtrim($row['providers'],',');
            $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();

            $providerlist = '';
            $x = 0;
            foreach($result as $key => $record){
              $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
              $x ++ ;
            }
         
            $providerlists = ($x > 1 )? rtrim($providerlist,',').' <span class="label label-info">Joint Venture</span> ' : $providerlist;
          
            $contract_amount =  ($row['contractprice'] > 0 ) ?  number_format($row['contractprice']).''.$row['currency'] : '';   
           
            echo "<tr><td> ".$providerlists."</td><td>   ".$contract_amount."</td></tr>";

     
   }
    }
  
    ?>
    </table>
      <?php
  
  
  
}

     else{
     ?>
   <?=($beb['page_list'][0]['contractprice'] > 0 ) ? number_format($beb['page_list'][0]['contractprice'])."".$beb['page_list'][0]['currency'] : ''; ?>
<?php } ?>
   </td>

  </tr>

</table>
<br/>
<table  width="100%" border="1" id="bebnotice-table">
  <tr>
    <th width="56%" align="left" scope="row">Date of Display</th>
    <td width="44%"><?=display_date($beb['page_list'][0]['date_of_display']); ?></td>
  </tr>
  <tr>
    <th align="left" scope="row">Date of Removal</th>
    <td><?php
      echo display_date($beb['page_list'][0]['beb_expiry_date']); ?></td>
  </tr>
</table>



<p>
<h1> Unsuccesful Bidders </h1>
</p>
<?php
$searchstring = '';
if(!empty($haslots) && $haslots == 'Y')
  {
        $unsuccesful_bidders = mysql_query("select receipts.*,lots.lot_number,lots.lot_title from receipts INNER JOIN received_lots ON receipts.receiptid = received_lots.receiptid INNER JOIN lots ON  received_lots.lotid = lots.id  where receipts.bid_id=".$beb['page_list'][0]['bid_id'].$searchstring." and beb != 'Y' ") or die("".mysql_error());
      }
      else
      {
       $unsuccesful_bidders = mysql_query("select * from receipts where bid_id=".$beb['page_list'][0]['bid_id'].$searchstring." and beb != 'Y' ") or die("".mysql_error());
      }
if(mysql_num_rows($unsuccesful_bidders) > 0)
{

?>

<table  width="100%" border="1" id="bebnotice-table">
  <tr>
   <th> 
<?php
$st = '#';
if(!empty($haslots) && $haslots == 'Y')
{
  $st = 'LOT';

}
echo $st;

?>
    </th>
    <th align="left" scope="row">NAME OF THE BIDDER</th>
    <th>REASON FOR BEING UNSUCCESSFUL</th>
  </tr>

<?php
while($row = mysql_fetch_array($unsuccesful_bidders))
{

  $provider = $row['providerid'] > 0 ? $row['providerid'] : mysql_fetch_array(mysql_query("select providers from joint_venture where jv = '".$row['joint_venture']."' limit 1"));

?>
  <tr>
   <td> 
<?php if(!empty($haslots) && $haslots == 'Y')
{ ?>
   <?=$row['lot_number'].'&nbsp; &nbsp;'.$row['lot_title']; ?>
<?php 
} ?>
    </td>
    <td align="left" scope="row"> <?php
    if( $row['providerid']  > 0 )
    {
    $provder =   mysql_query("select * from providers where providerid = ".$row['providerid']."") or die("".mysql_error());
    $addition = '';
   }else
     {
    $provder=    mysql_query("select * from providers where providerid in (".rtrim($provider['providers'],", ").")") or die("".mysql_error());
    $addition = '<span class="label label-info">Joint Venture</span>';
    }
     $p_record = '';
     while($rows = mysql_fetch_array($provder))
     {
       $p_record .=$rows['providernames'].',';
     }
      print_r(rtrim($p_record,',').$addition);
     ?>
     </td>
    <td><?=$row['reason']; ?>
    <br/>
    <?=$row['reason_detail']; ?>
    </td>
  </tr>
  <?php } ?>
</table>
<?php
}
else
{
  echo "No Unsuccessful Bidders";
}
?>

<?php
if(!empty($haslots) && $haslots == 'Y')
      {
        $recordqueryserial  = 
        $this ->db->query("SELECT lots.lot_number,lots.lot_title , bestevaluatedbidder.seerialnumber FROM receipts   INNER JOIN bestevaluatedbidder ON receipts.receiptid = bestevaluatedbidder.pid   INNER JOIN received_lots ON receipts.receiptid = received_lots.receiptid    INNER JOIN lots ON lots.id = received_lots.lotid   WHERE beb = 'Y'  AND lots.bid_id = ".mysql_real_escape_string($bidid) )->result_array();
       


       echo "<br/><table  width='100%' border='1' id='bebnotice-table' > 
       <tr><td  > SERIAL NUMBER(s) </td></tr>";
       foreach ($recordqueryserial as $key => $serial) {
         # code...
        echo "<tr><td>".$serial['lot_title']."</td><td>".$serial['seerialnumber']."</td></tr>";
       }
       echo "</table>";
       }
       else
        { ?>
      <label style="font-size:15px;">
Serial Number : <?=$beb['page_list'][0]['seerialnumber'];?>
<?php } ?>
</label>


<br/>
<h5>Authorized By </h5>
	 <p>Name &nbsp; &nbsp; : __________________________________________Title &nbsp; : ______________________________________________________
	  <br/>  
 <br/>  
	  Signature  ___________________________________________Date &nbsp; _______________________________________________________

	</p>

<br/>
<br/>
<br/>

</div>
 
</body>
</html>
