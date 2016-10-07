                <?php
                ob_start();
                /**
                * Created by PhpStorm.
                * User: moverr@gmail.com
                * Date: 4/20/14
                * Time: 3:04 PM
                *
                * controls notifications CRUD
                */

                class Notification_m extends MY_Model
                {

                function __construct()
                {
                // Call the Model constructor
                parent::__construct();
                }

                public $_tablename='notifications';
                public $_primary_key='id';

                //mark as seen
                    public function mark_as_seen($user_id,$msg_id)
                    {
                    $data=array
                    (
                    'seen'=>'y'
                    );
                    $this->db->where('user_id', $user_id);
                    $this->db->update('users', $data);

                    }

                //prevent duplications
                public function prevent_duplicate($notification_data){
                //the query
                $query=$this->db->select()->from('notifications')->where($notification_data)->get()  ;

                return $query->num_rows();
                }

                public function get_unseen_messages($userid,$param)
                {
                $data = array
                (
                'user_id'   =>$userid,
                'seen'      =>'n',
                'trash'     =>'n'
                );
                $query=$this->db->select()->from($this->_tablename)->where($data)->get();

                foreach($query->result_array() as $row)
                {
                switch($param)
                {
                //case of notifications
                case 'message':
                $result=$row['notification'];
                break;
                case 'alert_type':
                $result=$row['alert_type'];
                break;
                case 'dateadded':
                $result=$row['dateadded'];
                break;
                default:
                $result=$query->result_array();
                }

                return $result;
                }
                }

                //EMAIL ACCESS

                function email_access($permission,$pdeid)
                {
                $usergroup = $this->session->userdata('usergroup');
                $receipients_str = '';


                if($this->session->userdata('level') =='ppda'){
                $data = array('searchstring' => ' permissions.code = "'.$permission.'"  AND groupaccess.groupid ="14" ');
                }

                else{
                $data = array('searchstring' => ' permissions.code = "'.$permission.'"  AND users.pde = "' . $pdeid . '" AND groupaccess.groupid ="'.$usergroup.'"');
                }

                $query = $this->Query_reader->get_query_by_code('notification_access', $data);
                # print_r($query); exit();
                $result = $this->db->query($query)->result_array();
                $emailarray = array();

                if(!empty($result))
                {
                  
                foreach($result as $row)
                {
                  
                if(in_array($row['emailaddress'], $emailarray))
                continue;

                $receipients_str .= (!empty($receipients_str)? '|' : '') . $row['emailaddress'];
                array_push($emailarray,$row['emailaddress']);
                }

                $receipients_str = '|' . $receipients_str . '|';

                }

                # print_r($receipients_str); exit();
                return $receipients_str;
                }

                //notification access

      function notification_access($permission, $pdeid='')
{
  
$usergroup = $this->session->userdata('usergroup');
$receipients_str = '';
$receipients_str2 = '';

$datasx = $this->session->userdata('level');
if(!empty($datasx) ){
$data = array('searchstring' => ' groupaccess.groupid ="14" OR  groupaccess.groupid ="24" ');
$this->session->unset_userdata('level');
}

else{
    if(!empty($pdeid))
    {
        $data = array('searchstring' => ' permissions.code = "'.$permission.'"  AND users.pde = "' . $pdeid . '" ');
    }
    else
    {
        $data = array('searchstring' => ' permissions.code = "'.$permission.'"   ');
    }


#AND groupaccess.groupid ="'.$usergroup.'"
}
#$data = array('searchstring' => ' permissions.code = "'.$permission.'"  AND users.pde = "' . $pdeid . '" AND groupaccess.groupid ="'.$usergroup.'"');

$query = $this->Query_reader->get_query_by_code('notification_access', $data);
#  echo "reslute <br/>";
#  print_r($query); exit();
#echo "<br/>";

$result = $this->db->query($query)->result_array();

if(!empty($result))
{
  $emailarray = array();
foreach($result as $row)
{

  if(in_array($row['emailaddress'], $emailarray))
  continue;
  
  $receipients_str .= (!empty($receipients_str)? '|' : '') . $row['userid'];
  $receipients_str2 .= (!empty($receipients_str2)? '|' : '') . $row['emailaddress'];
  
  array_push($emailarray,$row['emailaddress']);

}

$receipients_str = '|' . $receipients_str . '|';
$receipients_str2 = '|' . $receipients_str2 . '|';




# print_r($receipients_str); exit()
}

return $receipients_str."<><><>".$receipients_str2;
}



                //notification access all

                 
                 function notification_access_all($permission)
                 {
                
                $usergroup = $this->session->userdata('usergroup');
                $receipients_str = '';
                $receipients_str2 = '';

                 
                $data = array('searchstring' => ' permissions.code = "'.$permission.'"   ');
                 

                $query = $this->Query_reader->get_query_by_code('notification_access', $data);
                

                $result = $this->db->query($query)->result_array();

                if(!empty($result))
                {
                  $emailarray = array();
                foreach($result as $row)
                {

                  if(in_array($row['emailaddress'], $emailarray))
                  continue;
                  
                  $receipients_str .= (!empty($receipients_str)? '|' : '') . $row['userid'];
                  $receipients_str2 .= (!empty($receipients_str2)? '|' : '') . $row['emailaddress'];
                  
                  array_push($emailarray,$row['emailaddress']);

                }

                $receipients_str = '|' . $receipients_str . '|';
                $receipients_str2 = '|' . $receipients_str2 . '|';




                # print_r($receipients_str); exit()
                }

                return $receipients_str."<><><>".$receipients_str2; 
                 //end
                 
                }
                function push_permission($title,$body,$level,$permission,$entity='')
                {

                        $this->load->library('email');
                        /* SAVE NOTIFICATION*/
                        $title = mysql_real_escape_string($title);
                        $body = mysql_real_escape_string($body);
                        $level = mysql_real_escape_string($level);

                        $data = array('TITLE' => $title,'BODY'=>$body,'ISACTIVE'=>'Y','LEVEL'=>$level);

                        $query = $this->Query_reader->get_query_by_code('insert_notification', $data);

                        if($query){
                        $result = $this->db->query($query);
                        $insertid = $this->db->insert_id();

                      
                      if(!empty($entity))
                            {
                            $pdeid = $entity;
                            }
                            else
                            {
                            $pdeid =  $this->session->userdata('pdeid');
                                    if(!empty($pdeid) && $pdeid > 0 )
                        {
                        $pdeid = $pdeid;
                        }
                        else
                        {
                        $pdeid = 0;
                        }
}
                        #echo "<br/><br/>:::<br/><br/>";
                        #exit($pdeid);


                        $recipients = $this->notification_access($permission,$pdeid);
                        $ni_recepient = explode("<><><>",$recipients);
                        $r1 = $ni_recepient[0];
                        $r2 = $ni_recepient[1];
                        // print_r($r2);
                        // exit();
                        $recipients = rtrim($r1,'|');
                        $recipientsarray = explode("|", $recipients);
                        $emailarray = explode("|", $r2);

                        for($x = 0; $x < count($recipientsarray); $x ++)
                        {

                        if(($recipientsarray[$x] == 0) || ($insertid == 0)) continue;

                        $datar = array('RECEPIENTID' => $recipientsarray[$x],'NOTIFICATIONID'=>$insertid,'ISACTIVE'=>'Y');
                        $query = $this->Query_reader->get_query_by_code('insert_notifications_recipients', $datar);
                        $result = $this->db->query($query);

                        //print_r($emailaddr);

                        $emailaddr = $emailarray[$x];
                        if($emailaddr =='') continue;
                        #$emailadress = array('RECEPIENTID' => $recipientsarray[$x],'NOTIFICATIONID'=>$insertid,'ISACTIVE'=>'Y');

                        $this->email->from('noreply@gpp.ppda.go.ug', 'Government of Uganda Procurement Portal Notifications');
                        $this->email->to(''.$emailaddr.'');

                        $this->email->cc('helpdesk@ppda.go.ug');
                        #$this->email->bcc('helpdesk@ppda.go.ug');

                        $this->email->subject(''.$title.'');
                        $this->email->message(''.$body.'');

                        $this->email->send();
                        }



                        $datasx = $this->session->userdata('level');
                        if(!empty($datasx) && ($datasx  =='ppda') ){
                        #   $data = array('searchstring' => ' groupaccess.groupid ="14" ');
                        $this->session->unset_userdata('level');
                        }
                        $pdeidd = $this->session->userdata('pdeidd');

                        if(!empty($pdeidd))
                        {
                        $pdeid  =$pdeidd;
                        $this->session->unset_userdata('pdeidd');
                        }



                        }




                }

                function push_permission_all($title,$body,$level,$permission,$entity='')
                {

                        $this->load->library('email');
                        /* SAVE NOTIFICATION*/
                        $title = mysql_real_escape_string($title);
                        $body = mysql_real_escape_string($body);
                        $level = mysql_real_escape_string($level);

                        $data = array('TITLE' => $title,'BODY'=>$body,'ISACTIVE'=>'Y','LEVEL'=>$level);

                        $query = $this->Query_reader->get_query_by_code('insert_notification', $data);

                        if($query){
                        $result = $this->db->query($query);
                        $insertid = $this->db->insert_id();

                      
                       
                        $pdeid = 0;
                        


                        $recipients = $this->notification_access_all($permission);
                        $ni_recepient = explode("<><><>",$recipients);
                        $r1 = $ni_recepient[0];
                        $r2 = $ni_recepient[1];
                        // print_r($r2);
                        // exit();
                        $recipients = rtrim($r1,'|');
                        $recipientsarray = explode("|", $recipients);
                        $emailarray = explode("|", $r2);

                        for($x = 0; $x < count($recipientsarray); $x ++)
                        {

                        if(($recipientsarray[$x] == 0) || ($insertid == 0)) continue;

                        $datar = array('RECEPIENTID' => $recipientsarray[$x],'NOTIFICATIONID'=>$insertid,'ISACTIVE'=>'Y');
                        $query = $this->Query_reader->get_query_by_code('insert_notifications_recipients', $datar);
                        $result = $this->db->query($query);

                        //print_r($emailaddr);

                        $emailaddr = $emailarray[$x];
                        if($emailaddr =='') continue;
                        #$emailadress = array('RECEPIENTID' => $recipientsarray[$x],'NOTIFICATIONID'=>$insertid,'ISACTIVE'=>'Y');

                        $this->email->from('noreply@gpp.ppda.go.ug', 'Government of Uganda Procurement Portal Notifications');
                        $this->email->to(''.$emailaddr.'');

                        $this->email->cc('helpdesk@ppda.go.ug');
                        #$this->email->bcc('helpdesk@ppda.go.ug');

                        $this->email->subject(''.$title.'');
                        $this->email->message(''.$body.'');

                        $this->email->send();
                        }



                        $datasx = $this->session->userdata('level');
                        if(!empty($datasx) && ($datasx  =='ppda') ){
                        #   $data = array('searchstring' => ' groupaccess.groupid ="14" ');
                        $this->session->unset_userdata('level');
                        }
                        $pdeidd = $this->session->userdata('pdeidd');

                        if(!empty($pdeidd))
                        {
                        $pdeid  =$pdeidd;
                        $this->session->unset_userdata('pdeidd');
                        }



                        }




                }



                function number_of_notifications($userid, $searchstring )
                {
                #$query = $this->Query_reader->get_query_by_code('new_user_notifications',$searchstring );
                # print_r($query); exit();

                $result_row = $this->Query_reader->get_row_as_array('new_user_notifications', $searchstring);
                return $result_row['numOfNotifications'];
                }

                function number_of_other_notifications($userid, $searchstring )
                {
                #$query = $this->Query_reader->get_query_by_code('new_user_notifications',$searchstring );
                # print_r($query); exit();

                $result_row = $this->Query_reader->get_row_as_array('old_notifications', $searchstring);
                return $result_row['numOfNotifications'];
                }




                function sample_new_notifications($userid, $searchstring )
                {
                $query = $this->Query_reader->get_query_by_code('sample_new_notifications', $searchstring);
                $result = $this->db->query($query)->result_array();
                return $result;

                }
                function  update_status($userid,$notification,$status){
                $searchstring = array('NOTIFICATION' => $notification ,'USERID'=>$userid,'STATUS'=>$status );
                $query = $this->Query_reader->get_query_by_code('insert_notification_status', $searchstring);
                #print_r($query); exit();
                $result = $this->db->query($query);
                return 1;
                }

                function view_notifications($userid,$data = array())
                {
                  if(empty($data)){

                $query = $this->Query_reader->get_query_by_code('view_user_notifications', array('searchstring' => ' users.id = '. $userid,' limittext'=>'limit 5'));
                #print_r($query); exit();

                $result = $this->db->query($query)->result_array();
                return $result;
                }

                }
                function view_notification($notification_id){
                $query = $this->Query_reader->get_query_by_code('notification_detail', array('id' => $notification_id ));

                $result = $this->db->query($query)->result_array();

                $userid = $this->session->userdata('userid');
                update_status($userid,$result[0]['id'],'R');

                return $result;
                }


               
               function fetch_notifications($userid,$limittext=0,$data = array(),$level)
                {
                //notification_id
                # print_r($userid); exit();
                #  $query = $this->Query_reader->get_query_by_code('view_user_notifications',  array('searchstring' => '  notifications_recepients.recepient_id ='.$userid.' ','limittext'=>''));
                #print_r($query);
                switch ($level) {
                case 'read':

                $data = paginate_list($this, $data, 'view_user_notifications',  array('searchstring' => ' notifications_recepients.recepient_id = notifications_status.userid AND    notifications_recepients.recepient_id = '.$userid.' AND notifications_status.status ="R"  ORDER BY notifications.dateadded DESC  ' ),150 );
                return $data;
                # code...
                break;
                case 'unread':
                $data = paginate_list($this, $data, 'view_user_notifications',  array('searchstring' => '     notifications_recepients.recepient_id = '.$userid.' AND  notifications_recepients.notification_id  not in(SELECT A.notification_id FROM notifications_status A INNER JOIN notifications_recepients B  ON A.userid = B.recepient_id where A.userid='.$userid.' )    ORDER BY notifications.dateadded DESC ' ),150 );
                return $data;
                break;
                case 'starred':

                $data = paginate_list($this, $data, 'view_user_notifications',  array('searchstring' => ' notifications_recepients.recepient_id = notifications_status.userid AND   notifications_recepients.recepient_id = '.$userid.' AND notifications_status.status ="S" ORDER BY notifications.dateadded DESC  ' ),150 );
                return $data;
                # code...
                break;

                case 'trash':

                 $data = paginate_list($this, $data, 'view_user_notifications',  array('searchstring' => ' notifications_recepients.recepient_id = notifications_status.userid AND   notifications_recepients.recepient_id = '.$userid.' AND notifications_status.status ="T" ORDER BY notifications.dateadded DESC  ' ),150 );
                return $data;
                # code...
                break;

                default:
                # code...
                $data = paginate_list($this, $data, 'view_user_notifications',  array('searchstring' => '  notifications_recepients.recepient_id = notifications_status.userid AND   notifications_recepients.recepient_id ='.$userid.' ORDER BY notifications.dateadded DESC ' ),150 );
                return $data;
                break;

                }
                }

                function del_archive($post)
                {
                   // print_r($post);
                    $userid =  $this->session->userdata('userid');
                    $status = $post['type'];
                    switch ( $status) {
                        case 'archive':                     
                            $query = $this->db->query("UPDATE notifications_status SET status = 'T'  WHERE      notification_id = '".$_POST['id']."' AND userid = '".$userid ."' ") or die("".mysql_error());
                           return 1;
                           break;
                    case 'star':
                           $query = $this->db->query("UPDATE notifications_status SET status = 'S'  WHERE      notification_id = '".$_POST['id']."' AND userid = '".$userid ."' ") or die("".mysql_error());
                           return 1;
                           break;
                        
                        default:
                            # code...
                            break;
                    }
                }




                function update_viewed_list($notification_id)
                {
                
                
                /*  $userid = $this->session->userdata('userid');
                $query= $this->Query_reader->get_query_by_code('notification_detail', array('id' => $notification_id));
                $result_row = $this->db->query($query)->result_array();
                
                $viewedby = explode('|',$result_row[0]['viewedby']);
                //print_r($viewedby ); exit();
                $viewd ='';
                foreach ($viewedby as $key => $value) {
                # code...

                if($userid == $value)
                {
                return 0;

                }
                

                }
                $viewwedby  = ltrim($result_row[0]['viewedby'],'Array');
                $viewwedby  = ltrim($result_row[0]['viewedby'],'|');
                print_r($viewwedby); exit();
                $viewby = (strlen($viewwedby) > 0) ? $viewedby.'|'.$userid :'|'.$userid.'|' ;

                $query = mysql_query("UPDATE  notifications set viewedby = '". ltrim($viewby,'Array')."' where id= ".$notification_id) or die("".mysql_error());
                return 1;  */


                }

                #wekly report
                function weeklyreport($level,$data = array()){
                        switch ($level) {
                        case 'ppda':



                        $search_str = '  ';


                        #Get the paginated list of bid invitations
                        $results = paginate_list($this, $data, 'weekly_IFB_report',array('orderby'=>'', 'searchstring'=> ''.$search_str  ) ,1000);
                        # print_r($results); exit();
                        $table = "<div>";
                        if(!empty($results['page_list'])):

                        $table .='<table class="table table-striped table-hover">'.
                        '<thead>'.
                        '<tr>'.
                        '<th width="5%"></th>'.
                        '<th>Procurement Ref. No</th>'.
                        '<th class="hidden-480">Subject of procurement</th>'.
                        '<th class="hidden-480">Bid security</th>'.
                        '<th class="hidden-480">Bid invitation date</th>'.
                        '<th class="hidden-480">Addenda</th>'.
                        '<th>Status</th>'.
                        '<th>Published by</th>'.
                        '<th>Date Added</th>'.
                        '</tr>'.
                        '</thead>'.
                        '</tbody>';


                        foreach($results['page_list'] as $row)
                        {

                        $this->session->unset_userdata('pdeid');
                        $status_str = '';
                        $addenda_str = '[NONE]';
                        $delete_str ='';
                        $edit_str  = '';

                        if(!empty($level) && ($level == 'active'))  {
                        $delete_str = '<a title="Delete bid invitation" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'bids/delete_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'\', \'Are you sure you want to delete this bid invitation?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="icon-trash"></i></a>';

                        $edit_str = '<a title="Edit bid details" href="'. base_url() .'bids/load_bid_invitation_form/i/'.encryptValue($row['bidinvitation_id']).'"><i class="icon-edit"></i></a>';
                        }

                        if($row['bid_approved'] == 'Y' && get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')<0)
                        {
                        $status_str = 'Bid evaluation | <a title="Select BEB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Select BEB]</a>';
                        }
                        elseif($row['bid_approved'] == 'N')
                        {
                        $status_str = 'Not published | <a title="Publish IFB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Publish IFB]</a>';
                        }
                        elseif($row['bid_approved'] == 'Y' && get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')>0)
                        {
                        $status_str = 'Bidding closes in '. get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days') .' days | <a title="view IFB document" href="'. base_url() .'bids/view_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[View IFB]</a>';

                        $addenda_str =  '<a title="view addenda list" href="'. base_url() .'bids/view_addenda/b/'.encryptValue($row['bidinvitation_id']).'">[View Addenda]</a> | <a title="Add addenda" href="'. base_url() .'bids/load_ifb_addenda_form/b/'.encryptValue($row['bidinvitation_id']).'">[Add Addenda]</a>';
                        }
                        else
                        {

                        }

                        $table .='<tr>'.
                        '<td></td>'.
                        '<td>'. format_to_length($row['procurement_ref_no'], 40) .'</td>'.
                        '<td>'. format_to_length($row['subject_of_procurement'], 50) .'</td>'.
                        '<td>'. (is_numeric($row['bid_security_amount'])? number_format($row['bid_security_amount'], 0, '.', ',') . ' ' . $row['bid_security_currency_title'] :
                        (empty($row['bid_security_amount'])? '<i>N/A</i>' : $row['bid_security_amount'])) .'</td>'.
                        '<td>'. custom_date_format('d M, Y', $row['invitation_to_bid_date']) .'</td>'.
                        '<td>'. $addenda_str .'</td>'.
                        '<td>'. $status_str .'</td>'.
                        '<td>'. (empty($row['approver_fullname'])? 'N/A' : $row['approver_fullname']).'</td>'.
                        '<td>'. custom_date_format('d M, Y', $row['bid_dateadded']) .'</td>'.
                        '</tr>';
                        }

                        $table .='</tbody></table>';

                        $table .='<div class="pagination pagination-mini pagination-centered">'.
                        pagination($this->session->userdata('search_total_results'), $results['rows_per_page'], $results['current_list_page'], base_url().
                        "bids/manage_bid_invitations/".$level."/p/%d")
                        .'</div>';

                        else:
                        $table .= format_notice('WARNING: No bid invitations expiring this week');
                        endif;
                        $table .="</div>";
                        $adons  = '';
                        //$entity =  $records['pdeid'];


                        //$this->session->set_userdata('pdeid',$entity);

                        $datasx = $this->session->set_userdata('level','ppda');




                        $entityname = '';

                        $entityname = '';

                        $adons  = date('d-m');

                        $level = "Procurement";
                        # exit('moooooo');
                        $titles = "Weekly  report on expiring IFBs of ITP";
                        $body =  " ".html_entity_decode($table);
                        $permission = "view_bid_invitations";

                        $xcv = 0;


                        push_permission($titles,$body,$level,$permission);


                        #end
                        break;
                        case 'ifb':
                        $search_str  ='';
                        # code...

                        $querys = $this->db->query("select distinct b.pdeid,b.pdename,a.* from pdes b inner join   users a on a.pde = b.pdeid  ")->result_array();

                        foreach($querys as $row => $records )
                        {
                        #get the PDE ID " Idividual Pde Ids ";"
                        $search_str = ' AND procurement_plans.pde_id="'. $records['pdeid'] .'"';

                        $results = paginate_list($this, $data, 'weekly_IFB_report',array('orderby'=>'', 'searchstring'=> ''.$search_str  ) ,1000);
                        # print_r($results); exit();
                        $table = "<div>";
                        if(!empty($results['page_list'])):

                        $table .='<table class="table table-striped table-hover">'.
                        '<thead>'.
                        '<tr>'.
                        '<th width="5%"></th>'.
                        '<th>Procurement Ref. No</th>'.
                        '<th class="hidden-480">Subject of procurement</th>'.
                        '<th class="hidden-480">Bid security</th>'.
                        '<th class="hidden-480">Bid invitation date</th>'.
                        '<th class="hidden-480">Addenda</th>'.
                        '<th>Status</th>'.
                        '<th>Published by</th>'.
                        '<th>Date Added</th>'.
                        '</tr>'.
                        '</thead>'.
                        '</tbody>';


                        foreach($results['page_list'] as $row)
                        {

                        $this->session->unset_userdata('pdeid');
                        $status_str = '';
                        $addenda_str = '[NONE]';
                        $delete_str ='';
                        $edit_str  = '';

                        if(!empty($level) && ($level == 'active'))  {
                        $delete_str = '<a title="Delete bid invitation" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'bids/delete_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'\', \'Are you sure you want to delete this bid invitation?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="icon-trash"></i></a>';

                        $edit_str = '<a title="Edit bid details" href="'. base_url() .'bids/load_bid_invitation_form/i/'.encryptValue($row['bidinvitation_id']).'"><i class="icon-edit"></i></a>';
                        }

                        if($row['bid_approved'] == 'Y' && get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')<0)
                        {
                        $status_str = 'Bid evaluation | <a title="Select BEB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Select BEB]</a>';
                        }
                        elseif($row['bid_approved'] == 'N')
                        {
                        $status_str = 'Not published | <a title="Publish IFB" href="'. base_url() .'bids/approve_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[Publish IFB]</a>';
                        }
                        elseif($row['bid_approved'] == 'Y' && get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days')>0)
                        {
                        $status_str = 'Bidding closes in '. get_date_diff(date('Y-m-d'), $row['bid_submission_deadline'], 'days') .' days | <a title="view IFB document" href="'. base_url() .'bids/view_bid_invitation/i/'.encryptValue($row['bidinvitation_id']).'">[View IFB]</a>';

                        $addenda_str =  '<a title="view addenda list" href="'. base_url() .'bids/view_addenda/b/'.encryptValue($row['bidinvitation_id']).'">[View Addenda]</a> | <a title="Add addenda" href="'. base_url() .'bids/load_ifb_addenda_form/b/'.encryptValue($row['bidinvitation_id']).'">[Add Addenda]</a>';
                        }
                        else
                        {

                        }

                        $table .='<tr>'.
                        '<td></td>'.
                        '<td>'. format_to_length($row['procurement_ref_no'], 40) .'</td>'.
                        '<td>'. format_to_length($row['subject_of_procurement'], 50) .'</td>'.
                        '<td>'. (is_numeric($row['bid_security_amount'])? number_format($row['bid_security_amount'], 0, '.', ',') . ' ' . $row['bid_security_currency_title'] :
                        (empty($row['bid_security_amount'])? '<i>N/A</i>' : $row['bid_security_amount'])) .'</td>'.
                        '<td>'. custom_date_format('d M, Y', $row['invitation_to_bid_date']) .'</td>'.
                        '<td>'. $addenda_str .'</td>'.
                        '<td>'. $status_str .'</td>'.
                        '<td>'. (empty($row['approver_fullname'])? 'N/A' : $row['approver_fullname']).'</td>'.
                        '<td>'. custom_date_format('d M, Y', $row['bid_dateadded']) .'</td>'.
                        '</tr>';
                        }

                        $table .='</tbody></table>';

                        $table .='<div class="pagination pagination-mini pagination-centered">'.
                        pagination($this->session->userdata('search_total_results'), $results['rows_per_page'], $results['current_list_page'], base_url().
                        "bids/manage_bid_invitations/".$level."/p/%d")
                        .'</div>';

                        else:
                        $table .= format_notice('WARNING: No bid invitations expiring this week');
                        endif;
                        $table .="</div>";
                        $adons  = '';
                        $entity =  $records['pdeid'];


                        $this->session->set_userdata('pdeid',$entity);
                        if($records['usergroup'] > 0){
                        $level =$records['usergroup'];
                        $this->session->set_userdata('usergroup',$records['usergroup']);
                        // else
                        // $datasx = $this->session->set_userdata('level','ppda');
                        }





                        $entityname = $records['pdename'];
                        $adons  = date('d-m');

                        $level = "Procurement";

                        $titles = "Weekly  report on expiring IFBs of ".$entityname.$adons;
                        $body =  " ".html_entity_decode($table);
                        $permission = "view_bid_invitations";

                        $xcv = 0;


                        push_permission($titles,$body,$level,$permission,$records['pdeid']);

                        }
                        break;

                        default:
                        # code...
                        break;
                        #  exit();
                        }

                }


                    function notifyrop($bidinvitation)
                    {

                      $bidinvitation = $bidinvitation;
                      #################################################

                      # Get the passed details into the url data array if any
                      $urldata = $this->uri->uri_to_assoc(2, array('m', 'p'));

                      # Pick all assigned data
                      $data = assign_to_data($urldata);

                      $data = add_msg_if_any($this, $data);
                      #print_r($data); exit();

                      $data = handle_redirected_msgs($this, $data);

                      $search_str = '';
                      $level =$status = $this->uri->segment(3);
                      $data['level'] = $level;


                      $search_str = ' AND bidinvitations.id="'. $bidinvitation.'"';
                      $records  = paginate_list($this, $data, 'bid_invitation_details', array('orderby'=>'bid_dateadded DESC', 'searchstring'=>'bidinvitations.isactive = "Y"  AND bidinvitations.id not in (SELECT bid_id FROM receipts INNER JOIN bidinvitations ON receipts.bid_id =  bidinvitations.id  INNER JOIN bestevaluatedbidder
                      ON receipts.receiptid = bestevaluatedbidder.pid  WHERE receipts.beb="Y" ) '. $search_str),1);

                       #bid invitation details
                       $recorded_data = $records['page_list'][0];


                       #procurement type::
                       $procurementType = $recorded_data['procurement_type'];

                       if(($procurementType == 'Non consultancy services') || ($procurementType == 'Consultancy Services'))
                       {
                         $procurementType = "Services";
                       }
						
						 
                     #connect to ROP to fetch providers with that procurement method.
                     $this->load->model('Remoteapi_m');
                     $emaillist = $this->Remoteapi_m->group_emaillist_providers($procurementType);
 

                  $str= '<table>'.
                        '<tr> <th colspan="2"><h2> RE: BID INVITATION </h2> </th> </tr>'.
                        '<tr> <th> PROCUREMENT AND DISPOSING ENTITY </th> <td>'.$recorded_data['pdename'].'<td> </tr>'.
                        '<tr> <th> FINANCIAL YEAR </th> <td>'.$recorded_data['financial_year'].'<td> </tr>'.
                        '<tr> <th> PROCUREMENT REFERENCE NUMBER </th> <td>'.$recorded_data['procurement_ref_no'].'<td> </tr>'.
                        '<tr> <th> SUBJECT OF PROCUREMENT </th> <td>'.$recorded_data['subject_of_procurement'].'<td> </tr>'.
                        '<tr> <th> PROCUREMENT TYPE </th> <td>'.$recorded_data['procurement_type'].'<td> </tr>'.
                        '<tr> <th> PROCUREMENT METHOD </th> <td>'.$recorded_data['procurement_method'].'<td> </tr>'.
                        '<tr> <th> SOURCE OF FUNDING </th> <td>'.$recorded_data['funding_source'].'<td> </tr>'.
                        '<tr> <th>BID SUBMISSION DEADLINE </th> <td>'.date('m -d,Y',strtotime($recorded_data['bid_submission_deadline'])).'<td> </tr>'.

                       '</table>'.
                      ' NOTE : <BR/>'.
                      ' FOR MORE INFORMATION : ';

                 $strbody = html_entity_decode($str);
                 
                 $msg =  "<h2> PUBLIC NOTICE  <br/> PROCUREMENT TYPE ".$procurementType." </h2> <hr/>".$strbody."<br/> <br/>";
                 $msg .= "<ul>";
						 
			     $msg .= "<style>		
			     .dont-break-out {

 
						  overflow-wrap: break-word;
						  word-wrap: break-word;

						  -ms-word-break: break-all;

						  word-break: break-all;
						  word-break: break-word;

						  -ms-hyphens: auto;
						  -moz-hyphens: auto;
						  -webkit-hyphens: auto;
						  hyphens: auto;

						}
						
						</style>";
						 
                 


                     $this->email->from('noreply@ppda.go.ug', 'Tender Portal Notifications');
                     $this->email->to('noreply@gpp.ppda.go.ug');
					 //Blast Emails to All ROP providers 
					 $this->email->bcc($emaillist); 
                     $this->email->subject('RE: BID INVITATION');
                     $this->email->message(''.$strbody.'');

                    $this->email->send();
                    $msg .= "<li class='dont-break-out'>Provider Emails: <strong>".$emaillist."</strong> </li>";

                  #}

                  $msg .=   "</ul><br/><br/>Sent ON ".date("Y-M-d H:I:s");
						
				 #Echo the Message to The Sending 
				 if(!empty($msg))
					print_r($msg);
 
 
                }
					
				 

                function push_permission_ppda($title,$body,$level,$permission,$entity='')
                {

                $this->load->library('email');
                /* SAVE NOTIFICATION*/
                $title = mysql_real_escape_string($title);
                $body = mysql_real_escape_string($body);
                $level = mysql_real_escape_string($level);

                $data = array('TITLE' => $title,'BODY'=>$body,'ISACTIVE'=>'Y','LEVEL'=>$level);

                $query = $this->Query_reader->get_query_by_code('insert_notification', $data);

                if($query){
                $result = $this->db->query($query);
                $insertid = $this->db->insert_id();

                $pdeid =  $this->session->userdata('pdeid');


                $recipients = $this->notification_access($permission,$pdeid);

                $usergroup = $this->session->userdata('usergroup');
                $receipients_str = '';
                $receipients_str2 = '';

                $data = array('searchstring' => ' groupaccess.groupid ="14" ');

                $query = $this->Query_reader->get_query_by_code('notification_access', $data);

                $result = $this->db->query($query)->result_array();

                $emailarray = array();
                if(!empty($result))
                {
                foreach($result as $row)
                {
                if(in_array($row['emailaddress'], $emailarray))
                continue;

                $receipients_str .= (!empty($receipients_str)? '|' : '') . $row['userid'];
                $receipients_str2 .= (!empty($receipients_str2)? '|' : '') . $row['emailaddress'];

                array_push($emailarray,$row['emailaddress']);
                }

                $receipients_str = '|' . $receipients_str . '|';
                $receipients_str2 = '|' . $receipients_str2 . '|'; 
				
                }

                $recipients =    $receipients_str."<><><>".$receipients_str2; 
                $ni_recepient = explode("<><><>",$recipients);
                $r1 = $ni_recepient[0];
                $r2 = $ni_recepient[1];
                
                $recipients = rtrim($r1,'|');
                $recipientsarray = explode("|", $recipients);
                $emailarray = explode("|", $r2);
                


					for($x = 0; $x < count($recipientsarray); $x ++)
					{
						if(($recipientsarray[$x] == 0) || ($insertid == 0)) continue;

						$datar = array('RECEPIENTID' => $recipientsarray[$x],'NOTIFICATIONID'=>$insertid,'ISACTIVE'=>'Y');
						$query = $this->Query_reader->get_query_by_code('insert_notifications_recipients', $datar);
						$result = $this->db->query($query);
						# print_r($recipientsarray[$x]);

						$emailaddr = $emailarray[$x];
						if($emailaddr =='') continue;
						#$emailadress = array('RECEPIENTID' => $recipientsarray[$x],'NOTIFICATIONID'=>$insertid,'ISACTIVE'=>'Y');




						$this->email->from('noreply@tenderportal.ppda.go.ug', 'Tender Portal Notifications');
						$this->email->to(''.$emailaddr.'');

						//$this->email->cc('helpdesk@ppda.go.ug');
						#$this->email->bcc('helpdesk@ppda.go.ug');

						$this->email->subject(''.$title.'');
						$this->email->message(''.$body.'');

						$this->email->send();
					} 

                } 
        }
                
          //monthly reporting on expiring BEB                          
		function monthlyreportonexpiringcontracts($level)
		{

				$urldata = $this->uri->uri_to_assoc(3, array('m', 'p'));
				# Pick all assigned data
				$data = assign_to_data($urldata);

				$data = add_msg_if_any($this, $data);

				$data = handle_redirected_msgs($this, $data);



			   $search_str  ='';
				# code...

				$querys = $this->db->query("select distinct b.pdeid,b.pdename,a.* from pdes b inner join   users a on a.pde = b.pdeid  ")->result_array();
				 foreach($querys as $row => $records )
				{
				
				 $entityname = $records['pdename'];
					 $search_str = '';
					  $search_str .= 'AND PP.pde_id ='.$records['pdeid'].'  AND C.completion_date BETWEEN  CURDATE() AND DATE_ADD(CURDATE(), INTERVAL  10  DAY)';
					   $query = $this->Query_reader->get_query_by_code('get_published_contracts',  array('orderby'=>'C.date_signed DESC', 'limittext'=>'','searchstring'=>' AND PPE.isactive ="Y"  AND BI.isactive = "Y"  AND C.isactive="Y"' . $search_str));
					   $result = $this->db->query($query)->result_array(); 
						print_r($this->db->last_query());
						
						$str = '<style>.tablex tr th{text-align:left; background:#ccc; padding:20px; text-transform:uppercase;} .tablex tr td{border:1px solid #eee; padding:5px; font-size:15px;}</style> <div class="widget-body" id="results" style="width:100%;">';
			   
					if(!empty($result))
					{
						
										$str .= '<h2 style="width:100%; text-align:center; padding:5px;">CONTRACTS  SOON EXPIRING THIS MONTH FOR '.$records['pdename'].'</h2> <table class="table tablex table-striped table-hover" style="width:100%; padding:5px;">'.
											  '<thead>'.
											  '<tr style="width:100%; padding:5px; border:1px solid #eee;" >'.
											  '<th width="94px"></th>';
											if($this->session->userdata('isadmin') == 'N')
											{
											  $str  .= '<th> Procuring And Diposing Entity </th>';
											}
											  $str  .=   '<th>Date signed</th>'.
												'<th>Procurement Reference Number </th>'.
														  '<th>Subject of procurement</th>'.  
												'<th>Status</th>'.
												'<th style="text-align:right">Contract amount (UGX)</th>'.                    
												'<th class="hidden-480">Date added</th>'.
														  '</tr>'.
														  '</thead>'.
														  '</tbody>';
						
						foreach ($result as $key => $row) {
							# code...
					   
								 $edit_str = '';
								$delete_str = '';
												if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
											{
										$delete_str = '<a title="Delete contract details" href="javascript:void(0);" onclick="confirmDeleteEntity(\''.base_url().'contracts/delete_contract/i/'.encryptValue($row['id']).'\', \'Are you sure you want to delete this contract?\nClick OK to confirm, \nCancel to cancel this operation and stay on this page.\')"><i class="fa fa-trash"></i></a>';
										$termintate_str = '';
										
									  }else
									  {
										  $termintate_str = '<a href="'. base_url() .'contracts/contract_termination/i/'.encryptValue($row['id']) .'" title="Click to terminate contract"><i class="fa fa-times-circle"></i></a>';     
										$edit_str = '<a title="Edit contract details" href="'. base_url() .'contracts/contract_award_form/i/'.encryptValue($row['id']).'"><i class="fa fa-edit"></i></a>';
									  }
				  
										$status_str = '';
										$completion_str = '';
									  
									  if(!empty($row['actual_completion_date']) && str_replace('-', '', $row['actual_completion_date'])>0)
									  {
										$status_str = '<span class="label label-success label-mini">Completed</span>';
										$completion_str = '<a title="Click to view contract completion details" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']).'/v/'. encryptValue('view') .'"><i class="fa fa-eye"></i></a>';
									  }
									  else
									  {
										$status_str = '<span class="label label-warning label-mini">Awarded</span>';
										$completion_str = '<a title="Click to enter contract completion details"" href="'. base_url() .'contracts/contract_completion_form/c/'.encryptValue($row['id']) .'"><i class="fa fa-check"></i></a>';
									  }
								  

									$variations = ' <a class="view_variations" id="view_'.$row['id'].'" data-ref="'. base_url() .'contracts/contract_variation_view/i/'.encryptValue($row['id']) .'" title="Click to view Variations "><i class="fa fa-bars"></i></a> &nbsp; &nbsp; ';  
										 
									if(empty($row['actual_completion_date']) )
									 {
									$variations .= '<a href="'. base_url() .'contracts/contract_variation_add/i/'.encryptValue($row['id']) .'" title="Click to Add Variations "><i class="fa fa-plus-circle "></i></a> &nbsp; &nbsp;';
								   
									 }

											$more_actions = '<div class="btn-group" style="font-size:10px">
																 <a href="#" class="btn btn-primary">more</a><a href="javascript:void(0);" data-toggle="dropdown" class="btn btn-primary dropdown-toggle"><span class="fa fa-caret-down"></span></a>
																 <ul class="dropdown-menu">
																	 <li><a href="#"><i class="fa fa-times-circle"></i></a></li>
																	 <li class="divider"></li>
																	 <li>'. $completion_str .'</li>
																 </ul>
															  </div>';
				  
							$str  .=  '<tr>'.
											'<td>';
								  if($this->session->userdata('isadmin') == 'N')
									$str  .=   $delete_str .'&nbsp;&nbsp;'. $edit_str .'&nbsp;&nbsp;'. $termintate_str .' &nbsp; &nbsp; '.$completion_str.'&nbsp;&nbsp;'.$variations;;
								
										$str  .=  ' </td>';
										if($this->session->userdata('isadmin') == 'N')
										{
											  $str  .=    '<th> '.$row['pdename'].' </th>';
										}
											$str  .=   '<td>'. custom_date_format('d M, Y',$row['date_signed']) .'</td>'.
											'<td>'. format_to_length($row['procurement_ref_no'], 30) .'</td>'.
												'<td>'. format_to_length($row['subject_of_procurement'], 30).'';
								   if($row['pmethod'] == 'Framework and Special Contracts' )
									{
									 $str .= '<br/><a href="#" id="'.$row['id'].'" class="togglecalloforders"  > Add Call off Order </a> | <a href="#" data-procurement="'.$row['procurement_ref_no'].'" id="'.$row['id'].'" class="viewlistcalloff" >View Call off Orders </a>  </br/>';
									}

								  $str  .=  '</td>'.
								  '<td>'. $status_str .'</td>'.
									'<td style="text-align:right; font-family:Georgia; font-size:14px">'. addCommas($row['total_price'], 0) .'</td>'.
									'<td>'. custom_date_format('d M, Y', $row['dateadded']) .' by '. format_to_length($row['authorname'], 10) .'</td>'.
								  '</tr>';
				
						}
						
						$str  .=  '</tbody></table>';
				

					   
						}
						else
						{
						 $str .="<h2> No Records Expiring soon this month for ".$entityname." </h2>";
						}
						
				  
			   
		   $str  .=  '</div>';
				  

		 $adons  = ' | '.date('d-m-y');

				$level = "Procurement";

				$titles = "Monthly Report on Soon Expiring Contracts for  ".$entityname.'| '. $adons;
				
				$body =  " ".html_entity_decode($str);
				$permission = "view_bid_invitations";
			   $this-> push_permission($titles,$body,$level,$permission,$records ['pdeid']);
				
				echo $body;

			  


				}

			   

		}

 

		//monthly report on  published BEBs to Suspended Providers
		function awarded_beb_to_suspended_providers(){

		  $this->load->model('Remoteapi_m');

				$urldata = $this->uri->uri_to_assoc(4, array('m', 'i'));
				$data = assign_to_data($urldata);
				$data = add_msg_if_any($this, $data);
				$data = handle_redirected_msgs($this, $data);
				 
				$userid = $this->session->userdata['userid'];
				$pde = mysql_query("select * from  users where userid =".$userid);
				$q = mysql_fetch_array($pde);
		 
				$result = paginate_list($this, $data, 'view_bebs',  array('SEARCHSTRING' => '  and bidinvitations.isactive="Y" AND procurement_plan_entries.isactive = "Y"  and bidinvitations.id not in ( select bidinvitation_id FROM contracts  ) and    users.userid = '.$userid.' ORDER BY bestevaluatedbidder.dateadded DESC' ),100);                      
				 // $provider = '';
				$provider_selected = '';
				$contract_status = '';
				$visible = 0 ;

				//header information 
				  $st = '<div class="widget-body" id="results"><table class="table table-striped table-hover">'.
					'<thead>'.
					'<tr>'.            
					'<th>Procurement Ref Number</th>'.
					'<th class="hidden-480">Selected Provider</th>'.
					'<th class="hidden-480">Subject of Procurement</th>'.
					'<th class="hidden-480">Value</th>'.
					'<th>Status</th>'.
					'<th>Date Added</th>'.
					'</tr>'.
					'</thead>'.
					'<tbody>';
				//end of header information
				 foreach ($result['page_list'] as $key => $row) {            
					 
			  #   print_r($row);

					 $provider = rtrim($row['providers'],',');

					 $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();
					
					 $providerlist = '';
					 $x = 0;
		 
					 foreach($result as $key => $record){

					  $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
					  $provider_selected = str_replace('-', ' ',$record['providernames']);
					 # print_r($providerlist);   

					  /*get me the provider names and get me the date beb was added :*/
					  $provider = $provider_selected;
					  $dateadded =  $row['dateadded']; 
					 # print_r( $dateadded);  
					  $result_data = mysqli_fetch_array($this -> Remoteapi_m -> suspended_provider_betweendates($dateadded,$provider_selected)); 
						if(count($result_data) > 0 )
						{
							$contract_status = "Y";
							$visible = 1 ;
							break;
						}
				   

					 }
					 if($contract_status == "Y")  
					 {
						 //records fetch right here
						$bidd = $row['bid_id'];
					#print_r($row['bid_id']); exit();
					$st .=  '<tr> '. 
		 
					 '<td>'.$row['procurement_ref_no'].'</td>';
					 $provider = rtrim($row['providers'],',');

					 $result =   $this-> db->query("SELECT providernames FROM providers where providerid in(".$provider.")")->result_array();
					 $st .=  '<td class="hidden-480">';
					 $providerlist = '';
					 $x = 0;

					 foreach($result as $key => $record){
					  $providerlist .= $x > 0 ? $record['providernames'].',' : $record['providernames'];
					  $x ++ ;
					 }

					//print_r($providerlist);
					$providerlists = ($x > 1 )? rtrim($providerlist,',').' <span class="label label-info">Joint Venture</span> ' : $providerlist;

					$st .=  $providerlists.'</td>'.
					'<td class="hidden-480">'.$row['subject_of_procurement'].'</td>'.
					'<td class="hidden-480">'.number_format($row['contractprice']).$row['currency'].'</td>'.
					'<td>';
					if($row['isreviewed'] == 'Y')
					{
						 $st .= "<span class='label label-info minst'> Under Administrative Review </span>";
					}
				   else
					{
						 $st .= '-';
					}
					$st .=  ' </th>'.
					'<td>'.date('Y-M-d',strtotime($row['dateadded'])).'</th>'.
					'</tr>';
				}

					
					 $contract_status  = '';


				}
				  $st .=  '</tbody></table></div>';

				 if($visible ==  1)
				 {   
					//

					$adons  = ' | '.date('d-m-y');

				$level = "Procurement";

				$titles = "Awarded BEB to suspended provider ".'| '. $adons;
				
				$body =  " ".html_entity_decode($st);
				$permission = "awarded_beb_to_suspended_providers";
				$this-> push_permission_all($titles,$body,$level,$permission);
				
				echo $body;

				 #print_r($st);   
			  
				 }
			   




		}

 
				
	//Notify Entites without Procurement Plans in a given FInancial Year 			
    function  entities_without_plans()
    {
        $current_financial_year = currentyear.'-'.endyear;
        
                          
   
        $result = $this->db->query(" select distinct B.pdeid,B.pdename from pdes B inner join   users A on A.pde = B.pdeid       WHERE B.pdeid NOT IN(SELECT DISTINCT procurement_plans.pde_id  FROM procurement_plans  WHERE procurement_plans.isactive ='Y'  and procurement_plans.financial_year = '".$current_financial_year."' ) ")->result_array();

 
       if(!empty($result))
       {
         
            foreach($result as $row => $records )
            {
                $str = '<style>.tablex tr th{text-align:left; background:#ccc; padding:20px; text-transform:uppercase;} .tablex tr td{border:1px solid #eee; padding:5px; font-size:15px;}</style> <div class="widget-body" id="results" style="width:100%;">';
  
                 $str .=  "<h2>NOTIFICATION ABOUT PROCUREMENT PLAN </h2>";
                 $str .=  " This serves to inform you that procurement plan   ".$current_financial_year." for  ".$records['pdename']." has not yet been uploaded, so the concerned parties are advised to upload to the GPP portal ";
                 $str .=  "<br/><b> From the GPP Team </b> ";
                 $str .=  "</div>";
                  
                  
                 $entityname = $records['pdename'];
                 $adons  = date('d-m-Y-h');
                 $level = "Procurement";
                 $titles = "Notification about Not Yet Uploaded Procurement Plan for  ".$entityname." Financial Year ".$current_financial_year." Date ".$adons;
                 $body =  " ".html_entity_decode($str);
                 $permission = "view_bid_invitations";
                 push_permission($titles,$body,$level,$permission,$records['pdeid']);
				 echo  $body;
                 
            }
        }

   
   }
   

                function weeklybebreport($level)
                {
                    
                    switch ($level) {
                        case 'ppda':
                            # code...
                        //fetch beb weekly report :: 
                         $urldata = $this->uri->uri_to_assoc(3, array('m'));
                        # Pick all assigned data
                         $data = assign_to_data($urldata);
                          $searchstring = '';
                         $querys = $this->db->query("select distinct b.pdeid,b.pdename,a.* from pdes b inner join   users a on a.pde = b.pdeid  ")->result_array();

                        foreach($querys as $row => $records )
                        {
                          #get the PDE ID " Idividual Pde Ids ";"
                         $searchstring = ' AND a.pde_id='. $records['pdeid'] .'  AND ( bestevaluatedbidder.beb_expiry_date > DATE_SUB(NOW() - INTERVAL 7 DAY , INTERVAL 1 WEEK) AND  bestevaluatedbidder.beb_expiry_date <= CURDATE() ) ';
                        
                        $query = $this->Query_reader->get_query_by_code('fetchbebs', array('SEARCHSTRING' => $searchstring.' and bestevaluatedbidder.ispublished = "Y"  and  receipts.beb="Y" order by bestevaluatedbidder.dateadded DESC','limittext'=>''));
                       # print_r($query);
                      #  exit();
                         $result = $this ->db->query($query)->result_array();

                         if(!empty($result))
                         {
                           
                           
                      $st =    ' <table><tr>
                                     <th> <b>Date Posted</b> </th> <th >  <b>Procuring/Disposing Entity</b></th>
                                     <th> <b>Procurement Reference Number</b> </th>
                                     <th> <b>Selected Provider</b> </th> <th > <b>Subject </b> </th>
                                     <th> <b>Date BEB Expires</b>  </th> <th > <b>Status</b> </th><th> <b>BEB Price </b></th>
                                     </tr>';
                           


                                        foreach ($result as $key => $row) {
                                            # code...
                                             
                                           $st .= '<tr><td >'.
                                                custom_date_format('d M, Y', $row['dateadded']).
                                            '</td> <td class="col-md-2">'.$row['pdename'].
                                            '</td><td class="col-md-2">'.$row['procurement_ref_no'].
                                           ' </td> <td class="col-md-2">';
                                         

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
                        $st .=$provider.'&nbsp; '.$label;
                    $x  = 0 ;
                    $label = '';
                    }
                                     else{  $st .=$row['providernames'];}

                                    
                                           $st .= '</td> <td class="col-md-1">'.$row['subject_of_procurement'].
                                           ' </td> <td class="col-md-1">';
                                           $st .= $row['beb_expiry_date'].
                                         //              date("d M, Y",strtotime($row['beb_expiry_date'])).
                                            '</td> <td class="col-md-1">';
                                               

                                                  switch($row['isreviewed'])
                                                        {

                                                          case 'Y':
                                                          $st .=" <span class='label label-info '> For Admin Review </span>  <br/> <span class='label label-success'>".$row['review_level']." </span> <br/>";
                                                        #  print "<span class='label label-info'".$row['review_level']."</span>";
                                                          //class="label label-info"
                                                          break;


                                                          case 'N':
                                                            $st .=" <span class='btn btn-xs btn-success'> Active </span>";
                                                    
                                                          break;


                                                          default:
                                                           $st .="-";
                                                          break;
                                                        }

                                              
                                          $st .= '</td>   <td class="col-md-2">';
                                               

                                                 $readout = mysql_query("SELECT * FROM readoutprices WHERE receiptid=".$row['receiptid']."");
                                            
                                            if(mysql_num_rows($readout) > 0 )
                                            {
                                              $st .="<ul>";
                                              while ( $valsue = mysql_fetch_array($readout)) {
                                                if($valsue['readoutprice']<=0)
                                                  continue;
                                                # code...
                                                  $st .= "<li>".number_format($valsue['readoutprice']).$valsue['currence']."</li>";
                                              }
                                               $st .= "</ul>";
                                            }



                                                
                                           $st .= ' </td> </tr>';
                                            
                                        }



                $st .='</table></div>';

                print_r($st);

                //push notificationss ::

                 $entity =  $records['pdeid'];


                        $this->session->set_userdata('pdeid',$entity);
                        if($records['usergroup'] > 0){
                        $level =$records['usergroup'];
                        $this->session->set_userdata('usergroup',$records['usergroup']);
                        // else
                        // $datasx = $this->session->set_userdata('level','ppda');
                        }





                        $entityname = $records['pdename'];
                        $adons  = date('d-m');

                        $level = "Procurement";

                        $titles = "Weekly  report on expiring Best Evaluated Bidders of  ".$entityname.$adons;
                        $body =  " ".html_entity_decode($st);
                        $permission = "view_bid_invitations";

                        $xcv = 0;


                        push_permission($titles,$body,$level,$permission,$records['pdeid']);



                         
                         }
                         else
                         {

                         }
                          // print_r($result); 
                           
                          
                          }
                        
                            break;
                        
                        default:
                            # code...
                            break;
                    }
                    
                }


                }