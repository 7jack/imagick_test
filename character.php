<?php
// 定義済み関数・変数読み込み
require_once("define.php");
set_time_limit(120);

// ==========================定義============================= //

// 画像に埋め込むテキスト
$text = TEXT;
// フォントfamilyを指定するためのキー
$font_family = FONT_FAMILY;
// フォントサイズ
$font_size = FONT_SIZE;
// フォントサイズを画像にフィットさせるかどうか
$font_fit = false;
// 文字色
$font_color = FONT_COLOR;
// 縁取りするかしないか
$unbordered = false;
// 縁取りの色
$border_color = BORDER_COLOR;
// 文字の太さ
$font_weight = 300;
// 文字列の表示形式
$text_type = TEXT_TYPE;
// 画像加工設定(初期は加工無し)
$image_effect = IMAGE_EFFECT;
// デフォルトの画像パス(透明画像)
$image_name = IMAGE_NAME;
// 文字表示エリア
$area = unserialize(TEXT_AREA);
// // 画像表示ポジション
// $position = ["x"=>0, "y"=>0];
// 文字列長
$text_len = 0;
// 文字整列形式
$align = unserialize(ALIGN);
// 文字変形タイプ
$dis_type = DIS_TYPE;//初期値は変形なし
// エラー種別
$error = "";

// ==========================end/定義============================= //

// POST以外は弾く
// if(!isset($_POST["submit"])){
//   header('Location: index.php');
//   exit;
// }
if(!isset($_POST["chara"]) || $_POST["chara"] == ""){
  $error = "?error=1";
  header('Location: index.php'.$error);
  exit;
}

// ======================= ファイルエラー検知 ======================= //

// アップされたファイル
$up_image = $_FILES["up_image"];

// ファイルタイプチェック用変数
$data_type = false;

// 画像系ファイルかどうか
$data_type1 = strpos($up_image["type"],"image/");

// 画像編集ファイル対策
$data_type2 = strpos($up_image["type"],"image/x");

// 画像ファイルかつ、画像編集ファイルではない
if($data_type1 === 0 && $data_type2 === false){
  $data_type = true;
}


// 画像ファイルであるかもしくはファイルがアップロードされていないか
if($data_type == true || $up_image["error"] == 4){
  // ==========================POST値セット============================= //
  if($up_image["error"] == 0){
    // 画像名(画像までのパス)
    $image_name = $up_image["tmp_name"];
  }

  // 画像加工の種類
  $image_effect = $_POST["image_effect"];

  // 入力された文字
  $text = $_POST["chara"];


  // 文字列長
  $text_len = mb_strlen($text);

  // 文字の大きさ
  $font_size = $_POST["size"];

  // サイズ調整
  if($font_size < MIN_FONT_SIZE) $font_size = MIN_FONT_SIZE;
  if($font_size > MAX_FONT_SIZE) $font_size = MAX_FONT_SIZE;
  

  // フィットさせるか否か
  if(isset($_POST["font_fit"]) && $_POST["font_fit"] == "on"){
    $font_fit = true;
  }

  // 文字列の表示形式
  $text_type = $_POST["text_type"];
  
  // 文字の種類
  $font_family = $_POST["font"];
  // 文字色
  $font_color = $_POST["color"];
  // 文字を縁取りするかしないか
  if(isset($_POST["unbordered"]) && $_POST["unbordered"] == "on"){
    $unbordered = true;
  }
  // 文字の縁取りの色
  $border_color = $_POST["border_color"];
  
  //フォントサイズを"ランダム"にした場合
  // if($font_size_key == 999){
  //   $font_size_key = rand(0,2);
  // }
  // 文字表示エリア
  list($area["x"], $area["y"]) = explode(",",$_POST["area"]);

  // 文字整列形式
  $align["x"] = $_POST["align_x"];
  $align["y"] = $_POST["align_y"];

  // $text = "x:".$align["x"]."y:".$align["y"];
  // var_dump($align["x"],$align["y"]);
  // echo "<br>";
  // var_dump($area["x"],$area["y"]);

  //文字変形タイプ
  if(isset($_POST["dis_type"])){
    $dis_type = $_POST["dis_type"];
  }  
  // ==========================end/POST値セット============================= //  
}elseif($data_type == false){
  // 画像以外のファイルがアップロードされた場合
  $text_type = "415";
  $$font_size = FONT_SIZE;
}else{
  // 送られた画像に問題がある場合
  $text_type = "400";
  $$font_size = FONT_SIZE;
}

