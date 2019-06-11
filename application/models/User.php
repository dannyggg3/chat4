<?php

class User extends CP_Model {

    public $validation_rules = array(
        // rules for user login
        'login' => array(
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|valid_email'
            ),
            array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required'
            )
        ),
        // rules for insert new user
        'insert' => array(
            array(
                'field' => 'name',
                'label' => 'Name',
                'rules' => 'required'
            ),
            array(
                'field' => 'display_name',
                'label' => 'Display Name',
                'rules' => 'required'
            ),
            array(
                'field' => 'role',
                'label' => 'Role',
                'rules' => 'required'
            ),
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|valid_email|callback_email_check'
            ),
            array(
                'field' => 'pass',
                'label' => 'Password',
                'rules' => 'required'
            ),
            array(
                'field' => 'confirm_pass',
                'label' => 'Confirm Password',
                'rules' => 'required|matches[pass]'
            )
        ),
        //rules to update user info
        'update' => array(
            array(
                'field' => 'name',
                'label' => 'Name',
                'rules' => 'required'
            ),
            array(
                'field' => 'display_name',
                'label' => 'Display Name',
                'rules' => 'required'
            ),
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'required|valid_email'
            )
        ),
        //rules to update password of a user
        'update_password' => array(
            array(
                'field' => 'pass',
                'label' => 'Password',
                'rules' => 'required'
            ),
            array(
                'field' => 'confirm_pass',
                'label' => 'Confirm Password',
                'rules' => 'required|matches[pass]'
            )
        )
    );

    public function __construct() {
        parent::__construct();
        $this->table = TABLE_USERS;
    }

    /*
     * To save user's data
     */

    public function insert() {
        $user = $this->user->get_single(array('email' => $this->model_data['email']));
        if ($user and $user->id) {
            $this->model_data['user_status'] = 'active';
            $this->update($user->id);

            return $user->id;
        }

        $this->model_data['created_at'] = date("Y-m-d H:i:s", now());
        $this->model_data['modified_at'] = date("Y-m-d H:i:s", now());

        $this->db->insert($this->table, $this->model_data);
        return $this->db->insert_id();
    }

    /*
     * To update user's data     * 
     * 
     * @param: $id (id of the user)
     * 
     * @return
     *      if (successful entry done) then true
     *      else false
     */

    public function update($id) {
        $this->model_data['modified_at'] = date("Y-m-d H:i:s", now());
        if ($this->db->update($this->table, $this->model_data, array("id" => $id))) {
            return true;
        }

        return false;
    }

    /*
     * To get user's values and will assign in model_data
     * 
     * @param: $id (id of the user)
     * 
     */

    public function get($id) {
        //pull data from users table and store in $this->user->model_data
        //pull data from user_tags table and store in $this->user->model_data['tags']
        $query = $this->db->select('id, name, display_name, email, profile_pic, role, last_login, user_status, contact_number')
                ->where('id', $id)
                ->get($this->table);

        $this->model_data = $query->row_array();

        $tags_users = $this->db->select('tag_id')->where('user_id', $id)->get(TABLE_USER_TAGS)->result();
        foreach ($tags_users as $tag) {
            $this->model_data['tags'][$tag->tag_id] = $tag->tag_id;
        }
    }

    /*
     * To get all user's list with filter options applied
     * 
     * @param: array of filter options (role, keywords, tags (array)
     * 
     * @return:
     *      array of user objects (with tags)
     */

    public function get_users($filter_values = array()) {
        $user_ids = array();
        $tag_uids = array();
        $users = array();

        if (isset($filter_values['tags']) and $filter_values['tags']) {
            $tags_users = $this->db->select('user_id')->where_in('tag_id', $filter_values['tags'])->get('chatbull_user_tags')->result();
            foreach ($tags_users as $user)
                $tag_uids[$user->user_id] = $user->user_id;
        }

        $sql = "select id, name, email, profile_pic, role, last_login, user_status from " . $this->table . " where user_status != 'deleted'";

        if (isset($filter_values['tags']) and $filter_values['tags']) {
            if (empty($tag_uids))
                return $users;

            $sql .= " and id in (" . implode(",", $tag_uids) . ") ";
        }

        if (isset($filter_values['keywords']) and $filter_values['keywords']) {
            $sql .= " and (email like '%" . $filter_values['keywords'] . "%' or name like '%" . $filter_values['keywords'] . "%' ";
            $sql .= " or display_name like '%" . $filter_values['keywords'] . "%' or contact_number like '%" . $filter_values['keywords'] . "%' )";
        }

        if (isset($filter_values['roles']) and $filter_values['roles']) {
            $sql .= " and role in ('" . implode("','", $filter_values['roles']) . "') ";
        }

        $sql .= " order by role asc limit " . $filter_values['offset'] . ", " . $this->item_per_page;
        $users = $this->db->query($sql)->result();

        if (count($users) > 0) {

            foreach ($users as $user) {
                $user->last_login_string = strtotime($user->last_login) * 1000;
                $user->profilePic = $this->media->get_thumbnail($user->profile_pic, PROFILE_PICS, $user->email);

                $user_ids[] = $user->id;
            }

            $tags = $this->db->select('chatbull_user_tags.*, chatbull_tags.tag_name')
                    ->where_in('user_id', $user_ids)
                    ->from('chatbull_user_tags')
                    ->join('chatbull_tags', 'chatbull_tags.id = chatbull_user_tags.tag_id')
                    ->get()
                    ->result();

            $user_tags = array();
            foreach ($tags as $tag) {
                $user_tags[$tag->user_id][] = $tag;
            }

            foreach ($users as $user) {
                $user->tags = array();
                if (isset($user_tags[$user->id])) {
                    $user->tags = $user_tags[$user->id];
                }
            }
        }

        return $users;
    }

    /*
     * This function will return all agents of a department.
     * 
     * @param $tag_id (department id)
     * 
     * @return $agents ;
     */

    function get_department_agents($tag_id) {
        $agents = $this->db->select(TABLE_USER_TAGS . '.user_id')
                ->where(TABLE_USER_TAGS . '.tag_id', $tag_id)
                ->where_in(TABLE_USERS . '.role', array('agent', 'admin'))
                ->where(TABLE_USERS . '.user_status', 'active')
                ->from(TABLE_USER_TAGS)
                ->join(TABLE_USERS, TABLE_USERS . '.id = ' . TABLE_USER_TAGS . '.user_id')
                ->get()
                ->result();

        return $agents;
    }

    /*
     * Get monthly users
     * 
     * @param $month
     * @param $year
     * 
     * @return $totla_users;
     */

    function getUsersData() {
        $usersData = array();

        //$where = " MONTH(created_at) = '" . $month . "' and YEAR(created_at) = '" . $year . "' and role = 'visitor'";
        $results = $this->db->select("count(*) as total, DATE_FORMAT(`created_at`, '%Y-%m-%d') AS date_formatted")
                ->where('role', 'visitor')
                ->group_by('date_formatted')
                ->order_by('created_at', 'asc')
                ->get(TABLE_USERS)
                ->result();

        foreach ($results as $row) {
            $usersData[$row->date_formatted] = $row->total;
        }

        return $usersData;
    }
    
    /*
     * Get monthly users
     * 
     * @param $month
     * @param $year
     * 
     * @return $totla_users;
     */

    function getRequestsData() {
        $requestsData = array();

        $results = $this->db->select("count(*) as total, DATE_FORMAT(`started_at`, '%Y-%m-%d') AS date_formatted")
                ->where('user_role', 'visitor')
                ->group_by('date_formatted')
                ->order_by('started_at', 'asc')
                ->get(TABLE_CHAT_USERS)
                ->result();

        foreach ($results as $row) {
            $requestsData[$row->date_formatted] = $row->total;
        }
        
        return $requestsData;
    }

    /*
     * This function will return visitors address data to populate map on dashboard.
     * 
     * @return $users;
     */

    function getVisitorsAddress() {
        $select = TABLE_USER_ADDRESS . '.id, '
                . TABLE_USER_ADDRESS . '.user_id, '
                . TABLE_USER_ADDRESS . '.city, '
                . TABLE_USER_ADDRESS . '.state, '
                . TABLE_USER_ADDRESS . '.country, '
                . TABLE_USER_ADDRESS . '.latitude, '
                . TABLE_USER_ADDRESS . '.longitude, '
                . 'user.name, '
                . 'user.email, '
                . 'user.pass, '
                . 'user.profile_pic, '
                . " CASE WHEN user.profile_pic = '' THEN CONCAT(CONCAT(CONCAT('http://www.gravatar.com/avatar/', MD5(LOWER(user.email))),'?s=200'), user.profile_pic) ELSE CONCAT('" . base_url(UPLOAD_DIR . PROFILE_PICS) . "/thumb/', user.profile_pic) END  as userProfilePic";

        $this->db->select($select);

        $users = $this->db->from(TABLE_USER_ADDRESS)
                ->join(TABLE_USERS . ' user', 'user.id = ' . TABLE_USER_ADDRESS . '.user_id')
                ->get()
                ->result();
        return $users;
    }

    /*
     * This function will return hits of visitors
     */

    function get_visitors() {
        $visitorsData = array();
        $this->db->select("*");

        $results = $this->db->from(TABLE_DAILY_VISITORS)
                ->order_by('created_at', 'asc')
                ->get()
                ->result();

        foreach ($results as $row) {
            $visitorsData[$row->created_at] = $row->visitors;
        }

        return $visitorsData;
    }

    /*
     * This function will return highes page views
     * 
     * @param $limit (dfault 5)
     */

    function get_pagevies($limit = 5) {
        $this->db->select("sum(counter) as total, page_url, page_title");

        $results = $this->db->from(TABLE_DAILY_PAGEVIEWS)
                ->group_by('page_url')
                ->order_by('total', 'desc')
                ->limit($limit)
                ->get()
                ->result();

        return $results;
    }

}
