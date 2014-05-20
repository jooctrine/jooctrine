<?php
/**
 * Created by Herman Peeren, Yepr
 * August 2013
 * GPL
 *
 * Get Doctrine's EntityManager (to be injected in the Jooctrine-component's model)
 */

namespace Jooctrine;

use Doctrine\ORM\Tools\Setup,
	Doctrine\ORM\EntityManager,
	Doctrine\Common\EventManager,
	Doctrine\ORM\Events,
	Jooctrine\TablePrefix;

// Protect from access outside Joomla!
defined('_JEXEC') or die();

Class EntityManagerFactory
{
	/**
	 * The name of the component using Jooctrine
	 */
	protected $componentname;

	/**
	 * Is this used for development (true) or production (false)?
	 * @var boolean, default = false (= APC-cache) TODO: look at MemCache-possibilities
	 * N.B.: often no APC on shared hosting so set 'development' to true in componentdispatcher-plugin parameter
	 */
	private $development = false;


	/**
	 * constructor ($config must not be empty!)
	 */
	public function __construct($config = array())
	{
		$this->componentname = $config['componentname'];
		$this->development = $config['development'];
	}

	/**
	 * Initialise Doctrine's EntityManager by setting the location for entities, development-caching, db-credentials etc.
	 */
	public function getEntityManager() {
		$entities_dir = JPATH_ADMINISTRATOR . '/components/com_' . $this->componentname . '/model/entities';
		$isDevMode = $this->development;

		// Event-listener to change the  #__ db-prefix into the current db-prefix
		$prefix = \JFactory::getConfig()->get('dbprefix');
		$evm = new EventManager;
		$tablePrefix = new TablePrefix($prefix);
		$evm->addEventListener(Events::loadClassMetadata, $tablePrefix);

		// Add UTF8 handler to EntityManager
		$evm->addEventSubscriber(
			new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit('utf8', 'utf8_unicode_ci')
		);

		// Annotations-driver. You could use other drivers (like yaml or xml) here too
		$config = Setup::createAnnotationMetadataConfiguration(array($entities_dir), $isDevMode,null, null,false);

		// Add DAY, MONTH and YEAR DQL-functions
		//$config->addCustomStringFunction('DAY', '\DoctrineExtensions\Query\MySql\Day');
		//$config->addCustomStringFunction('MONTH', '\DoctrineExtensions\Query\MySql\Month');
		//$config->addCustomStringFunction('YEAR', '\DoctrineExtensions\Query\MySql\Year');

		// Database-connection configuration
		$conn = $this->getConfigurationOptions();

		// Obtaining the entity manager
		$entityManager = EntityManager::create($conn, $config, $evm);

		// Temporary, slow fix: without first calling getAllClassNames() I get a fatal error:
		// "Call to private method JLoader::_autoload() in Doctrine\Common\ClassLoader on line 228"
		//$classNames = $entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames(); // ---- fix: uncomment this line! ---
		// See: http://stackoverflow.com/questions/4532367/doctrine-not-working-unless-i-explicitly-call-annotationdrivergetallclassnames
		// Exact cause??? How to fix??? Has it anything to do with the order in which autoloading is taking place???
		// I get the error after: getRepository() in Jooctrine\Model line 79

		return $entityManager;
	}

	private function getConfigurationOptions() {
		// Get database configuration options from Joomla's configuration
		$joomlaConfig = \JFactory::getConfig();
		return array(
			'driver' => $joomlaConfig->get('dbtype'),
			'path' => 'database.mysql',
			'charset' => 'utf8',
			'host' => $joomlaConfig->get('host'),
			'dbname' => $joomlaConfig->get('db'),
			'user' =>  $joomlaConfig->get('user'),
			'password' =>  $joomlaConfig->get('password')
		);
	}

}