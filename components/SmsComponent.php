<?php
namespace sms\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\ErrorException;

class SmsComponent extends Component
{
    /**
     * ```php
     * [
     *    '/preg_match_expression/' => [
     *        'url' => 'http://url', // This is fixed parameter
     *        // any other list of get parameters
     *        'account' => 'xxxxx',
     *        'password' => 'pAsSw0rD',
     *    ],
     * ],
     * ```
     */
    public $providers_map;

    public $url;
    public $account;
    public $password;
    public $errors;
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
        $data = [];
        if(! empty($this->providers_map)) {
            foreach($this->providers_map as $exp => $dat) {
                if(preg_match($exp,$this->_phone)) {
                    if(! isset($dat['url'])) {
                        throw new \Exception(__CLASS__ . ': You have to set provider url for '.$exp);
                    }
                    $url = $dat['url'];
                    $data = $dat;
                    unset($data['url']);
                }
            }
            if(empty($data)) {
                return false;
            }
        } else {
            $url = $this->url;
            $data = [
                'login' => $this->account,
                'password' => $this->password,
                'phone' => $this->_phone,
                'sender'=> $this->sender,
        ];
        }
        $data['text'] = $this->_text;
        $data['phone'] = $this->_phone;
        if(!isset($data['sender'])) {
            throw new ErrorException('Sender is not specified for sms component');
        }
        $request=$url."?".http_build_query($data);
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $request);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($connection);
        curl_close($connection);

/*
        var_dump($data);
        list($res, $mes_id) = explode(';', $result);
        echo "$res --- $mes_id";
*/

        $answer='accepted';
        return  ($result!=false && substr($result, 0, strlen($answer)) === $answer);
    }

}