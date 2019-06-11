<div class="modal fade" id="crop-modal-box" aria-labelledby="modalLabel" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabel">Crop the image</h4>
            </div>
            <div class="modal-body">
                <div class="cropper-modal-inner" ng-show="user.profile_pic_large">
                    <img id="profile_pic" class="" ng-src="{{user.large_profile_picture}}" alt="Profile Picture" title="" />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="crop-image" class="btn btn-primary getWidth">
                    Crop
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="close_crop_modal">Cancel</button>
            </div>
        </div>
    </div>
</div>