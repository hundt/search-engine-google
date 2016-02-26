<?php
/**
 * @license see LICENSE
 */

namespace Serps\Test\TDD\SearchEngine\Google;

use Serps\Core\Http\HttpClientInterface;
use Serps\Core\Http\SearchEngineResponse;
use Serps\Core\UrlArchive;
use Serps\Exception\CaptchaException;
use Serps\SearchEngine\Google\GoogleUrl;
use Serps\SearchEngine\Google\GoogleClient;
use Serps\SearchEngine\Google\GoogleUrlArchive;
use Serps\SearchEngine\Google\Page\GoogleCaptcha;
use Serps\SearchEngine\Google\Page\GoogleSerp;

/**
 * @covers Serps\SearchEngine\Google\GoogleClient
 */
class GoogleClientTest extends \PHPUnit_Framework_TestCase
{

    public function testValidDom()
    {
        $httpClientMock = $this->getMock(HttpClientInterface::class);
        $responseFromMock = new SearchEngineResponse(
            [],
            200,
            file_get_contents('test/resources/simpsons+movie+trailer.html'),
            false,
            GoogleUrlArchive::fromString('https://www.google.fr/search?q=simpsons+movie+trailer'),
            GoogleUrlArchive::fromString('https://www.google.fr/search?q=simpsons+movie+trailer')
        );
        $httpClientMock->method('sendRequest')->willReturn($responseFromMock);

        /* @var $httpClientMock HttpClientInterface */
        $googleClient = new GoogleClient($httpClientMock);
        $url = GoogleUrl::fromString('https://www.google.fr/search?q=simpsons+movie+trailer');

        $dom = $googleClient->query($url);
        $this->assertInstanceOf(GoogleSerp::class, $dom);
        $this->assertEquals('https://www.google.fr/search?q=simpsons+movie+trailer', (string)$dom->getEffectiveUrl());
    }

    public function testCaptchaDom()
    {
        $httpClientMock = $this->getMock(HttpClientInterface::class);
        $responseFromMock = new SearchEngineResponse(
            [],
            503,
            file_get_contents('test/resources/captcha.html'),
            false,
            GoogleUrlArchive::fromString('https://www.google.fr/search?q=simpsons+movie+trailer'),
            GoogleUrlArchive::fromString('https://www.google.fr/search?q=simpsons+movie+trailer')
        );

        $httpClientMock->method('sendRequest')->willReturn($responseFromMock);

        /* @var $httpClientMock HttpClientInterface */
        $googleClient = new GoogleClient($httpClientMock);
        $url = GoogleUrl::fromString('https://www.google.fr/search?q=simpsons+movie+trailer');

        try {
            $googleClient->query($url);
            $this->fail('Exception not thrown');
        } catch (CaptchaException $e) {
            $this->assertInstanceOf(GoogleCaptcha::class, $e->getCaptcha());
            $this->assertEquals('128.78.166.25', $e->getCaptcha()->getDetectedIp());
        }
    }
}