// ======================= end/ファイルエラー検知 ======================= //


// Imagick オブジェクトを透過キャンバスで作ります
$img = new Imagick($image_name);
$img->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
$img->setImageMatte(true);

// 幅と高さを画像から取得
$width = $img->getImageWidth();
$height = $img->getImageHeight();

// 画像合成時用エリア指定変数
$text_posi = ["x" => 0, "y" => 0];


// 画像が大きすぎると処理に時間がかかるので圧縮します
if($width*$height > 250000){//500*500より大きかったら

  // 画像の圧縮比率
  $raito = 1;

  // 最大辺を500pxに
  if($width >= $height){
    $raito = 500 / $width;
    $width = 500;
    $height = $height * $raito;
  }else{
    $raito = 500 / $height;
    $height = 500;
    $width = $width * $raito;
  }

  // リサイズします
  $img->thumbnailImage($width, $height, false, true);
}

// 幅と高さから１エリアサイズを算出
$base_area = ["x" => $width/SPLIT_X, "y" => $height/SPLIT_Y];

//============================================画像加工============================================//
switch ($image_effect) {
  case "sketch":
    // スケッチ調
    $img->blackThresholdImage('#808080');
    $img->whiteThresholdImage('#808080');
    $img->negateImage(true);
    $img->setImageMatte(true);
    $img = $img->fxImage("r", Imagick::CHANNEL_ALPHA);

    $img2 = new Imagick();
    $img2->newImage($img->getImageWidth(),
    $img->getImageHeight(), "#303030");
    $img2->sketchImage(8,0,135);

    $img->compositeImage($img2, Imagick::COMPOSITE_IN, 0, 0, Imagick::CHANNEL_ALL);
    break;

  case "contour_line":
    // 等高線
    $img->medianFilterImage(2);
    $img->quantizeImage(8, Imagick::COLORSPACE_GRAY, 0, FALSE , false);
    $img->edgeImage(1);
    $img->negateImage(true);
    //$img->writeImage('sample1134a.png');
    break;

  case "gray_scale":
    // グレースケール
    $img->transformImageColorspace(Imagick::COLORSPACE_GRAY);
    break;

  case "two_gradation":
    // 漫画チック(2階調化)
    $img->medianFilterImage(2);
    $img->quantizeImage(4, Imagick::COLORSPACE_GRAY, 0, FALSE , false);
    $img->blackThresholdImage('#202020');
    $img->whiteThresholdImage('#aaaaaa');
    $img->negateImage(true);
    $img->setImageMatte(true);
    $img = $img->fxImage("r", Imagick::CHANNEL_ALPHA);
    //$img->transformImageColorspace(Imagick::COLORSPACE_GRAY);
    
    $img2 = new Imagick();
    $img2->newImage($img->getImageWidth(),
    $img->getImageHeight(), "#000000");
    //img2->sketchImage(8,0,135);

    $img->compositeImage($img2, Imagick::COMPOSITE_IN,
    0, 0, Imagick::CHANNEL_ALL);
    break;

  default:
    break;
}
// 使用済みのオブジェクトを開放
if(isset($img2)) $img2->destroy();
//============================================end/画像加工============================================//

