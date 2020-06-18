# fcm
谷歌fcm推送
## 安装

> composer require since/fcm

### 调用例子
```
use fcm\Fcm;
class Index
{


    public function index(){
        $config=[
            'key'=>'谷歌服务器的key',
            'project_id'=>'项目id',
            'google_server'=>'firebase的json文件路径'
        ];
        $register_token='设备获取到的token';
        # 订阅
        $topic_name = "news";		//自定义的主题名称 字符串
        $Fcm= new Fcm($config);
        //$Fcm->addManyTopic($topic_name,$register_tokens)//推送多设置
        $Fcm->addTopic($topic_name,$register_token);//推送单设置
        $sendDate=[
            'data'=>["story_id" => 'story_12345'],
            'notification'=>[
                'title' => 'title',
                'body' => 'New news story available.',
                'image'=>'https://aiteam.runearntoday.com/abc_48.png',
            ],
            'topic'=>$topic_name,
        ];
        $result=$Fcm->sendTopicMessage($sendDate);			//主题推送消息
    }
}
```
