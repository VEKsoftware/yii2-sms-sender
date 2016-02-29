<?php
namespace sms\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

 /*

 * Параметры
 * msg - текст сообщения
 * clientId - кому отослать
 * action - действие, о котором информируем абонента (разбирается программой)
  /

function PushMessage(&$params, &$res){
// параметры
 $msg = $params["msg"];
 $clientId = $params['clientId'];
 $action = $params['action'];
// константы
 $url='https://gcm-http.googleapis.com/gcm/send';
 $headers=array('Content-Type: application/json; charset=UTF-8',
  'Authorization: key=AIzaSyAgqJk5WLkboD7rprEwl8_4aEhr4XWTmDQ');
 $q="select push_token from sessions where status=1 and project_id=2 and user_id=$clientId order by start_time desc limit 1";
 $row=querySingle($q,$res);
 $data=array('message'=>$msg, 'action'=>$action);
 $notification=array('text'=>$msg, 'body'=>$msg,'title'=>'INET-TAXI',icon=>"myicon");
 if (isset($row['push_token'])) {
  $deviceToken = $row["push_token"];
  $postdata=array(
   "data"=>$data,
   "notification"=>$notification,
   "to"=>$deviceToken);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $r = curl_exec($ch);
  curl_close($ch);

  //$r=http_post_fields($url,$data,null,array("headers"=>$headers));
  if (!$r){
   $res["error"]["code"]=ERR_BAD_PARAMETERS;
   $res["error"]["msg"]='Push сервер вернул ошибку ';
  }
  else {
   echo $r;
   $res["error"]["code"]=0;
   $res["error"]["msg"]='';
  }
 }
 else {
  $res["error"]["code"]=ERR_BAD_PARAMETERS;
  $res["error"]["msg"]=$q;//'Не удалось найти push token для '.$clientId;
 }
}
*/

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