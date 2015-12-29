<?php
namespace sms\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class SmsComponent extends Component
{
    public $url;
    public $account;
    public $password;
    public $sender;

    private $_text;
    private $_phone;

    public function compose($params)
    {
        foreach($params as $param => $data) {
            switch($param) {
            case('text'):
                $this->setText($data);
                break;
            case('phone'):
                $this->setPhone($data);
                break;
            }
        }
        return $this;
    }

    public function setText($text)
    {
        $this->_text = $text;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->_phone = $phone;
        return $this;
    }

    public function send()
    {
        $data = [
            'login' => $this->account,
            'password' => $this->password,
            'phone' => $this->_phone,
            'text' => $this->_text,
            'sender'=> $this->sender,
        ];
        $request=$this->url."?".http_build_query($data);
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $request);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($connection);
        curl_close($connection);

        $answer='accepted';
        return  ($result!=false && substr($result, 0, strlen($answer)) === $answer);
    }

}