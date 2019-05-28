<?php
namespace Lorem;
/**
 * A model class that supports
 * data fetching, validation and saving
 */
use Contentomat\DBCex;
use Contentomat\Contentomat;
use Contentomat\PsrAutoloader;
use Contentomat\Debug;
use Contentomat\FieldHandler;
use Contentomat\ApplicationHandler;
use Contentomat\TableMedia;;
use Contentomat\Parser;
use Contentomat\CmtPage;
use \Exception;


class Model {
	
	/**
	 * @var Object
	 */

	/**
	 * Contentomat Object instance
	 * @var Object
	 */
	protected $cmt;

	/**
	 * @var Object
	 */
	protected $PsrAutoloader;

	/**
	 * @var \Contentomat\CmtPage
	 */
	protected $CmtPage;

	/**
	 * @var \Contentomat\TableMedia
	 */
	protected $TableMedia;

	/**
	 * @var String
	 */
	protected $tableName;

	/**
	 * @var Array
	 */
	protected $fields;

	/**
	 * @var Array
	 */
	protected $filter;

	/**
	 * @var Array
	 */
	protected $order;

	/**
	 * @var Int
	 */
	protected $limit;

	/**
	 * @var string
	 */
	protected $language;


	/**
	 * @var Array
	 */
	protected $belongsTo = [];

	/**
	 * @var Array
	 */
	protected $hasOne = [];

	/**
	 * @var Array
	 */
	protected $hasMany = [];


	/**
	 * @var Object
	 */
	protected $FieldHandler;

	/**
	 * @var Object
	 */
	protected $Parser;

	/**
	 * @var Array;
	 */
	protected $validationErrors = [];

	/**
	 * @var Array
	 */
	protected $validationRules = [];

	/**
	 * @var string
	 */
	protected $formTemplatesPath = PATHTOWEBROOT . 'templates/forms/';


	/**
	 * @var id  The CMT Application ID
	 */
	protected $applicationId;

	/**
	 * @var Array  The CMT Application Settings
	 */
	protected $applicationSettings;

	public function __construct () {

		$this->db = new DBCex ();
		$this->cmt = Contentomat::getContentomat ();
		$this->PsrAutoloader = new PsrAutoloader ();
		$this->Parser = new Parser ();
		$this->FieldHandler = new FieldHandler ();
		$this->fields = [];
		$this->filter = [];
		$this->order = [];
		$this->limit = -1;
		// $this->belongsTo = [];
		// $this->hasMany = [];

		$this->ApplicationHandler = new ApplicationHandler();
		$this->CmtPage = new CmtPage();
		$this->TableMedia = new TableMedia();

		// Set system default language
		$this->setLanguage(DEFAULTLANGUAGE);

		$this->init ();
	}



	public function init () {
	}



	/**
	 * Setter for `language`
	 *
	 * @access public
	 * @param string 			The language to use, defaults to 'de'
	 * @return void
	 */
	public function setLanguage ($language = 'de') {
		$this->language = $language;
	}



	public function getLanguage() {
		return $this->language;
	}



	/**
	 * Setter for `tableName`
	 *
	 * @access public
	 * @param string 			The mysql table name
	 * @return void
	 */
	public function setTableName ($tableName) {
		$this->tableName = $tableName;
	}


	/**
	 * Getter for tableName
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}


	/**
	 * Setter for formTemplatesPath
	 * 
	 * @access public
	 * @param string
	 * @return void
	 */
	public function setFormTemplatesPath ($formTemplatesPath) {
		$this->formTemplatesPath = $formTemplatesPath;
	}


	/**
	 * Getter for formrTemplatesPath
	 *
	 * @access public
	 * @return string
	 */
	public function getFormTemplatesPath () {
		return $this->formrTemplatesPath;
	}



	public function order ($order = []) {
		$this->order = $order;
		return $this;
	}



	public function filter ($filter = []) {
		$this->filter = $filter;
		return $this;
	}



	public function limit ($limit = -1) {
		$this->limit = $limit;
		return $this;
	}



	public function fields ($fields = []) {
		$this->fields = $fields;
		return $this;
	}



