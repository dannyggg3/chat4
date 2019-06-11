<div class="container" ng-controller="UpdateController">
    <div class="logo-chatbull">
        <a href="<?php echo site_url(); ?>">
            <img src="<?php echo theme_url("images/logo.png"); ?>" alt="chatbull" title="chatbull"> 
        </a>
    </div>
    <div class="installer-content">
        <h1 class="page-title"><strong>{{pagetitle}}</strong></h1>

        <?php if ($this->session->userdata('new-version')): ?>
            <div class="clearfix" ng-show="download_button">
                <p><?php echo $this->lang->line('update_introduction_text'); ?></p>
                <p><strong><?php echo $this->lang->line('take_backup_first'); ?></strong></p>
            </div>
        
            <div class="alert alert-success" ng-show="notification.showMessage">
                <a href="#" class="close" aria-label="close" ng-click="notification.showMessage = false">&times;</a>
                <div ng-bind-html="'<strong>Success!</strong> ' + notification.message"></div>            
            </div>
            <div class="alert alert-danger" ng-show="notification.showErrors">
                <a href="#" class="close" aria-label="close" ng-click="notification.showErrors = false">&times;</a>
                <div ng-bind-html="notification.errors"></div>
            </div>
        
            <div class="clearfix" ng-show="download_button">
                <div ng-show="processing"><i class="fa fa-spinner fa-pulse"></i> {{processing_text}}</div>
                <button class="btn btn-primary" type="submit" ng-click="start_update($event)" ng-disabled="processing">Download Update</button>
            </div>

            <div class="clearfix" ng-hide="download_button">
                <?php echo $this->lang->line('updation_done'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>