<?php 
session_start();

if(!isset($_SESSION['UserData'])){
    echo '請先進行登錄';
    exit(); 
}

$pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');



if ($_SERVER["REQUEST_METHOD"] == "POST"){
    
    try {
        $pdo->beginTransaction();
        //刪除字詞
        if(isset($_POST['delete'])){
            $ForSql = '
                DELETE FROM word
                WHERE user_id = :user_id AND word_id = :wordId;
            ';

            $stmt = $pdo->prepare($ForSql);

            $SQL_user_id = $_SESSION['UserData']['Id'];
            $SQL_word_id = $_POST['wordId'];
            $stmt->bindParam(':user_id', $SQL_user_id);
            $stmt->bindParam(':wordId', $SQL_word_id);

            $stmt->execute();
            // 重定向到當前頁面
            // header("Location: ".$_SERVER['HTTP_REFERER']);
            // exit();
            
        } else {

            //編輯或是新增字詞


            if(isset($_POST['edit'])){
                $ForSql = '
                    UPDATE word 
                    SET
                    word = :word,
                    word_name = :word_name,
                    word_content = :word_content 
                    WHERE user_id = :user_id AND word_id = :wordId;
                    ';
            }else{
                $ForSql = '
                    INSERT INTO word(user_id,word,word_name,word_content)
                    VALUES (:user_id, :word, :word_name, :word_content);
                    SET @last_id_in_A_table = LAST_INSERT_ID(); -- 获取自动生成的 ID

                    -- 使用获取的 ID，在 bridge_table 插入新记录
                    INSERT INTO word_page_bridge (word_id, Type_page_id) VALUES (@last_id_in_A_table, :Page);';
            }


                //置入SQL定義
                $SQL_user_id = $_SESSION['UserData']['Id'];
                $SQL_word = (isset($_POST['word']))? $_POST['word'] : '未指定詞字';
                $SQL_word_name = (isset($_POST['wordname']))? $_POST['wordname'] : '未填入名稱';
                $SQL_word_content = $_POST['wordcontent'];
                $SQL_word_id = $_POST['wordId'];
                $SQL_Page = $_GET['PageId'];




                //錨定SQL定義
                $stmt = $pdo->prepare($ForSql);
                
                $stmt->bindParam(':user_id', $SQL_user_id);
                $stmt->bindParam(':word', $SQL_word);
                $stmt->bindParam(':word_name', $SQL_word_name);
                $stmt->bindParam(':word_content', $SQL_word_content);
                
                if(isset($_POST['edit'])){
                    $stmt->bindParam(':wordId', $SQL_word_id);
                }else{
                    $stmt->bindParam(':Page', $SQL_Page);
                }
                
                $stmt->execute();

                // 第二步：执行带有 CTE 的 DELETE 语句
                
                if(isset($_POST['edit'])){
                    $DeleteGroupSql = '
                        DELETE FROM word_group_bridge
                        WHERE (word_id, group_id) IN (
                            SELECT wgb.word_id, wgb.group_id
                            FROM word_group_bridge wgb
                            LEFT JOIN word_group g ON wgb.group_id = g.group_id
                            WHERE g.Type_page_id = :Page AND g.user_id = :user_id AND wgb.word_id = :wordId
                        )
                    ';

                    //錨定SQL定義
                    $stmt = $pdo->prepare($DeleteGroupSql);
                    $stmt->bindParam(':user_id', $SQL_user_id);
                    $stmt->bindParam(':Page', $SQL_Page);
                    $stmt->bindParam(':wordId', $SQL_word_id);
                    $stmt->execute();
                }
                
                
                if($_POST['group']){
                    $ForAddGroupSQL = 'INSERT INTO word_group_bridge(word_id,group_id)
                    VALUES ';
                    foreach ($_POST['group'] as $key => $value) { 
                        //對新增和編輯的編碼做出差異
                        $var = (isset($_POST['edit']))? $SQL_word_id : '@last_id_in_A_table';
                        //置入所有群組錨點
                        $ForAddGroupSQL .= ' ('.$var.','.$value.'),';
                    }
                    $ForAddGroupSQL = rtrim($ForAddGroupSQL,',') . ';';

                    // echo '<br><br>'.$ForAddGroupSQL.'<br><br>';
                    $stmt = $pdo->prepare($ForAddGroupSQL);
                    $stmt->execute();
                }

                
        }





            $stmt->closeCursor();
            $pdo->commit();
            
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "錯誤信息: " . $e->getMessage() . "<br>";
        echo '<br>';
        echo "錯誤代碼: " . $e->getCode() . "<br>";
    }   
    
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}







