<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $title
 * @property string $anons
 * @property string $text
 * @property string $tags
 * @property string $image
 * @property string $datatime
 *
 * @property Comment[] $comments
 */
class Post extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';
    public $imageFile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post';
    }

    public function upload()
    {
        if($this->imageFile){
            copy($this->imageFile->tempName, $_SERVER['DOCUMENT_ROOT'] . '/web/upload/' . $this->image);
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'anons', 'text', 'image'], 'required'],
            [['text'], 'string'],
            [['datatime'], 'safe'],
            [['title', 'anons', 'tags', 'image'], 'string', 'max' => 255],
            [['title'], 'unique'],
            [['tags'], 'match', 'pattern' => '/^[\S]*(,\s[\S]*)*$/'],
            [['imageFile'], 'file', 'extensions' => ['jpg', 'png'], 'maxSize' => 2 * 1024 *1024,'on' => static::SCENARIO_CREATE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'anons' => 'Anons',
            'text' => 'Text',
            'tags' => 'Tags',
            'image' => 'Image',
            'datatime' => 'Datatime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['post_id' => 'id']);
    }
}
