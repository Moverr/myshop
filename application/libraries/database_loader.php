<?php
/**
 * Created by PhpStorm.
 * User: cengkuru
 * Date: 5/5/2015
 * Time: 12:53 PM
 */
/**
 * A simple class which handles loading multiple-databases in codeigniter
 */

class Database_loader
{

    public function __construct()
    {
        $this->load();
    }

    /**
     * Load the databases and ignore the old ordinary CI loader which only allows one
     */
    public function load()
    {
        $CI =& get_instance();

        $CI->db = $CI->load->database('default', TRUE);
        $CI->db2 = $CI->load->database('rop', TRUE);
    }
}