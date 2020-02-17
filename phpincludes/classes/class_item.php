<?php
/**
 * phpincludes/classes/class_item.php
 *
 * @author Johannes Braun <johannes.braun@hannenz.de
 * @package kfe
 * @version 2019-10-16
 */
namespace KFE;

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
		return $this->save($data);
	}



	protected function afterRead($item) {
		$item['valueFmt'] = sprintf('%.2f', $item['item_value'] / 100);
		return $item;
	}
	
}
?>
