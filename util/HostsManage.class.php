<?php
 
/**
* 一个更改 hosts 的 PHP 脚本
* 有这样一个需求，我有多个网址希望在不同的时候对应不同的 ip，如果一个个配 hosts，这工作显得有些繁琐。写了如下脚本来批量更改。
*/
define('HOST_FILE', 'C:\Windows\System32\drivers\etc\hosts');
 
$hm = new HostManage(HOST_FILE);
 
$env = $argv[1];
if (empty($env)) {
        $hm->delAllGroup();
} else {
        $hm->addGroup($env);
}
 
class HostManage {
 
        // hosts 文件路径
        protected $file;
        // hosts 记录数组
        protected $hosts = array();
        // 配置文件路径，默认为 __FILE__ . '.ini';
        protected $configFile;
        // 从 ini 配置文件读取出来的配置数组
        protected $config = array();
        // 配置文件里面需要配置的域名
        protected $domain = array();
        // 配置文件获取的 ip 数据
        protected $ip = array();
 
        public function __construct($file, $config_file = null) {
                $this->file = $file;
                if ($config_file) {
                    $this->configFile = $config_file;
                } else {
                    $this->configFile = __FILE__ . '.ini';
                }
                $this->initHosts()
                        ->initCfg();
        }
 
        public function __destruct() {
                $this->write();
        }
 
        public function initHosts() {
                $lines = file($this->file);
                foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line) || $line[0] == '#') {
                                continue;
                        }
                        $item = preg_split('/\s+/', $line);
                        $this->hosts[$item[1]] = $item[0];
                }
                return $this;
        }
 
        public function initCfg() {
                if (! file_exists($this->configFile)) {
                        $this->config = array();
                } else {
                        $this->config = (parse_ini_file($this->configFile, true));
                }
                $this->domain = array_keys($this->config['domain']);
                $this->ip = $this->config['ip'];
                return $this;
        }
 
        /**
         * 删除配置文件里域的 hosts 
         */
        public function delAllGroup() {
                foreach ($this->domain as $domain) {
                        $this->delRecord($domain);
                }
        }
 
        /**
         * 将域配置为指定 ip
         * @param type $env
         * @return \HostManage
         */
        public function addGroup($env) {
                if (! isset($this->ip[$env])) {
                        return $this;
                }
                foreach ($this->domain as $domain) {
                        $this->addRecord($domain, $this->ip[$env]);
                }
                return $this;
        }
 
        /**
         * 添加一条 host 记录
         * @param type $ip
         * @param type $domain
         */
        function addRecord($domain, $ip) {
                $this->hosts[$domain] = $ip;
                return $this;
        }
 
        /**
         * 删除一条 host 记录
         * @param type $domain
         */
        function delRecord($domain) {
                unset($this->hosts[$domain]);
                return $this;
        }
 
        /**
         * 写入 host 文件
         */
        public function write() {
                $str = '';
                foreach ($this->hosts as $domain => $ip) {
                        $str .= $ip . "\t" . $domain . PHP_EOL;
                }
                file_put_contents($this->file, $str);
                return $this;
        }
 
}


//使用方法
//php hosts.php local # 域名将指向本机 127.0.0.1  
//php hosts.php dev # 域名将指向开发机 192.168.1.100  
//php hosts.php # 删除域名的 hosts 配置  