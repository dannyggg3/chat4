(function () {
    app.controller('ChatlistController', function ($scope, $http, $interval, $timeout, $filter, Setting) {
        var orderBy = $filter('orderBy');
        $scope.chat_history = [];
        $scope.chat_session_id = cmodule.chat_session_id;
        $scope.agent = cmodule.user;
        $scope.last_id = 0;
        $scope.loaded_sessions = [];
        $scope.in_process = false;
        $scope.counter = 0;

        /*
         * get closed chats
         * @param {type} showLoder
         * @returns {undefined}
         */
        $scope.get_closed_chats = function (showLoder) {
            if (showLoder) {
                $scope.toggleLoder();
            }

            $scope.in_process = true;

            $http.post(Setting.site_url + "?d=agents&c=chat&m=closed", {excepts: $scope.loaded_sessions}).success(function (response) {
                if (response.result == 'success') {
                    angular.forEach(response.data.closed_chats, function (row, key) {
                        if ($.inArray(row.id, $scope.loaded_sessions) == -1) {
                            row.message_id = parseFloat(row.message_id);
                            $scope.chat_history.push(row);
                            $scope.loaded_sessions.push(row.id);
                        }
                    });

                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }

                if (showLoder) {
                    $scope.toggleLoder();
                }
                
                $scope.in_process = false;
                $scope.counter++;
            });
        }

        $scope.$watch("loaded_sessions", function (response, stored) {
            angular.element(".chat-sidebar").mCustomScrollbar({
                /* keyboard default options */
                keyboard: {
                    enable: true,
                    scrollType: "stepless",
                    scrollAmount: "auto"
                },
                callbacks: {
                    onScrollStart: function () {

                    },
                    whileScrolling: function () {
                        if (this.mcs.topPct > 98 && $scope.in_process === false) {
                            //$scope.get_closed_chats();
                        }
                    },
                    onScroll: function () {
                        if (this.mcs.topPct > 90 && $scope.in_process === false && $scope.counter < 6) {
                            $scope.get_closed_chats(false);
                        }
                    },
                    onTotalScroll: function () {
                        if ($scope.in_process === false && $scope.counter > 5) {
                            $scope.get_closed_chats(false);
                        }
                    }
                }
            });
        });

        // init requests
        $scope.get_related_data();

        $interval(function () {
            $scope.get_related_data();
        }, 3000);

        /*
         * To paly sound id condition ox
         */

        $scope.canPlaySound = function (current_session_id, list_session_id, message_count) {
            if (!current_session_id && current_session_id !== list_session_id && message_count > 0) {
                $scope.play();
            }
        }

        // get chat session
        $scope.get_chat_session = function (record, bckcolor) {
            $scope.active_color = bckcolor;
            $scope.toggleLoder();
            $scope.storeTypedMessage($scope.chat_session_id);
            $scope.chat_session_id = record.id;

            $http.post(Setting.site_url + "?d=agents&c=chat&m=get_session&chat_session_id=" + $scope.chat_session_id).success(function (response) {
                if (response.result == 'success') {
                    response.chat_session_id = $scope.chat_session_id;
                    response.bckcolor = bckcolor;
                    $scope.set_chat_session(response);
                } else if (response.result == 'failed') {
                    $scope.displayError(response);
                }

                $scope.toggleLoder();
            });
        }

    });

    app.factory('chat_session', function ($http) {
        function get_chat_session(chat_session_id) {
            return $http.get(site_url + "?d=agents&c=chat&m=get_session&=chat_session_id" + chat_session_id);
        }
        return {session_data: get_chat_session};
    });
})();