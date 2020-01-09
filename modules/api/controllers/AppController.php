<?php
/**
 * Created by PhpStorm.
 * User: EndyRoys
 * Date: 15.11.2019
 * Time: 17:11
 */

namespace app\modules\api\controllers;

use Yii;
use app\models\User;
use app\models\Comment;
use yii\rest\ActiveController;


class AppController extends ActiveController
{

    public $modelClass = 'app\models\Post';

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        return $actions;
    }

    public function actionAuth()
    {
        $login = Yii::$app->request->get('login');
        $password = Yii::$app->request->get('password');
        $model = User::findByLogin($login);
        if ( !empty($model) and $model->validatePassword($password)) {
            $model->token = Yii::$app->security->generateRandomString();
            $model->save();
            $this->setResponse('Successful authorization');
            $response = [
                'status' => true,
                'token' => $model->token,
            ];
        } else {
            $this->setResponse('Invalid authorization data', 401);
            $response = [
                'status' => false,
                'message' => 'Invalid authorization data',
            ];
        }
        return $response;
    }

    public function setResponse($text, $code = 200)
    {
        return Yii::$app->response->setStatusCode($code, $text);
    }

    public function formatErrors($arr)
    {
        foreach ($arr as $key => $value) {
            $arr[$key] = $value[0];
        }
        return $arr;
    }

    public function formatTags($str)
    {
        return explode(', ', $str);
    }

    public function formatPost($arr)
    {
        $arr['comments'] = Comment::find()->where(['post_id'=>$arr['id']])->asArray()->all();
        foreach($arr['comments'] as $key=>$value){
            unset($arr['comments'][$key]['post_id']);
            $arr['comments'][$key]['comment_id'] = $arr['comments'][$key]['id'];
            unset($arr['comments'][$key]['id']);
        }
        $arr['tags'] = $this->formatTags($arr['tags']);
        $arr['image'] = "http://sbornaya2.day/upload/" . $arr['image'];
        unset($arr['id']);
        return $arr;
    }
}