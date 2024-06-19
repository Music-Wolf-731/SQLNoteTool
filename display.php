<?php 
function SessionSet(){
    ini_set('session.cookie_lifetime', 172800);
    session_set_cookie_params(172800);
    session_start();
}

function PrintTopBar($Type,$ExtarArr){
    $ForReturn = '';
    switch ($Type) {
        case 'WordRiver':
            $ForReturn .= '
                <div id="TopBar">
                    <div>
                        <div id="TopBarMainMenu">
                            <a href="../choose-page"><p>回選頁</p></a>
                            <a class="" data-bs-toggle="collapse" href="#GroupABox" role="button" aria-expanded="false" aria-controls="GroupABox">
                                <p>前往群組</p>
                            </a>
                            <a href="../Edit_Group.php/?PageId='.$_GET["PageId"].'"><p>編輯群組</p></a>
                        </div>
                        <div>
                            <a data-bs-toggle="modal" data-bs-target="#LogOutCheck"><p>登出</p></a>
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
                            <a href="../choose-page"><p>回選頁</p></a>
                            <a href="../textRiver/?PageId='.$_GET["PageId"].'"><p>返回字詞頁</p></a>
                        </div>
                        <div>
                            <a data-bs-toggle="modal" data-bs-target="#LogOutCheck"><p>登出</p></a>
                        </div>
                    </div>
                </div>
            ';

            
            break;
        
        case 'ChoosePage':
            $ForReturn .= '
                <div id="TopBar">
                    <div>
                        <div id="TopBarMainMenu">
                        </div>
                        <div>
                            <a data-bs-toggle="modal" data-bs-target="#LogOutCheck"><p>登出</p></a>
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
    <div class="modal fade" id="LogOutCheck" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div id="logoutWindow" class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <h3>確定要登出嗎？</h3>
            <a href="../logout"><button type="button" class="btn btn-primary">登出</button></a>
        </div>
      </div>
    </div>
    ';
    echo $ForReturn;
}

function OnCheckSignIn($UnlimitedEnter = false){
    if(!isset($_SESSION['UserData'])){
        $BackBut = '<a href="../login"><div>點我前往登錄頁</div></a>';
    }else{
        $BackBut = '<a href="../choose-page"><div>點我回頁目總款</div></a>';
    }
    //未登入則會禁止進入
    $NoEnter = (!isset($_SESSION['UserData']))?true:false;
    //無限制進入若開啟，則會取消觀看限制
    $NoEnter = ($UnlimitedEnter)?false:$NoEnter;

    if($NoEnter){
        echo '
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
                '. PrintHead('哈勒筆記 | 登入過渡') .'

            </head>
            
            <body>
                <div id="OnlyBox">
                    <div>
                        <p>您無權查看目前內容</p>
                        '.$BackBut.'
                    </div>
                </div>
            </body>
            </html>
        ';
        exit(); 
    }
}
function PrintHead($title){
    return '
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

