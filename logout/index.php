<?php
    session_start();

    unset($_SESSION['UserData']);

    if(isset($_SESSION['UserData'])){echo '仍然存在';}else{echo '消失了';}
?>