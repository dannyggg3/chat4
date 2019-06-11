<?php

/*
 * Controller to manage all users related actions
 */

class Users extends CP_AdminController {
    /*
     * Calling parent constructor
     */

    public function __construct() {
        parent::__construct();

        // check has admin access
        $this->has_admin_access();
    }

    /*
     * To load view for users management
     */

    function index() {
        // add users.js file
        $this->addcss(base_url("assets/cropper-master/dist/cropper.css"), TRUE);
        $this->addjs(base_url("assets/cropper-master/dist/cropper.js"), TRUE);
        $this->addjs(theme_url("js/app/users.js"), TRUE);

        $tags = $this->tag->get_all();
        $this->add_js_inline(array('tags' => $tags, 'roles' => $this->roles, 'user' => $this->current_user, 'settings' => $this->settings));

        // add filter file.
        $this->data['filter_view'] = 'users/filters.php';

        //write code here to load view
        $this->bulid_layout("users/list");
    }

    /*
     * To pull users list with filter options applied if any given
     * Possible filter variables in POST (role, keywords, tags (array))
     * 
     */

    function users_list() {
        $users = $this->user->get_users($this->input->post());

        return $this->output->set_content_type('application/json')->set_output($this->return_json($users));
    }

    /*
     * To add a new user into db
     * will have POST values for variables (name, display_name, email, pass, confirm_pass, contact_number, role)
     * $_FILES variable profile_pic 
     * 
     * @return 
     *      if(successful data entry) then {result: "success", id: user_id, message: "DYNAMIC MESSAGE AS PER LANG"}
     *      else {result: "failed", errors: [{error_data}]}
     */

