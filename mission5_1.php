<?php

    // DB接続設定
	$dsn = 'データベース名';
	$user = 'ユーザー名';
	$password = 'パスワード';
	$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
	
	//テーブルの作成
	$sql = 'CREATE TABLE IF NOT EXISTS tbpost(
	    id INT AUTO_INCREMENT PRIMARY KEY,
	    name TEXT,
	    comment TEXT,
	    time TEXT,
	    password TEXT)';
	$stmt = $pdo->query($sql);
    
    session_start();
    
        $name = $_POST["name"];
        $comment = $_POST["comment"];
        $time = date("Y/m/d H:i:s");
        $commentPassWord = $_POST["commentPassWord"];
        $editionid = $_POST["editionid"];
        
        $deleteid = $_POST["deleteid"];
        $deletePassWord = $_POST["deletePassWord"];
        
        $editid = $_POST["editid"];
        $editPassWord = $_POST["editPassWord"];
        
    //送信ボタンが押された？
        if(isset($_POST["submit"])){
            //名前、コメント、編集番号のいずれかに入力がある？
            if(empty($name) && empty($comment) && empty($editionid)){
            
            }
            //トークン番号は一致？
            elseif(isset($_POST["token"]) && isset($_SESSION["token"]) && ($_POST["token"] == $_SESSION["token"])){
                
                //新規投稿
                if(empty($editionid)){
                    //パスワードに記入はある？
                    if(empty($commentPassWord)){
                
                    }
                    //新規投稿できる！
                    else{
                        $sql = 'INSERT INTO tbpost (name,comment,time,password) VALUES(:name,:comment,:time,:commentPassWord)';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':name',$name,PDO::PARAM_STR);
                        $stmt->bindParam(':comment',$comment,PDO::PARAM_STR);
                        $stmt->bindParam(':time',$time,PDO::PARAM_STR);
                        $stmt->bindParam(':commentPassWord',$commentPassWord,PDO::PARAM_STR);
                        $stmt->execute();
                    }
                }
    
                //編集投稿
                else{
                    //パスワードに記入はある？
                    if(empty($commentPassWord)){
                
                    }
                    //編集番号と一致する投稿だけ名前とコメントとパスワードを編集
                    else{
                        $sql = 'UPDATE tbpost SET name = :name,comment = :comment,password = :editionPassWord 
                        WHERE id = :editionid';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':name',$name,PDO::PARAM_STR);
                        $stmt->bindParam(':comment',$comment,PDO::PARAM_STR);
                        $stmt->bindParam(':editionPassWord',$commentPassWord,PDO::PARAM_STR);
                        $stmt->bindParam(':editionid',$editionid,PDO::PARAM_STR);
                        $stmt->execute();
                    
                    //編集番号を空欄に戻す
                    $editionid = "";
                    }
                }
            }
        }
    
    //削除ボタンが押された？
        if(isset($_POST['delete'])){
            
            //トークン番号は一致？
            if(isset($_POST["token"]) && isset($_SESSION["token"]) && ($_POST["token"] == $_SESSION["token"])){
                
                //削除
                $sql = 'delete from tbpost WHERE id = :deleteid AND password = :deletePassWord';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':deleteid',$deleteid,PDO::PARAM_INT);
                $stmt->bindParam(':deletePassWord',$deletePassWord,PDO::PARAM_STR);
                $stmt->execute();
                
                //idを書き直す
                $sql = 'SELECT*FROM tbpost';
                $stmt = $pdo->query($sql);
                $results = $stmt->fetchAll();
                
                foreach($results as $row){
                    if($row['id']>$deleteid){
                        $newid = $row['id']-1;
                        $pastid = $row['id'];
                        //$name = "aaa";
                        
                        $sql = 'UPDATE tbpost SET id = :newid WHERE id = :pastid';
                        $stmt = $pdo->prepare($sql);
                
                        $stmt->bindParam(':newid',$newid,PDO::PARAM_INT);
                        $stmt->bindParam(':pastid',$pastid,PDO::PARAM_INT);
                    
                        $stmt->execute();
                    }
                }
            }
        }
    
     //編集ボタンが押された？
        if(isset($_POST["edit"])){
            
            //トークン番号は一致？
            if(isset($_POST["token"]) && isset($_SESSION["token"]) && ($_POST["token"] == $_SESSION["token"])){
                
                //編集したい投稿の内容を投稿フォームに表示
                $sql = 'SELECT*FROM tbpost WHERE id = :editid AND password = :editPassWord';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':editid',$editid,PDO::PARAM_INT);
                $stmt->bindParam(':editPassWord',$editPassWord,PDO::PARAM_STR);
                $stmt->execute();
                $results = $stmt->fetchAll();
                foreach($results as $row){
                    if($row['id'] == $editid && $row['password'] == $editPassWord){
                    
                        $editionid = $row['id'];
                        $editName = $row['name'];
                        $editComment = $row['comment'];
                        $editionPassWord = $row['password'];
                    
                    }
                }
               
            }
        }
    
    //新しいトークンをセット    
    $_SESSION["token"] = $token = mt_rand();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission5_1</title>
</head>
<body>
    <form action = "" method = "post">
    <input type = "hidden" name = "token" value = "<?= $token ?>">
    〜〜プログラミングが難しいことについて話す掲示板〜〜<br>
     ■投稿用フォーム<br>    
        <!--編集したい投稿の番号-->
        <input type = "hidden" name = "editionid" value = "<?= $editionid ?>">
        お名前<br>
        <input type = "text" name = "name" value = "<?= $editName ?>"><br>
        コメント<br>
        <input type = "text" name = "comment" value = "<?= $editComment ?>"><br>
        パスワード<br>
        <input type = "text" name = "commentPassWord" value = "<?= $editionPassWord ?>"><br>
        <!--送信ボタン-->
        <input type = "submit" name = "submit"><br>
    <br>
    ■削除用フォーム<br>
        削除したい投稿の番号<br>
        <input type = "text" name = "deleteid"><br>
        パスワード<br>
        <input type = "text" name = "deletePassWord"><br>
        <!--削除ボタン-->
        <button type = "submit" name = "delete">削除</button><br>
    <br>
    ■編集番号指定用フォーム<br>
        編集したい投稿の番号<br>
        <input type = "text" name = "editid"><br>
        パスワード<br>
        <input type = "text" name = "editPassWord"><br>
        <!--編集ボタン-->
        <button type = "submit" name = "edit">編集</button><br>
    </form>

    <?php   
       //書き出し
       //投稿番号どうにかする
       $sql = 'SELECT*FROM tbpost';
       $stmt = $pdo->query($sql);
       $results = $stmt->fetchAll();
       
       foreach($results as $row){
           echo $row['id'].",".$row['name'].",".$row['comment'].",".$row['time'].'<br>';
       }
    ?>
    
</body>
</html>