//============================================文字描画============================================//
// 画像の最大辺の長さを取得します(文字列を縦表記したりしないのなら$widthでOK)
// $longest = $width >= $height ? $width : $height;

// 文字サイズを画像のサイズに最適化させます
// $font_size_num = $width / $font_size;





// 1エリア

// 図形、文字列描画用に、新しい ImagickDraw のインスタンスを作ります
$draw = new ImagickDraw();

// テキストの描画用に色を設定 (ImagickDraw オブジェクトを再利用していることに注目)
$draw->setFillColor($font_color);
if(!$unbordered){
  // 縁取りの色を設定します
  $draw->setStrokeColor($border_color);
}
// 線の太さを設定します
$draw->setStrokeWidth(2);
// フォントのカーニングを設定します (負の値は、文字と文字の間隔を狭くすることを意味します)
$draw->setTextKerning(0);
// フォントとそのサイズ、重さを設定します
$draw->setFont('fonts/'.$font_family);
$draw->setFontSize($font_size);
$draw->setFontWeight($font_weight);
// テキストを縦横ともに中央寄せにします
//$draw->setGravity(Imagick::GRAVITY_CENTER);
//$draw->setGravity(Imagick::GRAVITY_NORTHWEST);
// テキストをアンチエイリアスします
$draw->setTextAntialias(true);
$draw->setStrokeAntialias(true);

// =====================文字配置位置設定========================= //

// 文字列メイトリックス取得
$text_metrics = $img->queryFontMetrics($draw, $text);
//文字列全体の長さと高さ
$text_width = $text_metrics["textWidth"];
$text_height = $text_metrics["textHeight"];

// フィット設定している場合フィッティング処理を行う
if($font_fit){
  // 文字列幅が画像幅を超えている場合
  // 縦も同じく
  if($text_height > $height){
    $text_height = $height;
    // 文字列幅が画像幅以下になるようにする
    $font_size = $height;
    // フォントサイズセットしなおし
    $draw->setFontSize($font_size);
    $text_metrics = $img->queryFontMetrics($draw, $text);
    $text_height = $text_metrics["textHeight"];
  }

  
  if($text_width > $width){
    // 文字列幅が画像幅以下になるようにする
    $font_size = $width / $text_len;
    // フォントサイズセットしなおし
    $draw->setFontSize($font_size);
    $text_metrics = $img->queryFontMetrics($draw, $text);
    $text_width = $text_metrics["textWidth"];
    // $text = $draw->getFontSize();

  }
  if($text_width > $base_area["x"]){
    $align["x"] = $area["x"]-1;
  }
  if($text_height > $base_area["y"]){
    $align["y"] = $area["y"]-1;
  }
}

switch ($text_type) {
  // ================================================ 入力された文字列をそのまま表示する場合 ================================================ //
  case "no_effect":
    // 入力された文字列をそのまま表示する場合
    // $textの文字を、キャンバス内に描画します
    // $img->annotateImage($draw, $text_posi["x"], $text_posi["y"], 0, $text);
    // 加工処理呼び出し
    list($img, $t_canvas) = transform("no_effect", $img, $draw, $text, $text_width, $text_height);
    break;
  // ================================================ end/入力された文字列をそのまま表示する場合 ================================================ //

  // ================================================ 入力された文字を複数表示する場合 ================================================ //
  case "serial":
    // 入力された文字を複数表示する場合
    /*if($_POST["size"] == 3){// ランダム指定の場合
      for($i=0; $i<5; $i++){
        // フォントサイズをランダム指定します
        $draw->setFontSize($longest / $font_size_list[rand(0,2)]);
        $img->annotateImage($draw, ($font_size_num*($i-2)), ($height/2)-($height/($i+2)), 0, $text);
      }
    }else{
      for($i=0; $i<5; $i++){
        $img->annotateImage($draw, ($font_size_num*($i-2)), ($height/2)-($height/($i+2)), 0, $text);
      }
    }*/

    break;
  // ================================================ end/入力された文字を複数表示する場合 ================================================ //

  // ================================================ 入力された文字列を歪めて表示する場合 ================================================ //
  case "distortion":
    // 入力された文字列を歪めて表示する場合    

    // 加工処理呼び出し
    list($img, $t_canvas) = transform($dis_type, $img, $draw, $text, $text_width, $text_height);

    break;
  // ================================================ end/入力された文字列を歪めて表示する場合 ================================================ //

  // ================================================ 画像データにエラーがあった場合 ================================================ //
  case "400":
    $error = "?error=400";
    header('Location: index.php'.$error);
    exit;
    break;
  // ================================================ end/画像以外のデータが送られた場合 ================================================ //

  // ================================================ 画像以外のデータが送られた場合 ================================================ //
  case "415":
    $error = "?error=415";
    header('Location: index.php'.$error);
    exit;
    break;
  // ================================================ end/画像以外のデータが送られた場合 ================================================ //
  default:
    break;
}

