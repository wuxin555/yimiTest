<?php

namespace app\controllers;

use app\helpers\lib_image_imagick;
use Imagine\Image\ManipulatorInterface;
use Yii;
use app\models\Images;
use app\models\ImagesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\imagine\Image;

/**
 * ImagesController implements the CRUD actions for Images model.
 */
class ImagesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Images models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ImagesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Images model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Images model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Images();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->image_id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Images model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->image_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Images model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Images model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Images the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Images::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
    /*
    *通过id，宽度，后缀生成所需图片格式
    **/
    public function actionCreateImage(){
        /*
         * 1.获取参数 img_id,width,height,extension等参数
         * 2.从缓存中查询该图片是否存在，10个级距，若存在，直接返回图片
         * 3.从数据库查询图片路径
         * 4.通过判断该图片是否存在,若不存在，返回false
         * 5.将图片进行方法缩小改格式等操作。取其内容存入缓存文件中
         */

         $img = yii::$app->request->get("img");

        //获取图片的参数 width height img_id extension
//        $img = '000117ade10eed8a3640b44a22de45ae-w200-h190.jpg';
        $img = explode('.', $img);
        $extension = $img[1]; //图片格式

        $imgInfo = explode('-', $img[0]);
        $imgId = null;
        $width = null;
        $height = null;
        foreach ($imgInfo as &$v) {
            if (strstr($v, 'w')) {
                $width = substr($v,1); //图片宽度
            } else if (strstr($v, 'h')) {
                $height = substr($v,1); //图片高度
            } else {
                $imgId = $v; //图片id
            }
        }

        //判断传入值是否正确
        $imgTypes = ['jpg' , 'png' , 'webp'];
        if (!$imgId || !in_array($extension , $imgTypes)) {
            $arr = ['code' => 1 , 'msg' => '参数错误'];
            return json_encode($arr);
        }

        include '../helper/imagick.class.php';
        //从缓存中读取图片  10 个级距，放入
        for ($i=0;$i<=5;$i++) {
            $keys = [];
            if ($width && !$height) {
                //只有宽度 无高度的情况
                $key1 = $imgId.'-w'.($width+$i)."-".$extension;
                $key2 = $imgId.'-w'.($width-$i)."-".$extension;
                array_push($keys , $key1 , $key2);
            } else if ($height && !$width) {
                //只有高度 无宽度的情况
                $key1 = $imgId.'-h'.($height+$i)."-".$extension;
                $key2 = $imgId.'-h'.($height-$i)."-".$extension;
                array_push($keys , $key1 , $key2);
            } else if ($width && $height) {
                for ($j=0;$j<=5;$j++) {
                    //获取同宽度下高度级距为10的数据
                    $key1 = $imgId.'-w'.($width+$i).'-h'.($height+$j)."-".$extension;
                    $key2 = $imgId.'-w'.($width+$i).'-h'.($height-$j)."-".$extension;
                    $key3 = $imgId.'-w'.($width-$i).'-h'.($height+$j)."-".$extension;
                    $key4 = $imgId.'-w'.($width-$i).'-h'.($height-$j)."-".$extension;
                    array_push($keys , $key1 , $key2 , $key3 , $key4);
                }
            }

            $keys = array_unique($keys);
            foreach ($keys as &$v) {
                if (yii::$app->cache->get($v)) {
                    //若缓存中存在该图片
                    $imgInfo = yii::$app->cache->get($v);
                    $imageBlob = base64_decode($imgInfo);
                    //从二进制中读出图片
                    $image = new lib_image_imagick();
                    $image->readImageByBlob($imageBlob , $extension);
                    $image->output();
                    exit;
                }
            }
        }

        //缓存中不存在，获取图片路径
        $image = Images::find()->select('image_id , url')->where(['image_id' => $imgId])->asArray()->one();
        $path = Yii::getAlias('@webroot');
        $imgPath = $path.'/'.$image['url'];

        //判断图片是否存在
        if (!file_exists($imgPath)) {
            $arr = ['code' => 2 , 'msg' => '图片信息不存在'];
            return json_encode($arr);
        }

        //处理图片
        $image = new lib_image_imagick();
        $image->open($imgPath);
        $image->resize_to($width, $height, 'scale');
        $image->set_type($extension);

        //将图片放入缓存
        $key = implode("-" , $img);
        $filePath = $path.'/'.$key;
        $image->save_to($filePath);
        $content = file_get_contents($filePath);
        unlink($filePath);

        yii::$app->cache->set($key , base64_encode($content));
        $image->output();
    }
}
