<?php
	$actual_link = explode('catalog/controller', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	$r_link = $actual_link[0].'index.php?route=extension/payment/fastpgesun/callback';
	
	$order_info = explode(',', $_GET['DATA']);
	$aryTemp = array();
	foreach ($order_info as $value){
		$aryStr = explode('=',$value);
		$aryTemp[$aryStr[0]] =  $aryStr[1];
	}	
	$postdata = http_build_query($aryTemp);
	$options = array(
		'http' => array(
		'method' => 'POST',
		'header' => 'Content-type:application/x-www-form-urlencoded',
		'content' => $postdata
	    )
	);
	$context = stream_context_create($options);
	$result = file_get_contents($r_link, false, $context);
	header("Location: $result");