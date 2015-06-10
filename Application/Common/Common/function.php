<?php
//function imagebmp(&$im, $filename = '', $bit = 8, $compression = 0)
//{
//if (!in_array($bit, array(1, 4, 8, 16, 24, 32)))
//{
//$bit = 8;
//}
//else if ($bit == 32) // todo:32 bit
//{
//$bit = 24;
//}
//
//$bits = pow(2, $bit);
//
//// 调整调色板
//imagetruecolortopalette($im, true, $bits);
//$width = imagesx($im);
//$height = imagesy($im);
//$colors_num = imagecolorstotal($im);
//
//if ($bit <= 8)
//{
//// 颜色索引
//$rgb_quad = '';
//for ($i = 0; $i < $colors_num; $i ++)
//{
//$colors = imagecolorsforindex($im, $i);
//$rgb_quad .= chr($colors['blue']) . chr($colors['green']) . chr($colors['red']) . "\0";
//}
//
//// 位图数据
//$bmp_data = '';
//
//// 非压缩
//if ($compression == 0 || $bit < 8)
//{
//if (!in_array($bit, array(1, 4, 8)))
//{
//$bit = 8;
//}
//
//$compression = 0;
//
//// 每行字节数必须为4的倍数，补齐。
//$extra = '';
//$padding = 4 - ceil($width / (8 / $bit)) % 4;
//if ($padding % 4 != 0)
//{
//$extra = str_repeat("\0", $padding);
//}
//
//for ($j = $height - 1; $j >= 0; $j --)
//{
//$i = 0;
//while ($i < $width)
//{
//$bin = 0;
//$limit = $width - $i < 8 / $bit ? (8 / $bit - $width + $i) * $bit : 0;
//
//for ($k = 8 - $bit; $k >= $limit; $k -= $bit)
//{
//$index = imagecolorat($im, $i, $j);
//$bin |= $index << $k;
//$i ++;
//}
//
//$bmp_data .= chr($bin);
//}
//
//$bmp_data .= $extra;
//}
//}
//// RLE8 压缩
//else if ($compression == 1 && $bit == 8)
//{
//for ($j = $height- 1; $j >= 0; $j --)
//{
//$last_index = "\0";
//$same_num = 0;
//for ($i = 0; $i <= $width; $i ++)
//{
//$index = imagecolorat($im, $i, $j);
//if ($index !== $last_index || $same_num > 255)
//{
//if ($same_num != 0)
//{
//$bmp_data .= chr($same_num) . chr($last_index);
//}
//
//$last_index = $index;
//$same_num = 1;
//}
//else
//{
//$same_num ++;
//}
//}
//
//$bmp_data .= "\0\0";
//}
//
//$bmp_data .= "\0\1";
//}
//
//$size_quad = strlen($rgb_quad);
//$size_data = strlen($bmp_data);
//}
//else
//{
//// 每行字节数必须为4的倍数，补齐。
//$extra = '';
//$padding = 4 - ($width * ($bit / 8)) % 4;
//if ($padding % 4 != 0)
//{
//$extra = str_repeat("\0", $padding);
//}
//
//// 位图数据
//$bmp_data = '';
//
//for ($j = $height - 1; $j >= 0; $j --)
//{
//for ($i = 0; $i < $width; $i ++)
//{
//$index = imagecolorat($im, $i, $j);
//$colors = imagecolorsforindex($im, $index);
//
//if ($bit == 16)
//{
//$bin = 0 << $bit;
//
//$bin |= ($colors['red'] >> 3) << 10;
//$bin |= ($colors['green'] >> 3) << 5;
//$bin |= $colors['blue'] >> 3;
//
//$bmp_data .= pack('v', $bin);
//}
//else
//{
//$bmp_data .= pack('c*', $colors['blue'], $colors['green'], $colors['red']);
//}
//
//// todo: 32bit;
//}
//
//$bmp_data .= $extra;
//}
//
//$size_quad = 0;
//$size_data = strlen($bmp_data);
//$colors_num = 0;
//}
//
//// 位图文件头
//$file_header = 'BM' . pack("V3", 54 + $size_quad + $size_data, 0, 54 + $size_quad);
//
//// 位图信息头
//$info_header = pack("V3v2V*", 0x28, $width, $height, 1, $bit, $compression, $size_data, 0, 0, $colors_num, 0);
//
//// 写入文件
//if ($filename != '')
//{
//$fp = fopen("test.bmp", "wb");
//
//fwrite($fp, $file_header);
//fwrite($fp, $info_header);
//fwrite($fp, $rgb_quad);
//fwrite($fp, $bmp_data);
//fclose($fp);
//}
//return 1;
//}
//function imagecreatefrombmp( $filename ){
//    if ( !$f1 = fopen( $filename, "rb" ) )
//        return FALSE;
//     
//    $FILE = unpack( "vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread( $f1, 14 ) );
//    if ( $FILE['file_type'] != 19778 )
//        return FALSE;
//     
//    $BMP = unpack( 'Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' . '/Vcompression/Vsize_bitmap/Vhoriz_resolution' . '/Vvert_resolution/Vcolors_used/Vcolors_important', fread( $f1, 40 ) );
//    $BMP['colors'] = pow( 2, $BMP['bits_per_pixel'] );
//    if ( $BMP['size_bitmap'] == 0 )
//        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
//    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
//    $BMP['bytes_per_pixel2'] = ceil( $BMP['bytes_per_pixel'] );
//    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
//    $BMP['decal'] -= floor( $BMP['width'] * $BMP['bytes_per_pixel'] / 4 );
//    $BMP['decal'] = 4 - (4 * $BMP['decal']);
//    if ( $BMP['decal'] == 4 )
//        $BMP['decal'] = 0;
//     
//    $PALETTE = array();
//    if ( $BMP['colors'] < 16777216 ){
//        $PALETTE = unpack( 'V' . $BMP['colors'], fread( $f1, $BMP['colors'] * 4 ) );
//    }
//     
//    $IMG = fread( $f1, $BMP['size_bitmap'] );
//    $VIDE = chr( 0 );
//     
//    $res = imagecreatetruecolor( $BMP['width'], $BMP['height'] );
//    $P = 0;
//    $Y = $BMP['height'] - 1;
//    while( $Y >= 0 ){
//        $X = 0;
//        while( $X < $BMP['width'] ){
//            if ( $BMP['bits_per_pixel'] == 32 ){
//                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) );
//                $B = ord(substr($IMG, $P,1));
//                $G = ord(substr($IMG, $P+1,1));
//                $R = ord(substr($IMG, $P+2,1));
//                $color = imagecolorexact( $res, $R, $G, $B );
//                if ( $color == -1 )
//                    $color = imagecolorallocate( $res, $R, $G, $B );
//                $COLOR[0] = $R*256*256+$G*256+$B;
//                $COLOR[1] = $color;
//            }elseif ( $BMP['bits_per_pixel'] == 24 )
//                $COLOR = unpack( "V", substr( $IMG, $P, 3 ) . $VIDE );
//            elseif ( $BMP['bits_per_pixel'] == 16 ){
//                $COLOR = unpack( "n", substr( $IMG, $P, 2 ) );
//                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
//            }elseif ( $BMP['bits_per_pixel'] == 8 ){
//                $COLOR = unpack( "n", $VIDE . substr( $IMG, $P, 1 ) );
//                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
//            }elseif ( $BMP['bits_per_pixel'] == 4 ){
//                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
//                if ( ($P * 2) % 2 == 0 )
//                    $COLOR[1] = ($COLOR[1] >> 4);
//                else
//                    $COLOR[1] = ($COLOR[1] & 0x0F);
//                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
//            }elseif ( $BMP['bits_per_pixel'] == 1 ){
//                $COLOR = unpack( "n", $VIDE . substr( $IMG, floor( $P ), 1 ) );
//                if ( ($P * 8) % 8 == 0 )
//                    $COLOR[1] = $COLOR[1] >> 7;
//                elseif ( ($P * 8) % 8 == 1 )
//                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
//                elseif ( ($P * 8) % 8 == 2 )
//                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
//                elseif ( ($P * 8) % 8 == 3 )
//                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
//                elseif ( ($P * 8) % 8 == 4 )
//                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
//                elseif ( ($P * 8) % 8 == 5 )
//                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
//                elseif ( ($P * 8) % 8 == 6 )
//                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
//                elseif ( ($P * 8) % 8 == 7 )
//                    $COLOR[1] = ($COLOR[1] & 0x1);
//                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
//            }else
//                return FALSE;
//            imagesetpixel( $res, $X, $Y, $COLOR[1] );
//            $X++;
//            $P += $BMP['bytes_per_pixel'];
//        }
//        $Y--;
//        $P += $BMP['decal'];
//    }
//    fclose( $f1 );
//     
//    return $res;
//}
// 过滤XSS
function clearXSS($string)
{
	require_once './htmlpurifier/HTMLPurifier.auto.php';
	// 生成配置对象
	$_clean_xss_config = HTMLPurifier_Config::createDefault();
	// 以下就是配置：
	$_clean_xss_config->set('Core.Encoding', 'UTF-8');
	// 设置允许使用的HTML标签
	$_clean_xss_config->set('HTML.Allowed','div,b,strong,i,em,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]');
	// 设置允许出现的CSS样式属性
	$_clean_xss_config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align');
	// 设置a标签上是否允许使用target="_blank"
	$_clean_xss_config->set('HTML.TargetBlank', TRUE);
	// 使用配置生成过滤用的对象
	$_clean_xss_obj = new HTMLPurifier($_clean_xss_config);
	// 过滤字符串
	return $_clean_xss_obj->purify($string);
}
function showImage($image, $width='', $height='')
{
	if(!$image)
		return ;
	// 无论showImage调用多少次，C函数只调用了一次
	static $prefix = null;
	if($prefix === null)
		$prefix = C('IMAGE_PREFIX');
	if($width)
		$width = " width='$width'";
	if($height)
		$height = " height='$height'";
	echo "<img $width $height src='{$prefix}{$image}' />";
}
/**
 * 删除一个数组中所有的图片
 *
 * @param unknown_type $image
 */
