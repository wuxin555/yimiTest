<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Images;

/**
 * ImagesSearch represents the model behind the search form of `app\models\Images`.
 */
class ImagesSearch extends Images
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['image_id', 'storage', 'image_name', 'ident', 'url', 'l_ident', 'l_url', 'm_ident', 'm_url', 's_ident', 's_url', 'watermark'], 'safe'],
            [['width', 'height', 'last_modified'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Images::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'width' => $this->width,
            'height' => $this->height,
            'last_modified' => $this->last_modified,
        ]);

        $query->andFilterWhere(['like', 'image_id', $this->image_id])
            ->andFilterWhere(['like', 'storage', $this->storage])
            ->andFilterWhere(['like', 'image_name', $this->image_name])
            ->andFilterWhere(['like', 'ident', $this->ident])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'l_ident', $this->l_ident])
            ->andFilterWhere(['like', 'l_url', $this->l_url])
            ->andFilterWhere(['like', 'm_ident', $this->m_ident])
            ->andFilterWhere(['like', 'm_url', $this->m_url])
            ->andFilterWhere(['like', 's_ident', $this->s_ident])
            ->andFilterWhere(['like', 's_url', $this->s_url])
            ->andFilterWhere(['like', 'watermark', $this->watermark]);

        return $dataProvider;
    }
}
