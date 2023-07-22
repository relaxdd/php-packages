<?php

namespace ZereCargo\RestApi\Data;

use ZereCargo\RestApi\Interfaces\Controller;

class RouterInfo {
  public string $router;
  /** @var RouteInfo[] $routes */
  public array $routes;
  /** @var MiddlewareInfo[] $middlewares */
  public array $middlewares;
  public $controller;

  /**
   * @param string $router
   * @param RouteInfo[] $routes
   * @param MiddlewareInfo[] $middlewares
   * @param Controller|string|null $controller
   */
  public function __construct(
    string $router,
    array  $routes,
    array  $middlewares = [],
           $controller = null
  ) {
    $this->router = $router;
    $this->controller = $controller;
    $this->routes = $routes;
    $this->middlewares = $middlewares;
  }
}