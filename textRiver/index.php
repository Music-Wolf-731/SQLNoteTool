<?php 
session_start();
require '../display.php';OnCheckSignIn();

$pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    print_r($_POST);
    
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
            if($_POST['word']==''){header("Location: ".$_SERVER['HTTP_REFERER']);exit();}
            //編輯或是新增字詞
            
            if(!isset($_POST['edit'])){
                //新增一個項目並且將wordId調出
                $ForSql = 'INSERT INTO word (user_id) VALUES ('.$_SESSION['UserData']['Id'].')';
                $stmt = $pdo->prepare($ForSql);$stmt->execute();
                $_POST['wordId']=$pdo->lastInsertId();
                $ForSql = 'INSERT INTO word_page_bridge (word_id, Type_page_id) VALUES ('.$pdo->lastInsertId().', '.$_GET['PageId'].');';
                $stmt = $pdo->prepare($ForSql);$stmt->execute();
            }
            
            

                $ForSql = '
                    UPDATE word 
                    SET
                    word = :word,
                    word_name = :word_name,
                    word_content = :word_content 
                    WHERE user_id = :user_id AND word_id = :wordId;
                    ';



                //置入SQL定義
                $SQL_user_id = $_SESSION['UserData']['Id'];
                $SQL_word = (isset($_POST['word']))? $_POST['word'] : '未指定詞字';
                $SQL_word_name = (isset($_POST['wordname']))? $_POST['wordname'] : '未填入名稱';
                $SQL_word_content = $_POST['wordcontent'];
                $SQL_word_id = $_POST['wordId'];
                $SQL_Page = $_GET['PageId'];


                echo $SQL_word_id;

                //錨定SQL定義
                $stmt = $pdo->prepare($ForSql);
                
                $stmt->bindParam(':user_id', $SQL_user_id);
                $stmt->bindParam(':word', $SQL_word);
                $stmt->bindParam(':word_name', $SQL_word_name);
                $stmt->bindParam(':word_content', $SQL_word_content);
                $stmt->bindParam(':wordId', $SQL_word_id);
                
                
                $stmt->execute();
                //帶有CTE的查詢                
                if(isset($_POST['OnEditGroup'])){


                    // 創建一個臨時表並選擇需要刪除的 ID
                    $CreateTempTableSql = '
                        CREATE TEMPORARY TABLE temp_word_group_ids AS
                        SELECT wgb.word_id, wgb.group_id
                        FROM word_group_bridge wgb
                        LEFT JOIN word_group g ON wgb.group_id = g.group_id
                        WHERE g.Type_page_id = :Page AND g.user_id = :user_id AND wgb.word_id = :wordId
                    ';
                    $stmt = $pdo->prepare($CreateTempTableSql);
                    $stmt->bindParam(':user_id', $SQL_user_id);
                    $stmt->bindParam(':Page', $SQL_Page);
                    $stmt->bindParam(':wordId', $SQL_word_id);
                    $stmt->execute();

                    // 使用臨時表進行刪除操作
                    $DeleteGroupSql = '
                        DELETE FROM word_group_bridge
                        WHERE (word_id, group_id) IN (
                            SELECT word_id, group_id FROM temp_word_group_ids
                        )
                    ';
                    $stmt = $pdo->prepare($DeleteGroupSql);
                    $stmt->execute();


                    if(isset($_POST['group'])){
                        $ForAddGroupSQL = 'INSERT INTO word_group_bridge(word_id,group_id)
                        VALUES ';
                        foreach ($_POST['group'] as $key => $value) { 
                            $ForAddGroupSQL .= ' ('.$_POST['wordId'].','.$value.'),';
                        }
                        $ForAddGroupSQL = rtrim($ForAddGroupSQL,',') . ';';

                        
                        $stmt = $pdo->prepare($ForAddGroupSQL);
                        $stmt->execute();
                    }
                }
                if(isset($_POST['OnEditPage'])){
                    $DeleteGroupSql = '
                        DELETE FROM word_page_bridge
                        WHERE word_id = :wordId;
                    ';

                    //錨定SQL定義
                    $stmt = $pdo->prepare($DeleteGroupSql);
                    $stmt->bindParam(':wordId', $SQL_word_id);
                    $stmt->execute();

                    $ForAddPageSQL = '
                        INSERT INTO word_page_bridge (Type_page_id,word_id)
                        value ('.$SQL_Page.','.$SQL_word_id.')
                    ';
                
                    if(isset($_POST['page'])){
                        foreach ($_POST['page'] as $key => $value) { 
                            $ForAddPageSQL .= ',('.$value.','.$_POST['wordId'].')';
                        }
                    }
                    $stmt = $pdo->prepare($ForAddPageSQL);
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
    
    // header("Location: ".$_SERVER['HTTP_REFERER']);
    // exit();
}
$ForSQL = 'SELECT * FROM word LEFT JOIN word_page_bridge ON  word.word_id = word_page_bridge.word_id ';
$ForSQL .= 'WHERE Type_page_id = ? AND user_id = ?';

