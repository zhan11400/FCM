<?php
/**
 * Create by
 * User: 湛工
 * DateTime: 2020/6/18 10:06
 * Email:  1140099248@qq.com
 */

namespace since;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use think\facade\Cache;

class Fcm
{
    private $curl;
    public  $common = [
        'name' => null,
        'data' => null,
        "notification" => null,
        "android" =>null,
        "webpush" => null,
        "apns" => null,
        "fcm_options" => null,
        "token" => null,
        "topic" => null,
        "condition" => null,
    ];
    protected $options = [
        'key'     => '',//谷歌服务器的key
        'project_id'    => '',//项目id
        'google_server'       => '',//下载的service-account-file.json文件存放路径
    ];
    protected $Authorization;
    public function __construct(array $options)
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $stack = new HandlerStack();
        $stack->setHandler(new CurlHandler());
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {

            return $request
                ->withHeader('Content-Type',"application/json")
                ->withHeader('Authorization', $this->Authorization);
        }));
        $stack->push(Middleware::mapResponse(function (ResponseInterface $response) {
            $httpCode=$response->getStatusCode();
            switch ($httpCode){
                case 400: $result='{"error":"request parameters are missing or invalid. Check error messages for"}';break;
                case 403:$result='{"error":"authorization header doesn\'t match the authorizedEntity"}';break;
                case 401:$result='{"error":"authorization header is invalid."}';break;
                case 404:$result='{"error":" Invalid HTTP path or IID token not found. Check error messages for detailed information."}';break;
                default:
                    $result=$response->getBody()->getContents();
            }
            return $result;
        }));

        $this->curl= new Client(['handler' => $stack]);
    }

    /**
     * @param $register_token
     * @return ResponseInterface
     * @throws \Google_Exception
     */
    public function info($register_token)
    {

        $url = sprintf('https://iid.googleapis.com/iid/info/%s', $register_token);
        return  $this->setHeader()->curl->get($url);
    }

    /**将设备添加到主题
     * @param string $topic_name 根据业务自定义的主题名称
     * @param string $register_token 前端授权得到的REGISTRATION_TOKEN
     * @return string
     * @throws \Google_Exception
     */
    public  function addTopic(string  $topic_name, string $register_token)
    {

        $url = sprintf('https://iid.googleapis.com/iid/v1/%s/rel/topics/%s', $register_token, $topic_name);
        return  $this->setHeader()->curl->post($url);
    }

    /**将多设备添加到主题
     * @param string $topic_name
     * @param array $register_tokens 最多更新1000个应用程序实例
     * @return string
     * @throws \Google_Exception
     */
    public function addManyTopic(string $topic_name, array $register_tokens)
    {

        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $data=[
            'to'=>"/topics/".$topic_name,
            'registration_tokens'=>$register_tokens
        ];
        return  $this->setHeader()->curl->post($url,[
            'json'=>$data
        ]);
    }

    /**
     * @param string $type
     * @return $this
     * @throws \Google_Exception
     */
    protected function setHeader($type='topic')
    {
        if($type=='topic') {//主题头
            $this->Authorization = 'key=' . $this->options['key'];
        }else{//发送头
            $this->Authorization =  'Bearer '.$this->getAccessToken($this->options['google_server']);
        }
        return $this;
    }

    /**获取access_token的方法，并对access_token做了缓存处理
     * @param $config_path 下载的service-account-file.json文件存放路径
     * @return mixed
     * @throws \Google_Exception
     */
    public  function getAccessToken($config_path)
    {
        $cacheKey = 'fcm';
        $temp = Cache::get($cacheKey);
        if (empty($temp)) {
            $temp = $this->requestAccessToken($config_path);
            Cache::set($cacheKey, $temp['access_token'],$temp['expires_in']);
            return $temp['access_token'];
        }
        return $temp;
    }

    /**
     * 调用google google-api-php-client 获取access_token 这个是通过google的服务账号授权(用于server端) 不是页面的OAuth授权
     * @param string $config_path option 配置文件路径
     * @throws \Google_Exception
     * @return [
     *      'access_token' => 'ya29.*****',             //访问令牌
     *      'expires_in' => 3600,                       //访问令牌过期时间
     *      'token_type' => 'Bearer',                   //token_type
     *      'created' => 1574401624,                    //token 创建时间
     * ]
     */
    protected function requestAccessToken($config_path)
    {
        $client = new \Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setAuthConfig($config_path);
        $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);     # 授予访问 FCM 的权限
        return $client->fetchAccessTokenWithAssertion();
    }

    /**发送主题推送
     * @param $messageData
     * @return string
     * @throws \Google_Exception
     */
    public function sendTopicMessage($messageData)
    {
        if (!empty($messageData)) {
            $this->common = array_merge($this->common, $messageData);
        }
        $sendData= ['message' => array_filter($this->common)];
        $url = 'https://fcm.googleapis.com/v1/projects/'.$this->options['project_id'].'/messages:send';
        return $this->setHeader('send')->curl->post($url,[
            'json'=>$sendData
        ]);
    }
}