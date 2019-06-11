<div ng-controller="ChathistoryController">
    <div class="header-panel clearfix">
        <h2 class="panel-title pull-left"><?php echo $this->lang->line('chat_history'); ?></h2>
        <div ng-show="filter_button" class="pull-right col-filter"><a href="#/" ng-click="show_filter($event)"><i class="fa fa-filter"></i></a></div>
    </div>
    <div class="alert alert-success" ng-show="notification.showMessage">
        <a href="#" class="close" aria-label="close" ng-click="notification.showMessage = false">&times;</a>
        <div ng-bind-html="'<strong>Success!</strong> ' + notification.message"></div>            
    </div>

    <div class="alert alert-danger" ng-show="notification.showErrors">
        <a href="#" class="close" aria-label="close" ng-click="notification.showErrors = false">&times;</a>
        <div ng-bind-html="notification.errors"></div>
    </div>

    <div class="table-responsive">
        <table class="table user-table">
            <thead>
                <tr>
                    <th width="60">&nbsp;</th>
                    <th><?php echo $this->lang->line('visitor_name'); ?></th>
                    <th><?php echo $this->lang->line('message'); ?></th>
                    <th><?php echo $this->lang->line('status'); ?></th>
                    <th width="40">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="record in records">
                    <td ng-init="rand_color = getColor()">
                        <img ng-show="record.profile_pic" class="img-circle avatar" ng-src="{{record.visitorProfilePic}}" alt="Profile Picture" title="" />
                        <span ng-hide="record.profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{record.visitorName}}">{{record.visitorName|oneCapLetter}}</span>
                    </td>
                    <td><a href="#formblock" data-toggle="modal" ng-click="get_conversations(record)">{{record.visitorName}}</a></td>
                    <td><p ng-bind-html="record.chat_message | cut:true:100:' ...' | newlines"></p></td>
                    <td width="60">{{record.session_status| capitalize}}</td>
                    <td>
                        <div class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" href="#/"><i class="ellipsis-icon"></i></a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#formblock" data-toggle="modal" ng-click="get_conversations(record)"><?php echo $this->lang->line('conversation'); ?></a></li>
                                <li><a href="" ng-click="delete_record(record, '<?php echo $this->lang->line('confirm_deleted_chat'); ?>')"><?php echo $this->lang->line('delete'); ?></a> </li>
                            </ul>
                        </div>						
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="load-more-block">
        <div ng-show="showNoRecordMessage" role="alert" class="alert alert-info"> <?php echo $this->lang->line('no_record_found'); ?></div>
        <button class="btn btn-success btn-primary text-center" ng-click="load_more()"><?php echo $this->lang->line('load_more'); ?> <i ng-show="loading" class="fa fa-refresh fa-spin"></i></button>
    </div>

    <div id="formblock" class="additional-section modal fade">
        <div class="modal-table">
            <div class="modal-cell">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close-model">&times;</button>
                        <h2 class="modal-title"><?php echo $this->lang->line('chat_conversation'); ?></h2>
                        <div class="list-action">
                            <span class="tag label">{{visitor.email}}</span>
                            <span ng-if="visitor.ip_address" title="{{visitor.ip_address}}" class="tag label">IP = {{visitor.ip_address}}</span>
                            <span ng-if="visitor.started_at" class="tag label">{{visitor.started_at | datetimeToTimestamp | date:'medium'}}</span>
                            <a ng-if="visitor.page_title" target="_blank" ng-href="{{visitor.page_url}}" title="{{visitor.page_title}}"><span class="tag label">{{visitor.page_title}}</span></a>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="row">        
                            <div class="col-xs-12">
                                <div class="chat-container" id="message_box">
                                    <div class="chat-row" ng-repeat="row in conversations" ng-class="{'reply': row.sender_id != visitor.id}" on-last-repeat>
                                        <img ng-show="row.profile_pic && row.sender_id == visitor.id" class="img-circle avatar" ng-src="{{row.profile_picture}}" alt="{{row.name}}" title="{{row.name}}">
                                        <span ng-hide="row.profile_pic || row.sender_id != visitor.id" style="background-color: {{rand_color}};" class="user-avatar" title="{{row.name}}">{{row.name|oneCapLetter}}</span>
                                        <div title="{{row.name}}" class="chat-message"><p ng-bind-html="row.chat_message | newlines | smilies"></p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>	
                </div>
            </div>
        </div>
    </div>
</div>