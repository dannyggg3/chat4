<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Chatbox extends CP_VisitorController {
    /*
     * This function will load chatbox for mobile
     */

    public function index() {
        $access_token = $this->input->get('token');
        $siteuser = $this->input->get(array('name', 'email', 'message'));
        $cbwindow = $this->input->get(array('is_mobile', 'innerHeight', 'outerHeight', 'innerWidth', 'outerWidth'));
        $pageinfo = $this->input->get(array('page_title', 'page_url'));
        $token = $this->token->is_valid($access_token);

        if (isset($_SERVER['HTTP_REFERER'])) {
            $this->session->set_userdata('referer_url', $_SERVER['HTTP_REFERER']);
        }

        if (get_domain($token->site_url) == get_domain(referer_url())) {
            $this->load->view('chatbox/mobile', array('access_token' => $access_token, 'siteuser' => $siteuser, 'cbwindow' => $cbwindow, 'pageinfo' => $pageinfo));
        }
    }

    /*
     * This function will send notification to ios server
     * 
     * @param $appkey
     */

    function send_notifications($appkey = '') {
        $response = array('error' => '', 'result' => 'failed');
        if ($appkey == APPKEY) {
            $ios_notifications = $this->input->post('ios_notifications');
            $domain_url = $this->input->post('domain_url');
            $licence_key = $this->input->post('licence_key');
            if ($ios_notifications and $domain_url and $licence_key) {
                foreach ($ios_notifications as $notification) {
                    send_ios_alert($notification['device_id'], $notification['badge'], $notification['message']);
                }
            }

            $response['result'] = 'success';
        } else {
            $response['error'] = $this->lang->line('invalid_appkey');
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function retrurns chatbox mini html.
     */

    function botton() {
        $access_token = $this->input->get('token');
        $siteuser = $this->input->get(array('name', 'email', 'message'));
        $cbwindow = $this->input->get(array('is_mobile', 'innerHeight', 'outerHeight', 'innerWidth', 'outerWidth'));
        $token = $this->token->is_valid($access_token);

        if (get_domain($token->site_url) == get_domain(referer_url())) {
            $this->load->view('chatbox/chatbox-btn', array('access_token' => $access_token, 'siteuser' => $siteuser, 'cbwindow' => $cbwindow, 'token' => $token));
        }
    }

    /*
     * This function retrurns chatbox mini html.
     */

    function getbox() {
        $this->load->view('chatbox/minibox');
    }

    /*
     * This function retrurns chatbox mini html.
     */

    function chatpanel() {
        $access_token = $this->input->get('token');
        $siteuser = $this->input->get(array('name', 'email', 'message'));
        $cbwindow = $this->input->get(array('is_mobile', 'innerHeight', 'outerHeight', 'innerWidth', 'outerWidth'));
        $token = $this->token->is_valid($access_token);

        if (get_domain($token->site_url) == get_domain(referer_url())) {
            $this->load->view('chatbox/chatpanel', array('access_token' => $access_token, 'siteuser' => $siteuser, 'cbwindow' => $cbwindow, 'token' => $token));
        }
    }

    /*
     * This function retrurns chatbox settings as a json.
     */

    function settings() {
        echo 'var settings = ' . $this->return_json($this->settings) . ';';
        exit();
    }

    /*
     * To check plugin installed and validated.
     */

    function is_installed() {
        $response = array('result' => 'failed');
        $wp_url = $this->input->get('wp_url');
        $domain_name = get_domain($wp_url);
        $return_token = ($this->input->get('token')) ? $this->input->get('token') : 'no';

        if ($this->config->item('installed') == 'yes') {
            if ($this->config->item('validated_code') == 'yes') {
                if (empty($domain_name)) {
                    $response['errors']['widget-not-created'] = "Plugin is installed but chatbox widget is not created for your domain. To create chatbox widget <a target='_new' href='" . site_url('c=settings') . "'>click here</a>";
                } else {
                    $this->db->like('site_url', $domain_name);
                    $query = $this->db->get($this->token->table);
                    $token = $query->row();

                    if ($token) {
                        $response['result'] = 'success';
                        $response['message'] = 'Plugin is configured and ready to use.';
                        if ($return_token == 'yes') {
                            $response['token'] = $token;
                        }
                    } else {
                        $response['errors']['widget-not-created'] = "Plugin is installed but chatbox widget is not created yet. To create chatbox widget <a target='_new' href='" . site_url('c=settings') . "'>click here</a>";
                    }
                }
            } else {
                $response['errors']['not-validated'] = "Plugin is installed but not validated yet. To validate plugin <a target='_new' href='" . site_url('c=settings') . "'>click here</a>";
            }
        } else {
            $response['errors']['not-installed'] = "Plugin files are copied but not insalled yet. To install plugin <a target='_new' href='" . site_url() . "'>click here</a>";
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

}
