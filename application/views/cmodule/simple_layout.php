<!DOCTYPE html>
<html lang="en" ng-app="cmodule" class="login-page">
<head><?php include theme_path('partials/head.php'); ?></head>
<body ng-controller="BodyController" class="<?php echo body_classes(); ?>" ng-cloak>
    <?php //include theme_path('partials/header.php');?>
            <?php include theme_path('partials/notifications.php'); ?>
            <?php include theme_path($this->data['view'] . '.php'); ?>
    <?php include theme_path('partials/end.php'); ?>
</body>
</html>