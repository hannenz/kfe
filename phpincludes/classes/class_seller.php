<?php
namespace KFE;

use KFE\Model;
use Contentomat\Mail;
use \Exception;

class SellerExistsForMarketException extends Exception { }
class ActivationFailedException extends Exception { }

class Seller extends Model {


	/**
	 * @var Contentomat\Mail
	 */
	public $Mail;


	public function init() {
		$this->Mail = new Mail();
		$this->tableName = 'kfe_sellers';
	}


	/**
	 * Registrate a seller for a certain market
	 *
	 * @param string 		E-Mail
	 * @param int 			Market ID
	 * @access public
	 * @throws SellerExistsForMarketException
	 * @throws Exception
	 */
	public function registrate($email, $marketId) {

		// Check if email is already registered for this market
		// Could also check for the hash?
		if ($this->checkSellerExistsForMarket($email, $marketId)) {
			throw new SellerExistsForMarketException("E-Mail already registered for market");
		}

		$hash = hash('sha256', sprintf('%s%04u', $email, $marketId));

		$query = sprintf("INSERT INTO %s SET %s",
			$this->tableName,
			$this->db->makeSetQuery([
				'seller_email' => $email,
				'seller_activation_hash' => $hash,
				'seller_is_activated' => 0,
				'seller_market_id' => $marketId,
				'seller_registration_date' => strftime('%F-%T')
			])
		);

		if ($this->db->query($query) !== 0) {
			throw new Exception("Query failed: " . $query);
		}

		return $hash;
	}


	public function checkSellerExistsForMarket($email, $marketId) {
		$sellers = $this->filter([
			'seller_email' => $email,
			'seller_market_id' => $marketId
		])->findAll();

		return (!empty($sellers));
	}

	/**
	 * Activate a seller by its hash
	 *
	 * @param string 		The activation hash
	 * @throws ActivationFailedException
	 * @throws Exception
	 *
	 * @return void
	 */
	public function activate($hash) {
		// Get the seller for hash
		$seller = $this->filter([
			'seller_activation_hash' => $hash,
			'seller_is_activated' => 0,
			// 'seller_registration_datetime >= ' . strftime('%F-%T', strtotime('-48 hours'))
		])->findOne();

		if (empty($seller)) {
			throw new ActivationFailedException();
		}

		$query = sprintf("UPDATE %s SET %s WHERE id=%u",
			$this->tableName,
			$this->db->makeSetQuery([
				'seller_is_activated' => 1,
				'seller_activation_datetime' => strftime('%F-%T')
			]),
			$seller['id']
		);
		if ($this->db->query($query) !== 0) {
			throw new Exception("Query failed: " .$query);
		}

		// Re-Read the seller's record and return it
		return $this->findById($seller['id']);
	}
}
?>
