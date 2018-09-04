<?php
/**
 * Created by PhpStorm.
 * User: Jonlinc
 * Motto : Missed is missed.
 * Date: 2018/8/7 0007
 * Time: 下午 22:09
 * 指定图片处理程序，包括单图片和多图片的处理
 */
header("Content-type:text/html;Charset=utf-8");

require ("image.class.php");

/*$url = 'src_img/';
$handle = opendir($url);
while(($fileName = readdir($handle)) !== false)
{
    if($fileName != "." && $fileName != "..") {
        $res = areaRGB($url.$fileName,625,635,775,740);
        echo $fileName."的主色调是：RGB(".$res[0].",".$res[1].",".$res[2].")总色值是：";
        echo $res[0]+$res[1]+$res[2];
        $rgb = "rgb(".$res[0].",".$res[1].",".$res[2].")";
        $color = RGBToHex($rgb);
        echo "<div style='width: 50px;height: 25px;background-color: $color'></div>";
        echo "<br/><br/>";
    }
}*/

$res = multiDealPicture('src_img');//图片处理结果

if(false == $res)
{
    echo "不是目录！";
    return false;
}else{
    result_display($res);
}
/*$origin_img_url = 'src_img/';
$fileName = '36_31.50.jpg';
$res = singleDealPicture($origin_img_url,$fileName);
echo $res;*/


/**
 * 单张图片处理函数
 * @param $origin_img_url 原图路径
 * @param $fileName 文件名
 * @return string 返回结果
 */
function singleDealPicture($origin_img_url,$fileName)
{
    $entry_url = $origin_img_url.$fileName;
    if(!file_exists($entry_url))
    {
        return "文件不存在";
    }
    $pattern = '/^\d+_\d+\.\d{2}\.(jpg|png|bmp|jpeg)$/';

    if(preg_match($pattern,$fileName) == 0)
    {
        return "格式错误";
    }

    $price = substr($fileName,3,-4);
    if(!is_numeric($price))
    {
        return "价格错误";
    }else{
        $water = 'src/images/font_img.png';
        $font_url = 'src/images/font.Otf';
        $output = 'temp/test';
        direct_deal($entry_url,$water,1,"RM".$price,$font_url,$output);
    }
    return $fileName;
}

/**
 * 批量合成图片
 * @param $dir 要合成图片的目录
 * @return array|bool
 */
function multiDealPicture($dir)
{
    if(!file_exists($dir))
    {
        return false;
    }
    /**其中$filename = readdir($handler)
    每次循环时将读取的文件名赋值给$filename，$filename !== false。
    一定要用!==，因为如果某个文件名如果叫'0′，或某些被系统认为是代表false，用!=就会停止循环
     **/
    $success_file_array = array();//存放已处理的图片
    $failed_file_array = array(); //存放未处理的图片/文件
    $result = array();//结果统计
    $handle = opendir($dir);
    //$pattern = '/^(\w+\.)+(jpg|bmp|png)$/';
    $pattern = '/^\d+_\d+\.\d{2}\.(jpg|png|bmp|jpeg)$/';

    /** 参数设定 **/
    $font_url = 'src/images/font.otf'; //字体
    $source_url = 'src_img/';//原图路径
    $result_url = "res_img/";//最终生成的目录

    while(($fileName = readdir($handle)) !== false)
    {
        //略过目录中的名字为‘.’和‘..’的文件
        if($fileName != "." && $fileName != "..")
        {
            if(preg_match($pattern,$fileName) == 0)
            {
                array_push($failed_file_array,$fileName);
                continue;
            }else{
                $price = substr($fileName,3,-4);
                if(!is_numeric($price))
                {
                    array_push($failed_file_array,$fileName);
                    continue;
                }else{
                    $file_name = substr($fileName,0,strlen($fileName)-4);//去除后缀只保留名称(针对后缀为3位)
                    $price_num = strlen(floor($price))-1;//价格小数点前位数
                    $text = "RM".$price;//显示文本
                    $img_water = "src/images/font_img.png";//默认原图水印
                    $color= array(0=>0,1=>0,2=>0);
                    $res = areaRGB($source_url.$fileName,625,635,775,740);

                    if($res == 1)
                    {
                        $rand_water = array(
                            0 => array(
                                'src' => 'src/images/white.png',
                                'r' => 255,
                                'g' => 255,
                                'b' => 255
                            ),
                            1 => array(
                                'src' => 'src/images/yellow.png',
                                'r' => 255,
                                'g' => 255,
                                'b' => 0
                            ),
                            2 => array(
                                'src' => 'src/images/green.png',
                                'r' => 0,
                                'g' => 255,
                                'b' => 0
                            ),
                            3 => array(
                                'src' => 'src/images/red.png',
                                'r' => 255,
                                'g' => 0,
                                'b' => 0
                            ),
                            4 => array(
                                'src' => 'src/images/blue.png',
                                'r' => 0,
                                'g' => 0,
                                'b' => 255
                            )
                        );

                        $new_water = $rand_water[rand(0,4)];
                        if(file_exists($new_water['src']))
                        {
                            $img_water = $new_water['src'];
                            $color = array(0=>$new_water['r'],1=>$new_water['g'],2=>$new_water['b']);
                        }

                    }
                    direct_deal($source_url.$fileName,$img_water,$price_num,$text,$font_url,$result_url.$file_name,$color);
                    array_push($success_file_array,$fileName);
                }
            }
        }
    }

    $result[0] = $success_file_array;//成功处理
    $result[1] = $failed_file_array;//失败处理
    $result[2] = count($success_file_array) + count($failed_file_array);//处理总数

    return $result;
}

