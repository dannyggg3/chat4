<?php

class Canned_messages extends CP_AgentController {
    /*
     * Calling parent constructor
     */

    public function __construct() {
        parent::__construct();
        $this->load->model('canned_message');
    }

    /*
     * Get all entries from database
     * 
     * @return Json list
     */

    function index() {
        $canned_messages = array();
        if ($this->session->userdata('current_user_id')) {
            $canned_messages = $this->canned_message->get_messages($this->current_user->id, $this->current_user->role);
        }
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($canned_messages));
    }

    /*
     * To save entry in database.
     * 
     * @method post
     * @return Json $response
     */

    function save() {
        $response = array('result' => 'failed', 'description' => '');

        //checking validations
        $this->form_validation->set_rules($this->canned_message->validation_rules);

        if ($this->form_validation->run() === true) {
            $saved = false;
            $this->canned_message->model_data = $this->input->post(array('title', 'description'));
            $message_id = $this->input->post('id');
            if ($message_id) {
                $saved = $this->canned_message->update($message_id);
                $response['is_new'] = FALSE;
            } else {
                $this->canned_message->model_data['user_id'] = $this->current_user->id;
                $saved = $this->canned_message->insert();
                $response['is_new'] = TRUE;
                $response['created'] = $saved;
            }

            if ($saved) {
                $response['result'] = 'success';
                $response['description'] = $this->lang->line('canned_saved_success');
            } else {
                $response['description'] = $this->lang->line('canned_saved_error');
            }
        } else {
            $response['validation_errors'] = validation_errors();
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

    /*
     * To delete entry from database
     * 
     * @return Json $response
     */

    function delete() {
        $message_id = $this->input->get('id');
        
        $response = array('result' => 'failed', 'description' => '');

        if ($this->canned_message->delete_where(array("id" => $message_id))) {
            $response['result'] = 'success';
            $response['description'] = $this->lang->line('canned_deleted_success');
        } else {
            $response['description'] = $this->lang->line('canned_deleted_error');
        }

        return $this->output->set_content_type('application/json')->set_output($this->return_json($response));
    }

}
