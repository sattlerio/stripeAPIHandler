<?php
use Slim\Http\Request;
use Slim\Http\Response;

// Ping Route
$app->get('/ping', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");
    // Render index view

    return "pong";
});

// Test Authentication
$app->post('/validate_credentials', function (Request $request, Response $response) {
    $tid =  \Ramsey\Uuid\Uuid::uuid4()->toString();
    $this->logger->info("got new request to validate credentials with ID: " . $tid);

    $data = array(
        "status" => "ERROR",
        "message" => "",
        "request_id" => $tid
    );

    $cType = $request->getContentType();
    if ($cType !== "application/json") {

        $this->logger->info($tid . ": invalid content type abort transaction");
        $data["message"] = "invalid content type";

        return $response->withJson($data, 400);
    }

    try {
        $pData =  json_decode($request->getBody(), true);
        $api_key = $pData["api_key"];

        if(in_array("api_key", $pData, true) === 0) {

            $this->logger->info($tid . ": missing api key in post data");
            $data["message"] = "please submit a api key";

            return $response->withJson($data, 400);
        }

        $this->logger->info("-----------------");
        $this->logger->info($api_key);
        $this->logger->info("-----------------");


        \Stripe\Stripe::setApiKey($api_key);
        $strip_data = \Stripe\Account::retrieve();

        $this->logger->info($tid . ": successfully validated api key");

        \Stripe\Stripe::setApiKey(null);

        $data["status"] = "OK";
        $data["message"] = "api key is valid";
        $data["api_feedback"] = $strip_data;

        return $response->withJson($data, 200);

    } catch (\Stripe\Error\RateLimit $e) {
        $this->logger->info($tid . ": Rate Limit exceeded not possible to authenticate");
        $response->withStatus(500);
        $data["message"] = "Rate Limit exceeded not possible to validate credentials, try again later";
        return $response->withJson($data, 500);
    } catch (\Stripe\Error\InvalidRequest $e) {
        $this->logger->info($tid . ": invalid request");
        $this->logger->info($e);
        $data["message"] = "Unkown error..";
        return $response->withJson($data, 500);

    } catch (\Stripe\Error\Authentication $e) {
        $this->logger->info($tid . ": invalid credentials...");
        $this->logger->info($e);
        $data["message"] = "invalid credentials";
        return $response->withJson($data, 400);
    } catch (\Stripe\Error\Permission $e) {
        $this->logger->info($tid . ": invalid credentials");
        $this->logger->info($e);
        $data["message"] = "Please ise a secret API ke";
        return $response->withJson($data, 400);
    } catch (\Stripe\Error\ApiConnection $e) {
        $this->logger->info($tid . ": communication error with stripe api");
        $this->logger->info($e);
        $data["message"] = "unkown error";
        return $response->withJson($data, 500);
    } catch (\Stripe\Error\Base $e) {
        $this->logger->info($tid . ": stripe base error");
        $this->logger->info($e);
        $data["message"] = "unkown error";
        return $response->withJson($data, 500);
    } catch (Exception $e) {
        $this->logger->info($tid . ": communication with stripe broken because unkown error");
        $this->logger->info($e);
        $data["message"] = "unkown error";
        return $response->withJson($data, 500);
    }
});