<?php
// 定義済み関数・変数読み込み
require_once("define.php");

  // フォントディレクトリ
  $fontsdir = 'fonts/';

  // 結果文
  $message = "";

  // MIMEタイプ
  $mime_type = "";

  // MIMEタイプが一致したか
  $mime_check = false;

  // アップロード可能なMIMEタイプの記載してあるjsonファイルの読み込み
  $json_path = "json/mime.json";

  // jsonファイルに現在登録されているMIMEタイプの一覧を取得
  $mime_json = file_get_contents($json_path);
  //配列に変換
  $mime_list = json_decode($mime_json,true);

  // ================================================ アップロード処理 =========================================== //
  if(isset($_POST["font_up"]) && $_POST["font_up"] != null && is_uploaded_file($_FILES["fonts"]['tmp_name'][0])){

    // アップロードファイルを取得
    $upfonts = $_FILES["fonts"];
    $result = 0;    

    // アップロードされたファイル分チェックする
    foreach ($upfonts["error"] as $key => $value) {
      
      // アップロードされたかどうか
      if ($value == UPLOAD_ERR_OK) {

        // MIMEタイプ取得
        $mime = shell_exec('file -bi '.escapeshellcmd($upfonts['tmp_name'][$key]));
        $mime = trim($mime);
        $mime = preg_replace("/ [^ ]*/", "", $mime);

        // MIMEタイプ確認
        foreach ($mime_list as $mimeval) {
        if(strpos($mime,$mimeval)){
            $mime_check = true;
          }
        } 

        if($mime_check){

          // ファイルの保存名
          $uploadfile = $fontsdir.basename($upfonts['name'][$key]);

          if(move_uploaded_file($upfonts['tmp_name'][$key], $uploadfile)){
            //move_uploaded_file($uploadfile, $uploaddir)
          }else{
            //何らかの理由でディレクトリに移動できない場合
            $result = 1;
          }
        }else{
          $result = 2;
        }
      }
    }
    if($result == 0){
      $message =  "アップロード成功です。ページを更新して下さい。";
    }elseif($result == 1){
      $message = "アップロード失敗です。<br>ディレクトリ名:".$fontsdir."を確認してください<br>現在のディレクトリは".getcwd()."です。";
    }else{
      $message = "アップロード失敗です。<br>ファイル形式を確認してください。フォントのみアップロードできます。";
    }
  }
  // ================================================ /アップロード処理 =========================================== //

  // ================================================ MIMEタイプcheck処理 =========================================== //
  if(isset($_POST["font_check"]) && $_POST["font_check"] != null && is_uploaded_file($_FILES["fonts"]['tmp_name'][0])){

    // アップロードファイルを取得
    $upfonts = $_FILES["fonts"];

    foreach ($upfonts["error"] as $key => $value) {
      
      // アップロードされたかどうか
      if ($value == UPLOAD_ERR_OK) {
        
        // MIMEタイプ確認
        $mime = shell_exec('file -bi '.escapeshellcmd($upfonts['tmp_name'][$key]));
        $mime = trim($mime);
        $mime = preg_replace("/ [^ ]*/", "", $mime);

        $mime_type = $mime;
      }
    }

  }

  // ================================================ /MIMEタイプcheck処理 =========================================== //

  // ================================================ 削除処理 =========================================== //
  if(isset($_POST["delete"]) && $_POST["delete"] != null && isset($_POST["filename"]) && $_POST["filename"] != null){

    $status = true;

    // 削除対象のファイル名
    foreach ($_POST["filename"] as $key => $value) {

      $filename = $fontsdir.$value;

      if(!unlink($filename)){
        $status = false;
      }
    }
    if($status){
      $message = "削除成功です。ページを更新して下さい。";
    }else{
      $message = "削除失敗です。";
    }
  }
  // ================================================ /削除処理 =========================================== //

  // ================================================ MIMEタイプ登録処理 =========================================== //
  if(isset($_POST["mime_add"]) && $_POST["mime_add"] != null){

    $new_mime = $_POST["mime_type"];

    // 既に同じMIME-TYPEが登録されていないかチェック
    if(!in_array($new_mime,$mime_list)){

      // jsonから読み出した配列に追加
      array_push($mime_list, $new_mime);

      // 配列をjsonにエンコード
      $new_mime_json = json_encode($mime_list);

      // jsonファイルを上書き
      file_put_contents($json_path, $new_mime_json);
    }

  }
  // ================================================ /MIMEタイプ登録処理 =========================================== //

  // 現在アップロードされているフォント一覧を取得
  $server_fonts;
  exec('ls "/var/www/html/imagick_test/fonts/"', $server_fonts);

