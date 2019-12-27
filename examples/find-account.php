<?php
include_once '../vendor/autoload.php';

$address = "";
$ripple = new \MaxMadKnight\RippleAPI\Ripple($address);

dump($ripple->getAccount());
