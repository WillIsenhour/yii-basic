<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

use yii\httpclient\Client;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		
		$myAddress = 'foo';
//		$myAddress = Yii::$app->request->getUserIP();
		
//		$myAddress = Requests::get('https://api.ipify.org');
		
		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
     	    ->setFormat(Client::FORMAT_JSON)
			->setUrl('https://api.ipify.org')
			->send();
		if($response->isOk) {
			$myAddress = $response;
		} else {
			$myAddress = "Something's... not right...";
		}

        return $this->render('index', ['myAddress' => $myAddress]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    // public function actionLogin()
    // {
        // if (!Yii::$app->user->isGuest) {
            // return $this->goHome();
        // }

        // $model = new LoginForm();
        // if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // return $this->goBack();
        // }

        // $model->password = '';
        // return $this->render('login', [
            // 'model' => $model,
        // ]);
    // }

    /**
     * Logout action.
     *
     * @return Response
     */
    // public function actionLogout()
    // {
        // Yii::$app->user->logout();

        // return $this->goHome();
    // }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}

