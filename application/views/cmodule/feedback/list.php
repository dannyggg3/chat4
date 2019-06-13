<div class="" ng-controller="FeedbackController">
    <div class="header-panel clearfix">
        <h2 class="panel-title pull-left"><?php echo $this->lang->line('feedbacks'); ?></h2>
        <div ng-show="filter_button" class="pull-right col-filter"><a href="#/" ng-click="show_filter($event)"><i class="fa fa-filter"></i></a></div>
    </div>
    <div class="feedback-container">
        <div class="feedback-list feedback-even clearfix" ng-repeat="record in records">
            <div class="feedback-user" ng-init="rand_color = getColor()">
                <img ng-show="record.receiver_profile_pic" class="img-circle avatar" ng-src="{{record.receiverProfilePic}}" alt="{{record.receiverName}}" title="{{record.receiverName}}" />
                <span ng-hide="record.receiver_profile_pic" style="background-color: {{rand_color}};" class="user-avatar" title="{{record.receiverName}}">{{record.receiverName|oneCapLetter}}</span>
                <h6 class="user-name">{{record.receiverName}}</h6>
            </div>
            <div class="feedback">
                <p ng-bind-html="record.message | newlines"></p>
                <p class="feedback-footer clearfix"><span class="rating">Califícanos:{{record.rating}}/5 <span>({{rating_status[record.rating]}})</span></span><cite class="feedback-by">{{record.senderName}}</cite></p>
            </div>
        </div>
    </div>
    <div class="load-more-block">
        <div ng-show="showNoRecordMessage" role="alert" class="alert alert-info"> <?php echo $this->lang->line('no_record_found'); ?></div>
        <button class="btn btn-success btn-primary text-center" ng-click="load_more()"><?php echo $this->lang->line('load_more'); ?> <i ng-show="loading" class="fa fa-refresh fa-spin"></i></button>
    </div>
</div>