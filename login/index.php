<?php 
session_start();
if(isset($_SESSION['UserData'])){
    header("Location: ../choose-page");
}
// exit();
// session_unset(); 
// unset($_SESSION);

require '../display.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/Login.css">
    <?php echo PrintHead('登入哈勒筆記') ?>
</head>
<body>
    <form action="login-output.php" method="post">
        <div id="LoginBox">
            <div>
                <div><p>帳號</p><input type="text" name="login"></div>
                <div><p>密碼</p><input type="password" name="password"></div>
                <input type="submit" value="登入">
            </div>
        </div>
    </form>
</body>
</html>