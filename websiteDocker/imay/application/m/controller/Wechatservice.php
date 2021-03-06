<?php
/*===============================================================
*   Copyright (C) 2015 All rights reserved.
*
*   file        : Wechat.php
*   author      : jason.zhi
*   date        : 2016/12/26
*   description : 微信服务号接口接入
*   modify      :
*
================================================================*/
namespace app\m\controller;

class Wechatservice extends \think\Controller
{
    private $weObj;
    
    protected function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        
        vendor("Wechat");
        $this->config = config('WX.MP');
        
        $this->weObj = new \Wechat([
            'appid'     => $this->config['APPID'],
            'appsecret' => $this->config['AppSecret'],
            'token'     => 'weixin',
        ]);
    }
    
    public function getAccessToken()
    {
        echo $this->weObj->checkAuth();
    }
    
    /**
     * @brief  获取素材的相关信息，主要用于获取素材的media id
     *         xxx/Wechatservice/getForeverList/type/image/offset/0/count/20
     * @author wojiaojason@gmail.com
     */
    public function getForeverList()
    {
        $type   = input('get.type', 'image');
        $offset = input('get.offset', 0);
        $count  = input('get.count', 10);
        // echo "{$type}:{$offset}:{$count}<br>\r\n";
        $data = $this->weObj->getForeverList($type, $offset, $count);
        if ($data === FALSE) {
            echo $this->weObj->errCode . " " . $this->weObj->errMsg . "<br>\r\n";
        }
        // var_dump($data);
        echo json_encode($data);
    }
    
    public function wxApi()
    {
        //HOW TO Request?change the host and the relative params in the following string.
        $str = <<<STR
POST http://mywap.imay.com/WechatService/wxApi?apiName=getForeverList HTTP/1.1
Host: mywap.imay.com
Connection: keep-alive
Cache-Control: max-age=0
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8
Accept-Encoding: gzip, deflate, sdch
Content-Type: application/x-www-form-urlencoded

type=image&count=10&offset=0
STR;
    
        $params = input('post.');
        if (empty($params)) {
            $params = ['type' => 'image', 'offset' => 0, 'count' => 10];
        }
        $apiName = input('get.apiName', "getForeverList");
        $ret     = call_user_func_array([$this->weObj, $apiName], $params);
        // echo json_encode($params);
        // echo json_encode($apiName);
        echo json_encode($ret);
    }
    
    public function uploadForeverMedia()
    {
        $data = $this->weObj->uploadForeverMedia('@/1.jpg', 'image');
        if ($data === FALSE) {
            echo $this->weObj->errCode . " " . $this->weObj->errMsg . "<br>\r\n";
        }
        var_dump($data);
    }
    
    /**
     * @brief  接口接入
     * @author jason.zhi@lihuobao.cn
     * @date   2016/10/24
     */
    private function switchIn()
    {
        echo $_GET["echostr"];//验证的时候一定要输出这个值，不然是不能验证成功
        exit;
    }
    
    /**
     * @brief  关键词回复
     * @date   2017/1/9
     * @author wojiaojason@gmail.com
     */
    private function keyWordReply()
    {
        $content = trim($this->weObj->getRev()->getRevContent());
        // $this->weObj->text("帮助信息")->reply();
        // return;
        //关键词对应的回复
        $rules = [
            '充值|充钱|冲钱|钱|充'                           => ['type' => 'text', 'value' => "亲爱的~充值去撩妹的小主们请点击菜单下方的【充值】子菜单"],
            '下载|哪里下载|怎么下载'                           => ['type' => 'image', 'value' => 'pwDExe5CLAMReyPgsADEv6xBt9HqjHAKwIFZjJQOjPY'],
            '注册|如何注册|怎么注册|魅号'                        => ['type' => 'text', 'value' => '打开iMay手机客户端，点击注册，进入注册页面。在注册页面输入手机号，点击验证码后方的发送按钮，获取短信验证码，输入正确的验证码和密码之后，点击下一步，输入昵称，上传你喜欢的头像即可完成注册。你也可以使用QQ、微信等第三方账号快速登录iMay平台。'],
            '实名认证|认证|实名'                             => ['type' => 'text', 'value' => '打开iMay直播app，进入个人中心，点击头像旁边的小铅笔进入个人资料页面，拉到最下面，就看到实名认证的入口,请按页面提示输入真实姓名、身份证号码，并上传身份证正面，本人与证件合影，身份证背面。'],
            '主播招募|家族入驻|工会|经纪公司|主播|入驻|家族|明星|经纪人|线下活动' => ['type' => 'text', 'value' => $this->strHexToUtf8("F09F98B3") . "亲爱的，主播招募、家族入驻、工会、经纪公司等洽谈， 请联系我们的娱乐总监，黄总监微信号：15013244959\nps:电话说不清，请加微信，谢谢！"],
            '提现'                                     => ['type' => 'text', 'value' => '亲爱的~提现的小主们请点击菜单下方的【提现】'],
            '客服'                                     => ['type' => 'text', 'value' => '亲爱哒，如有需要紧急答复的问题，请加QQ：800811388，客服24小时恭候你过来撩'],
        ];
        
        $ruleSelect = [];
        if ($content) {
            foreach ($rules as $rule => $v) {
                if (mb_ereg_match($rule, $content)) {
                    $ruleSelect = $v;
                    break;
                }
            }
            // $ruleSelect = ['type' => 'text', 'value' => "亲爱的~充值去撩妹的小主们请点击菜单下方的【充值】子菜单"];
            if ($ruleSelect) {
                $type  = $ruleSelect['type'];
                $value = $ruleSelect['value'];
                switch ($type) {
                    case 'text':
                        $this->weObj->text($value)->reply();
                        break;
                    case 'image':
                        $this->weObj->image($value)->reply();
                        break;
                    default:
                        $this->weObj->text('规则类型暂不支持')->reply();
                        break;
                }
                
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * @brief 16进制的编码转为字符形式
     * @param $str
    @author wojiaojason@gmail.com
     */
    private function strHexToUtf8($str)
    {
        $len = strlen($str);
        
        $code = "";
        for ($i = 0; $i < $len; $i += 2) {
            $code .= chr(hexdec($str[$i] . $str[$i + 1]));
        }
        
        return $code;
    }
    
    private function route()
    {
        $type = $this->weObj->getRev()->getRevType();
        
        switch ($type) {
            case \Wechat::MSGTYPE_TEXT:
                if ($this->keyWordReply()) {
                    //todo do nothing
                    return;
                } else {
                    $this->weObj->transfer_customer_service()->reply();
                }
                break;
            case \Wechat::MSGTYPE_IMAGE:
                break;
            case \Wechat::MSGTYPE_EVENT:
                $event = $this->weObj->getRev()->getRevEvent()['event'];
                if ($event == \Wechat::EVENT_SUBSCRIBE) {
                    $reply = "山无陵、天地合,也不许取关噢 ~么么哒";
                    $this->weObj->text($reply)->reply();
                }
                break;
            default:
                $this->weObj->text("help info")->reply();
        }
    }
    
    /**
     * @brief  微信服务器回调地址
     * @author jason.zhi@lihuobao.cn
     * @date   2016/10/24
     */
    public function index()
    {
        $this->weObj->valid();
        if (isset($_GET["echostr"])) {
            $this->switchIn();
        } else {
            $this->route();
        }
    }
    
    /**
     * @brief  创建自定义菜单
     * @author jason.zhi@lihuobao.cn
     * @date   2016/10/31
     */
    public function createMenu()
    {
        $menu = [
            "button" => [
                [
                    "type" => "view",
                    "name" => "首页",
                    //"url"  => "http://" . C('WEIXIN_MP_CONFIG.request_host') . "/Wechat/Index/index",
                    "url"  => "https://m.imay.com/index/index",
                ],
                [
                    "type" => "view",
                    "name" => "充值",
                    //"url"  => "http://" . C('WEIXIN_MP_CONFIG.request_host') . "/Recharge/index",
//                    "url"  => "https://m.imay.com/recharge/index",
                    "url" => "",
                ],
                [
                    "type" => "view",
                    "name" => "提现",
                    //"url"  => "http://" . C('WEIXIN_MP_CONFIG.request_host') . "/Wechat/Withdraw/index",
//                    "url"  => "https://m.imay.com/withdraw/index",
                    "url" => "",
                ],
            ],
        ];
        $rs   = $this->weObj->createMenu($menu);
        var_dump($rs);
    }
}