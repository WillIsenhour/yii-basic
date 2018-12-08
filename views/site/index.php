<?php

/* @var $this yii\web\View */

$this->title = 'Weather Gremlin';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>It's Toasted!</h1>
    </div>

    <div class="body-content">

		<p><?=$myAddress?></p>
		<br/>
		<p>Ip-API</p>
		<pre><?php print_r($ipApiData)?></pre>
		<br/>
		<p>Free Geo IP</p>
		<pre><?php print_r($freeGeoIpData)?></pre>
		<br/>
		

    </div>
</div>
