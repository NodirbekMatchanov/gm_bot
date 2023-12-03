<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $last_date
 * @property string|null $title
 * @property string|null $link
 */
class News extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['last_date'], 'required'],
            [['last_date'], 'safe'],
            [['title', 'link'], 'string', 'max' => 255],
            [['link'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'last_date' => 'Last Date',
            'title' => 'Title',
            'link' => 'Link',
        ];
    }
}
