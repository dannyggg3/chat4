<?php

class Chat_session extends CP_Model {

    //model db table
    var $table = "";

    /*
     * define construct function
     */

    public function __construct() {
        parent::__construct();
        $this->table = TABLE_CHAT_SESSIONS;
    }

    /*
     * This function will return chat list for a user.
     * 
     * @param $user_id (ID of user)
     * 
     * @return $chat_list (List of chat of user)
     */

    function get_chat_list($user_id, $d = '', $last_id = 0) {
        $output = array('last_id' => $last_id, 'chat_list' => array());
        $chat_ids = array();
        $chat_list = array();

        //gettting chat sessions ids
        $chats = $this->db->select(TABLE_CHAT_USERS . '.chat_session_id')
                ->where(TABLE_CHAT_SESSIONS . '.id > ', $last_id)
                ->where(TABLE_CHAT_USERS . '.user_id', $user_id)
                ->where(TABLE_CHAT_USERS . '.user_state', 'in-chat')
                ->where_in(TABLE_CHAT_SESSIONS . '.session_status', array('open', 'closed', 'disconnected', 'on-hold'))
                ->from(TABLE_CHAT_USERS)
                ->join(TABLE_CHAT_SESSIONS, TABLE_CHAT_SESSIONS . '.id = ' . TABLE_CHAT_USERS . '.chat_session_id')
                ->get()
                ->result();

        foreach ($chats as $chat) {
            $chat_ids[] = $chat->chat_session_id;
            $output['last_id'] = $chat->chat_session_id;
        }

        if (count($chat_ids) > 0) {
            $messages = array();
            $unread_messages = $this->db->select('count( ' . TABLE_CHAT_MESSAGES . '.id ) AS total, ' . TABLE_CHAT_MESSAGES . '.chat_session_id , ' . TABLE_CHAT_MESSAGES . '.sender_id ')
                    ->where_in(TABLE_CHAT_MESSAGES . '.chat_session_id', $chat_ids)
                    ->where(TABLE_CHAT_MESSAGES . '.message_status', 'unread')
                    ->from(TABLE_CHAT_MESSAGES)
                    ->join(TABLE_USERS, TABLE_USERS . '.id = ' . TABLE_CHAT_MESSAGES . '.sender_id')
                    ->group_by(TABLE_CHAT_MESSAGES . ".sender_id")
                    ->group_by(TABLE_CHAT_MESSAGES . ".chat_session_id")
                    ->order_by(TABLE_CHAT_MESSAGES . ".chat_session_id", "asc")
                    ->get()
                    ->result();

            foreach ($unread_messages as $message) {
                $messages[$message->chat_session_id][$message->sender_id] = $message->total;
            }

            // fetching all active chat listing
            $list = $this->db->select(TABLE_CHAT_SESSIONS . '.id, max(' . TABLE_CHAT_MESSAGES . '.id) as message_id, ( select sqm.chat_message from ' . TABLE_CHAT_MESSAGES . ' sqm where sqm.id = max(' . TABLE_CHAT_MESSAGES . '.id) ) as chat_message, ' . TABLE_USERS . '.id as uid, ' . TABLE_USERS . '.name, ' . TABLE_USERS . '.email, ' . TABLE_USERS . '.profile_pic, ' . TABLE_CHAT_MESSAGES . '.message_status')
                    ->where_in(TABLE_CHAT_SESSIONS . '.id', $chat_ids)
                    //->where_in(TABLE_USERS . '.role', array('visitor', 'agent'))
                    ->where(TABLE_USERS . '.id !=', $user_id)
                    ->from(TABLE_CHAT_MESSAGES)
                    ->join(TABLE_CHAT_SESSIONS, TABLE_CHAT_SESSIONS . '.id = ' . TABLE_CHAT_MESSAGES . '.chat_session_id')
                    ->join(TABLE_USERS, TABLE_USERS . '.id = ' . TABLE_CHAT_MESSAGES . '.sender_id')
                    ->group_by(TABLE_CHAT_MESSAGES . ".chat_session_id")
                    ->order_by("message_id", "desc")
                    //->order_by(TABLE_CHAT_MESSAGES . ".created_at", "desc")
                    ->get()
                    ->result();

            foreach ($list as $key => $row) {
                $chat_list[$key]['id'] = $row->id;
                $chat_list[$key]['messageId'] = $row->message_id;
                $chat_list[$key]['name'] = $row->name;
                $chat_list[$key]['chatMessage'] = $row->chat_message;
                $chat_list[$key]['unread'] = 0;
                $chat_list[$key]['profilePic'] = $this->media->get_thumbnail($row->profile_pic, PROFILE_PICS, $row->email, $d);

                if (isset($messages[$row->id]) and isset($messages[$row->id][$row->uid])) {
                    $chat_list[$key]['unread'] = $messages[$row->id][$row->uid];
                }
            }

            $output['chat_list'] = $chat_list;
        }

        return $output;
    }

