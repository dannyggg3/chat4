<?php if (PRODUCT_NAME == 'chatbull'): ?>
    <div id="setup-app" class="form-group form-divider">
        <h4><?php echo $this->lang->line('application_url'); ?></h4>
        <div class="bg-info"><?php highlight_string(rtrim(base_url(), '/')); ?></div>
    </div>
<?php endif; ?>

<div class="form-group form-divider">
    <div class="bg-info"><?php echo $this->lang->line('token_info_text'); ?></div>
</div>

<div ng-if="tokens.length > 0" class="table-responsive">
    <table class="table token-table">
        <thead>
            <tr>
                <th><?php echo $this->lang->line('site_url'); ?></th>
                <th class="text-center"><?php echo $this->lang->line('chatbox_code'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr ng-repeat="record in tokens| orderBy:'site_url'">
                <td>
                    <p>
                        <span class="pull-right">
                            <a class="btn btn-default btn-xs" href="#" ng-click="edit_token($event, record)" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a>
                        </span>
                        <span>{{record.site_url}}</span>
                    </p>
                </td>
                <td>
                    <?php
                    $html_code = '<script type="text/javascript">';
                    $html_code .= "var cbuser = {name: '', email: '', message: ''};";
                    $html_code .= "var cburl = '" . base_url() . "', access_token = '{{record.token}}';";
                    $html_code .= "document.write('<script type=\"text/javascript\" src=\"' + cburl + 'assets/cmodule-chat/js/chatbull-init.js\"></' + 'script>');";
                    $html_code .= '</script>';
                    ?>
                    <div class="bg-info" select-on-click><?php highlight_string($html_code); ?></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div ng-if="tokens.length == 0" class="form-group form-divider">
    <p><?php echo $this->lang->line('no_chatbox_code'); ?></p>
</div>

<div class="form-group form-divider">
    <div class="row">
        <div class="col-md-8">
            <input class="form-control" placeholder="<?php echo $this->lang->line('site_url'); ?>" type="text" ng-model="record.site_url" />
        </div>
        <div class="col-md-4">
            <button ng-show="!record.id" class="btn btn-primary" ng-click="generate_code($event)"><?php echo $this->lang->line('generate_code'); ?></button>
            <button ng-show="record.id" class="btn btn-primary" ng-click="update_code($event)"><?php echo $this->lang->line('update_code'); ?></button>
            <button ng-show="record.id" class="btn btn-default" ng-click="cancel_action($event)"><?php echo $this->lang->line('cancel'); ?></button>
        </div>
    </div>
</div>
