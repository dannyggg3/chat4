(function () {
    app.controller("SettingController", function ($scope, $http, $window, Setting) {
        $scope.settings = cmodule.settings;
        $scope.settings.time_interwal = parseInt(cmodule.settings.time_interwal);
        $scope.settings.time_automatically_open_chatbox = parseInt(cmodule.settings.time_automatically_open_chatbox);
        $scope.settings.file_upload_size = parseInt(cmodule.settings.file_upload_size);
        $scope.files = [];
        $scope.logofiles = [];
        $scope.filename = '';
        $scope.logo_filename = '';
        $scope.tokens = [];
        $scope.record = {site_url: ''};
        $scope.verify_lkey = true;
        $scope.plugin = {license_key: angular.copy($scope.settings.licence_key)}

        if ($scope.settings.plugin_validated == 'yes') {
            $scope.verify_lkey = false;
        }

        // updating on files changed
        $scope.$watchCollection("files", function (newValue, oldValue) {
            if (newValue != oldValue) {
                angular.forEach(newValue, function (file) {
                    $scope.filename = file.name.replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, "");
                });
            }
        });

        $scope.$watchCollection("logofiles", function (newValue, oldValue) {
            if (newValue != oldValue) {
                angular.forEach(newValue, function (file) {
                    $scope.logo_filename = file.name.replace(/"/g, "").replace(/'/g, "").replace(/\(|\)/g, "");
                });
            }
        });

        /*
         * To change license key will show input box.
         * @param {type} event
         * @returns {undefined}
         */
        $scope.toggle_license_key = function (event) {
            event.preventDefault();

            $scope.verify_lkey = !$scope.verify_lkey;
        }

        /*
         * To verify license key from server
         * @param {type} event
         * @returns {undefined}
         */
        $scope.verify_license_key = function (event) {
            event.preventDefault();
            $scope.toggleLoder();

            //hide notification
            $scope.notification.showMessage = false;
            $scope.notification.showErrors = false;

            $http.post(Setting.site_url + "?c=settings&m=verify_license_key", $scope.plugin).success(function (response) {
                if (response.result == 'success') {
                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.message;

                    $scope.settings.licence_key = angular.copy($scope.plugin.license_key);
                    $scope.settings.plugin_validated = 'yes';
                    $scope.verify_lkey = false;
                } else {
                    $scope.notification.showErrors = true;
                    $scope.notification.errors = response.errors;
                }

                $scope.toggleLoder();
            });
        }

        /*
         * To unregister license key from server
         * @param {Event} event
         * @param {String} unregister_confirm
         * @returns {undefined}
         */
        $scope.unregister = function (event, unregister_confirm) {
            event.preventDefault();

            var $confirm = $window.confirm(unregister_confirm);

            if ($confirm) {
                $scope.toggleLoder();
                //hide notification
                $scope.notification.showMessage = false;
                $scope.notification.showErrors = false;

                $http.post(Setting.site_url + "?c=settings&m=unregister", $scope.plugin).success(function (response) {
                    if (response.result == 'success') {
                        $scope.notification.showMessage = true;
                        $scope.notification.message = response.message;

                        $scope.plugin.license_key = '';
                        $scope.settings.licence_key = angular.copy($scope.plugin.license_key);
                        $scope.settings.plugin_validated = 'no';
                        $scope.verify_lkey = true;
                    } else {
                        $scope.notification.showErrors = true;
                        $scope.notification.errors = response.errors;
                    }

                    $scope.toggleLoder();
                });
            }
        }

        // update password
        $scope.update_settings = function (event, index) {
            event.preventDefault();
            $scope.toggleLoder();

            //hide notification
            $scope.notification.showMessage = false;
            $scope.notification.showErrors = false;

            $http.post(Setting.site_url + "?c=settings&m=update_settings", $scope.settings).success(function (response) {
                if (response.result == 'success') {

                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.message;
                } else {
                    $scope.notification.showErrors = true;
                    $scope.notification.errors = response.errors;
                }

                $scope.toggleLoder();
            });
        }

        // update theme style
        $scope.update_theme = function () {
            $scope.custom_styles = ".chat-cmodule-header, .chatnox-btn-default, .chat-cmodule-header *, .chat-cmodule-header, .chat-cmodule-widget-head, .cmodule-window-widget-title, .chat-cmodule .cmodule-chat-icon { color: " + $scope.settings.title_color + " !important; }";
            $scope.custom_styles += ".chat-cmodule-header, .chatnox-btn-default, .chat-cmodule-header, .chat-cmodule-widget-head, .chat-cmodule .cmodule-chat-btn { background-color: " + $scope.settings.background_color + " !important; }";

            $scope.update_custom_styles($scope.custom_styles);
        }

        $scope.update_theme();

        $scope.upload_avatar = function (event) {
            event.preventDefault();
            $scope.toggleLoder();
            
            //hide notification
            $scope.notification.showMessage = false;
            $scope.notification.showErrors = false;

            var formdata = new FormData();
            angular.forEach($scope.files, function (file) {
                formdata.append('avatar', file);
            });

            $http.post(Setting.site_url + "?c=settings&m=upload_avatar", formdata, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(function (response) {
                if (response.result == 'success') {
                    $scope.settings.default_avatar = response.avatar_url;
                    $scope.files = [];
                    $scope.filename = '';
                    angular.element('#default_avatar_img').val('');

                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.message;
                }
                $scope.toggleLoder();
            }).error(function () {
                $scope.toggleLoder();
            });
        }

        $scope.upload_logo = function (event) {
            event.preventDefault();
            $scope.toggleLoder();
            
            //hide notification
            $scope.notification.showMessage = false;
            $scope.notification.showErrors = false;

            var formdata = new FormData();
            angular.forEach($scope.logofiles, function (file) {
                formdata.append('logofile', file);
            });

            $http.post(Setting.site_url + "?c=settings&m=upload_logo", formdata, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            }).success(function (response) {
                if (response.result == 'success') {
                    $scope.settings.site_logo = response.site_logo;
                    $scope.logofiles = [];
                    $scope.logo_filename = '';
                    angular.element('#site_logo_img').val('');

                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.message;
                }

                $scope.toggleLoder();
            }).error(function () {
                $scope.toggleLoder();
            });
        }

        /*
         * To fetch all access tikens
         * @returns {undefined}
         */
        $scope.get_tokens = function () {
            $http.get(Setting.site_url + "?c=settings&m=get_tokens").success(function (response) {
                $scope.tokens = response.tokens;
            });
        }

        $scope.get_tokens();

        /*
         * Will enter new chatbox token entry in database
         * @param event
         */
        $scope.generate_code = function (event) {
            event.preventDefault();
            $scope.toggleLoder();
            
            //hide notification
            $scope.notification.showMessage = false;
            $scope.notification.showErrors = false;

            $http.post(Setting.site_url + "?c=settings&m=generate_code", $scope.record).success(function (response) {
                if (response.result == 'success') {

                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.message;

                    $scope.record = {site_url: ''};
                    $scope.get_tokens();
                } else {
                    $scope.notification.showErrors = true;
                    $scope.notification.errors = response.errors;
                }

                $scope.toggleLoder();
            });
        }

        /*
         * To edit token
         * 
         * @param event
         * @param {object} token
         */
        $scope.edit_token = function (event, token) {
            event.preventDefault();

            $scope.record = angular.copy(token);
        }

        /*
         * To cancel action
         * 
         * @param event
         */
        $scope.cancel_action = function (event) {
            event.preventDefault();

            $scope.record = {site_url: ''};
        }

        /*
         * To update token entry in database
         * @param event
         */
        $scope.update_code = function (event) {
            event.preventDefault();
            $scope.toggleLoder();
            
            //hide notification
            $scope.notification.showMessage = false;
            $scope.notification.showErrors = false;

            $http.post(Setting.site_url + "?c=settings&m=update_code", $scope.record).success(function (response) {
                if (response.result == 'success') {

                    $scope.notification.showMessage = true;
                    $scope.notification.message = response.message;

                    $scope.record = {site_url: ''};
                    $scope.get_tokens();
                } else {
                    $scope.notification.showErrors = true;
                    $scope.notification.errors = response.errors;
                }

                $scope.toggleLoder();
            });
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
    ]).directive('selectOnClick', function ($window) {
        return {
            link: function (scope, element) {
                element.on('click', function () {
                    var selection = $window.getSelection();
                    var range = document.createRange();
                    range.selectNodeContents(element[0]);
                    selection.removeAllRanges();
                    selection.addRange(range);
                });
            }
        }
    });
})();