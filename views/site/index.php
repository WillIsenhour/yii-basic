<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\GremlinForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Weather Gremlin';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="body-content">
        <div class="row">
		
			<div id="inputForm" class="col-xs-4">
				<?php $form = ActiveForm::begin(['id' => 'gremlin-form']); ?>
				<div class="row">
					<div  class="form-group col-xs-12">
						<?= $form->field($model, 'ip')
							->textInput([
								'autofocus' => true, 
								'placeholder' => $myAddress
							])->label("IP Address")
						?>
					</div>
				</div>
				<div class="row">
					<div  class="form-group col-xs-12">
						<?= $form->field($model, 'operation')
							->inline()
							->radioList([
								'geolocation' => 'Geolocation', 
								'weather' => 'Weather'
							])->label("Which Operation?") 
						?>
					</div>
				</div>
				<div class="row">					
					<div  class="form-group col-xs-12">
						<?= $form->field($model, 'service')
							->inline()
							->radioList([
								'ip-api' => 'IP-API', 
								'freegeoip' => 'Freegeoip'
							])->label("Which Service?") 
						?>
					</div>
				</div>			
				<div class="row">				
					<div class="form-group col-xs-12">
						<?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
					</div>
				</div>				
				<?php ActiveForm::end(); ?>
			</div>

			<div id="gremlin" class="col-xs-3">
			</div>				
			
			<div id="dataOutput" class="col-xs-5">				
				<?php if (is_array($dataOutput)) {
					echo '<pre>'.print_r($dataOutput, true).'</pre>';
				} else {
					echo $dataOutput;
				}
				?>
			</div>

		</div>

	</div>
</div>

