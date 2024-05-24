<?php session_unset(); 
unset($_SESSION);

require 'display.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php PrintHead('登入哈勒筆記') ?>
</head>
<body>
    <form action="login-output.php" method="post">
    登入ID<input type="text" name="login"><br>
    密碼<input type="password" name="password"><br>
    <input type="submit" value="登入">
    </form>
</body>
</html>