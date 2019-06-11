<?php

class Settings extends CP_AdminController {

    public function __construct() {
        parent::__construct();

        // check has admin access
        $this->has_admin_access();
    }

    /*
     * To load view for tags listing page in angular
     */

    public function index() {
        // add users.js file
        $this->addcss(base_url("assets/cmodule-chat/css/cmodule-chat.css"), TRUE);
        $this->addjs(theme_url("js/app/settings.js"), TRUE);

        $this->add_js_inline(array('settings' => $this->settings, 'user' => $this->current_user));

        //write code here to load view
        $this->bulid_layout("settings/settings");
    }

    /*
     * To update a tag
     * 
     * Input will be given in $_POST (tag_name)
     * 
     * @return 
     *      if(successful validtaion) then {result: "success", message: "DYNAMIC MESSAGE AS PER LANG"}
     *      else    {result: "failed", errors: [{error messages}]}
     */

    public function update_settings() {
        //check if data is valid or not
        $this->form_validation->set_rules($this->configuration->validation_rules['update']);

        $response = array('errors' => '', 'result' => 'failed');

        if ($this->form_validation->run() === true) {
            $this->configuration->model_data = $this->input->post();
            if ($this->configuration->update()) {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('setting_updated');
            } else {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * Verify license key (purchase code)
     */

    function verify_license_key() {
        $response = array('errors' => '', 'result' => 'failed');

        $license_key = $this->input->post('license_key');
        if ($license_key) {
            $result = validate_license_key($license_key);

            if ($result->result == 'success') {
                // load the file helper
                $this->load->helper('string');
                if (!$this->config->config_update(array('validated_code' => "yes"))) {
                    $response['errors'] = 'The ' . $this->lang->line('licence_key') . " is not validated. Please try again.";
                } else {
                    $response['result'] = 'success';
                    $response['message'] = $result->message;

                    $this->configuration->model_data = array('config_value' => $license_key);
                    $this->configuration->update_where(array('config_option' => 'licence_key'));
                }
            } else {
                if (is_object($result->errors)) {
                    foreach ($result->errors as $errorType => $error) {
                        $error_type = '';
                        if (is_string($errorType)) {
                            $error_type = '<strong>' . str_replace("_", " ", $errorType) . '</strong> - ';
                        }
                        $response['errors'] .= $error_type . $error;
                    }
                } else {
                    $response['errors'] = $result->errors;
                }
            }
        } else {
            $response['errors'] = $this->lang->line('licence_key') . " field is reuired.";
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To unregister domain and license key
     */

    function unregister() {
        $response = array('errors' => '', 'result' => 'failed');

        $license_key = $this->input->post('license_key');
        if ($license_key) {
            $result = unregister_license_key($license_key);
            if ($result->result == 'success') {
                // load the file helper
                $this->load->helper('string');
                if (!$this->config->config_update(array('validated_code' => "no"))) {
                    $response['errors'] = 'The ' . $this->lang->line('licence_key') . " is not unregistered. Please try again.";
                } else {
                    $response['result'] = 'success';
                    $response['message'] = $result->message;

                    $this->configuration->model_data = array('config_value' => '');
                    $this->configuration->update_where(array('config_option' => 'licence_key'));
                }
            } else {
                $response['errors'] = $result->errors;
            }
        } else {
            $response['errors'] = $this->lang->line('licence_key') . " field is reuired.";
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will upload image for default avatar
     * 
     * @return $imgurl;
     */

    function upload_avatar() {
        $response = array('result' => 'failed', 'errors' => '');
        if (isset($_FILES['avatar']) and $_FILES['avatar']['name']) {
            $upload_path = UPLOAD_DIR . AVATAR;
            $config = array("upload_path" => "./" . $upload_path, "allowed_types" => "jpg|jpeg|JPEG|gif|png", "file_name" => time());
            $thumbnail_config = array('create_thumb' => true, 'width' => '120', 'height' => '120');
            $filedata = $this->media->upload_media('avatar', $config, $thumbnail_config);

            if (isset($filedata['error'])) {
                $response['errors'] = $filedata['error'];
            } else {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('avatar_uploaded');
                $response['avatar_url'] = $this->media->get_thumbnail($filedata['file_name'], AVATAR);
            }
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will upload image for default avatar
     * 
     * @return $imgurl;
     */

    function upload_logo() {
        $response = array('result' => 'failed', 'errors' => '');
        if (isset($_FILES['logofile']) and $_FILES['logofile']['name']) {
            $upload_path = UPLOAD_DIR . LOGOS;
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, TRUE);
                mkdir($upload_path . '/thumb/', 0777, TRUE);
            }

            $config = array("upload_path" => "./" . $upload_path, "allowed_types" => "jpg|jpeg|JPEG|gif|png", "file_name" => time());
            $thumbnail_config = array('create_thumb' => true, 'width' => '120', 'height' => '120');
            $filedata = $this->media->upload_media('logofile', $config, $thumbnail_config);

            if (isset($filedata['error'])) {
                $response['errors'] = $filedata['error'];
            } else {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('avatar_uploaded');
                $response['site_logo'] = $this->media->get_thumbnail($filedata['file_name'], LOGOS);
            }
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * Wii return all access tikens
     * 
     * @return $response
     */

    function get_tokens() {
        $response = array();
        $response['tokens'] = $this->token->get_tokens();

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * Will create new entry for access token
     */

    function generate_code() {
        $response = array('result' => 'failed', 'errors' => '');

        //check if data is valid or not
        $this->form_validation->set_rules($this->token->validation_rules);

        if ($this->form_validation->run() === true) {
            $this->token->model_data = $this->input->post();
            $this->token->model_data['token'] = random_string('alnum', 20);
            if ($this->token->create()) {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('setting_updated');
            } else {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * to update entry for access token
     */

    function update_code() {
        $response = array('result' => 'failed', 'errors' => '');

        //check if data is valid or not
        $this->token->validation_rules = array(
            array(
                'field' => 'site_url',
                'label' => 'lang:site_url',
                'rules' => 'required|callback__domain_check[' . $this->input->post('id') . ']'
            )
        );
        $this->form_validation->set_rules($this->token->validation_rules);

        if ($this->form_validation->run() === true) {
            $this->token->model_data = $this->input->post();
            if ($this->token->update_where(array('id' => $this->input->post('id')))) {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('token_updated');
            } else {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * Check domain name is already exists or not.
     * 
     * @param {String} $url
     * @param {Int} $id
     * 
     * @return Boolean
     */

    function _domain_check($url, $id = 0) {
        $domain_exists = $this->lang->line('domain_exists');
        $domain_name = get_domain($url);

        if ($id) {
            $this->db->where('id !=', $id);
            $this->db->like('site_url', $domain_name);
            $query = $this->db->get($this->token->table);
            $token = $query->row();
            if ($token) {
                $this->form_validation->set_message('_domain_check', $domain_exists);
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            $this->db->like('site_url', $domain_name);
            $query = $this->db->get($this->token->table);
            $token = $query->row();
            if ($token) {
                $this->form_validation->set_message('_domain_check', $domain_exists);
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }

}
