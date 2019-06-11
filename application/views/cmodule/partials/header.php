<div class="row container-header">
    <div class="header-fixed">
        <div class="pull-left">
            <ul class="user-info pull-left pull-none-xsm">
                <li class="profile-info dropdown" ng-init="rand_color = getColor()">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="<?php echo site_url('c=users&m=edit_profile'); ?>">
                        <img ng-show="user.profile_pic" class="img-circle avatar" ng-src="{{user.profile_picture}}" alt="Profile Picture" title="" />
                        <span ng-hide="user.profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{user.name}}">{{user.name|oneCapLetter}}</span>
                        <?php echo $this->current_user->name; ?> <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo site_url('c=users&m=edit_profile'); ?>"><?php echo $this->lang->line('edit_profile'); ?></a></li>
                        <li><a href="<?php echo site_url('c=users&m=change_password'); ?>"><?php echo $this->lang->line('change_password'); ?></a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="pull-right">
            <ul class="list-link pull-right">
                <li><a href="<?php echo site_url('c=admin&m=logout'); ?>"><?php echo $this->lang->line('logout'); ?> <i class="fa fa-sign-out"></i></a></li>
            </ul>
        </div>
    </div>
</div>

<?php if ($this->session->userdata('dismis_update_alert') == 'no' and $this->session->userdata('new-version')): ?>
    <div class="alert alert-info alert-dismissible" role="alert">
        <a href="#" class="close" data-dismiss="alert" aria-label="close" ng-click="dismis_message($event);">&times;</a>
        <div><?php echo $this->session->userdata('notify-update-message');?></div>            
    </div>
<?php endif; ?>

<?php if ($this->settings->licence_key == ''): ?>
    <div class="alert alert-danger">
        <div><?php echo $this->lang->line('licence_key_missing'); ?></div>            
    </div>
<?php endif; ?>