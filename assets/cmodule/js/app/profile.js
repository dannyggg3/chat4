(function () {
    app.controller("ProfileController", ['$scope', '$http', '$window',
        function ($scope, $http, $window) {
            $scope.tags = cmodule.tags;
            $scope.user = cmodule.user;
            $scope.files = [];
            $scope.filename = '';
            $scope.show_pass_form = true

            // updating on files changed
            $scope.$watchCollection("files", function (newValue, oldValue) {
                if (newValue != oldValue) {
                    angular.forEach(newValue, function (file) {
                        $scope.filename = file.name.replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, "");
                    });
                }
            });

            // fetching user on ready
            $http.get(site_url+"?c=users&m=get&id="+$scope.user.id).success(function (response) {
                $scope.user = response;
            });

            $scope.cancel = function () {
                window.location = site_url;
            }

            $scope.toggle_password_form = function () {
                window.location = site_url;
            }

            // update password
            $scope.update_password = function (event, index) {
                event.preventDefault();
                //hide notification
                $scope.notification.showMessage = false;
                $scope.notification.showErrors = false;

                $http.post(site_url + "?c=users&m=update_password&id=" + $scope.user.id, $scope.user).success(function (response) {
                    if (response.result == 'success') {
                        $scope.user.pass = '';
                        $scope.user.confirm_pass = '';

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            // save user
            $scope.save_user = function (event) {
                event.preventDefault();
                //hide notification
                $scope.notification.showMessage = false;
                $scope.notification.showErrors = false;

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

                $http.post(site_url + "?c=users&m=update_profile&id=" + $scope.user.id, formdata, {
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }).success(function (response) {
                    if (response.result == 'success') {
                        $scope.user.profile_picture = response.profile_picture + '?decache=' + Math.random();
                        $scope.user.profile_pic = response.profile_pic;
                        angular.element("#upload-profile_pic").val('');

                        $scope.user.large_profile_picture = response.large_profile_picture;
                        $scope.user.profile_pic_large = response.profile_picture;
                        $scope.files = [];
                        $scope.filename = '';

                        // overriding user
                        $scope.overrideUser($scope.user);

                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;

                        if (response.is_image_upload == 1) {
                            $scope.crop_image($scope.user.id);
                        }
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }
                });
            }

            // open a popup for crop image
            $scope.crop_image = function (uid) {
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
                        viewMode: 3,
                        dragMode: 'move',
                        aspectRatio: 1 / 1,
                        minCropBoxWidth: 120,
                        minCropBoxHeight: 120,
                        strict: true,
                        built: function () {
                            $image.cropper('setCanvasData', canvasData);
                            $image.cropper('setCropBoxData', {
                                width: 100,
                                height: 100
                            });
                            $scope.save_crop_image($image, uid);
                        }
                    });
                }).on('hidden.bs.modal', function () {
                    cropBoxData = $image.cropper('getCropBoxData');
                    canvasData = $image.cropper('getCanvasData');
                    $image.cropper('destroy');
                });
            };

            // saving a crop image in folder
            $scope.save_crop_image = function ($image, uid) {
                angular.element(document).off('click.confirm').on("click.confirm", "#crop-image", function () {
                    var data = $image.cropper('getData');
                    $scope.cropData.x_axis = data.x;
                    $scope.cropData.y_axis = data.y;
                    $scope.cropData.height = data.height;
                    $scope.cropData.width = data.width;
                    $http.post(site_url + "?c=users&m=crop_user_picture&id=" + uid, $scope.cropData).success(function (response) {
                        if (response.result == 'success') {
                            var imageUrl = response.profile_picture + '?decache=' + Math.random();
                            $scope.user.profile_picture = imageUrl;
                            $scope.user.profile_pic = response.profile_pic;

                            // overriding user
                            $scope.overrideUser($scope.user);

                            $scope.notification.showMessage = true;
                            $scope.notification.message = response.message;
                            $scope.filename = '';
                        } else {
                            $scope.notification.showErrors = true;
                            $scope.notification.errors = response.errors;
                        }
                        angular.element("#close_crop_modal").trigger('click');
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

                            // overriding user
                            $scope.overrideUser($scope.user);

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