    /*
     * This function will return recent chats list for a user.
     * 
     * @param $user_id (ID of user)
     * 
     * @return $chat_list (List of chat of user)
     */

    function get_recent_chats($user_id, $d = '') {
        $chat_ids = array();
        $chat_list = array();

        //gettting chat sessions ids
        $chats = $this->db->select(TABLE_CHAT_USERS . '.chat_session_id')
                ->where(TABLE_CHAT_USERS . '.user_id', $user_id)
                ->where(TABLE_CHAT_USERS . '.user_state', 'in-chat')
                ->where_in(TABLE_CHAT_SESSIONS . '.session_status', array('open', 'disconnected', 'on-hold'))
                ->from(TABLE_CHAT_USERS)
                ->join(TABLE_CHAT_SESSIONS, TABLE_CHAT_SESSIONS . '.id = ' . TABLE_CHAT_USERS . '.chat_session_id')
                ->get()
                ->result();

        foreach ($chats as $chat) {
            $chat_ids[] = $chat->chat_session_id;
        }

        if (count($chat_ids) > 0) {
            $messages = array();
            $unread_messages = $this->db->select('count( ' . TABLE_CHAT_MESSAGES . '.id ) AS total, ' . TABLE_CHAT_MESSAGES . '.chat_session_id , ' . TABLE_CHAT_MESSAGES . '.sender_id ')
                    ->where_in(TABLE_CHAT_MESSAGES . '.chat_session_id', $chat_ids)
                    ->where(TABLE_CHAT_MESSAGES . '.message_status', 'unread')
                    ->from(TABLE_CHAT_MESSAGES)
                    ->join(TABLE_USERS, TABLE_USERS . '.id = ' . TABLE_CHAT_MESSAGES . '.sender_id')
                    ->group_by(TABLE_CHAT_MESSAGES . ".sender_id")
                    ->group_by(TABLE_CHAT_MESSAGES . ".chat_session_id")
                    ->order_by(TABLE_CHAT_MESSAGES . ".chat_session_id", "asc")
                    ->get()
                    ->result();

            foreach ($unread_messages as $message) {
                $messages[$message->chat_session_id][$message->sender_id] = $message->total;
            }

            // fetching all active chat listing
            $list = $this->db->select(TABLE_CHAT_SESSIONS . '.id, max(' . TABLE_CHAT_MESSAGES . '.id) as message_id, ( select sqm.chat_message from ' . TABLE_CHAT_MESSAGES . ' sqm where sqm.id = max(' . TABLE_CHAT_MESSAGES . '.id) ) as chat_message, ' . TABLE_USERS . '.id as uid, '
                            . TABLE_USERS . '.name, '
                            . TABLE_USERS . '.email, '
                            . TABLE_USERS . '.profile_pic, '
                            . TABLE_CHAT_MESSAGES . '.message_status, '
                            . TABLE_USER_VISIT_INFO . '.ip_address, '
                            . TABLE_USER_VISIT_INFO . '.page_title, '
                            . TABLE_USER_VISIT_INFO . '.page_url'
                    )
                    ->where_in(TABLE_CHAT_SESSIONS . '.id', $chat_ids)
                    //->where_in(TABLE_USERS . '.role', array('visitor', 'agent'))
                    ->where(TABLE_USERS . '.id !=', $user_id)
                    ->from(TABLE_CHAT_MESSAGES)
                    ->join(TABLE_CHAT_SESSIONS, TABLE_CHAT_SESSIONS . '.id = ' . TABLE_CHAT_MESSAGES . '.chat_session_id')
                    ->join(TABLE_USERS, TABLE_USERS . '.id = ' . TABLE_CHAT_MESSAGES . '.sender_id')
                    ->join(TABLE_USER_VISIT_INFO, TABLE_USER_VISIT_INFO . ".request_id = " . TABLE_CHAT_SESSIONS . ".id and " . TABLE_USER_VISIT_INFO . ".request_type = 'online'", "left")
                    ->group_by(TABLE_CHAT_MESSAGES . ".chat_session_id")
                    ->order_by("message_id", "desc")
                    ->get()
                    ->result();

            foreach ($list as $key => $row) {
                $chat_list[$key]['id'] = $row->id;
                $chat_list[$key]['message_id'] = $row->message_id;
                $chat_list[$key]['name'] = $row->name;
                $chat_list[$key]['email'] = $row->email;
                $chat_list[$key]['ip_address'] = $row->ip_address;
                $chat_list[$key]['page_title'] = $row->page_title;
                $chat_list[$key]['page_url'] = $row->page_url;
                $chat_list[$key]['chat_message'] = $row->chat_message;
                $chat_list[$key]['unread'] = 0;
                $chat_list[$key]['profile_pic'] = $row->profile_pic;
                $chat_list[$key]['profile_picture'] = $this->media->get_thumbnail($row->profile_pic, PROFILE_PICS, $row->email, $d);

                if (isset($messages[$row->id]) and isset($messages[$row->id][$row->uid])) {
                    $chat_list[$key]['unread'] = $messages[$row->id][$row->uid];
                }
            }
        }

        return $chat_list;
    }