	/**
	 * findAll
	 *
	 * apply all filters and options and fetch all records
	 *
	 * @access public
	 * @param Array 		Options
	 * @return Array
	 * @throws Exception
	 *
	 * Available Options
	 * - fetchAssociations 		Boolean 	Whether to fetch asscoiated records too
	 */
	public function findAll($options = []) {

		$options = array_merge([
			'fetchAssociations' => true
		], $options);

		$query = sprintf("SELECT %s FROM %s %s %s %s",
			empty($this->fields) ? "*" : join(',', $this->fields),
			$this->tableName,
			empty($this->filter) ? "" : "WHERE " . $this->getFilterString(),
			empty($this->order) ? "" : "ORDER BY " . $this->getOrderString(),
			$this->limit == -1 ? "" : sprintf("LIMIT %u", $this->limit)
		);

		
		if ($this->db->query($query) != 0) {
			throw new Exception("Query failed: " . $query);
		}
		$results = $this->db->getAll();

		if ($options['fetchAssociations']) {
			foreach ($results as &$result) {
				$result = $this->fetchAssociations($result);
			}
		}

		foreach ($results as &$result) {
			$result = $this->afterRead($result);
		}

		return $results;
	}



	/**
	 * findOne
	 *
	 * apply all filters and options and fetch one record
	 *
	 * @access public
	 * @param Array 		Options
	 * @return Array 		The record's data
	 * @throws Exception
	 *
	 * Available Options
	 * - fetchAssociations 		Boolean 	Whether to fetch asscoiated records too
	 */
	public function findOne($options = []) {

		$options = array_merge ([
			'fetchAssociations' => true
		], $options);

		$query = sprintf("SELECT %s FROM %s %s %s LIMIT 1",
			empty($this->fields) ? "*" : join(',', $this->fields),
			$this->tableName,
			empty($this->filter) ? "" : "WHERE " . $this->getFilterString (),
			empty($this->order) ? "" : "ORDER BY " . $this->getOrderString ()
		);
		if ($this->db->query ($query) != 0) {
			throw new Exception ("Query failed: " . $query);
		}

		$results = $this->db->getAll ();
		if (count ($results) > 0)  {

			$result = array_shift($results);

			// if ($options['fetchAssociations']) {
			// 	$result = $this->fetchAssociations($result);
			// }

			$result = $this->afterRead($result);
			return $result;
		}
		else {
			return null;
		}
	}


	/**
	 * find by id, for convenience
	 * @param integer
	 * @return Array
	 */
	public function findById($id) {
		return $this->filter(compact('id'))->findOne();
	}

	/**
	 * Retrieve a certain field's value from a record
	 *
	 * @access public
	 * @throws Exception
	 * @param int 			ID of the record
	 * @param string 		Field name
	 * @return mixed
	 */
	public function getFieldValue($id, $fieldName) {


		$query = sprintf("SELECT %s FROM %s WHERE id=%u", $fieldName, $this->tableName, $id);
		if ($this->db->query($query) != 0) {
			throw new Exception("Query failed: " . $query);
		}
		$rec = $this->db->get();
		if (empty($rec[$fieldName])) {
			throw new Exception("No result");
		}
		return $rec[$fieldName];
	}
	


	/**
	 * A filter is passed like this:
	 * [
	 *     'operand1' => $operand2,
	 * ]
	 */
	private function getFilterString () {

		$strings = [];

		foreach ($this->filter as $operand1 => $operand2) {
			$operator = '=';
			if (preg_match('/^(.*)\s+([\=\!\>\<\%]+)\s*$/', $operand1, $matches)) {
				$operand1 = $matches[1];
				$operator = $matches[2];
			}
			$strings[] = sprintf ('%s %s %s', $operand1, $operator, $operand2);
		}

		return join (' AND ', $strings);
	}



	/**
	 * An order is passed like this:
	 * [
	 *     'field' => 'direction',
	 * ]
	 */
	private function getOrderString () {

		$strings = [];

		foreach ($this->order as $field => $direction) {
			switch (strtolower ($direction)) {
				case '<':
				case 'd':
				case 'desc':
					$direction = 'DESC';
					break;
				case '>':
				case 'a':
				case 'asc':
					$direction = 'ASC';
					break;
			}

			$strings[] = sprintf ('%s %s', $field, $direction);
		}

		return join (',', $strings);
	}

