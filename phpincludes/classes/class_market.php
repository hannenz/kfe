<?php
namespace KFE;

use KFE\Model;

class Market extends Model {

	public function init() {
		$this->tableName = 'kfe_markets';
	}

	public function getMarketsWithOpenNumberAssignment() {
		$markets = $this->filter([
		])->findAll();

		return $markets;
	}
}
?>
