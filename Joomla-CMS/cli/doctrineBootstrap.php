<?php
/**
 * Herman Peeren, Yepr, March 2014
 * bootstrapping Doctrine for CLI on this Example project
 *
 */
use Composer\Autoload\ClassLoader,
	Jooctrine\EntityManagerCliFactory;

$config = array
(
	'componentname' => 'example'
);

// Development or not? Default: yes.
if (!isset($config['development'])) $config['development'] = true;

// Load the Composer classloader.
require_once dirname(__DIR__) . "/libraries/vendor/autoload.php";
$loader = new ClassLoader();

// register the Jooctrine-namespace
$loader->setPsr4('Jooctrine\\', array(dirname(__DIR__) . '/libraries/Jooctrine'));

// Classloader for the component-namespace: \Componentname
$componentname = $config['componentname'];
$componentnamespace = ucfirst($componentname) . '\\';
$componentdir_admin = dirname(dirname(__DIR__)) . '/administrator/components/com_' . $componentname;
$loader->setPsr4($componentnamespace, array($componentdir_admin));
$config['componentnamespace_admin'] = $componentnamespace;

$loader->register(true);

// Instantiate Doctrine's EntityManager
$entityManagerFactory = new EntityManagerCliFactory($config);