	protected function afterRead ($result) {
		return $result;
	}

	/**
	 * Fetch the associations of that model for a given record
	 *
	 * @param Array
	 * @param Boolean 			$merge: Default: true. Whether to merge the associations into tje
	 * 							results array or create a subarray for each association
	 * @return Array
	 */ 			
	protected function fetchAssociations ($result, $merge = true) {

		foreach ((array)$this->belongsTo as $assoc) {

			$className = $assoc['className'];

			$this->PsrAutoloader->loadClass ($className);
			$instance = new $className ();

			$assocData = $instance
				->filter([
					$assoc['foreignKey'] => $result[$assoc['foreignKeyField']]
				])
				->findOne ([
					'fetchAssociations' => false
				])
			;
			if ($merge) {
				$result = array_merge ($result, $assocData);
			}
			else {
				$result[$assoc['name']] = $assocData;
			}
		}

		foreach ((array)$this->hasOne as $assoc) {


		}

		foreach ((array)$this->hasMany as $assoc) {

			$className = $assoc['className'];

			$this->PsrAutoloader->loadClass ($className);
			$instance = new $className ();

			$findOptions = [
				'fetchAssociations' => false
			];
			if (!empty($assoc['order'])) {
				$findOptions['order'] = $assoc['order'];
			}

			$assocData = $instance
				->filter([
					$assoc['foreignKeyField'] => $result[$assoc['foreignKey']]
				])
				->findAll ([
					'fetchAssociations' => false,
				])
			;

			$result[$assoc['name']] = $assocData;
		}

		return $result;
	}



	/**
	 * Getter for validationErrors
	 *
	 * @return Array 
	 */
	public function getValidationErrors () {
		return $this->validationErrors;
	}



	/**
	 * Setter for validationRules
	 *
	 * @param Array
	 * @return void
	 */
	public function setValidationRules ($validationRules) {
		$this->validationRules = $validationRules;
	}



	/**
	 * Getter for validationRules
	 *
	 * @return Array 
	 */
	public function getValidationRules () {
		return $this->validationRules;
	}


	/**
	 * Validate
	 *
	 * @param Array The data to validate
	 * @return boolean
	 */
	public function validate ($data) {

		$this->validationErrors = [];

		foreach ((array)$this->validationRules as $fieldName => $rules) {

			if (!isset ($data[$fieldName])) {
				$this->validationErrors[$fieldName] = [ "Missing field: $fieldName" ];
				continue;
			}

			$value = $data[$fieldName];

			foreach ((array)$rules as $ruleName => $rule) {
				if (method_exists (get_class ($this), $rule)) {
					$result = call_user_func ([__NAMESPACE__ . '\\' . get_class(), $rule], $data[$field]);
				}
				else {
					$result = preg_match ($rule, $data[$field]);
				}

				if (!$result) {
					$this->validationErrors[$field][] = $ruleName;
				}
			}	
		}

		return (empty ($this->validationErrors));
	}



	/**
	 * Validate one form field
	 *
	 * @access public
	 * @param string 	The name of the field to validate
	 * @param mixed 	The value to validat against
	 * @return boolean 	Whether the data validates or not
	 */
	public function validateFormField ($fieldName, $value) {

		$success = true;

		if (!isset ($this->validationRules[$fieldName])) {
			return $success;
		}





		foreach ((array)$this->validationRules[$fieldName] as $ruleName => $rule) {

			if (method_exists ($this, $rule)) {
				$result = call_user_func ([$this, $rule], $value);
			}
			else {
				$result = preg_match ($rule, (string)$value) === 1;
			}


			if (!$result) {
				$success = false;
				if (!is_string ($ruleName)) {
					$ruleName = 'default';
				}
				if (!is_array ($this->validationErrors[$fieldName])) {
					$this->validationErrors[$fieldName] = [ $ruleName ];
				}
				else {
					$this->validationErrors[$fieldName][] = $ruleName;
				}
			}
		}
		return $success;
	}



