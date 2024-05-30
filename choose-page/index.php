<?php
    require '../display.php';SessionSet();OnCheckSignIn();


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

        if($_POST['Type']=='Delete'){
            $ForSQL = 'SELECT word_id FROM word_page_bridge WHERE Type_page_id != ?';
            $sql=$pdo->prepare($ForSQL);$sql->execute([$_POST['PageId']]);
            $OtherPageWord = '';
            foreach ($sql->fetchAll() as $row) {$OtherPageWord .= $row['word_id'].',';}
            $OtherPageWord = rtrim($OtherPageWord, ",");
            $ForSQL = 'SELECT word_id FROM word_page_bridge WHERE Type_page_id = ? AND word_id NOT IN('.$OtherPageWord.')';
            $sql=$pdo->prepare($ForSQL);$sql->execute([$_POST['PageId']]);
            $WordJustInThisPage = '';
            foreach ($sql->fetchAll() as $row) {$WordJustInThisPage .= $row['word_id'].',';}
            $WordJustInThisPage = rtrim($WordJustInThisPage, ",");
            // echo $WordJustInThisPage;

            $ForSQL = 'DELETE FROM type_page WHERE Type_page_id = ?;';
            $sql=$pdo->prepare($ForSQL);$sql->execute([$_POST['PageId']]);
            $ForSQL = 'DELETE FROM word WHERE word_id IN ('.$WordJustInThisPage.');';
            if($WordJustInThisPage !== ''){$sql=$pdo->prepare($ForSQL);$sql->execute();}
            
            
        }

        
        
        // 清除 POST 記錄
        $_POST = null;

        // 重定向到當前頁面
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }


    
    //先列出SQL的指令，內部的?則為事後填入的變數
    $sql=$pdo->prepare('select * from type_page where user_id=?');


    //填入?並激活SQL指令
    $sql->execute([$_SESSION['UserData']['Id']]);
    $PageListArr=[];
	foreach ($sql->fetchAll() as $row) {
        $PageListArr[$row['Type_page_id']]['Name']=$row['page_name'];
        $PageListArr[$row['Type_page_id']]['content']=$row['page_content'];
    }



    $json_array = json_encode($PageListArr);




    function WritePagePain($arr){
        $ForReturn = '';
        foreach ($arr as $key => $value) {
            $Pane = '<div class="title"><h3>'.$value['Name'] . '</h3></div>';
            // $Pane .= '<div class="content">'.$value['content'] . '</div>';
            $Pane = '<div class="PaneIcon" data-bs-toggle="modal" data-bs-target="#CopyPage" PageId="'.$key.'"></div><a href="../textRiver/?PageId='.$key.'">'. $Pane . '</a>';
            $Pane = '<div class="Page_Pain">'. $Pane . '</div>';
            $ForReturn .= $Pane;
        }

        $ForReturn=($ForReturn == '')?'<div class="hint">您目前尚無任何頁目<br>請點擊下方下拉式選單選擇新增<br>於小的填入格填入頁目名稱<br>大窗格填入頁目描述</div>':$ForReturn;

        echo $ForReturn;
    }




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php PrintHead('登入哈勒筆記') ?>
    <link rel="stylesheet" href="../css/choosePage.css">
    <script>var PageListArr = <?php echo $json_array?></script>
