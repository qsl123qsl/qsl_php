<?php
/**
 *  Redis锁操作类
 *  Date:   2016-06-30
 *  Author: shilin.qu
 *  Ver:    1.0
 *
 *  Func:
 *  public  lock    获取锁
 *  public  unlock  释放锁
 *  private connect 连接
 */
class RedisLock { // class start

    private $_config;
    private $_redis;

    /**
     * 初始化
     * @param Array $config redis连接设定
     */
    public function __construct($config=array()){
        $this->_config = $config;
        $this->_redis = $this->connect();
    }

    /**
     * 获取锁
     * @param  String  $key    锁标识
     * @param  Int     $expire 锁过期时间
     * @return Boolean
     */
    public function lock($key, $expire=5){
        $is_lock = $this->_redis->setnx($key, time()+$expire);

        // 不能获取锁
        if(!$is_lock){

            // 判断锁是否过期
            $lock_time = $this->_redis->get($key);

            // 锁已过期，删除锁，重新获取
            if(time()>$lock_time){
                $this->unlock($key);
                $is_lock = $this->_redis->setnx($key, time()+$expire);
            }
        }

        return $is_lock? true : false;
    }

    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public function unlock($key){
        return $this->_redis->del($key);
    }

    /**
     * 创建redis连接
     * @return Link
     */
    private function connect(){
        try{
            $redis = new Redis();
            $redis->connect($this->_config['host'],$this->_config['port'],$this->_config['timeout'],$this->_config['reserved'],$this->_config['retry_interval']);
            if(empty($this->_config['auth'])){
                $redis->auth($this->_config['auth']);
            }
            $redis->select($this->_config['index']);
        }catch(RedisException $e){
            throw new Exception($e->getMessage());
            return false;
        }
        return $redis;
    }

} // class end


//demo.php

$config = array(
    'host' => 'localhost',
    'port' => 6379,
    'index' => 0,
    'auth' => '',
    'timeout' => 1,
    'reserved' => NULL,
    'retry_interval' => 100,
);

// 创建redislock对象
$oRedisLock = new RedisLock($config);

// 定义锁标识
$key = 'mylock';

// 获取锁
$is_lock = $oRedisLock->lock($key, 10);

if($is_lock){
    echo 'get lock success<br>';
    echo 'do sth..<br>';
    sleep(5);
    echo 'success<br>';
    $oRedisLock->unlock($key);

// 获取锁失败
}else{
    echo 'request too frequently<br>';
}


/*
*
测试方法： 
打开两个不同的浏览器,同时在A,B中访问demo.php 
如果先访问的会获取到锁 
输出 
get lock success 
do sth.. 
success

另一个获取锁失败则会输出request too frequently

保证同一时间只有一个访问有效，有效限制并发访问。 


为了避免系统突然出错导致死锁，所以在获取锁的时候增加一个过期时间，如果已超过过期时间，即使是锁定状态都会释放锁，避免死锁导致的问题。 
*
*/