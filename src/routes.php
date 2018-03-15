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