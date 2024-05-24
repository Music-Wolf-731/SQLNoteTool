
<?php

session_start();
require '../display.php';
unset($_SESSION['UserData'],$_SESSION['account']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php PrintHead('登入哈勒筆記') ?>
</head>
<body>


<?php
$pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');

//先列出SQL的指令，內部的?則為事後填入的變數
$sql=$pdo->prepare("select * from login where account='Miro_OwO' and password='000000'");
//填入?並激活SQL指令
echo '帳：'.$_POST['login'] .'  密：'.$_POST['password'];
$sql->execute([$_POST['login'], $_POST['password']]);

//
foreach ($sql->fetchAll() as $row) { $_SESSION['account'] = $row['account']; echo "有嗎？";}

if (isset($_SESSION['account'])) {
	$sql=$pdo->prepare('select * from userdata where account=?');
	$sql->execute([$_SESSION['account']]);
	foreach ($sql->fetchAll() as $row) {
		$_SESSION['UserData']['Id'] = $row['user_id'];
		$_SESSION['UserData']['name'] = $row['user_name'];
		$_SESSION['UserData']['avatar'] = $row['avatar_img'];
	}
	echo $_SESSION['UserData']['name'] . '登錄成功<br><a href="../choose-page">前往筆記選頁</a>' ;
} else {
	echo '登入ID或密碼有誤。<br><a href="../login">返回登入頁</a>';
} 

?>


</body>
</html>
