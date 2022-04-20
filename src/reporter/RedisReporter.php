<?php

namespace think\tracing\reporter;

use Exception;
use Redis;
use RedisException;
use think\helper\Str;

class RedisReporter
{
    protected $options = [
        'host'       => '127.0.0.1',
        'port'       => 6379,
        'password'   => '',
        'select'     => 0,
        'timeout'    => 0,
        'expire'     => 0,
        'persistent' => false,
        'prefix'     => '',
    ];

    protected $name;

    /** @var Redis */
    protected $redis;

    public function __construct($name, $redis)
    {
        $this->name  = $name;
        $this->redis = $redis;
    }

    public static function __make($name, $config)
    {
        if (!extension_loaded('redis')) {
            throw new Exception('redis扩展未安装');
        }

        $redis = new class($config) {
            protected $config;
            protected $client;

            public function __construct($config)
            {
                $this->config = $config;
                $this->client = $this->createClient();
            }

            protected function createClient()
            {
                $config = $this->config;
                $func   = $config['persistent'] ? 'pconnect' : 'connect';

                $client = new Redis;
                $client->$func($config['host'], $config['port'], $config['timeout']);

                if ('' != $config['password']) {
                    $client->auth($config['password']);
                }

                if (0 != $config['select']) {
                    $client->select($config['select']);
                }
                return $client;
            }

            public function __call($name, $arguments)
            {
                try {
                    return call_user_func_array([$this->client, $name], $arguments);
                } catch (RedisException $e) {
                    if (Str::contains($e->getMessage(), 'went away')) {
                        $this->client = $this->createClient();
                    }

                    throw $e;
                }
            }
        };

        return new self($name, $redis);
    }

    protected function key()
    {
        return "tracing:{$this->name}:spans";
    }

    public function push(string $spans)
    {
        $this->redis->lPush($this->key(), $spans);
    }

    public function pop()
    {
        [, $spans] = $this->redis->brPop($this->key(), 0);
        return $spans;
    }
}
