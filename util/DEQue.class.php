<?php
/** php 双向队列。支持限定队列长度，输入受限，输出受限，及输出必须与输入同端几种设置
*   Date:   2014-04-30
*   Author: shilin.qu
*   Ver:    1.0
*
*   Func:
*   public  frontAdd     前端入列
*   public  frontRemove  前端出列
*   public  rearAdd      后端入列
*   pulbic  rearRemove   后端出列
*   public  clear        清空对列
*   public  isFull       判断对列是否已满
*   private getLength    获取对列长度
*   private setAddNum    记录入列,输出依赖输入时调用
*   private setRemoveNum 记录出列,输出依赖输入时调用
*   private checkRemove  检查是否输出依赖输入
*/

class DEQue{ // class start

    private $_queue = array(); // 对列
    private $_maxLength = 0;   // 对列最大长度，0表示不限
    private $_type = 0;        // 对列类型
    private $_frontNum = 0;    // 前端插入的数量
    private $_rearNum = 0;     // 后端插入的数量


    /** 初始化
    * @param $type       对列类型
    *                    1:两端均可输入输出
    *                    2:前端只能输入，后端可输入输出
    *                    3:前端只能输出，后端可输入输出
    *                    4:后端只能输入，前端可输入输出
    *                    5:后端只能输出，前端可输入输出
    *                    6:两端均可输入输出，在哪端输入只能从哪端输出
    * @param $maxlength  对列最大长度
    */
    public function __construct($type=1, $maxlength=0){
        $this->_type = in_array($type, array(1,2,3,4,5,6))? $type : 1;
        $this->_maxLength = intval($maxlength);
    }


    /** 前端入列
    * @param  Mixed   $data 数据
    * @return boolean
    */
    public function frontAdd($data=null){

        if($this->_type==3){ // 前端输入限制
            return false;
        }

        if(isset($data) && !$this->isFull()){

            array_unshift($this->_queue, $data);

            $this->setAddNum(1);

            return true;
        }

        return false;

    }


    /** 前端出列
    * @return Array
    */
    public function frontRemove(){

        if($this->_type==2){ // 前端输出限制
            return null;
        }

        if(!$this->checkRemove(1)){ // 检查是否依赖输入
            return null;
        }

        $data = null;

        if($this->getLength()>0){

            $data = array_shift($this->_queue);

            $this->setRemoveNum(1);

        }

        return $data;

    }


    /** 后端入列
    * @param  Mixed   $data 数据
    * @return boolean
    */
    public function rearAdd($data=null){

        if($this->_type==5){ // 后端输入限制
            return false;
        }

        if(isset($data) && !$this->isFull()){

            array_push($this->_queue, $data);

            $this->setAddNum(2);

            return true;

        }

        return false;
    }


    /** 后端出列
    * @return Array
    */
    public function rearRemove(){

        if($this->_type==4){ // 后端输出限制
            return null;
        }

        if(!$this->checkRemove(2)){ // 检查是否依赖输入
            return null;
        }

        $data = null;

        if($this->getLength()>0){

            $data = array_pop($this->_queue);

            $this->setRemoveNum(2);

        }

        return $data;

    }


    /** 清空对列
    * @return boolean
    */
    public function clear(){
        $this->_queue = array();
        $this->_frontNum = 0;
        $this->_rearNum = 0;
        return true;
    }


    /** 判断对列是否已满
    * @return boolean
    */
    public function isFull(){
        $bIsFull = false;
        if($this->_maxLength!=0 && $this->_maxLength==$this->getLength()){
            $bIsFull = true;
        }
        return $bIsFull;
    }


    /** 获取当前对列长度
    * @return int
    */
    private function getLength(){
        return count($this->_queue);
    }


    /** 记录入列,输出依赖输入时调用
    * @param int $endpoint 端点 1:front 2:rear
    */
    private function setAddNum($endpoint){
        if($this->_type==6){
            if($endpoint==1){
                $this->_frontNum ++;
            }else{
                $this->_rearNum ++;
            }
        }
    }


    /** 记录出列,输出依赖输入时调用
    * @param int $endpoint 端点 1:front 2:rear
    */
    private function setRemoveNum($endpoint){
        if($this->_type==6){
            if($endpoint==1){
                $this->_frontNum --;
            }else{
                $this->_rearNum --;
            }
        }
    }


    /** 检查是否输出依赖输入
    * @param int $endpoint 端点 1:front 2:rear
    */
    private function checkRemove($endpoint){
        if($this->_type==6){
            if($endpoint==1){
                return $this->_frontNum>0;
            }else{
                return $this->_rearNum>0;
            }
        }
        return true;
    }

} // class end

// 例子1  
  
$obj = new DEQue(); // 前后端都可以输入，无限长度  
  
$obj->frontAdd('a'); // 前端入列  
$obj->rearAdd('b');  // 后端入列  
$obj->frontAdd('c'); // 前端入列  
$obj->rearAdd('d');  // 后端入列  
  
// 入列后数组应为 cabd  
  
$result = array();  
  
$result[] = $obj->rearRemove(); // 后端出列  
$result[] = $obj->rearRemove(); // 后端出列  
$result[] = $obj->frontRemove(); // 前端出列  
$result[] = $obj->frontRemove(); // 前端出列  
  
print_r($result); // 出列顺序应为 dbca  
  
// 例子2  
$obj = new DEQue(3, 5); // 前端只能输出，后端可输入输出，最大长度5  
  
$insert = array();  
$insert[] = $obj->rearAdd('a');  
$insert[] = $obj->rearAdd('b');  
$insert[] = $obj->frontAdd('c'); // 因前端只能输出，因此这里会返回false  
$insert[] = $obj->rearAdd('d');  
$insert[] = $obj->rearAdd('e');  
$insert[] = $obj->rearAdd('f');  
$insert[] = $obj->rearAdd('g'); // 超过长度，返回false  
  
var_dump($insert);  
  
// 例子3  
$obj = new DEQue(6); // 输出依赖输入  
  
$obj->frontAdd('a');  
$obj->frontAdd('b');  
$obj->frontAdd('c');  
$obj->rearAdd('d');  
  
$result = array();  
$result[] = $obj->rearRemove();  
$result[] = $obj->rearRemove();  // 因为输出依赖输入，这个会返回NULL  
$result[] = $obj->frontRemove();  
$result[] = $obj->frontRemove();  
$result[] = $obj->frontRemove();  
  
var_dump($result);  