    /*
     * This function will return closed chats list for a user.
     * 
     * @param $user_id (ID of user)
     * 
     * @return $chat_list (List of chat of user)
     */

    function get_closed_chats($user_id, $excepts = array(), $item_per_page = 0) {
        $chat_ids = array();
        $closed_chats = array();
        
        if($item_per_page == 0) {
            $item_per_page = $this->item_per_page;
        }

        //gettting chat sessions ids
        $query = $this->db->select(TABLE_CHAT_USERS . '.chat_session_id');

        if ($excepts) {
            $query->where_not_in(TABLE_CHAT_USERS . '.chat_session_id', $excepts);
        }

        $query->where(TABLE_CHAT_USERS . '.user_id', $user_id);
        $query->where(TABLE_CHAT_USERS . '.user_state', 'in-chat');
        $query->where_in(TABLE_CHAT_SESSIONS . '.session_status', array('closed'));
        
        $chats = $query->from(TABLE_CHAT_USERS)
                ->join(TABLE_CHAT_SESSIONS, TABLE_CHAT_SESSIONS . '.id = ' . TABLE_CHAT_USERS . '.chat_session_id')
                ->order_by(TABLE_CHAT_USERS . '.chat_session_id', "desc")
                ->limit($item_per_page, 0)
                ->get()
                ->result();

        foreach ($chats as $chat) {
            $chat_ids[] = $chat->chat_session_id;
        }

        if (count($chat_ids) > 0) {
            // fetching all active chat listing
            $closed_chats = $this->db->select('sess.id, max(msg.id) as message_id, ( select sqm.chat_message from ' . TABLE_CHAT_MESSAGES . ' sqm where sqm.id = max(msg.id) ) as chat_message, ' . 'user.id as sender_id, '
                            . 'user.name, '
                            . 'user.email, '
                            . 'user.profile_pic, '
                            . "CASE WHEN user.profile_pic != '' THEN CONCAT('" . base_url(UPLOAD_DIR . PROFILE_PICS) . "thumb/', user.profile_pic) END  as profile_picture, "
                            . 'msg.message_status, '
                            . 'tag.tag_name as department, '
                            . 'visit_info.ip_address, '
                            . 'visit_info.page_title, '
                            . 'visit_info.page_url'
                    )
                    ->where_in('sess.id', $chat_ids)
                    ->where('user.id !=', $user_id)
                    ->from(TABLE_CHAT_MESSAGES . ' msg')
                    ->join(TABLE_CHAT_SESSIONS . ' sess', 'sess.id = ' . 'msg.chat_session_id')
                    ->join(TABLE_USERS . ' user', 'user.id = ' . 'msg.sender_id')
                    ->join(TABLE_TAGS . ' tag', 'tag.id = ' . 'sess.requested_tag', 'left')
                    ->join(TABLE_USER_VISIT_INFO . ' visit_info', "visit_info.request_id = " . "sess.id and " . "visit_info.request_type = 'online'", "left")
                    ->group_by("msg.chat_session_id")
                    ->order_by("message_id", "desc")
                    ->get()
                    ->result();

            /* foreach ($list as $key => $row) {
              $chat_list[$key]['id'] = $row->id;
              $chat_list[$key]['messageId'] = $row->message_id;
              $chat_list[$key]['name'] = $row->name;
              $chat_list[$key]['email'] = $row->email;
              $chat_list[$key]['ipAddress'] = $row->ip_address;
              $chat_list[$key]['panelName'] = $row->page_title;
              $chat_list[$key]['panelAddress'] = $row->page_url;
              $chat_list[$key]['department'] = $row->tag_name;
              $chat_list[$key]['chatMessage'] = $row->chat_message;
              $chat_list[$key]['unread'] = 0;
              $chat_list[$key]['profile_pic'] = $row->profile_pic;
              $chat_list[$key]['profilePic'] = $this->media->get_thumbnail($row->profile_pic, PROFILE_PICS, $row->email);
              } */

            //$output['chat_list'] = $list;
        }

        return $closed_chats;
    }

