<?php
namespace frontend\models;
use backend\models\Article as ArticleModel;
use yii\data\Pagination;
use yii\helpers\Url;

class Article extends ArticleModel
{
    /**
     * 获取精品文章
     */
    public static function getIndexBest(){
        return self::find()
            ->select(['id','title','bigimg'])
            ->where(['isbest'=>1])
            ->andWhere(['isrecycle'=>0])
            ->orderBy(['created_at'=>SORT_DESC])
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * 获取文章列表
     * @param string $type #获取类型（index 首页文章列表）
     * @return array #数据提供者
     */
    public static function getArticle($type=''){
        $query = self::find()->andWhere(['isrecycle'=>0]);





        $countQuery = clone $query;
        $count = $countQuery->count();

        $pagination = new Pagination(['totalCount' => $count,'pageSize'=>15]);

        $articles = $query
            ->with('user','subject')
            ->select(['{{%article}}.id','title','brief','favorite','collect','visited','created_at','smallimg','created_by','subject_id'])
            ->offset($pagination->offset)
            ->limit($pagination->limit)

            ->asArray()
            ->orderBy(['created_at'=>SORT_DESC])
            ->all();

        return [
            'articles' => $articles,
            'pagination' => $pagination,
        ];

    }

    /**
     * 获取指定专题的文章
     * @params int $subject #专题id
     * @param string $type #搜索类型
     * @return array #文章列表和 分页对象
     */
    public static function getSubjectArticles($subject_id, $type){
        $query = self::find()
            ->where(['subject_id'=>$subject_id])
            ->andWhere(['isrecycle'=>0]);

        $countQuery = clone $query;
        $count = $countQuery->count();

        $pagination = new Pagination(['totalCount' => $count,'pageSize'=>15]);

        if($type == 'new')
            $query->orderBy(['created_at'=>SORT_DESC]);
        if($type == 'hot')
            $query->orderBy(['visited'=>SORT_DESC]);
        if ($type == 'best')
            $query->andWhere(['isbest' => 1]);

        $articles = $query
            ->with('user')
            ->select(['{{%article}}.id','title','brief','favorite','collect','visited','created_at','smallimg','created_by','subject_id'])
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        return [
            'articles' => $articles,
            'pagination' => $pagination,
        ];
    }

    /**
     * 获取制定文章
     */
    public static function getDetail($id){
        $ret = self::find()
            ->alias('a')
            ->select(['a.*','c.content'])
            ->leftJoin(['c'=>'{{%content}}'], 'c.id=a.content_id')
            ->where(['a.id'=>$id])
            ->asArray()
            ->one();
        return $ret;
    }


    /**
     * 获取上一篇和下一篇
     * @param int $article_id #当前文章id
     * @param int $subject_id #当前专题
     * @return array
     */
    public static function getPreviousAndNext($article_id, $subject_id){
        $previous = self::find()
            ->select(['id', 'title'])
            ->andFilterWhere(['<', 'id', $article_id])
            ->andFilterWhere(['subject_id'=>$subject_id])
            ->andFilterWhere(['!=', 'isrecycle', 1])
            ->orderBy(['id'=>SORT_DESC])
            ->limit(1)
            ->one();
        $next = self::find()
            ->select(['id', 'title'])
            ->andFilterWhere(['>', 'id', $article_id])
            ->andFilterWhere(['subject_id'=>$subject_id])
            ->andFilterWhere(['!=', 'isrecycle', 1])
            ->orderBy(['id'=>SORT_ASC])
            ->limit(1)
            ->one();

        //拼接url
        $prev_article = [
            'url' => !is_null($previous)?Url::current(['id'=>$previous->id]):'javascript:void(0);',
            'title' => !is_null($previous)?$previous->title:'已经是第一篇了',
        ];
        $next_article = [
            'url' => !is_null($next)?Url::current(['id'=>$next->id]):'javascript:void(0);',
            'title' => !is_null($next)?$next->title:'已经是最后一篇了',
        ];
        return [
            'prev' => $prev_article,
            'next' => $next_article
        ];

    }
}