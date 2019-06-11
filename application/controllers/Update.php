<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Update extends CP_AdminController {

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
    }

    /*
     * Load update view
     */

    function index() {
        // Only show install page if there is no lock
        if (!$this->session->userdata('new-version')) {
            redirect('');
        }

        $new_version = $this->input->get('lv');
        $data = array('new_version' => $new_version);
        $this->addjs(theme_url("js/app/install/main.js"), TRUE);
        $this->addjs(theme_url("js/app/install/update.js"), TRUE);
        $this->data['title'] = ' - Chatbull';
        $this->data['pagetitle'] = 'Need to update';
        $this->add_js_inline(array('new_version' => $new_version));
        $data['content'] = $this->load->view('install/update', $data, TRUE);
        $this->load->view('install/template', $data);
    }

    /*
     * To check next version of plugin
     */

    function next_version() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');

        // sending request on server to check version.
        $url = CHATBULL_APIURL . 'api-update.php';
        $fields = array('current_version' => $this->settings->current_version, 'action' => 'next-version');

        $this->curl->create($url);
        $this->curl->http_header('purchasecode', $this->settings->licence_key);
        $this->curl->http_header('registerurl', base_url());
        $this->curl->post($fields);
        $result = json_decode($this->curl->execute());

        if ($result->result == 'success') {
            $response['result'] = 'success';
            $this->session->set_userdata('update_version', $result->next_version);
            $response['new_version'] = $result->new_version;
        } else {
            $response['errors'] = sprintf($this->lang->line('alrady_updated'), $this->settings->current_version);
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function downoad latest version of chatbull plugin
     */

    function download() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');

        $download_url = CHATBULL_APIURL . 'api-update.php';
        $zipFile = UPDATE_DIR . "chatbull-" . $this->session->userdata('update_version') . ".zip"; // Local Zip File Path

        if (!is_dir(UPDATE_DIR)) {
            mkdir(UPDATE_DIR, 0777, TRUE);
        }

        if (is_really_writable(UPDATE_DIR) === FALSE) {
            if (!chmod(UPDATE_DIR, DIR_WRITE_ALL_MODE)) {
                $response['errors'] = 'Update folder is not writable.';
            }
        }

        if ($response['errors'] == '') {
            // setting header
            $headers = array(
                'purchasecode: ' . $this->settings->licence_key,
                'registerurl: ' . base_url()
            );

            // settings post params
            $post_fields = array(
                'current_version' => $this->settings->current_version,
                'update_version' => $this->session->userdata('update_version'),
                'action' => 'download'
            );

            $zipResource = @fopen($zipFile, FOPEN_READ_WRITE_CREATE_DESTRUCTIVE);
            $ch = curl_init(str_replace(" ", "%20", $download_url)); //Here is the file we are downloading, replace spaces with %20
            curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
            curl_setopt($ch, CURLOPT_FILE, $zipResource); // write curl response to file
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
            $result = curl_exec($ch); // get curl response
            curl_close($ch);
            fclose($zipResource);

            if ($result) {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('plugin_name') . " " . $this->session->userdata('update_version') . $this->lang->line('files_downloaded');
                $response['response'] = $result;
            } else {
                
            }
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function Extracting downloaded latest version of chatbull plugin
     */

    function extract() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');
        $zipFile = UPDATE_DIR . "chatbull-" . $this->session->userdata('update_version') . ".zip"; // Local Zip File Path

        if (file_exists($zipFile)) {
            $output = extractZip($zipFile, UPDATE_DIR);
            if ($output['result'] == 'failed') {
                $response['errors'] = $output['errors'];
            } else {
                $zipFile = UPDATE_DIR . "chatbull-" . $this->session->userdata('update_version') . "/chatbull-plugin.zip"; // Local Zip File Path
                $output = extractZip($zipFile, FCPATH);
                if ($output['result'] == 'failed') {
                    $response['errors'] = $output['errors'];
                } else {
                    $response['result'] = 'success';
                    $response['message'] = $this->lang->line('files_extracted');
                }
            }
        } else {
            $response['errors'] = $this->lang->line('zip_not_found');
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will be use to make changes in database on made in new version.
     * #method Get
     * @return json object
     */

    function update_db() {
        // Only show install page if there is no lock
        if (!$this->session->userdata('new-version') and $this->settings->current_version < 4.0 ) {
            $this->session->set_userdata('new-version', '4.0');
            $this->session->set_userdata('update_version', '4.0');
        }

        $response = array('result' => 'failed', 'errors' => '', 'message' => '');
        $response['message'] = $this->lang->line('update_installed') . $this->session->userdata('update_version') . ".<br>Note: Please clear your browser cache to see changes.";
        $response['result'] = 'success';
        $response['current_version'] = $this->session->userdata('update_version');

        $this->load->library('migration');

        if ($this->migration->latest() === FALSE) {
            $response['errors'] = $this->migration->error_string();
        } else {
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

            // changing site lived year value.
            $lived_date = explode("-", $this->settings->site_lived_year);
            if (count($lived_date) < 2) {
                $admin = $this->user->get_single(array('role' => 'admin'));

                // updating site_lived_year in database.
                $this->configuration->model_data = array('site_lived_year' => date("Y-m-d", strtotime($admin->created_at)));
                $this->configuration->update();
            }
        }

        $this->load->helper('file');
        if (is_dir(UPDATE_DIR . "chatbull-" . $this->session->userdata('update_version'))) {
            delete_files(UPDATE_DIR . "chatbull-" . $this->session->userdata('update_version'), true);
            rmdir(UPDATE_DIR . "chatbull-" . $this->session->userdata('update_version'));
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To update version to database.
     */

    function update_version() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');

        // load the file helper
        $this->load->helper('string');

        $update_config_data = array();
        $update_config_data['enable_query_strings'] = 'TRUE';
        $update_config_data['time_reference'] = 'UTC';

        // updating config options
        if (!$this->config->config_update($update_config_data)) {
            $response['errors'] = 'Not able to write config file.';
        } else {
            $response['result'] = 'success';
            $response['updated_version'] = $this->session->userdata('update_version');

            // sending request on server to check version.
            $url = CHATBULL_APIURL . 'api-update.php?action=add-history';
            $fields = array('current_version' => $this->settings->current_version, 'new_version' => $this->session->userdata('update_version'));

            $this->curl->create($url);
            $this->curl->http_header('purchasecode', $this->settings->licence_key);
            $this->curl->http_header('registerurl', base_url());
            $this->curl->post($fields);
            $result = $this->curl->execute();

            // updating version in database.
            $this->configuration->model_data = array('current_version' => $this->session->userdata('update_version'));
            $this->configuration->update();

            if ($this->session->userdata('update_version') == $this->session->userdata('new-version')) {
                $this->session->unset_userdata('new-version');
                $this->session->unset_userdata('update_version');
                $this->session->unset_userdata('notify-update-message');
            }
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

}