    /*
     * This function will return chat list for a user.
     * 
     * @param $filters
     * 
     * @return $chat_list (List of chat of user)
     */

    function get_chat_session($filters = array()) {
        // select fileds
        $select = 'sess.id, '
                . 'sess.requested_tag, '
                . 'sess.session_status, '
                . 'sess.session_type, '
                . 'msg.chat_message, '
                . 'visitor.profile_pic, '
                . 'visitor.id as visitorId, '
                . 'visitor.name as visitorName, '
                . 'visitor.role as visitorRole, '
                . 'visitor.email as visitorEmail, '
                . "CASE WHEN visitor.profile_pic != '' THEN CONCAT('" . base_url(UPLOAD_DIR . PROFILE_PICS) . "thumb/', visitor.profile_pic) END  as visitorProfilePic";

        $this->db->select($select);
        //$this->db->where('visitor.role', 'visitor');

        if (isset($filters['agents']) and $filters['agents']) {
            $this->db->where_in('agent.id', $filters['agents']);
            //$this->db->where('agent.role', 'agent');
            //$this->db->where(TABLE_CHAT_USERS . '.user_state', $filters['in-chat']);
        }

        $query = $this->db->from(TABLE_CHAT_SESSIONS . ' sess')
                ->join(TABLE_CHAT_MESSAGES . ' msg', 'msg.chat_session_id = ' . 'sess.id')
                ->join(TABLE_USERS . ' visitor', 'visitor.id = ' . 'msg.sender_id');

        if (isset($filters['agents']) and $filters['agents']) {
            $query->join(TABLE_CHAT_USERS, TABLE_CHAT_USERS . '.chat_session_id = ' . 'sess.id');
            $query->join(TABLE_USERS . ' agent', 'agent.id = ' . TABLE_CHAT_USERS . '.user_id');
        }

        $chat_list = $query->limit($this->item_per_page, $filters['offset'])
                ->order_by("sess.id", 'desc')
                ->group_by("msg.chat_session_id")
                ->get()
                ->result();

        return $chat_list;
    }

    /*
     * This function will update chat session
     * 
     * @param $chat_session_id (ID of chat session)
     * @return true or false (boolean)
     */

    public function update_chat_session($chat_session_id) {
        if ($this->db->update($this->table, $this->model_data, array('id' => $chat_session_id))) {
            return true;
        }

        return false;
    }

    /*
     * This function will ba call to return available agents for current chat
     * 
     * @param $chat_session_id (ID of chat session)
     * @return $agents_list
     */

    public function get_available_agents($chat_session_id) {
        $available_agents = array();
        $exists_uids = array();

        $chat_agents = $this->db->select(TABLE_CHAT_USERS . '.user_id')
                ->where(TABLE_CHAT_USERS . '.chat_session_id', $chat_session_id)
                ->from(TABLE_CHAT_USERS)
                ->join(TABLE_USERS, TABLE_USERS . '.id = ' . TABLE_CHAT_USERS . '.user_id')
                ->get()
                ->result();

        foreach ($chat_agents as $agent) {
            $exists_uids[] = $agent->user_id;
        }


        $this->db->select('id, name, email, profile_pic');
        if (count($exists_uids) > 0) {
            $this->db->where_not_in('id', $exists_uids);
        }

        $agents = $this->db->where('role', 'agent')                
                ->where('user_status', 'active')
                ->from(TABLE_USERS)
                ->get()
                ->result();

        $user_ids = array();
        $tag_uids = array();
        $user_departments = array();

        foreach ($agents as $key => $row) {
            $user_ids[] = $row->id;
        }

        if (count($user_ids) > 0) {
            $tags = $this->db->select('utag.user_id, tag.tag_name')
                    ->where_in('user_id', $user_ids)
                    ->from(TABLE_USER_TAGS . ' utag')
                    ->join(TABLE_TAGS . ' tag', 'tag.id = utag.tag_id')
                    ->get()
                    ->result();

            foreach ($tags as $tag) {
                $user_departments[$tag->user_id][] = $tag->tag_name;
            }
        }

        foreach ($agents as $key => $row) {
            $available_agents[$key]['department'] = '';
            if (isset($user_departments[$row->id])) {
                $available_agents[$key]['department'] = implode(", ", $user_departments[$row->id]);
            }

            $available_agents[$key]['id'] = $row->id;
            $available_agents[$key]['name'] = $row->name;
            $available_agents[$key]['email'] = $row->email;
            $available_agents[$key]['profile_picture'] = $this->media->get_thumbnail($row->profile_pic, PROFILE_PICS, $row->email);
        }

        return $available_agents;
    }

