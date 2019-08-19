<?php
namespace KFE;

use KFE\Model;
use Contentomat\CmtPage;
use \Exception;

class Cart extends Model {

	public function init() {
		$this->tableName = 'kfe_carts';
	}

	/**
	 * Add a cart
	 *
	 * @return void
	 */
	public function add($data) {
		$setQuery = $this->db->makeSetQuery($data);

		// TODO:
		// Check if a cart with this timestamp is in already
		// Better yet: We create a hash value from timestamp, checkoutId and
		// marketId or uniqueid ...

		$query = sprintf("INSERT INTO %s SET %s", $this->tableName, $setQuery);
		if ($this->db->query($query) !== 0) {
			throw new Exception("Query failed: " . $Query);
		}
		return $this->db->lastInsertedId();
	}
	

	/**
	 * Callback to prepare data for output
	 * after fetching from the database
	 *
	 * @return void
	 */
	public function afterRead($result) {
		return $result;
	}
}
?>