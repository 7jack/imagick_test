<?php

// 画像表示エリア分割数
define("SPLIT_X", 3);
define("SPLIT_Y", 3);

// エラー文章
$error_list = ["1"   => "文字列は必ず入力して下さい。",
              "400" => "エラー:400\n不正な画像データです",
              "415" => "エラー:415\n画像ファイル以外は\n扱えません"];

// ================================================ 入力パラメータデフォルト値 ================================================ //

// 文字列
define("TEXT", "");
// フォントサイズ(px)
define("FONT_SIZE", 50);
// 最小フォントサイズ(px)
define("MIN_FONT_SIZE", 6);
// 最大フォントサイズ(px)
define("MAX_FONT_SIZE", 1000);
//フォントファミリー
define("FONT_FAMILY", "0Koruri-Regular.ttf");
// 文字色
define("FONT_COLOR", "#aa0000");
// 縁取りの色
define("BORDER_COLOR", "#f0f0f0");
// 文字の太さ
define("FONT_WEIGHT", 300);
// 文字列の表示形式
define("TEXT_TYPE", "no_effect");
// 画像加工設定
define("IMAGE_EFFECT", "no_effect");
// 画像パス
define("IMAGE_NAME", "./images/transparent.png");
// 文字表示エリア
define("TEXT_AREA", serialize(["x"=>1, "y"=>1]));
// $config["TEXT_AREA"] = array("x"=>1, "y"=>1);
// 画像表示ポジション
define("ALIGN", serialize(["x"=>1, "y"=>1]));
// $config["ALIGN"] = array("x"=>1, "y"=>1);
// 文字変形タイプ
define("DIS_TYPE", "no_effect");

// ============================================================================================================ //


// フォント一覧取得
$FONT_LIST;
// exec('ls "/var/www/html/imagick_test/fonts/"', $FONT_LIST);
exec('ls "./fonts/"', $FONT_LIST);

// 文字加工プリセット
function transform($dis_type, $img, $draw, $text, $text_width, $text_height){

  switch ($dis_type) {
    // ================================================ そのまま描画 ================================================ //
    case "no_effect":
      // 文字用のキャンバスを用意
      $t_canvas = new Imagick();
      // フォーマットをpingと定義
      $t_canvas->newImage($text_width, $text_height, new ImagickPixel('transparent'));//new ImagickPixel('transparent')

      // $text = $t_canvas->getImageWidth()."x".$t_canvas->getImageHeight();

      // 描画位置を修正
      $draw->setGravity(Imagick::GRAVITY_CENTER);
      // $textの文字を、キャンバス内に描画
      $t_canvas->annotateImage($draw, 0, 0, 0, $text);

      $t_canvas->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
      $t_canvas->setImageMatte(true);
      
      break; 
    // ============================================================================================================== //

    // ================================================ 斜体(右に傾く) ================================================ //
    case "italic_right":

      // 文字用のキャンバスを用意
      $t_canvas = new Imagick();
      // フォーマットをpingと定義
      $t_canvas->newImage($text_width, $text_height, new ImagickPixel('transparent'));//new ImagickPixel('transparent')

      // 描画位置を修正
      $draw->setGravity(Imagick::GRAVITY_CENTER);
      // $textの文字を、キャンバス内に描画
      $t_canvas->annotateImage($draw, 0, 0, 0, $text);

      $t_canvas->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
      $t_canvas->setImageMatte(true);

      // キャンバスを並行四辺形にする
      $t_canvas->shearImage(new ImagickPixel('transparent'),$text_width*0.1,0);

      break;
    // ============================================================================================================== //

    // ================================================ 斜体(左に傾く) ================================================ //
    case "italic_left":
      // 文字用のキャンバスを用意
      $t_canvas = new Imagick();
      // フォーマットをpingと定義
      $t_canvas->newImage($text_width, $text_height, new ImagickPixel('transparent'));//new ImagickPixel('transparent')

      // 描画位置を修正
      $draw->setGravity(Imagick::GRAVITY_CENTER);
      // $textの文字を、キャンバス内に描画
      $t_canvas->annotateImage($draw, 0, 0, 0, $text);

      $t_canvas->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
      $t_canvas->setImageMatte(true);

      // キャンバスを並行四辺形にする
      $t_canvas->shearImage(new ImagickPixel('transparent'),-$text_width*0.1,0);

      break;
    // ============================================================================================================== //
    
    // ================================================ 波形 ================================================ //
    case "wave":
      // 文字用のキャンバスを用意
      $t_canvas = new Imagick();
      // フォーマットをpingと定義
      $t_canvas->newImage($text_width, $text_height, new ImagickPixel('transparent'));//new ImagickPixel('transparent')

      // 描画位置を修正
      $draw->setGravity(Imagick::GRAVITY_CENTER);

      // $textの文字を、キャンバス内に描画します
      $t_canvas->annotateImage($draw, 0, 0, 0, $text);

      $t_canvas->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
      $t_canvas->setImageMatte(true);


      // $points = array($text_height,0,$text_width,$text_width*0.8);// 横幅,角度,上辺中心の高さ、下辺中心の高さ

      // ShepardsDistortion=15 in distort.h
      //$t_canvas->distortImage(Imagick::DISTORTION_ARC, $points, false);
      $t_canvas->setImageBackGroundColor("none");
      $t_canvas->waveImage(10, 100);

      break;
    // ============================================================================================================== //

    // ================================================ 円弧(上に出っ張る) ================================================ //
    case "arc_top":
      // 文字用のキャンバスを用意
      $t_canvas = new Imagick();
      // フォーマットをpingと定義
      $t_canvas->newImage($text_width, $text_height, new ImagickPixel('transparent'));//new ImagickPixel('transparent')

      // 描画位置を修正
      $draw->setGravity(Imagick::GRAVITY_CENTER);
      
      // $textの文字を、キャンバス内に描画します
      $t_canvas->annotateImage($draw, 0, 0, 0, $text);

      $t_canvas->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
      $t_canvas->setImageMatte(true);

      // 展開角度
      $arc = $text_width*0.1+$text_height*0.5;
      // 回転角度
      $rotate = 0;
      // 外周の半径
      $outer_radius = ($text_width+$text_height)*0.6;
      // 内周の半径
      $inner_radius = $text_width*0.6;
      // 中間の半径
      $ceneter_radius = ($outer_radius+$inner_radius)*0.5;
      // 外円の円周
      $outer_len = $outer_radius*2*pi();
      // 内円の円周
      $inner_len = $inner_radius*2*pi();
      // 中間の円周
      $center_len = $ceneter_radius*2*pi();
      // 展開部分の割合
      $view_per = $arc/360;
      // 展開部分の長さ(外内合算)
      $view_len = ($outer_len*$view_per+$inner_len*$view_per)*0.5;
      // 展開部分の中央部分の長さ
      $view_center_len = $center_len*$view_per;
      // ？
      $outer_center_len = ($outer_len*$view_per+$center_len*$view_per)*0.5;
      // 外円の展開部分の長さ
      $outer_view_len = $outer_len*$view_per;

      //扇型変形セット
      $points = array($arc,$rotate,$outer_radius,$inner_radius);

      // 扇型に変形
      $t_canvas->distortImage(Imagick::DISTORTION_ARC, $points, true);

      break;
    // ============================================================================================================== //
    default:
      # code...
      break;
  }

  return array($img, $t_canvas);
};

// 円弧(下に出っ張る)
// 縦表記
// ななめ(左上から右下に)
// ななめ(左下から右上に)
// 円状に(右上、右下、左下、左上)