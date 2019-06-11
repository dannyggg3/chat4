<?php

class Configuration extends CP_Model {

    //model db table
    var $table = "";
    public $validation_rules = array(
        // rules for chat forward
        'update' => array(
            array(
                'field' => 'licence_key',
                'label' => 'lang:licence_key',
                'rules' => 'required'
            ),
            array(
                'field' => 'chat_status',
                'label' => 'Chat status',
                'rules' => 'required'
            ),
            array(
                'field' => 'chat_mode',
                'label' => 'Chat mode',
                'rules' => 'required'
            ),
            array(
                'field' => 'show_depaertment_selection_box',
                'label' => 'lang:show_depaertment_selection_box',
                'rules' => 'required'
            ),
            array(
                'field' => 'enable_feedback_form',
                'label' => 'Enable feedback form',
                'rules' => 'required'
            ),
            array(
                'field' => 'theme',
                'label' => 'Theme',
                'rules' => 'required'
            ),
            array(
                'field' => 'title_color',
                'label' => 'Title color',
                'rules' => 'required'
            ),
            array(
                'field' => 'text_color',
                'label' => 'Text color',
                'rules' => 'required'
            ),
            array(
                'field' => 'background_color',
                'label' => 'Background color',
                'rules' => 'required'
            ),
            array(
                'field' => 'welcome_message',
                'label' => 'Welcome message',
                'rules' => 'required'
            ),
            array(
                'field' => 'waiting_message',
                'label' => 'Waiting message',
                'rules' => 'required'
            ),
            array(
                'field' => 'offline_heading_message',
                'label' => 'Offline Form introduction message',
                'rules' => 'required'
            ),
            array(
                'field' => 'offline_submission_message',
                'label' => 'Offline Form submission message',
                'rules' => 'required'
            ),
            array(
                'field' => 'feedback_heading_message',
                'label' => 'Feedback Form introduction message',
                'rules' => 'required'
            ),
            array(
                'field' => 'feedback_submission_message',
                'label' => 'Feedback Form submission message',
                'rules' => 'required'
            ),
            array(
                'field' => 'offline_form_title',
                'label' => 'Offline form title',
                'rules' => 'required'
            ),
            array(
                'field' => 'online_form_title',
                'label' => 'Online form title',
                'rules' => 'required'
            ),
            array(
                'field' => 'chat_start_title',
                'label' => 'Start chat title',
                'rules' => 'required'
            ),
            array(
                'field' => 'can_reply_attended_orequests',
                'label' => 'lang:can_reply_attended_orequests',
                'rules' => 'required'
            ),
            array(
                'field' => 'site_name',
                'label' => 'lang:site_name',
                'rules' => 'required'
            ),
            array(
                'field' => 'site_email',
                'label' => 'lang:site_email',
                'rules' => 'required|valid_email'
            )
        )
    );

    /*
     * define construct function
     */

    public function __construct() {
        parent::__construct();
        $this->table = TABLE_CONFIGURATION;
    }

    /*
     * This function will be use to fetch visitor settings.
     * 
     * @return $settings
     */

    function get_settings($conditions = array(), $licence = true) {
        $settings = new stdClass();
        $query = $this->db->get_where($this->table, $conditions);

        foreach ($query->result() as $option) {
            if ($licence) {
                $settings->{$option->config_option} = $option->config_value;
            } elseif ($option->config_option != 'licence_key') {
                $settings->{$option->config_option} = $option->config_value;
            }
        }

        return $settings;
    }

    /*
     * To update config data
     * 
     * @return
     *      if (successful entry done) then true
     *      else false
     */

    public function update() {
        $config_data = array();

        foreach ($this->model_data as $opt_name => $opt_val) {
            $config_data[] = array('config_option' => $opt_name, 'config_value' => $opt_val);
        }

        if (count($config_data) > 0) {
            $this->db->update_batch($this->table, $config_data, 'config_option');
            return true;
        }

        return false;
    }

    /*
     * Return settings for visitors panel as required
     * 
     * @param Array $conditions
     * @return $settings
     */

    function get_visitor_settings($conditions = array()) {
        $settings = new stdClass();
        $config_options = array(
            'theme',
            'visitor_widget_type',
            'chat_icon_size',
            'enable_online_animation',
            'enable_specific_agent_request',
            'enable_file_sharing',
            'file_upload_size',
            'allowed_filetypes',
            'chat_mode',
            'time_interwal',
            'chat_start_title',
            'offline_form_title',
            'online_form_title',
            'default_avatar',
            'text_color',
            'title_color',
            'background_color',
            'enable_feedback_form',
            'offline_submission_message',
            'initiate_bypass_chat',
            'feedback_submission_message',
            'send_chat_transcript_to_visitor',
            'open_chatbox_automatically',
            'time_automatically_open_chatbox',
            'chat_status',
            'window_position',
            'offline_heading_message',
            'show_depaertment_selection_box',
            'welcome_message',
            'feedback_heading_message',
            'waiting_message',
        );

        $this->db->where_in('config_option', $config_options);
        $query = $this->db->get_where($this->table, $conditions);

        foreach ($query->result() as $option) {
            $settings->{$option->config_option} = $option->config_value;
        }

        return $settings;
    }

