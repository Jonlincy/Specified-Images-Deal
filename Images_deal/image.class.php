<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/30 0030
 * Time: 下午 22:23
 */

class Image{
    /**
     * 内存中的图片
    */
    private $image;
    /**
     * 图片的基本信息
     */
    private $info;
    /**
     * 打开一张图片，读取到内存中
    */
    public function __construct($src)//构造方法,通常用它执行一些有用的初始化任务。该方法无返回值。
    {
        $info = getimagesize($src);//$this->获取图片信息，然后放到private $info里面
        $this->info = array(
            'width'=>$info[0],
            'height'=>$info[1],
            'type'=>image_type_to_extension($info[2],false),
            'mime'=>$info['mime']
        );
        //$type = image_type_to_extension($this->info['2'],false);
        $fun = "imagecreatefrom{$this->info['type']}";
        $this->image = $fun($src);
    }
    /**
     * 操作图片（压缩）
     */
    public function thumb($width,$height)
    {
        $image_thumb = imagecreatetruecolor($width,$height);
        imagecopyresampled($image_thumb,$this->image,0,0,0,0,$width,$height,$this->info['width'],$this->info['height']);
        imagedestroy($this->image);
        $this->image = $image_thumb;//此时的$this->image是压缩的图片
    }

    /**
     * 操作图片 合成透明图片并保留透明度
     * @param $dst_im 原图片
     * @param $src_im 水印图片
     * @param $dst_x  原图起始位置横坐标
     * @param $dst_y  原图起始位置纵坐标
     * @param $src_x  水印图起始位置横坐标
     * @param $src_y  水印图起始位置纵坐标
     * @param $src_w  水印图片宽
     * @param $src_h  水印图片高
     * @param $pct    水印透明度
     */
    private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        $opacity = 100 - $opacity;

        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }

    /**
     * 操作图片（添加字体水印）
     */
    public function fontMark($content,$font_url,$size,$color,$local,$angle)
    {
        $col = imagecolorallocatealpha($this->image,$color[0],$color[1],$color[2],0);

        imagettftext($this->image,$size,$angle,$local['x'],$local['y'],$col,$font_url,$content);
    }
    /**
     * 操作图片（添加图片水印）
     */
    public function imageMark($source,$local,$alpha)
    {
        $info2 = getimagesize($source);
        $type2 = image_type_to_extension($info2[2],false);
        $fun2 = "imagecreatefrom{$type2}";
        $water = $fun2($source);
        //$bg = imagecolorallocatealpha($water , 0 , 255 , 0 , 0);
        //imagealphablending($water , false);//关闭混合模式，以便透明颜色能覆盖原画板
        //imagefill($water , 0 , 0 , $bg);//填充
        //imagecopymerge($this->image,$water,$local['x'],$local['y'],0,0,$info2[0],$info2[1],$alpha);
        $this->imagecopymerge_alpha($this->image,$water,$local['x'],$local['y'],0,0,$info2[0],$info2[1],$alpha);
        imagedestroy($water);
    }

    /**
     * 在浏览器中显示图片
     */
    public function show()
    {
        header('content-type:',$this->info['mime']);
        $funs = "image{$this->info['type']}";
        $funs($this->image);
    }
    /**
     * 保存处理后的图片到硬盘中
    */
    public function save($newname)
    {
        $funs = "image{$this->info['type']}";
        $funs($this->image,$newname.'.'.$this->info['type']);
    }
    /**
     * 销毁图片
     * 析构方法允许在销毁一个类之前执行的一些操作或完成一些功能，
     * 比如说关闭文件、释放结果集等。析构函数不能带有任何参数，
     * 其名称必须是 _destruct()
     * 和构造方法一样，PHP 不会在本类中自动的调用父类的析构方法。
     * 要执行父类的析构方法，必须在子类的析构方法体中手动调用 parent::__destruct()
     * 试图在析构函数中抛出一个异常会导致致命错误
    */

    public function __destruct()//析构函数
    {
        imagedestroy($this->image);
    }

}