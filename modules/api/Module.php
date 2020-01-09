<?php

namespace app\modules\api;

use Yii;
/**
 * api module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::$app->user->logout();
        Yii::$app->session->destroy();

        Yii::$app->user->enableSession = false;
        Yii::$app->user->enableAutoLogin = false;
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->request->enableCsrfCookie = false;
        Yii::$app->request->enableCookieValidation = false;

        Yii::$app->response->headers->add('Access-Control-Allow-Origin','*');
        Yii::$app->response->headers->add('Access-Control-Allow-Header','Authorization');
    }
}