// =====================文字配置位置再設定========================= //
// 横方向
switch($align["x"]){
  case 0:
    // 左寄せパターン
    $text_posi["x"] = $base_area["x"]*$area["x"] - $base_area["x"];
    break;
  case 1:
    // 中央寄せパターン
    $text_posi["x"] = $base_area["x"]*$area["x"] - $base_area["x"] + $base_area["x"]/2 - $t_canvas->getImageWidth()/2;
    break;
  case 2:
    // 右寄せパターン
    $text_posi["x"] = $base_area["x"]*$area["x"] - $base_area["x"] + ($base_area["x"]-$t_canvas->getImageWidth());
    break;
}

// 縦方向
switch($align["y"]){
  case 0:
    // 高さ上辺寄せ
    $text_posi["y"] = $area["y"]*$base_area["y"] - $base_area["y"] + $t_canvas->getImageHeight();
    break;
  case 1:
    // 高さ中央寄せ
    $text_posi["y"] = $area["y"]*$base_area["y"] - $base_area["y"]/2 + ($t_canvas->getImageHeight()/2);
    break;
  case 2:
    // 高さ下辺寄せ
    $text_posi["y"] = $area["y"]*$base_area["y"];
    break;
}
// =====================end/文字配置位置再設定========================= //

    // 画像に文字キャンバスを結合
    $img->compositeImage($t_canvas, Imagick::COMPOSITE_DEFAULT, $text_posi["x"], $text_posi["y"]-$text_height);

if(isset($t_canvas)) $t_canvas->destroy();
//============================================end/文字描画============================================//

// 文字のみ入力時は文字以外の部分を切り落とす
if($up_image["error"] != 0){
  $img->trimImage(0);
}


// フォーマットをpingと定義
$img->setImageFormat('png');

// ダウンロード用に画像名登録
$fpath = "images/imagick.png";
$fname = "imagick.png";

// PNG のヘッダーを設定して、画像を出力します
header('Content-Type: image/png');
//echo $img;

// 一旦サーバに保存
$img->writeImage('images/imagick.png');
// このままだと同時アクセス時に、
// サーバに保存してダウンロードさせるまでに他のアクセスによって
// 画像が書き換えられてしまうおそれがあるので、
// 本番ではアクセス回数(画像を書き換えた回数)をサーバに保存して、
// 100枚までは別名保存するように設定する等の工夫が必要かもしれません。

// 後で戻せるように設定を保持っとく
$org_timeout = ini_get('default_socket_timeout');
 
// 20秒以上かかったらタイムアウトにする
ini_set('default_socket_timeout', 20);

// ダウンロードさせる
header('Content-Length: '.filesize($fpath));
header('Content-Disposition: attachment; filename="'.$fname.'"');
echo file_get_contents($fpath);

// 設定を戻す
ini_set('default_socket_timeout', $org_timeout);

// オブジェクトを削除
$img->destroy();
?>