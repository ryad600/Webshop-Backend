<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);

	//Morhaf ist meistens nice!

	//Set the content type for all endpoints to application/json.
	header("Content-Type: application/json");

	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	require __DIR__ . "/../vendor/autoload.php";
	require "model/registration.php";
	require_once "config/config.php";

	$app = AppFactory::create();

	/**
     * @OA\Info(title="Mr. Sollbergers nice API", version="1.0")
 	 */


	/**
	 * Returns an error to the client with the given message and status code.
	 * This will immediately return the response and end all scripts.
	 * @param $message The error message string.
	 * @param $code The response code to set for the response.
	 */
	function error($message, $code) {
		//Write the error as a JSON object.
		$error = array("message" => $message);
		echo json_encode($error);

		//Set the response code.
		http_response_code($code);

		//End all scripts.
		die();
	}

	/**
     * @OA\Post(
     *     path="/Authenticate",
     *     summary="Client can authenticate themself with username and password and get a token.",
     *     tags={"Authentication"},
     *     requestBody=@OA\RequestBody(
     *         request="/Authenticate",
     *         required=true,
     *         description="username and password are needed.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", example="root"),
     *                 @OA\Property(property="password", type="string", example="sec!ReT423*&")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Authentication succesful."),
     * 	   @OA\Response(response="401", description="invalid Credentials."))
     * )
	 */


	$app->post("/Authenticate", function (Request $request, Response $response, $args) {
		global $api_username;
		global $api_password;

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

		$username = $request_data["username"];
		$password = $request_data["password"];

		//If either the username or the password is incorrect, return a 401 error.
		if ($username != $api_username || $password != $api_password) {
			error("Invalid credentials.", 401);
		}

		//Generate the access token and store it in the cookies.
		$token = Token::create($username, $password, time() + 3600, "localhost");

		setcookie("token", $token);

		//Echo true for a successful response.
		echo "true";

		return $response;
	});

	$app->post("/Registration", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

		//Check if all values are provided.
		if (!isset($request_data["name"])) {
			error("Please provide a \"name\" field.", 400);
		}
		if (!isset($request_data["age"]) || !is_numeric($request_data["age"])) {
			error("Please provide an integer number for the \"age\" field.", 400);
		}
		if (!isset($request_data["diet"])) {
			error("Please provide a \"diet\" field with a value being one of \"omnivore\", \"halal\", \"vegetarian\" or \"vegan\".", 400);
		}

		//Sanitize the values where necessary.
		$name = strip_tags(addslashes($request_data["name"]));
		$age = intval($request_data["age"]);
		$diet = $request_data["diet"];

		//Make sure that the values are not empty and that the diet is one of the expected values.
		if (empty($name)) {
			error("The \"name\" field must not be empty.", 400);
		}
		if (empty($diet) || !in_array($diet, array("omnivore", "halal", "vegetarian", "vegan"))) {
			error("The \"diet\" field must be set to one of \"omnivore\", \"halal\", \"vegetarian\" or \"vegan\".", 400);
		}

		//Limit the length of the name.
		if (strlen($name) > 250) {
			error("The name is too long. Please enter less than or equal to 250 characters.", 400);
		}

		//Limit the range of the age.
		if ($age < 0 || $age > 200) {
			error("The age must be between 0 and 200 years.", 400);
		}

		//Make sure the age is an integer.
		if (is_float($age)) {
			error("The age must not have decimals.", 400);
		}

		if (create_new_registration($name, $age, $diet) === true) {
			http_response_code(201);
			echo "true";
		}
		else {
			error("An error occurred while saving the student data.", 500);
		}

		return $response;
	});

	$app->get("/Registration/{registration_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$registration_id = intval($args["registration_id"]);

		//Get the entity.
		$registration = get_registration($registration_id);

		if (!$registration) {
			//No entity found.
			error("No registration found for the ID " . $registration_id . ".", 404);
		}
		else if (is_string($registration)) {
			//Error while fetching.
			error($registration, 500);
		}
		else {
			//Success.
			echo json_encode($registration);
		}

		return $response;
	});

	$app->put("/Registration/{registration_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$registration_id = intval($args["registration_id"]);

		//Get the entity.
		$registration = get_registration($registration_id);

		if (!$registration) {
			//No entity found.
			error("No registration found for the ID " . $registration_id . ".", 404);
		}
		else if (is_string($registration)) {
			//Error while fetching.
			error($registration, 500);
		}

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

		//Put the updated information into the fetched entity.
		if (isset($request_data["name"])) {
			//Sanitize the name.
			$name = strip_tags(addslashes($request_data["name"]));

			//Make sure that the name is not empty.
			if (empty($name)) {
				error("The \"name\" field must not be empty.", 400);
			}

			//Limit the length of the name.
			if (strlen($name) > 250) {
				error("The name is too long. Please enter less than or equal to 250 characters.", 400);
			}

			$registration["name"] = $name;
		}
		if (isset($request_data["age"])) {
			//Make sure that the age is numeric.
			if (!is_numeric($request_data["age"])) {
				error("Please provide an integer number for the \"age\" field.", 400);
			}

			//Sanitize the age.
			$age = intval($request_data["age"]);

			//Limit the range of the age.
			if ($age < 0 || $age > 200) {
				error("The age must be between 0 and 200 years.", 400);
			}

			//Make sure the age is an integer.
			if (is_float($age)) {
				error("The age must not have decimals.", 400);
			}

			$registration["age"] = $age;
		}
		if (isset($request_data["diet"])) {
			//Sanitize the diet.
			$diet = $request_data["diet"];

			//Make sure that the diet is not empty and is one of the allowed values.
			if (empty($diet) || !in_array($diet, array("omnivore", "halal", "vegetarian", "vegan"))) {
				error("The \"diet\" field must be set to one of \"omnivore\", \"halal\", \"vegetarian\" or \"vegan\".", 400);
			}

			$registration["diet"] = $diet;
		}

		//Save the information.
		if (update_registration($registration_id, $registration["name"], $registration["age"], $registration["diet"])) {
			echo "true";
		}
		else {
			error("An error occurred while saving the registration data.", 500);
		}

		return $response;
	});

	$app->delete("/Registration/{registration_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$registration_id = intval($args["registration_id"]);

		//Delete the entity.
		$result = delete_registration($registration_id);

		if (!$result) {
			//No entity found.
			error("No registration found for the ID " . $registration_id . ".", 404);
		}
		else if (is_string($result)) {
			//Error while deleting.
			error($registration, 500);
		}
		else {
			//Success.
			echo json_encode($result);
		}

		return $response;
	});

	$app->get("/Registrations", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		//Get the entities.
		$registrations = get_all_registrations();

		if (is_string($registrations)) {
			//Error while fetching.
			error($registrations, 500);
		}
		else {
			//Success.
			echo json_encode($registrations);
		}

		return $response;
	});

	$app->run();
?>
