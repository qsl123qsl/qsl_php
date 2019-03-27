<?php
/** 字符编码转换类, ANSI、Unicode、Unicode big endian、UTF-8、UTF-8+Bom互相转换
*   Date:   2015-01-28
*   Author: shilin.qu
*   Ver:    1.0
*
*   Func:
*   public  convert       转换
*   private convToUtf8    把编码转为UTF-8编码
*   private convFromUtf8  把UTF-8编码转换为输出编码

四种常见文本文件编码方式

ANSI编码：

无文件头(文件编码开头标志性字节)

ANSI编码字母数字占一个字节，汉字占两个字节

回车换行符，单字节， 十六进制表示为0d  0a



UNICODE编码：

文件头，十六进制表示为FF FE

每一个字符都用两个字节编码

回车换行符， 双字节，十六进制表示为 000d  000a



Unicode big endian编码：

文件头十六进制表示为FE FF

后面编码是把字符的高位放在前面，低位放在后面，正好和Unicode编码颠倒

回车换行符，双字节，十六进制表示为0d00  0a00



UTF-8 编码：

文件头，十六进制表示为EF BB BF

UTF-8是Unicode的一种变长字符编码，数字、字母、回车、换行都用一个字节表示，汉字占3个字节

回车换行符，单字节，十六进制表示为0d 0a


*/

class CharsetConv{ // class start

    private $_in_charset = null;   // 源编码
    private $_out_charset = null;  // 输出编码
    private $_allow_charset = array('utf-8', 'utf-8bom', 'ansi', 'unicode', 'unicodebe');


    /** 初始化
    * @param String $in_charset  源编码
    * @param String $out_charset 输出编码
    */
    public function __construct($in_charset, $out_charset){

        $in_charset = strtolower($in_charset);
        $out_charset = strtolower($out_charset);

        // 检查源编码
        if(in_array($in_charset, $this->_allow_charset)){
            $this->_in_charset = $in_charset;
        }

        // 检查输出编码
        if(in_array($out_charset, $this->_allow_charset)){
            $this->_out_charset = $out_charset;
        }

    }


    /** 转换
    * @param  String $str 要转换的字符串
    * @return String      转换后的字符串
    */
    public function convert($str){

        $str = $this->convToUtf8($str);   // 先转为utf8
        $str = $this->convFromUtf8($str); // 从utf8转为对应的编码

        return $str;
    }


    /** 把编码转为UTF-8编码
    * @param  String $str 
    * @return String
    */
    private function convToUtf8($str){

        if($this->_in_charset=='utf-8'){ // 编码已经是utf-8，不用转
            return $str;
        }

        switch($this->_in_charset){
            case 'utf-8bom':
                $str = substr($str, 3);
                break;

            case 'ansi':
                $str = iconv('GBK', 'UTF-8//IGNORE', $str);
                break;

            case 'unicode':
                $str = iconv('UTF-16le', 'UTF-8//IGNORE', substr($str, 2));
                break;

            case 'unicodebe':
                $str = iconv('UTF-16be', 'UTF-8//IGNORE', substr($str, 2));
                break;

            default:
                break;
        }

        return $str;

    }


    /** 把UTF-8编码转换为输出编码
    * @param  String $str
    * @return String
    */
    private function convFromUtf8($str){

        if($this->_out_charset=='utf-8'){ // 输出编码已经是utf-8，不用转
            return $str;
        }

        switch($this->_out_charset){
            case 'utf-8bom':
                $str = "\xef\xbb\xbf".$str;
                break;

            case 'ansi':
                $str = iconv('UTF-8', 'GBK//IGNORE', $str);
                break;

            case 'unicode':
                $str = "\xff\xfe".iconv('UTF-8', 'UTF-16le//IGNORE', $str);
                break;

            case 'unicodebe':
                $str = "\xfe\xff".iconv('UTF-8', 'UTF-16be//IGNORE', $str);
                break;

            default:
                break;
        }

        return $str;

    }


} // class end

$str = file_get_contents('source/unicodebe.txt');  
  
$obj = new CharsetConv('unicodebe', 'utf-8bom');  
$response = $obj->convert($str);  
  
file_put_contents('response/utf-8bom.txt', $response, true);  