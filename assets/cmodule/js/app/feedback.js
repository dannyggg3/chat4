(function () {

    app.controller("FeedbackController", ['$scope', '$http', 'filterService', function ($scope, $http, filterService) {
            $scope.filters = filterService;
            $scope.rating_status = {1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very good', 5: 'Excellent'};

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

                $http.post(site_url + "?c=feedbacks&m=get_feddback_list", $scope.filters).success(function (data) {
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

                $http.post(site_url + "?c=feedbacks&m=get_feddback_list", $scope.filters).success(function (data) {
                    $scope.offset = data.length;
                    $scope.records = data;
                });
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