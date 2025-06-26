<?php
namespace App\Services;

use Illuminate\Support\Facades\Redis;

class JwtBlacklistService
{
    protected $prefix = 'jwt_blacklist:';

    public function blacklist(string $jti, int $ttl)
    {
        $key = $this->prefix . $jti;
        Redis::setex($key, $ttl, 1);
    }

    public function isBlacklisted(string $jti): bool
    {
        return Redis::exists($this->prefix . $jti) > 0;
    }
}
