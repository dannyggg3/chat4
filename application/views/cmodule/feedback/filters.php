<div id="filter" ng-controller="FilterCtrl">
    <div class="filter-inner">
        <div class="block-header"><button aria-label="Close" ng-click="hide_filter($event);" data-dismiss="filter-block" class="close" type="button"><span aria-hidden="true">×</span></button><h3><?php echo $this->lang->line('filter');?></h3></div>
        <form>
            <div class="form-group">
                <h5><?php echo $this->lang->line('agents');?></h5>
                <div ng-repeat="row in agents" class="checkbox"><label><input checklist-model="filters.agents" type="checkbox" checklist-value="row.id"> {{row.name}}</label></div>
            </div>
            <button class="btn btn-primary btn-sm"  style="margin-right: 10px" ng-click="checkAll()">Check all</button>
            <button class="btn btn-primary btn-sm"  style="margin-right: 10px" ng-click="uncheckAll()">Uncheck all</button>
        </form>
    </div>
</div>