<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $this->settings->site_name; ?> Visitor Panel</title>
<link href="<?php echo base_url('assets/cmodule-chat/css/chatbox.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/cmodule-chat/css/cmodule-responsive-chat.css'); ?>" rel="stylesheet">

<!--<link href="<?php echo base_url('assets/cmodule-chat/css/chat.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/cmodule-chat/css/cmodule-responsive-chat.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/angular-rangeslider-directive-master/angular-range-slider.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/scrollbar-plugin/css/jquery.mCustomScrollbar.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/angular-smilies/dist/angular-smilies-embed.min.css'); ?>" rel="stylesheet" type="text/css" />-->

<script>
    var site_url = '<?php echo site_url('/'); ?>';
    var cmodule_site_url = '<?php echo site_url();?>/';
    var cmodule_base_url = '<?php echo base_url();?>';
    var access_token = '<?php echo $access_token;?>';
    var siteuser = '<?php echo json_encode($siteuser);?>';
    var cbwindow = <?php echo json_encode($cbwindow);?>;
    var pageinfo = <?php echo json_encode($pageinfo);?>;
</script>

<script type="text/javascript" src="<?php echo base_url('assets/cmodule-chat/js/angularjs/jquery-1.8.0.min.js');?>"></script>
<script src="<?php echo base_url('assets/scrollbar-plugin/js/jquery.mCustomScrollbar.concat.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/cmodule-chat/js/angularjs/angular.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/cmodule-chat/js/angularjs/angular-sanitize.min.js'); ?>"></script>

<script src="<?php echo base_url("assets/angular-bootstrap/ui-bootstrap-tpls.min.js"); ?>"></script>
<script src="<?php echo base_url('assets/angular-rangeslider-directive-master/angular-range-slider.min.js'); ?>"></script>
<script src="<?php echo base_url("assets/angular-smilies/dist/angular-smilies.js"); ?>"></script>
<script src="<?php echo base_url("assets/angular-base64-upload/dist/angular-base64-upload.min.js"); ?>"></script>

<script src="<?php echo base_url('assets/cmodule-chat/js/mobileapp.js'); ?>"></script>

<link rel="shortcut icon" href="<?php echo theme_url('icon/favicon.ico'); ?>" type="image/x-icon">
<link rel="icon" href="<?php echo theme_url('icon/favicon.ico'); ?>" type="image/x-icon">

