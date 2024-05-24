<?php
    session_start();

    unset($_SESSION['UserData'],$_SESSION['account']);

    if(!isset($_SESSION['UserData'])){echo '帳號資料已抹除';}else{echo '帳號資料抹除失敗';}
    if(!isset($_SESSION['UserData'])){echo '使用者資料已抹除';}else{echo '使用者資料抹除失敗';}
?>