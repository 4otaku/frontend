<?php

include_once 'static.php';

use Otaku\Framework\Config;
use Otaku\Framework\Http;
use Otaku\Framework\Session;
use Otaku\Framework\Query;
use Otaku\Art\Module\Main as Module;

include_once 'framework/init.php';

define('API', ROOT_DIR . SL . 'api' . SL);
define('API_LIBS', API . 'libs' . SL);
define('API_IMAGES', API . 'images' . SL);

new Autoload(array(
	'Art' => LIBS,
	'Api' => API_LIBS,
	'Framework' => FRAMEWORK_LIBS
), FRAMEWORK_EXTERNAL);

mb_internal_encoding('UTF-8');

$config = Config::getInstance();
$config->parse(CONFIG . SL . 'define.ini', true);
$config->parse(CONFIG . SL . 'settings.ini');
$config->add(['safe' => ['mode' => $safeMode]], true);

$domain = $config->get('site', 'domain');
if ($domain && $domain != $_SERVER['SERVER_NAME']) {
	$url = 'http://'.$domain.$_SERVER['REQUEST_URI'];
	Http::redirect($url, true);
}

$session = Session::getInstance();
$session->init();
$config->add($session->get_data());

$query = new Query($_SERVER['REQUEST_URI'],
	array_replace($_POST, $_GET));
unset ($_GET, $_POST);

\RainTPL::configure('tpl_dir', TPL . SL);
\RainTPL::configure('cache_dir', CACHE . SL . 'tpl' . SL);
\RainTPL::configure('path_replace', false);

$module = new Module($query);
$request = $module->gather_request();

$request->perform();

$module->dispatch();
