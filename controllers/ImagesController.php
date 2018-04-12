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
         * 2.通过img_id查询原图信息，获取原始width,height，url
         * 3.通过传进来的width，height和原始图片的width,height判断最接近与图片中的哪个级距（1-10 按比例改变图片大小，级距越大，图片越大）
         * 4.从缓存中查询该img_id是否存在,若存在，获取图片信息
         * 5.若缓存中不存在，
         *      1）将图片信息存于数组,根据其图片类型区分，存入编码后的图片二进制流，
         *          ['jpg' => [['width*height' => 'ewe'],['20*30' => 'ewe'],...],
         *          'png' => [['20*30' => 'ewe'],['20*30' => 'ewe']],
         *          'webp' => [['20*30' => 'ewe'],['20*30' => 'ewe']]
         *          ]
         *      2）另外通过第三部拿到的最接近的级距和图片类型，拿到要展示的图片二进制流
         * 6.将数组存入缓存，以img_id为键值
         */

        $img = yii::$app->request->get("img");

        //获取图片的参数 width height img_id extension
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

        //获取图片信息 img_id url width height
        $originImage = Images::find()->select('image_id,url,width,height')->where(['image_id' => $imgId])->asArray()->one();
        $originWidth = $originImage['width'];
        $originHeight = $originImage['height'];
        $path = Yii::getAlias('@webroot');
        $imgPath = $path.'/'.$originImage['url'];

        //判断图片是否存在
        if (!file_exists($imgPath)) {
            $arr = ['code' => 2 , 'msg' => '图片信息不存在'];
            return json_encode($arr);
        }

        //判断当前图片尺寸最接近第几个级距，图片尺寸随级距依次增大
        $num = $this->getImageFittingPosition($width , $height , $originWidth , $originHeight);

        include '../helper/imagick.class.php';
        //从缓存中读取图片
        if (yii::$app->cache->get($imgId)) {
            $imageCache = yii::$app->cache->get($imgId);
            $key = round($originWidth/10*$num)."*".round($originHeight/10*$num); //获取最接近的width和height
            $imageCache = $imageCache[$extension];//获取缓存里该图片类型下的所有图片信息（10个）
            $imgInfo = $imageCache[$key];
            if ($imgInfo) {
                $imageBlob = base64_decode($imgInfo);
                //从二进制中读出图片
                $image = new lib_image_imagick();
                $image->readImageByBlob($imageBlob , $extension);
                $image->output();
                exit;
            }
        }

        //缓存中不存在，处理图片
        $image = new lib_image_imagick();
        $image->open($imgPath);
        $imagesArr = [];
        $imageBlob = '';//要展示的图片二进制
        for ($i = 1 ; $i <= 10 ; $i++) {
            $width = round($originWidth/10*$i);
            $height = round($originHeight/10*$i);

            $image->resize_to($width, $height, 'scale');
            foreach ($imgTypes as $v) {
                $image->set_type($extension);
                //将图片放入数组中 ['jpg' => [['20*30' => 'ewe'],['20*30' => 'ewe']]]
                $filePath = $path.'/'.$imgId;
                $image->save_to($filePath);
                $content = file_get_contents($filePath);
                $imagesArr[$v][$width.'*'.$height] = base64_encode($content);
                unlink($filePath);

                if ($num == $i && $v == $extension) {
                    //通过最接近的级距和图片类型，拿到要展示的图片二进制流
                    $imageBlob = $content;
                }
            }
        }

        //将该图片的信息存入缓存，以img_id为键值
        yii::$app->cache->set($imgId , $imagesArr);
        //通过二进制读取图片
        $image->readImageByBlob($imageBlob , $extension);
        $image->output();
    }
    /*
     *判断所要获取的图片最接近的级距位置，返回第几级距，级距越大，图片尺寸越大
     */
    public function getImageFittingPosition($currentWidth = '' , $currentHeight = '' , $originWidth , $originHeight){
        if ($currentWidth) {
            //传进来的width > 原图的width , 返回第10级距（返回原图大小）
            if ($currentWidth > $originWidth) {
                $num = 10;
            } else {
                $num = $currentWidth*10/$originWidth;
                if ($num < 1) {
                    //传进来的width < 最小图的width , 返回第1级距
                    $num = 1;
                } else {
                    //四舍五入取整
                    $num = round($num);
                }
            }
        } else if ($currentHeight) {
            //传进来的height > 原图的height , 返回第10级距（返回原图大小）
            if ($currentHeight > $originHeight) {
                $num = 10;
            } else {
                $num = $currentHeight*10/$originHeight;
                if ($num < 1) {
                    //传进来的height < 最小图的height , 返回第1级距
                    $num = 1;
                } else {
                    //四舍五入取整
                    $num = round($num);
                }
            }
        } else {
            //没有传进width , height参数 , 返回第10级距（返回原图大小）
            $num = 10;
        }

        return $num;
    }
}
