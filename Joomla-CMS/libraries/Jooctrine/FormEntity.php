<?php
/**
 * Created by Herman Peeren, Yepr
 * August - November 2013
 * GPL
 *
 * Description of the entity to use in Jooctrine to give information to the model from the view about the entitiy to be retrieved
 */

namespace Jooctrine;

// Protect from unauthorized access
defined('_JEXEC') or die();

Class FormEntity
{
	public $name;
	public $label;
	public $filter;
	public $ordering;
}