(function () {
    app.controller("CannedMessagesController", function ($scope, $http, filterService) {
        $scope.filters = filterService;
        $scope.canned = cmodule.canned;

        // filtring with agents
        $scope.$watchCollection("filters.keywords", function (newValue, oldValue) {
            if (newValue != oldValue) {
                $scope.filters_canned_messages();
            }
        });

        // fetching more users
        $scope.load_more = function () {
            $scope.loading = true;
            $scope.filters.offset = $scope.offset;

            $http.post(site_url + "?c=canned_messages&m=get_messages", $scope.filters).success(function (data) {
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
        $scope.filters_canned_messages = function () {
            $scope.filters.offset = 0;

            $http.post(site_url + "?c=canned_messages&m=get_messages", $scope.filters).success(function (data) {
                $scope.offset = data.length;
                $scope.records = data;
            });
        }

        /*
         * To handle server error 
         * @param {type} response
         * @returns {undefined}
         */
        $scope.handleServerError = function (response) {
            console.log(response);
            console.log('There is something issue in API response.');
            $scope.output.description = '<strong>' + response.status + ' ' + response.statusText + ' !</strong> ';
            $scope.output.description += response.data;

            /*$timeout(function () {
             $scope.output = {result: 'error', description: ''};
             }, 3000);*/
        }

        /*
         * Reset form data
         * @param {type} event
         * @returns {undefined}
         */
        $scope.resetForm = function (event) {
            event.preventDefault();

            $scope.record = {};
        }

        /*
         * To add new entry
         * @param Event event
         * @returns {undefined}
         */
        $scope.addMessage = function (event) {
            event.preventDefault();

            $scope.form_title = $scope.canned.canned_form_title;
            $scope.record = {};
        }

        /*
         * To edit entry
         * @param Event event
         * @param Object message
         * @returns {undefined}
         */
        $scope.editMessage = function (event, message) {
            event.preventDefault();
            var index = $scope.records.indexOf(message);
            $scope.form_title = $scope.canned.canned_edit + ' - ' + message.title;
            $scope.record = angular.copy(message);
            $scope.record.row_index = index;
        }

        /*
         * To save new entry
         * @returns {undefined}
         */
        $scope.saveMessage = function () {
            $http.post(site_url + "?c=canned_messages&m=save", $scope.record).then(function (response) {
                if (response.data.result == 'success') {                    
                    if(response.data.is_new) {
                        $scope.records.push(response.data.created)
                    } else {
                        $scope.records[$scope.record.row_index] = $scope.record;
                    }

                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.data.description;
                    $scope.record = {};
                    
                    angular.element('#formblock').modal('hide');
                    $scope.entryForm.$setPristine();
                } else {
                    $scope.notification.showErrors = true;
                    $scope.notification.errors = response.data.description;
                }
            }, function (response) {
                $scope.handleServerError(response);
            });
        }

        // delete entry from server
        $scope.deleteMessage = function (event, message) {
            event.preventDefault();

            var confirm_delete = confirm($scope.canned.canned_confirm_del);
            if (confirm_delete) {
                $http.get(site_url + "?c=canned_messages&m=delete&id=" + message.id).then(function (response) {
                    if (response.data.result == 'success') {
                        var index = $scope.records.indexOf(message);
                        $scope.records.splice(index, 1);

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.data.description;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.data.description;
                    }
                }, function (response) {
                    $scope.handleServerError(response);
                });
            }
        }

    });

    app.controller('FilterCtrl', function ($scope, filterService) {
        $scope.filters = filterService;
    });
})();