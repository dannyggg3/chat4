<div class="container" ng-controller="InstallController">
    <div class="logo-chatbull">
        <a href="<?php echo site_url(); ?>">
            <img src="<?php echo theme_url("images/logo.png"); ?>" alt="chatbull" title="chatbull"> 
        </a>
    </div>
    <div class="installer-content">
        <h1 class="page-title">Three <strong>easy steps</strong> to <strong>setup</strong> <?php echo plugin_name();?> <strong class="text-danger"><?php echo CHATBULL_VERSION; ?></strong></h1>
        <ul class="nav nav-pills">
            <li><a href="#" ng-click="disable_click($event)"  ng-class="{'active':setup_db}"><span class="title">1. Setup Database</span></a></li>
            <li><a href="#" ng-click="disable_click($event)"  ng-class="{'active':setup_user}"><span class="title">2. Admin Account Setup</span></a></li>
            <li><a href="#" ng-click="disable_click($event)"  ng-class="{'active':setup_complete}"><span class="title">3. Configuration Info</span></a></li>
        </ul>

        <div class="alert alert-success" ng-show="notification.showMessage">
            <a href="#" class="close" aria-label="close" ng-click="notification.showMessage = false">&times;</a>
            <div ng-bind-html="'<strong>Success!</strong> ' + notification.message"></div>            
        </div>
        <div class="alert alert-danger" ng-show="notification.showErrors">
            <a href="#" class="close" aria-label="close" ng-click="notification.showErrors = false">&times;</a>
            <div ng-bind-html="notification.errors"></div>
        </div>

        <div class="clearfix" ng-show="visible_area == 'setup-db'">
            <form name="setupDb" action="" method="post" ng-submit="setup_database($event) && setupDb.$valid">
                <fieldset>
                    <div class="clearfix">
                        <h3>Database Info</h3>
                        <p>You can use the existing database but make sure that any of your existing table should not have &ldquo;chatbull_&rdquo; as prefix. Because that is what ChatBull is already using along with each table it creates.</p>
                        <p><strong>Also, we recommend you to take backup first if you are using an existing database.</strong></p>
                        <div class="row">
                            <div class="col-lg-5 col-md-6">
                                <div class="form-group">
                                    <input type="text" name="host" id="host" ng-model="db.host" value="<?php echo $this->input->post('host'); ?>" class="form-control" placeholder="Host Name" required>
                                    <?php echo form_error('host'); ?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="db_name" id="db_name" ng-model="db.db_name" value="<?php echo $this->input->post('db_name'); ?>" class="form-control" placeholder="Database Name" required>
                                    <?php echo form_error('db_name'); ?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="db_user" id="db_user" ng-model="db.db_user" value="<?php echo $this->input->post('db_user'); ?>" class="form-control" placeholder="Database User" required>
                                    <?php echo form_error('db_user'); ?>
                                </div>
                                <div class="form-group">
                                    <input type="text" name="db_password" id="db_password" ng-model="db.db_password" value="<?php echo $this->input->post('db_password'); ?>" class="form-control" placeholder="Database Password">
                                    <?php echo form_error('db_password'); ?>
                                </div>
                            </div>
                            <div class="col-lg-7 col-md-6"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit" ng-disabled="!setupDb.$valid">Continue</button>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
        <div class="clearfix" ng-show="visible_area == 'setup-user'">
            <form name="setupUser" action="" method="post" ng-submit="setup_admin($event) && setupUser.$valid">
                <fieldset>
                    <div class="row">
                        <div class="clearfix">
                            <div class="col-lg-6">
                                <legend>Setup your admin account</legend>
                                <div class="form-group">
                                    <input type="text" name="name" id="name" ng-model="user.name" value="<?php echo $this->input->post('name'); ?>" class="form-control" placeholder="Nombre" required>
                                    <?php echo form_error('name'); ?>
                                </div>

                                <div class="form-group">
                                    <input type="text" name="display_name" id="display_name" ng-model="user.display_name" value="<?php echo $this->input->post('display_name'); ?>" class="form-control" placeholder="Display Name" required>
                                    <?php echo form_error('display_name'); ?>
                                </div>

                                <div class="form-group">
                                    <input type="email" name="email" id="email" ng-model="user.email" value="<?php echo $this->input->post('email'); ?>" class="form-control" placeholder="Email" required>
                                    <?php echo form_error('email'); ?>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="password" id="password" ng-model="user.password" class="form-control" placeholder="Password" required>
                                    <?php echo form_error('password'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <div class="form-group">
                                <button class="btn btn-primary" type="submit" ng-disabled="!setupUser.$valid">Continue</button>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>

        <div class="clearfix" ng-show="visible_area == 'setup-complete'">
            <div class="form-group form-divider">
                <p><?php echo $this->lang->line('intalled_suucess'); ?></p>
                <div ng-bind-html="setup_completed_msg"></div>
                <p><a href="<?php echo site_url('c=admin'); ?>" target="_blank">Click here</a> to login to Admin section. </p>
            </div>

            <div id="installation" class="visitor-box-settings">
                <div class="form-group form-divider">
                    <h4><?php echo $this->lang->line('visitor_widget_help_heading'); ?></h4>
                    <div class="alert alert-info" role="alert"><?php echo $this->lang->line('visitor_widget_help_text'); ?></div>
                </div>

                <?php if (PRODUCT_NAME == 'chatbull'): ?>
                    <div id="setup-app" class="form-group form-divider">
                        <h5><?php echo $this->lang->line('application_url'); ?></h5>
                        <div class="bg-info"><?php highlight_string(rtrim(base_url(), '/')); ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>