function deleteImage($image = array())
{
	$savePath = C('IMAGE_SAVE_PATH');
	foreach ($image as $v)
	{
		unlink($savePath . $v);
	}
}
/**
 * 上传图片并生成缩略图
 * 用法：
 * $ret = uploadOne('logo', 'Goods', array(
			array(600, 600),
			array(300, 300),
			array(100, 100),
		));
	返回值：
	if($ret['ok'] == 1)
		{
			$ret['images'][0];   // 原图地址
			$ret['images'][1];   // 第一个缩略图地址
			$ret['images'][2];   // 第二个缩略图地址
			$ret['images'][3];   // 第三个缩略图地址
		}
		else 
		{
			$this->error = $ret['error'];
			return FALSE;
		}
 *
 */
function uploadOne($imgName, $dirName, $thumb = array())
{
	// 上传LOGO
	if(isset($_FILES[$imgName]) && $_FILES[$imgName]['error'] == 0)
	{
		$rootPath = C('IMAGE_SAVE_PATH');
		$upload = new \Think\Upload(array(
			'rootPath' => $rootPath,
		));// 实例化上传类
		$upload->maxSize = (int)C('IMG_maxSize') * 1024 * 1024;// 设置附件上传大小
		$upload->exts = C('IMG_exts');// 设置附件上传类型
		/// $upload->rootPath = $rootPath; // 设置附件上传根目录
		$upload->savePath = $dirName . '/'; // 图片二级目录的名称
		// 上传文件 
		// 上传时指定一个要上传的图片的名称，否则会把表单中所有的图片都处理，之后再想其他图片时就再找不到图片了
		$info   =   $upload->upload(array($imgName=>$_FILES[$imgName]));
		if(!$info)
		{
			return array(
				'ok' => 0,
				'error' => $upload->getError(),
			);
		}
		else
		{
			$ret['ok'] = 1;
		    $ret['images'][0] = $logoName = $info[$imgName]['savepath'] . $info[$imgName]['savename'];
		    // 判断是否生成缩略图
		    if($thumb)
		    {
		    	$image = new \Think\Image();
		    	// 循环生成缩略图
		    	foreach ($thumb as $k => $v)
		    	{
		    		$ret['images'][$k+1] = $info[$imgName]['savepath'] . 'thumb_'.$k.'_' .$info[$imgName]['savename'];
		    		// 打开要处理的图片
				    $image->open($rootPath.$logoName);
				    $image->thumb($v[0], $v[1])->save($rootPath.$ret['images'][$k+1]);
		    	}
		    }
		    return $ret;
		}
	}
}
function sendMail($to, $title, $content)
{
	require_once('./phpmailer/class.phpmailer.php');
    $mail = new PHPMailer();
    // 设置为要发邮件
    $mail->IsSMTP();
    // 是否允许发送HTML代码做为邮件的内容
    $mail->IsHTML(TRUE);
    $mail->CharSet='UTF-8';
    // 是否需要身份验证
    $mail->SMTPAuth=TRUE;
    /*  邮件服务器上的账号是什么 -> 到163注册一个账号即可 */
    $mail->From=C('MAIL_ADDRESS');
    $mail->FromName=C('MAIL_FROM');
    $mail->Host=C('MAIL_SMTP');
    $mail->Username=C('MAIL_LOGINNAME');
    $mail->Password=C('MAIL_PASSWORD');
    // 发邮件端口号默认25
    $mail->Port = 25;
    // 收件人
    $mail->AddAddress($to);
    // 邮件标题
    $mail->Subject=$title;
    // 邮件内容
    $mail->Body=$content;
    return($mail->Send());
}














