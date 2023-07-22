<?php

$app = new RestOnActions();

$app->use([]);

$routesInfo = [
  new RouterInfo("auth", [
    new RouteInfo(Method::GET, "test", "test"),
    new RouteInfo(Method::GET, "logout", 'logout'),
    new RouteInfo(Method::POST, "register", 'register'),
  ]),
  new RouterInfo("user", []),
  new RouterInfo("admin", []),
];

AppBuilder::init($app, $routesInfo);

$app->run();