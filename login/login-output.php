
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
	<style>
		
		body{
			height: 100vh;
			display: flex;
			align-items: center;
			flex-direction: column;
			justify-content: space-around;
		}
                
	</style>
    <?php echo PrintHead('登入哈勒筆記') ?>
</head>
<body>


<?php
$pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');

//先列出SQL的指令，內部的?則為事後填入的變數
$sql=$pdo->prepare('select * from login where account=? and password=?');
//填入?並激活SQL指令
$sql->execute([$_REQUEST['login'], $_REQUEST['password']]);

//
foreach ($sql->fetchAll() as $row) { $_SESSION['account'] = $row['account']; }
if (isset($_SESSION['account'])) {
	$sql=$pdo->prepare('select * from userdata where account=?');
	$sql->execute([$_SESSION['account']]);
	foreach ($sql->fetchAll() as $row) {
		$_SESSION['UserData']['Id'] = $row['user_id'];
		$_SESSION['UserData']['name'] = $row['user_name'];
		$_SESSION['UserData']['avatar'] = $row['avatar_img'];
	}
	echo '
                <div id="OnlyBox">
                    <div>
						<p>' . $_SESSION['UserData']['name'] . ' 
                        歡迎登入</p>
                        <a href="../choose-page"><div>前往筆記選頁</div></a>
                    </div>
                </div>' ;
} else {
	echo '
	
                <div id="OnlyBox">
                    <div>
						<p>登入資訊有誤</p>
                        <a href="../login"><div>返回登入頁</div></a>
                    </div>
                </div>
	
				';
} 

?>


</body>
</html>
