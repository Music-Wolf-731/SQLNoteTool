<?php
    session_start();

    unset($_SESSION['UserData'],$_SESSION['account']);

    require '../display.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Login.css">
    <?php echo PrintHead('登出哈勒筆記') ?>
</head>
<body>
    <form action="login-output.php" method="post">
        <div id="LogoutBox">
            <div>
                您已成功登出
                <a href="../"><input type="button" value="回主頁"></a>
            </div>
        </div>
    </form>
</body>
</html>