<?php
// 定義済み定数読み込み
require_once("define.php");

// エラーキャッチ
$error_num = 0;
$error_text = "";
if(isset($_GET["error"])){
  $error_num = $_GET["error"];
  $error_text = $error_list[$error_num];
}

$font_html;

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Imagick Test</title>
<link rel="stylesheet" type="text/css" href="css/common.css">
<script type="text/javascript">
function AutoCheck(checkname) {
    document.getElementById(checkname).checked = true;
}
function resetradio() {
    for (i = 1; i <= 4; i++) {
        document.getElementById('d' + i).checked = false;
    }
}
</script>
</head>
<body>
<h1>任意の文字列をアップロードした画像に合成</h1>
<form action="character.php" method="post" enctype="multipart/form-data" id="wrapper">
  <div>
    <?php if($error_text) echo "<p class='error'>$error_text</p>"; ?>
    <h2>画像選択</h2>
    <section>
      <!-- <input type="file" name="up_image" required> -->
      <input type="file" name="up_image">
    </section>
    <p>※大きなサイズの画像を選択すると加工に時間がかかります。加工なしを選択すると速くなります。</p>
    <h2>画像加工</h2>
    <section>
      <input type="radio" value="<?=IMAGE_EFFECT?>" name="image_effect" id="e0" checked required><label for="e0">加工なし</label><br>
      <input type="radio" value="sketch" name="image_effect" id="e1"><label for="e1">スケッチ調(かなり時間がかかります。現状タイムアウトします。)</label><br>
      <input type="radio" value="contour_line" name="image_effect" id="e2"><label for="e2">等高線</label><br>
      <input type="radio" value="gray_scale" name="image_effect" id="e3"><label for="e3">グレースケール</label><br>
      <input type="radio" value="two_gradation" name="image_effect" id="e4"><label for="e4">2階調化(少し時間がかかります)</label><br>
    </section>
  </div>
  <div>
    <h2>文字列設定</h2>
    <section>
      <h3>文字装飾：</h3>
      <?php
        $d_tex = TEXT; // デフォルトテキストセット
        if($error_num == 1){
print<<<EOF
<label for='chara' class='error'>文字：</label>
<input type='textbox' id='chara' name='chara' value='必須入力' required>
EOF;
        }else{
print<<<EOF
<label for='chara'>文字：</label>
<input type='textbox' id='chara' name='chara' value='$d_tex' required placeholder='文字を入力して下さい'>
EOF;
        }
      ?>
      <br>
      <label for="font">文字の種類：</label>
      <select name="font" id="font">
        <?php
        foreach ($FONT_LIST as $key => $value) {
          // // もしKoruriフォントならデフォルト選択設定にする
          // if($value == DEFAULT_FONT){
          //   $font_html = "<option value='$value' selected>$value</option>";
          // }else{
          //   $font_html = "<option value='$value'>$value</option>";
          // }
          // echo $font_html;
          echo "<option value='$value'>$value</option>";
        }
        ?>
      </select><br>
      <label for="size">文字の大きさ：</label>
      <!-- <select name="size" id="size">
        <option value="0">大</option>
        <option value="1">中</option>
        <option value="2">小</option>
        <option value="999">ランダム</option>
      </select><br> -->
      <input type="number" name="size" id="size" value="<?=FONT_SIZE?>" min="<?=MIN_FONT_SIZE?>" max="<?=MAX_FONT_SIZE?>">pt<br>
      <input type="checkbox" name="font_fit" id="font_fit" checked>
      <label for="font_fit">文字が画像からはみ出ないようにする</label><br>
      <label for="color">文字色：</label>
      <input type="text" name="color" value="<?=FONT_COLOR?>" size="12" id="color" class="html5jp-cpick [coloring:true]" /><br>
      <label for="border_color">縁取りの色：</label>
      <input type="text" name="border_color" value="<?=BORDER_COLOR?>" size="12" id="border_color" class="html5jp-cpick [coloring:true]" /><br>
      <input type="checkbox" name="unbordered" id="unbordered">
      <label for="unbordered">縁取りしない</label><br>
      <h3>表示方法：</h3>
      <input type="radio" value="<?=TEXT_TYPE?>" name="text_type" id="t0" checked required onclick="resetradio()"><label for="t0">入力した文字列をそのまま表示</label><br>
      <!--<input type="radio" value="1" name="text_type" id="t1"><label for="t1">入力した文字を複数表示(例：ゴ→ゴゴゴゴゴ)</label><br>-->
      <input type="radio" value="distortion" name="text_type" id="t2" onclick="AutoCheck('d1')"><label for="t2">文字を歪める</label><br>
      <div id="distortion">
        <input type="radio" name="dis_type" value="italic_right" id="d1" onclick="AutoCheck('t2')"><label for="d1">斜体(右に傾く)</label><br>
        <input type="radio" name="dis_type" value="italic_left" id="d2" onclick="AutoCheck('t2')"><label for="d2">斜体(左に傾く)</label><br>
        <input type="radio" name="dis_type" value="wave" id="d3" onclick="AutoCheck('t2')"><label for="d3">波形</label><br>
        <input type="radio" name="dis_type" value="arc_top" id="d4" onclick="AutoCheck('t2')"><label for="d4">円弧(上に出っ張る)</label><br>
      </div>
      <h3>表示位置：</h3>
      <table>
        <?php 
        for ($i=1; $i <= SPLIT_Y; $i++) { 
          echo "<tr>";
          for ($j=1; $j <= SPLIT_X; $j++) { 

            if($i==1 && $j==1){
              echo "<td><input type='radio' value='$j,$i' checked name='area' id='$j#$i'><label for='$j#$i' class='back' required></label></td>";
            }else{
              echo "<td><input type='radio' value='$j,$i' name='area' id='$j#$i'><label for='$j#$i' class='back'></label></td>";
            }
          }
          echo "</tr>";
        }?>
      </table>
      <h3>文字整列：</h3>
      <p>※9分割されたそれぞれのエリアの(上or下or左or右)端にぴったりくっつくイメージです。<br>
      　中央はそれぞれのエリアの中心。
      </p>
      <h4>縦</h4>
      <input type="radio" name="align_y" value="0" id="v0"><label for="v0">上揃え</label>
      <input type="radio" name="align_y" value="1" id="v1" checked required><label for="v1">中央揃え</label>
      <input type="radio" name="align_y" value="2" id="v2"><label for="v2">下揃え</label>
      <h4>横</h4>
      <input type="radio" name="align_x" value="0" id="h0"><label for="h0">左揃え</label>
      <input type="radio" name="align_x" value="1" id="h1" checked required><label for="h1">中央揃え</label>
      <input type="radio" name="align_x" value="2" id="h2"><label for="h2">右揃え</label>
    </section>
  </div>
  <input type="submit" value="変換" name="submit">
</form>
<script type="text/javascript" src="js/cpick.js"></script>
</body>
</html>
