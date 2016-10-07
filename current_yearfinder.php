<?php
//logic to help get the current financial year :: 
$currentyear = date('Y');
$currentmonth = date('m');
if($currentmonth >= 7)
{
	 define('currentyear',date('Y'));
	 $endyear = currentyear+1;
	 define('endyear',$endyear);

}
 
else
{

	 define('currentyear',$currentyear -1);	 
	 define('endyear',$currentyear);
}
 
// print_r(currentyear.'-'.endyear);
// exit();
 define('startmonthname','july');
 define('startmonthnumber','7');

 define('endmonthname','july');
 define('endmonthnumber','7');

 
//  print_r(endmonthnumber);
// exit();