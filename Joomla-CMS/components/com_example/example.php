<?php
/**
 * Dispatch com_example
 *
 * Created by Herman Peeren, Yepr
 * August 2013; version 4 May 2014
 * license: GPL
 *
 */
// Protect from unauthorized access
defined('_JEXEC') or die();

// Dispatch com_example, using Jooctrine, by means of a system-plugin
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger( 'onComponentStart', array(array('componentname' => 'example')));