<?php
/**
 * Created by PhpStorm.
 * User: Jonlinc
 * Motto : Missed is missed.
 * Date: 2018/8/9
 * Time: 11:13
 * 网页抓取图片分析程序（爬虫？？）
 */

header("Content-type:text/html;Charset=utf-8");
/** 获取网页内容有两种方式，一种是CURL，另一种是file_get_contents() */

require("downloadImg.php");

$param = 50; //商品类别
$again = 0; //重试一次？
$pic_num = 0;//新增图片个数
$pic_pos = 0;//最终的图片文件序号名




dealData($param,$again,$pic_num,$pic_pos);

/**
 * 处理数据
 * @param $param    小类ID
 * @param int $again    链接找寻不到，重试一次
 * @param int $pic_num  下载指定个数的商品图片（若等于零或大于最大个数则返回所有结果）
 * @param int $pic_pos  价格序号设置
 */
function dealData($param,$again = 0,$pic_num = 0,$pic_pos = 0)
{

    if(!is_numeric($again) || !is_numeric($pic_num) || !is_numeric($pic_pos))
    {
        echo "非法参数，终止执行！";
        return;
    }

    $res=getInfoFromWeb($param,$again,$pic_num,$pic_pos);

    $path = 'download/';
    $use_time = 0;
    set_time_limit(0);//取消请求超时时限

    if($res['total'] > 0)
    {
        clear_info();
        foreach ($res['info'] as $url => $fileName)
        {
            $fileName = $fileName . ".jpg";
            $use_time += downLoadPicture($url, $path, $fileName)+1;
            echo "==========图片".$fileName."已下载===========<br/>";
            flush();
            sleep(1);
        }
        echo "<br/>=======本次下载图片共耗时".$use_time."秒，共下载了".$res['total']."张图片。==========<br/>";
        echo "<script>alert('商品封面已全部下载，请进一步处理！');</script>";

    }else {

        //clear_info();
        echo "换个方式重试中....<br/>";
        $result = getInfoFromWeb($param, 1);

        if(empty($result['total']))
        {
            flush();
            sleep(1);
            echo "未找到任何数据,请检查路径！";
        }else{

            clear_info();
            foreach ($result['info'] as $url => $fileName)
            {
                $fileName = $fileName . ".jpg";
                $use_time += downLoadPicture($url, $path, $fileName)+1;
                echo "图片".$fileName."已下载<br/>";
                flush();
                sleep(1);
            }
            echo "本次下载图片共耗时".$use_time."秒，共下载了".$result['total']."张图片。<br/>";
            echo "<script>alert('商品封面已全部下载，请进一步处理！');</script>";
        }

    }
}


/**
 * 下载图片
 * @param $img_url 图片链接
 * @param $path 存放路径
 * @param $file_name 文件名
 * @return int 返回下载所用时间
 */
function downLoadPicture($img_url,$path,$file_name)
{

   $img = new downloadImg();

   $count_time = 0;//下载图片的耗时

   if(is_array($img_url))
   {
       foreach ($img_url as $url)
       {
           $count_time += ($img->download($url,$path,$file_name));
       }
   }else{
       $count_time = $img->download($img_url,$path,$file_name);//单张图片链接
   }

   return $count_time;
}

/**
 * 从网站获取商品图片链接和商品价格
 * @param $param
 * @param int $again 再试一次
 * @return mixed
 */

/**
 * 从网站获取商品图片链接和商品价格
 * @param $param 商品小类别的ID
 * @param int $again    重试一次
 * @param int $pic_num  下载指定个数的商品图片（若等于零或大于最大个数则返回所有结果）
 * @param int $pic_pos  价格序号设置
 * @return mixed    返回获取结果
 */
