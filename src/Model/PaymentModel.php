<?php

namespace Portalbox\Model;

use Portalbox\Entity\Payment;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * PaymentModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class PaymentModel extends AbstractModel {
	/**
	 * Save a new payment to the database
	 *
	 * @param Payment payment - the payment to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Payment|null - the payment or null if the payment could not be saved
	 */
	public function create(Payment $payment) : ?Payment {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO payments (user_id, amount, time) VALUES (:user_id, :amount, :time)';
		$query = $connection->prepare($sql);

		$query->bindValue(':user_id', $payment->user_id(), PDO::PARAM_INT);
		$query->bindValue(':amount', $payment->amount());
		$query->bindValue(':time', $payment->time());

		if($query->execute()) {
			return $payment->set_id($connection->lastInsertId('payments_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a payment by its unique ID
	 *
	 * @param int id - the unique id of the payment
	 * @throws DatabaseException - when the database can not be queried
	 * @return Payment|null - the payment or null if the payment could not be found
	 */
	public function read(int $id) : ?Payment {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, user_id, amount, time FROM payments WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return (new Payment())
					->set_id($data['id'])
					->set_user_id($data['user_id'])
					->set_amount($data['amount'])
					->set_time($data['time']);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified payment to the database
	 *
	 * @param Payment payment - the payment to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Payment|null - the payment or null if the payment could not be saved
	 */
	public function update(Payment $payment) : ?Payment {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE payments SET user_id = :user_id, amount = :amount, time = :time WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $payment->id(), PDO::PARAM_INT);
		$query->bindValue(':user_id', $payment->user_id(), PDO::PARAM_INT);
		$query->bindValue(':amount', $payment->amount());
		$query->bindValue(':time', $payment->time());

		if($query->execute()) {
			return $payment;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete a payment secified by its unique ID
	 *
	 * @param int id - the unique id of the payment
	 * @throws DatabaseException - when the database can not be queried
	 * @return Payment|null - the payment or null if the payment could not be found
	 */
	public function delete(int $id) : ?Payment {
		$payment = $this->read($id);

		if(NULL !== $payment) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM payments WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $payment;
	}
}