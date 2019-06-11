(function () {
    app.controller("TagController", ['$scope', '$http', function ($scope, $http) {
            $scope.tag = {'tag_status': 'publish'};
            $scope.records = [];

            // fetching user on ready
            $http.post(site_url + "?c=tags&m=get_tags", {offset: $scope.offset}).success(function (data) {
                if (data) {
                    $scope.offset = data.length;
                    $scope.records = data;
                }
            });
            
            // fetching more users
            $scope.load_more = function () {
                $scope.loading = true;
                
                $http.post(site_url + "?c=tags&m=get_tags", {offset: $scope.offset}).success(function (data) {
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
            
            // this function will be call to add user.
            $scope.reset_tag = function(){
                $scope.tag = {'tag_status': 'publish'};
                $scope.is_edit = false;
            }
            
            // update user
            $scope.edit = function(index, record){
                $scope.tag = angular.copy(record);
                $scope.tag.index = index;
                $scope.is_edit = true;
            }
            
            // save user
            $scope.save_tag = function(event){
                event.preventDefault();
                if($scope.is_edit && $scope.tag.id){
                    $http.post(site_url + "?c=tags&m=update_tag&id=" + $scope.tag.id, $scope.tag).success(function (response) {
                        if (response.result == 'success') {
                            $scope.records[$scope.tag.index] = $scope.tag;
                            $scope.reset_tag();
                            
                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;
                            
                            angular.element('#close-model').trigger('click');
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                    });
                }else{
                    $http.post(site_url + "?c=tags&m=add_tag", $scope.tag).success(function (response) {
                        if (response.result == 'success') {
                            $scope.records.push(response.tag);
                            $scope.reset_tag();
                            $scope.offset++;
                            
                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;
                            
                            angular.element('#close-model').trigger('click');
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                    });
                }
            }
            
            $scope.toogle_status = function (event, record) {
                event.preventDefault();
                if (record.tag_status == 'publish') {
                    $scope.unpublish(record);
                } else {
                    $scope.publish(record);
                }
            }
            
            $scope.publish = function (record) {
                $http.post(site_url + "?c=tags&m=update_status&id=" + record.id, {tag_status: 'publish'}).success(function (response) {
                    if (response.result == 'success') {
                        record.tag_status = 'publish';

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            $scope.unpublish = function (record) {
                $http.post(site_url + "?c=tags&m=update_status&id=" + record.id, {tag_status: 'unpublish'}).success(function (response) {
                    if (response.result == 'success') {
                        record.tag_status = 'unpublish';

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            $scope.remove = function (record, conf_message) {
                var confirm_delete = confirm(conf_message);
                if (confirm_delete) {
                    $http.post(site_url + "?c=tags&m=delete_tag&id=" + record.id).success(function (response) {
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
        }
    ]);
})();