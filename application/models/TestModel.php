<?php


class TestModel extends CI_Model
{

    function __construct(){

        parent::__construct();

    }

    function getMarketAPIAccessKey($param){

        $this -> db -> select('value');
        $this -> db -> from('test_setting');
        $this -> db -> where('mall_id', $param['mall_id']);
        $this -> db -> where('code', $param['code']);
        $this -> db -> limit('1');

        $query = $this -> db -> get();

        return $query -> result_array();
    }

    function insertItemList($data){

        $this->db->insert('insert_item_list',$data);

        return ($this->db->affected_rows() != 1) ? false : true;
    }


}