/**
 * 水印区域颜色平均值
 * @param $origin_img 原图片
 * @param int $st_x 水印图案起始横坐标
 * @param int $st_y 水印图案起始纵坐标
 * @param int $ed_x 水印图案终点横坐标
 * @param int $ed_y 水印图案终点纵坐标
 * @return array 返回水印区域的平均RGB值
 */
function areaRGB($origin_img,$st_x=0,$st_y=0,$ed_x=800,$ed_y=800)
{

    $i=imagecreatefromjpeg($origin_img);//jpeg格式的图片
    //$RGB = array();
    $rTotal = 0;
    //$gTotal = 0;
    //$bTotal = 0;
    $total = 0;
    $flag = 0;
    //为了更精确分析图标水印区域的色值，本来是用imagesy()来开始取点。
    //目前测量出的起点坐标为（625,635），终点坐标为（775,740）
    for ($x=$st_x;$x<$ed_x;$x++) {

        for ($y=$st_y;$y<$ed_y;$y++) {

            $rgb = imagecolorat($i,$x,$y);
            $r=($rgb >>16) & 0xFF;
            $g=($rgb >>8) & 0xFF;
            $b=$rgb & 0xFF;

            $rTotal ++;
            if($r <= 60 && $g <= 60 && $b <= 60)
            {
                //如果出现黑色的可能，且占区域约6分之一，则需变色
                $total ++;
                if($total >= 2500)
                {
                    $flag = 1;
                    break;
                }
            }
            //$rTotal += $r;
            //$gTotal += $g;
            //$bTotal += $b;
            //$total++;
        }
    }
    //$RGB[0] = round($rTotal/$total);
    //$RGB[1] = round($gTotal/$total);
    //$RGB[2] = round($bTotal/$total);

    return $flag;
}
/**
 * @param $origin_src 原图片
 * @param $pic_water 水印图片
 * @param $price_num 价格小数点前的位数
 * @param $content 价格文本
 * @param $font_url 字体路径
 * @param $out_put 最终输出路径
 * @param int $size 字体大小（默认）
 * @param array $color 字体颜色（默认）
 * @param int $angle 旋转角度（默认）
 * @return bool 返回结果
 */
function direct_deal($origin_src,$pic_water,$price_num,$content,$font_url,$out_put,$color,$size=25,$angle=0)
{
    //水印合成
    $alpha = 0;
    $pic_local = array(
        'x'=>600,
        'y'=>600);
    $image = new Image($origin_src);
    $image->imageMark($pic_water,$pic_local,$alpha);

    //字体合成
    $arr = array(0,1,2);
    if(!in_array($price_num,$arr))
    {
        return false;
    }
    //$price_num 代表商品的价格小数点前的位数，0为1位数，1为2位数，2为三位数
    $local = array(
        'x'=>640-$price_num*10,
        'y'=>660);
    $image->fontMark($content,$font_url,$size,$color,$local,$angle);

    //保存图片
    $image->save($out_put);
    //显示图片
    //$image->show();

}
/**
 * 结果文本展示
 * @param $res 处理结果数据
 */
function result_display($res)
{
    echo "已处理的图片名称：<br/><br/>";
    if(0 == count($res[0]))
    {
        echo "<br/>未成功处理任何图片<br/>";
    }else{
        foreach ($res[0] as $val)
        {
            echo $val."<br/>";
        }
    }
    echo "<br/>处理失败的图片：<br/>";
    if(0 == count($res[1]))
    {
        echo "所有图片均已处理<br/>";
    }else{
        foreach ($res[1] as $val)
        {
            echo $val."<br/>";
        }
    }
    echo "<br/>本文件夹中共有".$res[2]."个图片/文件。<br/><br/>其中成功处理的文件有".count($res[0])."个。<br/></br>未处理的图片/文件有".count($res[1])."个。";
    echo "<script>alert('水印已全部处理，请进一步处理！');</script>";
}