</head>
<body>
    <?php PrintTopBar('ChoosePage','');?>
    <div class="hint">本頁尚未進行版型修繕，但功能狀態正常請安心使用<br>下方下拉式選單可以進行新增或是修改現有頁目名稱和描述，小框格為名稱，大框格為描述</div>
    <div id='PageList'>
    <?php
    
    WritePagePain($PageListArr);
    ?>
    </div>

    <div id="EditBox">
        <div id="ButtomMark" data-bs-toggle="collapse" href="#ButtomFormBox" role="button" aria-expanded="false" aria-controls="ButtomFormBox">
            <p>編輯窗</p>
        </div>
        <div id="ButtomFormBox" class="collapse">
            <form action="" method="post">
                <input type="text" name="Cho_Page" class='D_None Edit_PageId'>
                <div id="FormTop">
                    <h3 class="PageName">新增頁面</h3>
                    <div>
                        <input id="Form_Public" type="checkbox" name="Public" value="true">
                        <label for="Form_Public">公開(未實裝)</label>
                    </div>
                </div>
                <h3>名稱</h3>
                <input type="text" name="Page_Name" placeholder="頁目名稱"><hr>
                <h3>描述</h3>
                <textarea name="PageContent" id="" placeholder='本填入框將用於描述頁目'></textarea><hr>
                <input type="submit" value="提交">  
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="CopyPage" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="Special_Check modal-content">
                <div class="modal-header">
                    <h1 id="FloatTitle" class="modal-title fs-5">這是名稱</h1>
                    <!-- <button type="button" class="ScrollOutBut" ScrollOut="checkIfCopy" InType="Copy">複製</button> -->
                    <button type="button" class="ScrollOutBut" ScrollOut="checkIfDel" InType="Delete">刪除</button>
                    <button type="button" id="FlowEditBut" data-bs-toggle="modal" data-bs-target="#CopyPage" >編輯</button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="FloatContent" class="modal-body">
                    這是內文
                </div>
                <form action="" method="post" >
                    <div id="CheckBox">
                            <div><input class="InputType" type="text" name="Type"><input class="InputPageId" type="text" name="PageId"></div>
                            <div id="checkIfDel"><p>將會刪除本頁以及本頁獨有的字詞。<br>確定刪除嗎？此操作將不可逆。</p><input type="submit" value="確定"></div>
                            <div id="checkIfCopy"><p>註：字詞編號不受頁目的影響，若想複製字詞請於頁內操作。</p><input type="submit" value="確定"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.ScrollOutBut').forEach(value => {
            value.addEventListener('click', function(event) {
                document.querySelectorAll('#CheckBox div').forEach(ScrollIn => {ScrollIn.classList.remove('active')})
                const ScrollType = event.target.getAttribute('ScrollOut');
                document.getElementById(ScrollType).classList.add('active')

                document.querySelector('#CheckBox .InputType').value = event.target.getAttribute('InType');
            })
        })


        var ScrollOutBut = document.querySelectorAll('.ScrollOutBut');
        
        document.addEventListener('click', function(event) {

            var isScrollOutBut = Array.from(ScrollOutBut).some(function(element) {
                return element.contains(event.target);
            });

            if (!document.getElementById('checkIfDel').contains(event.target) && !isScrollOutBut){
                document.getElementById('checkIfDel').classList.remove('active');
            }
            if (!document.getElementById('checkIfCopy').contains(event.target) && !isScrollOutBut){
                document.getElementById('checkIfCopy').classList.remove('active');
            }
        });

        //
        document.getElementById('ButtomMark').addEventListener('click' , function(event){
                document.querySelector('#FormTop .PageName').innerHTML = '新增頁目';
            document.querySelector('.Edit_PageId').value = 'NewType';
        })


        //點擊edit時將替換描述窗內的資料
        document.querySelectorAll('.PaneIcon').forEach(value => {
            value.addEventListener('click', function(event){
                console.log(PageListArr)
                const PageId = event.target.getAttribute('PageId');

                //替換描述框
                document.getElementById('FloatTitle').innerHTML = PageListArr[PageId]['Name'];
                document.getElementById('FloatContent').innerHTML = PageListArr[PageId]['content'];
                document.querySelector('#CheckBox .InputPageId').value = PageId;

                //替換編輯窗
                document.querySelector('#FormTop .PageName').innerHTML = '編輯：'+ PageListArr[PageId]['Name'];
                document.querySelector('.Edit_PageId').value = PageId;
            })
            
        })



        //點擊edit時將編輯窗收起
        document.addEventListener('DOMContentLoaded', function() {
            // 获取需要隐藏的 Collapse 元件
            const collapseElement = document.getElementById('ButtomFormBox');
            // 创建 Collapse 实例
            const collapseInstance = new bootstrap.Collapse(collapseElement, { toggle: false });

            // 为所有 PaneIcon 元素添加点击事件监听器
            document.querySelectorAll('.PaneIcon').forEach(function(element) {
                element.addEventListener('click', function() {
                    // 调用 Bootstrap Collapse 实例的 hide() 方法隐藏元素
                    collapseInstance.hide();
                });
            });
        });
        
        //點擊edit展開編輯窗
        document.getElementById('FlowEditBut').addEventListener('click',function(){
            const collapseElementList = document.querySelectorAll('.collapse');
            const collapseList = [...collapseElementList].map(collapseEl => new bootstrap.Collapse(collapseEl));
        })
    </script>
</body>
</html>