$ForSQL = 'SELECT * FROM word LEFT JOIN word_page_bridge ON  word.word_id = word_page_bridge.word_id ';
$ForSQL .= 'WHERE Type_page_id = ? AND user_id = ?';

$sql=$pdo->prepare($ForSQL);
$sql->execute([$_GET['PageId'] , $_SESSION['UserData']['Id']]);
$WordArr = [];$var=[];
foreach ($sql->fetchAll() as $row) {
    $var[]=$row['word_id'];
    $WordArr[$row['word_id']]['word'] = $row['word'];
    $WordArr[$row['word_id']]['word_name'] = $row['word_name'];
    $WordArr[$row['word_id']]['word_content'] = $row['word_content'];
}

//渲染群組資料
$ForSQL = '
    SELECT group_id,group_name FROM `word_group` 
    WHERE user_id = ? and Type_page_id = ?
    ORDER BY `Order` ASC;
';
$sql=$pdo->prepare($ForSQL);$sql->execute( [$_SESSION['UserData']['Id'] , $_GET['PageId']] );
foreach ($sql->fetchAll() as $row) {
    $GroupList[$row['group_id']] = $row['group_name'];
}
$GroupList['N'] = '未分組';

//渲染本頁群組和字詞的關聯性資料
$PrintGroupSQL = '';
foreach ($var as $key => $value) {$PrintGroupSQL.=$value.',';}
$PrintGroupSQL = '
    SELECT * 
    FROM word_group_bridge
    WHERE word_id IN('.rtrim($PrintGroupSQL,',').')
';
$sql=$pdo->prepare($PrintGroupSQL);$sql->execute();

foreach ($sql->fetchAll() as $row) {
    $WordArr[$row['word_id']]['word_group'][] = 'group_'.$row['group_id'];
}


function WriteGroupEdit($Arr){
    foreach ($Arr as $key => $value) {
        if($key=='N'){continue;}
        echo '<input type="checkbox" id="group_'.$key.'" name="group[]" value="'.$key.'"><label for="group_'.$key.'">'.$value.'</label>';
    }
}

function WriteGroupABox($GroupList){
    foreach ($GroupList as $key => $value) {
        echo '<a href="#!" onclick="ScrollToGroup(event, \'GP_'.$key.'\') ">'.$value.'</a>';
    }
}

//產生字詞合道內容

function RiverWrite($GroupList,$WordArr){

    function write_Word_Pane($Word_Id,$arr){
            echo '
                <div class="WordPane" word_id="'.$Word_Id.'" onclick="GetPane(this)">
                    <h3 class="Word">'.$arr['word'].'</h3>
                    <p class="Name">'.$arr['word_name'].'</p>
                </div>';
    }
    
    foreach ($GroupList as $Group_ID => $Group_Name) {
        echo '<div class="River_Title" id="GP_'.$Group_ID.'">'.$Group_Name.'</div>';
        foreach ($WordArr as $Word_Id => $value) {
            if($Group_ID!=='N'){
                if (!isset($value['word_group'])){continue;}
                if (!in_array('group_'.$Group_ID, $value['word_group'])){continue;}
                write_Word_Pane($Word_Id,$value);echo '<br><br>';
            }else{
                if (isset($value['word_group'])){continue;}
                write_Word_Pane($Word_Id,$value);echo '<br><br>';
            }
        
        }
    }
    // print_r($GroupList);
}

//將資料陣列轉為js陣列qq
$json_array = json_encode($WordArr);





?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="../bootstrap/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../css/WordRiver.css">
    <script>
    // 在 JavaScript 中使用 JSON 字串
    var js_array = <?php echo $json_array; ?>;
    console.log(js_array);
    </script>
    <style>:root {--EditBoxHeight: var(--EditBox_S);}
    </style>
    
