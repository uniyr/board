<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <title>mission_5-1</title>
    </head>
    
    <body>
        <?php
        
        $name = "";
        $comment = "";
        $pass = "";
        $editN = ""; //編集番号が選択され、テキストをinputに表示する際使う
        // $filename = "mission_5-1.txt";
        $time = date("Y年m月d日 H時i分s秒"); //投稿時間
        
        // DB接続設定
        $dsn = '******';
        $user = '*******';
        $password = '*****';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        
        //テーブルpostdb と 項目id,name,comment,time,pass を作成
        $sql = "CREATE TABLE IF NOT EXISTS postdb" 
        ." ("
        . "id INT AUTO_INCREMENT PRIMARY KEY,"
        . "name TEXT,"
        . "comment TEXT,"
        . "time TEXT,"
        . "pass TEXT"
        .");";
        $stmt = $pdo->query($sql);
            
        //投稿ボタンが押された（投稿時、編集時）
        if(isset($_POST['submit'])) {
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $editN = $_POST["editN"];
            $pass = $_POST["pass"];
            //DBに何列データがあるか　$sql = "SELECT COUNT( * ) FROM user;"　$count = $pdo->query($sql);
            
            //編集の場合　（editNumが入力されていて、それがcount以下の時（過去の投稿の時）                    
            if (!empty($editN)){ 
                //編集番号と一致する行をアップデート
                // update テーブル名 set カラム1名 = 更新後の値1, カラム2名 = 更新後の値2 where 条件式;
                $sql = $pdo -> prepare("UPDATE postdb SET name = :name, comment = :comment, time = :time, pass = :pass WHERE id = :id");
                $sql -> bindParam(':id', $editN, PDO::PARAM_INT);
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':time', $time, PDO::PARAM_STR);
                $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
                $sql -> execute();
                echo "編集完了！"; //表示へ
            //投稿の場合
            } else {
                //DBに5つの要素を書き込む
                $sql = $pdo -> prepare("INSERT INTO postdb (name, comment, time, pass) VALUES (:name, :comment, :time, :pass)");
                $sql -> bindParam(':name', $name, PDO::PARAM_STR);
                $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
                $sql -> bindParam(':time', $time, PDO::PARAM_STR);
                $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
                $sql -> execute();
                echo "投稿完了"; //表示機能へ。
            } 
        }
        
        //削除ボタンが押された時の処理
        if(isset($_POST['delete'])) {
            $deleN = $_POST['deleNum']; //編集する投稿番号
            $delePass = $_POST['delePass']; //投稿のパスワード
            
            //パスワードをデータベースから取得
            $stmt = $pdo -> prepare("DELETE FROM postdb WHERE id = :id AND pass = :pass");
            $stmt -> bindParam(':id', $deleN, PDO::PARAM_INT);
            $stmt -> bindParam(':pass', $delePass, PDO::PARAM_STR);
            $stmt -> execute();
            echo "削除しました";
            
            //パスワードが正しくない時    
            
        }

               
        //編集ボタンが押された時 パスチェックして投稿機能にバトンタッチ
        if(isset($_POST['edit'])){
            $editN = $_POST['editNum']; //編集する投稿番号
            $editPass = $_POST['editPass']; //投稿のパスワード
            
            //パスワードをデータベースから取得
            $stmt = $pdo -> prepare("SELECT id,pass FROM postdb WHERE id = :id");
            $stmt -> bindParam(':id', $editN, PDO::PARAM_INT);
            $stmt -> execute();
            
            $results = $stmt-> fetchAll();
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                $correctId = $row['id'];
                $correctPass = $row['pass'];
            }
            //編集する投稿のパスワードが合っていた時
            if($editPass == $correctPass && $editN == $correctId){
                echo "編集モードです";
                //編集する投稿の情報をさらに取得
                $stmt = $pdo -> prepare("SELECT name, comment FROM postdb WHERE id = :id");
                $stmt -> bindParam(':id', $editN, PDO::PARAM_INT);
                $stmt -> execute();
                $results = $stmt->fetchAll();//ここではidの一致する1行のみ
                foreach ($results as $row){
                    //データ項目をinputタグにいれる
                    $name = $row['name'];
                    $comment = $row['comment'];
                    $editN = $editN; //idのこと
                    $pass = $correctPass;
                }
                
                
            }else{
                echo "パスワードが間違っています。";
            }
        }
        
        
    
        ?>
        <h1>新規投稿フォーム</h1>
        <form action="" method="post">
            <p>好きなアニメキャラは？</p>
            <label for="namel">名前</label>
            <input type="text" name="name" id="namel" placeholder="漢字氏名" autocomplete=off value="<?php echo $name; ?>">
            <br><br>
            <label for="commentl">コメント</label>
            <input type="text" name="comment" id="commentl" placeholder="コメント" autocomplete=off value="<?php echo $comment; ?>">
            <br><br>
            <label for="passl">Pass</label>
            <input type="text" name="pass" id="passl" placeholder="半角" autocomplete=off value="<?php echo $pass; ?>">
            <!--<br><br>-->
            <!--<label for="editl">番号</label>-->
            <input type="hidden" name="editN" id="editl" placeholder="編集時に表示される" autocomplete=off value="<?php echo $editN; ?>">
            <input type="submit" name="submit">
        </form>
        <h1>削除番号指定用フォーム</h1>
        <form action="" method="post">
            <label for="deleNuml">番号</label>
            <input type="text" name="deleNum" id="deleNuml" placeholder="削除対象番号" autocomplete=off>
            <br><br>
            <label for="dpassl">Pass</label>
            <input type="text" name="delePass" id="dpassl" placeholder="半角" autocomplete=off>
            
            <input type="submit" name="delete" value="削除">
        </form>
        <h1>編集番号指定用フォーム</h1>
        <form action="" method="post">
            <label for="editNuml">番号</label>
            <input type="text" name="editNum" id="editNuml" placeholder="編集対象番号" autocomplete=off>
            <br><br>
            <label for="epassl">Pass</label>
            <input type="text" name="editPass" id="epassl" placeholder="半角" autocomplete=off>
            
            <input type="submit" name="edit" value="編集">
            <br><br>
        </form>
        <?php
        
        //表示機能
        
        $sql = 'SELECT * FROM postdb';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].' ';
            echo $row['name'].' ';
            echo $row['comment'].' '; 
            echo $row['time'].'<br>';
        }

        ?>
    </body>
</html>