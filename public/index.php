<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);

	//Set the content type for all endpoints to application/json.
	header("Content-Type: application/json");

	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	require __DIR__ . "/../vendor/autoload.php";
	require_once "config/config.php";

	$app = AppFactory::create();

	/**
     * @OA\Info(title="M295 Webshop API", version="1.0")
 	 */


	/**
	 * Returns an error to the client with the given message and status code.
	 * This will immediately return the response and end all scripts.
	 * @param $message The error message string.
	 * @param $code The response code to set for the response.
	 */
	function error($message, $code) {
		//Write the error as a JSON object.
		$error = array("Error message" => $message);
		echo json_encode($error);

		//Set the response code.
		http_response_code($code);

		//End all scripts.
		die();
	}

	$app->run();
?>