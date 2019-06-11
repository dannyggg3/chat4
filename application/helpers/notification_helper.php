<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * This function will check new updates are availabel or not
 * 
 * @return update_message
 */

function check_for_updates() {
    $CI = & get_instance();

    // sending request on server to check version.
    $url = CHATBULL_APIURL . 'api-update.php';

    $CI->curl->create($url);
    $CI->curl->http_header('purchasecode', $CI->settings->licence_key);
    $CI->curl->http_header('registerurl', base_url());
    $result = $CI->curl->execute();
    $response = json_decode($result);

    if ($response and $response->result == 'success' and $response->latest_version > $CI->settings->current_version) {
        $CI->session->set_userdata('new-version', $response->latest_version);
        $CI->session->set_userdata('notify-update-message', '<div class="update-notification"><a target="_blank" href="' . CHATBULL_SITEURL . '">Chatbull ' . $response->latest_version . '</a> is available! <a target="_blank" href="' . site_url('c=update&m=index&lv=' . $response->latest_version) . '">Please update now</a>.</div>');
    }

    return $response;
}

/*
 * To verify and register lincese key and domain
 * 
 * @param String $license_key
 * 
 * @return json 
 */
function validate_license_key($license_key) {
  /*  $CI = & get_instance();

    // sending request on server to validate lincense key.
    $url = CHATBULL_APIURL . 'notify_domain.php?action=register-domain';
    $fields = array('license_key' => $license_key, 'site_url' => base_url(), 'product_name' => PRODUCT_NAME);

    $CI->curl->create($url);
    $CI->curl->post($fields);
    $result = $CI->curl->execute();
*/
    $result = (object) array('result'=>'success','message'=>'OK','errors'=>'LICENCIA IAV');
    return $result;
}

/*
 * To unregister lincese key and domain
 * 
 * @param String $license_key
 * 
 * @return json 
 */
function unregister_license_key($license_key) {
    $CI = & get_instance();

    // sending request on server to validate lincense key.
    $url = CHATBULL_APIURL . 'notify_domain.php?action=unregister-domain';
    $fields = array('license_key' => $license_key, 'site_url' => base_url());

    $CI->curl->create($url);
    $CI->curl->post($fields);
    $result = $CI->curl->execute();

    return json_decode($result);
}

/*
 * This function will use to send android notification to user.
 * 
 * @param $registatoin_id ( ids of device which will get notification.)
 * @param $message (array of options)
 */

function send_android_alert($registatoin_id, $message) {
    $url = 'https://android.googleapis.com/gcm/send';

    // Set POST variables
    $fields = array(
        'registration_ids' => array($registatoin_id),
        'data' => array("price" => $message),
    );

    $CI = & get_instance();
    $CI->curl->create($url);
    $CI->curl->http_header('Authorization', 'key=' . ANDROID_NOTIFICATION_KEY);
    $CI->curl->http_header('Content-Type', 'application/json');
    $CI->curl->post(json_encode($fields));
    $response = $CI->curl->execute();

    return json_decode($response);
}

/*
 * This function will use to send ios notification to user.
 * 
 * @param $deviceToken ( Token of device which will get notification.)
 * @param $badge ( badge nomber)
 * @param $message (array of options)
 */

function send_ios_alert($deviceToken, $badge, $message) {
    if (isset($message['message'])) {
        $payload = $message;
        $payload['aps'] = array('alert' => $message['message'], 'badge' => $badge, 'sound' => 'default');
        $payload = json_encode($payload);

        // development mode
        /* $passphrase = 'password';
          $apnsHost = 'gateway.sandbox.push.apple.com';
          $apnsPort = 2195;
          $apnsCert = 'ck-development.pem'; */

        // production mode
        $passphrase = '&&paciFFic1';
        $apnsHost = 'gateway.push.apple.com';
        $apnsPort = 2195;
        $apnsCert = 'production-ck.pem';

        $streamContext = @stream_context_create();
        @stream_context_set_option($streamContext, 'ssl', 'local_cert', $apnsCert);
        @stream_context_set_option($streamContext, 'ssl', 'passphrase', $passphrase);
        $apns = @stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);

        if (!$apns) {
            $check = $errorString;
        } else {
            $check = "connected";
        }

        $apnsMessage = chr(0) . pack("n", 32) . pack('H*', str_replace(' ', '', $deviceToken)) . pack("n", strlen($payload)) . $payload;

        $result = @fwrite($apns, $apnsMessage);

        if (!$result) {
            $check = 'iOS = Message not delivered';
        } else {
            $check = 'iOS = Message delivered';
        }

        @fclose($apns);

        return $check;
    }
}

/*
 * This function will use to send notifications.
 * 
 * @param $user_id
 * @param $message
 * @param $badge
 * @param $sedor_device_id
 * 
 * @return true
 */

function push_notification($user_id, $message, $badge, $sedor_device_id = '') {
    $ios_notifications = array();
    $CI = & get_instance();
    $devices = $CI->gcm->get_all(array('user_id' => $user_id, 'user_status' => 1));

    foreach ($devices as $device) {
        if ($device->device_id != $sedor_device_id) {
            if ($device->device_type == 'iOS') {
                //send_ios_alert($device->device_id, $badge, $message);
                $ios_notifications[] = array('device_id' => $device->device_id, 'badge' => $badge, 'message' => $message);
            } else {
                $response = send_android_alert($device->device_id, json_encode($message));
                if (isset($response->results) and $response->failure == '1' and ( (isset($response->results[0]->error) and $response->results[0]->error == 'NotRegistered') or ( isset($response->results[0]->registration_id) and $response->results[0]->registration_id != $device->device_id))) {
                    $CI->gcm->delete_where(array('id' => $device->id));
                }
            }
        }
    }

    if (count($ios_notifications) > 0) {
        $settings = $CI->configuration->get_settings();
        if (isset($settings->ios_notification_url) and $settings->ios_notification_url and $settings->licence_key) {
            $url = $settings->ios_notification_url . '/hootchat';
            $CI->curl->create($url);
            $CI->curl->post(array('ios_notifications' => $ios_notifications, 'domain_url' => site_url(), 'licence_key' => $settings->licence_key));
            $response = $CI->curl->execute();
        }
    }
    return true;
}

/*
 * This function will be use to send template email
 * 
 * @param $template_file
 * @param $to
 * @param $data
 * 
 * @return true or false
 */

function send_template_email($template_file, $to, $subject, $data = array()) {
    if (empty($data)) {
        return false;
    }

    $CI = & get_instance();

    $settings = $CI->configuration->get_settings();
    if (empty($settings->site_logo)) {
        $settings->site_logo = base_url("assets/cmodule/images/logo.png");
    }

    $data['settings'] = $settings;

    $CI->load->library('email');
    $config = array('priority' => 1, 'mailtype' => 'html');
    $CI->email->initialize($config);
    $CI->email->from($settings->site_email, $settings->site_name);
    $CI->email->to($to);

    if (isset($data['cc']) and $data['cc']) {
        $CI->email->cc($data['cc']);
    }

    if (isset($data['bcc']) and $data['bcc']) {
        $CI->email->cc($data['bcc']);
    }

    $CI->email->subject($subject . ' - Chatbull');
    $message = $CI->load->view($CI->data['theme'] . '/emails/' . $template_file, $data, true);
    $CI->email->message($message);

    return $CI->email->send();
}
