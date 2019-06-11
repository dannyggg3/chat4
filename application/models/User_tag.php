<?php
class User_tag extends CP_Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = TABLE_USER_TAGS;
    }
    
    /*
     * To save tags associated with a user
     * 
     * @param: $user_id (id of the user to associate the tags with)
     * 
     */
    public function save($user_id) {
        $this->db->delete($this->table, array('user_id' => $user_id));
        
        if($this->model_data['tags'] and is_array($this->model_data['tags'])){
            $insertData = array();
            foreach ($this->model_data['tags'] as $row){
                if($row){
                    $insertData[] = array('user_id' => $user_id, 'tag_id' => $row);
                }
            }

            if(count($insertData) > 0){
                $this->db->insert_batch($this->table, $insertData);
            }
        }
    }
    
    /*
     * To pull all tags along with their tag name associated with a user
     * 
     * @param: $user_id (id of the user tags are associated with)
     * 
     * @return 
     *      array of objects (tag_id, tag_name)
     */
    public function get_user_tags($user_id) {
        $tags = $this->db->select('chatbull_user_tags.*, chatbull_tags.tag_name')
                    ->where('chatbull_user_tags.user_id', $user_id)
                    ->from('chatbull_user_tags')
                    ->join('chatbull_tags', 'chatbull_tags.id = chatbull_user_tags.tag_id')
                    ->get()
                    ->result();
        
        return $tags;
    }
}