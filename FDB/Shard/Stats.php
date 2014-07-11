<?php

class DB_Shard_Stats extends DB_Shard {

    public $db = array();

    public $shardKey;

    public $options;

    public $table;

    public function __construct($options, $shardKey) {

        $this->options = $options;
        $this->shardKey = $shardKey;

        $this->shard($options['config'], $shardKey);
    }

    /**
     * shard
     * 分片处理
     *
     * @param  array $shards
     * @param  array $shardKey
     *
     * @return void
     */
    public function shard($shards, $shardKey) {

        $dbId = $shardKey['di_id'] % $shards['modulus'];
        $conditions = 'dc_db_id = ? AND dc_table_name = ? AND dc_start_date <= ?';
        $params = array($dbId, $this->options['table'], $shardKey['start_date']);

        try {
            $rows = DAO_MySql::findAll($shards['table'], $conditions, $params,
                array(),
                array('*'),
                array('dc_start_date' => 'DESC'));
            $db = $rows[0];
            if (!$db) {

                throw new DB_Shard_Exception('shard data not found');
            }

            $databasePre = array_shift(explode('_', $this->options['table']));

            // 数据库地址
            $hostIp = $db['dc_master_ip'];
            // 备用数据库地址
            $failoverIp = $db['dc_slave_ip'];
            if ('slave' == $shardKey['host_type']) {

                // 如果指定了读取从库
                $hostIp = $db['dc_slave_ip'];
                $failoverIp = $db['dc_master_ip'];
            } elseif ('master' == $shardKey['host_type']) {

                // 如果指定了读取主库
                $hostIp = $db['dc_master_ip'];
                $failoverIp = $db['dc_slave_ip'];
            } elseif (0 == ($this->_GETRandomByDiId($diId) % 2)) {

                // 随机调换主从IP，均衡负载
                list($hostIp, $failoverIp) = array($db['dc_slave_ip'], $db['dc_master_ip']);
            }

            $this->db['dsn'] = sprintf('mysql:dbname=%s_%03d;host=%s',
                $databasePre, $dbId, $hostIp);
            $this->db['user'] = $shards['user'];
            $this->db['password'] = $shards['password'];
            $this->db['charset'] = $shards['charset'];
            $this->db['persistent'] = $shards['persistent'];
            $this->db['failover'] = sprintf('mysql:dbname=%s_%03d;host=%s',
                $databasePre, $dbId, $failoverIp);

        } catch (Exception $e) {

            throw new DB_Shard_Exception($e);
        }

    }

    /**
     * getRandomByDiId
     * 根据站点ID和时间间隔生成随机数
     *
     * @param integer $diId 站点ID
     * @param integer $second 控制平均多长时间进行一次随机操作，单位(s)
     *
     * @return integer
     */
    protected function _GETRandomByDiId($diId = 0, $second = 3600) {

        $timestamp = time();
        // 通过站点ID、以及时间来生成一个随机数种子
        $seed = floor($timestamp / $second) + $diId;
        $md5 = md5($seed);
        // 把 md5 值的字串每一位转成 10 进制数，并求和，其结果作为随机数
        $random = 0;
        for ($i = 0; $i < 32; $i++) {
            $random = $random + hexdec($md5{$i});
        }

        return $random;

    }

    /**
     * getDB
     * 获取数据库配置
     *
     * @return void
     */
    public function getDB() {

        $db = array('dsn' => $this->db['dsn'],
            'user' => $this->db['user'],
            'password' => $this->db['password'],
            'charset' => $this->db['charset'],
            'persistent' => $this->db['persistent'],
            'failover' => $this->db['failover']
        );

        return $db;
    }

}
