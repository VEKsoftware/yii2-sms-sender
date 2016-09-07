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
    public $method = 'GET';

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
            foreach($this->providers_map as $dat) {
                if(! isset($dat['match']) || preg_match($dat['match'], $this->_phone)) {
                    if(! isset($dat['url'])) {
                        throw new \Exception(__CLASS__ . ': You have to set provider url for '.$exp);
                    }
                    $url = $dat['url'];
                    $data = $dat['options'];
                    $phoneField = isset($dat['phoneField']) ? $dat['phoneField'] : 'phone';
                    $textField = isset($dat['textField']) ? $dat['textField'] : 'text';
                    $data[$phoneField] = preg_replace('/[^0-9]/','',$this->_phone);
                    $data[$textField] = $this->_text;


                    if(isset($dat['method']) && $dat['method'] === 'POST') {
                        $connection = curl_init("http://sms.ru/sms/send");
                        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($connection, CURLOPT_TIMEOUT, 30);
                        curl_setopt($connection, CURLOPT_POSTFIELDS, $data);
                        throw new \Exception(print_r($data,true));
                        $result = curl_exec($ch);
                        curl_close($connection);
                    } else {
                        $request=$url."?".http_build_query($data);
                        $connection = curl_init();
                        curl_setopt($connection, CURLOPT_URL, $request);
                        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($connection);
                        curl_close($connection);
                    }
                    return true;
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
                'text' => $this->_text,
                'phone' => $this->_phone,
        ];
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
