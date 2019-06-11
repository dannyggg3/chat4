<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Token_model extends CP_Model {

    //model db table
    var $table = "";
    var $validation_rules = array();

    /*
     * construct function 
     * defins model table name
     */

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->table = TABLE_ACCESS_TOKEN;

        $this->validation_rules[] = array(
            'field' => 'site_url',
            'label' => 'lang:site_url',
            'rules' => 'required|callback__domain_check'
        );
    }

    /*
     * To create new entry of resource
     * 
     * @return new insert id 
     */

    public function create() {
        $this->model_data['created_at'] = date("Y-m-d H:i:s", now());

        $this->db->insert($this->table, $this->model_data);
        return $this->db->insert_id();
    }

    /*
     * To return all record from database
     */
    function get_tokens() {
        $query = $this->db->get($this->table);
        
        return $query->result();
    }

    /*
     * To validate token in databse
     * 
     * @param (string) $token
     * @return $response
     */

    function is_valid($token) {
        $query = $this->db->get_where($this->table, array('token' => $token));
        $response = $query->row();

        return $response;
    }

}
