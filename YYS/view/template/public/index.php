
<html class="no-js" lang="en">

<?php
include 'head.php';
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
        include 'left.php';
    ?>
    <!-- sidebar menu area end -->
    <!-- main content area start -->
    <div class="main-content">
        <!-- page title area start -->
        <?php
            include 'top.php';
        ?>
        <!-- page title area end -->
        <div class="main-content-inner" style="text-align: center;">
            <div style="width: 100%;height: 80px; text-align: center;display: flex;flex-direction: column;justify-content: center;align-items: center;">
                <i class="fa fa-spinner" style="margin: 0 auto;" > 内容加载中...</i>
            </div>
        </div>
    </div>
    <!-- main content area end -->
    <!-- footer area start-->
    <?php
        include 'footer.php';
    ?>
    <!-- footer area end-->
</div>
<!-- page container area end -->
<!-- offset area start -->
<?php
    include 'bottom.php';
?>
<!-- offset area end -->
<?php
    include 'tail.php';
?>
</body>

</html>
