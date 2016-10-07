<?php

/*
@mover 1st jan Newwvetech
VALIDATION OF ANY KIND NIN THIS MODEL:: SERVER SIDE VALIDATION :: 
*/
class Validation_m extends CI_Model
{
 	function __construct()
    {
    	$this->load->model('Query_reader', 'Query_reader', TRUE);
        parent::__construct();
    }


    #FUCNTION TO VALIDATE  USER FOR A GIVEN PDE::
    /*
    1 :: CHECK  IF USER EXISTS  ROLES TABLE AND IF HE IS ASSOCIATED TO A NOTHER PDE:
    2 :: CHECK IF A GIVEN ROLE IS NOT YET ASSIGNED TO THE USER  ON A GIVEN PDE 
    REQUIREMENTS :: INSERT_ID OR PDEID IN CASE OF EDIT [PDE] ...
    CCID
    AOID
    PDUID
    USERID 
    */
    function validatepderole($pdeid,$post,$step)
    {
          //check if user id exists in roles where not pde not = pdeid :
        //and userid in($cc,$ao,$pdu) 
    // CHECK IF PDE EXISTS IN ROLES TABLE
        /*
        If it has Ever been Assigned to the Roles Table :: 
        */



    $logerror = '';
    $erod = 1;
    // $usrs = array($cc,$ao,$pdu);
    // $elemnt  = array($ccid,$aoid,$pduid);

 
    $query = mysql_query("SELECT a.* FROM roles as a inner join users as b on a.userid = b.userid where b.pde = $pdeid and b.pde > 0  ") or die("".mysql_error()); 
    $query2 = mysql_query("SELECT a.* FROM roles as a inner join users as b on a.userid = b.userid where  b.pde != $pdeid and b.pde > 0 ") or die("".mysql_error());
    $count = mysql_num_rows($query);
    $count2 = mysql_num_rows($query2);

        switch ($step) {
            case 'insert':
                # code...
             
  
    // > 0 means it has already been assigned to  some one :: 
	    if($count > 0)
	    {
	     //CHECK CC
	    	$erod = 1;
	    	$x = 0;
	    	foreach ($post as $key => $value) {
	# code...
               $dt = explode("_", $key);
    	    	$record = mysql_query("SELECT a.* FROM roles as a inner join users as b on a.userid = b.userid where a.groupid =  $dt[1] and a.userid = $value and  b.pde = $pdeid and b.pde > 0 ") or die("".mysql_error()); 
    		    if(mysql_num_rows( $record) > 0)
    		    {
    		    	//this user cant pass
    		    	$query = mysql_query("SELECT * FROM users where userid = $value limit 1") or die ("".mysql_error());
    		    	$data = mysql_fetch_array($query);
    		    	
    		    	$logerror .=" USERNAMES : ".$data['firstname']." ".$data['lastname'];
    		    	$erod = 0;
                    break;

    		    }

            }
		    

	    }
//get to see if there already roles assigned in different pdes 
    else if($count2 > 0)
        {
         //CHECK CC
            
            $x = 0;
            foreach ($post as $key => $value) {
    # code...
                 $dt = explode("_", $key);

                $record = mysql_query("SELECT a.* FROM roles as a inner join users as b on a.userid = b.userid where  a.userid = $value and  b.pde != $pdeid and b.pde > 0 ") or die("".mysql_error()); 
                if(mysql_num_rows( $record) > 0)
                {
                    //this user cant pass
                    $query = mysql_query("SELECT * FROM users where userid = $value limit 1") or die ("".mysql_error());
                    $data = mysql_fetch_array($query);
                    
                    $logerror .=" USERNAMES : ".$data['firstname']."".$data['lastname'];
                    $erod = 2;
                    break;

                }

            }
            

        }
	    else
	    {
	    	$erod = 1;


	    }

	    $result = $erod.':'.$logerror;
	    return $result;
           break;
           case 'update':

           if($count2 > 0)
        {
             
         //CHECK CC
            
            $x = 0;
            foreach ($post as $key => $value) {
    # code...
                 $dt = explode("_", $key);

                $record = mysql_query("SELECT a.* FROM roles as a inner join users as b on a.userid = b.userid where  a.userid = $value and  b.pde != $pdeid  and b.pde > 0 ") or die("".mysql_error()); 
                if(mysql_num_rows( $record) > 0)
                {
                    //this user cant pass
                    $query = mysql_query("SELECT * FROM users where userid = $value limit 1") or die ("".mysql_error());
                    $data = mysql_fetch_array($query);
                    
                    $logerror .=" USERNAMES : ".$data['firstname']." ".$data['lastname'];
                    $erod = 2;
                    break;

                }

            }
            

        }
        else
        {
            $erod = 1;

        }
         $result = $erod.':'.$logerror;
        return $result;

           break;
            
            default:
                # code...
                break;
        }
    }
    

}
?>