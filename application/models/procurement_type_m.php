<?php
class Procurement_type_m extends MY_Model
{
    public $_tablename='procurement_types';
    public $_primary_key='id';
    function __construct()
    {
        parent::__construct();

        $this->load->model('users_m');

    }

    //visible and trashed
    public function get_all()//visible is either y or n
    {
        $this->db->cache_on() ;
        $query=$this->db->select()->from($this->_tablename)->order_by($this->_primary_key,'DESC')->get();
        //echo $this->db->last_query();
        return $query->result_array();
    }

       //get procurement method details
    function get_procurement_type_info($id='',$param='')
    {
        if($id=='')
        {
            return NULL;
        }
        else
        {
            $this->db->cache_on() ;
           $query=$this->db->select()->from($this->_tablename) ->where($this->_primary_key,$id)->get();
        }

        if($query->result_array())
        {
            foreach($query->result_array() as $row)
            {
                switch($param)
                {
                    case 'title':
                        $result=$row['title'];
                        break;
                    case 'author_id':
                        $result=$row['author'];
                        break;
                    case 'author':
                        $result=get_user_info($row['author'],'fullname');
                        break;
                    case 'isactive':
                        $result=$row['active'];
                        break;
                    case 'dateadded':
                        $result=$row['dateadded'];
                        break;
                    case 'evaluation_time':
                        $result=$row['evaluation_time'];
                        break;
                    default:
                        $result=$query->result_array();
                }
            }

            return $result;
        }
        else
        {
           return NULL;
        }
    }




    //get by passed parameters
    public function get_where($where)
    {
        $this->db->cache_on() ;
        $query=$this->db->select()->from($this->_tablename)->where($where)->order_by($this->_primary_key,'DESC')->get();

        return $query->result_array();
    }

     function get_procurementtype_list()
     {
      $query = $this->Query_reader->get_query_by_code('fetch_all_procurement_types', array('SEARCHSTRING' => '') );              
      $result = $this->db->query($query)->result_array();
      return $result;    

       
      }

       #FETCH FUNDING SOURCE
      function get_fundingsource_list()
      {
      $query = $this->Query_reader->get_query_by_code('funding_source_list', array('SEARCHSTRING' => '') );              
      $result = $this->db->query($query)->result_array();
      return $result;  
      }

       #FETCH PROCUREMENT METHOND LIST
      function get_procurement_method_list()
      {
      $query = $this->Query_reader->get_query_by_code('fetch_procurement_method_list', array('SEARCHSTRING' => '') );              
      $result = $this->db->query($query)->result_array();
      return $result; 
      }

          #FETCH FINANCIAL YEAR  METHOND LIST
      function fetch_financialyears_list()
      {
      $query = $this->Query_reader->get_query_by_code('fetch_financialyears_list', array('SEARCHSTRING' => '') );              
      $result = $this->db->query($query)->result_array();
      return $result; 
      }
      
        function fetch_disposal_methods()
      {
        #  $query = $this->Query_reader->get_query_by_code('fetch_financialyears_list', array('SEARCHSTRING' => '') );              
          $result = $this->db->query("SELECT * FROM  disposal_method ")->result_array();
          return $result; 
      }

       function fetch_disposal_years()
      {
        #  $query = $this->Query_reader->get_query_by_code('fetch_financialyears_list', array('SEARCHSTRING' => '') );              
          $result = $this->db->query("SELECT distinct disposal_plans.financial_year  FROM  disposal_plans ")->result_array();
          return $result; 
      }

      
      


      








}