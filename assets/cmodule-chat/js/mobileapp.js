(function () {
    var app = angular.module("cmodule", ['ngSanitize', 'angularRangeSlider', 'cmodule.filters', 'ui.bootstrap', 'angular-smilies', 'naif.base64'], function ($httpProvider) {
        // Use x-www-form-urlencoded Content-Type
        $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

        /**
         * The workhorse; converts an object to x-www-form-urlencoded serialization.
         * @param {Object} obj
         * @return {String}
         */
        var param = function (obj) {
            var query = '', name, value, fullSubName, subName, subValue, innerObj, i;

            for (name in obj) {
                value = obj[name];

                if (value instanceof Array) {
                    for (i = 0; i < value.length; ++i) {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                } else if (value instanceof Object) {
                    for (subName in value) {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                } else if (value !== undefined && value !== null)
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        // Override $http service's default transformRequest
        $httpProvider.defaults.transformRequest = [function (data) {
                return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
            }];
    });

    app.controller("BodyController", function ($http, $scope, $interval, $timeout, $window, $location, $document, $log) {
        $scope.message_stored = [];
        $scope.settings = {'theme': ''};
        $scope.tags = [];
        $scope.visitor = {};
        $scope.agent = {name: ''};
        $scope.chat_session = {};
        $scope.messages = [];

        $scope.windowTitle = $document[0].title;
        $scope.chatboxTitle = '';
        $scope.chatboxState = '';
        $http.defaults.headers.common['Accesstoken'] = access_token;

        // to handle image backgrouund color
        $scope.colors = ['#f16364', '#f58559', '#f9a43e', '#e4c62e', '#67bf74', '#59a2be', '#2093cd', '#ad62a7', '#805781', '#e57373', '#f06292', '#a1887f'
                    , '#ba68c8', '#9575cd', '#7986cb', '#64b5f6', '#4fc3f7', '#4dd0e1', '#4db6ac', '#81c784', '#aed581', '#ff8a65', '#d4e157', '#ffd54f', '#ffb74d', '#90a4ae'];

        $scope.rand_color = '';

        // to handle page parent detail
        $scope.current_page = {'page_url': '', 'page_title': ''};
        if ($window.pageinfo !== undefined && $window.pageinfo) {
            angular.merge($scope.current_page, angular.fromJson($window.pageinfo));
        }

        $scope.custom_styles = '';
        $scope.is_agents_online = false;
        $scope.online_agent = null;
        $scope.locked_agent = null;
        $scope.miliseconds = new Date().getTime();
        $scope.form_title = '';
        $scope.time_difference = 0;

        $scope.message_box_id = "#message_box";
        $scope.heartbeat_status = ['requested', 'forward', 'open', 'on-hold', 'disconnected'];
        $scope.chatHeartbeatTime = 3000;
        $scope.time_interval = 30000;
        var stop_heartbeat = undefined;
        var stop_users_request = undefined;
        var past_scrolled = 0;

        $scope.visible_widget = 'online-widget';
        $scope.is_scroll_start = false;
        $scope.is_scrollable = true;
        $scope.is_typing = false;
        $scope.is_waiting = false;
        $scope.minimized = false;
        $scope.offline_request_sent = false;
        $scope.ask_for_transcript = 'no';
        $scope.ask_to_confirm = 'no';
        $scope.confirm_close_session = 'no';
        $scope.last_id = 0;
        $scope.display_loader = true;
        $scope.new_msg_indecator = false;
        $scope.blink_chatbox = false;
        $scope.new_message = '';
        $scope.redirect_to = '';
        $scope.is_file_sending = false;
        $scope.chatfiles = [];

        $scope.showError = false;
        $scope.errors = '';
        $scope.showMessage = false;
        $scope.success_message = '';

        // to handle notification
        $scope.showError = false;
        $scope.errors = '';
        $scope.showMessage = false;
        $scope.success_message = '';

        $scope.show_chat_start_button = false;

        // to handle feedback
        $scope.feedback_sent = false;
        $scope.feedback = {'rating': 4};
        $scope.rating_status = {1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very good', 5: 'Excellent'};

        //Play Standart
        $scope.play = function () {
            var audio = document.getElementById("audio1");
            audio.play();
        }

        // watch the chatbox state
        $scope.$watch("chatboxState", function (newState, oldState) {
            if (newState === 'focus') {
                $document[0].title = $scope.windowTitle;
                angular.element('#chat-cmodule-header').removeClass('blinking');
                $scope.blink_chatbox = false;
            }
        });

        /*
         * This function will return a random color
         * @returns {undefined}
         */
        $scope.getColor = function () {
            var rand_color = $scope.colors[Math.floor((Math.random() * 26) + 1)];
            return rand_color;
        }

        // managing chatbox height.
        $scope.set_height = function () {
            var headerHeight = angular.element("#chat-cmodule-header").outerHeight();
            var footerHeight = angular.element("#chat-cmodule-footer").outerHeight();
            var windowHeight = $window.innerHeight;

            if ($scope.is_typing) {
                angular.element($scope.message_box_id).height(windowHeight - (206));
            } else {
                angular.element($scope.message_box_id).height(windowHeight - (172));
            }
        }

        angular.element(window).resize(function () {
            $scope.set_height();
        });

        $scope.$on('onRepeatLast', function (scope, element, attrs) {
            //work your magic
            if ($scope.chatboxState === '') {
                angular.element("#message").focus();
            }

            $timeout(function () {
                $scope.scroll_chat();
            }, 100);
        });

        // reset data
        $scope.reset = function () {
            $scope.message_stored = [];
            $scope.visitor = {};
            $scope.agent = {};
            $scope.chat_session = {};
            $scope.messages = [];

            $scope.new_msg_indecator = false;
            $scope.blink_chatbox = false;
            $scope.new_message = '';
            $scope.visible_widget = 'online-widget';
            $scope.is_scroll_start = false;
            $scope.is_scrollable = true;
            $scope.is_typing = false;
            $scope.is_waiting = false;
            $scope.minimized = false;
            $scope.offline_request_sent = false;
            $scope.ask_for_transcript = 'no';
            $scope.ask_to_confirm = 'no';
            $scope.confirm_close_session = 'no';
            $scope.last_id = 0;
            $scope.display_loader = false;
            $scope.redirect_to = '';

            // to handle notification
            $scope.showError = false;
            $scope.errors = '';
            $scope.showMessage = false;
            $scope.success_message = '';

            // to handle feedback
            $scope.feedback_sent = false;
            $scope.feedback = {'rating': 4};
            $scope.form_title = $scope.settings.chat_start_title;

            $http.post(cmodule_site_url + "?d=visitors&c=chat&m=get_session", $scope.current_page).success(function (response) {
                $scope.settings = response.settings;
                $scope.form_title = $scope.settings.chat_start_title;
                $scope.tags = response.tags;
                $scope.chatHeartbeatTime = parseInt($scope.settings.time_interwal) * 1000;

                // updating theme.
                $scope.updateTheme();
                $scope.setChatStatus(response);

                if ($scope.settings.chat_mode == 'online' && angular.isDefined(stop_users_request) === false && angular.isDefined(stop_heartbeat) === false) {
                    stop_users_request = $interval(function () {
                        $scope.get_online_agents();
                    }, $scope.time_interval);
                }
            });

            $scope.offlineForm.$setPristine();
            $scope.onlineForm.$setPristine();
            $scope.feedbackForm.$setPristine();
        }

        // get server currant time in milliseconds
        $scope.get_server_time = function () {
            $http.get(cmodule_site_url + "?d=visitors&c=chat&m=get_server_time").success(function (response) {
                if (response.result == 'success') {
                    $scope.miliseconds = new Date().getTime();
                    $scope.time_difference = parseInt(response.milliseconds) - $scope.miliseconds;
                }
            });
        }

        //get currant time
        $scope.currant_time = function () {
            $scope.miliseconds = new Date().getTime();
            var miliseconds = ($scope.miliseconds + $scope.time_difference).toString();
            $scope.message_stored.push(miliseconds);

            return miliseconds;
        }

        /*
         * To update theme according to settings
         * @returns {undefined}
         */
        $scope.updateTheme = function () {
            $scope.custom_styles = ".chat-cmodule-header, .chatnox-btn-default, .chat-cmodule-header *, .chat-cmodule-header, .chat-cmodule-widget-head, .cmodule-window-widget-title, .chat-cmodule .cmodule-chat-icon { color: " + $scope.settings.title_color + " !important; }";
            $scope.custom_styles += ".chat-cmodule-header, .chatnox-btn-default, .chat-cmodule-header, .chat-cmodule-widget-head, .chat-cmodule .cmodule-chat-btn { background-color: " + $scope.settings.background_color + " !important; }";
        }

        /*
         * To set chat panel status onlinr 
         * @returns {undefined}
         */
        $scope.setChatStatus = function (response) {
            if ($scope.settings.chat_mode == 'online') {
                if (response.is_agents_online) {
                    if ($scope.settings.enable_specific_agent_request == 'yes' && response.agents_list && response.agents_list.length > 0) {
                        $scope.online_agent = response.agents_list[Math.floor(Math.random() * response.agents_list.length)];
                    }
                    
                    $scope.is_agents_online = true;

                    if ($scope.visible_widget == 'offline-widget') {
                        $scope.form_title = $scope.settings.online_form_title;
                        $scope.visible_widget = 'online-widget';
                    } else if ($scope.visible_widget == 'start' && $scope.form_title == $scope.settings.offline_form_title) {
                        $scope.form_title = $scope.settings.online_form_title;
                    }
                } else {
                    $scope.online_agent = null;
                    $scope.locked_agent = null;
                    $scope.is_agents_online = false;

                    if ($scope.visible_widget == 'online-widget') {
                        $scope.visible_widget = 'offline-widget';
                        $scope.form_title = $scope.settings.offline_form_title;
                    } else if ($scope.visible_widget == 'start') {
                        $scope.form_title = $scope.settings.offline_form_title;
                    }
                }
            } else {
                $scope.form_title = $scope.settings.offline_form_title;

                if (angular.isDefined(stop_users_request)) {
                    $interval.cancel(stop_users_request);
                    stop_users_request = undefined;
                }
            }
        }

        // get online agents
        $scope.get_online_agents = function () {
            // get online users
            $http.get(cmodule_site_url + "?d=visitors&c=chat&m=get_online_agents").success(function (response) {
                if (response.result == 'success') {
                    $scope.setChatStatus(response);
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }
            });
        }

        /*
         * To show chatbox form
         * @returns {Boolean}
         */
        $scope.visible_form = function () {
            $scope.showError = false;
            $scope.showMessage = false;

            if ($scope.is_agents_online && $scope.settings.chat_mode == 'online') {
                $scope.visible_widget = 'online-widget';
                $scope.form_title = $scope.settings.online_form_title;
            } else {
                $scope.visible_widget = 'offline-widget';
                $scope.form_title = $scope.settings.offline_form_title;
            }
        }

        /*
         * To generate random string
         * @param Intger length
         * @param String type
         * @returns String result
         */
        $scope.randomString = function (length, type) {
            var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            if (type === 'num') {
                chars = '0123456789';
            } else if (type === 'alpha') {
                chars = 'abcdefghijklmnopqrstuvwxyz';
            } else if (type === 'alphac') {
                chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            } else if (type === 'alphacm') {
                chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            }

            var result = '';
            for (var i = length; i > 0; --i)
                result += chars[Math.floor(Math.random() * chars.length)];
            return result;
        }
        
        /*
         * Initiate by pass chat
         * @returns {undefined}
         */
        $scope.initiate_bypass_chat = function () {
            var $visitor_id = $scope.miliseconds;
            $scope.visitor = {
                name: 'Visitor ' + $visitor_id,
                email: $visitor_id + '@' + $scope.randomString(8, 'alpha') + '.com',
                message: 'Hi, I need your help.'
            };

            if ($window.siteuser !== undefined && $window.siteuser) {
                var site_visitor = angular.fromJson($window.siteuser);
                if (site_visitor.name) {
                    $scope.visitor.name = site_visitor.name;
                }

                if (site_visitor.email) {
                    $scope.visitor.email = site_visitor.email;
                }

                if (site_visitor.message) {
                    $scope.visitor.message = site_visitor.message;
                }
            }
            
            $scope.visitor.sort_order = $scope.currant_time();
            $scope.visitor.page_url = $scope.current_page.page_url;
            $scope.visitor.page_title = $scope.current_page.page_title;
            
            if($scope.locked_agent) {
                $scope.visitor.agent_id = $scope.locked_agent.id;
            }

            $http.post(cmodule_site_url + "?d=visitors&c=chat&m=request", $scope.visitor).success(function (response) {
                if (response.result == 'success') {
                    $scope.visitor = response.data.visitor;
                    $scope.agent = response.data.agent;
                    $scope.chat_session = response.data.chat_session;
                    $scope.messages = response.data.chatHistory;
                    $scope.last_id = response.data.last_id;
                    $scope.message_stored = response.data.message_stored;

                    $scope.visible_widget = 'chatting-widget';
                    $scope.is_waiting = true;

                    $scope.start_chat();
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }
            });
        }

        // check if session exists.
        $scope.get_session = function () {
            $http.post(cmodule_site_url + "?d=visitors&c=chat&m=get_session", $scope.current_page).success(function (response) {
                $scope.redirect_to = response.redirect_to;
                $scope.settings = response.settings;
                $scope.form_title = $scope.settings.chat_start_title;
                $scope.chatHeartbeatTime = parseInt($scope.settings.time_interwal) * 1000;

                // updating theme.
                $scope.updateTheme();
                $scope.setChatStatus(response);

                if ($scope.settings.theme == 'classic') {
                    $scope.message_box_id = '#classic_message_box';
                }

                if (response.result == 'success') {
                    $scope.visitor = response.data.visitor;
                    $scope.agent = response.data.agent;
                    $scope.chat_session = response.data.chat_session;
                    $scope.messages = response.data.chatHistory;
                    $scope.last_id = response.data.last_id;
                    $scope.message_stored = response.data.message_stored;

                    $timeout(function () {
                        $scope.scroll_chat();
                    }, 100);

                    if (response.data.show_feedback_form == 'yes' && $scope.chat_session.session_status == 'closed') {
                        $scope.visible_widget = 'feedback-widget';
                        $scope.form_title = 'Danos tu opinión';
                    } else if ($scope.chat_session) {
                        $scope.visible_widget = 'chatting-widget';
                        $scope.form_title = 'Welcome';

                        if (response.data.agent.id) {
                            $scope.form_title = $scope.agent.name;

                            if ($scope.agent.profile_pic) {
                                $scope.settings.default_avatar = $scope.agent.profilePic;
                            } else {
                                $scope.settings.default_avatar = '';
                            }

                            $scope.is_typing = ($scope.agent.is_typing > 0) ? true : false;

                            if ($scope.chat_session.session_status == 'closed') {
                                $scope.showError = true;
                                $scope.errors = 'Your chat session is closed.';
                                $scope.new_message = $scope.errors;

                                $timeout(function () {
                                    $scope.showError = false;
                                    $scope.errors = '';
                                }, 2500);
                            }
                        } else {
                            $scope.is_waiting = true;
                        }
                    }

                    //start chat
                    $scope.start_chat();
                } else if (response.result == 'no-session') {
                    $scope.tags = response.tags;

                    if ($scope.settings.chat_mode == 'online') {
                        if($scope.online_agent && $scope.settings.enable_specific_agent_request == 'yes') {
                            $scope.locked_agent = angular.copy($scope.online_agent);
                        }
                        
                        if ($scope.is_agents_online && $scope.settings.initiate_bypass_chat === 'yes') {
                            $scope.visible_widget = 'initaiting-bypasschat-widget';
                            $scope.form_title = $scope.settings.online_form_title;
                    
                            $scope.initiate_bypass_chat();
                        } else {
                            stop_users_request = $interval(function () {
                                $scope.get_online_agents();
                            }, $scope.time_interval);
                        }
                        
                        // update locked agent info into form
                        if($scope.locked_agent) {
                            $scope.form_title = $scope.locked_agent.name;
                            if($scope.locked_agent.profile_picture) {
                                $scope.settings.default_avatar = $scope.locked_agent.profile_picture;
                            }
                        }
                    }
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }

                $scope.set_height();

                angular.element($scope.message_box_id).mCustomScrollbar({
                    /* keyboard default options */
                    keyboard: {
                        enable: true,
                        scrollType: "stepless",
                        scrollAmount: "auto"
                    },
                    callbacks: {
                        onScrollStart: function () {
                            $scope.is_scroll_start = true;
                            past_scrolled = this.mcs.topPct;
                        },
                        onScroll: function () {
                            if (past_scrolled > this.mcs.topPct) {
                                $scope.is_scrollable = false;
                            }
                            /*
                             if(this.mcs.topPct > 95 && !$scope.is_scrollable && $scope.is_total_scrolled){
                             $scope.is_scrollable = true;
                             }
                             
                             if(this.mcs.topPct > 95 && $scope.is_scrollable && !$scope.is_total_scrolled){
                             $scope.is_total_scrolled = true;
                             }*/

                        },
                        onTotalScroll: function () {
                            $scope.is_total_scrolled = true;
                            $scope.is_scrollable = true;
                            $scope.new_msg_indecator = false;
                        }
                    }
                });

                $scope.display_loader = false;
            });
        }

        /*
         * This function will show chatbox
         */
        $scope.show_chatbox = function () {
            //angular.element("#chat-cmodule-mainContainer").show();
            $scope.get_server_time();
            $scope.get_session();
        }

        //send request
        $scope.send_request = function (event) {
            event.preventDefault();
            $scope.display_loader = true;

            $scope.visitor.sort_order = $scope.currant_time();
            $scope.visitor.page_url = $scope.current_page.page_url;
            $scope.visitor.page_title = $scope.current_page.page_title;
            
            if($scope.locked_agent) {
                $scope.visitor.agent_id = $scope.locked_agent.id;
            }

            $http.post(cmodule_site_url + "?d=visitors&c=chat&m=request", $scope.visitor).success(function (response) {
                if (response.result == 'success') {
                    $scope.display_loader = false;
                    $scope.visitor = response.data.visitor;
                    $scope.agent = response.data.agent;
                    $scope.chat_session = response.data.chat_session;
                    $scope.messages = response.data.chatHistory;
                    $scope.last_id = response.data.last_id;
                    $scope.message_stored = response.data.message_stored;

                    $scope.visible_widget = 'chatting-widget';
                    $scope.is_waiting = true;

                    $scope.start_chat();
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }
            });
        }

        // send offline request
        $scope.send_offline_request = function (event) {
            event.preventDefault();
            $scope.display_loader = true;

            $scope.visitor.page_url = $scope.current_page.page_url;
            $scope.visitor.page_title = $scope.current_page.page_title;

            $http.post(cmodule_site_url + "?d=visitors&c=orequests&m=request", $scope.visitor).success(function (response) {
                if (response.result == 'success') {
                    $scope.showMessage = true;
                    $scope.success_message = $scope.settings.offline_submission_message;

                    $timeout(function () {
                        $scope.reset();
                    }, 2500);
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }
            });
        }

        // start chatting.
        $scope.start_chat = function () {
            if (angular.isDefined(stop_users_request)) {
                $interval.cancel(stop_users_request);
                stop_users_request = undefined;
            }

            if (angular.isDefined(stop_heartbeat)) {
                $interval.cancel(stop_heartbeat);
                stop_heartbeat = undefined;
            }

            if ($scope.chat_session && angular.element.inArray($scope.chat_session.session_status, $scope.heartbeat_status) != -1) {
                stop_heartbeat = $interval(function () {
                    $scope.chatHeartbeat();
                }, $scope.chatHeartbeatTime);
            } else if ($scope.settings.chat_mode == 'online') {
                stop_heartbeat = $interval(function () {
                    $scope.get_online_agents();
                }, $scope.time_interval);
            }
        }

        //chatHeartbeat
        $scope.chatHeartbeat = function () {
            var typing = ($scope.new_message) ? 1 : 0;
            $http.post(cmodule_site_url + "?d=visitors&c=chat&m=chatHeartbeat&last_id=" + $scope.last_id + '&typing=' + typing, $scope.visitor).success(function (response) {
                if (response.result == 'success') {
                    $scope.setChatStatus(response);
                    $scope.chat_session = response.chat_session;
                    $scope.agent = response.agent;
                    $scope.last_id = response.last_id;
                    $scope.form_title = 'Welcome';

                    if (response.agent.id) {
                        $scope.chatboxTitle = response.agent.name + ' says...';
                        $scope.form_title = $scope.agent.name;

                        if ($scope.agent.profile_pic) {
                            $scope.settings.default_avatar = $scope.agent.profilePic;
                        } else {
                            $scope.settings.default_avatar = '';
                        }

                        $scope.is_typing = ($scope.agent.is_typing > 0) ? true : false;
                        $scope.is_waiting = false;
                        
                        if ($scope.is_typing) {
                            $scope.set_height();
                        }

                        if ($scope.chat_session.session_status == 'closed') {
                            $scope.showError = true;
                            $scope.errors = 'Your chat session is closed.';
                            $scope.new_message = 'Your chat session is closed.';

                            $timeout(function () {
                                $scope.showError = false;
                                $scope.errors = '';

                                if (angular.isDefined(stop_heartbeat)) {
                                    $interval.cancel(stop_heartbeat);
                                    stop_heartbeat = undefined;

                                    if ($scope.settings.chat_mode == 'online') {
                                        stop_users_request = $interval(function () {
                                            $scope.get_online_agents();
                                        }, $scope.time_interval);
                                    }
                                }
                            }, 2500);
                        }
                    }

                    angular.forEach(response.chatMessagesData, function (row, key) {
                        if (angular.element.inArray(row.sort_order, $scope.message_stored) == -1 && angular.element.inArray(row.id, $scope.message_stored) == -1) {
                            $scope.messages.push(row);
                            $scope.message_stored.push(row.id);

                            if ($scope.is_scrollable) {
                                $scope.new_msg_indecator = false;
                            } else {
                                $scope.new_msg_indecator = true;
                            }

                            if (row.sender_id !== $scope.visitor.id) {
                                $scope.playSound = true;
                                $scope.blink_chatbox = true;
                            }
                        }
                        //$scope.blink_chatbox = false;
                    });

                    if ($scope.playSound) {
                        $scope.play();
                        $scope.playSound = false;
                    }

                    /*if ($scope.blink_chatbox) {
                     $document[0].title = $scope.chatboxTitle;
                     angular.element('#chat-cmodule-header').toggleClass('blinking');
                     }*/

                    if (response.chatMessagesData.length > 0) {
                        $scope.scroll_chat();
                    }
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }
            });
        }

        // submit message on enter key press
        $scope.submit_message = function (event) {
            if (event.keyCode == 13 && $scope.new_message) {
                if (!event.shiftKey) {
                    $scope.send_message(event);
                }
            } else if (event.keyCode == 13) {
                event.preventDefault();
            }
        }

        // sending new message 
        $scope.send_message = function (event) {
            event.preventDefault();
            if ($scope.new_message) {
                if (!$scope.new_msg_indecator && $scope.is_scroll_start) {
                    $scope.is_scrollable = true;
                }

                //prepare json data
                var message_data = {
                    name: $scope.visitor.name,
                    chat_session_id: $scope.chat_session.id,
                    chat_message: $scope.new_message,
                    message_status: 'unread',
                    sender_id: $scope.visitor.id,
                    sort_order: $scope.currant_time(),
                    class: ''
                };

                $scope.messages.push(message_data);
                $scope.new_message = '';
                $scope.scroll_chat();
                // sending request to send message
                $http.post(cmodule_site_url + "?d=visitors&c=chat&m=send", message_data).success(function (response) {
                    if (response.result == 'success') {
                        //$scope.messages.push(response.message_row);
                        //$scope.new_message = '';

                        //$scope.scroll_chat();
                    } else if (response.result == 'failed') {
                        if(response.error) {
                            $scope.displayError(response);
                        }
                        
                        $scope.new_message = message_data.chat_message;
                    }
                });
            }
        }

        /*
         * Upload file in chat
         * 
         * @param Event - event
         * @param Array - fileObjs
         * @param Array - filelist
         */
        $scope.upload_files = function (event, fileObjs, filelist) {
            angular.forEach(fileObjs, function (file, order) {
                if (file.filetype && file.filename && file.filesize && file.base64) {
                    file.chat_session_id = $scope.chat_session.id;
                    file.sender_id = $scope.visitor.id;
                    file.message_status = 'unread';
                    file.sort_order = $scope.currant_time();

                    $scope.is_file_sending = true;
                    $http.post(cmodule_site_url + "?d=visitors&c=chat&m=upload_file", file).success(function (response) {
                        if (response.result == 'success') {
                            if(response.message_data) {
                                $scope.messages.push(response.message_data);
                            }
                        } else if (response.result == 'failed') {
                            if(response.error) {
                                $scope.displayError(response);
                            }
                        }
                        
                        $scope.is_file_sending = false;
                    });
                }
            });
        }

        /*
         * Handle file upload error.
         */
        $scope.file_error_handler = function (event, reader, file, fileList, fileObjs, object) {
            $log.log("An error occurred while reading file: " + file.name);
            //alert("An error occurred while reading file: " + file.name);

            var errorData = {error: "An error occurred while reading file: " + file.name};
            $scope.displayError(errorData);
            reader.abort();
        };

        // close chat 
        $scope.end_chat = function (event) {
            event.preventDefault();

            if ($scope.visible_widget == 'chatting-widget') {
                if ($scope.confirm_close_session == 'yes') {
                    if ($scope.settings.send_chat_transcript_to_visitor != 'ask_to_visiter') {
                        $scope.display_loader = true;

                        $http.post(cmodule_site_url + "?d=visitors&c=chat&m=end", {send_chat_transcript: $scope.settings.send_chat_transcript_to_visitor}).success(function (response) {
                            if (response.result == 'success') {
                                $scope.ask_for_transcript = 'no';
                                $scope.chat_session = response.chat_session;

                                if (response.show_feedback_form == 'yes') {
                                    $scope.visible_widget = 'feedback-widget';
                                    $scope.form_title = 'Danos tu opinión';
                                } else {
                                    $scope.tags = response.tags;

                                    if (response.redirect_to) {
                                        window.location = response.redirect_to;
                                    } else {
                                        $scope.reset();
                                    }
                                }

                                if (angular.isDefined(stop_heartbeat)) {
                                    $interval.cancel(stop_heartbeat);
                                    stop_heartbeat = undefined;

                                    if ($scope.settings.chat_mode == 'online') {
                                        stop_users_request = $interval(function () {
                                            $scope.get_online_agents();
                                        }, $scope.time_interval);
                                    }
                                }
                            } else if (response.result == 'failed') {
                                $scope.displayError(response);
                            }

                            $scope.display_loader = false;
                        });
                    } else {
                        if ($scope.minimized) {
                            $scope.maximize_chat(event);
                        }
                        $scope.ask_for_transcript = 'yes';
                    }
                } else {
                    if ($scope.minimized) {
                        $scope.maximize_chat(event);
                    }
                    $scope.ask_to_confirm = 'yes';
                }
            } else {
                /*if ($scope.chat_session) {
                 window.history.back();
                 } else {
                 $scope.reset();
                 }*/

                if ($scope.chat_session && $scope.redirect_to) {
                    window.location = $scope.redirect_to;
                } else {
                    $scope.reset();
                }
            }
        }

        // send feedback
        $scope.send_feedback = function (event) {
            event.preventDefault();
            $scope.display_loader = true;

            $scope.feedback.chat_session_id = $scope.chat_session.id;
            $scope.feedback.feedback_by = $scope.visitor.id;
            $scope.feedback.feedback_to = $scope.agent.id;
            $scope.feedback.sort_order = $scope.currant_time();

            $http.post(cmodule_site_url + "?d=visitors&c=chat&m=send_feedback", $scope.feedback).success(function (response) {
                if (response.result == 'success') {
                    $scope.showMessage = true;
                    $scope.success_message = $scope.settings.feedback_submission_message;

                    $timeout(function () {
                        if (response.redirect_to) {
                            window.location = response.redirect_to;
                        } else {
                            $scope.reset();
                        }
                    }, 2500);
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }
            });
        }

        // scroll chat box
        $scope.scroll_chat = function () {
            if ($scope.is_scrollable) {
                //scrolling window to footer
                angular.element($scope.message_box_id).mCustomScrollbar('scrollTo', 'bottom', {
                    scrollInertia: 100,
                    timeout: 10
                });
            }
        }

        // will display error is accur
        $scope.displayError = function (data) {
            $scope.showError = true;
            $scope.errors = data.error;

            $timeout(function () {
                $scope.display_loader = false;
                $scope.showError = false;
                $scope.errors = '';
            }, 2500);
        }
    });

    angular.module('cmodule.filters', []).
            filter('oneCapLetter', function () {
                return function (input) {
                    return input.substring(0, 1).toLowerCase().replace(/\b[a-z]/g, function (letter) {
                        return letter.toUpperCase();
                    });
                };
            })
            .filter('newlines', function () {
                var ishtml = function (str) {
                    var a = document.createElement('div');
                    a.innerHTML = str;
                    for (var c = a.childNodes, i = c.length; i--; ) {
                        if (c[i].nodeType == 1)
                            return true;
                    }
                    return false;
                }

                return function (text) {
                    if (text) {
                        if (ishtml(text)) {
                            return text.replace(/\n/g, ' <br/> ')
                                    .replace(/([A-Za-z0-9._%+-]+@+[A-Za-z0-9._%+-]+\.[^\s]+)/g, '<a target="_blank" href="mailto:$1">$1</a>');
                        }
                        return text.replace(/\n/g, ' <br/> ')
                                .replace(/((ftp|http)[^\s]+)/g, '<a target="_blank" href="$1">$1</a>')
                                //.replace(/(www\.[^\s]+)/g, '<a target="_blank" href="http://$1">$1</a>')
                                .replace(/([A-Za-z0-9._%+-]+@+[A-Za-z0-9._%+-]+\.[^\s]+)/g, '<a target="_blank" href="mailto:$1">$1</a>');
                    }

                    return text;
                }
            });

    // adding last repeat directive
    app.directive('onLastRepeat', function () {
        return function (scope, element, attrs) {
            if (scope.$last) {
                setTimeout(function () {
                    scope.$emit('onRepeatLast', element, attrs);
                }, 1);
            }
        };
    });

    /*
     This directive allows us to pass a function in on an enter key to do what we want.
     */
    app.directive('ngEnter', function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.ngEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    });
})();