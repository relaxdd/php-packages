<?php

namespace ZereCargo\RestApi\Data;

class RouteInfo {
  public string $method;
  public string $action;
  /** @var \Closure|callable|string $controller */
  public \Closure $controller;
  /** @var MiddlewareInfo[] */
  public array $middlewares;
  public ?string $description;

  /**
   * @param string $method
   * @param string $action
   * @param callable|string $controller
   * @param MiddlewareInfo[] $middlewares
   * @param string|null $description
   */
  public function __construct(
    string  $method,
    string  $action,
            $controller,
    array   $middlewares = [],
    ?string $description = null
  ) {
    if (!in_array($method, ["GET", "POST", "PUT", "DELETE", "PATCH"]))
      throw new \TypeError("Не валидный метод запроса в \$method");

    $this->method = $method;
    $this->action = $action;
    $this->controller = $controller;
    $this->middlewares = $middlewares;
    $this->description = $description;
  }
}