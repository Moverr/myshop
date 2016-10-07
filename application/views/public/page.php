<?php
ob_start();

#**************************************************************************************
# All normal website pages that do not require login are directed from this controller
#**************************************************************************************

class Page extends CI_Controller {

    # Constructor
    function Page()
    {

        parent::__construct();

        $this->load->model('users_m','users');
        $this->load->model('sys_email','sysemail');
        #date_default_timezone_set(SYS_TIMEZONE);
        $this->load->model('Remoteapi_m');
        $this->load->model('Receipts_m');
        $this->load->model('procurement_plan_m');
        $this->load->model('procurement_plan_entry_m');
        $this->load->model('disposal_m','disposal');
        #$this->load->model('schedule_m');
        $this->load->model('contracts_m');
        $this->load->model('query_reader','query_reader');

        $this->load->model('usergroups_m');
        $this->load->model('role_m');
        $this->load->model('bid_invitation_m');
        $this->load->model('procurement_method_m');

    }


    # Default to home
    function index()
    {




        #Update Query List
        if(!empty($_GET['reason'] ))
        {
            $this->query_reader->load_queries_into_cache();
            #Go home
            exit("Query CACHE UPDATED ");
        }
        else if($_GET['info'])
        {
            phpinfo();
            exit();

        }else{}

        #Go home
        redirect('page/home');

    }

    #GET beb lots
    function get_beb_lots()
    {
        $urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);
        $data['results'] = $this->Receipts_m->fetch_lots_awarded_beb($data,$_POST);
        #print_r($data['results']);
        if(!empty($_POST['side']))
        {
            $data['side'] = $_POST['side'];
        }
        $data['page_title'] = 'View Best Evaluated BIdder Lots';
        $data['view_to_load'] = 'bids/manage_lots_beb';
        $data['view_data']['form_title'] = $data['page_title'];
        $this->load->view( $data['view_to_load'], $data);


    }



    #publish best evaluated bidder to the front end
    function best_evaluated_bidder()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data

        $data = assign_to_data($urldata);




       

        if(!empty($data['level']) && $data['level'] == 'export' )
        {
 
            $searchstring = '  AND procurement_plan_entries.isactive ="Y" AND procurement_plans.isactive="Y" AND bidinvitations.isactive="Y"   AND ( (bestevaluatedbidder.beb_expiry_date >= CURDATE()) || (bestevaluatedbidder.isreviewed != "N") ) ' ;
            $searchresult = $this->Receipts_m->fetchbeb($data,$searchstring);





        $objPHPExcel = new PHPExcel();

        #$this->load->model('source_funding_m');

        $xx = 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', ' Procuring/Disposing Entity ');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', '  Procurement Reference Number   ');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', '  Selected Provider ');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', '  Subject ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', '  Date  of Display ');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', '  Date  BEB Expires');

      
        $objPHPExcel->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("C1:D1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("E1:H1")->getFont()->setBold(true);
       


          $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
           $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
             $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
              $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
              $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
             


  $stack = array( );

  foreach ($searchresult['page_list'] as $key => $row) {

 
  $haslots = $row['haslots'];
  $bidd = $row['bidid'];

  $lot_count = $this->db->query("SELECT COUNT(*) as nums  FROM lots INNER JOIN received_lots    ON lots.id = received_lots.lotid INNER JOIN receipts ON receipts.receiptid = received_lots.receiptid  INNER JOIN bestevaluatedbidder ON receipts.receiptid = bestevaluatedbidder.pid INNER JOIN bidinvitations ON  bidinvitations.id = lots.bid_id  WHERE lots.bid_id = ".$row['bidid']."   AND    bidinvitations.haslots ='Y' AND receipts.beb='Y'  AND bestevaluatedbidder.ispublished='Y'   ")->result_array();

 if (in_array(  $bidd, $stack))
        continue;
 if($haslots == 'Y' && ( $lot_count[0]['nums'] > 0 ) )
        array_push( $stack, $bidd);



                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$xx,$row['pdename']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$xx,$row['procurement_ref_no'] );

/* Selected Provider */


                          /*  if($haslots == 'N')
                                    {


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
                                                        $lead = '[project Lead ]';
                                                        #break;
                                                    }
                                                    else{
                                                        $lead = '';

                                                    }

                                                    $provider  .= ",";
                                                    $provider  .=   strpos($vaue['providernames'] ,"0") !== false ? '' :  $lead.$vaue['providernames'];
                                                    $provider  .= " ";

                                                }else{
                                                    $provider  .=strpos($vaue['providernames'] ,"0") !== false ? '' : $vaue['providernames'];
                                                }
                                            }

                                            if(mysql_num_rows($rows) > 1){
                                                $provider .= "";
                                            }
                                            else{
                                                $provider = rtrim($provider,' ,');
                                            }

                                            if($x > 1)
                                                $label = '[Joint Venture]';
                                            $provider =  $provider.'&nbsp; '.$label
                                             
                                            $x  = 0 ;
                                            $label = '';
                                        }
                                        else{ 

                                          $provider = $row['providernames'];

                                      }
                                  }
                                   else
                                    {

                                        $provider =  ' Awarded  In Lots ';
                                    }   */




            /* End of Selection */

                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$xx,$provider );
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$xx,$row['subject_of_procurement'] );
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$xx, date('d M, Y', strtotime($row['date_of_display']) ));   
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$xx, date('d M, Y', strtotime($row['beb_expiry_date']) ));
               
               
                 
                 $xx ++;   
       
            }    
 
        $objPHPExcel->getActiveSheet()->setTitle('Procurement Plan Details ');
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $fileName = 'downloads/current_tenders_Export'.rand(1234,34566).'.xls';
        $objWriter->save($fileName);


        header('Location:'.base_url().$fileName);       
 
            
        }




        if(!empty($urldata['level']) && ($urldata['level'] == 'search'))
        {
            #paginates search egine
            $searchstring =  $this->session->userdata('searchengine3');
            # print_r($searchstring);

            $data['page_list'] = $this->Receipts_m->fetchbeb($data,$searchstring);

            #$data['page_list'] = $this->disposal->fetch_disposal_records($data,$searchstring);
            $data['level'] = 'search';

        }
        else
        {

            $searchstring = '  AND procurement_plan_entries.isactive ="Y" AND procurement_plans.isactive="Y" AND bidinvitations.isactive="Y"   AND ( (bestevaluatedbidder.beb_expiry_date >= CURDATE()) || (bestevaluatedbidder.isreviewed != "N") ) ' ;
            $data['page_list'] = $this->Receipts_m->fetchbeb($data,$searchstring);


            $this->db->order_by("title", "asc");
            $data['procurement_types'] = $this->db->get_where('procurement_types', array('isactive' => 'Y'))->result_array();

            $this->db->order_by("title", "asc");
            $data['procurement_methods'] = $this->db->get_where('procurement_methods', array('isactive' => 'Y'))->result_array();

            $this->db->order_by("pdename", "asc");
            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();
        }

        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'beb';
        $this->load->view('public/beb_v', $data);
    }

