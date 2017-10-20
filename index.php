<?php

error_reporting(E_ALL);

// --- автолоадер ----------------------------------------------------------- //

require_once 'lib/Lava/Autoloader.php';

$al  = new Lava\Autoloader;

$al->registerPrefixes(array(
	'Controller' => 'controllers',
));
$al->extensions      ('php');
$al->register        ();

// --- приложение ----------------------------------------------------------- //

$app = new App (array(

	'type'     => 'html',
	'charset'  => 'UTF-8',

	'langs'    => array(
		'ru-RU' => 'Русский',
		'en-US' => 'English',
	),

	'timezone' => 'UTC',
));


// кодировка
if (function_exists('mb_internal_encoding'))
	mb_internal_encoding($app->conf->charset);

// зона
date_default_timezone_set   ($app->conf->timezone);

// --- контроллёры ---------------------------------------------------------- //

// главная страница
$app	->route	()
	->name	('index')
	->to	('Controller\Common', 'index');

// язык
$app	->route	('lang/:code')
	->name	('lang')
	->to	('Controller\Common', 'lang');

// окружение
$app	->route	('env')
	->name	('env')
	->to	('Controller\Common', 'env');

// ссылки
$app	->route	('link')
	->name	('link')
	->to	(function($app) {
		include 'templates/link.php';
	});

// --- 404 ------------------------------------------------------------------ //

if (! $app->route_match())
	$app->render(array(
		'json' => array('error' => 'not-found'),
		function($app) {

			header('HTTP/1.0 404 Not Found');

			if ($app->type() == 'html')
				include 'templates/not-found.php';
		},
	));

?>
