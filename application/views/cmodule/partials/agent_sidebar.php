<div class="page-sidebar">
    <div class="user-info-block">
        <div class="dropdown" ng-init="rand_color = getColor()">
            <a class="user-dropdown dropdown-toggle" href="<?php echo site_url('d=agents&c=agents'); ?>" data-toggle="dropdown"></a>
            <img ng-show="user.profile_pic" class="img-circle avatar" ng-src="{{user.profile_picture}}" alt="Profile Picture" title="" />
            <span ng-hide="user.profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{user.name}}">{{user.name|oneCapLetter}}</span>
            <span class="profile-info"><span class="user-name"><?php echo $this->current_user->name; ?> <span class="caret"></span></span><br><a class="user-logout" href="<?php echo site_url('c=admin&m=logout'); ?>"><i class="fa fa-sign-out"></i> <?php echo $this->lang->line('logout'); ?></a></span>
            <ul class="dropdown-menu">
                <li><a href="<?php echo site_url('d=agents&c=agents'); ?>"><?php echo $this->lang->line('edit_profile'); ?></a></li>
                <li><a href="<?php echo site_url('d=agents&c=agents&m=change_password'); ?>"><?php echo $this->lang->line('change_password'); ?></a></li>
                <?php if($this->data['browser_name'] == 'chrome'):?>
                <li><a href="#" ng-click="disable_click($event)" class="js-push-button"><?php echo $this->lang->line('enable_push_notification'); ?></a></li>
                <?php endif;?>
                <li><a href="<?php echo site_url('c=admin&m=logout'); ?>"><?php echo $this->lang->line('logout'); ?></a></li>
            </ul>
        </div>
    </div>
    
    <div class="user-panel" ng-controller="ChatlistController">
        <ul role="tablist" class="nav nav-tabs">
            <li class="active"><a href="#recent-chats" aria-controls="recent-chats" role="tab" data-toggle="tab" id="tab-recent-chats"><?php echo $this->lang->line('recent_chats'); ?></a></li>
            <li class=""><a href="#history" aria-controls="history" role="tab" data-toggle="tab" ng-click="get_closed_chats(true)" id="tab-chat-history"><?php echo $this->lang->line('chat_history'); ?></a></li>
        </ul>
        
        <div class="tab-content">
            <div class="tab-pane active chat-sidebar sidebar-recent-chats" id="recent-chats">
                <ul class="user-list">
                    <li ng-show="show_sidebar_alert">
                        <div class="alert {{sidebar_alert_class}}">{{sidebar_message}}</div>
                    </li>
                    <li ng-repeat="row in recent_chats" ng-init="rand_color = getColor()">
                        <span class="chat-item" id="chat-session-{{row.id}}" ng-click="get_chat_session(row, rand_color)" ng-class="{'active': chat_session_id == row.id}" ng-init="canPlaySound(chat_session_id, row.id, row.unread)">
                            <img ng-show="row.profile_pic" class="img-circle avatar" ng-src="{{row.profile_picture}}" alt="{{row.name}}" title="{{row.name}}">
                            <span ng-hide="row.profile_pic" style="background-color: {{(chat_session_id == row.id) ? active_color : rand_color}};" class="user-avatar" title="{{row.name}}">{{row.name|oneCapLetter}}</span>
                            <span class="user-name" ng-class="{'strong':row.unread > 0}">{{row.name}}</span>
                            <span ng-show="row.unread > 0" class="pull-right message-counter badge badge-warning">{{row.unread}}</span>
                        </span>
                    </li>
                    <li ng-hide="recent_chats.length">
                        <span class="chat-item"><span class="user-name"><?php echo $this->lang->line('no_request'); ?></span></span>
                    </li>
                </ul>
            </div>
            <div class="tab-pane chat-sidebar sidebar-chat-history" id="history">
                <ul class="user-list">
                    <li ng-show="show_sidebar_alert">
                        <div class="alert {{sidebar_alert_class}}">{{sidebar_message}}</div>
                    </li>

                    <li ng-repeat="row in chat_history| orderBy:'-message_id'" ng-init="rand_color = getColor()">
                        <span class="chat-item" ng-click="get_chat_session(row, rand_color)" ng-class="{'active': chat_session_id == row.id}">
                            <img ng-show="row.profile_pic" class="img-circle avatar" ng-src="{{row.profile_picture}}" alt="{{row.name}}" title="{{row.name}}">
                            <span ng-hide="row.profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{row.name}}">{{row.name|oneCapLetter}}</span>
                            <span class="user-name">{{row.name}}</span>
                        </span>
                    </li>
                    
                    <li ng-show="in_process" class="processing-loader-block">
                        <p class="processing-loader"><i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i> <span><?php echo $this->lang->line('loading'); ?></span></p>
                    </li>
                    
                    <li ng-hide="chat_history.length && !in_process">
                        <span class="chat-item" ng-click="disable_click($event)"><span class="user-name"><?php echo $this->lang->line('no_request'); ?></span></span>
                    </li>                    
                </ul>
            </div>
        </div>
    </div>
</div>