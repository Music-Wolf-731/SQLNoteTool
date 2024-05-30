<?php 
require 'display.php';SessionSet();OnCheckSignIn();
$_SESSION['UserData']['Id'];

$pdo=new PDO('mysql:host=localhost;dbname=notetool;charset=utf8','NoteToolController', 'ToolMaker');

if ($_SERVER["REQUEST_METHOD"] == "POST"){
  switch ($_POST['FormType']) {
    case 'Order':
      function ChangeOrder($pdo,$G_id,$Od){
        $ForSQL = '
          UPDATE word_group 
          SET `Order` = ?
          WHERE group_id = ? AND user_id = ?
        ';
        $sql=$pdo->prepare($ForSQL);
        $sql->execute([$Od,$G_id,$_SESSION['UserData']['Id']]);
      }
      foreach ($_POST['Order'] as $key => $value) {
        ChangeOrder($pdo,$value,$key+1);
      }
      break;



    case 'Add':
        $ForSQL = '
          INSERT INTO word_group(Type_page_id,group_name,user_id)
          VALUES (?,?,?);
        ';

        $sql=$pdo->prepare($ForSQL);

        $sql->execute([$_GET['PageId'],$_POST['GroupName'],$_SESSION['UserData']['Id']]);
        
      break;


      
    case 'Delete':
      
        $ForSQL = '
          DELETE FROM word_group
          WHERE group_id = ? AND user_id = ?; 
        ';

        $sql=$pdo->prepare($ForSQL);
        $sql->execute([$_POST['GroupId'],$_SESSION['UserData']['Id']]);
    
      break;
    case 'EditName':
      
        $ForSQL = '
          UPDATE word_group
          SET group_name = ?
          WHERE group_id = ? AND user_id = ?; 
        ';

        // print_r($_POST);
        $sql=$pdo->prepare($ForSQL);
        $sql->execute([$_POST['GroupName'],$_POST['GroupId'],$_SESSION['UserData']['Id']]);
        
      break;
    default:
      # code...
      break;
  }
    
}


function WriteGroupList($pdo,$Type){
  $user_id = $word = '';
  $ForReturn = '';
  $ForSQL = '
    SELECT group_id,group_name 
    FROM word_group 
    WHERE user_id = :userId AND Type_page_id = :PageId
    ORDER BY `Order` ASC
    ';
  
  $stmt=$pdo->prepare($ForSQL);
  
  $stmt->bindParam(':userId', $user_id);
  $stmt->bindParam(':PageId', $word);

  $user_id=$_SESSION['UserData']['Id'];
  $word=$_GET['PageId'];
  
  $stmt->execute();
  switch ($Type) {
    case 'Order':
      foreach ($stmt->fetchAll() as $row) {
        $ForReturn .= '
        <div class="ui-state-default">
          <input name="Order[]" type="text" value="'.$row['group_id'].'">
          '.$row['group_name'].'
        </div>
        ';
      }
      $ForReturn =($ForReturn == '')? '<div class="hint" style="width:100%;text-align: center;">尚無可進行排序之群組，需先進行新增</div>':'<div id="sortable">'.$ForReturn.'</div>';
      
      break;
    
    case 'option':
      foreach ($stmt->fetchAll() as $row) {
        $ForReturn .= '
        <option value="'.$row['group_id'].'">'.$row['group_name'].'</option>
        ';
      }
      $ForReturn = ($ForReturn=='')?'<option disabled">需先新增群組</option>':$ForReturn;
    default:
      # code...
      break;
  }
  return $ForReturn;
}

?>


<!DOCTYPE html><html lang="en"><head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php PrintHead('群組編輯 | 哈勒筆記')?>
  <link rel="stylesheet" href="../css/Edit_Group.css">
  <style>
    #sortable input{display: none;}
  ul { list-style-type: none; margin: 0; padding: 0; margin-bottom: 10px; }
  li { margin: 5px; padding: 5px; width: 150px; }
  </style>
  <script>
  $( function() {
    $( "#sortable" ).sortable({
      revert: true
    });
    $( "#draggable" ).draggable({
      connectToSortable: "#sortable",
      helper: "clone",
      revert: "invalid"
    });
    $( "ul, li" ).disableSelection();
  } );
  </script>
</head>
<body>
        <?php PrintTopBar('EditWordGroup','')?>
 
<ul>
</ul>
 


<div id='row_bookmark'>
  <div><p>順位</p></div>
  <div><p>新增</p></div>
  <div><p>刪除</p></div>
  <div><p>更名</p></div>
</div>

<div id='bookmark'>
  <div class="active">
    <form action="" method="post">
      <input type="text" style="display: none;" name="FormType" value="Order">
      
        <?php echo WriteGroupList($pdo,'Order') ?>
        <input class="output" type="submit"><h5>*以拖曳的方式進行排序</h5>
    </form>
  </div>
  <div>
    <form action="" method="post">
      <input type="text" style="display: none;" name="FormType" value="Add">
      <input type="text" name="GroupName" placeholder="填入新群組名稱" autocomplete="off">
      <input class="output" type="submit" value="新增">
    </form>
  </div>
  <div>
    <form action="" method="post">
      <input type="text" style="display: none;" name="FormType" value="Delete">
      <div>
        <select type="text" name="GroupId">
          <?php echo WriteGroupList($pdo,'option') ?>
        </select>
        
      </div>
      <input class="output" type="submit" value="刪除">
    </form>
  </div>
  <div>
    <form action="" method="post">
      <input type="text" style="display: none;" name="FormType" value="EditName">
      <div>
        <select type="text" name="GroupId">
          <?php echo WriteGroupList($pdo,'option') ?>
        </select>
      <input type="text" name="GroupName" placeholder="填入更正後的名稱" autocomplete="off">
        
      </div>
      <input class="output" type="submit" value="更名">
    </form>
  </div>
</div>

</body>
<script>
        document.addEventListener('DOMContentLoaded', () => {
            const rowBookmarkDivs = document.querySelectorAll('#row_bookmark > div');
            const bookmarkDivs = document.querySelectorAll('#bookmark > div');

            rowBookmarkDivs.forEach((div, index) => {
                div.addEventListener('click', () => {
                    bookmarkDivs.forEach(bookmarkDiv => {
                        bookmarkDiv.classList.remove('active');
                    });
                    bookmarkDivs[index].classList.add('active');
                });
            });
        });
    </script>

</html>