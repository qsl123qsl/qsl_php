<?php
/**
     * 将xml转换为数组
     * @param $xml  需要转化的xml
     * @return mixed
     */
    function xml_to_array($xml)
    {
        $ob = simplexml_load_string($xml);
        $json = json_encode($ob);
        $array = json_decode($json, true);
        return $array;
    }

    /**
     * 将数组转化成xml
     * @param $data 需要转化的数组
     * @return string
     */
    function data_to_xml($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        $xml = '';
        foreach ($data as $key => $val) {
            if (is_null($val)) {
                $xml .= "<$key/>\n";
            } else {
                if (!is_numeric($key)) {
                    $xml .= "<$key>";
                }
                $xml .= (is_array($val) || is_object($val)) ? self::data_to_xml($val) : $val;
                if (!is_numeric($key)) {
                    $xml .= "</$key>";
                }
            }
        }
        return $xml;
    }

    /**
     * PHP post请求之发送XML数据
     * @param $url 请求的URL
     * @param $xmlData
     * @return mixed
     */
    function xml_post_request($url, $xmlData)
    {
        $header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlData);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * PHP post请求之发送Json对象数据
     *
     * @param $url 请求url
     * @param $jsonStr 发送的json字符串
     * @return array
     */
    function http_post_json($url, $jsonStr)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return array($httpCode, $response);
    }

    /**
     * PHP post请求之发送数组
     * @param $url
     * @param array $param
     * @return mixed
     * @throws Exception
     */
    function httpsPost($url, $param = array())
    {
        $ch = curl_init(); // 初始化一个 cURL 对象
        curl_setopt($ch, CURLOPT_URL, $url); // 设置需要抓取的URL
        curl_setopt($ch, CURLOPT_HEADER, 0); // // 设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        // 如果你想PHP去做一个正规的HTTP POST，设置这个选项为一个非零值。这个POST是普通的 application/x-www-from-urlencoded 类型，多数被HTML表单使用。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($param)); // 传递一个作为HTTP “POST”操作的所有数据的字符串。//http_build_query:生成 URL-encode 之后的请求字符串
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-type:application/x-www-form-urlencoded;charset=utf-8'
        ));
        $rtn = curl_exec($ch); // 运行cURL，请求网页
        if ($errno = curl_errno($ch)) {
            throw new Exception ('Curl Error(' . $errno . '):' . curl_error($ch));
        }
        curl_close($ch); // 关闭URL请求
        return $rtn; // 返回获取的数据
    }

    /**
     * 接收xml数据并转化成数组
     * @return array
     */
    function getRequestBean()
    {
        $bean = simplexml_load_string(file_get_contents('php://input')); // simplexml_load_string() 函数把 XML 字符串载入对象中。如果失败，则返回 false。
        $request = array();
        foreach ($bean as $key => $value) {
            $request [( string )$key] = ( string )$value;
        }
        return $request;
    }

    /**
     * 接收json数据并转化成数组
     * @return mixed
     */
    function getJsonData()
    {
        $bean = file_get_contents('php://input');
        $result = json_decode($bean, true);
        return $result;
    }

    /**
     * 翻译中英文字符串（调换位置）
     */
    function m_strrev($string)
    {
        $num = mb_strlen($string, 'utf-8');
        $new_string = "";
        for ($i = $num - 1; $i >= 0; $i--) {
            $char = mb_substr($string, $i, 1, 'utf-8');
            $new_string .= $char;
        }
        return $new_string;
    }

    /**
     * 判断当前服务器系统
     * @return string
     */
    function getOS()
    {
        if (PATH_SEPARATOR == ':') {
            return 'Linux';
        } else {
            return 'Windows';
        }
    }

    /**
     * 日志方法
     * @param $log
     */
    function writeLog($log)
    {
        $dir = __DIR__ . "/../Log/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $filename = $dir . date("Y-m-d") . ".log";
        file_put_contents($filename, date("Y-m-d H:i:s") . "\t" . $log . PHP_EOL, FILE_APPEND);
    }

     /**
     * 当前微妙数
     * @return number
     */
    function microtime_float() {
        list ( $usec, $sec ) = explode ( " ", microtime () );
        return (( float ) $usec + ( float ) $sec);
    }

     /**
     * 遍历文件夹
     * @param string $dir
     * @param boolean $all  true表示递归遍历
     * @return array
     */
    function scanfDir($dir='', $all = false, &$ret = array()){
        if ( false !== ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                if (!in_array($file, array('.', '..', '.git', '.gitignore', '.svn', '.htaccess', '.buildpath','.project'))) {
                    $cur_path = $dir . '/' . $file;
                    if (is_dir ( $cur_path )) {
                        $ret['dirs'][] =$cur_path;
                        $all && self::scanfDir( $cur_path, $all, $ret);
                    } else {
                        $ret ['files'] [] = $cur_path;
                    }
                }
            }
            closedir ( $handle );
        }
        return $ret;
    }

    /**
     * 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）
     * @param string $file 文件/目录
     * @return boolean
     */
    function is_writeable($file) {
        if (is_dir($file)){
            $dir = $file;
            if ($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        } else {
            if ($fp = @fopen($file, 'a+')) {
                @fclose($fp);
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }
        return $writeable;
    }


    /**
    * 下载远程图片
    * @param string $url 图片的绝对url
    * @param string $filepath 文件的完整路径（例如/www/images/test） ，此函数会自动根据图片url和http头信息确定图片的后缀名
    * @param string $filename 要保存的文件名(不含扩展名)
    * @return mixed 下载成功返回一个描述图片信息的数组，下载失败则返回false
    */
    function downloadImage($url, $filepath, $filename) {
        //服务器返回的头信息
        $responseHeaders = array();
        //原始图片名
        $originalfilename = '';
        //图片的后缀名
        $ext = '';
        $ch = curl_init($url);
        //设置curl_exec返回的值包含Http头
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置curl_exec返回的值包含Http内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置抓取跳转（http 301，302）后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //设置最多的HTTP重定向的数量
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
         
        //服务器返回的数据（包括http头信息和内容）
        $html = curl_exec($ch);
        //获取此次抓取的相关信息
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        if ($html !== false) {
        //分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
        $httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
        //倒数第二段是服务器最后一次response的http头
        $header = $httpArr[count($httpArr) - 2];
        //倒数第一段是服务器最后一次response的内容
        $body = $httpArr[count($httpArr) - 1];
        $header.="\r\n";
         
        //获取最后一次response的header信息
        preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
        if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                if (array_key_exists($i, $matches[2])) {
                    $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                }
            }
        }
        //获取图片后缀名
        if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
            $originalfilename = $matches[0];
            $ext = $matches[1];
        } else {
            if (array_key_exists('Content-Type', $responseHeaders)) {
                if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
                    $ext = $extmatches[1];
                }
            }
        }
        //保存文件
        if (!empty($ext)) {
            //如果目录不存在，则先要创建目录
            if(!is_dir($filepath)){
                mkdir($filepath, 0777, true);
            }
             
            $filepath .= '/'.$filename.".$ext";
            $local_file = fopen($filepath, 'w');
                if (false !== $local_file) {
                    if (false !== fwrite($local_file, $body)) {
                    fclose($local_file);
                    $sizeinfo = getimagesize($filepath);
                    return array('filepath' => realpath($filepath), 'width' => $sizeinfo[0], 'height' => $sizeinfo[1], 'orginalfilename' => $originalfilename, 'filename' => pathinfo($filepath, PATHINFO_BASENAME));
                    }
                }
            }
        }
        return false;
    }

    /**
    * 取得输入目录所包含的所有目录和文件
    * 以关联数组形式返回
    * author: flynetcn
    */
    function deepScanDir($dir)
    {
        $fileArr = array();
        $dirArr = array();
        $dir = rtrim($dir, '//');
        if(is_dir($dir)){
            $dirHandle = opendir($dir);
            while(false !== ($fileName = readdir($dirHandle))){
                $subFile = $dir . DIRECTORY_SEPARATOR . $fileName;
                if(is_file($subFile)){
                    $fileArr[] = $subFile;
                } elseif (is_dir($subFile) && str_replace('.', '', $fileName)!=''){
                    $dirArr[] = $subFile;
                    $arr = self::deepScanDir($subFile);
                    $dirArr = array_merge($dirArr, $arr['dir']);
                    $fileArr = array_merge($fileArr, $arr['file']);
                }
            }
            closedir($dirHandle);
        }
        return array('dir'=>$dirArr, 'file'=>$fileArr);
    }

    /**
    * 删除文件夹及其文件夹下所有文件
    */
    function deldir($dir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::deldir($fullpath);
                }
            }
        }
         
        closedir($dh);
        //删除当前文件夹：
        if(rmdir($dir)) {
            return true;
        } else {
            return false;
        }
    } 
