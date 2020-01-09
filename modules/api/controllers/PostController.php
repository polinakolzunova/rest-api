<?php

namespace app\modules\api\controllers;

use Yii;
use app\models\Post;
use app\models\Comment;
use yii\web\UploadedFile;
use yii\filters\auth\HttpBearerAuth;

/**
 * Default controller for the `api` module
 */
class PostController extends AppController
{
    public $modelClass = 'app\models\Post';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['bearerAuth'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['create', 'update2', 'delete', 'create-comment','delete-comment'],
            'optional' => ['create-comment']
        ];
        return $behaviors;
    }

    public function actionIndex($tag)
    {
        $model = Post::find()
            ->filterWhere(['LIKE','tags',$tag])
            ->asArray()
            ->all();
        foreach ($model as $key => $value) {
            $model[$key]['image'] = "http://sbornaya2.day/upload/" . $model[$key]['image'];
        }
        $response = $model;
        $this->setResponse('List posts');
        return $response;
    }

    public function actionView($id)
    {
        $model = Post::find()->where(['id' => $id])->asArray()->one();

        if ( !empty($model)) {
            $this->setResponse('View post');
            $response = $this->formatPost($model);
        } else {
            $this->setResponse('Post not found', 404);
            $response = [
                'message' => 'Post not found',
            ];
        }
        return $response;
    }

    public function actionCreate()
    {
        $model = new Post();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->scenario = Post::SCENARIO_CREATE;

        if ( !empty($_FILES)) {
            $model->imageFile = UploadedFile::getInstanceByName('image');
            $model->image = 'upload' . time() . '.' . $model->imageFile->extension;
        }

        if ($model->validate()) {
            $model->upload();
            $model->save(false);
            $this->setResponse('Successful creation', 201);
            $response = [
                'status' => true,
                'post_id' => $model->id,
            ];
        } else {
            $this->setResponse('Creating error', 400);
            $response = [
                'status' => false,
                'message' => $this->formatErrors($model->errors),
            ];
        }


        return $response;
    }

    public function actionUpdate2($id)
    {
        $model = Post::findOne(['id' => $id]);

        if ( !empty($model)) {
            $model->setAttributes(Yii::$app->getRequest()->getBodyParams(), false);

            if ( !empty($_FILES)) {
                $model->imageFile = UploadedFile::getInstanceByName('image');
                $model->image = 'upload' . time() . '.' . $model->imageFile->extension;
            }

            if ($model->validate()) {
                $model->upload();
                $model->save(false);
                $this->setResponse('Successful creation', 201);
                $response = [
                    'status' => true,
                    'post' => [
                        'title' => $model->title,
                        'datatime' => $model->datatime,
                        'anons' => $model->anons,
                        'text' => $model->text,
                        'tags' => $this->formatTags($model->tags),
                        'image' => $model->image,
                    ],
                ];
            } else {
                $this->setResponse('Creating error', 400);
                $response = [
                    'status' => false,
                    'message' => $this->formatErrors($model->errors),
                ];
            }
        } else {
            $this->setResponse('Post not found', 404);
            $response = [
                'message' => 'Post not found',
            ];
        }
        return $response;
    }

    public function actionDelete($id)
    {
        $model = Post::findOne(['id' => $id]);

        if ( !empty($model)) {
            $model->delete();
            $this->setResponse('Successful delete', 201);
            $response = [
                'status' => true,
            ];
        } else {
            $this->setResponse('Post not found', 404);
            $response = [
                'message' => 'Post not found',
            ];
        }
        return $response;
    }

    public function actionCreateComment($id)
    {

        $model = new Comment();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->post_id = $id;
        if (!Yii::$app->user->isGuest) $model->author = 'admin';

        if ( !$model->validate('post_id')) {
            $this->setResponse('Post not found', 404);
            $response = [
                'message' => 'Post not found'
            ];
        } else if ($model->validate()) {
            $model->save();
            $this->setResponse('Successful creation', 201);
            $response = [
                'status' => true
            ];
        } else {
            $this->setResponse('Creating error', 400);
            $response = [
                'status' => false,
                'message' => $this->formatErrors($model->errors)
            ];
        }
        return $response;
    }

    public function actionDeleteComment($post_id, $comment_id)
    {
        $post = Post::find()->where(['id' => $post_id])->count();
        if ($post == 0) {
            $this->setResponse('Post not found', 404);
            $response = [
                'message' => 'Post not found',
            ];
            return $response;
        }

        $model = Comment::findOne(['id' => $comment_id]);
        if ( !empty($model)) {
            $model->delete();
            $this->setResponse('Successful delete', 201);
            $response = [
                'status' => true,
            ];
        } else {
            $this->setResponse('Comment not found', 404);
            $response = [
                'message' => 'Comment not found',
            ];
        }
        return $response;
    }
}
