<div class="chat-cmodule">
    <div class="chat-cmodule-container">
        <div id="chat-cmodule-header" class="chat-cmodule-header cmodule-clearfix">
            <div class="cmodule-window-avatar" ng-init="rand_color = getColor()">
                <img ng-show="settings.default_avatar" ng-src="{{settings.default_avatar}}" height="50" width="50" alt="Chatbull Avatar" title="Chatbull Avatar">
                <span ng-hide="settings.default_avatar" style="background-color: {{rand_color}};" class="cmodule-user-avatar" title="Chatbull Avatar">A</span>
            </div>
            <div class="cmodule-window-title">{{settings.online_form_title}}</div>
            <div class="cmodule-window-controls">
                <a title="Minimize window" id="cmodule-chat-minimize" class="chat-cmodule-minimize cmodule-window-control" href="javascript:void(0)"></a> 
                <a title="End Chat" id="cmodule-chat-close" class="chat-cmodule-close cmodule-window-control" href="javascript:void(0)"></a>
            </div>
        </div>

        <!-- theme bubbles with avatar -->
        <div id="live-chat-cmodule-widget" class="chat-cmodule-widget" ng-show="settings.theme == 'bubbles_with_avatar'">
            <div class="chat-cmodule-view">
                <div class="chat-cmodule-row">
                    <div class="chat-cmodule-widget-content">
                        <div class="chat-cmodule-message-row" ng-init="rand_color = getColor()">
                            <img ng-show="settings.default_avatar" title="setlla-johnson" alt="" ng-src="{{settings.default_avatar}}" class="cmodule-img-circle cmodule-avatar">
                            <span ng-hide="settings.default_avatar" style="background-color: {{rand_color}};" class="cmodule-user-avatar" title="Chatbull Avatar">S</span>
                            <div class="chat-cmodule-message">
                                Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty.
                            </div>
                        </div>
                        <div class="chat-hootud-message-reply-row">
                            <div class="chat-cmodule-message">
                                Sure thing. Let me look into this. Please wait for 2 minutes
                            </div>
                        </div>
                        <div class="chat-hootud-message-reply-row">
                            <div class="chat-cmodule-message">
                                Sure thing. Let me look into this. Please wait for 2 minutes
                            </div>
                        </div>
                        <div class="chat-cmodule-message-row" ng-init="rand_color = getColor()">
                            <img ng-show="settings.default_avatar" title="setlla-johnson" alt="" ng-src="{{settings.default_avatar}}" class="cmodule-img-circle cmodule-avatar">
                            <span ng-hide="settings.default_avatar" style="background-color: {{rand_color}};" class="cmodule-user-avatar" title="Chatbull Avatar">S</span>
                            <div class="chat-cmodule-message">
                                Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty.
                            </div>
                        </div>
                        <div class="chat-hootud-message-reply-row">
                            <div class="chat-cmodule-message">
                                Sure thing. Let me look into this. Please wait for 2 minutes
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chat-cmodule-footer">
                <div class="cmodule-message-box">
                    <textarea cols="20" rows="2" class="cmodule-form-control" placeholder="Your Message..."></textarea>
                </div>
            </div>
        </div>

        <!-- theme bubbles -->
        <div id="live-chat-cmodule-widget" class="chat-cmodule-widget cmodule-avatar-removed" ng-show="settings.theme == 'bubbles'">
            <div class="chat-cmodule-view">
                <div class="chat-cmodule-row">
                    <div class="chat-cmodule-widget-content">
                        <div class="chat-cmodule-message-row">
                            <div class="chat-cmodule-message">
                                Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty.
                            </div>
                        </div>
                        <div class="chat-hootud-message-reply-row">
                            <div class="chat-cmodule-message">
                                Sure thing. Let me look into this. Please wait for 2 minutes
                            </div>
                        </div>
                        <div class="chat-hootud-message-reply-row">
                            <div class="chat-cmodule-message">
                                Sure thing. Let me look into this. Please wait for 2 minutes
                            </div>
                        </div>
                        <div class="chat-cmodule-message-row">
                            <div class="chat-cmodule-message">
                                Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty.
                            </div>
                        </div>
                        <div class="chat-hootud-message-reply-row">
                            <div class="chat-cmodule-message">
                                Sure thing. Let me look into this. Please wait for 2 minutes
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chat-cmodule-footer">
                <div class="cmodule-message-box">
                    <textarea cols="20" rows="2" class="cmodule-form-control" placeholder="Your Message..."></textarea>
                </div>
            </div>
        </div>

        <!-- theme classic -->
        <div id="live-chat-cmodule-widget" class="chat-cmodule-widget" ng-show="settings.theme == 'classic'">
            <div class="chat-cmodule-view">
                <div class="chat-cmodule-row">
                    <div class="chat-cmodule-widget-content">
                        <ul class="cmodule-chat-list">
                            <li class="cmodule-chat-item cmodule-even">
                                <div class="chat-user-name">You</div>
                                <div class="cmodule-chat-message">
                                    Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty. 
                                </div>
                            </li>
                            <li class="cmodule-chat-item cmodule-odd">
                                <div class="chat-user-name">Chatbull</div>
                                <div class="cmodule-chat-message">
                                    Sure thing. Let me look into this. Please wait for 2 minutes.
                                </div>
                            </li>
                            <li class="cmodule-chat-item cmodule-even">
                                <div class="chat-user-name">You</div>
                                <div class="cmodule-chat-message">
                                    Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty. 
                                </div>
                            </li>
                            <li class="cmodule-chat-item cmodule-odd">
                                <div class="chat-user-name">Chatbull</div>
                                <div class="cmodule-chat-message">
                                    Sure thing. Let me look into this. Please wait for 2 minutes.
                                </div>
                            </li><li class="cmodule-chat-item cmodule-even">
                                <div class="chat-user-name">You</div>
                                <div class="cmodule-chat-message">
                                    Hi, I’ve been facing an issue on my website. Its related to online purchasing. Everytime I try to buy something, it redirects me to the home page and makes my cart empty. 
                                </div>
                            </li>
                            <li class="cmodule-chat-item cmodule-odd">
                                <div class="chat-user-name">Chatbull</div>
                                <div class="cmodule-chat-message">
                                    Sure thing. Let me look into this. Please wait for 2 minutes.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="chat-cmodule-footer">
                <div class="cmodule-message-box">
                    <textarea cols="20" rows="2" class="cmodule-form-control" placeholder="Your Message..."></textarea>
                </div>
            </div>
        </div>
    </div>
</div>