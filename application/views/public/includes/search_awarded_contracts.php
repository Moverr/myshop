 
 


  
         
        <div class="column">

         <a class="pull-right  btn btn-sm btn-danger"
                               href="<?= base_url() . 'page/awarded_contracts/level/export';  ?>">Export This Page</a>
                               
            <?php
            if(!empty($page_list))
            {
                # print_r($page_list);
                print '<div class="row titles_h">
                              <div class="col-md-2">
                                  <b>Procuring/Disposing Entity</b>
                              </div>
                              
                              <div class="col-md-2">
                                  <b>Subject Of Procurement</b>
                                  [Procurement Reference Number]
                              </div>
                              <div class="col-md-2">
                                  <b>Service Provider</b>
                              </div>
                                <div class="col-md-1">
                                  <b>Date Signed</b>
                              </div>
                               <div class="col-md-2">
                                 Date of Commencement <br/> <b>[ Date of Completion]</b>
                              </div>
                              <div class="col-md-2"><b>Contract Value  </b></div>
                               <div class="col-md-1">
                                  <b>Status</b>
                              </div>
                          </div><hr>';
                # print_r($page_list);

                foreach($page_list as $row)
                {
                    #if multiple providers..
                    $providername = $row['providernames'];
                    if(!empty($row['joint_venture'])):
                        $providername = '';
                        $jv_info = $this->db->query('SELECT * FROM joint_venture WHERE jv = "'. $row['joint_venture'] .'"')->result_array();

                        if(!empty($jv_info[0]['providers'])):
                            $providers = $this->db->query('SELECT * FROM providers WHERE providerid IN ('. rtrim($jv_info[0]['providers'], ',') .')')->result_array();
                            foreach($providers as $provider):
                                $providername .= (!empty($providername)? ', ' : ''). $provider['providernames'];
                            endforeach;

                        endif;

                    endif;

                    print '<div class="row">'.
                        '<div class="col-md-2 procurement_pde">'. $row['pdename'] .'</div>'.

                        '<div class="col-md-2 procurement_subject">'. $row['subject_of_procurement'] .'<br/><label class="procurement_pde">['.$row['procurement_ref_no'].']</label> <br/>
 
          
                              <a href="https://twitter.com/share" class="twitter-share-button  " 
                              data-url="'.base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/details/'.encryptValue($row['id']).'" data-size="small" data-hashtags="tenderportal_ug"
                              data-count="none" data-dnt="none"></a> &nbsp; <div class="g-plusone" 
                              data-action="share" data-size="medium" data-annotation="none" 
                              data-height="24" data-href="'.base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/details/'.encryptValue($row['id']).'" 
                              ></div>&nbsp;<div class="fb-share-button" 
                              data-href="'.base_url().$this->uri->segment(1).'/'.$this->uri->segment(2).'/details/'.encryptValue($row['id']).'" 
                              data-layout="button" data-size="medium"></div>
                             
                             </div>'.
                        '<div class="col-md-2 procurement_pde">'. $providername .'</div>'.


                        '<div class="col-md-1"><strong>'. custom_date_format('d M, Y', $row['date_signed']) .'</strong></div>'.
                        '<div class="col-md-2"> '. custom_date_format('d M, Y', $row['commencement_date']).' <br/> <b>['. custom_date_format('d M, Y', $row['completion_date']).']</b>';
                    if(!empty($row['actual_completion_date']))
                    {
                        echo "<br/><a  class='btn  btn-xs btn-primary' role='button' href='javascript:void(0);'  style='background:#ddd;color:#000; border:none; '> Final: ".custom_date_format('d M, Y',$row['actual_completion_date'])."</a>";
                    }
                    else
                    {}
                    // (!empty($row['actual_completion_date'])) ? $row['actual_completion_date'] : ''.
                    echo  '</div>'.

                        '<div class="col-md-2" style="font-family:Georgia; font-size:13px;">';
                    #number_format($row['id']).

                    $query = $this->db->query("select a.amount,b.title from  contract_prices a  inner join  currencies b on a.currency_id = b.id where a.contract_id =".$row['id']." ORDER BY a.dateadded DESC ")-> result_array();
                    #currency_id
                    if(!empty($query))
                    {
                        echo "<ul>";
                        foreach ($query as $key => $rowvalue) {
                            #code...
                            echo "<li>".number_format($rowvalue['amount'])."&nbsp; ".$rowvalue['title']."</li>";
                        }
                        echo "</ul>";
                    }
                    if(!empty($row['final_contract_value']))
                    {
                        echo "<br/><a  class='btn  btn-xs btn-primary' role='button' href='javascript:void(0);'  style='background:#ddd;color:#000; border:none; '> Final: ".number_format($row['final_contract_value'])."&nbsp; </a>";
                    }
                    else
                    {}

                    print '</div>'.
                        '<div class="col-md-1">';
                    if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
                    {
                        $status_str = '<span class="label label-success label-mini">Completed</span>';
                        $completion_str = '<a title="Click to view contract completion details" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']).'/v/'. encryptValue('view') .'"><i class="icon-ok"></i> View completion details</a>';
                    }
                    else
                    {
                        $status_str = '<span class="label label-warning label-mini">Awarded</span>';
                        $completion_str = '<a title="Click to enter contract completion details"" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']) .'"><i class="icon-ok"></i> Mark as complete</a>';
                    }

                    echo $status_str.'</div>'.
                        '</div>'.
                        '<hr>';
                }
                #pagination::
                print '<div class="pagination pagination-mini pagination-centered">'.
                    pagination($this->session->userdata('search_total_results'), $rows_per_page, $current_list_page, base_url().
                        "page/awarded_contracts/p/%d")
                    .'</div>';
            }
            else
            {
                print format_notice("ERROR: No contracts have been signed");
            }
            ?>
        </div>
    