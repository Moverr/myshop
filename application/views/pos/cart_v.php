<?php 


if(!empty($cart))
{
  ?>
  <table class="table table-stripped">
  <tr>
  <th> </th>
  <th>ITEM NAME </th>
  <th> Quantity </th>
  <th> Unit Selling Price </th>
  <th> Exchange Rate </th> 
  <th> Currency </th> 
  <th> Total Amount (UGx) </th> 

  </tr>
  <?php
  foreach ($cart as $key => $row) {
    # code...
    ?>
    <tr>
    <td > 
    </td>
    <td>
    <?=$row['item_name']; ?>
    </td>
     <td>
    <?=number_format($row['quantity']); ?>
    </td>
     <td>
    <?=number_format($row['unit_selling_price']); ?>
    </td>
     <td>
    <?=number_format($row['unit_selling_price_exchange_rate']); ?>
    </td>
     <td>
    <?=get_currency_info_by_id($row['unit_selling_price_currency'],'title'); ?>
    </td>
      <td>
    <?=number_format($row['unit_selling_price'] * $row['unit_selling_price_exchange_rate']  ) ; ?>
    </td>

    </tr>
    <?php
  }
  ?>
  </table>
  <?php
}

?>