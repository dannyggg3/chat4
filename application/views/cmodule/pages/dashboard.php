<div ng-controller="DashboardController">
    <div class="row">
        <div class="col-sm-12">
            <h2><?php echo $this->lang->line('dashboard'); ?></h2>
            <div class="panel panel-default">
                <div class="panel-heading"> 
                    <div class="panel-title" id="chart-title"><?php echo $this->lang->line('visitors_and_sessions_per_day'); ?></div> 
                </div>
                <div class="panel-body">
                    <div ng-init="" ng-mouseover="UseTooltip()" id="placeholder" class="demo-placeholder" style="height:300px; width: 100%;"></div>
                    <div id="placeholder_overview" style="width:100%;height:60px;"></div>
                    <p class="info-block">To zoom in, select the area and to reset click on <a href="#" ng-click="claerChart($event)">CLEAR</a></p>
                </div>
            </div>
        </div>
    </div>	
    <div class="row">
        <div class="col-sm-5">
            <div class="panel panel-default">
                <div class="panel-heading"> 
                    <div class="panel-title"><?php echo $this->lang->line('latest_pageviews'); ?></div> 
                </div>
                <div class="table-panel-body panel-body">
                    <div class="table-responsive">
                        <table class="table layout-fixed user-table">
                            <thead>
                                <tr>
                                    <th width="30%"><?php echo $this->lang->line('title'); ?></th>
                                    <th><?php echo $this->lang->line('page_url'); ?></th>
                                    <th class="text-center" width="72"><?php echo $this->lang->line('visits'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="row in pageviews">
                                    <td><a ng-href="{{row.page_url}}" target="_blank">{{row.page_title || '<?php echo $this->lang->line('no_page_title'); ?>'}}</a></td>
                                    <td><a ng-href="{{row.page_url}}" target="_blank">{{row.page_url}}</a></td>
                                    <td class="text-center">{{row.total}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-7">
            <div class="panel panel-default">
                <div class="panel-heading"> 
                    <div class="panel-title"><?php echo $this->lang->line('visitors_map'); ?></div> 
                </div>
                <div class="map-container">
                    <div ng-init="" id="usersMap" class="maps embed-responsive embed-responsive-16by9"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12"><?php powered_by();?></div>
    </div>
</div>