</head>
<body>
    <div style="width:100%">
    <div id="TopBar">
        <div>
            <div id='TopBarMainMenu'>
                <a href="../Edit_Group.php<?php echo "/?PageId=".$_GET['PageId']?>"><div>編輯群組</div></a>
                <a class="" data-bs-toggle="collapse" href="#GroupABox" role="button" aria-expanded="false" aria-controls="GroupABox">
                    跳轉群組
                </a>
            </div>
            <a href="../choose_Page.php"><div>回選頁</div></a>
        </div>
        <div class="collapse" id="GroupABox">
            <?php WriteGroupABox($GroupList); ?>
        </div>
    </div>
    <div id="TopBarSpace"></div>
    <div id="WordRiver">
    <?php RiverWrite($GroupList,$WordArr);?>
    </div>
    <div id="FormBox">
        <div id="SizeBox">
            <div onclick="ZoomEditBox('N')">N</div>
            <div onclick="ZoomEditBox('S')">S</div>
            <div onclick="ZoomEditBox('M')">M</div>
            <div onclick="ZoomEditBox('L')">L</div>
        </div>
        <form id="EditUse" action="" method="post">
            <div>
                <div id="EditWordContent">
                    <div style="display:none;">
                        <input id="Input_Word_Id" type="text" name="wordId">
                        <input id="OnEditGroup" type="checkbox" name="OnEditGroup" value="true">
                    </div>
                    <div>
                        <label for="Input_word">詞句：</label>
                        <input id="Input_word" type="text" name="word">
                    </div>
                    <div>
                        <label for="Input_wordname">簡述：</label>
                        <input id="Input_wordname" type="text" name="wordname">
                    </div>
                    <div class="CheckBigbox">
                            <input name="edit" class="EditOrDelete" onclick="EditOrDelete(this)" id="Input_wordNotForNew" type="checkbox">
                            <label for="Input_wordNotForNew">編輯</label>

                            <input name="delete" class="EditOrDelete" onclick="EditOrDelete(this)" id="Input_wordDelete" type="checkbox">
                            <label for="Input_wordDelete">刪除</label>
                    </div>
                    <textarea id="Input_wordcontent" name="wordcontent"></textarea>
                </div>
                <div id="GroupEditBox"><?php WriteGroupEdit($GroupList)?></div>
            </div>
            <div>
                <input type="submit">
            </div>
        </form>
    </div>
</body>

<script>
    //前往指定群組
    function ScrollToGroup(event, sectionId) {
        event.preventDefault(); // 防止默認行為
        const section = document.getElementById(sectionId);
        const ScrollTo = section.getBoundingClientRect().top + window.pageYOffset - 80;
        document.getElementById('WordRiver').scrollTo({ 
            top: ScrollTo,
            behavior: 'smooth' 
        });
    }

    //縮放編輯框
    function ZoomEditBox(Size){
        document.documentElement.style.setProperty('--EditBoxHeight', 'var(--EditBox_'+Size+')');
    }

    //確認框單選
    function EditOrDelete(Check){
        if(Check.checked == false){
            Check.checked = false;
        }else{
            document.querySelectorAll('.EditOrDelete').forEach(element => {
                element.checked = false;
            })
            Check.checked = true
        }
    }

    document.getElementById('GroupEditBox').querySelectorAll('label').forEach(checkbox => {
        checkbox.addEventListener('click', () => {
            document.getElementById('OnEditGroup').checked = true;
        });
    })

    //抓取資料
    function GetPane(Pos){

        document.getElementById('EditUse').reset();


        let WordId = Pos.getAttribute('word_id');
        let WordArr = js_array[WordId];
        document.getElementById('Input_word').value = WordArr['word'];
        document.getElementById('Input_Word_Id').value = WordId;
        document.getElementById('Input_wordname').value = WordArr['word_name'];
        document.getElementById('Input_wordcontent').value = WordArr['word_content'];

        document.getElementById('Input_wordNotForNew').checked = true;

        let GroupItem = document.getElementById('GroupEditBox').querySelectorAll('input');

        GroupItem.forEach(value => {
            value.checked = false;
        })

        console.log(WordArr['word_group'])
        WordArr['word_group'].forEach(value => {
            console.log(value)
            document.getElementById(value).checked = true;
        });

    }
</script>
</html>