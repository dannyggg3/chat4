<div ng-controller="UserController">
    <div class="clearfix"> 
        <div class="header-panel clearfix">
            <h2 class="panel-title pull-left"><?php echo $this->lang->line('agents_and_visitors'); ?></h2>
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
                        <th><?php echo $this->lang->line('name'); ?></th>
                        <th><?php echo $this->lang->line('email'); ?></th>
                        <th><?php echo $this->lang->line('department'); ?></th>
                        <th><?php echo $this->lang->line('role'); ?></th>
                        <th><?php echo $this->lang->line('status'); ?></th>
                        <th width="40">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="record in records | orderBy:'role'">
                        <td ng-init="rand_color = getColor()">
                            <img ng-show="record.profile_pic" class="img-circle avatar" ng-src="{{record.profilePic}}" alt="Profile Picture" title="" />
                            <span style="background-color: {{rand_color}};" ng-hide="record.profile_pic" class="user-avatar" title="{{record.name}}">{{record.name|oneCapLetter}}</span>
                        </td>
                        <td>{{record.name}}</td>
                        <td><a href="mailto:{{record.email}}">{{record.email}}</a></td>
                        <td>
                            <span ng-repeat="tag in record.tags" class="tag label">{{tag.tag_name}}</span>
                        </td>
                        <td><span class="badge badge-outline">{{record.role}}</span></td>
                        <td class="text-center"><a ng-show="record.role == 'agent'" href="#/" ng-click="toogle_status($event, record)" class="status {{record.user_status| statusClass:'active'}}"><i class="fa fa-power-off"></i></a></td>
                        <td><div class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" href="#/"><i class="ellipsis-icon"></i></a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li ng-if="record.role != 'visitor'"><a href="#formblock" data-toggle="modal" ng-click="edit_user($index, record)"><?php echo $this->lang->line('edit'); ?></a></li>
                                    <li ng-if="record.role != 'visitor'"><a href="#passwordformblock" data-toggle="modal" ng-click="change_password(record)"><?php echo $this->lang->line('change_password'); ?></a></li>
                                    <li ng-if="record.role != 'admin'"><a href="" ng-click="delete_user(record, '<?php echo $this->lang->line('confirm_deleted'); ?>')"><?php echo $this->lang->line('delete'); ?></a> </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="load-more-block">
            <div ng-show="showNoRecordMessage" role="alert" class="alert alert-info"> <?php echo $this->lang->line('no_record_found'); ?></div>
            <button class="btn btn-primary text-center" ng-click="load_more()"><?php echo $this->lang->line('load_more'); ?> <i ng-show="loading" class="fa fa-refresh fa-spin"></i></button>
        </div>
        <a class="btn btn-circle btn-primary btn-fixed" href="#formblock" data-toggle="modal" ng-click="add($event)">+</a>
    </div>
    <?php include theme_path('users/form.php'); ?>
    <?php include theme_path('users/crop-image-form.php'); ?>
    <?php include theme_path('users/change_password.php'); ?>
</div>