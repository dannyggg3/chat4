<?php

/*
 * Main Base Controller
 */

class CP_Controller extends CI_Controller {

    var $settings;
    var $body_classes = array();
    var $data = array('theme' => 'cmodule');

    public function __construct() {
        parent::__construct();

        //$this->output->enable_profiler(TRUE);
        //define('DB_PRIFIX', $this->db->dbprefix);
    }

    /*
     * Returns the data in json format
     * @param : data if anything else than controller's data attribute.
     * returns: either data passed in argument or controller's data attribute in JSON format.
     * Author: Pukhraj Prajapat, pukhraj.prajapat@g-axon.com
     */

    public function return_json($data = "") {
        if (!empty($data)) {
            return json_encode($data);
        }
    }

    /*
     * This function will cancel all requests.
     * 
     * @param $chat_session_id
     * 
     * @return true or false
     */

    function close_requests($chat_session_id) {
        return $this->chat_request->close_requests($chat_session_id);
    }

    /*
     * This function will return cureent server time in miliseconds.
     * 
     * @return $milliseconds
     */

    function get_time_miliseconds() {
        $micro_time = microtime(true);
        $milliseconds = round($micro_time * 1000);

        return $milliseconds;
    }

}

/*
 * Main Admin Base Controller
 */

class CP_AdminController extends CP_Controller {

    var $files = array('css_header' => '', 'css_footer' => '', 'js_header' => '', 'js_footer' => '');
    var $item_per_page = 10;
    var $current_user = '';

    public function __construct() {
        parent::__construct();

        $this->form_validation->set_error_delimiters('', '');
        if ($this->config->item('installed') != 'no') {
            if ($this->session->userdata('current_user_id')) {
                $current_user_id = $this->session->userdata('current_user_id');
                $this->current_user = $this->user->get_single(array('id' => $current_user_id));
                $this->current_user->profile_picture = $this->media->get_thumbnail($this->current_user->profile_pic, PROFILE_PICS, $this->current_user->email);

                if ($this->router->class == 'admin' and ( $this->router->method == 'login')) {
                    redirect("c=admin");
                }
            } else {
                $remember_token = $this->input->cookie('remember_token');
                $this->current_user = $this->user->get_single(array('remember_token' => $remember_token));
                if ($this->current_user) {
                    // closing closable sessions
                    $this->user->closeClosedSessions();

                    $this->session->set_userdata('current_user_id', $this->current_user->id);
                    $this->session->set_userdata('dismis_update_alert', 'no');
                    $this->db->update(TABLE_USERS, array('last_login' => date("Y-m-d H:i:s", now())), array("id" => $this->current_user->id));

                    if ($this->current_user->role == 'agent') {
                        redirect("d=agents&c=agents");
                    } elseif ($this->router->class == 'admin' and $this->router->method == 'login') {
                        redirect("c=admin");
                    }
                }
            }

            $this->settings = $this->configuration->get_settings();
            $this->settings->plugin_validated = 'no';
            if ($this->settings->licence_key) {
                $this->settings->plugin_validated = $this->config->item('validated_code');
            }

            if (!isset($this->settings->site_logo)) {
                $this->settings->logo_empty = true;
            }
            if (empty($this->settings->site_logo)) {
                $this->settings->site_logo = base_url("assets/cmodule/images/logo.png");
            }

            if ($this->settings->current_version != CHATBULL_VERSION or ! isset($this->settings->current_product_name) or $this->settings->current_product_name != PRODUCT_NAME) {
                if ($this->router->class != 'upgrade') {
                    redirect("c=upgrade&m=upgrade");
                }
            }
        } elseif ($this->config->item('installed') != 'yes' and $this->router->class != 'install') {
            redirect("c=install");
        }

        $this->_setDefaultData();
    }

    /*
     * This function will be use to add js in view.
     * 
     * @pparam $filepath (js file path)
     * @param $in_footer (If true file will be include before body end
     */

    function addjs($filepath, $in_footer = false) {
        if ($in_footer) {
            $this->files['js_footer'] .= '<script src="' . $filepath . '"></script>' . "\n";
        } else {
            $this->files['js_header'] .= '<script type="text/javascript" src="' . $filepath . '"></script>' . "\n";
        }
    }

    /*
     * This function will be use to add js for ie.
     * 
     * @pparam $filepath (js file path)
     * @param $ie_version
     */

    function addjsIe($filepath, $ie_version = '') {
        if ($ie_version) {
            $this->files['js_header'] .= '<!--[if lte IE ' . $ie_version . ']><script type="text/javascript" src="' . $filepath . '"></script><![endif]-->' . "\n";
        }
    }

    /*
     * This function will be use to add inline js in view.
     * 
     * @pparam $filepath (js file path)
     * @param $in_footer (If true file will be include before body end
     */

    function add_js_inline($data, $in_footer = false) {
        if ($in_footer) {
            $this->files['js_footer'] .= '<script> var cmodule = ' . $this->return_json($data) . '</script>' . "\n";
        } else {
            $this->files['js_header'] .= '<script> var cmodule = ' . $this->return_json($data) . '</script>' . "\n";
        }
    }

    /*
     * This function will be use to add css in view.
     * 
     * @pparam $filepath (css file path)
     * @param $in_footer (If true file will be include before body end
     */

