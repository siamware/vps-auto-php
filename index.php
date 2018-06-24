<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/engine/autoload.php';

if(isset($_GET['action'])) {
    if($_GET['action'] == "confirmEmail") {
        if($engine->user->confirmEmail($_GET['token'])) {
            echo "<script>alert('ยืนยันอีเมล์สำเร็จ');window.location = 'index.php';</script>";
        }else{
            header("Location: index.php");
        }
    }
}

$engine->template->bundleJS('vendor');
$engine->template->bundleJS('apps');
?>
    <!DOCTYPE html>
    <html>

    <head>
        <!-- ========== Title ========== -->
        <title>
            <?php echo config('sitename');?>
        </title>
        <!-- ========== Meta Tags ========== -->
        <meta charset="utf-8" />
        <meta name="description" content="<?php echo config('sitedesc');?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!-- ========== Favicon Ico ========== -->
        <link rel="apple-touch-icon" sizes="57x57" href="assets/favicon/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="assets/favicon/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="assets/favicon/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="assets/favicon/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="assets/favicon/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="assets/favicon/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="assets/favicon/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="assets/favicon/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192" href="assets/favicon/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
        <link rel="manifest" href="assets/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="assets/favicon/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <!-- ========== STYLESHEETS ========== -->
        <link href="assets/lib/font-awesome/css/font-awesome.css" rel="stylesheet">
        <link href="assets/lib/Ionicons/css/ionicons.css" rel="stylesheet">
        <link href="assets/lib/chartist/css/chartist.css" rel="stylesheet">
        <link href="assets/lib/rickshaw/css/rickshaw.min.css" rel="stylesheet">
        <link href="assets/lib/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
        <link href="assets/lib/select2/css/select2.min.css" rel="stylesheet">
        <link href="assets/lib/SpinKit/css/spinkit.css" rel="stylesheet">
        <link href="assets/css/slim.css" rel="stylesheet">
    </head>

    <body class="slim-sticky-header">
        <div id="app"></div>

        <?php if($_SERVER['SERVER_ADDR'] != "::1" && $_SERVER['SERVER_ADDR'] != "localhost" && $_SERVER['SERVER_ADDR'] != "127.0.0.1") { ?>
        <!-- Load Facebook SDK for JavaScript -->
        <div id="fb-root"></div>
        <script>
            (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s);
                js.id = id;
                js.src =
                    'https://connect.facebook.net/th_TH/sdk/xfbml.customerchat.js#xfbml=1&version=v2.12&autoLogAppEvents=1';
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        <!-- Your customer chat code -->
        <div class="fb-customerchat" attribution="setup_tool" page_id="1019592918085968" theme_color="#0084ff" logged_in_greeting="สวัสดีครับ มีปัญหาการใช้งานหรือไม่?"
            logged_out_greeting="สวัสดีครับ มีปัญหาการใช้งานหรือไม่?">
        </div>
        <?php } ?>

        <!-- Product -->
        <script>
            var _language = {};
        </script>

        <script src="lang/lang.js?<?php echo $engine->template->id('../lang/lang')?>" type="text/javascript"></script>
        <script src="dist/vendor.js?<?php echo $engine->template->id('vendor')?>" type="text/javascript"></script>

        <script src="https://unpkg.com/promise-polyfill"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2"></script>

        <script src="dist/apps.js?<?php echo $engine->template->id('apps')?>" type="text/javascript"></script>
        <!--<script type="text/javascript" src="https://cdn.omise.co/omise.js">-->
        </script>
        <!-- Template storage -->
        <noscript id="template-container"></noscript>
    </body>

    </html>

    <?php
    close_connection();
    ?>