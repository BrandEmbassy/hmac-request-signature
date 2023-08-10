<?php declare(strict_types = 1);

namespace BE\HmacRequestValidator;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use Acquia\Hmac\KeyInterface;
use Closure;

class HmacRequestSignerMiddleware
{
    private string $realm;

    private ?KeyInterface $key = null;

    /**
     * @var string[][]
     */
    private array $customHeaders;


    /**
     * @param string[][] $customHeaders
     */
    public function init(string $keyId, string $keySecret, string $realm, array $customHeaders = []): void
    {
        $this->key = new Key($keyId, $keySecret);
        $this->realm = $realm;
        $this->customHeaders = $customHeaders;
    }


    public function __invoke(callable $handler): Closure
    {
        if ($this->key === null) {
            return fn($request, array $options) => $handler($request, $options);
        }

        $hmacAuthMiddleware = new HmacAuthMiddleware($this->key, $this->realm, $this->customHeaders);

        return $hmacAuthMiddleware($handler);
    }
}
