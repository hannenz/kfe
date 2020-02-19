<?php
/**
 * phpincludes/classes/class_item.php
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-16
 */
namespace KFE;

use \Contentomat\Logger;
use \Contentomat\Model;


class Item extends Model {

	public function init() {
		$this->setTableName('kfe_items');
		setlocale(LC_NUMERIC, 'de_DE.UTF-8');
	}


	/**
	 * Wrapper around Model::save
	 *
	 * @param Arrat 	The item data
	 * @return int 		ID
	 * @throws Exception
	 */
	public function add($data) {
		// As kind of simple backup, we log every item that gets added.
		// So if anything goes wrong with the db we are still covered in some
		// way.
		Logger::log(sprintf('Code: %s, MarketId: %u, SellerNr: %u, Value: %u',
			!empty($data['code']) ? $data['item_code'] : '-------- manual --------',
			$data['item_market_id'],
			$data['item_seller_nr'],
			$data['item_value']
		));
		return $this->save($data);
	}



	protected function afterRead($item) {
		$item['valueFmt'] = sprintf('%.2f', $item['item_value'] / 100);
		return $item;
	}
	
}
?>
