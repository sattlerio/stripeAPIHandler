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
    $tid = \Ramsey\Uuid\Uuid::uuid4();
    $this->logger->info("got new request to validate credentials with ID: " . $tid);

    $data = array(
        "status" => "ERROR",
        "message" => "",
        "request_id" => $tid
    );
    $newResponse = $response->withHeader('Content-type', 'application/json');

    $cType = $request->getContentType();
    if ($cType !== "application/json") {

        $this->logger->info($tid . ": invalid content type abort transaction");
        $data["message"] = "invalid content type";

        $newResponse->withStatus(401);
        $newResponse->write(json_encode($data));
    }

    try {
        $pData =  json_decode($request->getBody(), true);
        $api_key = $pData["api_key"];

        if(in_array("api_key", $pData, true) === 0) {

            $this->logger->info($tid . ": missing api key in post data");
            $data["message"] = "please submit a api key";

            $newResponse->withStatus(401);
            $newResponse->write(json_encode($data));
            return $newResponse;
        }


        \Stripe\Stripe::setApiKey($api_key);
        $strip_data = \Stripe\Account::retrieve();

        $this->logger->info($tid . ": successfully validated api key");

        \Stripe\Stripe::setApiKey(null);

        $newResponse->withStatus(200);
        $data["status"] = "OK";
        $data["message"] = "api key is valid";
        $data["api_feedback"] = $strip_data;

        $newResponse->write(json_encode($data));
        return $newResponse;

    } catch (\Stripe\Error\RateLimit $e) {
        $this->logger->info($tid . ": Rate Limit exceeded not possible to authenticate");
        $newResponse->withStatus(500);
        $data["message"] = "Rate Limit exceeded not possible to validate credentials, try again later";
        $newResponse->write(json_encode($data));
        return $newResponse;

    } catch (\Stripe\Error\InvalidRequest $e) {
        $this->logger->info($tid . ": invalid request");
        $this->logger->info($e);
        $newResponse->withStatus(500);;
        $data["message"] = "Unkown error..";
        $newResponse->write(json_encode($data));
        return $newResponse;

    } catch (\Stripe\Error\Authentication $e) {
        $this->logger->info($tid . ": invalid credentials...");
        $this->logger->info($e);
        $newResponse->withStatus(400);;
        $data["message"] = "invalid credentials";
        $newResponse->write(json_encode($data));
        return $newResponse;
    } catch (\Stripe\Error\Permission $e) {
        $this->logger->info($tid . ": invalid credentials");
        $this->logger->info($e);
        $newResponse->withStatus(400);;
        $data["message"] = "Please ise a secret API ke";
        $newResponse->write(json_encode($data));
        return $newResponse;
    } catch (\Stripe\Error\ApiConnection $e) {
        $this->logger->info($tid . ": communication error with stripe api");
        $this->logger->info($e);
        $newResponse->withStatus(500);;
        $data["message"] = "unkown error";
        $newResponse->write(json_encode($data));
        return $newResponse;
    } catch (\Stripe\Error\Base $e) {
        $this->logger->info($tid . ": stripe base error");
        $this->logger->info($e);
        $newResponse->withStatus(500);;
        $data["message"] = "unkown error";
        $newResponse->write(json_encode($data));
        return $newResponse;
    } catch (Exception $e) {
        $this->logger->info($tid . ": communication with stripe broken because unkown error");
        $this->logger->info($e);
        $newResponse->withStatus(500);;
        $data["message"] = "unkown error";
        $newResponse->write(json_encode($data));
        return $newResponse;
    }
});