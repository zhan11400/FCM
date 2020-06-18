<?php
/**
 * Create by
 * User: 湛工
 * DateTime: 2020/6/18 10:07
 * Email:  1140099248@qq.com
 */

namespace since;


use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class Curl
{
    protected $common = [
        'headers' => null,
        'json' => null
    ];

    /**设置header
     * @param $header
     * @return \app\common\lib\FCM\Curl
     */
    public function setHeader($header)
    {
        $this->common['headers'] = $header;
        return $this;
    }

    /**设置body
     * @param $param
     * @return $this
     */
    public function setBody($param)
    {

        $this->common['json'] = $param;
        return $this;
    }

    /**获取参数
     * @return array
     */
    public function getParam(): array
    {
        return $this->common;
    }

    /**
     * @param $url
     * @param $method
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($url, $method)
    {
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $r) {
            return $r;
        }));

        $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {

            return $response->withHeader('bbb', 'bbb');
        }));
        $client = new Client(['handler' => $stack]);
        $response = $client->request($method, $url, $this->getParam());
        return $response->getBody()->getContents();
    }

    /**get方式请求
     * @param $url
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get($url)
    {
        return $this->request($url, 'GET');
    }

    /**POST方式请求
     * @param $url
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post($url)
    {
        return $this->request($url, 'POST');
    }

}