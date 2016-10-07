<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 10/15/14
 * Time: 7:53 AM
 */
class Home extends CI_Controller
{

    function __construct()
    {
        //load ci controller
        parent::__construct();
   $this->load->model('Remoteapi_m');  

    }

    //admin home page
    function index()
    {
        $data['title']='National Tender Portal';

        $this->load->view('public/home_v', $data);

    }
     function beb(){
     exit('reachec');
     $data['receiptinfo'] =   $this-> Receipts_m -> fetchbeb();
     $data['title']='National Tender Portal';
     $this->load->view('public/beb_v', $data);        
    }

}