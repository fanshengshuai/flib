<?php

class DB_Shard {

    public static $rules = array('range' => 'DB_Shard_Range',
        'stats' => 'DB_Shard_Stats'
    );

    public function factory($options, $shardKey) {

        $rule = $options['rule'];
        $rules = self::$rules;

        $ruleKeys = $options['keys'];
        if (!is_array($options['keys'])) {
            $ruleKeys = array($options['keys']);
        }

        foreach ($ruleKeys as $key) {
            if (!$shardKey[$key]) {
                throw new DB_Shard_Exception('shardKey invalid');
            }
        }

        if (array_key_exists($rule, $rules)) {

            $class = $rules[$rule];
            return new $class($options, $shardKey);
        }

        throw new DB_Shard_Exception('Rule not found');
    }

}
