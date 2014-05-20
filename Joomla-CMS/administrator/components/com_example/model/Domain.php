<?php
/**
* Created by Herman Peeren, Yepr
* August 2013
* GPL
*
* INTA-model
*/

namespace Example\model;
use Jooctrine\Model,
	Doctrine\ORM\Query,
	Doctrine\ORM\QueryBuilder;
use Example\model\entities\Person as Person;

// Protect from unauthorized access
defined('_JEXEC') or die();

Class Domain extends Model
{
	// gerenderde stukjes voor view, output
	private $drpVaart = null;

	// gemaakte keuzes, state
	private $vaartId = 0;


	protected $current_id = 0;
	protected $current_region = null;
	protected $namesearch = null;
	protected $countrysearch = null;
	protected $organisationsearch = null;

	/**
	 * Method to get the persons
	 *
	 */
	public function getPersons() {

		$language = \JFactory::getLanguage()->getTag();
		//$dql  = "SELECT p FROM entity:Person p";
		//$dql .= " WHERE p.language IN ('" . $language . "','*')";
		//$dql .= " ORDER BY p.LastName ASC"; // no, there is no lastname-field: that is the cb-field, so you should join them!

		//$query = $this->em->createQuery($dql);
		$qb = $this->em->createQueryBuilder();
		$qb->select('p')
			->from('\entity:Person', 'p');
		$query = $qb->getQuery();

		$persons = $query->getResult();

		return $persons;
	}


	/**
	 * Method to get 1 particular  person, filtered by it's id
	 *
	 */
	public function getPerson() {
		/*
		$dql  = "SELECT p FROM entity:Person p";
		$dql .= " WHERE p.id=".$this->current_id;
		$query = $this->em->createQuery($dql);
		$person = $query->getResult();
		if (count($person)) {
			return $person[0];//er moet precies 1 resultaat zijn
		}
		else {
			//$newPerson = new entities\Person; //geef een leeg record terug als geen person gematched
			//return $newPerson;
			return false;
		}
		*/
		return $this->em->find('entity:Person', 51);
	}
	/**
	 * Method to set the current id of an entity
	 *
	 */
	public function setCurrent_id($id) {
		$this->current_id = (int) $id;
	}

	/**
	 * Method to get the participants of a CoC (eager loaded and sorted on lastname)
	 *
	 * @return    ArrayCollection of Person objects with associated Comprofiler-objects
	 */
	public function getAllParticipants($coc_id)
	{
		$entityNamespace = '\\'.$this->getComponentModelNamespace().'\\entities\\';
		$participants = $this->em->getRepository($entityNamespace.'Actorgroup')->getAllParticipants($coc_id);
		return $participants;
	}

	/**
	 * Method to get the animators of a CoC (eager loaded and sorted on lastname)
	 *
	 * @return    ArrayCollection of Person objects with associated Comprofiler-objects
	 */
	public function getAllAnimators($coc_id)
	{
		$entityNamespace = '\\'.$this->getComponentModelNamespace().'\\entities\\';
		$animators = $this->em->getRepository($entityNamespace.'Actorgroup')->getAllAnimators($coc_id);
		return $animators;
	}

	/**
	 * (Temporary) Method to get the participants of a CoC  --- do not use this. Queries are in the Repository now
	 *
	 */
	public function OLDgetParticipants($coc_id) {
		$dql  = "SELECT p FROM entity:Actorgroup_participant c LEFT JOIN c.actor p";
		//$dql .= " WHERE p.id=".$this->current_id;//selecteer op $coc_id
		$query = $this->em->createQuery($dql);
		$persons = $query->getResult();
		return $persons;
	}

	/**
	 * Get INTA-members per current region
	 * uses $this->current_region and looks Persons up in Person-repository (eager loaded with CB-info)
	 *
	 * @return StdObject met name=(string) region en members=(ArrayCollction) persons
	 */
	public function getMembersPerCurrentRegion()
	{
		if ($current_region=$this->getCurrentRegion())
		{
			$region = new \stdClass();
			$region->name = $current_region;

			$entityNamespace = '\\'.$this->getComponentModelNamespace().'\\entities\\';
			$region->members = $this->em->getRepository($entityNamespace.'Person')->getMembersPerRegion($current_region);
			return $region;
		}
		return false;
	}
	/**
	 * Get current region
	 *
	 * @return string: current region
	 */
	public function getCurrentRegion()
	{
		if (!empty($this->current_region))
		{
			return $this->current_region;
		}
		return false;
	}

	/**
	 * set current region
	 *
	 * @return true if valid region is set
	 */
	public function setCurrentRegion($region='Europe')
	{
		// replace underscore with space
		$region= str_replace('_', ' ', $region);
		// validate possible values (? only check everything in lowercase?)
		$allowedRegions = array('Asia', 'Europe', 'Africa', 'South America', 'North America', 'Middle East', 'Australia'); // TODO: deze validatie van mogelijke ingevoerde waarden moet algemener!
		if (!in_array($region, $allowedRegions)) return false;

		$this->current_region = $region;
		return true;
	}

	//getSelectedMembers()
	/**
	 * Select INTA-members based on search-criteria
	 * uses $this->current_region and looks Persons up in Person-repository (eager loaded with CB-info)
	 *
	 * @return StdObject met selectcriteria=(array of strings) criteria en members=(ArrayCollection) persons
	 */
	public function getSelectedMembers()
	{
		$data = new \stdClass();
		$entityNamespace = '\\'.$this->getComponentModelNamespace().'\\entities\\';
		$textcriteria = array(); // the selectcriteria in human-presentable form
		$rawcriteria = array(); // the selectcriteria to feed to the query

		// search on part of name
		if ($namesearch = $this->getNamesearch())
		{
			$textcriteria[] = "last name contains '" . $namesearch . "'";
			$rawcriteria['lastname'] = $namesearch;
		}
		// select on region
		if ($region = $this->getCurrentRegion())
		{
			$textcriteria[] = "from region " . $region ;
			$rawcriteria['cbRegion'] = $region;
		}
		// search on part of country-name
		if ($country = $this->getCountrysearch())
		{
			$textcriteria[] = "country-name contains '" . $country . "'";
			$rawcriteria['country'] = $country;
		}
		// select on region
		if ($organisation = $this->getOrganisationsearch())
		{
			$textcriteria[] = "organisation-name contains '" . $organisation . "'";
			$rawcriteria['company'] = $organisation;
		}

		// Do the search, based on criteria, adding everything to $data and return it
		$data->selectcriteria = $textcriteria;
		$data->members = $this->em->getRepository($entityNamespace.'Person')->getSelectedMembers($rawcriteria);

		return $data;
	}

	/**
	 * Get namesearch (= string to search a name)
	 *
	 * @return string: current region
	 */
	public function getNamesearch()
	{
		if (!empty($this->namesearch))
		{
			return $this->namesearch;
		}
		return false;
	}

	/**
	 * set namesearch (= string to search a name)
	 *
	 * @return true if valid searchstring is set
	 */
	public function setNamesearch($namesearch)
	{
		// TODO: clean for sql-injection!!! Or is this safe??? (comes from JInput).

		$this->namesearch = $namesearch;
		return true;
	}
	/**
	 * Get countrysearch (= string to search a name)
	 *
	 * @return string: current country-search-string
	 */
	public function getCountrysearch()
	{
		if (!empty($this->countrysearch))
		{
			return $this->countrysearch;
		}
		return false;
	}

	/**
	 * set countrysearch (= string to search a country)
	 *
	 * @return true if valid searchstring is set
	 */
	public function setCountrysearch($countrysearch)
	{
		// TODO: clean for sql-injection!!! Or is this safe??? (comes from JInput).

		$this->countrysearch = $countrysearch;
		return true;
	}
	/**
	 * Get organisationsearch (= string to search a organisation-name)
	 *
	 * @return string: current organisation
	 */
	public function getOrganisationsearch()
	{
		if (!empty($this->organisationsearch))
		{
			return $this->organisationsearch;
		}
		return false;
	}

	/**
	 * set organisationsearch (= string to search a organisation-name)
	 *
	 * @return true if valid searchstring is set
	 */
	public function setOrganisationsearch($organisationsearch)
	{
		// TODO: clean for sql-injection!!! Or is this safe??? (comes from JInput).

		$this->organisationsearch = $organisationsearch;
		return true;
	}

	//------------------------------------------------------------------------------------------------------------

	public function getVaartenDropDownList() {
		if (is_null($this->drpVaart)) {
			$vaarten = $this->em->getRepository('entity:Flight')->findBy(array(), array('date' => 'DESC'));;

			// gebruik de jongste vaartId als er nog geen bekend was
			if ($this->vaartId==0) $this->vaartId=$vaarten[0]->getId();

			// maak array voor de dropdownlist
			$options = array();
			//if ($D)
			foreach ($vaarten as $vaart) {// hier is al een loop, dus hier kun je ook die vaartgegevens in elkaar plakken...
				$options[] = \JHTML::_('select.option', $vaart->getId(), $vaart->getVaartGegevens());
			}

			// zet in dropdownlist
			$this->drpVaart = \JHTML::_('select.genericlist', $options, 'VaartId', 'onchange="document.vaartForm.submit()"', 'value', 'text', $this->vaartId);
		}
		return $this->drpVaart;
	}

	public function getVaart() {
		$vaart = $this->em->find('entity:Flight', $this->vaartId);
		return $vaart;
	}

	public function setVaartId($vaart_id) {
		$this->vaartId = (int) $vaart_id;
	}

}