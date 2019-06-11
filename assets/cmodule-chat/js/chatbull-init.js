var ptitle = document.title;
var purl = window.location.href;
var iframeurl = cburl + 'index.php?d=visitors&c=chatbox&m=chatpanel&token=' + access_token;
var is_mobile = false;

var cbwindow = {
    is_mobile: window.is_mobile,
    innerHeight: window.innerHeight,
    outerHeight: window.outerHeight,
    innerWidth: window.innerWidth,
    outerWidth: window.outerWidth
};

/*
 * To chnage object to quesry string.
 */
function buildQueryParam(obj) {
    var query = '', name, value;

    for (name in obj) {
        value = obj[name];
        query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
    }

    return query.length ? query.substr(0, query.length - 1) : query;
}

/*
 * To check is mobile window or not.
 */
function detectmob() {
    if (navigator.userAgent.match(/Android/i)
            || navigator.userAgent.match(/webOS/i)
            || navigator.userAgent.match(/iPhone/i)
            || navigator.userAgent.match(/iPad/i)
            || navigator.userAgent.match(/iPod/i)
            || navigator.userAgent.match(/BlackBerry/i)
            || navigator.userAgent.match(/Windows Phone/i)
            ) {
        return true;
    } else {
        return false;
    }
}
is_mobile = detectmob();

document.write('<link rel="stylesheet" href="' + cburl + 'assets/cmodule-chat/css/chatbox-widget.css">');
if (is_mobile) {
    iframeurl = cburl + 'index.php?d=visitors&c=chatbox&m=botton&token=' + access_token;
}
//is_mobile = true;

if (typeof cbuser !== 'undefined') {
    iframeurl += '&' + buildQueryParam(cbuser);
}

cbwindow.is_mobile = is_mobile;
iframeurl += '&' + buildQueryParam(cbwindow);

document.write('<iframe style="position: fixed; height: 0; width: 0;" scrolling="no" name="' + ptitle + '[!]' + purl + '" id="chatbull-frame" class="chat-cmodule-iframe" src="' + iframeurl + '"></iframe>');
document.write('<script type="text/javascript" src="' + cburl + 'index.php?d=visitors&c=chatbox&m=settings"></' + 'script>');
document.write('<script type="text/javascript" src="' + cburl + 'assets/cmodule-chat/js/iframeResizer.min.js"></' + 'script>');
document.write('<script type="text/javascript" src="' + cburl + 'assets/cmodule-chat/js/iframe.js"></' + 'script>');