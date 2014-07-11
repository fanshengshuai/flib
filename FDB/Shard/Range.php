<?php

class DB_Shard_Range extends DB_Shard {

    public $db = array();

    public $shardKey;

    public $shards;

    public function __construct($options, $shardKey) {

        $this->shards = $options['shards'];
        $this->shardKey = $shardKey;

        $this->shard($options['keys'], $options['shards'], $shardKey);
    }

    public function shard($ruleKeys, $shards, $shardKey) {

        $shardField = key($shardKey);
        $shardValue = $shardKey[$key];

        // 在区间中查找
        foreach ($shards as $db) {

            // 查找匹配区间
            if ($shardValue >= $db['start'] && $shardValue <= $db['end']) {
                $this->db = $db;
            }
        }
    }

    public function getDB() {

        $db = array('dsn' => $this->db['dsn'],
            'user' => $this->db['user'],
            'password' => $this->db['password'],
            'charset' => $this->db['charset']
        );

        return $db;
    }

}
