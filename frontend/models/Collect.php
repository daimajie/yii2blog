<?php
namespace frontend\models;
use yii\db\ActiveRecord;

class Collect extends ActiveRecord
{
    //表明
    public static function tableName()
    {
        return '{{%collect}}';
    }

    /**
     * 绑定事件
     */
    public function init ()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_DELETE, [$this, 'onAfterDelete']);
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'onAfterInsert']);
    }

    /**
     * 添加收藏时更新文章收藏数
     * @param $event
     */
    public function onAfterInsert($event){
        Article::updateAllCounters(['collect'=>1], ['id'=>$this->article_id]);
    }


    /**
     * 取消收藏时更新文章收藏数
     *
     * @param $event
     */
    public function onAfterDelete($event){
        Article::updateAllCounters(['collect'=>-1], ['id'=>$this->article_id]);
    }

    /**
     * @param int $aid #文章id
     * @param int $uid #用户ID
     * @return bool #已收藏返回true 否则返回false
     */
    public static function isExists($aid, $uid){
        return (bool)self::find()
            ->select('id')
            ->where(['article_id'=>$aid])
            ->andWhere(['user_id'=>$uid])
            ->scalar();
    }

    /**
     * 定义文章关联
     * @return \yii\db\ActiveQuery
     */
    public function getArticle()
    {
        return $this->hasOne(Article::className(), ['id' => 'article_id']);
    }
}