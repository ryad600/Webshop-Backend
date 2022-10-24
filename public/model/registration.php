<?php
	//Connect to database.
	require "model/database.php";

	/**
	 * Fetches all registrations from the database.
	 * @return An array containing all registrations or a string if an error occurred.
	 */
	function get_all_registrations() {
		global $database;

		$result = $database->query("SELECT * FROM registration");

		if (!$result) {
			return "An error occurred while fetching the registrations.";
		}
		else if ($result === true || $result->num_rows == 0) {
			return array();
		}
		
		$registrations = array();

		while ($registration = $result->fetch_assoc()) {
			$registrations[] = $registration;
		}

		return $registrations;
	}

	/**
	 * Creates a new registration entity with the given values.
	 * @param $name The name of the participant.
	 * @param $age The age of the participant.
	 * @param $diet The diet of the participant.
	 * @return true on success, false otherwise.
	 */
	function create_new_registration($name, $age, $diet) {
		global $database;

		$result = $database->query("INSERT INTO registration(name, age, diet) VALUES('$name', $age, '$diet')");

		if (!$result) {
			return false;
		}
		
		return true;
	}

	/**
	 * Fetches the registration for the given ID.
	 * @param $registration_id The ID of the registration.
	 * @return The registration entity as an associative array or null if no registration was found with this ID or a string if an error occurred.
	 */
	function get_registration($registration_id) {
		global $database;

		$result = $database->query("SELECT * FROM registration WHERE registration_id = $registration_id");

		if (!$result) {
			return "An error occurred while fetching the registration.";
		}
		else if ($result === true || $result->num_rows == 0) {
			return null;
		}
		else {
			$registration = $result->fetch_assoc();

			return $registration;
		}
	}

	/**
	 * Updates an existing registration entity with the given values.
	 * @param $registration_id The ID of the existing registration.
	 * @param $name The name of the participant.
	 * @param $age The age of the participant.
	 * @param $diet The diet of the participant.
	 * @return true on success, false otherwise.
	 */
	function update_registration($registration_id, $name, $age, $diet) {
		global $database;

		$result = $database->query("UPDATE registration SET name = '$name', age = $age, diet = '$diet' WHERE registration_id = $registration_id");

		if (!$result) {
			return false;
		}
		
		return true;
	}

	/**
	 * Deletes the registration for the given ID.
	 * @param $registration_id The ID of the registration.
	 * @return true on success or null if no registration was found with this ID or a string if an error occurred.
	 */
	function delete_registration($registration_id) {
		global $database;

		$result = $database->query("DELETE FROM registration WHERE registration_id = $registration_id");

		if (!$result) {
			return "An error occurred while deleting the registration.";
		}
		else if ($database->affected_rows == 0) {
			return null;
		}
		else {
			return true;
		}
	}
?>