$sql=$pdo->prepare($ForSQL);
$sql->execute([$_GET['PageId'] , $_SESSION['UserData']['Id']]);

//渲染文字陣列及基本資料置入
$WordArr = [];$OnlyWordId=[];
foreach ($sql->fetchAll() as $row) {
    $OnlyWordId[]=$row['word_id'];
    $WordArr[$row['word_id']]['word'] = $row['word'];
    $WordArr[$row['word_id']]['word_name'] = $row['word_name'];
    $WordArr[$row['word_id']]['word_content'] = $row['word_content'];

    
    // 將內容進行 HTML 轉譯
    $WordArr[$row['word_id']]['word_content_HTML'] = nl2br(htmlspecialchars($row['word_content'], ENT_QUOTES, 'UTF-8'));
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
foreach ($OnlyWordId as $key => $value) {$PrintGroupSQL.=$value.',';}
$var='SELECT group_id FROM `word_group` WHERE Type_page_id = '.$_GET['PageId'].' AND user_id = '.$_SESSION['UserData']['Id'];

$PrintGroupSQL = '
    SELECT * 
    FROM word_group_bridge
    WHERE word_id IN('.rtrim($PrintGroupSQL,',').') AND group_id IN ('.$var.')
';
if(count($OnlyWordId)!==0){$sql=$pdo->prepare($PrintGroupSQL);$sql->execute();}
foreach ($sql->fetchAll() as $row) {
    $WordArr[$row['word_id']]['word_group'][] = 'group_'.$row['group_id'];
}


//渲染全頁數資料
$PrintPage_SQL = 'SELECT Type_page_id,page_name FROM `type_page` WHERE user_id = '.$_SESSION['UserData']['Id'] ;
$sql=$pdo->prepare($PrintPage_SQL);$sql->execute();
foreach ($sql->fetchAll() as $row) {
    if($row['Type_page_id']==$_GET['PageId']){$PageName = $row['page_name'] . ' | 哈勒筆記';continue;}
    $OnlyPage[$row['Type_page_id']] = $row['page_name'];
}
if(!isset($OnlyPage)){$OnlyPage=[];}

//取得各字詞是否存在於其它頁的資料
$SeachOtherPage = '';
foreach ($WordArr as $key => $value) {
    $SeachOtherPage .= $key . ',';
}
$SeachOtherPage = 'SELECT * FROM word_page_bridge WHERE word_id IN ('.rtrim($SeachOtherPage,',').') AND Type_page_id != '.$_GET['PageId'];
if(count($OnlyWordId)!==0){$sql=$pdo->prepare($SeachOtherPage);$sql->execute();}
foreach ($sql->fetchAll() as $row) {
    $WordArr[$row['word_id']]['InOtherPage'][] = 'page_'.$row['Type_page_id'];
}



function WriteGroupEdit($Arr){
    foreach ($Arr as $key => $value) {
        if($key=='N'){continue;}
        $var='<input type="checkbox" id="group_'.$key.'" name="group[]" value="'.$key.'"><label for="group_'.$key.'">'.$value.'</label>';
        $ForReturn=(isset($ForReturn))? $ForReturn.$var:$var;
    }
    $ForReturn=(!isset($ForReturn))?'<h3>尚未建立群組，可點擊上方的編輯群組進行調整</h3>':$ForReturn;
    echo $ForReturn;
}

function WriteGroupABox($GroupList){
    $ForReturn = '';
    foreach ($GroupList as $key => $value) {
        $ForReturn .= '<a href="#!" onclick="ScrollToGroup(event, \'GP_'.$key.'\') "><p>'.$value.'</p></a>';
    }
    return $ForReturn;
}

//產生字詞河道內容

function RiverWrite($GroupList,$WordArr){

    function write_Word_Pane($Word_Id,$arr){
            echo '
                <div class="WordPane" word_id="'.$Word_Id.'" onclick="GetPane(this)">
                    <h3 class="Word">'.$arr['word'].'</h3>
                    <p class="Name">'.$arr['word_name'].'</p>
                </div>';
    }
    
    foreach ($GroupList as $Group_ID => $Group_Name) {
        echo '<div class="River_Title" id="GP_'.$Group_ID.'"><p>'.$Group_Name.'</p><div></div></div>';
        foreach ($WordArr as $Word_Id => $value) {
            if($Group_ID!=='N'){
                if (!isset($value['word_group'])){continue;}
                if (!in_array('group_'.$Group_ID, $value['word_group'])){continue;}
                write_Word_Pane($Word_Id,$value);
            }else{
                if (isset($value['word_group'])){continue;}
                write_Word_Pane($Word_Id,$value);
            }
        
        }
    }
    // print_r($GroupList);
}

function PrintPageEdit($OnlyPage){
    foreach ($OnlyPage as $key => $value) {
        $var='<input type="checkbox" id="page_'.$key.'" name="page[]" value="'.$key.'"><label for="page_'.$key.'">'.$value.'</label>';
        $ForReturn =(isset($ForReturn))? $ForReturn.$var:$var;
    }
    $ForReturn = (!isset($ForReturn))?'<h3>當您建立其它可關聯字詞頁時即會顯示於此</h3>':$ForReturn;  
    echo $ForReturn;
}

//將資料陣列轉為js陣列qq
$json_array = json_encode($WordArr);



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php PrintHead($PageName)?>
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
        <?php PrintTopBar('WordRiver',$GroupList)?>
    <div id="WordRiver">




    <?php RiverWrite($GroupList,$WordArr);?>
    <div style="width:100%;height:3em;"></div>
    </div>
    <div id="FormBox">
        <div id="Switch">
            <div id="PageBox">
                <div onclick="" class="active"><p>展示</p></div>
                <div onclick=""><p>編輯文字</p></div>
                <div onclick=""><p>群組</p></div>
                <div onclick=""><p>跨頁</p></div>
            </div>
            <div id="SizeBox">
                <div onclick="ZoomEditBox('N',this)"><p>隱藏</p></div>
                <div onclick="ZoomEditBox('S',this)" class="active"><p>小</p></div>
                <div onclick="ZoomEditBox('M',this)"><p>中</p></div>
                <div onclick="ZoomEditBox('L',this)"><p>大</p></div>
            </div>
        </div>
        <form id="EditUse" action="" method="post">
            <div id="PageList">
                <div id="ViewContent"><div></div></div>
                <div id="EditWordContent" class="hidden">
                    <div style="display:none;">
                        <input id="Input_Word_Id" type="text" name="wordId">
                        <input id="OnEditGroup" type="checkbox" name="OnEditGroup" value="true">
                        <input id="OnEditPage" type="checkbox" name="OnEditPage" value="true">
                    </div>
                    <div class="inputflex">
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

                                <div onclick="PrintInTextarea('copy')">加入複製框</div>
                        </div>
                    </div>
                    <textarea id="Input_wordcontent" name="wordcontent" placeholder="字詞描述或內容，填入送出後在點擊詞卡時將會顯示在'展示'的頁籤中"></textarea>
                </div>
                <div id="GroupEditBox" class="FlexBox hidden"><?php WriteGroupEdit($GroupList)?></div>
                <div id="ThrowOtherPage" class="FlexBox hidden"><?php PrintPageEdit($OnlyPage) ?></div>
            </div>
            <div>
                <input type="reset">
                <input type="submit">
            </div>
        </form>
    </div>
</body>

<script>
    //選擇字詞框
    document.querySelectorAll('#WordRiver > .WordPane').forEach(event => {
        event.addEventListener('click', () => {
            let Var = document.querySelector('#WordRiver > .active')
            if(Var){Var.classList.remove('active')}
            // console.log(document.querySelector('#WordRiver > .active'))
            event.classList.add('active')
        })
    })
    //前往指定群組
    function ScrollToGroup(event, sectionId) {
        event.preventDefault(); // 防止默認行為
        let RiverBox = document.querySelector('#WordRiver > div').getBoundingClientRect().top;
        let Group = document.getElementById(sectionId).getBoundingClientRect().top;
        console.log(RiverBox+ '奇' +Group)
        const ScrollTo = Group - RiverBox - 30;
        document.getElementById('WordRiver').scrollTo({ 
            top: ScrollTo,
            behavior: 'smooth' 
        });
    }
    //切換頁面
    document.addEventListener('DOMContentLoaded', () => {
        const rowBookmarkDivs = document.querySelectorAll('#PageBox > div');
        const bookmarkDivs = document.querySelectorAll('#PageList > div');

        rowBookmarkDivs.forEach((div, index) => {
            div.addEventListener('click', () => {

                bookmarkDivs.forEach(value => {
                    if (!value.classList.contains('hidden')){
                        value.classList.add('hidden')
                    }

                    rowBookmarkDivs.forEach(Event => {
                        if (Event.classList.contains('active')){
                            Event.classList.remove('active')
                        }
                        div.classList.add('active')
                    })
                })
                bookmarkDivs[index].classList.remove('hidden')
                updateWordContentEdit()
            });
        });
    });

    //縮放編輯框
    function ZoomEditBox(Size,ThisCheck){
        document.getElementById('SizeBox').querySelectorAll('div').forEach( value => {
            //確認對應項目的class列表是否有active，有則刪除
            if (value.classList.contains('active')){
                value.classList.remove('active')
            }
        })
        //在受點擊項目class中加入active
        ThisCheck.classList.add('active')
        //修改root:中的--EditBoxHeight
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

    document.getElementById('ThrowOtherPage').querySelectorAll('label').forEach(checkbox => {
        checkbox.addEventListener('click', () => {
            document.getElementById('OnEditPage').checked = true;
        });
    })

    //抓取資料
    var CopyArr=[];
    function GetPane(Pos){

        document.getElementById('EditUse').reset();


        let WordId = Pos.getAttribute('word_id');
        let WordArr = js_array[WordId];
        document.getElementById('Input_word').value = WordArr['word'];
        document.getElementById('Input_Word_Id').value = WordId;
        document.getElementById('Input_wordname').value = WordArr['word_name'];
        document.getElementById('Input_wordcontent').value = WordArr['word_content'];


        //置入瀏覽頁       
        let PrintContentArr = WordArr['word_content_HTML'].split("|:|"); let PrintContent = '';
        CopyArr = WordArr['word_content'].split("|:|"); let PrintContent_copy = '';
        PrintContentArr.forEach((value,key) => {
            switch (value) {
                case 'Copy_S':
                key++;
                PrintContent += '<span class="ClickCopy" onclick="CopyText('+key+')" forcopy="'+key+'">';
                    break;
                case 'Copy_E':
                PrintContent += '</span>';
                    break;
            
                default:
                let regex = /^(<br \/>\s*)+|(\s*<br \/>)+$|^\s+|\s+$/g;
                let Print = value.replace(regex, '');
                PrintContent += Print.trim();
                    break;
            }
        })
            console.log(CopyArr)
            console.log(PrintContentArr)
        document.querySelector('#ViewContent div').innerHTML = '<p>'+PrintContent+'</p>';

        
        document.getElementById('Input_wordNotForNew').checked = true;

        let GroupItem = document.getElementById('GroupEditBox').querySelectorAll('input');

        GroupItem.forEach(value => {
            value.checked = false;
        })
        if(typeof WordArr['word_group'] !== 'undefined'){
            WordArr['word_group'].forEach(value => {
                console.log(value)
                document.getElementById(value).checked = true;
            });
        }
        if(typeof WordArr['InOtherPage'] !== 'undefined'){
            WordArr['InOtherPage'].forEach(value => {
                console.log(value)
                document.getElementById(value).checked = true;
            });
        }
    }
    //文字框內加入特殊文字
    function PrintInTextarea(type){
        let Print ;
        switch (type) {
            case 'copy':
            Print  = '\n|:|Copy_S|:|\n在這裡寫入點擊複製框的內文\n|:|Copy_E|:|';
                break;
        
            default:
                break;
        }
        document.getElementById('Input_wordcontent').value += Print;
    }

    //抓取複製
    function CopyText(value){
        navigator.clipboard.writeText(CopyArr[value].trim())
    }

    //抓取內容框大小並調整渲染內容
    function updateWordContentEdit() {
        // 获取两个元素
        const editWordContent = document.getElementById('EditWordContent');
        const inputWordContent = document.getElementById('Input_wordcontent');

        // 计算两个元素的顶部之间的距离
        const editWordContentTop = editWordContent.getBoundingClientRect().top;
        const inputWordContentTop = inputWordContent.getBoundingClientRect().top;
        const distance = inputWordContentTop - editWordContentTop;

        // 将距离设置为 CSS 变量
        document.documentElement.style.setProperty('--WordcontentEdit', `${distance}px`);
    }

    // 在页面加载时和窗口尺寸改变时调用 updateWordContentEdit 函数
    window.addEventListener('load', updateWordContentEdit);
    window.addEventListener('resize', updateWordContentEdit);
</script>
</html>