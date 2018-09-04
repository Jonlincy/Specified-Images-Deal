<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/8/10
 * Time: 14:22
 */

class downloadImg
{
    public function download($url,$path,$file_name)
    {
        $total_time = 0;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLINFO_CONNECT_TIME,30);

        //https的支持
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);

        $file = curl_exec($ch);
        // 检查是否有错误发生
        if(!curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            //echo '用时 ' . $info['total_time'] . ' 秒从 ' . $info['url']."下载图片";
            $total_time += $info['total_time'];
        }else{
            echo "There is error:".curl_error($ch);
        }
        curl_close($ch);
        $this->saveImage($file,$path,$file_name);
        return $total_time;
    }
    private function saveImage($file,$path,$file_name)
    {
        //$file_name = pathinfo($url,PATHINFO_BASENAME);//保留源文件名
        $resource = fopen($path.$file_name,'a');
        fwrite($resource,$file);
        fclose($resource);
    }
}