<?php
require "../../model/database.php";

$app->post("/Product", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

		//Check if all values are provided.
		if (!isset($request_data["sku"])) {
			error("", 400);
		}
		if (!isset($request_data["active"]) || is_bool($request_data["active"])) {
			error("", 400);
		}
		if (!isset($request_data["id_category"]) || !is_numeric($request_data["id_category"])) {
			error("", 400);
		}
		if (!isset($request_data["name"])) {
			error("", 400);
		}
		if (!isset($request_data["image"])) {
			error("", 400);
		}
		if (!isset($request_data["description"])) {
			error("", 400);
		}
		if (!isset($request_data["price"]) || !is_numeric($request_data["price"])) {
			error("", 400);
		}
		if (!isset($request_data["stock"]) || !is_numeric($request_data["stock"])) {
			error("", 400);
		}


		//Clean up all unnecessary tags and add backslashes to safe your database.
		$sku 			= strip_tags(addslashes($request_data["name"]));
		$active 		= $request_data["active"];
		$id_category	= intval($request_data["id_category"]);
		$name 			= strip_tags(addslashes($request_data["name"]));
		$image 			= strip_tags(addslashes($request_data["image"]));
		$description 	= strip_tags(addslashes($request_data["image"]));
		$price 			= intval($request_data["price"]);
		$stock 			= intval($request_data["stock"]);

		//make sure nothing is empty and make sure that the id category exists.
		if (empty($sku)) {
			error("The 'sku' field must not be empty.", 400);
		}
		if ( !NULL || check_category_id($id_category) === false) {
			error("This category doesn't exist, enter valid category or 0 fro no category.", 400);
		}
		if (empty($name)) {
			error("The 'name' field must not be empty.", 400);
		}
		if (empty($image)) {
			error("The 'image' field must not be empty.", 400);
		}
		if (empty($description)) {
			error("The 'description' field must not be empty.", 400);
		}
		if (empty($price)) {
			error("The 'price' field must not be empty.", 400);
		}
		if (create_new_product($name, $age, $diet) === true) {
			http_response_code(201);
			echo "true";
		}
		else {
			error("An error occurred while saving the student data.", 500);
		}

		return $response;
	});
?>