//Search Best Evaluated Bidders 
    function best_evaluated_bidder_search()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $searchstring = '';

        $procurement_type = mysql_real_escape_string($_POST['procurement_type']);
        if(!empty( $procurement_type))
        {
            $searchstring .= 'and procurement_plan_entries.procurement_type = "'.$procurement_type.'"';
        }

        $procurement_method = mysql_real_escape_string($_POST['procurement_method']);
        if(!empty( $procurement_method))
        {
            $searchstring .= 'and procurement_plan_entries.procurement_method= "%'.$procurement_method.'%" ';
        }

        $procurement_ref_no = mysql_real_escape_string($_POST['procurement_ref_no']);
        if(!empty( $procurement_ref_no))
        {
            $searchstring .= ' and  procurement_plan_entries.procurement_ref_no="'.$procurement_ref_no.'"  ';
        }

        $entity = mysql_real_escape_string($_POST['entity']);
        if(!empty($entity))
        {
            $searchstring .= 'and  b.pdeid = "'.$entity.'"   ';
        }

        $date_posted_from = mysql_real_escape_string($_POST['date_posted_from']);
        if(!empty($date_posted_from))
        {
            $searchstring .= 'and bestevaluatedbidder.dateadded >= "'.$date_posted_from.'" ';
        }

        $date_posted_to = mysql_real_escape_string($_POST['date_posted_to']);

        $searchstring .= '   AND procurement_plan_entries.isactive ="Y" AND procurement_plans.isactive="Y" AND bidinvitations.isactive="Y"  and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y"   order by bestevaluatedbidder.dateadded DESC';

        $this->db->order_by("title", "asc");
        $data['procurement_types'] = $this->db->get_where('procurement_types', array('isactive' => 'Y'))->result_array();

        $this->db->order_by("title", "asc");
        $data['procurement_methods'] = $this->db->get_where('procurement_methods', array('isactive' => 'Y'))->result_array();

        $this->db->order_by("pdename", "asc");
        $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();

        $data['page_list'] = $result = paginate_list($this, $data, 'fetchbebs', array('SEARCHSTRING' => $searchstring ),10);

        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'beb';
        $this->load->view('public/beb_v', $data);


        # print_r($_POST);

    }



    # The home page
    function home()
    {

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);


        

        if(!empty($data['level']) && $data['level'] == 'export' )
        {
 
             
             $search_str = '';
 
            $searchengine = $this->session->userdata('searchengines');
            if(!empty($searchengine))
              $search_str =   $searchengine;
 

           $searchresult = paginate_list($this, $data, 'active_ifb_details', array('orderby' => 'invitation_to_bid_date DESC', 'searchstring' => 'bidinvitations.isactive = "Y"  AND procurement_plans.isactive="Y"  AND procurement_plan_entries.isactive="Y"  AND    IF(bidinvitations.procurement_method_ifb > 0,bidinvitations.procurement_method_ifb  IN("1","2","9","11")  ,procurement_plan_entries.procurement_method  IN("1","2","9","11")  )  AND procurement_plans.isactive ="Y" AND bidinvitations.isapproved="Y" '.$search_str), 500);




        $objPHPExcel = new PHPExcel();

        #$this->load->model('source_funding_m');

        $x = 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', ' Procuring/Disposing Entity ');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', '  Subject of Procurement  ');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', '  Procurement Type ');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', '  Procurement Method ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', '  Deadline');

      
        $objPHPExcel->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("C1:D1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("E1")->getFont()->setBold(true);
       


          $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
           $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
             $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
              $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
             


 

  foreach ($searchresult['page_list'] as $key => $row) {

 
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['subject_of_procurement'] );
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,$row['procurement_type'] );
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x,$row['procurement_method'] );
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x, date('d M, Y', strtotime($row['bid_submission_deadline']) ));    
                
               
               
                 
                 $x ++;   
       
            }    
 
        $objPHPExcel->getActiveSheet()->setTitle('Procurement Plan Details ');
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $fileName = 'downloads/current_tenders_Export'.rand(1234,34566).'.xls';
        $objWriter->save($fileName);


        header('Location:'.base_url().$fileName);       
 
            
        }

 

         /*
            While Pagination : 
         */
        if(!empty($urldata['level']) && ($urldata['level'] == 'search'))
        {
            
            $search_str =  $this->session->userdata('searchengines');
          
             $data = paginate_list($this, $data, 'active_ifb_details', array('orderby' => 'invitation_to_bid_date DESC', 'searchstring' => 'bidinvitations.isactive = "Y"  AND procurement_plans.isactive="Y"  AND procurement_plan_entries.isactive="Y"  AND    IF(bidinvitations.procurement_method_ifb > 0,bidinvitations.procurement_method_ifb  IN("1","2","9","11")  ,procurement_plan_entries.procurement_method  IN("1","2","9","11")  )  AND procurement_plans.isactive ="Y" AND bidinvitations.isapproved="Y" '.$search_str), 10);


            
                 $data['level'] = 'search';

        }
        else
        {



            $this->db->order_by("title", "asc");
            $data['procurement_types'] = $this->db->get_where('procurement_types', array('isactive' => 'Y'))->result_array();

            $this->db->order_by("title", "asc");
            $data['procurement_methods'] = $this->db->get_where('procurement_methods', array('isactive' => 'Y'))->result_array();

            $this->db->order_by("pdename", "asc");
            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();



            #Search results
            if (!empty($_POST['search_btn'])) {
                $search_str = 'procurement_plan_entries.subject_of_procurement LIKE "%' . $_POST['subject_of_procurement'] . '%"  ';

                if (!empty($_POST['date_posted_from']) && !empty($_POST['date_posted_to']))
                    $search_str .= ' AND bid_invitation_details.invitation_to_bid_date >  "' .
                        custom_date_format('Y-m-d', $_POST['date_posted_from']) . '" - INTERVAL 1 DAY ' .
                        ' AND bid_invitation_details.invitation_to_bid_date <  "' .
                        custom_date_format('Y-m-d', $_POST['date_posted_to']) . '" + INTERVAL 1 DAY ';

                if (!empty($_POST['procurement_type']))
                    $search_str .= ' AND procurement_plan_entries.procurement_type =  "' . $_POST['procurement_type'] . '"';

                if (!empty($_POST['procurement_method']))
                    $search_str .= ' AND procurement_plan_entries.procurement_method =  "' . $_POST['procurement_method'] . '"';

                if (!empty($_POST['entity']))
                    $search_str .= ' AND procurement_plans.pde_id =  "' . $_POST['entity'] . '"';



                $data = paginate_list($this, $data, 'active_ifb_details', array('orderby' => 'invitation_to_bid_date DESC', 'searchstring' => $search_str .
                    ' AND bidinvitations.isactive = "Y" AND procurement_plans.isactive="Y"  AND procurement_plan_entries.isactive="Y"  AND procurement_plans.isactive="Y" AND bidinvitations.isapproved="Y"  AND   IF(bidinvitations.procurement_method_ifb > 0,bidinvitations.procurement_method_ifb  IN("1","2","9","11")  ,procurement_plan_entries.procurement_method  IN("1","2","9","11")  )   AND bidinvitations.bid_submission_deadline>NOW()'),10);

                $data['formdata'] = $_POST;

            }

            #normal page load
            else
            {

                #get available bids
                $data = paginate_list($this, $data, 'active_ifb_details', array('orderby' => 'invitation_to_bid_date DESC', 'searchstring' => 'bidinvitations.isactive = "Y"  AND procurement_plans.isactive="Y"  AND procurement_plan_entries.isactive="Y"  AND    IF(bidinvitations.procurement_method_ifb > 0,bidinvitations.procurement_method_ifb  IN("1","2","9","11")  ,procurement_plan_entries.procurement_method  IN("1","2","9","11")  )  AND procurement_plans.isactive ="Y" AND bidinvitations.isapproved="Y" '), 10);
            }
        }



        $this->load->view('public/home_v', $data);
    }

    #login page
    function login()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $this->load->view('login_v', $data);
    }

    #Function to process the contact us page
    function process_contactus()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        if($this->input->post('sendmessage'))
        {
            $required_fields = array('emailaddress*EMAILFORMAT', 'name');
            #$_POST['attachmenturl'] = !empty($_FILES['attachmenturl']['name'])? $this->sysfile->local_file_upload($_FILES['attachmenturl'], 'Upload_'.strtotime('now'), 'attachments', 'filename'): '';

            $_POST = clean_form_data($_POST);
            $validation_results = validate_form('', $_POST, $required_fields);

            #Only proceed if the validation for required fields passes
            #if($validation_results['bool'] && is_valid_captcha($this, $_POST['captcha']))
            if($validation_results['bool'])
            {
                #Send the contact message to the administrator and
                $send_result = $this->sysemail->email_form_data(array('fromemail'=>NOREPLY_EMAIL),
                    get_confirmation_messages($this, $_POST, 'website_feedback'));

                if($send_result)
                {
                    $data['msg'] = "Your message has been sent. Thank you for your feedback.";
                    $data['successful'] = 'Y';
                }
                else
                {
                    $data['msg'] = "ERROR: Your message could not be sent. Please contact us using our phone line.";
                }
            }

            if(!$validation_results['bool'])
            {
                $data['msg'] = "WARNING: The highlighted fields are required.";
            }

            $data['requiredfields'] = array_merge($validation_results['requiredfields'], array('captcha'));
            $data['formdata'] = $_POST;

        }

        $data['pagedata'] = $this->Query_reader->get_row_as_array('get_page_by_section', array('section'=>'Support', 'subsection'=>'Contact Us'));
        if(count($data['pagedata']) > 0)
        {
            $data['pagedata']['details'] = str_replace("&amp;gt;", "&gt;", str_replace("&amp;lt;", "&lt;", $data['pagedata']['details']));

            $data['pagedata']['parsedtext'] = $this->wiki_manager->parse_text_to_HTML(htmlspecialchars_decode($data['pagedata']['details'], ENT_QUOTES));
            $result = $this->db->query($this->Query_reader->get_query_by_code('get_subsections_by_section', array('section'=>$data['pagedata']['section'])));
            $data['subsections'] = $result->result_array();
        }


        $data = add_msg_if_any($this, $data);
        $this->load->view('page/contact_us_view', $data);
    }

    function register()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = paginate_list($this, $data, 'get_page_list',  array('searchstring'=>''));
        $data = add_msg_if_any($this, $data);
        $this->load->view('page/register_view', $data);
    }


    #Function to create the catpcha word
    function create_captcha()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $vals = array(
            'img_path'   => './images/captcha/',
            'img_url'    => IMAGE_URL.'captcha/',
            'img_width'  => 150,
            'img_height' => 50
        );

        $cap = create_captcha($vals);

        $data = array(
            'captcha_id'    => '',
            'captcha_time'  => $cap['time'],
            'ip_address'    => $this->input->ip_address(),
            'word'          => $cap['word']
        );


        $this->db->query($this->Query_reader->get_query_by_code('insert_captcha_record', array('captcha_time'=>$data['captcha_time'], 'ip_address'=>$data['ip_address'], 'word'=>$data['word'])));

        $data['capimage'] = $cap['image'];
        $data['area'] = 'catpcha_image_view';

        $data = add_msg_if_any($this, $data);
        $this->load->view('incl/addons', $data);
    }



    #Show this when javascript is not enabled
    function javascript_not_enabled()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        format_notication("WARNING: Javascript not enabled.<BR><BR><a href='".base_url()."'>&lsaquo;&lsaquo; GO BACK TO HOME</a>");
    }


    #Function to show the contact us page
    function contact_us()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $this->load->view('page/contact_us_view', $data);
    }



    #Function to show the about us page
    function about_us()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $this->load->view('page/about_view', $data);
    }

    #Function to show the privacy policy
    function privacy_policy()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);
        $this->load->view('page/privacy_policy_view', $data);
    }

    #Function to show the terms and conditions
    function terms_and_conditions()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data = add_msg_if_any($this, $data);

        $this->load->view('incl/terms_and_conditions_view', $data);
    }

    //function to download procurement plans into exel
    function procurement_plan_to_exel()
    {
        $this->db->order_by("pdename", "asc");
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        $current_financial_year = currentyear.'-'.endyear;
        $data = assign_to_data($urldata);


        $plan_id = decryptValue($data['page']);
        $limit = 600;
        $where = array(
            'procurement_plan_id' => $plan_id ,
            'isactive' => 'Y'
        );
        #$data['all_entries'] = $this->procurement_plan_entry_m->get_where($where);
        $all_entries = $this->db->query(" SELECT procurement_plan_entries.*,fs.title
                    AS fundingsourcea,pm.title p_method,pt.title AS p_title FROM procurement_plan_entries
                    LEFT OUTER JOIN procurement_types pt ON procurement_plan_entries.procurement_type = pt.id
                    LEFT OUTER JOIN procurement_methods pm ON procurement_plan_entries.procurement_method  = pm.id
                    LEFT OUTER JOIN funding_sources fs ON   procurement_plan_entries.funding_source = fs.id
                    WHERE procurement_plan_entries.procurement_plan_id = '".$plan_id."'   AND procurement_plan_entries.isactive = 'Y' ")->result_array();
        #$this->procurement_plan_entry_m->get_paginated_by_criteria($num = $limit, 0, $where);


        $objPHPExcel = new PHPExcel();

        #$this->load->model('source_funding_m');

        $x = 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'QUANTITY');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'SUBJECT OF PROCUREMENT ');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'PROCUREMENT TYPE');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'PROCUREMENT METHOD');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'SOURCE OF FUNDS ');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'ESTIMATED AMOUNT ');




        foreach($all_entries  as $key => $entry)
        {


            $procurement_type = $entry['p_title'];
            $procurement_method = $entry['p_method'];
            $source_of_funding = $entry['fundingsourcea'];

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,number_format($entry['quantity']));
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$entry['subject_of_procurement'] );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x, $procurement_type );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x, $procurement_method);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x, $source_of_funding);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x, number_format($entry['estimated_amount']));


            $x ++;
        }


        $objPHPExcel->getActiveSheet()->setTitle('Procurement Plan Details ');

        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);

        $fileName = 'downloads/procurement_plan'.rand(1234,34566).'.xls';
        $objWriter->save($fileName);


        header('Location:'.base_url().$fileName);





    }

    //display active procurement plans
    function procurement_plans()
    {
        $this->db->order_by("pdename", "asc");
        $urldata = $this->uri->uri_to_assoc(3, array('m', 's'));
        $current_financial_year = currentyear.'-'.endyear;

        # Pick all assigned data
        $data = assign_to_data($urldata);
        #print_r($urldata); exit();
        $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();
        //switch by parameter passed
        if(!empty($urldata['level']) && ($urldata['level'] == 'search'))
        {
            #paginates search egine
            $searchstring =  $this->session->userdata('searchengine');
            #  print_r($searchstring);

            $data['searchresult'] = paginate_list($this, $data,'procurement_search_list', array('SEARCHSTRING' => $searchstring));

            $data['current_menu'] = 'procurement_plans';
            $data['view_to_load'] = 'public/active_plans/active_plans_v';
            $this->load->view('public/home_v', $data);
        }
        else
        {

            switch ($this->uri->segment(3)) {
                //if its details
                case "details":

                    //verify that plan exists
                    $plan_info = get_procurement_plan_info(decryptValue($this->uri->segment(4)), '');
                    if ($plan_info) {
                        //print_array($plan_info);
                        //show active plans
                        $data['pagetitle'] = get_procurement_plan_entry_info(decryptValue($this->uri->segment(3)), 'title');
                        $data['current_menu'] = 'active_plans';
                        $data['view_to_load'] = 'public/active_plans/plan_details_v';
                        $data['plan_id'] = decryptValue($this->uri->segment(4));

                        $limit = NUM_OF_ROWS_PER_PAGE;
                        $where = array(
                            'procurement_plan_id' => decryptValue($this->uri->segment(4)),
                            'isactive' => 'Y'
                        );
                        $data['all_entries'] = $this->procurement_plan_entry_m->get_where($where);
                        $data['all_entries_paginated'] = $this->procurement_plan_entry_m->get_paginated_by_criteria($num = $limit, $this->uri->segment(5), $where);
                        $this->load->library('pagination');
                        //pagination configs
                        $config = array
                        (
                            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/' . $this->uri->segment(4) . '/',//contigure page base_url
                            'total_rows' => count($data['all_entries']),
                            'per_page' => $limit,
                            'num_links' => $limit,
                            'use_page_numbers' => TRUE,
                            'full_tag_open' => '<div class="btn-group">',
                            'full_tag_close' => '</div>',
                            'anchor_class' => 'class="btn" ',
                            'cur_tag_open' => '<div class="btn">',
                            'cur_tag_close' => '</div>',
                            'uri_segment' => '5'

                        );
                        //initialise pagination
                        $this->pagination->initialize($config);

                        //add to data array
                        $data['pages'] = $this->pagination->create_links();
                        //load view

                        $data['current_menu'] = 'procurement_plans';
                        //load view
                        $this->load->view('public/home_v', $data);
                    } else {
                        show_404();
                    }
                    break;

                //if its to see entry details
                case "entry_details":
                    //show active plans
                    $data['pagetitle'] = get_procurement_plan_entry_info(decryptValue($this->uri->segment(4)), 'title');
                    $data['current_menu'] = 'active_plans';
                    $data['view_to_load'] = 'public/active_plans/entry_details_v';
                    $data['entry_id'] = decryptValue($this->uri->segment(4));
                    //load view
                    $data['current_menu'] = 'procurement_plans';
                    $this->load->view('public/home_v', $data);
                    break;
                default:
                    //show active plans
                    $data['pagetitle'] = 'Annual Procurement Plans';
                    $data['current_menu'] = 'procurement_plans';

                    $where = array
                    (
                        'isactive' => 'y'
                    );

                    #show plans for the current financial yea
                    $where['financial_year'] = trim($current_financial_year);
                    $data['current_financial_year'] = trim($current_financial_year);

                    $data['all_plans'] = $this->procurement_plan_m->get_where($where);
                    $data['all_plans_paginated'] = $this->procurement_plan_m->get_paginated_by_criteria($num = NUM_OF_ROWS_PER_PAGE, $this->uri->segment(3), $where);
                    //pagination configs
                    $config = array
                    (
                        'base_url' => base_url() . $this->uri->segment(1) . '/procurement_plans',//contigure page base_url
                        'total_rows' => count($data['all_plans']),
                        'per_page' => NUM_OF_ROWS_PER_PAGE,

                        'full_tag_open' => '<div class="btn-group">',
                        'full_tag_close' => '</div>',
                        'anchor_class' => 'class="btn" ',
                        'cur_tag_open' => '<div class="btn">',
                        'cur_tag_close' => '</div>',
                        'uri_segment' => '3'

                    );
                    //initialise pagination
                    $this->pagination->initialize($config);

                    //add to data array
                    $data['pages'] = $this->pagination->create_links();
                    //load view

                    $data['view_to_load'] = 'public/active_plans/active_plans_v';
                    //load view

                    $this->load->view('public/home_v', $data);

            }
        }


    }

    #Function to show a selected bid's details
    function bid_details()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p', 'i'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        if(!empty($data['i']))
        {


            $app_select_str = ' procurement_plan_entries.isactive="Y" ';


            $bid_id = decryptValue($data['i']);
            #   print_r($bid_id);

            $query = $data['formdata'] = $this->Query_reader->get_row_as_array('view_bid_invitations_ifb', array('table'=>'bidinvitations', 'limittext'=>'', 'orderby'=>'BI.id', 'searchstring'=>' BI.id="'. $bid_id .'" '));
            /// AND isactive="Y"

            #   exit($this->db->last_query());
            #   print_r($query);

            # print_r($data);

            #exit();
            #get procurement plan details
            if(!empty($data['formdata']['procurement_ref_no']))
            {
                #exit($this->db->last_query());
                $data['formdata']['procurement_details'] = $this->Query_reader->get_row_as_array('procurement_plan_details_ifb', array('searchstring'=>$app_select_str . ' AND procurement_plan_entries.id="'. $data['formdata']['procurement_id'] .'"', 'limittext'=>'', 'orderby'=>' procurement_plan_entries.dateadded ' ));
            }




        }


        # get procurement method by title
        $my_data=array(
            'title'=>$data['formdata']['procurement_method']
        );
        $procurement_method=$this->procurement_method_m->get_where($my_data);




        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = $procurement_method[0]['id']==11?'bids/view_EOI_bid_invitation':'bids/view_bid_invitation';

        $this->load->view('public/home_v', $data);
    }


    #Function to show a selected bid's list of addenda
    function addenda_list()
    {
        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p', 'a'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #normal page load
        if(!empty($data['a']))
        {
            $bid_id = decryptValue($data['a']);

            #bid_details
            $data['bid_details'] = $this->Query_reader->get_row_as_array('bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y" AND bidinvitations.id="'. $bid_id .'"', 'limittext'=>''));

            #get available bids
            $data = paginate_list($this, $data, 'search_addenda', array('orderby' => 'A.id', 'searchstring' => ' AND A.bidid="'. $bid_id .'" AND BI.isactive="Y"'));
            #exit($this->db->last_query());
        }

        $data['current_menu'] = 'view_bid_invitations';
        $data['view_to_load'] = 'public/includes/addenda_list';

        $this->load->view('public/home_v', $data);
    }


    #Function to show awarded contracts
    function awarded_contracts()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data

        $data = assign_to_data($urldata);
        if(!empty($urldata['level']) && ($urldata['level'] == 'search'))
        {

            #paginates search egine
            $searchstring =  $this->session->userdata('searchengineA');
            # print_r($searchstring);
            $data = paginate_list($this, $data, 'get_published_contracts', array('orderby' => 'date_signed DESC', 'searchstring' => $searchstring), 10);

            // $data = paginate_list($this, $data, 'get_published_contracts', array('orderby' => 'date_signed DESC', 'searchstring' => $searchstring), 10);
            #$data['page_list'] = $this->disposal->fetch_disposal_records($data,$searchstring);
            $data['level'] = 'search';

            $data['view_to_load'] = 'public/includes/awarded_contracts';
            $data['current_menu'] = 'awarded_contracts';
            $this->load->view('public/home_v', $data);

        }
        else
        {
            $this->db->order_by("pdename", "asc");
            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();
            # Get the passed details into the url data array if any
            $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
            $searchme = 'AND PPE.isactive="Y" AND C.isactive ="Y" AND PP.isactive="Y" AND  BI.isactive="Y" ';

            # Pick all assigned data
            $data = assign_to_data($urldata);

            #Show contract details
            switch ($this->uri->segment(3)) {
                //if its details
                case "details":
                    //verify that contract exists
                    $contract_info = get_contract_detail_info(decryptValue($this->uri->segment(4)), '');
                    if ($contract_info) {

                        $contractid = decryptValue($this->uri->segment(4));
                        $data['details'] = $this->Query_reader->get_row_as_array('get_published_contracts', array('searchstring' => ' AND C.id = "'. $contractid .'"', 'limittext'=>'', 'orderby'=>'date_signed'));
                        $data['current_menu'] = 'awarded_contracts';
                        $data['view_to_load'] = 'public/includes/contract_details_v';
                        $this->load->view('public/home_v', $data);
                    }
                    break;

                default:
                    #get available contracts
                    $data = paginate_list($this, $data, 'get_published_contracts', array('orderby' => 'date_signed DESC', 'searchstring' => $searchme ), 10);

                    $data['view_to_load'] = 'public/includes/awarded_contracts';
                    $data['current_menu'] = 'awarded_contracts';
                    $this->load->view('public/home_v', $data);
            }
        }
    }


    #publish best evaluated bidder to the front end
    function suspended_providers()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        //  $this->load->model('Remoteapi_m');

        $data['suspendedlist'] = $this->Remoteapi_m->suspended_providers(0, 10000);
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'suspended_providers';
        $this->load->view('public/suspended_providers_v', $data);

    }
    #page
    function beb_notice()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $post = $_POST;

        $data['formdata'] = $_POST;

        if(!empty($_POST['haslots']) && $_POST['haslots'] == 'Y')
        {
            $data['haslots'] = $_POST['haslots'];
            $data['bidid'] = $_POST['bidid'];
            $data['beb'] = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' and bestevaluatedbidder.bidid = bidinvitations.id and bidinvitations.id ='.mysql_real_escape_string($post['bidid'] )),10);

        }
        else
        {

            $data['haslots'] = !empty($_POST['haslots']) ? $_POST['haslots'] : '' ;
            if(!empty($post['framework']) && $post['framework'] == 'Y')
            {
                $data['beb'] = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' and bestevaluatedbidder.bidid = bidinvitations.id and bidinvitations.id ='.mysql_real_escape_string($post['bidid'] )),10);
            }
            else
            {
                $data['beb'] = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => ' and bestevaluatedbidder.bidid = bidinvitations.id and receipts.receiptid ='.mysql_real_escape_string($post['receiptid'] )),10);
            }

        }
        $this->load->view('public/bebnotice', $data);
    }






    // View Disposal Plans 
    function disposal_plans(){

        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        if(!empty($urldata['level']) && ($urldata['level'] == 'search'))
        {
            #paginates search egine
            $searchstring =  $this->session->userdata('searchengine');
            $data['page_list'] = $this->disposal->fetch_disposal_records($data,$searchstring);
            $data['leve'] = 'search';

        }
        else
        {
            $this->db->order_by("title", "asc");
            $data['procurement_types'] = $this->db->get_where('procurement_types', array('isactive' => 'Y'))->result_array();

            $this->db->order_by("title", "asc");
            $data['procurement_methods'] = $this->db->get_where('procurement_methods', array('isactive' => 'Y'))->result_array();

            $this->db->order_by("pdename", "asc");
            $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();


            if (!empty($data['details'])) {
                $disposalplan = base64_decode($data['details']);
                $searchstring = ' 1 =1 and disposal_plans.id = ' . $disposalplan . '';
                $data['details'];
                $data['page_list'] = $this->disposal->fetch_disposal_records($data,$searchstring);

            } else {
                $searchstring = ' 1 =1 ';
                $current_financial_year = currentyear.'-'.endyear;
                $searchstring .= 'AND  disposal_plans.isactive="Y" AND financial_year like "%' . $current_financial_year . '%"';
                $data['page_list'] =   paginate_list($this, $data, 'fetched_disposal_plans', array('searchstring'=>$searchstring),10);

                # exit($this->db->last_query());
            }

        }
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';
        $this->load->view('public/disposal', $data);

    }



    // View Disposal Notices 
    function disposal_notices()
    {

        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $searchstring .=" 1 = 1 AND disposal_bid_invitation.isactive ='Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' AND disposal_record.method_of_disposal in (1,8,9) ";
        $data['page_list'] =   paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring),10);

        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';
        $this->load->view('public/disposal_notice', $data);

    }
    
    
    function search_disposal_notices()
    {
        
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $searchstring .=" 1 = 1 AND disposal_bid_invitation.isactive ='Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' AND disposal_record.method_of_disposal in (1,8,9) ";


         if(!empty($_POST['simple_search']))
            {
                
               $searchstring .= ' AND  ( disposal_record.method_of_disposal IN (  SELECT disposal_method.id  FROM disposal_method  WHERE  disposal_method.method LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"  )  ';

                $searchstring .= ' OR   pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                     $searchstring .= ' OR  disposal_record.subject_of_disposal  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                $searchstring .= ' OR   disposal_bid_invitation.disposal_ref_no  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                $searchstring .= ' OR   disposal_record.disposal_serial_no  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';
                     
                    $searchstring .= ' OR  disposal_plans.financial_year  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ) ';      



            }
            else
            {

                            
                              if (!empty($_POST['disposing_entity']))
                              {
                                   $searchstring .="   AND  disposal_plans.pde_id =  ".mysql_real_escape_string($_POST['disposing_entity']).""; 
                                  
                              }
                                if (!empty($_POST['disposing_method']))
                              {
                                    $searchstring .="   AND  disposal_record.method_of_disposal =  ".mysql_real_escape_string($_POST['disposing_method']).""; 
                                
                              }
                              
                              
                              if (!empty($_POST['subjectof_disposal']))
                              {
                                  $subjectof_disposal = mysql_real_escape_string($_POST['subjectof_disposal']);
                                  $subjectof_disposal = trim($subjectof_disposal);
                                  
                                    $searchstring .="   AND  disposal_record.subject_of_disposal  like '%".$subjectof_disposal."%' "; 
                                
                              }
                              
                                if (!empty($_POST['financial_year']))
                              {
                                    $searchstring .="   AND  disposal_plans.financial_year like '%".mysql_real_escape_string(trim($_POST['financial_year']))."%'"; 
                                
                              }
             }
          
          
          
        $data['page_list'] =   paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring),10);
        
        # print_r($_POST);
        #exit($this->db->last_query());

        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';
        $this->load->view('public/search_disposal_notice', $data);
        
    }
    
    
   
    // View Published LBAs , 
    function published_lbas(){

        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        $searchstring =" 1 = 1 AND disposal_bid_invitation.isactive ='Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' AND disposal_record.method_of_disposal in (1,8,9) ";
        $data['page_list'] =  paginate_list($this, $data, 'view_disposal_bebs',  array('SEARCHSTRING' => ' '.$searchstring.'' ),10);

         
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';

        $this->load->view('public/disposal_lba', $data);


    }
    
    //Search Published LBAs 
    function search_published_lbas(){
        
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'i'));
        $data = assign_to_data($urldata);
        $data = add_msg_if_any($this, $data);
        $data = handle_redirected_msgs($this, $data);

        $searchstring =" 1 = 1 AND disposal_bid_invitation.isactive ='Y' AND disposal_record.isactive ='Y'  AND disposal_plans.isactive ='Y' AND disposal_record.method_of_disposal in (1,8,9) ";
       
       

          if(!empty($_POST['simple_search']))
            {
                
               $searchstring .= ' AND  ( providers.providernames  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"   ';

                $searchstring .= ' OR   pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                     $searchstring .= ' OR  disposal_record.subject_of_disposal  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                $searchstring .= ' OR   disposal_bid_invitation.disposal_ref_no  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                $searchstring .= ' OR   disposal_record.disposal_serial_no  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';
                     
                    $searchstring .= ' OR  disposal_plans.financial_year  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ) ';      



            }
            else
            {

                    if (!empty($_POST['disposing_entity']))
                      {
                           $searchstring .="   AND  disposal_plans.pde_id =  ".mysql_real_escape_string($_POST['disposing_entity']).""; 
                          
                      }
                        if (!empty($_POST['disposing_method']))
                      {
                            $searchstring .="   AND  disposal_record.method_of_disposal =  ".mysql_real_escape_string($_POST['disposing_method']).""; 
                        
                      }
                      
                      
                      if (!empty($_POST['subjectof_disposal']))
                      {
                          $subjectof_disposal = mysql_real_escape_string($_POST['subjectof_disposal']);
                          $subjectof_disposal = trim($subjectof_disposal);
                          
                            $searchstring .="   AND  disposal_record.subject_of_disposal  like '%".$subjectof_disposal."%' "; 
                        
                      }
                      
                        if (!empty($_POST['financial_year']))
                      {
                            $searchstring .="   AND  disposal_plans.financial_year like '%".mysql_real_escape_string(trim($_POST['financial_year']))."%'"; 
                        
                      }
            }
          
          
        $data['page_list'] =  paginate_list($this, $data, 'view_disposal_bebs',  array('SEARCHSTRING' => ' '.$searchstring.'' ),10);

    #   print_r($_POST);
        #exit($this->db->last_query());
        
         
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';

        $this->load->view('public/search_published_lbas', $data);

        
    }


    #view Disposal Notice
    function view_disposal_notice()
    {

        $urldata = $this->uri->uri_to_assoc(3, array('m'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        $dipsosal_id = decryptValue($data['i']);

        # print_r($dipsosal_id);
        #  exit();

        $searchstring = "   1=1  AND disposal_bid_invitation.id='".$dipsosal_id."'  AND disposal_record.isactive ='Y' AND disposal_record.isactive='Y' AND pdes.isactive='Y' ";

 

        #$searchstring = "   1=1 AND DR.isactive ='Y' AND DP.isactive='Y' AND P.isactive='Y' AND  DR.id='".$dipsosal_id."'  ORDER BY  DR.dateadded DESC ";
        $data['page_list'] =   paginate_list($this, $data, 'view_disposal_bid_invitations', array('searchstring'=>$searchstring),10);


        #exit("notice");
        #print_r($data);
        # exit();
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';
        $data['view_to_load'] = 'notice';
        $this->load->view('public/disposal_notice', $data);
    }




    function suspendedproviders_search()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $organisation = $_POST['organisation'];
        #  print_r($organisation);
        #  exit();
        //  $this->load->model('Remoteapi_m');
        $searchstring = "and if(a.orgid>0,b.orgname like '%".mysql_real_escape_string($organisation)."%',a.orgid like '%".mysql_real_escape_string($organisation)."%') ";

        $data['suspendedlist'] = $this->Remoteapi_m->suspended_providers2($searchstring);
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'suspended_providers';
        $this->load->view('providers/susadons', $data);
    }

    function procurement_plans_search()
    {
        $this->db->order_by("pdename", "asc");
        $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();
        //switch by parameter passed
        switch ($this->uri->segment(3)) {
            //if its details
            case "details":
                //verify that plan exists
                $plan_info = get_procurement_plan_info(decryptValue($this->uri->segment(4)), '');
                if ($plan_info) {
                    //print_array($plan_info);
                    //show active plans
                    $data['pagetitle'] = get_procurement_plan_info(decryptValue($this->uri->segment(4)), 'title');
                    $data['current_menu'] = 'active_plans';
                    $data['view_to_load'] = 'public/active_plans/plan_details_v';
                    $data['plan_id'] = decryptValue($this->uri->segment(4));




                    $limit = NUM_OF_ROWS_PER_PAGE;
                    $where = array(
                        'procurement_plan_id' => decryptValue($this->uri->segment(4)),
                        'isactive' => 'Y'
                    );
                    $data['all_entries'] = $this->procurement_plan_entry_m->get_where($where);
                    $data['all_entries_paginated'] = $this->procurement_plan_entry_m->get_paginated_by_criteria($num = $limit, $this->uri->segment(5), $where);
                    $this->load->library('pagination');
                    //pagination configs
                    $config = array
                    (
                        'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/' . $this->uri->segment(4) . '/',//contigure page base_url
                        'total_rows' => count($data['all_entries']),
                        'per_page' => $limit,
                        'num_links' => $limit,
                        'use_page_numbers' => TRUE,
                        'full_tag_open' => '<div class="btn-group">',
                        'full_tag_close' => '</div>',
                        'anchor_class' => 'class="btn" ',
                        'cur_tag_open' => '<div class="btn">',
                        'cur_tag_close' => '</div>',
                        'uri_segment' => '5'

                    );
                    //initialise pagination
                    $this->pagination->initialize($config);

                    //add to data array
                    $data['pages'] = $this->pagination->create_links();
                    //load view


                    //load view
                    $this->load->view('public/home_v', $data);
                } else {
                    show_404();
                }

                break;

            //if its to see entry details
            case "entry_details":
                //show active plans
                $data['pagetitle'] = get_procurement_plan_entry_info(decryptValue($this->uri->segment(4)), 'title');
                $data['current_menu'] = 'active_plans';
                $data['view_to_load'] = 'public/active_plans/entry_details_v';
                $data['entry_id'] = decryptValue($this->uri->segment(4));
                //load view
                $this->load->view('public/home_v', $data);
                break;
            default:

                $financial_year = mysql_real_escape_string($_POST['procurement_method']);
                $entity = mysql_real_escape_string($_POST['entity']);
                //show active plans
                $data['pagetitle'] = 'Annual Procurement Plans';
                $data['current_menu'] = 'procurement_plans';

                $where = array
                (
                    'isactive' => 'y',
                    'financial_year'=>'like "%'.$financial_year.'%"'
                );

                #show plans for the current financial year
                $where['financial_year'] = ((date('m')>5)? date('Y') . '-' . (date('Y') + 1) : (date('Y') - 1) . '-' . date('Y'));

                $data['all_plans'] = $this->procurement_plan_m->get_where($where);
                $data['all_plans_paginated'] = $this->procurement_plan_m->get_paginated_by_criteria($num = NUM_OF_ROWS_PER_PAGE, $this->uri->segment(3), $where);
                //pagination configs
                $config = array
                (
                    'base_url' => base_url() . $this->uri->segment(1) . '/page',//contigure page base_url
                    'total_rows' => count($data['all_plans']),
                    'per_page' => NUM_OF_ROWS_PER_PAGE,
                    'num_links' => '3',
                    'use_page_numbers' => TRUE,
                    'full_tag_open' => '<div class="btn-group">',
                    'full_tag_close' => '</div>',
                    'anchor_class' => 'class="btn" ',
                    'cur_tag_open' => '<div class="btn">',
                    'cur_tag_close' => '</div>',
                    'uri_segment' => '3'

                );
                //initialise pagination
                $this->pagination->initialize($config);

                //add to data array
                $data['pages'] = $this->pagination->create_links();
                //load view

                $data['view_to_load'] = 'public/active_plans/active_plans_v';
                //load view
                $this->load->view('public/home_v', $data);
        }

    }

    function verifybeb()
    {
        $data['view_to_load'] = 'public/includes/verifybeb_v';
        $this->load->view('public/home_v', $data);

    }

    function searchbeb()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        #print_r( $this->uri->segment(3));
        $level = $this->uri->segment(3);
        switch ($level) {
            case 'verify':
                # code...
                $post = $_POST;
                $serialno = mysql_real_escape_string($post['serialno']);
                #$result = paginate_list($this, $data, 'fetchbebs', array('SEARCHSTRING' => ' and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y" order by bestevaluatedbidder.dateadded DESC'),10);
                $query = $this->Query_reader->get_query_by_code('fetchbebs', array('SEARCHSTRING' => 'and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y"  and  bestevaluatedbidder.seerialnumber like "' . $serialno . '%" order by bestevaluatedbidder.dateadded DESC', 'limittext' => '', 'orderby' => ''));
                $result = $this->db->query($query)->result_array();
                #print_r($serialno);
                if (!empty($result)) {
                    $post['receiptid'] = $result[0]['receiptid'];
                    #$post = $_POST;
                    #print_r($post['receiptid']); exit();
                    $data['beb'] = paginate_list($this, $data, 'view_bebs', array('SEARCHSTRING' => ' and bestevaluatedbidder.bidid = bidinvitations.id and receipts.receiptid =' . $post['receiptid']), 10);
                    $this->load->view('public/bebnotice', $data);
                } else {
                    print_r("0");
                }
                #print_r($query); exit();

                #   $query = $this->db->query()->result_array;

                break;

            default:
                # code...
                break;
        }
    }


    function generate_pdf(){
        $where = array(
            'procurement_plan_id' => decryptValue('MjQ='),
            'isactive' => 'Y'
        );
        $data['all_entries'] = $this->procurement_plan_entry_m->get_where($where);
        $this->load->view('pdfreport',$data);
    }

    function scheduler(){

        $this->schedule_m->add_user();

    }


    function search_procurement()
    {

        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

       
        if(!empty($data['level']) && $data['level'] == 'export' )
        {
             

            $searchengine = $this->session->userdata('searchengine');

            $searchresult = paginate_list($this, $data,'procurement_search_list', array('SEARCHSTRING' => $searchengine),500);

          




        $objPHPExcel = new PHPExcel();

        #$this->load->model('source_funding_m');

        $x = 2;

        $objPHPExcel->getActiveSheet()->SetCellValue('A1', '  Procuring/Disposing Entity');
        $objPHPExcel->getActiveSheet()->SetCellValue('B1', '  Financial Year  ');
        $objPHPExcel->getActiveSheet()->SetCellValue('C1', '  Quantity ');
        $objPHPExcel->getActiveSheet()->SetCellValue('D1', '  Subject of Procurement ');
        $objPHPExcel->getActiveSheet()->SetCellValue('E1', '  Procurement Type ');
        $objPHPExcel->getActiveSheet()->SetCellValue('F1', '  Procurement Method  ');
        $objPHPExcel->getActiveSheet()->SetCellValue('G1', '  Source of Funds');
        $objPHPExcel->getActiveSheet()->SetCellValue('H1', '  Estimated Cost ');
      
        $objPHPExcel->getActiveSheet()->getStyle("A1:B1:C1:D1:E1:F1:G1:H1")->getFont()->setBold(true);
         $objPHPExcel->getActiveSheet()->getStyle("C1:D1:E1:F1:G1:H1")->getFont()->setBold(true);
         $objPHPExcel->getActiveSheet()->getStyle("E1:F1:G1:H1")->getFont()->setBold(true);
          $objPHPExcel->getActiveSheet()->getStyle("G1:H1")->getFont()->setBold(true);



          $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
           $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
             $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
              $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
               $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                 $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);



 

  foreach ($searchresult['page_list'] as $key => $row) {

    $objPHPExcel->getActiveSheet()->SetCellValue('A'.$x,$row['pdename']);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$x,$row['financial_year'] );
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$x,number_format($row['quantity']) );
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$x, $row['subject_of_procurement'] );
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$x, $row['procurementtype'] );
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$x, $row['procurementmethod'] );

            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$x, $row['funding_source']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$x, number_format($row['estimated_amount']).' '.$row['currency'] ); 
             
             $x ++;   
       
            }
 
        $objPHPExcel->getActiveSheet()->setTitle('Procurement Plan Details ');

        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);

        $fileName = 'downloads/procurement_plan_search'.rand(1234,34566).'.xls';
        $objWriter->save($fileName);


        header('Location:'.base_url().$fileName);      
 
            
        }
       

        if(!empty($_POST))
        {

            $searchstring = '1 = 1';

            if(!empty($_POST['simple_search']))
            {
               $searchstring .= ' AND  ( procurement_plan_entries.subject_of_procurement LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"  ';

               $searchstring .= ' OR   pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ';


                 $searchstring .= ' OR   procurement_methods.title LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ';



                 $searchstring .= ' OR  procurement_types.title LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"   )   ';

     
                 



            }
            else
            {

           
            if(!empty($_POST['procurement_entity']) && ( $_POST['procurement_entity'] > 0 ))
            {
                $searchstring .= ' AND pdes.pdeid ='.mysql_real_escape_string($_POST['procurement_entity']);
            }

            if(!empty($_POST['procuremensearchenginet_type']) && ($_POST['procurement_type'] > 0))
            {
                $searchstring .= ' AND procurement_types.id ='.mysql_real_escape_string($_POST['procurement_type']);
            }

            if(!empty($_POST['procurement_method']) && ($_POST['procurement_method'] > 0))
            {
                $searchstring .= ' AND procurement_methods.id ='.mysql_real_escape_string($_POST['procurement_method']);
            }

            if(!empty($_POST['procurement_method']) && ($_POST['procurement_method'] > 0))
            {
                $searchstring .= ' AND procurement_methods.id ='.mysql_real_escape_string($_POST['procurement_method']);
            }

            if(!empty($_POST['sourceof_funding']) && ($_POST['sourceof_funding'] > 0))
            {
                $searchstring .= ' AND funding_sources.id ='.mysql_real_escape_string($_POST['sourceof_funding']);
            }

            if(!empty($_POST['subjectof_procurement']))
            {
                $subject_of_procurement= mysql_real_escape_string($_POST['subjectof_procurement']);
                $searchstring .= ' AND  procurement_plan_entries.subject_of_procurement like "%'.$subject_of_procurement.'%" ';
            }
            if(!empty($_POST['financial_year']))
            {
                $financial_year= mysql_real_escape_string($_POST['financial_year']);
                $searchstring .= ' AND  procurement_plans.financial_year like "%'.$financial_year.'%" ';
            }
        }
#print_r($searchstring);
#exit();

            $this->session->set_userdata('searchengine',$searchstring);
            $data['searchresult'] = paginate_list($this, $data,'procurement_search_list', array('SEARCHSTRING' => $searchstring),10);

            # print_r($data['searchresult']); exit();
            #exit('reached');
            #$data['result'] = $this->db->query($query)->result_array();
            $this->load->view('public/active_plans/entry_details_search_v', $data);


        }
    }

    function search_currentbids()
    {

        # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
        
        # Pick all assigned data
        $data = assign_to_data($urldata);


 
                /*
                    Simple Search :: 
                */
             $search_str = '  ';
        if(!empty($_POST['simple_search']))
        {
            $search_str = ' AND  ( procurement_plan_entries.subject_of_procurement LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"';

           $search_str .= ' OR  procurement_plans.pde_id in  ( SELECT pdes.pdeid FROM pdes WHERE pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%" ) ';

 
            $search_str .= ' OR  procurement_plan_entries.procurement_type in   ( SELECT procurement_types.id FROM procurement_types WHERE procurement_types.title LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%" )    ) ';

        }
        else
        {

                   /*
                    Advanced Search 
                    */

            $search_str = '  ';


        #print_r($_POST);
                #Search results
                
                    if(!empty($_POST['subjectof_procurement']))
                    {
                          $search_str = ' AND procurement_plan_entries.subject_of_procurement LIKE "%' .mysql_real_escape_string($_POST['subjectof_procurement']) . '%"';

         
                    }
                    
                     
                   #  if (!empty($_POST['date_posted_from']) && !empty($_POST['date_posted_to']))
                      #  $search_str .= ' AND bid_invitation_details.bid_date_approved >  "' . custom_date_format('Y-m-d', $_POST['date_posted_from']) . '" - INTERVAL 1 DAY ' .' AND bid_invitation_details.bid_date_approved <  "' . custom_date_format('Y-m-d', $_POST['date_posted_to']) . '" + INTERVAL 1 DAY ';

                    if(!empty($_POST['procurement_type']))
                       $search_str .= ' AND procurement_plan_entries.procurement_type =  "' .mysql_real_escape_string($_POST['procurement_type']). '"';

                    if(!empty($_POST['procurement_method']) && ($_POST['procurement_method'] > 0))
                       $search_str .= ' AND procurement_plan_entries.procurement_method =  "' .mysql_real_escape_string($_POST['procurement_method']). '"';

                    if(!empty($_POST['procurement_entity']) && ( $_POST['procurement_entity'] > 0 ))
                       $search_str .= ' AND procurement_plans.pde_id =  "' .mysql_real_escape_string($_POST['procurement_entity']) . '"';

                    if(!empty($_POST['financial_year']))
                    {
                        $financial_year= mysql_real_escape_string($_POST['financial_year']);
                        $search_str .= ' AND  procurement_plans.financial_year like "%'.$financial_year.'%" ';
                    }

                    if(!empty($_POST['sourceof_funding']) && ($_POST['sourceof_funding'] > 0))
                    {
                         
                        $search_str .= ' AND procurement_plan_entries.funding_source ='.mysql_real_escape_string($_POST['sourceof_funding']);
                    }
                        
                    if(!empty($_POST['admin_review']) && ($_POST['admin_review'] > 0))
                        $search_str .= ' AND bestevaluatedbidder.ispublished = "'.$_POST['admin_review'].'"';
        }
            
            $this->session->set_userdata('searchengines',$search_str);
          
            //Search Engine 
            $data = paginate_list($this, $data, 'active_ifb_details', array('orderby' => 'invitation_to_bid_date DESC', 'searchstring' => 'bidinvitations.isactive = "Y"  AND procurement_plans.isactive="Y"  AND procurement_plan_entries.isactive="Y"  AND    IF(bidinvitations.procurement_method_ifb > 0,bidinvitations.procurement_method_ifb  IN("1","2","9","11")  ,procurement_plan_entries.procurement_method  IN("1","2","9","11")  )  AND procurement_plans.isactive ="Y" AND bidinvitations.isapproved="Y" '.$search_str), 10);
      
           #exit($this->db->last_query());
           # $data = paginate_list($this, $data, 'bid_invitation_details', array('orderby' => 'bid_dateadded DESC', 'searchstring' => $search_str .
              #  ' AND bidinvitations.isactive = "Y" AND bidinvitations.isapproved="Y" AND bidinvitations.bid_submission_deadline>NOW()'),10);

            #  $data['formdata'] = $_POST;

            $this->load->view('public/includes/search_latest_bids', $data);

             //Micheals Work
      /*  # Get the passed details into the url data array if any
        $urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));

        # Pick all assigned data
        $data = assign_to_data($urldata);

        #Search results
        $data['page_list']=$this->bid_invitation_m->get_search_published_invitation_for_bids($pde = $_POST['procurement_entity'],$funding_source=$_POST['sourceof_funding'],$proc_method=$_POST['procurement_method'],$proc_type=$_POST['procurement_type'],$proc_subj=$_POST['subjectof_procurement'],$financial_year=$_POST['financial_year']);



        $this->load->view('public/includes/search_latest_bids', $data);   */


    }


