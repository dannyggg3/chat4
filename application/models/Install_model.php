<?php

class Install_model extends CP_Model {

    /**
     * Constructor
     * 
     * @access	public
     * @return	void
     */
    function __construct() {
        parent::__construct();

        $this->sql_path = APPPATH . 'models/installer.sql';

        // load the file helper
        $this->load->helper('file');
    }

    /*
     * This function run database sql file
     * 
     * @return true or false
     */
    public function use_sql_string() {
        $sql = read_file($this->sql_path);
        // Trim it
        $sql = trim($sql);
        return mysqli_multi_query($this->db->conn_id, $sql);
    }

    /*
     * To save user's data
     */

    public function save_admin() {
        $query = $this->db->get_where(TABLE_USERS, array('email' => $this->model_data['email']));
        $admin = $query->row();
        
        if($admin) {
            $this->model_data['modified_at'] = date("Y-m-d H:i:s", now());
            $this->db->update(TABLE_USERS, $this->model_data, array("id" => $admin->id));
            return true;
        } else {
            $this->model_data['created_at'] = date("Y-m-d H:i:s", now());
            $this->model_data['modified_at'] = date("Y-m-d H:i:s", now());

            $this->db->insert(TABLE_USERS, $this->model_data);
            return $this->db->insert_id();
        }
    }
}
