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
    protected $_body = [];
    protected $_header = [];
    /**设置header
     * @param $header
     * @return $this
     */
    public  function setHeader($header)
    {
        $this->_header = $header;
        return $this;
    }

    /**获取header
     * @return array
     */
    public  function getHeader()
    {
       return $this->_header;
    }

    /**设置body
     * @param $param
     * @return $this
     */
    public  function setBody($param)
    {
        $this->_body = $param;
        return $this;
    }

    /**获取参数
     * @return array
     */
    public  function getBody()
    {
        return $this->_body;
    }

    /**
     * @param $url
     * @param $method
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($url, $method){
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $r) {
            return $r;
        }));

        $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
            return $response->withHeader('aaa', 'bar');
        }));
        $client = new Client(['handler' => $stack]);
        $response=$client->request($method, $url,[
            'headers' => $this->getHeader(),
            'json'=> $this->getBody(),
        ]);
        return $response->getBody()->getContents();


    }
    /**get方式请求
     * @param $url
     * @return string
     */
    public  function get($url)
    {
        return $this->request($url,'GET');
    }

    /**POST方式请求
     * @param $url
     * @return string
     */
    public  function post($url)
    {
        return $this->request($url, 'POST');
    }

}