<?php

class Agents extends CP_AgentController {
    /*
     * Calling parent constructor
     */

    public function __construct() {
        parent::__construct();

        $this->data['title'] = 'Agent Panel';
        $this->data['layout'] = 'agent_layout';
        $this->body_classes[] = 'agent-logged-in';
        $this->load->library('user_agent');
        $this->data['browser_name'] = strtolower($this->agent->browser());

        $this->addjs(theme_url("js/app/agents/header.js"), TRUE);
        $this->addjs(theme_url("js/app/agents/chat_list.js"), TRUE);
        $this->addjs(theme_url("js/app/agents/workroom.js"), TRUE);

        if ($this->data['browser_name'] == 'chrome') {
            $this->addjs(theme_url("js/push-notification.js"), TRUE);
        }
           
        $cannet_messages = $this->lang->load('canned-messages', 'english', TRUE);
        $this->settings->is_https = is_https();
        $this->add_js_inline(array('user' => $this->current_user, 'settings' => $this->settings, 'canned' => $cannet_messages));
    }

    /**
     * Index Page for this controller.
     * This function will show edit profile page.
     */
    function index() {
        // add cropper plugins js and css file
        $this->addcss(base_url("assets/cropper-master/dist/cropper.css"), TRUE);
        $this->addjs(base_url("assets/cropper-master/dist/cropper.js"), TRUE);
        $this->addjs(theme_url("js/app/agents/profile.js"), TRUE);

        $tags = $this->tag->get_all();
        $cannet_messages = $this->lang->load('canned-messages', 'english', TRUE);
        $this->add_js_inline(array('tags' => $tags, 'user' => $this->current_user, 'chat_session_id' => '', 'settings' => $this->settings, 'canned' => $cannet_messages));

        //write code here to load view
        $this->bulid_layout("agents/profile");
    }

    /*
     * This function will return all new requests of agents.
     * 
     * @return $new_requests
     */

    function get_new_requests() {
        $last_request_id = ($this->input->get('last_request_id')) ? $this->input->get('last_request_id') : 0;
        
        $response = array('error' => '', 'result' => 'failed');
        // gettting new chat requests
        $new_requests = $this->chat_request->new_requests($this->current_user->id, $last_request_id);

        $response['result'] = 'success';
        $response['data'] = array('new_requests' => $new_requests, 'last_request_id' => $last_request_id);

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * this function will return online users.
     * This will also update last activity time of logged in user
     * 
     * @return $online_users (array of online users)
     */

    Function get_online_users() {
        // updating last activity time of logged in user
        $this->user->model_data = array('last_activity_time' => date("Y-m-d H:i:s", now()));
        $this->user->update($this->current_user->id);

        // getting online users
        $online_users = $this->user->get_online_users();
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($online_users));
    }

    /*
     * This function will return online users, new requests and recent chats.
     * 
     * @return $response
     */

