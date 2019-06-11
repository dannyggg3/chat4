<?php

/*
 * Controller to manage all users related actions
 * Author: Pukhraj Prajapat (pukhraj.prajapat@g-axon.com)
 */

class Feedbacks extends CP_AdminController {
    /*
     * Calling parent constructor
     */

    public function __construct() {
        parent::__construct();
        
        // check has admin access
        $this->has_admin_access();
    }

    /*
     * To load view for feedbacks management
     */

    function index() {
        // add users.js file
        $this->addjs(theme_url("js/app/feedback.js"), TRUE);

        // get all agents with admin
        $agents = $this->user->get_all(array('role !=' => 'visitor'), "id,name");
        $this->add_js_inline(array('agents' => $agents, 'user' => $this->current_user, 'settings' => $this->settings));

        // add filter file.
        $this->data['filter_view'] = 'feedback/filters.php';

        //write code here to load view
        $this->bulid_layout("feedback/list");
    }

    /*
     * This function will load feedback.
     * 
     * @return $feddbacks json array
     */

    public function get_feddback_list() {
        $feddbacks = $this->feedback->get_feddbacks($this->input->post());
        
        return $this->output->set_content_type('application/json')->set_output($this->return_json($feddbacks));
    }

}
