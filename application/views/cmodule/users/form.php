<div id="formblock" class="additional-section modal fade">
    <div class="modal-table">
        <div class="modal-cell">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="close-model">&times;</button>
                    <h2 class="modal-title" ng-hide="is_edit"><?php echo $this->lang->line('add_new_user'); ?></h2>
                    <h2 class="modal-title" ng-show="is_edit"><?php echo $this->lang->line('edit_user'); ?></h2>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success" ng-show="notification.showMessage">
                        <a href="#" class="close" aria-label="close" ng-click="notification.showMessage = false">&times;</a>
                        <div ng-bind-html="'<strong>Success!</strong> ' + notification.message"></div>            
                    </div>

                    <div class="alert alert-danger" ng-show="notification.showErrors">
                        <a href="#" class="close" aria-label="close" ng-click="notification.showErrors = false">&times;</a>
                        <div ng-bind-html="notification.errors"></div>
                    </div>

                    <div class="clearfix">
                        <form name="userForm" action="" method="post" ng-submit="save_user($event, user.index) && userForm.$valid">
                            <input type="hidden" ng-model="user.role" value="agent" required>
                            <div class="row">
                                <div class="clearfix">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <input class="form-control" type="text" placeholder="Nombre" ng-model="user.name" required />
                                        </div>

                                        <div class="form-group">
                                            <input class="form-control" type="text" placeholder="Display Name" ng-model="user.display_name" required />
                                        </div>

                                        <div class="form-group">
                                            <input class="form-control" type="email" placeholder="Email" ng-model="user.email" required />
                                        </div>

                                        <div class="form-group">
                                            <input class="form-control" type="text" placeholder="Contact Number" ng-model="user.contact_number" />
                                        </div>

                                        <div class="form-group form-group-picture" ng-show="user.profile_pic">
                                            <img id="upload-profile_pic" class="" ng-src="{{user.profile_picture}}" alt="Profile Picture" title="" />
                                            <a href="#/" ng-click="remove_photo(user.id, $event)" class="remove-user-pic"><i class="fa fa-times"></i></a>
                                        </div>

                                        <div class="form-group">
                                            <div class="input-group">
                                                <span class="input-group-btn">
                                                    <span class="btn btn-sn btn-default btn-file">
                                                        Browse… <input id="upload-profile_pic" type="file" file-input="files">
                                                    </span>
                                                </span>
                                                <input type="text" disabled="disabled" class="form-control" ng-model="filename">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group input-max" ng-hide="is_edit">
                                            <input class="form-control" type="password" placeholder="Password" ng-model="user.pass" ng-if="is_edit == false" required />
                                        </div>

                                        <div class="form-group input-max" ng-hide="is_edit">
                                            <input class="form-control" type="password" placeholder="Confirm Password" ng-model="user.confirm_pass" ng-if="is_edit == false" required />
                                        </div>

                                        <div class="form-group input-max clearfix">
                                            <label><?php echo $this->lang->line('departments'); ?></label>
                                            <div ng-repeat="tag in tags" class="checkbox"><label><input ng-model="user.tags[tag.id]" type="checkbox" ng-true-value="'{{tag.id}}'" ng-false-value="''"> {{tag.tag_name}}</label></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-footer">
                                        <button class="btn btn-primary" type="submit" ng-disabled="!userForm.$valid"><?php echo $this->lang->line('submit'); ?></button>
                                        <button class="btn btn-default" type="button" ng-click="toggle_user_form(true)" data-dismiss="modal"><?php echo $this->lang->line('submit_cancel'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>	
            </div>
        </div>
    </div>
</div>		
<!--End Add New Form-->