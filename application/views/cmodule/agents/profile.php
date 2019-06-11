<div class="row-container clearfix" ng-controller="ProfileController">
    <div ng-show="visible_area == 'main-content'" class="col-lg-12">
        <div class="container-header-secondary">
            <div class="header-fixed clearfix">
                <div class="pull-left user-info-section" ng-init="rand_color = getColor()">
                    <img ng-show="user.profile_pic" class="img-circle avatar" ng-src="{{user.profile_picture}}" alt="Profile Picture" title="" />
                    <span ng-hide="user.profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{user.name}}">{{user.name|oneCapLetter}}</span>
                    <span class="profile-info text-danger">
                        <span class="user-name">{{user.name}}</span>
                        <span class="title-2"><?php echo $this->lang->line('edit_profile'); ?></span>
                    </span>                    
                </div>
                <div class="pull-right">
                    <ul class="request-list">
                        <li><a  id="link-new-requests" href="" ng-click="show_new_requests($event)" class="btn btn-primary"><?php echo $this->lang->line('new_requests'); ?> <span ng-show="new_requests_counter > 0">({{new_requests_counter}})</span></a></li>
                        <li><a  href="<?php echo site_url('d=agents&c=orequests'); ?>" class="btn btn-primary"><?php echo $this->lang->line('offline_requests'); ?>  <span ng-show="offlineRequestsCounter > 0">({{offlineRequestsCounter}})</span></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="alert alert-success" ng-show="notification.showMessage">
            <a href="#" class="close" aria-label="close" ng-click="notification.showMessage = false">&times;</a>
            <div ng-bind-html="'<strong>Success!</strong> ' + notification.message"></div>            
        </div>

        <div class="alert alert-danger" ng-show="notification.showErrors">
            <a href="#" class="close" aria-label="close" ng-click="notification.showErrors = false">&times;</a>
            <div ng-bind-html="notification.errors"></div>
        </div>

        <div class="clearfix">
            <form name="userForm" action="" method="post" ng-submit="save_user($event) && userForm.$valid">
                <input type="hidden" ng-model="user.role" value="agent" required>
                <div class="row">
                    <div class="clearfix">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Nombre" ng-model="user.name" required />
                            </div>

                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Display Name" ng-model="user.display_name" required />
                            </div>

                            <div class="form-group">
                                <input class="form-control" type="text" placeholder="Contact Number" ng-model="user.contact_number" />
                            </div>

                            <div class="form-group form-group-picture" ng-show="user.profile_pic">
                                <img class="" ng-src="{{user.profile_picture}}" alt="Profile Picture" title="" />
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
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('departments'); ?></label>
                                <div ng-repeat="tag in tags" class="checkbox"><label><input ng-model="user.tags[tag.id]" type="checkbox" ng-true-value="'{{tag.id}}'" ng-false-value="''"> {{tag.tag_name}}</label></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit" ng-disabled="!userForm.$valid"><?php echo $this->lang->line('submit'); ?></button>
                            <button class="btn btn-default" type="button" ng-click="cancel()"><?php echo $this->lang->line('submit_cancel'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
            <?php include theme_path('agents/crop-image-form.php'); ?>
        </div>
    </div>
</div>