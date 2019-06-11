<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CP_AdminController {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index() {
        // check has admin access
        $this->has_admin_access();

        // add users.js file
        $this->addjs(base_url("assets/flot/jquery.flot.js"), TRUE);
        $this->addjsIe(base_url("assets/flot/excanvas.min.js"), 8);
        $this->addjs(base_url("assets/flot/jquery.flot.time.js"), TRUE);
        $this->addjs(base_url("assets/flot/jquery.flot.resize.js"), TRUE);
        $this->addjs(base_url("assets/flot/jquery.flot.selection.js"), TRUE);
        $this->addjs(base_url("assets/flot/jshashtable-2.1.js"), TRUE);
        $this->addjs(base_url("assets/flot/jquery.numberformatter-1.2.3.min.js"), TRUE);
        $this->addjs("//maps.google.com/maps/api/js?key=AIzaSyDAP5NazCpM8Z3eAdhJ0yX_xndq5mZAQ30", TRUE);
        $this->addjs(theme_url("js/markerclusterer_compiled.js"), TRUE);
        $this->addjs(theme_url("js/app/dashboard.js"), TRUE);

        $this->add_js_inline(array('user' => $this->current_user, 'settings' => $this->settings));

        $this->bulid_layout("pages/dashboard");
    }

    /*
     * This will return dashboard data to view.
     * 
     * @return Json $response
     */

    function get_dashboard_data() {
        $response = array();
        $settings = $this->settings;

        $lived_date = explode("-", $settings->site_lived_year);
        $livedYear = (isset($lived_date[0])) ? intval($lived_date[0]) : date('Y', now());
        $livedMonth = (isset($lived_date[1])) ? intval($lived_date[1]) : 1;
        $livedDay = (isset($lived_date[2])) ? intval($lived_date[2]) : 1;

        $endTime = date('Y', now());
        $today_data = date('Y-m', now());

        $users_record = array();
        $users_counter = array();
        $visitors_counter = array();
        $monthly_users = 0;
        $monthly_visitors = 0;
        $total_users = 0;

        $requestsData = $this->user->getRequestsData();
        $visitorsData = $this->user->get_visitors();

        for ($y = $livedYear; $y <= $endTime; $y++) {
            $monthrange = 12;
            $month_start = 1;
            if ($endTime == $y) {
                $monthrange = date('m', now());
            }

            if ($livedYear == $y) {
                $month_start = $livedMonth;
            }

            for ($m = $month_start; $m <= $monthrange; $m++) {
                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $m, $y);
                if ($today_data == $y . "-" . sprintf('%02d', $m)) {
                    $days_in_month = date('d', now());
                }

                $day_start = 1;
                if ($livedYear == $y and $m == $livedMonth) {
                    $day_start = $livedDay;
                }

                for ($d = $day_start; $d <= $days_in_month; $d++) {
                    $datestr = $y . "-" . sprintf('%02d', $m) . "-" . sprintf('%02d', $d);
                    $daily_visitors = (isset($visitorsData[$datestr])) ? intval($visitorsData[$datestr]) : 0;
                    $visitors_counter[] = array($y, $m, $d, $daily_visitors);

                    $daily_users = (isset($requestsData[$datestr])) ? intval($requestsData[$datestr]) : 0;
                    $users_record[] = array($y, $m, $d, $daily_users);
                }
            }
        }

        $response['users_per_day'] = array('total' => $users_record);
        $response['visitors_counter'] = array('total' => $visitors_counter);
        $response['mapdata'] = $this->user->getVisitorsAddress();
        $response['pageviews'] = $this->user->get_pagevies();

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To set admin data
     */

    function set_data() {
        $output = array('result' => 'success', 'message' => 'Information saved successfully.');
        /*
        if ($this->settings->licence_key and $this->config->item('validated_code') == 'yes') {
            $url = CHATBULL_APIURL . 'notify_domain.php?action=is-registered';
            $fields = array('license_key' => $this->settings->licence_key, 'site_url' => base_url());

            $this->curl->create($url);
            $this->curl->post($fields);
            $result = $this->curl->execute();
            $response = json_decode($result);
            if ($response->result != 'success') {
                // load the file helper
                $this->load->helper('string');
                if ($this->config->config_update(array('validated_code' => "no"))) {
                    $this->configuration->model_data = array('config_value' => '');
                    $this->configuration->update_where(array('config_option' => 'licence_key'));
                }
            }
        }
        */
        return $this->output->set_content_type('application/json')->set_output($this->return_json($output));
    }

    /*
     * This function will dismiss update availabel notification
     * 
     * @return json object
     */

    function dismis_message() {
        $this->session->set_userdata('dismis_update_alert', 'yes');
    }

    /*
     * To check is upgrades are available for current project.
     */

    function upgrade_available() {
        $response = array('result' => 'failed', 'errors' => '', 'message' => '');

        // sending request on server to check version.
        $url = CHATBULL_APIURL . 'api-info.php';
        $fields = array('product_name' => PRODUCT_NAME, 'action' => 'has-upgrade');

        $this->curl->create($url);
        $this->curl->post($fields);
        $result = json_decode($this->curl->execute());

        if ($result->result == 'success') {
            $response = $result;
        } else {
            $response['errors'] = $result->errors;
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function display login form.
     */

    function login() {
        $this->form_validation->set_error_delimiters('<div class="input-error">', '</div>');
        //check if data is valid or not
        $this->form_validation->set_rules($this->user->validation_rules['login']);

        if ($this->form_validation->run() === true) {
            $user = $this->authentication->login($this->input->post('email'), $this->input->post('password'));
            if ($user) {
                if ($this->input->post('remember_me') != NULL) {
                    $cookie = array(
                        'name' => 'remember_token',
                        'value' => $user->remember_token,
                        'expire' => (86500 * 30)
                    );
                } else {
                    $cookie = array(
                        'name' => 'remember_token',
                        'value' => $user->remember_token,
                        'expire' => ''
                    );
                }

                $this->input->set_cookie($cookie);

                // closing closable sessions
                $this->user->closeClosedSessions();

                if ($user->role == 'agent') {
                    redirect("d=agents&c=agents");
                } else {
                    redirect("c=admin");
                }
            }

            redirect("c=admin&m=login");
        }

        $this->add_js_inline(array('user' => $this->current_user, 'settings' => $this->settings));
        $this->bulid_layout("pages/login");
    }

    /*
     * This funtion destroy session
     */

    public function logout() {
        //$this->session->sess_destroy();
        $this->session->unset_userdata('workroomHistory');
        $this->session->unset_userdata('current_user_id');
        $this->session->unset_userdata('dismis_update_alert');
        $this->session->unset_userdata('is_update_checked');

        $this->session->unset_userdata('new-version');
        //$this->session->unset_userdata('is_update_checked');

        $remember_token = $this->input->cookie('remember_token');
        $cookie = array(
            'name' => 'remember_token',
            'value' => $remember_token,
            'expire' => ''
        );

        $this->input->set_cookie($cookie);

        $this->session->set_flashdata('success', " You have been logged out successfully.");
        redirect("");
    }

    /*
     * This function display forget password form and process.
     */

    public function forget_password() {
        //check if data is valid or not
        $this->form_validation->set_rules($this->password_reminder->validation_rules['forget_password']);
        if ($this->form_validation->run() === true) {
            $user = $this->user->get_single(array('email' => $this->input->post('email'), 'role !=' => 'visitor'));
            if ($user) {
                $pass_token = md5(uniqid());
                if ($this->password_reminder->temp_reset_password($pass_token, $this->input->post('email'))) {

                    // send email to visitor
                    $template_file = 'forget_password';
                    $to = $this->input->post('email');
                    $subject = $this->lang->line('reset_your_password');
                    $link = site_url('c=admin&m=reset_password&token=' . $pass_token);
                    $message = "<p>If you want to change password click on below link otherwise ignore it.</p>";
                    $data = array('name' => $user->name, 'link' => $link, 'message' => $message);
                    $response = send_template_email($template_file, $to, $subject, $data);
                    if ($response) {
                        $this->session->set_flashdata('success', $this->lang->line('reset_link_sent'));
                        redirect("c=admin&m=login");
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('reset_link_not_sent'));
                        redirect("c=admin&m=forget_password");
                    }
                } else {
                    $this->session->set_flashdata('error', $this->lang->line('process_error'));
                    redirect("c=admin&m=forget_password");
                }
            }
            $this->session->set_flashdata('error', $this->lang->line('email_not_found'));
            redirect("c=admin&m=forget_password");
        }

        $this->add_js_inline(array('user' => $this->current_user, 'settings' => $this->settings));
        $this->form_validation->set_error_delimiters('<div class="input-error">', '</div>');
        $this->bulid_layout("pages/forget_password", 'simple_layout');
    }

    /*
     * This function display reset password form and process.
     * 
     * @param  $pass_token
     */

    public function reset_password() {
        $pass_token = $this->input->get('token');
        $user = $this->password_reminder->is_temp_pass_valid($pass_token);
        if (!$user) {
            $this->session->set_flashdata('error', $this->lang->line('invalid_pass_token'));
            redirect("c=admin&m=forget_password");
        } else {
            $delta = 86400;
            if ($_SERVER["REQUEST_TIME"] - $user->created_at > $delta) {
                $this->session->set_flashdata('error', $this->lang->line('token_expired'));
                redirect("c=admin&m=forget_password");
            } else {
                //check if data is valid or not
                $this->form_validation->set_rules($this->password_reminder->validation_rules['update_password']);
                if ($this->form_validation->run() === true) {
                    $this->user->model_data = array('pass' => hashpassword($this->input->post('pass')));
                    $updated = $this->user->update_where(array('email' => $user->email));
                    if ($updated) {
                        $this->password_reminder->delete_where(array('email' => $user->email));
                        $this->session->set_flashdata('success', $this->lang->line('password_updated'));
                        redirect("c=admin&m=login");
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('process_error'));
                        redirect("c=admin&m=reset_password/" . $pass_token);
                    }
                }
            }
        }

        $this->add_js_inline(array('user' => $this->current_user, 'settings' => $this->settings));
        $this->data['token'] = $pass_token;
        $this->form_validation->set_error_delimiters('<div class="input-error">', '</div>');
        $this->bulid_layout("pages/reset_password", 'simple_layout');
    }

    /*
     * To testing demo
     */

    function demo() {
        $token = $this->token->get_single();
        print_r($token->token);
        echo '<br>';
        $online_activity_time = date("Y-m-d H:i:s", (now() - (60 * 60 * 24 * 3)));
        echo $online_activity_time . '<br>';

        echo date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", now()) . ' - 3 day'));
    }

}
