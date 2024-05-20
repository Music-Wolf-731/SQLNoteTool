<?php
    session_start();

$pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');
    
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    print_r($_POST);
    if(isset($_POST['Cho_Page']) and $_POST['Cho_Page'] !== 'NewType'){
        
        $ForSQL = 'UPDATE type_page SET ';
        if($_POST['Page_Name'] !== ''){$ForSQL .= 'page_name = "'.$_POST['Page_Name'].'" ,';}
        if($_POST['PageContent'] !== ''){$ForSQL .= 'page_content = "'.$_POST['PageContent'].'" ,';}
        
        $ForSQL = rtrim($ForSQL, ",");
        $ForSQL .= " WHERE Type_page_id = 1";

        $sql=$pdo->prepare($ForSQL);$sql->execute();
    }else if($_POST['Cho_Page'] == 'NewType'){
        $ForSQL = $_SESSION['UserData']['Id'];
        $ForSQL .= ($_POST['Page_Name'] !== '')?',"'.$_POST['Page_Name'].'"':' , "預設名稱"';
        $ForSQL .= ($_POST['PageContent'] !== '')?',"'.$_POST['PageContent'].'"':' , "尚未寫入描述"';
        $ForSQL = 'INSERT INTO type_page(user_id,page_name,page_content) VALUES('.$ForSQL.')';
        $sql=$pdo->prepare($ForSQL);$sql->execute();
    }   
}



// 執行跳轉到新的頁面
?>
