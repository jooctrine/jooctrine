<?php
/**
 * Created by Herman Peeren, Yepr
 * August 2013; adjusted for CLI March 2014
 * GPL
 *
 * Get Doctrine's EntityManager (to be used from commandline with Jooctrine)
 */

namespace Jooctrine;

use Doctrine\ORM\Tools\Setup,
	Doctrine\ORM\EntityManager,
	Doctrine\Common\EventManager,
	Doctrine\ORM\Events,
	Jooctrine\TablePrefix;

require_once dirname(dirname(__DIR__)) .'/configuration.php'; //Joomla's configuration

// Protect from access outside Joomla!
//defined('_JEXEC') or die();

Class EntityManagerCliFactory
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
	private $development = true;

	/**
	 * joomla's configuration
	 */
	protected $jconfig;


	/**
	 * constructor ($config must not be empty!)
	 */
	public function __construct($config = array())
	{
		$this->componentname = $config['componentname'];
		$this->jconfig = new \JConfig;
	}

	/**
	 * Initialise Doctrine's EntityManager by setting the location for entities, development-caching, db-credentials etc.
	 */
	public function getEntityManager() {
		$entities_dir = dirname(dirname(__DIR__)) . '/administrator/components/com_' . $this->componentname . '/model/entities';
		$isDevMode = $this->development;

		// Event-listener to change the  #__ db-prefix into the current db-prefix
		$prefix = $this->jconfig->dbprefix;
		$evm = new EventManager;
		$tablePrefix = new TablePrefix($prefix);
		$evm->addEventListener(Events::loadClassMetadata, $tablePrefix);

		// Add UTF8 handler to EntityManager
		$evm->addEventSubscriber(
			new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit('utf8', 'utf8_unicode_ci')
		);

		// Annotations-driver. You could use other drivers (like yaml or xml) here too
		$config = Setup::createAnnotationMetadataConfiguration(array($entities_dir), $isDevMode,null, null,false);

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
		$joomlaConfig = array();

		$joomlaConfig['driver'] = 'pdo_mysql';
		$joomlaConfig['path'] = 'database.mysql';
		$joomlaConfig['charset'] = 'utf8';
		$joomlaConfig['host'] = $this->jconfig->host;
		$joomlaConfig['dbname'] = $this->jconfig->db;
		$joomlaConfig['user'] =  $this->jconfig->user;
		$joomlaConfig['password'] =  $this->jconfig->password;

		return $joomlaConfig;
	}

}