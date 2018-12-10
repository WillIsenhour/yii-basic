<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class GremlinForm extends Model
{
	public $ip;
	public $operation;
	public $service;
	
	public function rules() 
	{
		return [
			[['operation', 'service'], 'string'],
			['ip', 'ip', 'ipv6' => false]
		];
	}
}