/**
 * RGB转 十六进制
 * @param $rgb RGB颜色的字符串 如：rgb(255,255,255);
 * @return string 十六进制颜色值 如：#FFFFFF
 */
function RGBToHex($rgb){
    $regexp = "/^rgb\(([0-9]{0,3})\,\s*([0-9]{0,3})\,\s*([0-9]{0,3})\)/";
    $re = preg_match($regexp, $rgb, $match);
    $re = array_shift($match);
    $hexColor = "#";
    $hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
    for ($i = 0; $i < 3; $i++) {
        $r = null;
        $c = $match[$i];
        $hexAr = array();
        while ($c > 16) {
            $r = $c % 16;
            $c = ($c / 16) >> 0;
            array_push($hexAr, $hex[$r]);
        }
        array_push($hexAr, $hex[$c]);
        $ret = array_reverse($hexAr);
        $item = implode('', $ret);
        $item = str_pad($item, 2, '0', STR_PAD_LEFT);
        $hexColor .= $item;
    }
    return $hexColor;
}

/**
 * 十六进制 转 RGB
 */
function hex2rgb($hexColor) {
    $color = str_replace('#', '', $hexColor);
    if (strlen($color) > 3) {
        $rgb = array(
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        );
    } else {
        $color = $hexColor;
        $r = substr($color, 0, 1) . substr($color, 0, 1);
        $g = substr($color, 1, 1) . substr($color, 1, 1);
        $b = substr($color, 2, 1) . substr($color, 2, 1);
        $rgb = array(
            'r' => hexdec($r),
            'g' => hexdec($g),
            'b' => hexdec($b)
        );
    }
    return $rgb;
}
/**
 * 第一次处理，将原图与透明水印合成
 * @param $src 原图片
 * @param $water 水印图片
 * @param $merge_pic 合成图片路径及文件名
 */
function makePicture($src,$water,$merge_pic)
{
    $alpha = 0;
    $local = array(
        'x'=>600,
        'y'=>600);
    $image = new Image($src);
    $image->imageMark($water,$local,$alpha);
    $image->save($merge_pic);
    //$image->show();
}

/**
 * 第二次处理，将前面合成的图片再添加价格
 * @param $price_num 价格位数，分为小数点前的1、2、3位
 * @param $src 原图（第一次合成的图片）
 * @param $merge_pic 合成后存入的路径及文件名
 * @param $content 合成的价格
 * @param $font_url  字体路径
 * @param int $size 字体大小
 * @param array $color 字体颜色及透明度，数组存放
 * @param int $angle 字体旋转角度，此处无需旋转
 * @return bool 返回处理结果，判别价格位数的合法性
 */
function makeFont($price_num,$src,$merge_pic,$content,$font_url,$size=25,$color=array(0=>0,1=>0,2=>0,3=>0),$angle=0)
{
    $arr = array(0,1,2);
    if(!in_array($price_num,$arr))
    {
        return false;
    }
    //$price_num 代表商品的价格小数点前的位数，0为1位数，1为2位数，2为三位数
    $local = array(
        'x'=>640-$price_num*10,
        'y'=>660);
    $image = new Image($src);
    $image->fontMark($content,$font_url,$size,$color,$local,$angle);
    $image->save($merge_pic);
    return true;
}
/**
 **********************************************************
 * ************************以下为测试函数代码***************
 * *******************************************************
 */
function drawIcon()
{
    $str = '▂▂';
    $n = 4;
    echo "<div style='font-weight: bolder;font-size: 21px;'>&nbsp;&nbsp;RM45.50</div>";
    for($i = 0; $i < $n; $i++)
    {
        /*for($m = 0; $m < $n-$i;$m++)
        {
            echo "&nbsp;";
        }*/

        for($m = $n - $i; $m <= $n;$m++)
        {
            if($m == 0)
            {
                continue;
            }
            echo "&nbsp;&nbsp;";
        }

        for($j = $i; $j <= $n;$j++)
        {
            echo $str;
        }

        echo "<br/>";
    }
}

function make_tm()
{
    $text= '呵呵呵呵';
    $block = imagecreatetruecolor(170,170);//建立一个画板
    $bg = imagecolorallocatealpha($block , 0 , 0 , 0 , 127);//拾取一个完全透明的颜色，不要用imagecolorallocate拾色
    $color = imagecolorallocate($block,255,0,0); //字体拾色
    imagealphablending($block , false);//关闭混合模式，以便透明颜色能覆盖原画板
    imagefill($block , 0 , 0 , $bg);//填充
    imagefttext($block,12,0,10,20,$color,'src/images/font.ttf',$text);
    imagesavealpha($block , true);//设置保存PNG时保留透明通道信息
    header("content-type:image/png");
    imagepng($block);//生成图片
    imagedestroy($block);

}