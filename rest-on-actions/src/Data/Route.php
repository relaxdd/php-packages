<?php

namespace Awenn2015\RestOnActions\Data;

use Awenn2015\RestOnActions\Http\Request;
use Awenn2015\RestOnActions\Http\Response;
use Awenn2015\RestOnActions\Interfaces\Middleware;

class Route {
  public string $method;
  public string $action;
  /** @var Middleware[] $middlewares */
  public array $middlewares;
  /** @var callable */
  public $callable;
  public ?string $description;
  
  public function __construct(
    string $method,
    string $action,
    callable $callback,
    array $middlewares = [],
    ?string $description = null
  ) {
    $this->method = $method;
    $this->action = $action;
    $this->middlewares = $middlewares;
    $this->callable = $callback;
    $this->description = $description;
  }
  
  public function callback(Request $request, Response $response) {
    $callable = $this->callable;
    $callable($request, $response);
  }
}
