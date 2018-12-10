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
            <div class="col-xs-12">
				<div id="dataOutput">
					<pre>
						<?=$dataOutput?>
					</pre>
				</div>
                <?php $form = ActiveForm::begin(['id' => 'gremlin-form']); ?>
					<div  class="form-group">
						<?= $form->field($model, 'ip')
							->textInput([
								'autofocus' => true, 
								'placeholder' => $myAddress
							])->label("IP Address")
						?>
					</div>
					<div  class="form-group">
						<?= $form->field($model, 'operation')
							->inline()
							->radioList([
								'geolocation' => 'Geolocation', 
								'weather' => 'Weather'
							])->label("Which Operation?") 
						?>
					</div>
					<div  class="form-group">
						<?= $form->field($model, 'service')
							->inline()
							->radioList([
								'ip-api' => 'IP-API', 
								'freegeoip' => 'Freegeoip'
							])->label("Which Service?") 
						?>
					</div>
                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
		    </div>
        </div>
    </div>
</div>
