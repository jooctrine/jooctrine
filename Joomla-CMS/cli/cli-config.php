<?php
/*
 * Herman Peeren, Yepr, March 2014
 * use Doctrine CLI to generate tables, getters/setters etc.
 */
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// file to this project's Doctrine bootstrap
require_once 'doctrineBootstrap.php';

// retrieve EntityManager in this application
$entityManager = $entityManagerFactory->getEntityManager();

return ConsoleRunner::createHelperSet($entityManager);