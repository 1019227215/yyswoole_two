<html class="no-js" lang="en">

<?php
include __DIR__ . '/../public/head.php';
?>

<body>
<!-- preloader area start -->
<div id="preloader">
    <div class="loader"></div>
</div>
<!-- preloader area end -->
<!-- page container area start -->
<div class="page-container">
    <!-- sidebar menu area start -->
    <?php
    include __DIR__ . '/../public/left.php';
    ?>
    <!-- sidebar menu area end -->
    <!-- main content area start -->
    <div class="main-content">
        <!-- page title area start -->
        <?php
        include __DIR__ . '/../public/top.php';
        ?>
        <!-- page title area end -->
        <div class="main-content-inner" style="text-align: center;">
            <?php isset($pathUrl) ? (include __DIR__ . $pathUrl) : ""; ?>
        </div>
    </div>
    <!-- main content area end -->
    <!-- footer area start-->
    <?php
    include __DIR__ . '/../public/footer.php';
    ?>
    <!-- footer area end-->
</div>
<!-- page container area end -->
<!-- offset area start -->
<?php
include __DIR__ . '/../public/bottom.php';
?>
<!-- offset area end -->
<?php
include __DIR__ . '/../public/tail.php';
?>
</body>

</html>
