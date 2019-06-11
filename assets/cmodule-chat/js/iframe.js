var initiating_bypass_chat = false;
var iframeOptions = {
    //log: true, // Enable console logging
    inPageLinks: true,
    autoResize: true,
    sizeWidth: true,
    resizedCallback: function (messageData) {
        //console.log(messageData.height);  
        // Callback fn when resize is received
        if (messageData.height < 130 && messageData.height > 50) {            
            if (settings && settings.visitor_widget_type == 'chaticon') {
                var offsetWidth = 0;
                if(settings.enable_online_animation == 'yes') offsetWidth = 30;
                
                messageData.width = 82 + offsetWidth;
                if (settings.visitor_widget_type == 'chaticon') {
                    if (settings.chat_icon_size == 'medium-size') {
                        messageData.width = 62 + offsetWidth;
                    } else if (settings.chat_icon_size == 'small-size') {
                        messageData.width = 41 + offsetWidth;
                    }
                }
            }
        }
        
        if(messageData.height < 190 && messageData.height > 160) {
            initiating_bypass_chat = true;
        } else if(messageData.height > 200) {
            initiating_bypass_chat = false;
        }

        if (settings) {
            var frame_classes = 'chat-cmodule-iframe';
            if (!is_mobile) {
                frame_classes = 'chat-cmodule-iframe iframe-pull-' + settings.window_position;
            } else {
                //frame_classes += ' chat-cmodule-mobile';
            }
            
            if (settings.visitor_widget_type == 'chaticon') {
                frame_classes += ' chat-cmodule-' + settings.chat_icon_size;
            } else if (settings.visitor_widget_type == 'chatbar'){
                messageData.width = 300;
                frame_classes += ' chat-cmodule-widget-bar';
            }

            document.getElementById("chatbull-frame").className = frame_classes;
        }
        
        if(initiating_bypass_chat) {
            messageData.width = 300;
        }
        
        document.getElementById("chatbull-frame").style.width = messageData.width + 'px';
    },
    initCallback: function (iframeData) {
        //console.log(iframeData);
    },
    messageCallback: function (messageData) {
        //console.log(messageData);        
        //alert(messageData.message);
	//document.getElementsByTagName('iframe')[0].iFrameResizer.sendMessage('Hello back from parent page');
    },
    closedCallback: function (id) {
        var chatboxurl = cburl + 'index.php?d=visitors&c=chatbox&m=index&token=' + access_token;
        if (typeof cbuser !== 'undefined') {
            chatboxurl += '&' + buildQueryParam(cbuser);
        }

        chatboxurl += '&' + buildQueryParam(cbwindow);
        chatboxurl += '&page_title=' + encodeURIComponent(ptitle);
        chatboxurl += '&page_url=' + encodeURIComponent(purl);

        window.location = chatboxurl;
    }
}

if (is_mobile) {
   //iframeOptions.scrolling = true;
   iFrameResize(iframeOptions);
} else {
    iFrameResize(iframeOptions);
}

