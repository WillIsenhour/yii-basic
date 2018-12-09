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
                    'logout' => ['post']
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
			$ipApiWeather = $this->getWeatherData($ipApiData);
			$freeGeoIpWeather = $this->getWeatherData($freeGeoIpData);
		}

        return $this->render('index', [
			'myAddress' => $myAddress,
			'ipApiData' => $ipApiData,
			'freeGeoIpData' => $freeGeoIpData,
			'ipApiWeather' => $ipApiWeather,
			'freeGeoIpWeather' => $freeGeoIpWeather
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


	
	public function actionGeolocation($ip = 'default')
	{		
		if ($ip === 'default') {
			$ipAddr = $this->getMyIpAddress();
		} else {
			$ip = strtr($ip, '-', '.');
			if(!filter_var($ip, FILTER_VALIDATE_IP)) {
				return $this->asJson(['Cannot parse IP address.']);
			}
			$ipAddr = $ip;
		}		
		$ipApiData = $this->getIpApiData($ipAddr);
		return $this->asJson($ipApiData);
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

	
	/**
	 * Gets location data from the FreeGeoIp API.
	 *
	 * @return array
	 */
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
	
	
	private function getWeatherData($locationData)
	{
		$data = false;
			
		$apiKey = '6103b0f582e78c7382bc6b0cdc06deb8';

		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl('https://api.openweathermap.org/data/2.5/weather')
			->setData([
				'lat' => $locationData['lat'], 
				'lon' => $locationData['lon'],
				'appid' => $apiKey,
				'units' => 'metric'				
			])
			->send();	

		if($response->isOk) {	
			$rawData = $response->getData();
			
			$temperature = array();
			$temperature['current'] = $rawData['main']['temp'];
			$temperature['low'] = $rawData['main']['temp_min'];
			$temperature['high'] = $rawData['main']['temp_max'];
			
			$wind = array();
			$wind['speed'] = $rawData['wind']['speed'];			
			$wind['direction'] = $rawData['wind']['deg'];
			
			$data['ip'] = $locationData['ip'];
			$data['city'] = $locationData['geo']['city'];
			$data['temperature'] = $temperature;
			$data['wind'] = $wind;
		}
			
		return $data;
	}
	
	private function returnError($type)
	{
		$error = [
			1 => 'Cannot parse IP Address.'
		];
		
		return $this->asJson($error[$type]);
	}
}

