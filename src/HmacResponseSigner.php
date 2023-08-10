<?php declare(strict_types = 1);

namespace BE\HmacRequestValidator;

use Acquia\Hmac\KeyLoader;
use Acquia\Hmac\RequestAuthenticator;
use Acquia\Hmac\ResponseSigner;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HmacResponseSigner
{
    private string $keyId;

    private string $keySecret;


    public function __construct(string $keyId, string $keySecret)
    {
        $this->keyId = $keyId;
        $this->keySecret = $keySecret;
    }


    public function getSignedResponse(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $keyLoader = new KeyLoader([$this->keyId => $this->keySecret]);
        $requestAuthenticator = new RequestAuthenticator($keyLoader);

        $key = $requestAuthenticator->authenticate($request);

        $responseSigner = new ResponseSigner($key, $request);

        return $responseSigner->signResponse($response);
    }
}