function getInfoFromWeb($param,$again = 0,$pic_num = 0,$pic_pos = 0)
{
    $url = 'http://www.igo2all.com/category.php?id='.$param;
    $web = 'http://www.igo2all.com/';
    $total = 0;//计算图片总数返回
    $price_count = 0;//价格张数
    try{
        $doc = new DOMDocument(); //创建DOM对象

        @$doc->loadHTML(file_get_contents($url)); //读取htmlDOM树
        $xpath = new DOMXPath($doc);

        //寻找图片和价格节点
        $img_rule = '/html/body/div[4]/div[2]/div[1]/div[2]/form/div/div/div/a/img';
        $price_rule = '/html/body/div[4]/div[2]/div[1]/div[2]/form/div/div/div/div/div[3]/div';
        if($again > 0)
        {
            //没有品牌时寻找的节点应该改变
            $img_rule = '/html/body/div[4]/div[2]/div[1]/div[1]/form/div/div/div/a/img';
            $price_rule = '/html/body/div[4]/div[2]/div[1]/div[1]/form/div/div/div/div/div[3]/div';
        }
        $goods_img = $xpath->query($img_rule);
        $goods_price = $xpath->query($price_rule);
    }catch (Exception $e)
    {
        print $e->getMessage();
        exit();
    }

    $img_arr = array();
    $price_arr = array();

    foreach ($goods_img as $url)
    {
        //获取商品图片链接
        $total ++;
        $link = trim($url->getAttribute('src'));
        array_push($img_arr,$web.$link);
        if($pic_num > 0 && $pic_num == $total) //指定下载张数
        {
            break;
        }
    }

    $count = $pic_pos > 0 ? $pic_pos + 1 : $total + 1; //用于倒序文件名

    foreach ($goods_price as $p)
    {
        //获取商品价格
        $count --;
        $price_count ++;
        $price = trim($p->nodeValue);
        $price = substr($price,2);
        if($count < 10)
        {
            $count = '0'.$count;
        }
        $price = $count."_".$price; //将价格作为文件名
        array_push($price_arr,$price);
        if($pic_num > 0 && $pic_num == $price_count)    //对应指定下载张数的价格
        {
            break;
        }
    }

    //倒序数组，使得图片可符合网站顺序排列
   // $img_arr = array_reverse($img_arr);
   // $price_arr = array_reverse($price_arr);

    $result['total'] = $total;
    $result['info'] = array_combine($img_arr,$price_arr);//合并数组
    return $result;
}

/**
 * 清除之前的信息，实现动态显示信息
 * 在输出时需要刷新缓存flush()和控制速度sleep()
 */
function clear_info()
{
    set_time_limit(0); //在有关数据库的大量数据的时候，可以将其设置为0，表示无限制。
    ob_end_clean(); //在循环输出前，要关闭输出缓冲区
    echo str_pad('',1024); //浏览器在接受输出一定长度内容之前不会显示缓冲输出，这个长度值 IE是256，火狐是1024，不会出现上下的拉动条。
}



















exit;
$url = "http://www.igo2all.com/category.php?id=35";

$res_txt = 'src/url.txt';

if(!file_exists($res_txt))
{
    $txt = fopen($res_txt,"w");
    fclose($txt);
    echo "The text file ".$res_txt." is created success";
}
if(filesize($res_txt) != 0)
{
    $txt = fopen($res_txt,"r") or die("文件打开错误！");
    echo fread($txt,filesize($res_txt));
    fclose($txt);
}else{
    $code = file_get_contents($url);
    if(empty($code))
    {
        echo "未获取到任何内容";
        return false;
    }
    file_put_contents($res_txt,$code);
}

//echo $code;



function curl($url)
{

/**
方式一
使用CURL的PHP扩展完成一个HTTP请求的发送一般有以下几个步骤：
初始化连接句柄；
设置CURL选项；
执行并获取结果；
释放CURL连接句柄
 **/
    //1、初始化curl句柄
    $ch = curl_init();

    //2、设置CURL选项
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_HEADER,0);

    //3、执行并获取网页内容
    $output = curl_exec($ch);
    if($output === FALSE)//判断输出是否为FALSE用的是全等号，这是为了区分返回空串和出错的情况
    {
        $output = "CURL错误：".curl_error($ch);
    }
    //4、关闭CURL连接句柄
    curl_close($ch);
    return $output;
}