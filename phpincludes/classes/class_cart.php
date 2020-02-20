<?php
namespace KFE;

use Contentomat\Model;
use \KFE\Item;
use \Exception;

class Cart extends Model {

	public function init() {
		$this->tableName = 'kfe_carts';

		$this->Item = new Item();
	}



	/**
	 * Add a cart
	 *
	 * @return int 			The ID of the cart
	 * @throws Exception
	 */
	public function add($data) {
		$setQuery = $this->db->makeSetQuery($data);

		// TODO:
		// Check if a cart with this timestamp is in already
		// Better yet: We create a hash value from timestamp, checkoutId and
		// marketId or uniqueid ...

		$query = sprintf("INSERT INTO %s SET %s", $this->tableName, $setQuery);
		if ($this->db->query($query) !== 0) {
			throw new Exception("Query failed: " . $query);
		}
		return $this->db->lastInsertedId();
	}



	/**
	 * When a cart gets edited, we need to:
	 * - delete all associated items
	 * - save the new data, including the (new) items
	 *
	 * @param int 		The cart's id
	 * @param Array 	$data
	 * @return void
	 * @throws Exception
	 */
	public function update($id, $data) {

		$oldCart = $this->findById($id);
		foreach ($oldCart['items'] as $item) {
			$this->Item->delete($item['id']);
		}

		$setQuery = $this->db->makeSetQuery($data);
		$query = sprintf("UPDATE %s SET %s WHERE id = %u", $this->tableName, $setQuery, $id);
		$this->query($query);
	}
	

	/**
	 * Delete a cart
	 *
	 * @param int 		The cart's id
	 * @return boolean 	Success
	 */
	public function delete($id) {
		try {
			$query = sprintf("DELETE FROM %s WHERE id=%u", $this->tableName, (int)$id);
			if ($this->db->query($query) !== 0) {
				throw new Exception("Query failed: " . $query);
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
		// $cart['items'] = json_decode($cart['cart_items'], true);
		$cart['items'] = $this->Item->filter([
			'item_cart_id' => $cart['id']
		])->findAll();
		return $cart;
	}
}
?>
