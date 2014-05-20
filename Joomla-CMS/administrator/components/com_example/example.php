<?php
/**
 * Dispatch com_example
 *
 * Created by Herman Peeren, Yepr
 * 2014
 * license: GPL
 *
 */

// Protect access from outside Joomla
defined('_JEXEC') or die();

// Access check: is this user allowed to access the backend of this component?
if (!JFactory::getUser()->authorise('core.manage', 'com_example'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Dispatch com_example, using Jooctrine, by means of a system-plugin
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger( 'onComponentStart', array(array('componentname' => 'example')));
