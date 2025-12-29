<?php

namespace Services;

use Predis\Client;

class CacheService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'scheme' => 'tcp',
            'host'   => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port'   => $_ENV['REDIS_PORT'] ?? 6379,
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        ]);
    }

    public function get($key)
    {
        $value = $this->client->get($key);
        return $value ? json_decode($value, true) : null;
    }

    public function set($key, $value, $ttl = 300)
    {
        // TTL default 5 minutes
        $this->client->setex($key, $ttl, json_encode($value));
    }

    public function remember($key, $ttl, callable $callback)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();

        if ($value !== null) {
            $this->set($key, $value, $ttl);
        }

        return $value;
    }

    public function forget($key)
    {
        return $this->client->del([$key]);
    }
}
