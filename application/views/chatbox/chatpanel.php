<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<link href="<?php echo base_url('assets/cmodule-chat/css/chatbox.css'); ?>" rel="stylesheet">
<script type="text/javascript">
    var cmodule_site_url = '<?php echo site_url(''); ?>';
    var cmodule_base_url = '<?php echo base_url(); ?>';
    var access_token = '<?php echo $access_token; ?>';

    var windowName = window.name;
    var strArray = windowName.split('[!]');
    var ptitle = strArray[0];
    var purl = strArray[1];
    var siteuser = '<?php echo json_encode($siteuser); ?>';
    var cbwindow = <?php echo json_encode($cbwindow); ?>;
</script>
<script type="text/javascript" src="<?php echo base_url('assets/cmodule-chat/js/angularjs/jquery-1.8.0.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/scrollbar-plugin/js/jquery.mCustomScrollbar.concat.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/cmodule-chat/js/angularjs/angular.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/cmodule-chat/js/angularjs/angular-sanitize.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/cmodule-chat/js/angularjs/angular-animate.min.js'); ?>"></script>

<script src="<?php echo base_url("assets/angular-bootstrap/ui-bootstrap-tpls.min.js"); ?>"></script>
<script src="<?php echo base_url('assets/angular-rangeslider-directive-master/angular-range-slider.min.js'); ?>"></script>
<script src="<?php echo base_url("assets/angular-smilies/dist/angular-smilies.js"); ?>"></script>
<script src="<?php echo base_url("assets/angular-base64-upload/dist/angular-base64-upload.min.js"); ?>"></script>
<script src="<?php echo base_url('assets/cmodule-chat/js/app.js'); ?>"></script>

<?php if($this->settings->visitor_widget_type == 'chaticon' and $this->settings->enable_online_animation == 'yes'):?>
<style>
iframe.chat-cmodule-iframe {width: 112px;min-width: 112px;height: 125px;}
.chat-cmodule .cmodule-chat-btn.status-online {right: 15px; bottom: 28px;animation: pulse 2s linear infinite;}
.chat-cmodule .cmodule-chat-btn.status-online .cmodule-chat-handle {}
.chat-cmodule .large-size {width: 112px; height: 125px;}
.chat-cmodule .medium-size {width: 92px; height: 105px;}
.chat-cmodule .small-size {width: 71px; height: 84px;}

@-webkit-keyframes pulse {
    0% {
        -webkit-box-shadow: 0 0 0 0 rgba(204,169,44, 0.4);
    }
    70% {
        -webkit-box-shadow: 0 0 0 15px <?php echo hex2rgba($this->settings->background_color, 0.7);?>;
    }
    100% {
        -webkit-box-shadow: 0 0 0 0 rgba(204,169,44, 0);
    }
}
@keyframes pulse {
    0% {
        -moz-box-shadow: 0 0 0 0 rgba(204,169,44, 0.4);
        box-shadow: 0 0 0 0 rgba(204,169,44, 0.4);
    }
    65% {
        -moz-box-shadow: 0 0 0 15px <?php echo hex2rgba($this->settings->background_color, 0.7);?>;
        box-shadow: 0 0 0 15px <?php echo hex2rgba($this->settings->background_color, 0.7);?>;
    }
    100% {
        -moz-box-shadow: 0 0 0 0 rgba(204,169,44, 0);
        box-shadow: 0 0 0 0 rgba(204,169,44, 0);
    }
}
</style>
<?php endif;?>
</head>
<body ng-app="cmodule">
    <div ng-controller="BodyController" ng-cloak>  
        <?php include VIEWPATH . 'chatbox/minibox.php'; ?>
    </div>
    <script src="<?php echo base_url('assets/cmodule-chat/js/iframeResizer.contentWindow.min.js'); ?>"></script>
</body>
</html>