    /*
     * This function will ba call to return available departments for current chat
     * 
     * 
     * @return $departments_list
     */

    public function get_available_departments() {
        // select fileds
        $select = TABLE_TAGS . '.id, '
                . TABLE_TAGS . '.tag_name as department';

        $departments = $this->db->select($select)
                ->where(TABLE_TAGS . '.tag_status', 'publish')
                ->from(TABLE_TAGS)
                ->join(TABLE_USER_TAGS, TABLE_USER_TAGS . '.tag_id = ' . TABLE_TAGS . '.id')
                ->group_by(TABLE_TAGS . '.id')
                ->get()
                ->result();

        return $departments;
    }

    /*
     * This function will create a session
     * 
     * @param $visitorData
     * 
     * @return insert_id or false
     */

    function create_session($visitorData = array()) {
        // creating session entry
        //$this->model_data['port'] = $this->get_port();
        $this->db->insert($this->table, $this->model_data);
        $chat_session_id = $this->db->insert_id();

        // creating visitor entry
        $visitorData['chat_session_id'] = $chat_session_id;
        $visitorData['user_role'] = 'visitor';
        $visitorData['started_at'] = date("Y-m-d H:i:s", now());

        //$visitor_data = array('chat_session_id' => $chat_session_id, 'user_id' => $user_id, 'user_role' => 'visitor', 'started_at' => date("Y-m-d H:i:s", now()));
        $this->db->insert(TABLE_CHAT_USERS, $visitorData);

        return $chat_session_id;
    }

    /*
     * Delete data from database
     * 
     * @param Int $chat_session_id
     * @return boolean
     */

    function delete_chat($chat_session_id) {
        $tables = array(TABLE_CHAT_MESSAGES, TABLE_FEEDBACK, TABLE_CHAT_USERS, TABLE_CHAT_REQUESTS);
        $this->db->where('chat_session_id', $chat_session_id);
        $this->db->delete($tables);
        
        // deleting notifications
        $this->db->where('chat_session_id', $chat_session_id);
        $this->db->where_in('notification_type', array('message', 'online_request'));
        $this->db->delete(TABLE_NOTIFICATIONS);
        
        $this->db->where('id', $chat_session_id);
        if($this->db->delete($this->table)) {
            return true;
        }
        
        return false;
    }

    /*
     * This function will return availabel port for new chat request
     * 
     * $return port number
     */

    function get_port() {
        $query = $this->db->select('port')
                ->where_in('session_status', array('requested', 'open', 'forward'))
                ->get($this->table);

        $busy_ports = array();
        foreach ($query->result() as $row) {
            $busy_ports[$row->port] = $row->port;
        }

        $i = 9001; // starts at 1 because 0 is the listen socket
        while (isset($busy_ports[$i])) {
            $i++;
        }

        return $i;
    }

    /*
     * To create visitor visit info entry in database.
     * 
     * @param (array) $data
     * @return false or last insert id
     */

    function createVisitInfo($data = array()) {
        if (is_array($data) and count($data) > 0) {
            $this->db->insert(TABLE_USER_VISIT_INFO, $data);
            return $this->db->insert_id();
        }

        return false;
    }

    /*
     * To update visitor visit info entry in database.
     * 
     * @param (array) $data
     * @return boolean
     */

    function updateVisitInfo($data = array(), $where = array()) {
        if (is_array($data) and count($data) > 0 and is_array($where) and count($where) > 0) {
            $this->db->update(TABLE_USER_VISIT_INFO, $data, $where);
            return true;
        }

        return false;
    }

}
