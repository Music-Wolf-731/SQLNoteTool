<?php
    session_start();


    $pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');
    
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        print_r($_POST);
        if(isset($_POST['Cho_Page']) and $_POST['Cho_Page'] !== 'NewType'){
            
            if($_POST['Page_Name'] == 'delete'){
                $ForSQL = 'DELETE FROM type_page WHERE Type_page_id = '.$_POST['Cho_Page'];
            }else{
                $ForSQL = 'UPDATE type_page SET ';
                if($_POST['Page_Name'] !== ''){$ForSQL .= 'page_name = "'.$_POST['Page_Name'].'" ,';}
                if($_POST['PageContent'] !== ''){$ForSQL .= 'page_content = "'.$_POST['PageContent'].'" ,';}
                
                $ForSQL = rtrim($ForSQL, ",");
                $ForSQL .= " WHERE Type_page_id = ".$_POST['Cho_Page'];
                echo $ForSQL;
            }
            echo $ForSQL;

            $sql=$pdo->prepare($ForSQL);$sql->execute();

        }else if(isset($_POST['Cho_Page']) and $_POST['Cho_Page'] == 'NewType'){
            $ForSQL = $_SESSION['UserData']['Id'];
            $ForSQL .= ($_POST['Page_Name'] !== '')?',"'.$_POST['Page_Name'].'"':' , "預設名稱"';
            $ForSQL .= ($_POST['PageContent'] !== '')?',"'.$_POST['PageContent'].'"':' , "尚未寫入描述"';
            $ForSQL = 'INSERT INTO type_page(user_id,page_name,page_content) VALUES('.$ForSQL.')';
            $sql=$pdo->prepare($ForSQL);$sql->execute();
        }
        
        // 清除 POST 記錄
        $_POST = null;

        // 重定向到當前頁面
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }


    
    //先列出SQL的指令，內部的?則為事後填入的變數
    $sql=$pdo->prepare('select * from type_page where user_id=?');

    function WritePagePain($arr){
        $ForReturn = '';
        $ForReturn .= '<div class="title">'.$arr['page_name'] . '</div>';
        $ForReturn .= '<div class="content">'.$arr['page_content'] . '</div>';
        $ForReturn = '<a href="Text_River.php/?PageId='.$arr['Type_page_id'].'"><div class="Page_Pain">'. $ForReturn . '</div></a>';
        echo $ForReturn;
    }





?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .Page_Pain {
            width:100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin:0 0 1.3em;
            border: 2px solid #000;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <?php

    
    //填入?並激活SQL指令
    $sql->execute([$_SESSION['UserData']['Id']]);
	foreach ($sql->fetchAll() as $row) {WritePagePain($row);}

    ?>
    <form action="" method="post" style="width:100%;display:flex;flex-wrap: wrap;">
        <select name="Cho_Page" id="" value="" style="width: 40%;">
            <option value="NewType" disabled selected>選擇要編輯的項目</option>
            <?php
                $sql->execute([$_SESSION['UserData']['Id']]);
                foreach ($sql->fetchAll() as $row) {
                    echo '<option value="'.$row['Type_page_id'].'">'.$row['page_name'].'</option>';
                }
            ?>
            <option value="NewType" style="color: red;">新增項目</option>
        </select>
        <input type="text" name="Page_Name" style="width: 56%;">
        <textarea name="PageContent" id="" style="width: 100%;"></textarea>
        <input type="submit" value="提交">  
    </form>
    
</body>
</html>

