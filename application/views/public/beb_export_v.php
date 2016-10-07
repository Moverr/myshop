<ol class="breadcrumb">
    <li>
        <a href="<?= base_url() . 'page/home' ?>">Back To  All Current Tenders</a>
    </li>

</ol>
<div class="widget widget-table">
    <div class="btn-group pull-right">
        <button class="btn btn-danger dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i> Export Data
        </button>
        <ul class="dropdown-menu">


            <li><a href="#" onClick="$('#customers2').tableExport({type:'excel',escape:'false'});"><img
                        src='<?= base_url() ?>assets/img/icons/xls.png' width="24"/> XLS</a></li>
            <li><a href="#" onClick="$('#customers2').tableExport({type:'doc',escape:'false'});"><img
                        src='<?= base_url() ?>assets/img/icons/word.png' width="24"/> Word</a></li>

        </ul>
    </div>


    <div class="widget-content">

        <table id="customers2" class="table table-sorting table-striped table-hover datatable" cellpadding="0"
               cellspacing="0"
               width="100%">
            <thead>
            <tr>
                <th>Date Posted</th>

                <th>Procuring / Disposing Entity</th>
                <th> Procurement Reference Number</th>
                <th> Selected Provider </th>
                <th>Subject</th>
                <th>Date BEB Expires</th>
                 <th>Status</th>
                 <th>BEB Price</th>
            </tr>

            </thead>
            <tbody>
            <?php
            #print_r($page_list);

            foreach ($page_list['page_list'] as $entry => $row) {
              #  print_r($record);
               # if (get_procurement_plan_entry_info_reference_number($record['procurement_ref_no'], 'pde') != '') {
                    ?>
                    <tr>

                <td><?= custom_date_format('d M, Y', $row['dateadded']); ?></td>
                <td><?=$row['pdename']; ?></td>
                <td> <?=$row['procurement_ref_no']; ?></td>
                <td> 
                <?php
                   if(((strpos($row['providernames'] ,",")!== false)) || (preg_match('/[0-9]+/', $row['providernames'] )))
      {

      $label = '';
      $providers  = rtrim($row['providernames'],",");
      $rows= mysql_query("SELECT * FROM `providers` where providerid in ($providers) ") or die("".mysql_error());
      $provider = "";
      $x = 0;
      $xl = 0;
         
        while($vaue = mysql_fetch_array($rows))
        {
            $x ++;
             if(mysql_num_rows($rows) > 1)
            {
                 $lead = '';
                  #print_r($provider_array);
              if ($row['providerlead'] ==   $vaue['providerid']) {
                       $lead = '&nbsp; <span class="label" title="Project Lead " style="cursor:pointer;background:#fff;color:orange;padding:0px;margin:0px; margin-left:-15px; font-size:18px; " >&#42;</span>';
              #break;
                    }
                    else{
                      $lead = '';
                     
                  }
             
                $provider  .= "<li>";
                $provider  .=   strpos($vaue['providernames'] ,"0") !== false ? '' :  $lead.$vaue['providernames'];
                $provider  .= "</li>";
             
            }else{
             $provider  .=strpos($vaue['providernames'] ,"0") !== false ? '' : $vaue['providernames'];
            }
        }

         if(mysql_num_rows($rows) > 1){
            $provider .= "</ul>";}
         else{
         $provider = rtrim($provider,' ,');
          }

      if($x > 1)
        $label = '<span class="label label-info">Joint Venture</span>';
        print_r($provider.'&nbsp; '.$label );
    $x  = 0 ;
    $label = '';
    }
                     else{  echo $row['providernames'];}
                ?>
                </td>
                <td><?=$row['subject_of_procurement']; ?></td>
                <td><?=date("d M, Y",strtotime($row['beb_expiry_date'])); ?></td>
                <td>
                    <?php
                     switch($row['isreviewed'])
                                        {

                                          case 'Y':
                                          print (" <span class='label label-info '> For Admin Review </span>  <br/> <span class='label label-success'>".$row['review_level']." </span> <br/>");
                                        #  print "<span class='label label-info'".$row['review_level']."</span>";
                                          //class="label label-info"
                                          break;


                                          case 'N':
                                           print (" <span class='btn btn-xs btn-success'> Active </span>");
                                    
                                          break;


                                          default:
                                          print("-");
                                          break;
                                        }

                    ?>
                </td>
                <td>
                <?php

                     $readout = mysql_query("SELECT * FROM readoutprices WHERE receiptid=".$row['receiptid']."");
                            
                            if(mysql_num_rows($readout) > 0 )
                            {
                              echo "<ul>";
                              while ( $valsue = mysql_fetch_array($readout)) {
                                if($valsue['readoutprice']<=0)
                                  continue;
                                # code...
                                 echo "<li>".number_format($valsue['readoutprice']).$valsue['currence']."</li>";
                              }
                              echo "</ul>";
                            }
                ?>
                </td>

                      </tr>
                <?php
               # }

            }
            ?>
            </tbody>
        </table>
    </div>
</div>