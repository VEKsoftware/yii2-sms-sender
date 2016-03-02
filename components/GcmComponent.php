<?php
namespace sms\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

use yii\helpers\Json;

class GcmComponent extends Component
{
    public $url;
    public $auth_key;
    public $sender;
    public $errors;

    private $_text;
    private $_data = [];
    private $_notification = [];
    private $_push_token;

    public function compose($params)
    {
        foreach($params as $param => $data) {
            switch($param) {
            case('text'):
                $this->setText($data);
                break;
            case('push_token'):
                $this->setPushToken($data);
                break;
            case('data'):
                $this->setData($data);
                break;
            case('notification'):
                $this->setNotification($data);
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

    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function setNotification($data)
    {
        $this->_notification = $data;
        return $this;
    }

    public function setPushToken($token)
    {
        $this->_push_token = $token;
        return $this;
    }

    public function send()
    {
        if (! isset($this->_push_token)) {
            $this->errors = [
                'push_token' => Yii::t('sms','Push token should be set'),
            ];
            return false;
        }
        $postdata = [
            'data' => $this->_data + [
                'message' => $this->_text,
                'sender' => $this->sender,
            ],
            'notification' => $this->_notification + [
                'text' => $this->_text,
                'body' => $this->_text,
                'title' => $this->sender,
//                'icon' => "myicon",
            ],
        ];
        if(is_string($this->_push_token)) {
            $postdata['to'] = $this->_push_token;
        } elseif(is_array($this->_push_token)) {
            foreach($this->_push_token as $token) {
                if($token) $postdata['registration_ids'][] = $token;
            }
        } else {
            throw new ErrorException('Unknown format of push_token for GCM service');
        }
        $headers = [
            'Content-Type: application/json; charset=UTF-8',
            'Authorization: key='.$this->auth_key,
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, Json::encode($postdata));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $r = curl_exec($ch);
        curl_close($ch);

        if ($r){
            return true;
        } else {
            $this->errors = [
                'result' => Yii::t('sms','Error answer to request'),
            ];
            return false;
        }
    }
}