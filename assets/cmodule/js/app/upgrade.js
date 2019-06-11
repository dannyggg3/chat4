(function () {
    app.controller("UpgradeController", function ($scope, $http, $window, $log, Setting) {
        /*$scope.has_license_key = '';
        $scope.verified_license_key = false;
        $scope.processing = false;
        $scope.upgrade_text = cmodule.upgrade_text;
        $scope.processing_text = '';
        $scope.files_updated = cmodule.files_updated;
        $scope.action_type = cmodule.action_type;
        $scope.is_upgraded = false;
        
        $scope.record = {license_key: '', downloaded_filename: ''};*/
        $scope.plugins = [];

        /*
         * To get links of plugins.
         */
        $scope.getPluginsLinks = function () {
            $scope.toggleLoder();
            
            $http.get(Setting.site_url + "?c=upgrade&m=get_server&action=pro-links").success(function (response) {
                if(response.result == 'success') {
                    $scope.plugins = response.plugins;
                }
                
                $scope.toggleLoder();
            });
        }
        
        $scope.getPluginsLinks();
    });
})();