<link rel="apple-touch-icon" sizes="57x57" href="<?php echo theme_url('icon/apple-touch-icon-57x57.png'); ?>">
<link rel="apple-touch-icon" sizes="60x60" href="<?php echo theme_url('icon/apple-touch-icon-60x60.png'); ?>">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo theme_url('icon/apple-touch-icon-72x72.png'); ?>">
<link rel="apple-touch-icon" sizes="76x76" href="<?php echo theme_url('icon/apple-touch-icon-76x76.png'); ?>">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo theme_url('icon/apple-touch-icon-114x114.png'); ?>">
<link rel="apple-touch-icon" sizes="120x120" href="<?php echo theme_url('icon/apple-touch-icon-120x120.png'); ?>">
<link rel="apple-touch-icon" sizes="144x144" href="<?php echo theme_url('icon/apple-touch-icon-144x144.png'); ?>">
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo theme_url('icon/apple-touch-icon-152x152.png'); ?>">
<link rel="icon" type="image/png" href="<?php echo theme_url('icon/favicon-32x32.png'); ?>" sizes="32x32">
<link rel="icon" type="image/png" href="<?php echo theme_url('icon/favicon-96x96.png'); ?>" sizes="96x96">
<link rel="icon" type="image/png" href="<?php echo theme_url('icon/favicon-16x16.png'); ?>" sizes="16x16">
<link rel="manifest" href="<?php echo theme_url('icon/manifest.json'); ?>">
<link rel="mask-icon" href="<?php echo theme_url('icon/safari-pinned-tab.svg'); ?>" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="msapplication-TileImage" content="<?php echo theme_url('icon/mstile-144x144.png'); ?>">
<meta name="theme-color" content="#ffffff">
</head>
<body ng-app="cmodule" ng-controller="BodyController" ng-cloak>    
    <!--Chat Container-->
    <div ng-show="settings.chat_status == 'enable' && settings.plugin_validated == 'yes'" class="chat-cmodule" id="chat-cmodule-mainContainer" ng-init="show_chatbox()">
        <div class="chat-cmodule-section">
            <div class="chat-cmodule-container">
                <div id="chat-cmodule-header" class="chat-cmodule-header cmodule-clearfix">
                    <div class="cmodule-window-avatar" ng-init="rand_color = getColor()">
                        <img ng-show="settings.default_avatar" ng-src="{{settings.default_avatar}}" height="50" width="50" alt="Chatbull Avatar" title="Chatbull Avatar">
                        <span ng-hide="settings.default_avatar" style="background-color: {{rand_color}};" class="cmodule-user-avatar">{{form_title|oneCapLetter}}</span>
                    </div>
                    <div class="cmodule-window-title">{{form_title}}</div>
                    <div class="cmodule-window-controls">
                        <a ng-click="end_chat($event)" title="End Chat" id="cmodule-chat-close" class="chat-cmodule-close cmodule-window-control" href="javascript:void(0)"></a>
                    </div>
                </div>

                <div class="cmodule-notification cmodule-error-message" ng-show="showError" ng-bind-html="errors" ng-class="{'in-cmodule': showError}"></div>
                <div class="cmodule-notification cmodule-success-message" ng-show="showMessage" ng-bind-html="success_message" ng-class="{'in-cmodule': showMessage}"></div>

                <div ng-show="visible_widget == 'initaiting-bypasschat-widget' && minimized != 'yes'" class="chat-cmodule-table-row">
                    <div class="chat-cmodule-widget chat-cmodule-table">
                        <div class="chat-cmodule-table-row">
                            <div class="chat-cmodule-view chat-cmodule-table">
                                <div class="chat-cmodule-row chat-cmodule-table-cell">                                    
                                    <div class="chat-cmodule-widget-content">
                                        <div class="initaiting-progrss-container">
                                            <i class="cmodule-icon fa fa-circle-o-notch fa-spin fa-3x fa-fw" aria-hidden="true"></i> <span class="progress-text"><?php echo $this->lang->line('initiating_chat');?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div ng-show="visible_widget == 'offline-widget'" id="cmodule-offline-widget" class="chat-cmodule-table-row">
                    <div class="chat-cmodule-widget chat-cmodule-table">
                        <form class="chat-cmodule-table" name="offlineForm" id="chatForm" action="" method="post" ng-submit="send_offline_request($event) && offlineForm.$valid">
                            <div class="chat-cmodule-table-row">
                                <div class="chat-cmodule-view chat-cmodule-table">
                                    <div class="chat-cmodule-row">
                                        <div class="chat-cmodule-widget-content">
                                            <div class="cmodule-help-message">{{settings.offline_heading_message}}</div>
                                            <div class="cmodule-form-group">
                                                <input class="cmodule-form-control" type="text" name="name" placeholder="Nombre" ng-class="{'cmodule-error': offlineForm.name.$dirty && offlineForm.name.$invalid}" ng-model="visitor.name" required>
                                            </div>
                                            <div class="cmodule-form-group">
                                                <input class="cmodule-form-control" type="email" name="email" placeholder="Email" ng-class="{'cmodule-error': offlineForm.email.$dirty && offlineForm.email.$invalid}" ng-model="visitor.email" required>
                                            </div>
                                            <div class="cmodule-form-group" ng-show="settings.show_depaertment_selection_box == 'yes'">
                                                <select class="cmodule-form-control" ng-model="visitor.requested_tag" ng-required="settings.show_depaertment_selection_box == 'yes'" name="department" ng-class="{'cmodule-error': offlineForm.department.$dirty && offlineForm.department.$invalid}">
                                                    <option value="">Seleccione un departamento</option>
                                                    <option ng-repeat="option in tags" value="{{option.id}}">{{option.tag_name}}</option>
                                                </select>
                                            </div>
                                            <div class="cmodule-form-group cmodule-last-item">
                                                <textarea name="message" class="cmodule-form-control" cols="20" rows="4" placeholder="Tu pregunta...." ng-class="{'cmodule-error': offlineForm.message.$dirty && offlineForm.message.$invalid}" ng-model="visitor.message" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>	
                            </div>	
                            <div class="chat-cmodule-footer">
                                <button id="cmodule-offline-submit" class="chatnox-btn-default" type="submit">Enviar Ahora</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div ng-show="visible_widget == 'online-widget'" id="cmodule-online-widget" class="chat-cmodule-table-row">
                    <div class="chat-cmodule-widget chat-cmodule-table">
                        <form class="chat-cmodule-table" name="onlineForm" id="chatForm" action="" method="post" ng-submit="send_request($event) && onlineForm.$valid">
                            <div class="chat-cmodule-table-row">
                                <div class="chat-cmodule-view chat-cmodule-table">
                                    <div class="chat-cmodule-row">
                                        <div class="chat-cmodule-widget-content">
                                            <div class="cmodule-help-message">{{settings.welcome_message}}</div>
                                            <div class="cmodule-form-group">
                                                <input class="cmodule-form-control" type="text" name="name" placeholder="Nombre" ng-model="visitor.name" ng-class="{'cmodule-error': onlineForm.name.$dirty && onlineForm.name.$invalid}" required>
                                            </div>
                                            <div class="cmodule-form-group">
                                                <input class="cmodule-form-control" type="email" name="email" placeholder="Email" ng-model="visitor.email" ng-class="{'cmodule-error': onlineForm.email.$dirty && onlineForm.email.$invalid}" required>
                                            </div>
                                            <div class="cmodule-form-group" ng-show="settings.show_depaertment_selection_box == 'yes'">
                                                <select class="cmodule-form-control" ng-model="visitor.requested_tag" ng-required="settings.show_depaertment_selection_box == 'yes'" name="department" ng-class="{'cmodule-error': onlineForm.department.$dirty && onlineForm.department.$invalid}">
                                                    <option value="">Seleccione un departamento</option>
                                                    <option ng-repeat="option in tags" value="{{option.id}}">{{option.tag_name}}</option>
                                                </select>
                                            </div>
                                            <div class="cmodule-form-group cmodule-last-item">
                                                <textarea name="message" class="cmodule-form-control" cols="20" rows="4" placeholder="Tu pregunta...." ng-model="visitor.message" ng-class="{'cmodule-error': onlineForm.message.$dirty && onlineForm.message.$invalid}" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>	
                            </div>		
                            <div class="chat-cmodule-footer">
                                <button id="cmodule-online-submit" class="chatnox-btn-default" type="submit">Iniciar Chat</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div ng-show="visible_widget == 'feedback-widget'" id="cmodule-feedback-widget" class="chat-cmodule-table-row">
                    <div class="chat-cmodule-table chat-cmodule-widget">
                        <form class="chat-cmodule-table" name="feedbackForm" id="chatForm" action="" method="post" ng-submit="send_feedback($event) && feedbackForm.$valid">
                            <div class="chat-cmodule-table-row">
                                <div class="chat-cmodule-view">
                                    <div class="chat-cmodule-row">
                                        <div class="chat-cmodule-widget-content">
                                            <div class="cmodule-help-message"><strong class="cmodule-strong">{{settings.feedback_heading_message}}</strong></div>
                                            <div class="cmodule-form-group">
                                                <p class="cmodule-rating">Rating: <span class="cmodule-badge-success">{{feedback.rating}}</span> <strong class="cmodule-rating-string">{{rating_status[feedback.rating]}}</strong></p>
                                                <div data-range-slider data-floor="1" data-ceiling="5" data-step="1" data-precision="2" data-highlight="left" data-ng-model="feedback.rating"></div>
                                            </div>
                                            <div class="cmodule-form-group cmodule-last-item">
                                                <textarea name="message" class="cmodule-form-control" cols="20" rows="4" placeholder="Escriba sus comentarios...." ng-model="feedback.feedback_text" ng-class="{'cmodule-error': feedbackForm.message.$dirty && feedbackForm.message.$invalid}" ></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>		
                            </div>
                            <div class="chat-cmodule-footer">
                                <button id="cmodule-online-submit" class="chatnox-btn-default" type="submit">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div ng-show="visible_widget == 'chatting-widget'" id="live-chat-cmodule-widget" class="chat-cmodule-table-row" ng-class="{'cmodule-avatar-removed':settings.theme == 'bubbles'}">
                    <div class="chat-cmodule-widget chat-cmodule-table">
                        <div ng-hide="settings.theme == 'classic'" class="chat-cmodule-table-row">
                            <div class="chat-cmodule-table chat-cmodule-view">
                                <div class="chat-cmodule-widget-content" id="message_box">
                                    <div ng-show="is_waiting" class="cmodule-help-message">{{settings.waiting_message}}</div>
                                    <div ng-repeat="row in messages| orderBy:'sort_order'" class="{{row.class}}" ng-class="{'chat-hootud-message-reply-row': row.sender_id == visitor.id, 'chat-cmodule-message-row': row.sender_id != visitor.id}" ng-mouseover="row.class = ''"  on-last-repeat>
                                        <img ng-show="row.profile_pic && row.sender_id != visitor.id && settings.theme == 'bubbles_with_avatar'" title="{{row.name}}" alt="" ng-src="{{row.profilePic}}" class="cmodule-img-circle cmodule-avatar">
                                        <span ng-hide="row.profile_pic || row.sender_id == visitor.id || settings.theme != 'bubbles_with_avatar'" style="background-color: {{rand_color}};" class="cmodule-user-avatar">{{row.name|oneCapLetter}}</span>
                                        <div class="chat-cmodule-message" ng-bind-html="row.chat_message | newlines | smilies"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div ng-show="settings.theme == 'classic'" class="chat-cmodule-table-row">
                            <div class="chat-cmodule-table chat-cmodule-view">
                                <div class="chat-cmodule-widget-content" id="classic_message_box">
                                    <div ng-show="is_waiting" class="cmodule-help-message">{{settings.waiting_message}}</div>
                                    <ul class="cmodule-chat-list">
                                        <li ng-repeat="row in messages| orderBy:'sort_order'" class="cmodule-chat-item {{row.class}}" ng-class="{'cmodule-even': row.sender_id == visitor.id, 'cmodule-odd': row.sender_id != visitor.id}" ng-mouseover="row.class = ''"  on-last-repeat>
                                            <div ng-show="row.sender_id != visitor.id" class="chat-user-name">{{row.name}}</div>
                                            <div ng-show="row.sender_id == visitor.id" class="chat-user-name">You</div>
                                            <div class="cmodule-chat-message" ng-bind-html="row.chat_message | newlines | smilies"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="cmodule-modal" ng-show="ask_to_confirm == 'yes' && confirm_close_session == 'no'" ng-class="{'cmodule-in':ask_to_confirm == 'yes'}">
                            <div class="cmodule-modal-dialog">
                                <div class="cmodule-modal-content">
                                    <div class="cmodule-modal-body">¿Estás seguro de que quieres terminar esta sesión de chat?</div>
                                    <div class="cmodule-modal-footer">
                                        <a href="#" ng-click="confirm_close_session = 'yes';end_chat($event)">Yes</a>
                                        <a href="#" ng-click="ask_to_confirm = 'no'">No</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="cmodule-modal" ng-show="ask_for_transcript == 'yes'" ng-class="{'cmodule-in':ask_for_transcript == 'yes'}">
                            <div class="cmodule-modal-dialog">
                                <div class="cmodule-modal-content">
                                    <div class="cmodule-modal-body">¿Quieres la transcripción en tu email?</div>
                                    <div class="cmodule-modal-footer">
                                        <a href="#" ng-click="settings.send_chat_transcript_to_visitor = 'yes';end_chat($event)">Yes</a>
                                        <a href="#" ng-click="settings.send_chat_transcript_to_visitor = 'no';end_chat($event)">No</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chat-cmodule-typing-status"  ng-show="(is_typing || (new_msg_indecator && is_scroll_start)) && ask_for_transcript != 'yes'">
                            <div ng-show="is_typing" class="chat-cmodule-message chat-cmodule-typing"> 
                                <div class="chat-cmodule-typing-indicator"></div>
                                <div class="chat-cmodule-typing-indicator"></div>
                                <div class="chat-cmodule-typing-indicator"></div>
                            </div>
                            <div ng-show="new_msg_indecator && is_scroll_start" class="chat-cmodule-new-message-indecator"></div>
                        </div>

                        <div id="chat-cmodule-footer" class="chat-cmodule-footer">
                            <form name="chatForm" id="chatForm" action="" method="post" ng-submit="send_message($event) && chatForm.$valid">
                                <div class="cmodule-message-box">
                                    <textarea focus-on-change="new_message" ng-focus="chatboxState = 'focus'" ng-blur="chatboxState = 'blur'" ng-model="new_message" ng-keypress="submit_message($event)" ng-disabled="chat_session.session_status == 'closed'" id="message" cols="20" rows="2" class="cmodule-form-control" placeholder="Tu mensaje..." required></textarea>
                                </div>
                                <div class="chat-cmodule-chat-toolbar">
                                    <span class="chat-cmodule-chat-toolbar-btn smilies-handle" smilies-selector="new_message" smilies-placement="top-left" smilies-title="Smilies"></span>
                                    <?php if($this->settings->enable_file_sharing == 'yes' and $this->settings->allowed_filetypes != ''):?>
                                        <span class="chat-cmodule-chat-toolbar-btn attachment-handle">
                                            <i aria-hidden="true" class="cmodule-icon cmodule-toolbar-icon fa fa-file-o" role="button" title="Send file"></i>
                                            <input title="Send file" class="chat-cmodule-input-file" on-after-validate="upload_files" onerror="file_error_handler" type="file" role="button" ng-model="chatfiles" name="chatfiles" accept="<?php echo str_replace("|", ",", $this->settings->allowed_filetypes);?>" maxsize="<?php echo $this->settings->file_upload_size;?>" base-sixty-four-input>
                                        </span>
                                        <span class="chat-cmodule-chat-toolbar-btn" ng-show="is_file_sending && chatForm.chatfiles.$valid"><?php echo $this->lang->line('sending_file');?></span>
                                        <span class="chat-cmodule-chat-toolbar-btn chat-cmodule-validation-error" ng-show="chatForm.chatfiles.$error.maxsize"><?php echo $this->lang->line('exceeded_filesize');?></span>
                                        <span class="chat-cmodule-chat-toolbar-btn chat-cmodule-validation-error" ng-show="chatForm.chatfiles.$error.accept"><?php echo $this->lang->line('invalid_filetype');?></span>
                                    <?php endif;?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div ng-show="display_loader" class="cmodule-spinner-loader">
                    <div class="cmodule-spinners">
                        <div class="cmodule-spinner-bounce"></div>
                        <div class="cmodule-spinner-bounce"></div>
                        <div class="cmodule-spinner-bounce"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <audio id="audio1">
        <source src="<?php echo theme_url("audio/ping.mp3"); ?>"></source>
    </audio>
    <style ng-bind-html="custom_styles"></style>
</body>
</html>