    function add_user() {
        $response = array('errors' => '', 'result' => 'failed');

        //check if data is valid or not
        $this->form_validation->set_rules($this->user->validation_rules['insert']);

        if ($this->form_validation->run() === true) {
            $this->user->model_data = $this->input->post(array('name', 'display_name', 'contact_number', 'email', 'pass', 'role'));
            $is_image_upload = 0;
            if (is_null($this->user->model_data['contact_number'])) {
                $this->user->model_data['contact_number'] = '';
            }

            $this->user->model_data['pass'] = hashpassword($this->user->model_data['pass']);

            $this->user->model_data['profile_pic'] = '';
            if (isset($_FILES['profile_pic']) and $_FILES['profile_pic']['name']) {
                $upload_path = UPLOAD_DIR . PROFILE_PICS;
                $config = array("upload_path" => "./" . $upload_path, "allowed_types" => "jpg|jpeg|JPEG|gif|png", "file_name" => time());
                $thumbnail_config = array('create_thumb' => true, 'width' => '120', 'height' => '120');
                $filedata = $this->media->upload_media('profile_pic', $config, $thumbnail_config);

                if (isset($filedata['error'])) {
                    $response['errors'] = $filedata['error'];
                } else {
                    $this->user->model_data['profile_pic'] = $filedata['file_name'];
                    $is_image_upload = 1;
                }
            }

            if (empty($response['errors']) and $user_id = $this->user->insert()) {
                $this->user_tag->model_data['tags'] = $this->input->post('tags');
                $this->user_tag->save($user_id);

                $user = $this->user->get_single(array('id' => $user_id), array('profile_pic'));

                $userdata = $this->input->post();
                $userdata['id'] = $user_id;
                $userdata['profile_pic'] = $user->profile_pic;
                $userdata['profile_picture'] = $this->media->get_thumbnail($user->profile_pic, PROFILE_PICS, $this->input->post('email'));
                $userdata['large_profile_picture'] = $this->media->get_image($user->profile_pic, PROFILE_PICS);
                $userdata['tags'] = $this->user_tag->get_user_tags($user_id);
                $userdata['is_image_upload'] = $is_image_upload;

                $response['result'] = 'success';
                $response['user'] = $userdata;
                $response['message'] = $this->lang->line('user_added');
            } elseif (empty($response['errors'])) {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
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

    function update_user() {
        $response = array('errors' => '', 'result' => 'failed');

        $id = $this->input->get('id');

        $this->user->validation_rules['update'] = $this->setEditUniqueRule($this->user->validation_rules['update'], 'email', $id);

        //check if data is valid or not
        $this->form_validation->set_rules($this->user->validation_rules['update']);

        if ($this->form_validation->run() === true) {
            $this->user->model_data = $this->input->post(array('name', 'display_name', 'contact_number', 'email'));
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

                $userdata = $this->input->post();
                $userdata['profile_pic'] = $user->profile_pic;
                $userdata['profile_picture'] = $this->media->get_thumbnail($user->profile_pic, PROFILE_PICS, $this->input->post('email'));
                $userdata['large_profile_picture'] = $this->media->get_image($user->profile_pic, PROFILE_PICS);
                $userdata['last_login_string'] = strtotime($userdata['last_login']) * 1000;
                $userdata['tags'] = $this->user_tag->get_user_tags($id);
                $userdata['is_image_upload'] = $is_image_upload;

                $response['result'] = 'success';
                $response['user'] = $userdata;
                $response['message'] = $this->lang->line('user_updated');
            } elseif (empty($response['errors'])) {
                $response['errors'] = $this->lang->line('process_error');
            }
        } else {
            $response['errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * This function will show edit profile page.
     */

    function edit_profile() {
        // add users.js file
        $this->addcss(base_url("assets/cropper-master/dist/cropper.css"), TRUE);
        $this->addjs(base_url("assets/cropper-master/dist/cropper.js"), TRUE);
        $this->addjs(theme_url("js/app/profile.js"), TRUE);

        $tags = $this->tag->get_all();
        $this->add_js_inline(array('tags' => $tags, 'user' => $this->current_user));

        //write code here to load view
        $this->bulid_layout("users/profile");
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

        $this->user->validation_rules['update'] = $this->setEditUniqueRule($this->user->validation_rules['update'], 'email', $id);

        //check if data is valid or not
        $this->form_validation->set_rules($this->user->validation_rules['update']);

        if ($this->form_validation->run() === true) {
            $this->user->model_data = $this->input->post(array('name', 'display_name', 'contact_number', 'email'));
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
                $response['is_image_upload'] = $is_image_upload;
                $response['message'] = $this->lang->line('profile_updated');
            } elseif (empty($response['errors'])) {
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
        $response = array('errors' => '', 'result' => 'failed');
        $id = $this->input->get('id');

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
        // add users.js file
        $this->addjs(theme_url("js/app/profile.js"), TRUE);
        $this->add_js_inline(array('tags' => '', 'user' => $this->current_user));

        //write code here to load view
        $this->bulid_layout("users/edit_password");
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
        $this->user->get($this->input->get('id'));
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
        $response = array('errors' => '', 'result' => 'failed');
        $id = $this->input->get('id');

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
     * To change status
     * 
     * Input in POST user_status
     * 
     * @return
     *      if(successful execution) then {result: "success", message: "DYNAMIC MESSAGE AS PER LANG"}
     *      else {result: "failed", errors: [{all error messages}]}
     */

    function update_status() {
        $response = array('errors' => '', 'result' => 'failed');
        $id = $this->input->get('id');

        $this->user->model_data['user_status'] = $this->input->post('user_status');
        if ($this->user->update($id)) {
            $response['result'] = 'success';
            $response['message'] = $this->lang->line('user_status_changed');
        } else {
            $response['errors'] = $this->lang->line('process_error');
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To delete user
     *      This doesn't mean we will delete user permanently. It will just mark that user's user_status as 
     * 
     * @return
     *      if(successful execution) then {result: "success", message: "DYNAMIC MESSAGE AS PER LANG"}
     *      else {result: "failed", errors: [{all error messages}]}
     */

    function delete_user() {
        $response = array('errors' => '', 'result' => 'failed');
        $id = $this->input->get('id');

        $this->user->model_data['user_status'] = 'deleted';
        if ($this->user->update($id)) {
            $response['result'] = 'success';
            $response['message'] = $this->lang->line('user_deleted');
        } else {
            $response['errors'] = $this->lang->line('process_error');
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To delete user profile picture from db and folder
     * 
     * @return response 
     */

    function remove_picture() {
        $response = array('errors' => '', 'result' => 'failed');
        $id = $this->input->get('id');

        $user = $this->user->get_single(array('id' => $id), array('profile_pic', 'email'));
        $this->user->model_data['profile_pic'] = '';
        if ($this->user->update($id)) {
            $this->media->delete_media($user->profile_pic, PROFILE_PICS);
            $src = $this->media->get_thumbnail($this->user->model_data['profile_pic'], PROFILE_PICS, $user->email);
            
            $response['result'] = 'success';
            $response['src'] = $src;
            $response['message'] = $this->lang->line('profile_pic_deleted');
        } else {
            $response['errors'] = $this->lang->line('process_error');
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

}