?>
<!DOCTYPE html>
<html>
<head>
  <title>フォント管理</title>
<link rel="stylesheet" type="text/css" href="css/common.css">
<style type="text/css">
.font{
  font-family: 
}
<?php
  foreach ($FONT_LIST as $key => $value) {
?>
  @font-face {
    font-family: '<?=$value?>';
    src: url('fonts/<?=$value?>');
  }
  .font_<?=$key?>{
    font-family: '<?=$value?>';
  }

<?php
  }
?>
table{
  width: 100%;
  font-size: 12pt;
}
</style>
</head>
<body>
  <main id="wrapper">
    <div>
      <p><?=$message?></p>
      <h1>フォント管理</h1>
      <h2>フォント登録</h2>
      <p>アップロードしたいフォントを選択してください(※複数可)<br>
      ※現在アップロードが確認できている拡張子は「woff,woff2,ttf,ttc,otf,svgf,eot」です。<br>
      フォントの登録が出来ない場合、そのフォントを選び「ファイル形式をチェック」を押して下さい。<br>
      下記のMIMEタイプ登録フォームにそのファイルのMIMEタイプが出力され、そのままMIMEタイプ登録ボタンを押すと、<br>
      そのフォントファイルが登録できるようになります。(完全一致でなくても大丈夫です。<br>
      例:"font"と登録すればMIMEタイプに"font"を含むファイルは全て許可されます。)
      </p>
      <form action="fonts.php" method="post" enctype="multipart/form-data">
        <input type="file" name="fonts[]" required multiple accept="">
        <input type="submit" name="font_up" value="登録">
        <input type="submit" name="font_check" value="ファイル形式をチェック(登録はされません)">
      </form>
      <h3>MIMEタイプ登録</h3>
      <form action="fonts.php" method="post" enctype="multipart/form-data">
        <label for="mime_type">mime_type:</label>
        <input type="text" name="mime_type" id="mime_type" value="<?=$mime_type?>">
        <input type="submit" name="mime_add" value="MIMEタイプ登録">
      </form>
      <h4>アップロード可能なファイルのMIMEタイプ一覧</h4>
      <ul>
      <?php
        foreach ($mime_list as $value) {
          echo "<li>$value</li>";
        }
      ?>
      </ul>
    </div>
    <div>
      <h2>現在アップロードされているフォント一覧</h2>
      <form action="fonts.php" method="post">
        <table>
        <tr>
          <th></th>
          <th>フォント名</th>
          <!-- <th>mimetype</th> -->
          <th>テスト文字列<br>(色は匂へど 散リヌルヲ aAbBcCdDeEfF '\"#$%&()=^~+-*/\|_[]{}<>「・」!?@)</th>
          <th>DL</th>
        </tr>
<?php
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  foreach ($FONT_LIST as $key => $value) {
    echo "<tr>";
    echo "<td><input type='checkbox' name='filename[]' value='$value'></td>";
    echo "<td>$value</td>";
    // echo "<td>$mime</td>";
    echo "<td class='font_$key'>色は匂へど 散リヌルヲ aAbBcCdDeEfF '\"#$%&()=^~+-*/\|_[]{}<>「・」!?@</td>";
    echo "<td><a href='fonts/$value'>DL</td>";
    echo "</tr>";
  finfo_close($finfo);
  }
?>
        </table>
        <input type="submit" name="delete" value="チェックしたフォントを削除">
      </form>
    </div>
  </main>
</body>
</html>