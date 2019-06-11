<div class="chat-section" ng-controller="WorkroomController">
    <div ng-show="visible_area == 'workroom'" class="row">        
        <div class="col-xs-12">
            <div class="container-header-secondary">
                <div class="header-fixed clearfix">
                    <div class="pull-left user-info-section">
                        <img ng-show="visitor.profile_pic" class="img-circle avatar" ng-src="{{visitor.profilePic}}" alt="" title="{{visitor.name}}">
                        <span ng-hide="visitor.profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{visitor.name}}">{{visitor.name|oneCapLetter}}</span>
                        <span ng-show="visitor.name" class="profile-info text-danger">
                            <span class="user-name" ng-show="user.id != visitor.id">{{visitor.name}}</span>
                            <span class="user-name" ng-show="user.id == visitor.id">You</span>
                            
                            <div class="list-action">
                                <span class="tag label">{{visitor.email}}</span>
                                <span ng-if="visitor.ip_address" title="{{visitor.ip_address}}" class="tag label">IP = {{visitor.ip_address}}</span>
                                <a ng-if="visitor.page_title && visitor.page_title != null" target="_blank" ng-href="{{visitor.page_url}}" title="{{visitor.page_title}}"><span class="tag label">{{visitor.page_title| cut:true:25:' ...'}}</span></a>
                            </div>
                            
                            <ul class="list-action list-inline" ng-hide="chat_session.session_status == 'closed'">
                                <li ng-show="chat_session.session_status == 'open'">
                                    <a href="#formblock" data-toggle="modal" ng-click="display_forward($event)"><img src="<?php echo theme_url("images/forword_chat.png"); ?>" alt="Forword" title="Forword" /></a>
                                </li>
                                <li ng-show="chat_session.session_status == 'open' || chat_session.session_status == 'on-hold' || chat_session.session_status == 'disconnected'">
                                    <a href="#/" ng-click="end_chat($event)"><img src="<?php echo theme_url("images/close_chat.png"); ?>" alt="Close" title="Close" /></a>
                                </li>
                            </ul>
                        </span>
                        <span class="{{visitor.status}}-status" title="{{visitor.status}}"></span>
                    </div>
                    <div class="pull-right">
                        <ul class="request-list">
                            <li><a  id="link-new-requests" href="" ng-click="show_new_requests($event)" class="btn btn-primary"><?php echo $this->lang->line('new_requests'); ?> <span ng-show="new_requests_counter > 0">({{new_requests_counter}})</span></a></li>
                            <li><a  href="<?php echo site_url('d=agents&c=orequests'); ?>" class="btn btn-primary"><?php echo $this->lang->line('offline_requests'); ?>  <span ng-show="offlineRequestsCounter > 0">({{offlineRequestsCounter}})</span></a></li>
                        </ul>
                    </div>
                </div>					
            </div>
            <div class="chat-container">
                <div class="chat-container-frame" ng-class="{'disabled-chatting':chat_session.session_status != 'open' && chat_session.session_status != 'on-hold' && chat_session.session_status != 'disconnected'}" id="message_box">
                    <div class="chat-row {{row.class}}" ng-repeat="row in messages| orderBy:'sort_order'" ng-class="{'reply': row.sender_id != visitor.id}" ng-mouseover="row.class = ''"  on-last-repeat>
                        <img ng-show="row.profile_pic && row.sender_id == visitor.id" class="img-circle avatar" ng-src="{{row.profilePic}}" alt="" title="{{row.name}}">
                        <span ng-hide="row.profile_pic || row.sender_id != visitor.id" style="background-color: {{rand_color}};" class="user-avatar" title="{{row.name}}">{{row.name|oneCapLetter}}</span>
                        <div title="{{row.name}}" class="chat-message"><p ng-bind-html="row.chat_message | newlines | smilies"></p></div>
                    </div>
                </div>
            </div>

            <div class="status-typing-indecator" ng-show="chat_session.session_status == 'open' || chat_session.session_status == 'on-hold' || chat_session.session_status == 'disconnected'">
                <div class="message-status-typing" ng-show="is_typing && chat_session.session_status == 'open'">
                    <i class="fa fa-pencil">&nbsp;</i> {{visitor.name}} is typing....
                </div>
                <div ng-show="new_msg_indecator && is_scroll_start && chat_session.session_status == 'open'" class="chat-cmodule-new-message-indecator"></div>
            </div>

            <div class="alert alert-success" ng-show="notification.showMessage">
                <a href="#" class="close" aria-label="close" ng-click="notification.showMessage = false">&times;</a>
                <div ng-bind-html="'<strong>Success!</strong> ' + notification.message"></div>            
            </div>

            <div class="alert alert-danger" ng-show="notification.showErrors">
                <a href="#" class="close" aria-label="close" ng-click="notification.showErrors = false">&times;</a>
                <div ng-bind-html="notification.errors"></div>
            </div>
            
            <form name="chatForm" id="chatForm" action="" method="post" ng-submit="send_message($event)" ng-show="chat_session.session_status == 'open' || chat_session.session_status == 'on-hold' || chat_session.session_status == 'disconnected'">
                <div class="message-chat-panel">                    
                    <div class="form-group chat-message-box">
                        <textarea ng-focus="chatboxState = 'focus'" focus-on-change="new_message" ng-blur="chatboxState = 'blur'" cols="40" name="message" ng-model="new_message" id="message" placeholder="Message" class="form-control" ng-keydown="submit_message($event)" ng-disabled="visitor.status == 'offline'"></textarea>
                   </div>	
                    <button id="send-btn" class="btn btn-link btn-lg" ng-disabled="!new_message || visitor.status == 'offline'"><i class="fa fa-paper-plane"></i></button>
                </div>
                <div class="chat-toolbar">
                    <span class="chat-toolbar-btn smilies-handle" smilies-selector="new_message" smilies-placement="top-right" smilies-title="Smilies"></span>
                    <span class="chat-toolbar-btn canned-message canned-message-handle" message-data="new_message" canned-options="cannedOptions"></span>
                    <span class="chat-toolbar-btn attachment-handle">
                        <i aria-hidden="true" class="fa fa-file-o" role="button" title="Send file"></i>
                        <input title="Send file" class="chat-input-file" on-after-validate="upload_files" onerror="file_error_handler" type="file" role="button" ng-model="chatfiles" name="chatfiles" accept="<?php echo str_replace("|", ",", $this->settings->allowed_filetypes);?>" maxsize="<?php echo $this->settings->file_upload_size;?>" base-sixty-four-input>
                    </span>
                    <span class="chat-toolbar-btn" ng-show="is_file_sending && chatForm.chatfiles.$valid"><?php echo $this->lang->line('sending_file');?></span>
                    <span class="chat-toolbar-btn text-danger" ng-show="chatForm.chatfiles.$error.maxsize"><?php echo $this->lang->line('exceeded_filesize');?></span>
                    <span class="chat-toolbar-btn text-danger" ng-show="chatForm.chatfiles.$error.accept"><?php echo $this->lang->line('invalid_filetype');?></span>
                </div>
            </form>            
        </div>

        <div id="formblock" class="additional-section modal fade">
            <div class="modal-table">
                <div class="modal-cell">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close-model">&times;</button>
                            <h2 class="modal-title"><?php echo $this->lang->line('forward_chat'); ?></h2>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-xs-12">
                                    <div ng-show="showError" ng-bind-html="errors"></div>
                                    <form name="forwardForm" action="" method="post" ng-submit="forward_chat($event, forward) && forwardForm.$valid">
                                        <div class="form-group" ng-init="forward.to = 'agents'">
                                            <h5><?php echo $this->lang->line('forward_to'); ?></h5>
                                            <div class="radio"><label><input ng-model="forward.to" type="radio" value="agents" ng-required="!forward.to"> <?php echo $this->lang->line('agents'); ?></label></div>
                                            <div class="radio"><label><input ng-model="forward.to" type="radio" value="departments" ng-required="!forward.to"> <?php echo $this->lang->line('departments'); ?></label></div>
                                        </div>

                                        <div class="form-group" ng-show="forward.to == 'agents'" ng-init="forward.agents = []">
                                            <h5><?php echo $this->lang->line('agents'); ?></h5>
                                            <div ng-repeat="agent in agents_list" class="checkbox"><label><input ng-model="forward.agents[$index]" type="checkbox" ng-true-value="{{agent.id}}" ng-false-value=""> {{agent.name}}</label></div>
                                        </div>

                                        <div class="form-group" ng-show="forward.to == 'departments'">
                                            <h5><?php echo $this->lang->line('departments'); ?></h5>
                                            <div ng-repeat="department in departments_list" class="radio"><label><input ng-model="forward.department" type="radio" value="{{department.id}}"> {{department.department}}</label></div>
                                        </div>
                                        <div class="panel">
                                            <button id="send-btn" class="btn btn-primary" ng-disabled="!forwardForm.$valid || (forward.agents.length == 0 && !forward.department)"><?php echo $this->lang->line('forward'); ?></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>	
                    </div>
                </div>
            </div>
        </div>
    </div>
    <audio id="audio1">
        <source src="<?php echo theme_url("audio/ping.mp3"); ?>"></source>
    </audio>
</div>