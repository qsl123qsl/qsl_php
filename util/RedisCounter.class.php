<?php
/**
 * PHP基于Redis计数器类
 * Date:    2017-10-28
 * Author:  fdipzone
 * Version: 1.0
 *
 * Descripton:
 * php基于Redis实现自增计数，主要使用redis的incr方法，并发执行时保证计数自增唯一。
 *
 * Func:
 * public  incr    执行自增计数并获取自增后的数值
 * public  get     获取当前计数
 * public  reset   重置计数
 * private connect 创建redis连接
 */
class RedisCounter{ // class start

    private $_config;
    private $_redis;

    /**
     * 初始化
     * @param Array $config redis连接设定
     */
    public function __construct($config){
        $this->_config = $config;
        $this->_redis = $this->connect();
    }

    /**
     * 执行自增计数并获取自增后的数值
     * @param  String $key  保存计数的键值
     * @param  Int    $incr 自增数量，默认为1
     * @return Int
     */
    public function incr($key, $incr=1){
        return intval($this->_redis->incr($key, $incr));
    }

    /**
     * 获取当前计数
     * @param  String $key 保存计数的健值
     * @return Int
     */
    public function get($key){
        return intval($this->_redis->get($key));
    }

    /**
     * 重置计数
     * @param  String  $key 保存计数的健值
     * @return Int
     */
    public function reset($key){
        return $this->_redis->delete($key);
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
// redis连接设定
$config = array(
    'host' => 'localhost',
    'port' => 6379,
    'index' => 0,
    'auth' => '',
    'timeout' => 1,
    'reserved' => NULL,
    'retry_interval' => 100,
);

// 创建RedisCounter对象
$oRedisCounter = new RedisCounter($config);

// 定义保存计数的健值
$key = 'mycounter';

// 执行自增计数，获取当前计数，重置计数
echo $oRedisCounter->get($key).PHP_EOL; // 0
echo $oRedisCounter->incr($key).PHP_EOL; // 1
echo $oRedisCounter->incr($key, 10).PHP_EOL; // 11
echo $oRedisCounter->reset($key).PHP_EOL; // 1
echo $oRedisCounter->get($key).PHP_EOL; // 0 


/**
*
输出：

0
1
11
1
0

*/


//并发调用计数器，检查计数唯一性

Require 'RedisCounter.class.php';

// redis连接设定
$config = array(
    'host' => 'localhost',
    'port' => 6379,
    'index' => 0,
    'auth' => '',
    'timeout' => 1,
    'reserved' => NULL,
    'retry_interval' => 100,
);

// 创建RedisCounter对象
$oRedisCounter = new RedisCounter($config);

// 定义保存计数的健值
$key = 'mytestcounter';

// 执行自增计数并返回自增后的计数，记录入临时文件
file_put_contents('/tmp/mytest_result.log', $oRedisCounter->incr($key).PHP_EOL, FILE_APPEND);

//测试并发执行，我们使用ab工具进行测试，设置执行150次，15个并发。
//ab -c 15 -n 150 http://localhost/test.php

/*
执行结果：
ab -c 15 -n 150 http://localhost/test.php
This is ApacheBench, Version 2.3 <$Revision: 1554214 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking home.rabbit.km.com (be patient).....done


Server Software:        nginx/1.6.3
Server Hostname:        localhost
Server Port:            80

Document Path:          /test.php
Document Length:        0 bytes

Concurrency Level:      15
Time taken for tests:   0.173 seconds
Complete requests:      150
Failed requests:        0
Total transferred:      24150 bytes
HTML transferred:       0 bytes
Requests per second:    864.86 [#/sec] (mean)
Time per request:       17.344 [ms] (mean)
Time per request:       1.156 [ms] (mean, across all concurrent requests)
Transfer rate:          135.98 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    0   0.2      0       1
Processing:     3   16   3.2     16      23
Waiting:        3   16   3.2     16      23
Total:          4   16   3.1     17      23

Percentage of the requests served within a certain time (ms)
  50%     17
  66%     18
  75%     18
  80%     19
  90%     20
  95%     21
  98%     22
  99%     22
 100%     23 (longest request)

*/

//检查计数是否唯一
/*
生成的总计数
wc -l /tmp/mytest_result.log 
     150 /tmp/mytest_result.log

生成的唯一计数
sort -u /tmp/mytest_result.log | wc -l
     150
*/