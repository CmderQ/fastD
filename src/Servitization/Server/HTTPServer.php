<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @see      https://www.github.com/janhuang
 * @see      http://www.fast-d.cn/
 */

namespace FastD\Servitization\Server;

use Exception;
use FastD\Http\Response;
use FastD\Http\SwooleServerRequest;
use FastD\Servitization\OnWorkerStart;
use FastD\Swoole\Server\HTTP;
use Psr\Http\Message\ServerRequestInterface;
use swoole_http_request;
use swoole_http_response;

/**
 * Class HTTPServer.
 */
class HTTPServer extends HTTP
{
    use OnWorkerStart;

    /**
     * @param swoole_http_request  $swooleRequet
     * @param swoole_http_response $swooleResponse
     */
    public function onRequest(swoole_http_request $swooleRequet, swoole_http_response $swooleResponse)
    {
        $request = SwooleServerRequest::createServerRequestFromSwoole($swooleRequet);

        try {
            $response = $this->doRequest($request);
            foreach ($response->getHeaders() as $key => $header) {
                $swooleResponse->header($key, $response->getHeaderLine($key));
            }
            foreach ($response->getCookieParams() as $key => $cookieParam) {
                $swooleResponse->cookie($key, $cookieParam);
            }
        } catch (Exception $e) {
            $response = app()->handleException($request, $e);
        }

        $swooleResponse->status($response->getStatusCode());
        $swooleResponse->end((string) $response->getBody());
        app()->shutdown($request, $response);
    }

    /**
     * @param ServerRequestInterface $serverRequest
     *
     * @return Response
     */
    public function doRequest(ServerRequestInterface $serverRequest)
    {
        return app()->handleRequest($serverRequest);
    }
}
