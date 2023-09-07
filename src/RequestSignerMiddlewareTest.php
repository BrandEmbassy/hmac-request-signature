<?php declare(strict_types = 1);

namespace BrandEmbassy\HmacRequestSignature;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Utils;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

final class RequestSignerMiddlewareTest extends TestCase
{
    /**
     * @dataProvider requestDataProvider
     */
    public function testCorrectSignatureHeader(MessageInterface $request, string $expectedSignature): void
    {
        $requestSignerMiddleware = new RequestSignerMiddleware('random');

        $mockHandler = new MockHandler(
            [
                static function (MessageInterface $request) use ($expectedSignature): void {
                    Assert::assertTrue($request->hasHeader('X-Request-Signature'));
                    Assert::assertSame(
                        $expectedSignature,
                        $request->getHeaderLine('X-Request-Signature')
                    );
                },
            ]
        );

        $stack = new HandlerStack($mockHandler);
        $stack->push($requestSignerMiddleware);

        $composed = $stack->resolve();

        Assert::assertInstanceOf(RequestInterface::class, $request);

        $composed($request, []);
    }


    /**
     * @return array<string, array{request: MessageInterface, expectedSignature: string}>
     */
    public function requestDataProvider(): array
    {
        return [
            'simple post request' => [
                'request' => $this->createRequest(Utils::jsonEncode(['foo' => 'bar'])),
                'expectedSignature' => '36HN7juP6erwuhrmZelPH58M9xDaNrImKsDNi3u8Bww=',
            ],
            'simple get request' => [
                'request' => $this->createRequest(''),
                'expectedSignature' => 'qXRq3V1/nR82wfT6WyNGQWZYp9ShpuSQfpvGGCZ99LI=',
            ],
        ];
    }


    private function createRequest(string $requestBody): MessageInterface
    {
        $request = new ServerRequest('post', '/');

        $body = $request->getBody();
        $body->write($requestBody);

        return $request->withBody($body);
    }
}
