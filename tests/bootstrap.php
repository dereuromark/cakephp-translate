<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\SchemaLoader;
use Templating\View\Icon\FontAwesome5Icon;
use TestApp\Application;
use TestApp\Controller\AppController;
use TestApp\Model\Entity\User;
use TestApp\Model\Table\UsersTable;
use TestApp\View\AppView;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');

define('APP', rtrim(sys_get_temp_dir(), DS) . DS . APP_DIR . DS);
if (!is_dir(APP)) {
	mkdir(APP, 0770, true);
}

define('TMP', ROOT . DS . 'tmp' . DS);
if (!is_dir(TMP)) {
	mkdir(TMP, 0770, true);
}
define('TESTS', ROOT . DS . 'tests' . DS);
define('CONFIG', TESTS . 'config' . DS);

define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . APP_DIR . DS);

define('WWW_ROOT', TMP . 'webroot' . DS);

require dirname(__DIR__) . '/vendor/autoload.php';
require CORE_PATH . 'config/bootstrap.php';
require CAKE . 'functions.php';

require ROOT . DS . 'config' . DS . 'bootstrap.php';

Configure::write('App', [
	'encoding' => 'UTF-8',
	'namespace' => 'TestApp',
	'paths' => [
		'templates' => [ROOT . DS . 'tests' . DS . 'test_app' . DS . 'templates' . DS],
	],
]);

Configure::write('TestSuite.errorLevel', E_ALL & ~E_USER_DEPRECATED);

Configure::write('debug', true);

// Disable audit logging during tests to avoid needing audit_logs table everywhere
Configure::write('Translate.disableAuditLog', true);

Configure::write('Yandex.key', env('YANDEX_KEY'));
Configure::write('Transltr.live', env('TRANSLTR_LIVE'));

$cache = [
	'default' => [
		'className' => 'File',
	],
	'_cake_core_' => [
		'className' => 'File',
		'prefix' => 'crud_myapp_cake_core_',
		'path' => CACHE . 'persistent/',
		'serialize' => true,
		'duration' => '+10 seconds',
	],
	'_cake_model_' => [
		'className' => 'File',
		'prefix' => 'crud_my_app_cake_model_',
		'path' => CACHE . 'models/',
		'serialize' => 'File',
		'duration' => '+10 seconds',
	],
];

Cache::setConfig($cache);

Configure::write('Icon', [
	'sets' => [
		'fas' => [
			'class' => FontAwesome5Icon::class,
		],
	],
]);

class_alias(Application::class, 'App\Application');
class_alias(AppController::class, 'App\Controller\AppController');
class_alias(AppView::class, 'App\View\AppView');
class_alias(UsersTable::class, 'App\Model\Table\UsersTable');
class_alias(User::class, 'App\Model\Entity\User');

//Cake\Core\Plugin::getCollection()->add(new \Tools\Plugin());
//Cake\Core\Plugin::getCollection()->add(new \Translate\Plugin());

//DispatcherFactory::add('Routing');
//DispatcherFactory::add('ControllerFactory');

// Ensure default test connection is defined
if (!getenv('DB_URL')) {
	putenv('DB_URL=sqlite:///:memory:');
}

ConnectionManager::setConfig('test', [
	'url' => getenv('DB_URL') ?: null,
	'timezone' => 'UTC',
	'quoteIdentifiers' => true,
	'cacheMetadata' => true,
]);

if (env('FIXTURE_SCHEMA_METADATA')) {
	$loader = new SchemaLoader();
	$loader->loadInternalFile(env('FIXTURE_SCHEMA_METADATA'));
}
