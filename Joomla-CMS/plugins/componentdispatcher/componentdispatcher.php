<?php
/**
 * @package		Jooctrine: using Doctrine ORM in Joomla!
 * @copyright 	Copyright (c) 2013 - 2014 Herman Peeren, Yepr
 * @license 	GNU General Public License version 3 or later
 *
 * Create and dispatch a component based on Jooctrine (= Doctrine ORM in Joomla!)
 */

// No direct access outside Joomla!.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

use Composer\Autoload\ClassLoader,
	Jooctrine\EntityManagerFactory;

class plgSystemComponentdispatcher extends JPlugin
{
	
	function onComponentStart(array $config = array())
	{
		$app = JFactory::getApplication();

		// Development or not? Default: yes. TODO: plugin-param for this default
		if (!isset($config['development'])) $config['development'] = true;
		$config['isAdmin'] = $app->isAdmin();

		// Load the Composer classloader.
		require_once JPATH_LIBRARIES . "/vendor/autoload.php";
		$loader = new ClassLoader();

		// register the Jooctrine-namespace
		$loader->setPsr4('Jooctrine\\', array(JPATH_LIBRARIES . '/Jooctrine'));

		// register the DoctrineExtensions-namespace
		//$loader->setPsr4('DoctrineExtensions\\', array(JPATH_LIBRARIES . '/DoctrineExtensions'));

		// Classloaders for the component-namespaces
		$componentname = $config['componentname'];

		// Admin = main component-namespace: \Componentname
		$componentnamespace = ucfirst($componentname) . '\\' ;
		$componentdir_admin = JPATH_ROOT . '/administrator/components/com_' . $componentname;
		$loader->setPsr4($componentnamespace,$componentdir_admin);
		$config['componentnamespace_admin'] = $componentnamespace;

		// Site - namespace only used for site-views: \Componentname\Site
		$componentnamespace_site = $componentnamespace . 'Site\\';
		$componentdir_site = JPATH_ROOT . '/components/com_' . $componentname;
		$loader->setPsr4($componentnamespace_site, $componentdir_site);
		$config['componentnamespace_site'] = $componentnamespace_site;

		$loader->register(true);

		// The view-namespace is either from the front-end or admin-side
		$config['viewnamespace']  = $config['isAdmin']?$componentnamespace:$componentnamespace_site;
		$config['viewnamespace'] .= 'views\\';

		// Instantiate the component
		$component_fqn = $componentnamespace . 'Component';
		$component = new $component_fqn($config);

		// Instantiate Doctrine's EntityManager
		$entityManagerFactory = new EntityManagerFactory($config);
		$em = $entityManagerFactory->getEntityManager();

		// Short alias for entities namespace (to be used in DQL)
		$entitiesnamespace = $componentnamespace . 'model\\entities';
		$em->getConfiguration()->addEntityNamespace('entity', $entitiesnamespace);

		// Instantiate the model
		$model_fqn = $componentnamespace . 'model\\Domain';
		$model = new $model_fqn($config);

		// Inject the Entitymanager into the model and inject  the model into  the component
		$model->setEntityManager($em);
		$component->setModel($model);

		// Start the component, given the input
		$input = $app->input;
		$component->execute($input);
	}
}