    function get_related_data() {
        $response = array('error' => '', 'result' => 'failed', 'data' => array());

        if ($this->current_user) {
            // updating last activity time of logged in user
            $this->user->model_data = array('last_activity_time' => date("Y-m-d H:i:s", now()));
            $this->user->update($this->current_user->id);

            // getting online users
            $online_users = $this->user->get_online_users();

            // gettting new chat requests
            $new_requests = $this->chat_request->new_requests($this->current_user->id);

            // getting recent chat list
            $chat_list = $this->chat_session->get_recent_chats($this->current_user->id);

            //getting pending offline request
            $offline_requests = $this->orequest->pending_offline_requests($this->current_user->id);

            $response['result'] = 'success';
            $response['data'] = array(
                'new_requests_counter' => count($new_requests),
                'online_users' => $online_users,
                'chatListData' => $chat_list,
                'offline_requests' => count($offline_requests)
            );
        } else {
            $response['error'] = 'Your session has been expired. Please login again.';
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To update an existing user
     * Input will be given in $_POST name, display_name, contact_number
     * Input in $_FILES profile_pic
     * 
     * @return
     *      if (successful exectution) then {result: "success", message: "DYNAMIC MESSAGE AS PER LANG"}
     *      else {result: "failed"}
     */

    function update_profile() {
        $id = $this->input->get('id');
        
        $response = array('errors' => '', 'result' => 'failed');
        
        //check if data is valid or not
        $this->form_validation->set_rules($this->user->validation_rules['update']);

        if ($this->form_validation->run() === true) {
            $this->user->model_data = $this->input->post(array('name', 'display_name', 'contact_number'));
            $is_image_upload = 0;

            if (isset($_FILES['profile_pic']) and $_FILES['profile_pic']['name']) {
                $upload_path = UPLOAD_DIR . PROFILE_PICS;
                $config = array("upload_path" => "./" . $upload_path, "allowed_types" => "jpg|jpeg|JPEG|gif|png", "file_name" => $id . '-' . time());
                $thumbnail_config = array('create_thumb' => true, 'width' => '200', 'height' => '200');
                $filedata = $this->media->upload_media('profile_pic', $config, $thumbnail_config);

                if (isset($filedata['error'])) {
                    $response['errors'] = $filedata['error'];
                } else {
                    $this->media->delete_media($this->input->post('profile_pic'), PROFILE_PICS);
                    $this->user->model_data['profile_pic'] = $filedata['file_name'];
                    $is_image_upload = 1;
                }
            }

            if (empty($response['errors']) and $this->user->update($id)) {
                $this->user_tag->model_data['tags'] = $this->input->post('tags');
                $this->user_tag->save($id);

                $user = $this->user->get_single(array('id' => $id), array('profile_pic'));

                $response['result'] = 'success';
                $response['profile_pic'] = $user->profile_pic;
                $response['profile_picture'] = $this->media->get_thumbnail($user->profile_pic, PROFILE_PICS);
                $response['large_profile_picture'] = $this->media->get_image($user->profile_pic, PROFILE_PICS);
                $response['message'] = $this->lang->line('profile_updated');
                $response['is_image_upload'] = $is_image_upload;
            } elseif(empty($response['errors'])) {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To crop user profile picture from folder
     * 
     * @return response 
     */

    function crop_user_picture() {
        $id = $this->input->get('id');
        
        $response = array('errors' => '', 'result' => 'failed');
        
        $user = $this->user->get_single(array('id' => $id), array('profile_pic'));
        $postData = $this->input->post();
        if ($postData) {
            $config['image_library'] = 'gd2';
            $config['source_image'] = upload_dir(PROFILE_PICS . $user->profile_pic);
            $config['new_image'] = upload_dir(PROFILE_PICS . $user->profile_pic);
            $config['quality'] = '100%';
            $config['maintain_ratio'] = FALSE;
            $config['width'] = (int) $postData["width"];
            $config['height'] = (int) $postData["height"];
            $config['x_axis'] = (int) $postData["x_axis"];
            $config['y_axis'] = (int) $postData["y_axis"];
            $this->image_lib->clear();
            $this->image_lib->initialize($config);
            if (!$this->image_lib->crop()) {
                $response['errors'] = $this->image_lib->display_errors();
            } else {
                $thumbnail_config = array('thumb_marker' => false, 'create_thumb' => true, 'width' => '120', 'height' => '120', 'source_image' => $config['source_image'], 'new_image' => upload_dir(PROFILE_PICS . 'thumb/' . $user->profile_pic));
                $this->media->create_thumb($thumbnail_config);
                
                $response['result'] = 'success';
                $response['profile_pic'] = $user->profile_pic;
                $response['profile_picture'] = $this->media->get_thumbnail($user->profile_pic, PROFILE_PICS);
                $response['message'] = $this->lang->line('profile_updated');
            }
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will show change password page.
     */

    function change_password() {
        $cannet_messages = $this->lang->load('canned-messages', 'english', TRUE);
        
        // add users.js file
        $this->addjs(theme_url("js/app/agents/profile.js"), TRUE);
        $this->add_js_inline(array('tags' => '', 'user' => $this->current_user, 'chat_session_id' => '', 'settings' => $this->settings, 'canned' => $cannet_messages));

        //write code here to load view
        $this->bulid_layout("agents/edit_password");
    }

    /*
     * To pull single user's complete data
     * 
     * Input GET $id (id of user)
     * 
     * @return
     *      {user complete object with tags}
     */

    function get() {
        $id = $this->input->get('id');
        
        $this->user->get($id);
        $this->user->model_data['profile_picture'] = $this->media->get_thumbnail($this->user->model_data['profile_pic'], PROFILE_PICS, $this->user->model_data['email']);

        return $this->output->set_content_type('application/json')->set_output($this->return_json($this->user->model_data));
    }

    /*
     * To change password
     * 
     * Input in GET $id
     * 
     * Input in POST pass, confirm_pass
     * 
     * @return
     *      if(successful execution) then {result: "success", message: "DYNAMIC MESSAGE AS PER LANG"}
     *      else {result: "failed", errors: [{all error messages}]}
     */

    function update_password() {
        $id = $this->input->get('id');
        
        $response = array('errors' => '', 'result' => 'failed');
        
        //check if data is valid or not
        $this->form_validation->set_rules($this->user->validation_rules['update_password']);
        if ($this->form_validation->run() === true) {
            $this->user->model_data['pass'] = hashpassword($this->input->post('pass'));

            if ($this->user->update($id)) {
                $response['result'] = 'success';
                $response['message'] = $this->lang->line('password_updated');
            } else {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To delete user profile picture from db and folder
     * 
     * @return response 
     */

    function remove_picture() {
        $id = $this->input->get('id');
        
        $response = array('errors' => '', 'result' => 'failed');
        
        $user = $this->user->get_single(array('id' => $id), array('profile_pic', 'email'));
        $this->user->model_data['profile_pic'] = '';
        if ($this->user->update($id)) {
            $this->media->delete_media($user->profile_pic, PROFILE_PICS);
            $src = $this->media->get_thumbnail($this->user->model_data['profile_pic'], PROFILE_PICS, $user->email);
            
            $response['result'] = 'success';
            $response['message'] = $this->lang->line('profile_pic_deleted');
            $response['src'] = $src;
        } else {
            $response['errors'] = $this->lang->line('process_error');
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function sync browser push notification id
     */

    function sync_pushid() {
        $response = array('result' => 'success', 'message' => '');
        $registrationId = $this->input->post('registrationId');

        if ($registrationId) {
            $response['message'] = $registrationId;
            $this->gcm->model_data = array('device_id' => $registrationId);
            $this->gcm->model_data['device_type'] = 'android';
            $this->gcm->model_data['mac_address'] = $registrationId;
            $this->gcm->model_data['user_id'] = $this->current_user->id;
            $this->gcm->model_data['name'] = $this->current_user->name;
            $this->gcm->model_data['user_status'] = 1;
            $this->gcm->sysc_gsm_user();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function sync browser push notification id
     */

    function remove_pushid() {
        $response = array('result' => 'success', 'message' => '');
        $registrationId = $this->input->post('registrationId');

        if ($registrationId) {
            $this->gcm->delete_where(array('device_id' => $registrationId, 'device_type' => 'android'));
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

}