// Search
    function search_best_evaluated_bidder()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));

        # Pick all assigned data
        $data = assign_to_data($urldata);
        $searchstring = '';


         $searchstring = '  AND procurement_plan_entries.isactive ="Y" AND procurement_plans.isactive="Y" AND bidinvitations.isactive="Y"   AND ( (bestevaluatedbidder.beb_expiry_date >= CURDATE()) || (bestevaluatedbidder.isreviewed != "N") ) ' ;

         


        if(!empty($_POST))
        {
            
          
            if(!empty($_POST['simple_search']))
            {
               $searchstring .= ' AND  ( procurement_plan_entries.subject_of_procurement LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"  ';

               $searchstring .= ' OR   pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ';

     
                $searchstring .= ' OR  bidinvitations.procurement_ref_no LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ) ';    



            }
            else
            {

                      

                    // Procurement Entity 
                    if(!empty($_POST['procurement_entity']) && ( $_POST['procurement_entity'] > 0 ))
                    {
                        $searchstring .= ' AND  b.pdeid ='.mysql_real_escape_string($_POST['procurement_entity']);
                    }

                    //Procurement Type 

                      if(!empty($_POST['procurement_type']) && ($_POST['procurement_type'] > 0))
                        {
                            $searchstring .= ' AND procurement_plan_entries.procurement_type ='.mysql_real_escape_string($_POST['procurement_type']);
                        }  

                        // Procurement method  :: 

                   if(!empty($_POST['procurement_method']) && ($_POST['procurement_method'] > 0))
                    {
                        /*
                        IF IFB METHOD IS SET. IT TAKES PRECEDENCE OVER THE PROCUREMENT ENTRY METHOD OF PROCUREMENT 
                        
                        procurement_plan_entries.procurement_method
                        bidinvitations. procurement_method_ifb

                        IF(   bidinvitations. procurement_method_ifb > 0 ,  bidinvitations. procurement_method_ifb = $_POST['procurement_method']  , procurement_plan_entries. procurement_method = $_POST['procurement_method']  )

                        */
                        $searchstring .= '  AND  IF(   bidinvitations. procurement_method_ifb > 0 ,  bidinvitations. procurement_method_ifb = '.$_POST['procurement_method'].'  , procurement_plan_entries. procurement_method ='. $_POST['procurement_method'] .' ) ';
                 
                      #  print_r($_POST['procurement_method']);
                       # exit();
                        // procurement_plan_entries.procurement_method ='.mysql_real_escape_string($_POST['procurement_method']);
                    }

                    
         
                    if(!empty($_POST['sourceof_funding']) && ($_POST['sourceof_funding'] > 0))
                    {
                        $searchstring .= ' AND procurement_plan_entries.funding_source ='.mysql_real_escape_string($_POST['sourceof_funding']);
                    }

                    if(!empty($_POST['subjectof_procurement']))
                    {
                          $searchstring .= ' AND  ( procurement_plan_entries.subject_of_procurement LIKE "%' .mysql_real_escape_string(trim($_POST['subjectof_procurement'])) . '%" ) ';
                    }

                    if(!empty($_POST['financial_year']))
                    {
                        $financial_year= mysql_real_escape_string($_POST['financial_year']);
                        $searchstring .= ' AND  a.financial_year like "%'.$financial_year.'%" ';
                    }

                    if(!empty($_POST['best_beb']))
                    {
                        $provider_beb = trim(mysql_real_escape_string($_POST['best_beb']));
                        $searchstring .= ' AND  if(receipts.providerid > 0, receipts.providerid  in (select  providerid from providers where providernames like "%'.$provider_beb.'%" ) , joint_venture.providers in(select  providerid from providers where providernames like "%'.$provider_beb.'%")  )     ' ;

                    }   

                    if(!empty($_POST['admin_review']))
                    {
                        $post_status_dropdown = $_POST['admin_review'];
                        $searchstring .=' AND bestevaluatedbidder.isreviewed = "'.$post_status_dropdown.'"';
                    }

          }
            $this->session->set_userdata('searchengine3',$searchstring);   
        }

         #   exit("pass");
 
 /*
 print_r($searchstring);
                exit("pass");
 */

              
             
 


        $data['page_list'] = $this->Receipts_m->fetchbeb($data,$searchstring);
        
        #exit($this->db->last_query());



        $this->db->order_by("title", "asc");
        $data['procurement_types'] = $this->db->get_where('procurement_types', array('isactive' => 'Y'))->result_array();

        $this->db->order_by("title", "asc");
        $data['procurement_methods'] = $this->db->get_where('procurement_methods', array('isactive' => 'Y'))->result_array();

        $this->db->order_by("pdename", "asc");
        $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();

        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'beb';
        $this->load->view('public/searchbeb_v', $data);

    }


    function search_awarded_contracts()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));

        #Pick all assigned data
        $data = assign_to_data($urldata);
        $searchstring = '';

        if(!empty($_POST))
        {


            if(!empty($_POST['simple_search']))
            {
               $searchstring .= ' AND  (  PPE.subject_of_procurement LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"  ';

               $searchstring .= ' OR   pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

 

       

                 
                $searchstring .= ' OR  BI.procurement_ref_no LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ) ';     



            }
            else
            {


                    #    $searchstring = '1 = 1';
                    if(!empty($_POST['procurement_entity']) && ( $_POST['procurement_entity'] > 0 ))
                    {
                        $searchstring .= ' AND  pdes.pdeid ='.mysql_real_escape_string($_POST['procurement_entity']);
                    }

                    if(!empty($_POST['procurement_type']) && ($_POST['procurement_type'] > 0))
                    {
                        $searchstring .= ' AND PPE.procurement_type ='.mysql_real_escape_string($_POST['procurement_type']);
                    }

                    if(!empty($_POST['procurement_method']) && ($_POST['procurement_method'] > 0))
                    {
                        $searchstring .= ' AND PPE.procurement_method ='.mysql_real_escape_string($_POST['procurement_method']);
                    }

                    if(!empty($_POST['sourceof_funding']) && ($_POST['sourceof_funding'] > 0))
                    {
                        $searchstring .= ' AND PPE.funding_source ='.mysql_real_escape_string($_POST['sourceof_funding']);
                    }

                    if(!empty($_POST['subjectof_procurement']))
                    {
                        $subject_of_procurement= mysql_real_escape_string($_POST['subjectof_procurement']);
                        $searchstring .= ' AND  PPE.subject_of_procurement like "%'.$subject_of_procurement.'%" ';
                    }

                    if(!empty($_POST['financial_year']))
                    {
                        $financial_year= mysql_real_escape_string($_POST['financial_year']);
                        $searchstring .= ' AND  PP.financial_year like "%'.$financial_year.'%" ';
                    }

                    if(!empty($_POST['service_providers']))
                    {
                      /*  $contract_providers = mysql_real_escape_string($_POST['service_providers']);
                        $searchstring .=' AND providernames LIKE "%'.$contract_providers.'%"'; */
                        
                         $contract_providers = mysql_real_escape_string($_POST['service_providers']);
                                        
                      /*   $searchstring .='  AND  IF(
               RS.providerid  > 0,
                
              RS.providerid IN ( SELECT providers.providerid  FROM providers WHERE 
                                     providers.providernames LIKE "%s%" LIMIT 1 )
               ,  
                1=1
                                     )
               
                   )
               ';   */
                    
                    
                    }

                    if(!empty($_POST['contracts_status']))
                    {
                        switch($_POST['contracts_status'])
                        {
                            case 'A':
                                $searchstring .= ' AND (actual_completion_date IS NULL OR actual_completion_date = " " ) ';
                                break;

                            case 'C':
                                $searchstring .= ' AND ( actual_completion_date != " " OR actual_completion_date IS NOT NULL )';
                                break;
                        }

                    }
                }
                $this->session->set_userdata('searchengineA',$searchstring);

        }

       # $query = $this->Query_reader->get_query_by_code('get_published_contracts',  array('orderby' => 'date_signed DESC', 'searchstring' => $searchstring,'limittext'=>''));
       # print_r($query); exit();

        $data = paginate_list($this, $data, 'get_published_contracts', array('orderby' => 'date_signed DESC', 'searchstring' => $searchstring), 10);

        $data['view_to_load'] = 'public/includes/awarded_contracts';
        $data['current_menu'] = 'awarded_contracts';
        $this->load->view('public/includes/search_awarded_contracts', $data);
    }

    function search_diposal_plans()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);
        $this->db->order_by("title", "asc");
        $data['procurement_types'] = $this->db->get_where('procurement_types', array('isactive' => 'Y'))->result_array();

        $this->db->order_by("title", "asc");
        $data['procurement_methods'] = $this->db->get_where('procurement_methods', array('isactive' => 'Y'))->result_array();

        $this->db->order_by("pdename", "asc");
        $data['pdes'] = $this->db->get_where('pdes', array('isactive' => 'Y', 'status' => 'in'))->result_array();

        $searchstring = '1 = 1 ';
        if(!empty($_POST))
        {

            if(!empty($_POST['simple_search']))
            {
             $searchstring .= ' AND  (  disposal_method.method LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"  ';

               $searchstring .= ' OR   pdes.pdename LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

                 $searchstring .= ' OR  disposal_record.subject_of_disposal  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"     ';

  
                 
                $searchstring .= ' OR  disposal_plans.financial_year  LIKE "%' .mysql_real_escape_string(trim($_POST['simple_search'])) . '%"    ) ';      



            }
            else
            {


                if(!empty($_POST['disposing_entity']) && ( $_POST['disposing_entity'] > 0 ))
                {
                    $searchstring .= ' AND  pdes.pdeid ='.mysql_real_escape_string($_POST['disposing_entity']);
                }

                if(!empty($_POST['disposing_method']) && ( $_POST['disposing_method'] > 0 ))
                {
                    $searchstring .= ' AND  disposal_method.id ='.mysql_real_escape_string($_POST['disposing_method']);
                }

                if(!empty($_POST['financial_year']))
                {
                    $searchstring .= ' AND  disposal_plans.financial_year  like "%'.mysql_real_escape_string($_POST['financial_year']).'%"';
                }

                if(!empty($_POST['subjectof_disposal']))
                {
                    $searchstring .= ' AND  disposal_record.subject_of_disposal like "%'.mysql_real_escape_string($_POST['subjectof_disposal']).'%"';
                }
          }

            # Adding Search Engine ::
          $this->session->set_userdata('searchengine',$searchstring);

        }

      
        $data['page_list'] = $this->disposal->fetch_disposal_records($data,$searchstring);
        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'disposal_plans';
        $this->load->view('public/search_disposal', $data);

    }

    function testemailsendtorop()
    {
        echo "INITIALIZING";
        $data =  $this-> Remoteapi_m -> emaillist_providers();
        print_r($data);
        echo "END";
    }

    function weeklyreport()
    {
        weeklyreport();
        echo "Finalized @ ";

    }

    # SEND EMAIL ALERTS TO ROP
    function notifyrop()
    {
        # echo "reached";
        $segment = $this->uri->segment(3);
        #   echo($segment);
        if(!empty($segment)){

            $bidinvitation =     $segment;
            if(!empty($bidinvitation ))
            {

                notifyropp($bidinvitation);
            }
        }


    }