	/**
	 * Returns the markup for a form field, ready with 
	 * validated data, errror messages etc.
	 *
	 * This function requires that for each cmt field type there is a 
	 * template in 'templates/forms/` (path is configurable)
	 * e.g. `templates/forms/string.tpl`
	 *
	 * @access public
	 * @param string 	$fieldName
	 * @param array 	$options 	Options
	 */

	public function getFormField ($fieldName, $params = []) {

		$fieldData = $this->FieldHandler->getField ([
			'tableName' => $this->tableName,
			'fieldName' => $fieldName
		]);

		// Determine the template fiel to  use
		// if it does not exist, dont waste computing time and skip
		$templateFile = $this->formTemplatesPath . $fieldData['cmt_fieldtype'] . '.tpl';
		if (!file_exists ($templateFile)) {
			return "...";
		}

		$defaultParams = [
			'label' => $fieldData['cmt_fieldalias'],
			'value' => $_POST[$fieldName],
			'required' => false,
			'validate' => false
		];
		$params = array_merge ($defaultParams, $params);

		if (!empty($this->validationRules[$fieldName])) {
			$params['required'] = true;
		}

		$this->Parser->setMultipleParserVars ($fieldData);
		$this->Parser->setParserVar ('fieldName', $fieldName);
		$this->Parser->setParserVar ('fieldValue', $params['value']);
		$this->Parser->setParserVar ('fieldLabel', $params['label']);
		$this->Parser->setParserVar ('required', $params['required']);

		if ($fieldData['cmt_fieldtype'] == 'select') {
			// var_dump($fieldData); die();
			$_options = [];
			if (!empty($fieldData['cmt_option_select_noselection'])) {
				$_options[] = [
					'optionValue' => '',
					'optionName' => $fieldData['cmt_option_select_noselection']
				];
			}
			$options = explode ("\n", $fieldData['cmt_option_select_aliases']);
			$values = explode ("\n", $fieldData['cmt_option_select_values']);
			foreach ($options as $n => $option) {
				$_options[] = [
					'optionValue' => trim($values[$n]),
					'optionName' => trim($option)
				];
			}
			$this->Parser->setParserVar ('options', $_options);

		}

		$this->Parser->deleteParserVar ('validationErrors');
		if ($params['validate']) {
			$success = $this->validateFormField ($fieldName, $params['value']);
			if (!$success) {

				// Normalize for use as parser var
				// TODO: Maybe this should happen in validation method
				$rules = [];
				foreach ($this->validationErrors[$fieldName] as $ruleName) {
					$rules[] = [ 'ruleName' => $ruleName ];
				}
				$this->Parser->setParserVar ('validationErrors', $rules) ; //$this->validationErrors[$fieldName]);
			}
		}

		$content = $this->Parser->parseTemplate ($templateFile);
		return $content;
	}



	/**
	 * Get markup for all form fields to be used in frontend forms
	 *
	 * This function requires that for each cmt field type there is a 
	 * template in 'templates/forms/` (path is configurable)
	 * e.g. `templates/forms/string.tpl`
	 *
	 * @param array $fieldNames 		Array of fieldnames to fetch, optional.
	 * 									Leave empty for all fields
	 * @param array $options 			Array of options as passed to `getFormField`, @see there
	 * @return Array 					An array with HTML markup for each form field
	 * 									in the form
	 * 									```
	 * 									[
	 * 										fieldName => "<MARKUP />"
	 * 									]
	 * 									```
	 */
	public function getFormFields ($fieldNames = [], $options = []) {

		if (empty ($fieldNames)) {
			// If $fields is empty, get all fields
			$fields = $this->FieldHandler->getAllFields ([
				'tableName' => $this->tableName,
				'getAll' => true
			]);
		}
		else {
			// else get only the specified fields
			$fields = [];
			foreach ($fieldNames as $fieldName) {
				$fields[] = $this->FieldHandler->getField ([
					'tableName' => $this->tableName,
					'fieldName' => $fieldName
				]);
			}
		}

		$_fields = [];
		foreach ($fields as $field) {
			$_fields[$field['cmt_fieldname']] = $this->getFormField ($field['cmt_fieldname'], $options);
		}

		return $_fields;
	}



