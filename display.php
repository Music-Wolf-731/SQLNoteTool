<?php 

function PrintTopBar($Type,$ExtarArr){
    $ForReturn = '';
    switch ($Type) {
        case 'WordRiver':
            $ForReturn .= '
                <div id="TopBar">
                    <div>
                        <div id="TopBarMainMenu">
                            <a href="../choosePage.php"><p>回選頁</p></a>
                            <a class="" data-bs-toggle="collapse" href="#GroupABox" role="button" aria-expanded="false" aria-controls="GroupABox">
                                <p>前往群組</p>
                            </a>
                            <a href="../Edit_Group.php/?PageId='.$_GET["PageId"].'"><p>編輯群組</p></a>
                        </div>
                        <div>
                            <a data-bs-toggle="modal" data-bs-target="#exampleModal"><p>登出</p></a>
                        </div>
                    </div>
                    <div class="collapse" id="GroupABox">
                        <div class="FlexBox">
                            '.WriteGroupABox($ExtarArr).'
                        </div>
                    </div>
                </div>
            ';
            break;
        
        case 'EditWordGroup':
            $ForReturn .= '
                <div id="TopBar">
                    <div>
                        <div id="TopBarMainMenu">
                            <a href="../choosePage.php"><p>回選頁</p></a>
                            <a href="../Text_River.php/?PageId='.$_GET["PageId"].'"><p>返回字詞頁</p></a>
                        </div>
                        <div>
                            <a data-bs-toggle="modal" data-bs-target="#exampleModal"><p>登出</p></a>
                        </div>
                    </div>
                </div>
            ';

            
            break;
        
        case 'test':

            
            break;
        
        default:
            # code...
            break;
    }
    $ForReturn .= '<div id="TopBarSpace"></div>';

    //登出視窗
    $ForReturn .= '
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div id="logoutWindow" class="modal-dialog">
        <div class="modal-content">
            <h3>確定要登出嗎？</h3>
            <button type="button" class="btn btn-primary">登出</button>
        </div>
      </div>
    </div>
    ';
    echo $ForReturn;
}

function OnCheckSignIn(){
    if(!isset($_SESSION['UserData'])){
        echo '<a href="../login-input.php">請先進行登錄</a>';
        exit(); 
    }
}
function PrintHead($title){
    echo'
        <title>'.$title.'</title>
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="../bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="../css/display.css">
        <link rel="stylesheet" href="css/display.css">
        <link rel="stylesheet" href="/resources/demos/style.css">
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>
    ';
}

?>