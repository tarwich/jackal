<?php

include("MySQLProxy.php");
Jackal::loadLibrary("Spyc");

class MySQLModel {
	protected static $proxy;

	/**
	 * @var boolean should fields that are not in the table definition be
	 * dropped when fixing the database?
	 */
	protected $removeAliens = false;
	
	private $_fixed = false;
	private $_structures = NULL;

	public function __construct() {
		self::$proxy = new MySQLProxy();
	}
	
	/**
	 * Run a query and return the result.  This is actually a violation of the
	 * model/proxy relationship, but it's used because we're going to listen
	 * for errors and fix the table structure if necessary
	 *
	 * @param String $sql
	 * @param ...rest arguments for substitution
	 *
	 */
	public function execute($sql, $replacements=NULL /*, ...rest */) {
		// Allow the replacements to be passed in as single array as well
		if(is_array($replacements)) {
			$arguments = $replacements;
		} else {
			// Get the arguments from the variant arguments
			$arguments = func_get_args();
			// Remove the SQL from the argument list
			array_shift($arguments);
		}

		// Run the query
		if(self::$proxy->getData($sql, $arguments)) {
			// Get the result, and return it
			return self::$proxy->fetch_all();
		} else {
			// Repair the DB
			$this->fixDB();
			return false;
		}
	}

	/**
	 * Returns the structure definition for this object.  Called by MySQLModel
	 * when there was a problem with the database
	 *
	 *  @return String definition of model
	 */
	protected function getDefinition() { }

	/**
	 * Returns an array of table structure arrays
	 *
	 * @return array structure of model
	 */
	public function getStructure() {
		// Allow caching
		if($this->_structures) return $this->_structures;
		
		// Initialize the structures array
		$this->_structures = array();
		
		// Get the structure declaration
		$structureString = $this->getDefinition();

		// Parse the structure
		$structure = Spyc::YAMLLoadString($structureString);

		// Parse each table
		foreach($structure as $tableName=>$tableStructure) {
			// Prepare fields array
			$fields = array();

			// Parse the fields
			foreach($tableStructure as $fieldName=>$fieldType) {
				if(is_array($fieldType)) {
					$caption = $fieldType["caption"];
					$fieldType = $fieldType["type"];
				} else {
					$caption = ucwords($fieldName);
				}
					
				if(substr($fieldName, 0, 1) == "!") {
					$fieldName = substr($fieldName, 1);
					$primary = $fieldName;
					unset($fields["!$fieldName"]);
					$fields[$fieldName] = $fieldType;
					$caption = $fieldName;
				} else {
					$fields[$fieldName] = $fieldType;
				}
			}
				
			$this->_structures[] = array(
				"name" 		=> $tableName, 
				"primary" 	=> $primary, 
				"fields" 	=> $fields
			);
		}
		
		// And give back the new structures array
		return $this->_structures;
	}

	/**
	 * Get the structure of the model and make sure it's properly implemented
	 *
	 * @return void|void
	 */
	protected function fixDB() {
		// Don't fix the table more than once in the same pass. If errors are
		// still occurring, then it's not my fault
		if($this->_fixed) return false;

		// Get the structure data
		$structure = $this->getStructure();

		// Rebuild each table
		foreach($structure as $table) {
			// Fix the table
			@$this->fixTable($table["name"], $table["primary"], $table["fields"]);
		}

		// Remember that we've fixed the database
		$this->_fixed = true;
	}

	/**
	 * Called by fixDB to fix one table at a time.  The purpose of this function
	 * is to keep the fixDB function clean
	 */
	private function fixTable($table, $primary, $fields) {
		echo "REBUILDING:$table";

		// Get the proxy
		$proxy = self::$proxy;

		// Get table information
		$success = $proxy->getData("DESCRIBE $table");

		if(!$success) {
			// Table didn't exist, so build from scratch
			$fieldSet = "";

			if($primary)
			$primaries = "primary key ($primary)";

			foreach($fields as $fieldName=>$fieldDefinition)
			$fieldSet .= "$fieldName $fieldDefinition, ";

			$proxy->getData("CREATE TABLE $table ($fieldSet $primaries);");
		} else {
			// Keep track of the fields we have yet to implement
			$fieldsLeft = $fields;

			// Table found, build it out
			// Retrieve the table definition
			$fields = $proxy->fetch_all();

			foreach($fields as $field) {
				$fieldName = $field["Field"];

				// If we're NOT supposed to have this field
				if(!@$fieldsLeft[$fieldName]) {
					// And if we're supposed to remove aliens
					if($this->removeAliens) {
						$proxy->getData("ALTER TABLE `$table` DROP COLUMN `$fieldName`;");
					}
				} else {
					// We're supposed to have this field
					$fieldDefinition = $fieldsLeft[$fieldName];
					$proxy->getData("ALTER TABLE `$table` MODIFY COLUMN `$fieldName` $fieldDefinition");
					// Remove this field from the list of fields we have to implement
					unset($fieldsLeft[$fieldName]);
				}
			}

			// Add the leftover fields
			foreach($fieldsLeft as $fieldName=>$fieldDefinition) {
				$proxy->getData("ALTER TABLE `$table` ADD COLUMN `$fieldName` $fieldDefinition");
			}
		}

	}
}
