(function () {

    app.controller("ChathistoryController", ['$scope', '$http', 'filterService', function ($scope, $http, filterService) {
            $scope.filters = filterService;
            $scope.conversations = [];
            $scope.visitor = {};
            $scope.chat_session = {};

            // filtring with agents
            $scope.$watchCollection("filters.agents", function (newValue, oldValue) {
                if (newValue != oldValue) {
                    $scope.filters_users();
                }
            });

            // fetching more users
            $scope.load_more = function () {
                $scope.loading = true;
                $scope.filters.offset = $scope.offset;

                $http.post(site_url + "?c=chat_history&m=get_chat_session", $scope.filters).success(function (data) {
                    if (data.length == 0) {
                        $scope.showNoMoreRecordAlert();
                    }
                    
                    $.each(data, function (key, row) {
                        $scope.offset++;
                        $scope.records.push(row);
                    });
                    $scope.loading = false;
                });
            }

            $scope.load_more();

            // fetching more users
            $scope.filters_users = function () {
                $scope.filters.offset = 0;

                $http.post(site_url + "?c=chat_history&m=get_chat_session", $scope.filters).success(function (data) {
                    $scope.offset = data.length;
                    $scope.records = data;
                });
            }

            // get conversations of chat between visitor and agents
            $scope.get_conversations = function (session) {
                $scope.chat_session = session;
                $http.post(site_url + "?c=chat_history&m=get_conversations&id=" + session.id).success(function (data) {
                    $scope.conversations = data.messages;
                    $scope.visitor = data.visitor;
                });
            }

            /*
             * Delete user from server
             * 
             * @param Object record
             * @param String conf_message
             * @returns {undefined}
             */
            $scope.delete_record = function (record, conf_message) {
                var confirm_delete = confirm(conf_message);
                if (confirm_delete) {
                    $http.post(site_url + "?c=chat_history&m=delete&id=" + record.id).success(function (response) {
                        if (response.result == 'success') {
                            var index = $scope.records.indexOf(record);
                            $scope.records.splice(index, 1);

                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                    });
                }
            }
        }]);

    app.controller('FilterCtrl', function ($scope, filterService) {
        $scope.filters = filterService;
        $scope.agents = cmodule.agents;

        $scope.checkAll = function () {
            $scope.filters.agents = $scope.agents.map(function (item) {
                return item.id;
            });
        };

        $scope.uncheckAll = function () {
            $scope.filters.agents = [];
        };

        $scope.checkFirst = function () {
            $scope.filters.agents.splice(0, $scope.filters.agents.length);
            $scope.filters.agents.push(1);
        };
    });
})();