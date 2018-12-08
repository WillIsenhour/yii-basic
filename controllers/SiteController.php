<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;


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
		$myAddress = $this->getMyIpAddress();
		if($myAddress) {
			$ipApiData = $this->getIpApiData($myAddress);		
			$freeGeoIpData = $this->getFreeGeoIpData($myAddress);
		}

        return $this->render('index', [
			'myAddress' => $myAddress,
			'ipApiData' => $ipApiData,
			'freeGeoIpData' => $freeGeoIpData,
		]);
    }


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

	
	/**
	 * Gets the IP address that the device you're on currently is connecting to the internet from.
	 *
	 * @return string
	 */
	private function getMyIpAddress()
	{
		$myAddress = false;
		
		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl('https://api.ipify.org')
			->setData(['format' => 'json'])
			->send();		

		if($response->isOk) {
			$myAddress = $response->getData()['ip'];
		} 
		
		return $myAddress;
	}

	
	/**
	 * Gets location data from the ip-api API.
	 *
	 * @return array
	 */
	private function getIpApiData($ip)
	{
		$data = false;
		
		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl('http://ip-api.com/json/'.$ip)
			->send();		

		if($response->isOk) {			
			$rawData = $response->getData();
			
			$geo = array();
			$geo['service'] = 'ip-api';
			$geo['city'] = $rawData['city'];
			$geo['region'] = $rawData['regionName'];
			$geo['country'] = $rawData['country'];

			$data['ip'] = $ip;
			$data['lat'] = $rawData['lat'];
			$data['lon'] = $rawData['lon'];
			$data['geo'] = $geo;
		}
		
		return $data;
	}

	
	private function getFreeGeoIpData($ip)
	{
		$data = false;

		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl('https://freegeoip.app/json/'.$ip)
			->send();

		if($response->isOk) {			
			$rawData = $response->getData();
			
			$geo = array();
			$geo['service'] = 'freegeoip';
			$geo['city'] = $rawData['city'];
			$geo['region'] = $rawData['region_name'];
			$geo['country'] = $rawData['country_name'];

			$data['ip'] = $ip;
			$data['lat'] = $rawData['latitude'];
			$data['lon'] = $rawData['longitude'];
			$data['geo'] = $geo;
		}
			
		return $data;
	}
}

