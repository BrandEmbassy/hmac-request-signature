<?php declare(strict_types = 1);

namespace BrandEmbassy\HmacRequestSignature;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

final class SignedRequestValidatorTest extends TestCase
{
    private const REQUEST_BODY = '{"foo":"bar"}';
    private const SECRET_KEY = 'thisIsSecretKey';


    public function testSignatureMatch(): void
    {
        $this->expectNotToPerformAssertions();
        $signedRequestValidator = new SignedRequestValidator(self::SECRET_KEY);

        $request = $this->createRequest(
            [
                'X-Request-Signature' => 'mY/ehO6FCpo8xKIzCS05s2gTicm/ZjDmCm3YgrREVSg=',
                'X-Signature-Algorithm' => 'sha256',
            ]
        );

        $signedRequestValidator->validateSignature($request);
    }


    /**
     * @dataProvider failedVerificationHeadersProvider
     *
     * @param array<string> $headers
     */
    public function testSignatureVerificationFailed(string $expectedExceptionMessage, array $headers): void
    {
        $signedRequestValidator = new SignedRequestValidator(self::SECRET_KEY);

        $request = $this->createRequest($headers);

        $this->expectException(SignatureVerificationException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $signedRequestValidator->validateSignature($request);
    }


    /**
     * @return  array<string, array<int, array<string, string>|string>>
     */
    public function failedVerificationHeadersProvider(): array
    {
        return [
            'missing signature header' => [
                'Request is missing signature header X-Request-Signature',
                [],
            ],
            'empty signature header' => [
                'Request is missing signature header X-Request-Signature',
                ['X-Request-Signature' => ''],
            ],
            'missing algorithm header' => [
                'Request is missing hash algorithm header X-Signature-Algorithm',
                ['X-Request-Signature' => 'foo'],
            ],
            'empty algorithm header' => [
                'Request is missing hash algorithm header X-Signature-Algorithm',
                ['X-Request-Signature' => 'foo'],
            ],
            'signature not match' => [
                'Request signature verification failed',
                [
                    'X-Request-Signature' => 'foo',
                    'X-Signature-Algorithm' => 'sha256',
                ],
            ],
        ];
    }


    /**
     * @param array<string> $headers
     */
    private function createRequest(array $headers = []): RequestInterface
    {
        $request = new ServerRequest('post', '/', $headers);

        $body = $request->getBody();
        $body->write(self::REQUEST_BODY);

        $requestWithBody = $request->withBody($body);
        assert($requestWithBody instanceof RequestInterface);

        return $requestWithBody;
    }
}
