<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
// use app\models\LoginForm;
use app\models\ContactForm;
use app\models\GremlinForm;

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
     * Displays homepage, handles web interface demo.
     *
     * @return string
     */
    public function actionIndex()
    {
		$model = new GremlinForm();		
		$myAddress = $this->getMyIpAddress();
		$dataOutput = "It's toasted...";
		
        if ($model->load(Yii::$app->request->post())) {

			$ip = $model['ip'] != '' ? $model['ip'] : $myAddress;
			$service = $model['service'] != '' ? $model['service'] : 'default';
error_log('service: ' . $model['service']);			
error_log('operation: ' . $model['operation']);
			if($model['operation'] == 'geolocation') {
				$rawData = $this->getLocationData($ip, $service);
			} else if ($model['operation'] == 'weather') {
				$rawData = $this->getWeatherData($this->getLocationData($ip, $service));
			} else {
				$rawData = $this->getLocationData($ip, $service);
			}	
			$dataOutput = print_r($rawData, true);
			$model = new GremlinForm();			
		}

        return $this->render('index', [
			'model' => $model, 
			'myAddress' => $myAddress,
			'dataOutput' => $dataOutput
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
	 * The geolocation endpoint. 
	 *
	 * @return json
	 */
	public function actionGeolocation($ip = 'default', $service = 'default')
	{		
		return $this->asJson($this->getLocationData($ip, $service));
	}

	
	/**
	 * The weather endpoint. 
	 *
	 * @return json
	 */
	public function actionWeather($ip = 'default', $service = 'default')
	{
		$locationData = $this->getLocationData($ip, $service);
		return $this->asJson($this->getWeatherData($locationData));
	}
		

	/** 
	 * Does the location work. Accepts ip addresses after a slash in the format '8-8-8-8' 
	 * as opposed to '8.8.8.8'. Also accepts a service option as '...?service=[option]'
	 *
	 * @return array
	 */
	private function getLocationData($ip, $service)
	{
error_log('ip in ' . __FUNCTION__ . ': ' . $ip);
		if ($ip === 'default') {
			$ipAddr = $this->getMyIpAddress();
		} else if (filter_var($ip, FILTER_VALIDATE_IP)) {
			$ipAddr = $ip;
		} else {
			$ip = strtr($ip, '-', '.');
			if(!filter_var($ip, FILTER_VALIDATE_IP)) {
				return array('Cannot parse IP address. Format as: NNN-NNN-NNN-NNN');
			}
			$ipAddr = $ip;
		}

		if ($service === 'default' || $service === 'ip-api') {
			$data = $this->getIpApiData($ipAddr);			
		} else if ($service === 'freegeoip') {
			$data = $this->getFreeGeoIpData($ipAddr);
		} else {
			return array('Invalid service option, will accept: default|ip-api|freegeoip');
		}
		
		return $data;	
	}
	

	/**
	 * Gets the IP address that the device you're on currently is connecting to the internet from.
	 *
	 * @return string
	 */
	private function getMyIpAddress()
	{
		$myAddress = false;
		$url = 'https://api.ipify.org';
		
		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl($url)
			->setData(['format' => 'json'])
			->send();		

		if($response->isOk) {
			$myAddress = $response->getData()['ip'];
		} else {
			return $this->responseError($url, $response);
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
		$url = 'http://ip-api.com/json/';

		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl($url.$ip)
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
		} else {
			return $this->responseError($url, $response);
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
		$url = 'https://freegeoip.app/json/';

		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl($url.$ip)
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
		} else {
			return $this->responseError($url, $response);
		}
			
		return $data;
	}
	
	
	/**
	 * Takes the location data generated by one of the other APIs and
	 * uses it to get weather information from Openweathermap.
	 *
	 * @return array
	 */
	private function getWeatherData($locationData)
	{
		$data = false;
		$url = 'https://api.openweathermap.org/data/2.5/weather';
		$apiKey = '6103b0f582e78c7382bc6b0cdc06deb8';

		$client = new Client();
		$response = $client->createRequest()
			->setMethod('GET')
			->setUrl($url)
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
		} else {
			return $this->responseError($url, $response);
		}
			
		return $data;
	}
	
	private function responseError($url, $response) 
	{
		return array('Uh oh... '.$url.' responded with '.$response->getStatusCode());
	}
	
	// private function returnError($type)
	// {
		// $error = [
			// 1 => 'Cannot parse IP Address.'
		// ];
		
		// return $this->asJson($error[$type]);
	// }
}

