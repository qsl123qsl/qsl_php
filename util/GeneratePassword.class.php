<?php  
/** Generate Password class，根据指定规则生成password 
*   Date:   2018/6/13
*   Author: shilin.qu 
*   Ver:    1.0 
* 
*   Func: 
*   public  batchGenerate 批量生成密码 
*   private generate      生成单个密码 
*   private getLetter     获取字母   
*   private getNumber     获取数字 
*   private getSpecial    获取特殊字符 
*/  
  
class GeneratePassword{ // class start  
  
    // 密码的规则 default  
    private $_rule = array(  
            'letter' => 1,  
            'number' => 1,  
            'special' => 1  
    );  
  
    private $_length = 8;                 // 密码长度  
    private $_num = 1;                    // 密码数量  
    private $_special = '!@#$%^&*()_+=-'; //允许的特殊字符  
  
  
    /** 初始化 
    * @param int    $length  密码长度 
    * @param int    $num     密码数量 
    * @param Array  $rule    密码规则 
    * @param String $special 允许的特殊字符 
    */  
    public function __construct($length=8, $num=1, $rule=array(), $special=''){  
  
        if(isset($length) && is_numeric($length) && $length>=4 && $length<=50){ // 长度  
            $this->_length = $length;  
        }  
  
        if(isset($num) && is_numeric($num) && $num>0 && $num<=100){ // 数量  
            $this->_num = $num;  
        }  
  
        if(isset($special) && is_string($special) && $special!=''){ // 特殊字符  
            $this->_special = $special;  
        }  
  
        if($rule){ // 规则  
  
            $t_rule = array();  
  
            if(isset($rule['letter']) && in_array($rule['letter'], array(1,2,3,4,5))){ // 1:可选用 2:必须 3:必须小写 4:必须大写 5:大小写都必须  
                $t_rule['letter'] = $rule['letter'];  
            }  
  
            if(isset($rule['number']) && in_array($rule['number'], array(1,2))){ // 1:可选用 2:必须  
                $t_rule['number'] = $rule['number'];  
            }  
  
            if(isset($rule['special']) && in_array($rule['special'], array(1,2))){ // 1:可选用 2:必须  
                $t_rule['special'] = $rule['special'];  
            }  
  
            if($t_rule){  
                $this->_rule = $t_rule;  
            }  
  
        }  
  
    }  
  
  
    /** 批量生成密码 
    * @return Array 
    */  
    public function batchGenerate(){  
  
        $passwords = array();  
  
        for($i=0; $i<$this->_num; $i++){  
            array_push($passwords, $this->generate());  
        }  
  
        return $passwords;  
    }  
  
  
    /** 生成单个密码 
    * @return String 
    */  
    private function generate(){  
  
        $password = '';  
        $pool = '';  
        $force_pool = '';  
  
        if(isset($this->_rule['letter'])){  
  
            $letter = $this->getLetter();  
  
            switch($this->_rule['letter']){  
                case 2:  
                    $force_pool .= substr($letter, mt_rand(0,strlen($letter)-1), 1);  
                    break;  
  
                case 3:  
                    $force_pool .= strtolower(substr($letter, mt_rand(0,strlen($letter)-1), 1));  
                    $letter = strtolower($letter);  
                    break;  
  
                case 4:  
                    $force_pool .= strtoupper(substr($letter, mt_rand(0,strlen($letter)-1), 1));  
                    $letter = strtoupper($letter);  
                    break;  
  
                case 5:  
                    $force_pool .= strtolower(substr($letter, mt_rand(0,strlen($letter)-1), 1));  
                    $force_pool .= strtoupper(substr($letter, mt_rand(0,strlen($letter)-1), 1));  
                    break;  
            }  
  
            $pool .= $letter;  
  
        }  
  
        if(isset($this->_rule['number'])){  
  
            $number = $this->getNumber();  
  
            switch($this->_rule['number']){  
                case 2:  
                    $force_pool .= substr($number, mt_rand(0,strlen($number)-1), 1);  
                    break;  
            }  
  
            $pool .= $number;  
  
        }  
  
        if(isset($this->_rule['special'])){  
  
            $special = $this->getSpecial();  
  
            switch($this->_rule['special']){  
                case 2:  
                    $force_pool .= substr($special, mt_rand(0,strlen($special)-1), 1);  
                    break;  
            }  
  
            $pool .= $special;  
        }  
  
        $pool = str_shuffle($pool); // 随机打乱  
  
        $password = str_shuffle($force_pool. substr($pool, 0, $this->_length-strlen($force_pool))); // 再次随机打乱  
  
        return $password;  
  
    }  
  
  
    /** 字母 */  
    private function getLetter(){  
        $letter = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';  
        return $letter;  
    }  
  
  
    /** 数字 */  
    private function getNumber(){  
        $number = '1234567890';  
        return $number;  
    }  
  
  
    /** 特殊字符 */  
    private function getSpecial(){  
        $special = $this->_special;  
        return $special;  
    }  
  
} // class end  


$rule = array(  
    'letter' => 5, // 必须含有大小写字母  
    'number' => 2, // 必须含有数字  
    'special' => 2 // 必须含有特殊字符  
);  
  
$special = '!@#$%_-';  
  
$obj = new GeneratePassword(8, 10, $rule, $special);  
$passwords = $obj->batchGenerate();  
  
echo implode('<br>', $passwords); 