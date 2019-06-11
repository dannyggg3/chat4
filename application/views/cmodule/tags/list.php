<div ng-controller="TagController">
    <div class="header-panel clearfix">
        <h2><?php echo $this->lang->line('departments'); ?></h2>
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
                    <th width="160"><?php echo $this->lang->line('department_name'); ?></th>
                    <th><?php echo $this->lang->line('agents'); ?></th>
                    <th class="text-center"><?php echo $this->lang->line('status'); ?></th>
                    <th width="40">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="record in records | orderBy:'tag_name'">
                    <td>{{record.tag_name}}</td>
                    <td><span ng-repeat="agent in record.agents" class="tag label">{{agent.name}}</span></td>
                    <td class="text-center"><a href="#/" ng-click="toogle_status($event, record)" class="status {{record.tag_status| statusClass:'publish'}}"><i class="fa fa-power-off"></i></a></td>
                    <td><div class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" href="#/"><i class="ellipsis-icon"></i></a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#formblock" data-toggle="modal" ng-click="edit($index, record)"><?php echo $this->lang->line('edit'); ?></a></li>
                                <li><a href="" ng-click="remove(record, '<?php echo $this->lang->line('confirm_delete_tag'); ?>')"><?php echo $this->lang->line('delete'); ?></a></li>
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

    <a href="#formblock" data-toggle="modal" class="btn btn-circle btn-primary btn-fixed">+</a>
    <!--Add New Form-->
    <div id="formblock" class="additional-section modal fade">
        <div class="modal-table">
            <div class="modal-cell">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close-model">&times;</button>
                        <h2 class="modal-title"><?php echo $this->lang->line('add_new_tag'); ?></h2>
                    </div>
                    <div class="modal-body">
                        <form name="tagForm" action="" method="post" ng-submit="save_tag($event) && tagForm.$valid">
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Department Name" ng-model="tag.tag_name" required />
                            </div>

                            <div class="form-footer">
                                <button class="btn btn-primary" type="submit" ng-disabled="!tagForm.$valid"><?php echo $this->lang->line('submit_save'); ?></button>
                                <button class="btn btn-default" type="button" ng-click="reset_tag()"  data-dismiss="modal"><?php echo $this->lang->line('submit_cancel'); ?></button>
                            </div>
                        </form>
                    </div>	
                </div>
            </div>
        </div>
    </div>		
    <!--End Add New Form-->	
</div>