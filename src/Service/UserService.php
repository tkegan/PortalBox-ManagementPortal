<?php

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\User;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;

/**
 * Manage Users
 */
class UserService {
	protected RoleModel $roleModel;
	protected UserModel $userModel;

	public function __construct(RoleModel $roleModel, UserModel $userModel) {
		$this->roleModel = $roleModel;
		$this->userModel = $userModel;
	}

	/**
	 * Import users from the open file handle
	 *
	 * We expect the first line to be a header and that the input is three
	 * columns: Name, Email Address, and Role Id in that order.
	 *
	 * @todo be smarter about column headers and column order. Maybe allow
	 *     optional columns.
	 *
	 * @param $fileHandle  an open file handle for reading csv data from
	 * @return User[]  The list of users which were added
	 */
	public function import($fileHandle): array {
		// we don't expect many roles in the system so let's just cache them
		$roles = [];
		foreach ($this->roleModel->search() as $role) {
			$roles[$role->name()] = $role;
		}

		// read and discard header line
		$header = fgetcsv($fileHandle);

		// read lines, validating each, to accumulate users
		$records = [];
		while ($user = fgetcsv($fileHandle)) {
			if (count($user) !== 3) {
				throw new InvalidArgumentException('Import files must contain 3 columns: "Name", "Email Address", and "Role Id"');
			}

			$role = trim($user[2]);
			if (!array_key_exists($role, $roles)) {
				throw new InvalidArgumentException('"Role" must be the name of an existing role');
			}

			$records[] = [
				'name' => strip_tags(trim($user[0])),
				'email' => strip_tags(trim($user[1])),
				'role' => $roles[$role]
			];
		}

		// persist users to database
		// @todo wrap in transaction
		$users = [];
		foreach ($records as $record) {
			$users[] = $this->userModel->create(
				(new User())
					->set_name($record['name'])
					->set_email($record['email'])
					->set_is_active(true)
					->set_role($record['role'])
			);
		}

		return $users;
	}
}
