<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Authentication {

    private $error = array();

    function __construct() {
        $this->ci = & get_instance();
    }

    /**
     * A generic funtion to check user as a authenticated or not, and check if a session is open or closed and show the messages.
     * @param ($email , $password) 
     */
    function login($user_login, $password) {
        if ($user = $this->ci->user->get_single(array('email' => $user_login, 'role !=' => 'visitor'))) {

            if ($user->user_status == 'blocked') {
                $this->ci->session->set_flashdata('error', "Your account is blocked at the moment. Please contact the administrator to re-activate your account.");
                return false;
                exit;
            }

            if ($user->user_status == 'pending') {
                $this->ci->session->set_flashdata('error', "Your account is awaiting activation at the moment. Please check your email filters to ensure that " . $this->ci->site_name . " is not marked as Junk.");
                return false;
            }

            if ($user->pass == md5($password) and $user->user_status == 'active') {
                $this->ci->session->set_userdata('current_user_id', $user->id);
                $this->ci->session->set_userdata('dismis_update_alert', 'no');
                $remember_token = $user->id . '-' . uniqid();
                if (empty($user->remember_token)) {
                    $user->remember_token = $remember_token;
                }

                $this->ci->db->update($this->ci->user->table, array('last_login' => date("Y-m-d H:i:s", now()), 'remember_token' => $user->remember_token), array("id" => $user->id));

                check_for_updates();
                return $user;
            } else {
                $this->ci->session->set_flashdata('error', "Your password does not match with this ID or email. Please try entering the correct password again.");
                return false;
            }
        } else {
            $this->ci->session->set_flashdata('error', "Your ID or email does not found in our records. Please try entering the correct email address again.");
            return false;
        }
    }

}