#Return all providers whose suspension eneded
    function fetch_ended_suspension()
    {
        $urldata = $this->uri->uri_to_assoc(3, array('m'));
        # Pick all assigned data
        $data = assign_to_data($urldata);

        $data['suspendedlist'] = $this->Remoteapi_m->provider_end_suspensions();
        //Return admin emails to send to
        $data['user_mails'] = $this->db->query("SELECT
                                        roles.userid,
                                        users.firstname,
                                        users.middlename,
                                        users.lastname,
                                        users.emailaddress
                                        FROM
                                        usergroups
                                        INNER JOIN roles ON roles.groupid = usergroups.id
                                        INNER JOIN users ON roles.userid = users.userid
                                        WHERE
                                        usergroups.id = 14 AND
                                        usergroups.isactive = 'Y' AND
                                        roles.isactive = 'Y' AND
                                        users.isactive = 'Y'
                                        ORDER BY
                                        users.userid DESC")->result_array();

        //print_array($data['user_mails']);


        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'suspended_providers';
        $this->load->view('public/test', $data);

    }










    function scheduler_awarded_contracts(){

        //redirect(base_url());
        //echo 'foo';

        //send_html_email('mcengkuru@gmail.com','foo','hi mike','this is a test','mcengkuru@newwavetech.co.ug');


        $to = date('Y')+1 . '-' . '06' . '-30';
        //print_array($to);
        $from = date('Y') . '-' . '07' . '-01';//Fiscal year: 1 July - 30 June

        $data['financial_year']=$from.'-'.$to;


        $data['page_title'] = 'PART I: CONTRACTS AWARDED ';

        $data['report_heading'] = $data['page_title'];
        $data['reporting_period'] = custom_date_format('d/F/Y', $from) . ' &nbsp<b>to</b> &nbsp &nbsp' . custom_date_format('d/F/Y', $to);


        $pde = $this->session->userdata('pdeid');
        $data['results'] = $this->contracts_m->get_contracts_all_awarded($from, $to, $pde);

        //$to=$this->session->userdata('emailaddress');
        $to='mcengkuru@gmail.com';
        $subject=$data['page_title'];
        $salutation='';

        $cc='mcengkuru@newwavetech.co.ug';
        $email_from='noreply@gpp.ppda.co.ug';


        $content=template_awarded_contracts($data['page_title'],$data['financial_year'],$data['reporting_period'],$data['results']);



        send_html_email($to,$subject,$salutation,$content,$email_from,$cc);

//        $myFile = "templates/filename.html"; // or .php
//        $fh = fopen($myFile, 'w'); // or die("error");
//        $stringData = $content;
//        fwrite($fh, $stringData);
//        chmod($myFile, 0777);

        //send_html_email_no_template($to,$subject,$content,$from);






        $this->load->view('email_templates/awarded_contracts_tplt', $data);


    }




    function weeklybebreport()
    {
        weeklybebreport();
        echo "Finalized @ ";
    }



    function monthlyreportonexpiringcontracts()
    {
        monthlyreportonexpiringcontracts();
        echo "Finalized @ ";
    }

    function awarded_beb_to_suspended_providers()
    {
        awarded_beb_to_suspended_providers();
        echo "Finalized @ ";
    }


    function fallback(){
        $host=$this->db->hostname;
        $username=$this->db->username;
        $password=$this->db->password;
        $db=$this->db->database;
        //print_array($password);
        backup_database($username, $password,$host, $db);

    }



    #FETCH procurement plan notification:
    function  procurement_plan_notifications(){
        print_r(procurement_plan_notifications());
    }

    function test(){
        $data=$this->bid_invitation_m->get_search_published_invitation_for_bids($pde = $_POST['procurement_entity'],$funding_source=$_POST['sourceof_funding'],$proc_method=$_POST['procurement_method'],$proc_type=$_POST['procurement_type'],$proc_subj=$_POST['subjectof_procurement'],$financial_year=$_POST['financial_year']);

        print_array($data);
    }


    # search for current tenders
    public function search_tenders(){
        $data['page_title'] = 'Current Tenders';
        #Search results

        $limit = 1000;
        $data['page_list'] = $this->bid_invitation_m->get_paginated_published_invitation_for_bids($num = $limit, $this->uri->segment(3), $this->input->get('term'));
        //pagination configs
        $config = array
        (
            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page',//contigure page base_url
            'total_rows' => count($data['page_list']),
            'per_page' => $limit,
            'num_links' => $limit,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<div class="btn-group">',
            'full_tag_close' => '</div>',
            'anchor_class' => 'class="btn" ',
            'cur_tag_open' => '<div class="btn">',
            'cur_tag_close' => '</div>',
            'uri_segment' => '3'

        );
        //initialise pagination
        $this->pagination->initialize($config);

        //add to data array
        $data['pages'] = $this->pagination->create_links();

        //print_array($data['pages']);




        $this->load->view('public/home_v', $data);
    }


    # search for bebs
    public function search_bebs(){
        $data['page_title'] = 'Best Evaluated Bidders';
        #Search results

        $limit = 1000;
        $data['page_list'] = $this->bid_invitation_m->get_paginated_bebs($num = $limit, $this->uri->segment(3), $this->input->get('term'));
        //pagination configs
        $config = array
        (
            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page',//contigure page base_url
            'total_rows' => count($data['page_list']),
            'per_page' => $limit,
            'num_links' => $limit,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<div class="btn-group">',
            'full_tag_close' => '</div>',
            'anchor_class' => 'class="btn" ',
            'cur_tag_open' => '<div class="btn">',
            'cur_tag_close' => '</div>',
            'uri_segment' => '3'

        );
        //initialise pagination
        $this->pagination->initialize($config);

        //add to data array
        $data['pages'] = $this->pagination->create_links();

        //print_array($data['page_list']);




        $this->load->view('public/beb_search_results_v', $data);
    }


    # search for disposals
    public function search_disposals(){
        $data['page_title'] = 'Disposal notices';
        #Search results

        $limit = 1000;
        $data['page_list'] = $this->disposal->get_paginated_disposal_notices($num = $limit, '', $this->input->get('term'));


        //pagination configs
        $config = array
        (
            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page',//contigure page base_url
            'total_rows' => count($data['page_list']),
            'per_page' => $limit,
            'num_links' => $limit,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<div class="btn-group">',
            'full_tag_close' => '</div>',
            'anchor_class' => 'class="btn" ',
            'cur_tag_open' => '<div class="btn">',
            'cur_tag_close' => '</div>',
            'uri_segment' => '3'

        );
        //initialise pagination
        $this->pagination->initialize($config);

        //add to data array
        $data['pages'] = $this->pagination->create_links();


        # switch the views depending on the tab
        switch($this->uri->segment(3)){
            case 'disposal_notices':
                $this->load->view('public/disposal_notice', $data);
                break;
            case 'disposal_plans':
                $this->load->view('public/disposal', $data);
                break;


        }





    }



    # search for plans
    public function search_plans(){
        $data['page_title'] = 'Procurement Plans';
        #Search results

        $limit = 1000;
        $data['all_plans']=$data['all_plans_paginated'] = $this->procurement_plan_m->get_paginated_procurement_plans($num = $limit, $this->uri->segment(3), $this->input->get('term'));
        //pagination configs
        $config = array
        (
            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page',//contigure page base_url
            'total_rows' => count($data['page_list']),
            'per_page' => $limit,
            'num_links' => $limit,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<div class="btn-group">',
            'full_tag_close' => '</div>',
            'anchor_class' => 'class="btn" ',
            'cur_tag_open' => '<div class="btn">',
            'cur_tag_close' => '</div>',
            'uri_segment' => '3'

        );
        //initialise pagination
        $this->pagination->initialize($config);

        //add to data array
        $data['pages'] = $this->pagination->create_links();

        //print_array($data['page_list']);





        $data['view_to_load'] = 'public/active_plans/active_plans_v';
        //load view
        $this->load->view('public/home_v', $data);
    }

    # search for contracts
    public function search_contracts(){
        $data['page_title'] = 'Signed Contracts';
        #Search results

        $limit = 1000;
        $data['page_list'] = $this->contracts_m->get_paginated_published_contracts($num = $limit, $this->uri->segment(3), $this->input->get('term'));
        //pagination configs
        $config = array
        (
            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page',//contigure page base_url
            'total_rows' => count($data['page_list']),
            'per_page' => $limit,
            'num_links' => $limit,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<div class="btn-group">',
            'full_tag_close' => '</div>',
            'anchor_class' => 'class="btn" ',
            'cur_tag_open' => '<div class="btn">',
            'cur_tag_close' => '</div>',
            'uri_segment' => '3'

        );
        //initialise pagination
        $this->pagination->initialize($config);

        //add to data array
        $data['pages'] = $this->pagination->create_links();

        //print_array($data['page_list']);





        $data['view_to_load'] = 'public/includes/awarded_contracts';
        $data['current_menu'] = 'awarded_contracts';
        $this->load->view('public/home_v', $data);
    }



    # search for suspended providers
    public function search_suspended(){
        $data['page_title'] = 'Suspended Providers';
        #Search results

        $limit = 1000;
        $data['suspendedlist'] = $this->Remoteapi_m->search_paginated_providers($this->input->get('term'));
     
        //pagination configs
        $config = array
        (
            'base_url' => base_url() . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page',//contigure page base_url
            'total_rows' => count($data['page_list']),
            'per_page' => $limit,
            'num_links' => $limit,
            'use_page_numbers' => TRUE,
            'full_tag_open' => '<div class="btn-group">',
            'full_tag_close' => '</div>',
            'anchor_class' => 'class="btn" ',
            'cur_tag_open' => '<div class="btn">',
            'cur_tag_close' => '</div>',
            'uri_segment' => '3'

        );
        //initialise pagination
        $this->pagination->initialize($config);

        //add to data array
        $data['pages'] = $this->pagination->create_links();

        //print_array($data['page_list']);

        $data['title'] = 'National Tender Portal';
        $data['current_menu'] = 'suspended_providers';
        $this->load->view('public/suspended_providers_v', $data);
    }












}
