<!-- ----------DBを使った掲示板---------- -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>keizi</title>
</head>
<body>
    <?php
    // ----------【データベース接続設定】--------------------------------------------------------------------
    // DSN(Data Source Name)データベースに接続するために必要な情報
    // データベースの種類を指定:で区切る,項目名=値;で区切る
    $dsn = 'mysql:dbname=*****db;host=localhost';
    $user = '*****';
    $password = '*****';
    // エラーがあった際警告として表示
    // PDO(PHP DATA Objects)データベースへのアクセスを抽象的にしてくれる
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    // ----------【データベース内に新規テーブル作成】----------------------------------------------------------
     // もしまだこのテーブルが存在しないなら
     $sql = "CREATE TABLE IF NOT EXISTS k_table"
     ." ("
     . "id INT AUTO_INCREMENT PRIMARY KEY,"
     . "name char(32),"
     . "comment TEXT,"
     . "password TEXT,"
     . "date DATETIME"
     .");";
     // sql文のなかに変数がないのでqueryメソッドで実行
     $stmt = $pdo->query($sql);

    // ----------【投稿機能】------------------------------------------------------------------------------
    // データベースの値として使う変数を初期化
    $name = '';
    $comment = '';
    $password = '';
    $date = '';

    // エラー配列を初期化
    $error = [];

    // prepareメソッドで変数を使ったSQL文を準備
    $sql = $pdo->prepare("INSERT INTO k_table (name, comment, password, date) VALUES(:name, :comment, :password, :date)");
    // bindParamで値をセット
    $sql -> bindParam(':name', $name, PDO::PARAM_STR);
    $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
    $sql -> bindParam(':password', $password, PDO::PARAM_STR);
    $sql -> bindParam(':date', $date, PDO::PARAM_STR);

    // 投稿フォームが送信された時
    if(isset($_POST['submit'])){

        // -----nameのエラーキャッチ
        if($_POST['name'] == ''){
            $error['name'] = 'blank';
        }
        
        // -----commentのエラーキャッチ
        if($_POST['comment'] == ''){
            $error['comment'] = 'blank';
        }
        
        // -----passwordのエラーキャッチ
        if($_POST['password'] == ''){
            $error['password'] = 'blank';
        }
        
        // エラーが1つもなければ
        if(empty($error)){
            $name = $_POST['name'];
            $comment = $_POST['comment'];
            $password = $_POST['password'];
            $date = date("Y/m/d/ H:i:s");

            // バインドを実行
            $sql->execute();
        }
    }

    // ----------【編集機能】------------------------------------------------------------------------------

    // 編集選択機能-------------------------------------
    if(isset($_POST['e_submit'])){
        
        
        // -----編集番号のエラーキャッチ
        if($_POST['edit_id'] == ''){
            $error['edit_id'] = 'blank';
        }
        // -----編集のパスワードエラーキャッチ
        if($_POST['edit_password'] == ''){
            $error['edit_password'] = 'blank';
        }
        
        if(empty($error)){
            $id = $_POST['edit_id'];
            $sql = 'SELECT * FROM k_table';
            $records = $pdo->query($sql);
            $results = $records->fetchAll();
            foreach ($results as $row){
                if($row['id'] == $id){
                    // 投稿番号が一致した投稿の名前、コメント、パスワード
                    $e_name = $row['name'];
                    $e_comment = $row['comment'];
                    $e_password =  $row['password'];
                    // 編集フォームに入力されたパスワードの値
                    $password = $_POST['edit_password'];
                    // パスワードチェック
                    if($e_password == $password){
                        $edit_value = $id;
                        $name_value = $e_name;
                        $comment_value = $e_comment;
                    }else{
                        // パスワードが違いことを知らせる
                        $error['error_pass'] = 'wrong';
                        // エラーが出るので空にしておく
                        $edit_value = '';
                        $name_value = '';
                        $comment_value = '';
                    }  
                }
            }
        }
    }

    // 編集実行機能-------------------------------------
    if(isset($_POST['edit_submit'])){
        $id = $_POST['edit'];
        $name = $_POST['edit_name'];
        $comment = $_POST['edit_comment']; 
        $date = date("Y/m/d/ H:i:s");
        $sql = 'UPDATE k_table SET name=:name,comment=:comment, date=:date WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
    }

    // ----------【削除機能】------------------------------------------------------------------------------
    if(isset($_POST['d_submit'])){
        
        
        // -----編集番号のエラーキャッチ
        if($_POST['delete_id'] == ''){
            $error['delete_id'] = 'blank';
        }
        // -----編集のパスワードエラーキャッチ
        if($_POST['delete_password'] == ''){
            $error['delete_password'] = 'blank';
        }

        if(empty($error)){
            $id = $_POST['delete_id'];
            $password = $_POST['delete_password'];

            $sql = 'SELECT * FROM k_table';
            $records = $pdo->query($sql);
            $results = $records->fetchAll();
            foreach ($results as $row){
                // $rowの中にはテーブルのカラム名が入る
                if($row['id'] == $id){
                    $d_password =  $row['password'];
                    // パスワードチェック
                    if($d_password == $password){
                        $sql = 'delete from k_table where id=:id';
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                        $stmt->execute();
                    }else{
                        $error['error_pass'] = 'wrong';
                    }  
                }
            }
        }
    }
    ?>
    <h1>DBを使った掲示板</h1><br>
    <!-- ----------【入力フォーム】--------------------------------------------------------------------- -->
    <!-- 投稿フォーム -->
    【　投稿フォーム　】
    <form action="" method="post"> 
        <input type="hidden" name="edit" value="<?php echo $edit_value ?>">
        <?php if(empty($_POST["edit_id"])): ?>
            <!-- 通常フォーム -->
            <input type="text" name="name" placeholder="名前"><br>
            <input type="text" name="comment" placeholder="コメント"><br>
            <input type="text" name="password" placeholder="パスワード">
            <input type="submit" name="submit"><br><br> 
            <?php else: ?>
            <!-- 編集実行用フォーム -->
            <input type="text" name="edit_name" value="<?php echo $name_value ?>" placeholder="名前"><br>
            <input type="text" name="edit_comment" value="<?php echo $comment_value ?>" placeholder="コメント">
            <input type="submit" name="edit_submit"><br><br> 
        <?php endif; ?>
    </form>
       
    【　削除フォーム　】
    <!-- 削除フォーム -->
    <form action="" method="post">  
        <input type="text" name="delete_id" placeholder="削除する番号"><br>
        <input type="text" name="delete_password" placeholder="パスワード">
        <input type="submit" name="d_submit" value="削除"><br><br>
    </form>

    【　編集フォーム　】
    <!-- 編集フォーム -->
    <form action="" method="post">   
        <input type="text" name="edit_id" placeholder="編集する番号"><br>
        <input type="text" name="edit_password" placeholder="パスワード">
        <input type="submit" name="e_submit" value="編集"> <br><br><br>
    </form>

    <!-- ----------【エラーメッセージ】------------------------------------------------------------------ -->
    <?php if(!empty($error)): ?>
        <p>!----------!</p>
    <?php endif; ?>

    <!-- 名前空欄エラー -->
    <?php if(isset($error['name']) && $error['name'] === 'blank'): ?>
        <p>※名前を入力してください</p>
    <?php endif; ?>

    <!-- コメント空欄エラー -->
    <?php if(isset($error['comment']) && $error['comment'] === 'blank'): ?>
        <p>※コメントを入力してください</p>
    <?php endif; ?>

    <!-- パスワード空欄エラー -->
    <?php if(isset($error['password']) && $error['password'] === 'blank'): ?>
        <p>※パスワードを入力してください</p>
    <?php endif; ?>

    <!-- 編集番号空欄エラー -->
    <?php if(isset($error['edit_id']) && $error['edit_id'] === 'blank'): ?>
        <p>※編集する投稿番号を入力してください</p>
    <?php endif; ?>

    <!-- 編集パスワードエラー -->
    <?php if(isset($error['edit_password']) && $error['edit_password'] = 'blank'): ?>
        <p>※パスワードを入力してください</p>
    <?php endif; ?>

    <!-- 削除番号空欄エラー -->
    <?php if(isset($error['delete_id']) && $error['delete_id'] === 'blank'): ?>
        <p>※削除する投稿番号を入力してください</p>
    <?php endif; ?>

    <!-- 削除パスワードエラー -->
    <?php if(isset($error['delete_password']) && $error['delete_password'] = 'blank'): ?>
        <p>※パスワードを入力してください</p>
    <?php endif; ?>

    <!-- パスワード誤りエラー -->
    <?php if(isset($error['error_pass']) && $error['error_pass'] = 'wrong'): ?>
        <p>※パスワードが違います。</p>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <p>!----------!</p>
    <?php endif; ?>

    <!-- ----------【ブラウザ表示】--------------------------------------------------------------------- -->
    ------------------ 投稿一覧 ---------------------<br><br>
    <?php
    $sql = 'SELECT * FROM k_table';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].'<br>';
    echo "<hr>";
    }
    ?>
</body>
</html>