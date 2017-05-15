<!-- <!doctype html> -->
<html class="no-js" data-ng-app="urbanApp">
  <head>
    <meta charset="utf-8">
    <title>urban admin ui kit</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1, maximum-scale=1">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

    <!-- oclazyload stylesheets before this tag -->
    <meta id="load_styles_before">

    <!-- build:css({.tmp,app}) styles/app.min.css -->
    <link rel="stylesheet" href="vendor/bootstrap/dist/css/bootstrap.css">
    <link rel="stylesheet" href="vendor/perfect-scrollbar/css/perfect-scrollbar.css">
    <link rel="stylesheet" href="styles/roboto.css">
    <link rel="stylesheet" href="styles/font-awesome.css">
    <link rel="stylesheet" href="styles/panel.css">
    <link rel="stylesheet" href="styles/feather.css">
    <link rel="stylesheet" href="styles/animate.css">
    <link rel="stylesheet" href="styles/urban.css">
    <link rel="stylesheet" href="styles/urban.skins.css">
    <link rel="stylesheet" href="styles/custom.css">
    <link rel="stylesheet" href="styles/basic.css">
    <link rel="stylesheet" href="vendor/dropdown/dropdown.css">
    <link rel="stylesheet" href="vendor/bootstrap-datepicker/datepicker3.css">
    <link rel="stylesheet" href="vendor/dropzone/basic.css">
    <link rel="stylesheet" href="vendor/dropzone/dropzone.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- endbuild -->

  </head>
  <body data-ng-controller="AppCtrl">

    <!-- quick launch panel -->
    <div class="quick-launch-panel" data-ng-include="'views/common/quick-launch-panel.html'"></div>
    <!-- /quick launch panel -->

    <div class="app" data-ui-view>
    </div>

    <!-- <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script> -->

    <!-- build:js({.tmp,app}) scripts/app.min.js -->
    <script src="scripts/extentions/modernizr.js"></script>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.1.1.min.js"></script>
    <script src="vendor/angular/angular.js"></script>
    <script src="vendor/angular-bootstrap/ui-bootstrap-tpls.js"></script>
    <script src="vendor/angular-animate/angular-animate.js"></script>
    <script src="vendor/angular-ui-router/release/angular-ui-router.js"></script>
    <script src="vendor/angular-sanitize/angular-sanitize.js"></script>
    <script src="vendor/angular-touch/angular-touch.js"></script>
    <script src="vendor/angular-ui-utils/ui-utils.js"></script>
    <script src="vendor/ngstorage/ngStorage.js"></script>
    <script src="vendor/ocLazyLoad/dist/ocLazyLoad.js"></script>
    <script src="vendor/perfect-scrollbar/js/perfect-scrollbar.jquery.js"></script>
    <script src="scripts/extentions/lib.js"></script>
    <script src="vendor/fastclick/lib/fastclick.js"></script>

    <script src="scripts/app.js"></script>
    <script src="scripts/app.main.js"></script>
    <script src="scripts/config.router.js"></script>

    <script src="scripts/directives/anchor-scroll.js"></script>
    <script src="scripts/directives/c3.js"></script>
    <script src="scripts/directives/chosen.js"></script>
    <script src="scripts/directives/navigation.js"></script>
    <script src="scripts/directives/offscreen.js"></script>
    <script src="scripts/directives/panel-control-collapse.js"></script>
    <script src="scripts/directives/panel-control-refresh.js"></script>
    <script src="scripts/directives/panel-control-remove.js"></script>
    <script src="scripts/directives/preloader.js"></script>
    <script src="scripts/directives/quick-launch.js"></script>
    <script src="scripts/directives/rickshaw.js"></script>
    <script src="scripts/directives/scrollup.js"></script>
    <script src="scripts/directives/tooltip.js"></script>
    <script src="scripts/directives/vector.js"></script>
    <script src="vendor/dropzone/dropzone.js"></script>
    <script src="vendor/dropdown/dropdown.js"></script>

    <script src="vendor/bootstrap-datepicker/bootstrap-datepicker.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <!-- endbuild -->
</body>
</html>