    function addcss($filepath, $in_footer = false) {
        if ($in_footer) {
            $this->files['css_footer'] .= '<link rel="stylesheet" type="text/css" href="' . $filepath . '" media="all" />' . "\n";
        } else {
            $this->files['css_header'] .= '<link rel="stylesheet" type="text/css" href="' . $filepath . '" media="all" />' . "\n";
        }
    }

    /*
     * Function will be call to set Default data
     */

    protected function _setDefaultData() {
        $this->data['title'] = 'Admin Panel';
        $this->data['pagetitle'] = '';
        $this->data['description'] = '';
        $this->data['tags'] = '';
        $this->data['theme'] = 'cmodule';
        $this->data['layout'] = 'main_layout';
        $this->data['filter_view'] = '';

        $this->roles = array(
            array('name' => 'admin', 'label' => 'Admins'),
            array('name' => 'agent', 'label' => 'Agents'),
            array('name' => 'visitor', 'label' => 'Visitors')
        );
    }

    /*
     * This function will be call to build view.
     * 
     * @param string $view file path
     * @param string $layout (optional)
     * @param string $theme (optional)
     */

    function bulid_layout($view, $layout = '', $theme = '') {
        if ($layout)
            $this->data['layout'] = $layout;
        if ($theme)
            $this->data['theme'] = $theme;
        $this->data['view'] = $view;

        // checking user session
        if (empty($this->current_user) and $this->router->directory != 'visitors/' and $this->router->method != 'forget_password' and $this->router->method != 'reset_password' and $this->router->class != 'cmodule') {
            // set login view and layout
            $this->data['layout'] = 'simple_layout';
            $this->data['view'] = 'pages/login';
            $this->data['pagetitle'] = 'Login';
        }

        if ($this->data['pagetitle'])
            $this->data['pagetitle'] .= ' - ';

        // load main vies files.
        $this->load->view($this->data['theme'] . '/' . $this->data['layout']);
    }

    /*
     * This function check user can access admin panel or not.
     * If can access then ok otherwise redirect them to his accessible area.
     */

    function has_admin_access() {
        if ($this->session->userdata('current_user_id')) {
            if ($this->current_user->role == 'agent') {
                redirect('d=agents&c=agents');
            }
        }
    }

    /*
     * This will add uniqe validation ruke in validation
     */

    function setEditUniqueRule($rules, $unique_field, $id) {
        $update_rules = array();
        foreach ($rules as $rule) {
            if ($rule['field'] == $unique_field) {
                $rule['rules'] = $rule['rules'] . '|callback_email_check[' . $id . ']';
            }

            $update_rules[] = $rule;
        }

        return $update_rules;
    }

    /*
     * This function will check email of user
     * This is validation callback function.
     */

    public function email_check($email, $user_id = '') {
        $uniqu_email_message = "The {field} field must contain a unique value.";
        if ($user_id) {
            $user = $this->user->get_single(array('email' => $email, 'id !=' => $user_id));
            if ($user and $user->id) {
                if ($user->user_status == 'deleted') {
                    $this->user->model_data = array('user_status' => 'active');
                    $this->user->update($user->id);
                }

                $this->form_validation->set_message('email_check', $uniqu_email_message);
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            $user = $this->user->get_single(array('email' => $email, 'user_status !=' => 'deleted'));
            if ($user and $user->id) {
                $this->form_validation->set_message('email_check', $uniqu_email_message);
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }

}

/*
 * Main Agent Base Controller
 */

class CP_AgentController extends CP_AdminController {
    /*
     * construct function
     */

    public function __construct() {
        parent::__construct();
    }

}

/*
 * Main Visitor Base Controller
 */

class CP_VisitorController extends CP_Controller {

    /**
     * Access token for authorization
     *
     * @var string
     */
    protected $access_token = '';

    /**
     * Token Object if valid token
     *
     * @var object or FALSE
     */
    protected $valid_token;

    /*
     * construct function
     */

    public function __construct() {
        parent::__construct();
        $this->form_validation->set_error_delimiters('', '');
        
        $this->settings = $this->configuration->get_visitor_settings();
        $this->settings->plugin_validated = $this->config->item('validated_code');

        if ($this->config->item('validated_code') == 'yes' and $this->config->item('installed') == 'yes') {
            // gettting access token
            $this->input->request_headers();
            $this->access_token = ($this->input->get('token')) ? $this->input->get('token'): $this->input->get_request_header('Accesstoken', TRUE);
            $this->valid_token = $this->token->is_valid($this->access_token);
        }
    }

}

/*
 * Main App Base Controller
 */

class CP_AppController extends CP_Controller {

    /**
     * Access token for authorization
     *
     * @var string
     */
    protected $access_token = '';

    /**
     * Token Object if valid token
     *
     * @var object or FALSE
     */
    protected $valid_token;

    /*
     * construct function
     */

    public function __construct() {
        parent::__construct();
        $this->form_validation->set_error_delimiters('', '');

        $this->settings = $this->configuration->get_settings();
        $this->settings->plugin_validated = $this->config->item('validated_code');
        
        if ($this->config->item('validated_code') == 'yes' and $this->config->item('installed') == 'yes') {
            // gettting access token
            $this->input->request_headers();
            $this->access_token = ($this->input->get('token')) ? $this->input->get('token'): $this->input->get_request_header('Accesstoken', TRUE);
            $this->valid_token = $this->token->is_valid($this->access_token);
        }
    }

}
