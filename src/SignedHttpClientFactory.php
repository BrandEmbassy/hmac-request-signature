<?php declare(strict_types = 1);

namespace CustomComponents;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class SignedHttpClientFactory
{
    /**
     * @var HmacAuthMiddleware
     */
    private $hmacAuthMiddleware;


    public function __construct(HmacAuthMiddleware $hmacAuthMiddleware)
    {
        $this->hmacAuthMiddleware = $hmacAuthMiddleware;
    }


    public function create(): Client
    {
        $stack = HandlerStack::create();
        $stack->push($this->hmacAuthMiddleware);

        return new Client(['handler' => $stack]);
    }
}
