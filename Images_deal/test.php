<?php
/**
 * Created by PhpStorm.
 * User: Jonlinc
 * Motto : Missed is missed.
 * Date: 2018/8/10 0010
 * Time: 下午 21:41
 */
set_time_limit(0); //在有关数据库的大量数据的时候，可以将其设置为0，表示无限制。
ob_end_clean(); //在循环输出前，要关闭输出缓冲区
echo str_pad('',1024); //浏览器在接受输出一定长度内容之前不会显示缓冲输出，这个长度值 IE是256，火狐是1024，不会出现上下的拉动条。
for($i = 0; $i <= 100; $i++)
{
    echo $i."<br/>";
    flush(); //刷新输出缓冲
    sleep(1); //控制输出的速度。
}