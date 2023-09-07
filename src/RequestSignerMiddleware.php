<?php declare(strict_types = 1);

namespace BrandEmbassy\HmacRequestSignature;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

final class RequestSignerMiddleware
{
    public const HASH_ALGORITHM = 'sha256';

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * @var string
     */
    private $signatureHeader;

    /**
     * @var string
     */
    private $signatureAlgorithmHeader;


    public function __construct(
        string $secretKey,
        string $hashAlgorithm = self::HASH_ALGORITHM,
        string $signatureHeader = Headers::SIGNATURE_HEADER,
        string $signatureAlgorithmHeader = Headers::SIGNATURE_ALGORITHM_HEADER
    ) {
        $this->secretKey = $secretKey;
        $this->hashAlgorithm = $hashAlgorithm;
        $this->signatureHeader = $signatureHeader;
        $this->signatureAlgorithmHeader = $signatureAlgorithmHeader;
    }


    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            $signedRequest = $this->signRequest($request);

            return $handler($signedRequest, $options);
        };
    }


    private function signRequest(RequestInterface $request): MessageInterface
    {
        $sign = RequestBodyEncoder::encode($request, $this->secretKey, $this->hashAlgorithm);

        return $request
            ->withHeader($this->signatureHeader, $sign)
            ->withHeader($this->signatureAlgorithmHeader, $this->hashAlgorithm);
    }
}