	/**
	 * Save data from $_POST
	 * Optionally validate it (better done manually before save though)
	 * 
	 * @access public
	 * @param Array 		$data The data to save (defaults to $_POST)
	 * @param Array 		$options
	 * 						'fields' => array
	 * 						'validate' => boolean
	 * 						'callback' => executable
	 * @return boolean
	 */
	public function save ($data = null, $options = []) {
		$defaultOptions = [
			'fields' => [],
			'validate' => true,
			'callback' => false
		];
		$options = array_merge ($defaultOptions, $options);


		// Default data to postvars
		if (empty ($data)) {
			$data = $_POST;
		}



		// Determine fields to save, either from options or
		// save all fields that are set in $data
		if (!empty ($options['fields'])) {
			$_fields = $options['fields'];
		}
		else {
			$_fields = array_keys ($data);
		}


		// Be sure to only use fields that are actually
		// available in the table
		$availableFields = $this->FieldHandler->getFieldNames ($this->tableName, true);


		$fields = [];
		foreach ($_fields as $fieldName) {
			if (isset ($availableFields[$fieldName]) && isset ($data[$fieldName])) {
				$fields[$fieldName] = $data[$fieldName];
			}
		}
		if (isset($data['id'])) {
			  $fields['id'] = $data['id'];
		}

		$success = true;
		if ($options['validate']) {
			foreach ($fields as $fieldName => $value) {
				if (!$this->validateFormField ($fieldName, $value)) {
					$success = false;
					break;
				}
			}
		}
		if ($success) {
			$setQuery = $this->db->makeSetQuery($fields);


			// if (isset ($fields['id'])) {
			// 	$query = sprintf ('UPDATE %s SET %s WHERE id=%u', $this->tableName, $setQuery, (int)id);
			// }
			// else {
			// 	$query = sprintf ('INSERT INTO %s SET %s', $this->tableName, $setQuery);
			// }
			$query = sprintf ('INSERT INTO %s SET %s ON DUPLICATE KEY UPDATE %s',
				$this->tableName,
				$setQuery,
				$setQuery
			);

			$ret = $this->db->query ($query);
			$success = ($ret === 0);
		}

		if (is_callable ($options['callback'])) {
			$success = call_user_func ($options['callback'], $success, $data, $options);
		}
		
		return $success;
	}

	/**
	 * Setup Application ID and app settings
	 * Must be called after  setTableName
	 *
	 * @return void
	 */
	protected function setupApplication() {
		$this->applicationId = $this->getApplicationId();
		$this->applicationSettings = $this->ApplicationHandler->getApplicationSettings($this->applicationId);
	}
	

	/**
	 * Get the Application Id of the model's table
	 *
	 * @return integer
	 */
	protected function getApplicationId() {
		$query = sprintf("SELECT id FROM cmt_tables WHERE cmt_tablename='%s'", $this->tableName);
		if ($this->db->query($query) != 0) {
			return null;
		}
		$result = $this->db->get();
		return (int)$result['id'];
	}

	/**
	 * Getter for overviewPageId
	 *
	 * @return string
	 */
	public function getOverviewPageId() {
	    return $this->overviewPageId;
	}

	/**
	 * Getter for tableId
	 *
	 * @return int
	 */
	public function getTableId() {
		// Get table id (for table data layout)
		if (empty($this->tableId)) {
			$this->db->query(sprintf("SELECT id FROM cmt_tables WHERE cmt_tablename='%s'", $this->tableName));
			$r = $this->db->get();
			$this->tableId = (int)$r['id'];
		}
	    return $this->tableId;
	}

	/**
	 * Get table media 
	 *
	 * @param string 		media type
	 * @param int 			The record's id
	 * @return array
	 */
	public function getMedia($mediaType, $id) {
		return $this->TableMedia->getMedia([
			'entryID' => $id,
			'tableID' => $this->getTableId(),
			'mediaType' => $mediaType
		]);
	}
}
?>
