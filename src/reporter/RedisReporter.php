<?php

namespace think\tracing\reporter;

use Exception;
use Redis;
use RedisException;
use think\helper\Str;

class RedisReporter
{
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
                $this->config = array_merge([
                    'host'    => 'localhost',
                    'port'    => 6379,
                    'timeout' => 10,
                ], $config);

                $this->client = $this->createClient();
            }

            protected function createClient()
            {
                $config = $this->config;

                $client = new Redis;
                $ret    = $client->connect($config['host'], $config['port'], $config['timeout']);

                if ($ret === false) {
                    throw new \RuntimeException(sprintf('Failed to connect Redis server: %s', $client->getLastError()));
                }

                if (isset($config['password'])) {
                    $config['password'] = (string) $config['password'];
                    if ($config['password'] !== '') {
                        $client->auth($config['password']);
                    }
                }

                if (isset($config['database'])) {
                    $client->select($config['database']);
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
        $this->redis->rPush($this->key(), $spans);
    }

    public function pop()
    {
        return $this->redis->lPop($this->key());
    }
}
