<?php

class DB_Shard_IndexTable extends DB_Shard {

    public $db = array();

    public $shardKey;

    public $shardConfigs;

    public function __construct($shardConfigs, $shardKey) {

        $this->shardConfigs = $shardConfigs;
        $this->shardKey = $shardKey;

        $this->shard($shardConfigs, $shardKey);
    }

    public function shard($shardConfigs, $shardKey) {

        $shardField = key($shardKey);
        $shardValue = $shardKey[$key];

        // 从索引表中查找
        $table = $shardConfigs['table'];
        $this->db['charset'] = $shardConfig['charset'];
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
