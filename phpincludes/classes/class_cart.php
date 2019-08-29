<?php
namespace KFE;

use Contentomat\Model;
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
	
	public function delete($id) {
		try {
			$query = sprintf("DELETE FROM %s WHERE id=%u", $this->tableName, (int)$id);
			if ($this->db->query($query) !== 0) {
				throw new Exception("Query failed: " . $Query);
			}
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Callback to prepare data for output
	 * after fetching from the database
	 *
	 * @return void
	 */
	public function afterRead($cart) {
		$cart['items'] = json_decode($cart['cart_items'], true);
		return $cart;
	}
}
?>
