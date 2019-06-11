<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Install extends CP_AdminController {

    // Email and Password gets displayed at the end of the installer
    var $admin_password = '';
    var $admin_email = '';
    // Some database defaults and information that needs tracking throughout the process
    var $db_driver = 'mysqli';
    var $db_prefix = 'chatbull_';
    var $db_char_set = 'utf8';
    var $db_dbcollat = 'utf8_general_ci';
    
    // Figured out in the constructor and needed for the install process
    var $base_url = '';

    /**
     * Constructor
     * 
     * @access	public
     * @return	void
     */
    function __construct() {
        parent::__construct();

        $this->body_classes[] = 'intallation-process';
        $this->addcss(base_url("assets/cmodule/css/cmodule-installer.css"), TRUE);

        // Only show install page if there is no lock
        if ($this->config->item('installed') != 'no') {
            redirect('c=admin');
        }
    }

    /**
     * First Step
     *
     * We check if the server can be used to install NAB
     *
     * @access	public
     * @param	bool
     * @return	mixed
     */
    function index() {
        $data['install_warnings'] = array();

        // is PHP version ok?
        if (!is_php('5.1.6')) {
            $data['install_warnings'][] = 'PHP version too old';
        }

        // is config file writable? we need to be sure of this before start
        if (is_really_writable($this->config->config_path) === FALSE && !@chmod($this->config->config_path, FILE_WRITE_MODE)) {
            $data['install_warnings'][] = 'config.php file is not writable';
        }

        // is autoload file writable? we need to be sure of this before start
        if (is_really_writable($this->config->autoload_path) === FALSE && !@chmod($this->config->autoload_path, FILE_WRITE_MODE)) {
            $data['install_warnings'][] = 'autoload.php file is not writable';
        }

        // is database file writable? we need to be sure of this before start
        if (is_really_writable($this->config->database_path) === FALSE && !@chmod($this->config->database_path, FILE_WRITE_MODE)) {
            $data['install_warnings'][] = 'database file is not writable';
        }

        // No errors? let's move to the next step
        if (count($data['install_warnings']) == 0) {
            redirect('c=install&m=start');
        } else {
            $this->add_js_inline(array('intallation_step' => 'warnings'));
            $this->addjs(theme_url("js/app/install/main.js"), TRUE);
            $this->addjs(theme_url("js/app/install/install.js"), TRUE);
            $this->data['pagetitle'] = 'Install problems';
            $data['content'] = $this->load->view('install/warnings', $data, TRUE);
            $this->load->view('install/template', $data);
        }
    }

    /**
     * Start (firts and only step)
     *
     * The install process unique step
     *
     * @access	public
     * @return	string
     */
    function start() {
        $data = array();

        $intallation_step = 'setup-db';

        if (@include($this->config->database_path)) {
            // load based on custom passed information
            if ($db[$active_group]['username'] and $db[$active_group]['database']) {
                $tables = $this->db->list_tables();
                if (count($tables) > 0 and $this->db->table_exists(TABLE_USERS)) {
                    $intallation_step = 'setup-user';
                }
            }
        }

        $this->add_js_inline(array('intallation_step' => $intallation_step));

        $this->addjs(theme_url("js/app/install/main.js"), TRUE);
        $this->addjs(theme_url("js/app/install/install.js"), TRUE);
        $this->data['pagetitle'] = 'Welcome to Chatbull installer';
        $data['content'] = $this->load->view('install/index', $data, TRUE);
        $this->load->view('install/template', $data);
    }

    /*
     * This function will check all tables are installed.
     * 
     * @return json object
     */

    function all_tables_created() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');
        $this->load->library('migration');
        
        if ($this->migration->latest() === FALSE) {
            $response['errors'] = $this->migration->error_string();
        } else {
            $this->settings = $this->configuration->get_settings();
            $options = $this->configuration->get_default();
            $new_options = array();

            foreach ($options as $opt) {
                if (!isset($this->settings->{$opt['config_option']})) {
                    $new_options[] = $opt;
                }
            }

            if (count($new_options) > 0) {
                $this->configuration->insert_options($new_options, TRUE);
            }
                    
            $response['db_installed'] = 'yes';
            $response['result'] = 'success';
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will setup database.
     * 
     * @return json response
     */

    function setup_db() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');

        $this->form_validation->set_message('required', '%s field is required.');

        $this->form_validation->set_rules('host', 'Host Name', 'required');
        $this->form_validation->set_rules('db_name', 'Database Name', 'required|callback__check_mysql_connection');
        $this->form_validation->set_rules('db_user', 'Database user', 'required');
        if ($this->form_validation->run() == TRUE) {

            // setup database config file
            if (!$this->_setup_database()) {
                $response['errors'] = 'database file is not writable.';
            } else {

                $update_autoload_data = array();
                $update_autoload_data['libraries'] = "'database', 'form_validation', 'curl', 'email',  'authentication', 'media'";
                if (!$this->config->autoload_update($update_autoload_data)) {
                    $response['errors'] = 'Not able to write autoload file.';
                } else {                    
                    $response['message'] = 'Database installed successfully.';
                    $response['result'] = 'success';
                }
            }
        } else {
            $response['errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will setup user.
     * 
     * @return json response
     */

    function setup_user() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');
        if (!$this->db->table_exists(TABLE_USERS)) {
            $response['errors'] = 'Databse is installing. Please try again.';
            echo $this->return_json($response);
            exit();
        }

        $this->form_validation->set_message('required', '%s field is required.');

        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == TRUE) {
            $this->load->model('install_model');

            $this->install_model->model_data = $this->input->post(array('name', 'email', 'display_name'));
            $this->install_model->model_data['pass'] = hashpassword($this->input->post('password'));
            $this->install_model->model_data['role'] = 'admin';
            $user_id = $this->install_model->save_admin();

            if ($user_id) {
                $this->configuration->model_data = array('config_value' => $this->input->post('email'));
                $this->configuration->update_where(array('config_option' => 'site_email'));

                $this->configuration->model_data = array('config_value' => date("Y-m-d", now()));
                $this->configuration->update_where(array('config_option' => 'site_lived_year'));

                // load the file helper
                $this->load->helper('string');

                // updating config options
                $update_config_data = array();
                $update_config_data['encryption_key'] = 'chatbull_' . random_string('alnum');
                $update_config_data['sess_driver'] = 'database';
                $update_config_data['sess_cookie_name'] = random_string('alnum') . '_session';
                $update_config_data['sess_save_path'] = 'session';
                $update_config_data['time_reference'] = 'UTC';

                if (!$this->config->config_update($update_config_data)) {
                    $response['errors'] = 'Not able to write config file.';
                } else {
                    $update_autoload_data = array();
                    $update_autoload_data['libraries'] = "'database', 'session', 'form_validation', 'curl', 'email', 'authentication', 'media'";
                    if (!$this->config->autoload_update($update_autoload_data)) {
                        $response['errors'] = 'Not able to write autoload file.';
                    } else {
                        if (!$this->config->config_update(array('installed' => "yes"))) {
                            $response['errors'] = 'Installation conplete but not locked.';
                        } else {
                            @chmod($this->config->config_path, FILE_READ_MODE);
                            @chmod($this->config->database_path, FILE_READ_MODE);

                            $response['result'] = 'success';
                            $response['page_title'] = 'Plugin Installed';
                            $response['message'] = 'Installation has been completed successfully.';
                            $response['completed_message'] = '<p>Your email: (<strong> ' . $this->input->post('email') . ' </strong>) and password: (<strong> ' . $this->input->post('password') . ' </strong>)</p>';
                        }
                    }
                }
            } else {
                $response['errors'] = 'User not created.';
            }
        } else {
            $response['errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    // --------------------------------------------------------------------

    /**
     * DB Driver Check
     *
     * Test a given driver to ensure the server can use it. We'll also create the
     * database here if we need to.
     *
     * @access	private
     * @param	array
     * @return	bool
     */
    function _check_mysql_connection() {
        if ($this->input->post('db_user')) {
            $config_db = array();
            $config_db['hostname'] = $this->input->post('host');
            $config_db['username'] = $this->input->post('db_user');
            $config_db['password'] = $this->input->post('db_password');
            $config_db['database'] = '';
            $config_db['dbdriver'] = $this->db_driver;
            $config_db['dbprefix'] = $this->db_prefix;
            $config_db['char_set'] = $this->db_char_set;
            $config_db['dbcollat'] = $this->db_dbcollat;

            // load based on custom passed information
            $this->load->database($config_db);
            $database_error = $this->db->error();

            if (is_resource($this->db->conn_id) OR is_object($this->db->conn_id)) {
                $server_ver = $this->db->version();
                if (version_compare($server_ver, '5.5.3', '>')) {
                    // php version is high enough
                    $config_db['char_set'] = $this->db_char_set = 'utf8mb4';
                    $config_db['dbcollat'] = $this->db_dbcollat = 'utf8mb4_general_ci';
                    
                    $this->db->close();
                    $this->load->database($config_db);
                }
                
                // There is a connection
                $this->load->dbutil();

                // Now then, does the DB exist?
                if ($this->dbutil->database_exists($this->input->post('db_name'))) {
                    // Connected and found the db. Happy days are here again!
                    return TRUE;
                } else {
                    $this->load->dbforge();

                    // creating database 
                    if ($this->dbforge->create_database($this->input->post('db_name'))) {
                        return TRUE;
                    } else {
                        $this->form_validation->set_message('_check_mysql_connection', 'Unable to connect to database. Please make sure provided information is valid.');
                        return FALSE;
                    }
                }
            } else {
                $this->form_validation->set_message('_check_mysql_connection', 'Database not exists. Please create first. ' . $database_error['message']);
                return FALSE;
            }
        } else {
            $this->form_validation->set_message('_check_mysql_connection', 'Database name field is required.');
            return FALSE;
        }
    }

    /**
     * Setup Database (only MySQL supported now)
     *
     *
     * @access	private
     * @return	bool
     */
    function _setup_database() {
        $_db['hostname'] = $this->input->post('host');
        $_db['username'] = $this->input->post('db_user');
        $_db['password'] = $this->input->post('db_password');
        $_db['database'] = $this->input->post('db_name');
        $_db['dbdriver'] = $this->db_driver;
        $_db['dbprefix'] = $this->db_prefix;
        $_db['char_set'] = $this->db_char_set;
        $_db['dbcollat'] = $this->db_dbcollat;

        // Update config/database.php file
        return $this->config->db_config_update($_db);
    }

}

/* End of file install.php */
/* Location: ./application/controllers/install.php */