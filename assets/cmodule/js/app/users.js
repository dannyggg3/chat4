(function () {

    app.controller("UserController", ['$scope', '$http', 'filterService', '$window', '$timeout', function ($scope, $http, filterService, $window, $timeout) {
            $scope.filters = filterService;
            $scope.tags = cmodule.tags;
            $scope.show_user_form = false;
            $scope.show_pass_form = false;
            $scope.show_users_list = true;
            $scope.user = {role: 'agent', user_status: 'active', tags: []};
            $scope.files = [];
            $scope.filename = '';

            // updating on files changed
            $scope.$watchCollection("files", function (newValue, oldValue) {
                if (newValue != oldValue) {
                    angular.forEach(newValue, function (file) {
                        $scope.filename = file.name.replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, "");
                    });
                }
            });

            // calling filter function on watch
            // filtring with keywords
            $scope.$watch("filters.keywords", function (newValue, oldValue) {
                if (newValue != oldValue) {
                    $scope.filters_users();
                }
            });

            // filtring with roles
            $scope.$watchCollection("filters.roles", function (newValue, oldValue) {
                if (newValue != oldValue) {
                    $scope.filters_users();
                }
            });

            // filtring with tags
            $scope.$watchCollection("filters.tags", function (newValue, oldValue) {
                if (newValue != oldValue) {
                    $scope.filters_users();
                }
            });

            // fetching user on ready
            $http.post(site_url + "?c=users&m=users_list", $scope.filters).success(function (data) {
                $scope.offset = data.length;
                $scope.records = data;
            });

            // fetching more users
            $scope.load_more = function () {
                $scope.loading = true;
                $scope.filters.offset = $scope.offset;

                $http.post(site_url + "?c=users&m=users_list", $scope.filters).success(function (data) {
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

            // fetching more users
            $scope.filters_users = function () {
                $scope.filters.offset = 0;

                $http.post(site_url + "?c=users&m=users_list", $scope.filters).success(function (data) {
                    $scope.offset = data.length;
                    $scope.records = data;
                });
            }

            // this function will be call to toggle display user form.
            $scope.toggle_user_form = function (clearForm) {
                $scope.show_user_form = !$scope.show_user_form;
                $scope.show_users_list = !$scope.show_users_list;

                //hide notification
                $scope.notification.showMessage = false;
                $scope.notification.showErrors = false;

                $scope.files = [];

                if (clearForm) {
                    $scope.user = {role: 'agent', user_status: 'active', tags: []};
                }
            }

            // this function will be call to add user.
            $scope.add = function (event) {
                event.preventDefault();
                $scope.toggle_user_form(true);
                $scope.is_edit = false;
            }

            // add new user
            $scope.get_user = function (index, user) {
                $http.get(site_url + "?c=users&m=get&id=" + user.id).success(function (response) {
                    $scope.user = response;
                    $scope.user.index = $scope.records.indexOf(user);
                });
            }

            // update user
            $scope.edit_user = function (index, user) {
                $scope.toggle_user_form();
                $scope.get_user(index, user);
                $scope.is_edit = true;
            }

            // save user
            $scope.save_user = function (event, index) {
                event.preventDefault();
                var formdata = new FormData;

                angular.forEach($scope.user, function (value, key) {
                    if (key == 'tags') {
                        //formdata.append(key+'[]', value);
                    } else {
                        formdata.append(key, value);
                    }
                });

                angular.forEach($scope.user.tags, function (value, key) {
                    formdata.append("tags[]", value);
                });

                angular.forEach($scope.files, function (file) {
                    formdata.append('profile_pic', file);
                });

                if ($scope.is_edit && $scope.user.id) {
                    $http.post(site_url + "?c=users&m=update_user&id=" + $scope.user.id, formdata, {
                        transformRequest: angular.identity,
                        headers: {'Content-Type': undefined}
                    }).success(function (response) {
                        if (response.result == 'success') {
                            var imageUrl = response.user.profile_picture + '?decache=' + Math.random();
                            response.user.profilePic = imageUrl;
                            $scope.user.profile_picture = imageUrl;
                            $scope.records[index] = response.user;
                            $scope.files = [];
                            $scope.filename = '';

                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;

                            $scope.user.large_profile_picture = response.user.large_profile_picture;

                            $scope.user.profile_pic_large = imageUrl;

                            angular.element("#upload-profile_pic").val('');

                            if (response.user.is_image_upload == 1) {
                                $scope.crop_image();
                            } else {
                                $scope.toggle_user_form(true);
                                angular.element('#close-model').trigger('click');
                            }
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                    });
                } else {
                    $http.post(site_url + "?c=users&m=add_user", formdata, {
                        transformRequest: angular.identity,
                        headers: {'Content-Type': undefined}
                    }).success(function (response) {
                        if (response.result == 'success') {
                            var imageUrl = response.user.profile_picture + '?decache=' + Math.random();
                            response.user.profilePic = imageUrl;
                            $scope.user.id = response.user.id;
                            $scope.records.push(response.user);
                            $scope.files = [];
                            $scope.filename = '';
                            var imageUrl = response.user.profile_pic + '?decache=' + Math.random();

                            $scope.offset++;

                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;

                            $scope.user.large_profile_picture = response.user.large_profile_picture;
                            $scope.user.profile_pic_large = imageUrl;

                            angular.element("#upload-profile_pic").val('');

                            if (response.user.is_image_upload == 1) {
                                $scope.crop_image();
                            } else {
                                $scope.toggle_user_form(true);
                                angular.element('#close-model').trigger('click');
                            }
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                    });
                }
            }

            // crop image function
            $scope.crop_image = function () {
                angular.element("#crop-modal-box").modal({
                    backdrop: 'static'
                });

                angular.element(document).find(".cropper-container").remove();

                var $image = angular.element('#profile_pic');
                var cropBoxData;
                var canvasData;
                $scope.cropData = {};
                angular.element('#crop-modal-box').on('shown.bs.modal', function () {
                    $image.cropper({
                        autoCropArea: 0.5,
                        aspectRatio: 1 / 1,
                        viewMode: 3,
                        dragMode: 'move',
                        minCropBoxWidth: 120,
                        minCropBoxHeight: 120,
                        strict: true,
                        built: function () {
                            $image.cropper('setCanvasData', canvasData);
                            $image.cropper('setCropBoxData', {
                                width: 100,
                                height: 100
                            });
                            $scope.save_crop_image($image);

                        }
                    });
                }).on('hidden.bs.modal', function () {
                    cropBoxData = $image.cropper('getCropBoxData');
                    canvasData = $image.cropper('getCanvasData');
                    $image.cropper('destroy');

                    angular.element('body').addClass('modal-open');
                });
            };

            // saving a crop image in folder
            $scope.save_crop_image = function ($image) {
                angular.element(document).off('click.confirm').on("click.confirm", "#crop-image", function () {
                    var data = $image.cropper('getData');
                    $scope.cropData.x_axis = data.x;
                    $scope.cropData.y_axis = data.y;
                    $scope.cropData.height = data.height;
                    $scope.cropData.width = data.width;
                    $http.post(site_url + "?c=users&m=crop_user_picture&id=" + $scope.user.id, $scope.cropData).success(function (response) {
                        if (response.result == 'success') {
                            var imageUrl = response.profile_picture + '?decache=' + Math.random();
                            $scope.user.profile_picture = imageUrl;
                            $scope.user.profile_pic = response.profile_pic;

                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;

                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }

                        angular.element("#close_crop_modal").trigger('click');
                        $timeout(function () {
                            $scope.toggle_user_form(true);
                            angular.element('#close-model').trigger('click')
                        }, 1000);
                    });
                });
            }

            // remove user profile picture
            $scope.remove_photo = function (id, event) {
                event.preventDefault();
                if ($window.confirm("Do you want to delete it?")) {
                    $http.post(site_url + "?c=users&m=remove_picture&id=" + id).success(function (response) {
                        if (response.result == 'success') {
                            $scope.user.profile_pic = '';
                            $scope.user.profile_picture = response.src;

                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                    });
                }
            }

            // this function will be call to toggle display user password form.
            $scope.toggle_password_form = function (clearForm) {
                $scope.show_pass_form = !$scope.show_pass_form;
                $scope.show_users_list = !$scope.show_users_list;

                //hide notification
                $scope.notification.showMessage = false;
                $scope.notification.showErrors = false;

                if (clearForm) {
                    $scope.user = {role: 'agent', tags: []};
                }
            }

            //change password
            $scope.change_password = function (user) {
                $scope.toggle_password_form();
                $scope.user = user;
            }

            /*
             * update password
             * 
             * @param Event event
             * @param Int index
             * @returns {undefined}
             */
            $scope.update_password = function (event, index) {
                event.preventDefault();
                $http.post(site_url + "?c=users&m=update_password&id=" + $scope.user.id, $scope.user).success(function (response) {
                    if (response.result == 'success') {
                        $scope.records[index] = response.user;
                        $scope.toggle_password_form(true);

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;

                        angular.element('#close-passform-model').trigger('click');
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            /*
             * Change user status
             * 
             * @param Event event
             * @param Object record
             * @returns {undefined}
             */
            $scope.toogle_status = function (event, record) {
                event.preventDefault();
                if (record.user_status == 'active') {
                    $scope.block_user(record);
                } else {
                    $scope.active_user(record);
                }
            }

            /*
             * Do active user
             * @param Object record
             * @returns {undefined}
             */
            $scope.active_user = function (record) {
                $http.post(site_url + "?c=users&m=update_status&id=" + record.id, {user_status: 'active'}).success(function (response) {
                    if (response.result == 'success') {
                        record.user_status = 'active';

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            /*
             * Blocked User
             * @param Object record
             * @returns {undefined}
             */
            $scope.block_user = function (record) {
                $http.post(site_url + "?c=users&m=update_status&id=" + record.id, {user_status: 'blocked'}).success(function (response) {
                    if (response.result == 'success') {
                        record.user_status = 'blocked';

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            /*
             * Delete user from server
             * 
             * @param Object record
             * @param String conf_message
             * @returns {undefined}
             */
            $scope.delete_user = function (record, conf_message) {
                var confirm_delete = confirm(conf_message);
                if (confirm_delete) {
                    $http.post(site_url + "?c=users&m=delete_user&id=" + record.id).success(function (response) {
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
        $scope.tags = cmodule.tags;
        $scope.roles = cmodule.roles;
        $scope.selected_roles = [];
        $scope.selected_tags = [];

        $scope.toggle_check_roles = function (index, role_name) {
            if ($scope.selected_roles[index]) {
                $scope.filters.roles.push(role_name);
            } else {
                $scope.filters.roles.splice($.inArray(role_name, $scope.filters.roles), 1);
            }
        }

        $scope.toggle_check_tags = function (index, tag_id) {
            if ($scope.selected_tags[index]) {
                $scope.filters.tags.push(tag_id);
            } else {
                $scope.filters.tags.splice($.inArray(tag_id, $scope.filters.tags), 1);
            }
        }
    });

    app.directive('fileInput', ['$parse',
        function ($parse) {
            return {
                restrict: 'A',
                link: function (scope, elm, attrs) {
                    elm.bind('change', function () {
                        $parse(attrs.fileInput).assign(scope, elm[0].files)
                        scope.$apply()
                    });
                }
            }
        }
    ]);
})();