    /*
     * Return settings for visitors panel as required
     * 
     * @param Array $conditions
     * @return $settings
     */

    function get_agent_settings($conditions = array()) {
        $settings = $this->get_visitor_settings();
        return $settings;
    }

    /*
     * Default all options for configuration
     * 
     * @return array $options
     */

    function get_default() {
        $options = array(
            array('config_option' => 'is_tag_required', 'config_value' => 'no'),
            array('config_option' => 'enable_feedback_form', 'config_value' => 'yes'),
            array('config_option' => 'theme', 'config_value' => 'bubbles_with_avatar'),
            array('config_option' => 'visitor_widget_type', 'config_value' => 'chatbar'),
            array('config_option' => 'chat_icon_size', 'config_value' => 'large-size'),
            array('config_option' => 'enable_online_animation', 'config_value' => 'yes'),
            array('config_option' => 'initiate_bypass_chat', 'config_value' => 'no'),
            array('config_option' => 'window_position', 'config_value' => 'right'),
            array('config_option' => 'chat_status', 'config_value' => 'enable'),
            array('config_option' => 'chat_mode', 'config_value' => 'online'),
            array('config_option' => 'time_interwal', 'config_value' => '3'),
            array('config_option' => 'open_chatbox_automatically', 'config_value' => 'no'),
            array('config_option' => 'time_automatically_open_chatbox', 'config_value' => '5'),
            array('config_option' => 'text_color', 'config_value' => '#fff'),
            array('config_option' => 'background_color', 'config_value' => '#069971'),
            array('config_option' => 'welcome_message', 'config_value' => 'Hello Guest! How can I help you today?'),
            array('config_option' => 'waiting_message', 'config_value' => 'Our agents are already engaged with trying to help out some customers. Please be patient. Someone will be here in a jiffy!'),
            array('config_option' => 'offline_heading_message', 'config_value' => 'Sorry, no agent currently online. Please leave a message & we will contact you soon.'),
            array('config_option' => 'offline_submission_message', 'config_value' => 'Thank you for contacting us! we will get back to you within 24 hours.'),
            array('config_option' => 'feedback_heading_message', 'config_value' => 'Your opinion matters a lot. Kindly leave your feedback.'),
            array('config_option' => 'feedback_submission_message', 'config_value' => 'Your Feedback has been sent successfully.'),
            array('config_option' => 'offline_form_title', 'config_value' => 'We are offline'),
            array('config_option' => 'online_form_title', 'config_value' => 'Live Chat'),
            array('config_option' => 'chat_start_title', 'config_value' => 'Start Chat'),
            array('config_option' => 'title_color', 'config_value' => '#ffffff'),
            array('config_option' => 'can_reply_attended_orequests', 'config_value' => 'yes'),
            array('config_option' => 'default_avatar', 'config_value' => ''),
            array('config_option' => 'send_chat_transcript_to_visitor', 'config_value' => 'ask_to_visiter'),
            array('config_option' => 'site_name', 'config_value' => 'Chatbull'),
            array('config_option' => 'site_email', 'config_value' => ''),
            array('config_option' => 'show_depaertment_selection_box', 'config_value' => 'no'),
            array('config_option' => 'site_lived_year', 'config_value' => ''),
            array('config_option' => 'licence_key', 'config_value' => ''),
            array('config_option' => 'ios_notification_url', 'config_value' => ''),
            array('config_option' => 'licence_validation_url', 'config_value' => ''),
            array('config_option' => 'invalid_domain_url', 'config_value' => 'Domain Url is not valid.'),
            array('config_option' => 'current_version', 'config_value' => CHATBULL_VERSION),
            array('config_option' => 'current_product_name', 'config_value' => PRODUCT_NAME),
            array('config_option' => 'update_message', 'config_value' => ''),
            array('config_option' => 'is_update_available', 'config_value' => ''),
            array('config_option' => 'site_logo', 'config_value' => ''),
            array('config_option' => 'enable_specific_agent_request', 'config_value' => 'yes'),
            array('config_option' => 'enable_file_sharing', 'config_value' => 'yes'),
            array('config_option' => 'file_upload_size', 'config_value' => '5000'),
            array('config_option' => 'allowed_filetypes', 'config_value' => '.docx|.txt|.gif|.jpg|.png')
        );
        
        return $options;
    }

    /*
     * Insert new record in database
     * 
     * @param Array $data
     * @param Boolean $batch Default False
     * 
     * @return boolean or insert id
     */

    function insert_options($data = array(), $batch = FALSE) {
        if (is_array($data) and count($data) > 0) {
            if ($batch) {
                $this->db->insert_batch($this->table, $data);
                return TRUE;
            } else {
                $this->db->insert($this->table, $data);
                return $this->db->insert_id();
            }
        }

        return FALSE;
    }

}
