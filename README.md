SMS sender
==========
Sends an SMS message throw a web gateway

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist veksoftware/yii2-sms-sender "*"
```

or add

```
"veksoftware/yii2-sms-sender": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
// config/main.php
<?php
    'components' => [
        'sms' => [
            'class' => '\sms\components\SmsComponent',
            'url' => 'http://location.of.sms.gateway.service',
            'account' => 'my_account_at_service',
            'password' => 'my password at service',
            'sender' => 'Set From String',
        ]
    ]
```

Then you can use it in your code :

```php

<?php
    Yii::$app->sms->setText('SMS text')->setPhone('+12345678901')->send();

    Yii::$app->sms->compose([ 'text' => 'SMS text', 'phone' => '+12345678901')->send();
?>
```
