<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/8/13
 * Time: 9:39
 */

header("Content-type:text/html;Charset=utf-8;");

require ("downloadImg.php");

$url = isset($_GET['url']) ? $_GET['url'] : "";
if(empty($url))
{
    echo "非法数值,缺少链接。";
    exit;
}else{

    $order = isset($_GET['order_entry_id']) ? $_GET['order_entry_id'] : "";//妈的，还分割
    if(empty($order))
    {
        echo "非法链接,缺少order参数。";
        exit;
    }
    $url = $url."&order_entry_id=".$order;

    $path = "C:/Users/admin/Desktop/Template/";
    if(!file_exists($path))
    {
        echo "存放路径不存在！";
        exit;
    }
    $result = get_detail_img($url,$path);
    echo "<br/>===============================================================<br/></br/>";
    echo "本次下载图片共耗时".round($result['time'],3)."秒，共下载了".$result['count']."张图片。";

}




/**
 * 从淘宝下载商品详情图片
 * @param $url 商品链接
 * @param $path 详情图存放路径
 * @return mixed 返回下载耗时和下载的图片链接以及文件数
 */
function get_detail_img($url,$path)
{
    $b_time = time_count();//开始处理时间
    $res = @file_get_contents($url);
    try{

        if($res === FALSE)
        {
            throw new Exception('链接地址错误!');
        }

    }catch (Exception $e)
    {
        echo $e->getMessage();
        return false;
    }

    $count = 0; //

    $pattern = '/<img alt=\"(.*?)\" (.*?)>/';

    //获取详情图的内容
    if(preg_match_all($pattern,$res,$content_matches))
    {
        $url_pattern = '/src="(.*?)"/';
        //clear_info();
        echo ">>>>>>>>>>>>>>>>>>>开始处理>>>>>>>>>>>>>>>>>><br/><br/>";
        set_time_limit(0);//取消请求超时时限
        foreach ($content_matches[0] as $value)
        {
            $count ++;
            //获取img标签中src的内容
            if(preg_match($url_pattern,$value,$url_matches))
            {
                //提取最后的图片链接
                $url = substr($url_matches[0],5,strlen($url_matches[0])-6);
                $fileName = $count.".jpg";
                //flush();
               // sleep(1);
                echo "获取图片链接：".$url."<br/><br/>";
                $load_time = downLoadPicture($url, $path, $fileName);
                echo "已成功下载图片：".$fileName."耗时约".$load_time."秒<br/><br/>";
            }
        }

    }else{//若以上常见的方式找不到商品图片，就将范围扩大


        /*$pattern = '/<img alt=\"(.*?)\" (.*?)>/';

        //获取详情图的内容
        if(preg_match_all($pattern,$res,$content_matches))
        {
            $url_pattern = '/src="(.*?)"/';
            //clear_info();
            echo ">>>>>>>>>>>>>>>>>>>图片标签变化请注意区分，开始处理>>>>>>>>>>>>>>>>>><br/><br/>";
            foreach ($content_matches[0] as $value)
            {
                $count ++;
                //获取img标签中src的内容
                if(preg_match($url_pattern,$value,$url_matches))
                {
                    //提取最后的图片链接
                    $url = substr($url_matches[0],5,strlen($url_matches[0])-6);
                    $fileName = $count.".jpg";
                    //flush();
                    // sleep(1);
                    echo "获取图片链接：".$url."<br/><br/>";
                    $load_time = downLoadPicture($url, $path, $fileName);
                    echo "已成功下载图片：".$fileName."耗时约".$load_time."秒<br/><br/>";
                }
            }

        }else{
            echo "正则表达式无法匹配到结果！";
        }*/

        echo "正则表达式无法匹配到结果！";
    }

    $e_time = time_count();//处理结束时间
    $spend_time = $e_time - $b_time;
    $result['time'] = $spend_time;
    $result['count'] =$count;

    return $result;
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
 * 清除之前的信息，实现动态显示信息
 * 在输出时需要刷新缓存flush()和控制速度sleep()
 */
function clear_info()
{
    set_time_limit(0); //在有关数据库的大量数据的时候，可以将其设置为0，表示无限制。
    ob_end_clean(); //在循环输出前，要关闭输出缓冲区
    echo str_pad('',1024); //浏览器在接受输出一定长度内容之前不会显示缓冲输出，这个长度值 IE是256，火狐是1024，不会出现上下的拉动条。
}

function time_count()
{
    list($begin,$end) = explode(" ",microtime());
    return ((float)$begin + (float)$end);
}