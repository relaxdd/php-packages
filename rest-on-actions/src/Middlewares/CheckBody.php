<?php

namespace Awenn2015\RestOnActions\Middlewares;

use Awenn2015\RestOnActions\Http\Request;
use Awenn2015\RestOnActions\Http\Response;
use Awenn2015\RestOnActions\Http\Status;
use Awenn2015\RestOnActions\Interfaces\Middleware;

class CheckBody implements Middleware {
  public array $args = [];
  
  public function __construct(...$args) {
    $this->args = array_merge($this->args, $args);
  }
  
  // TODO: Надо будет по гуглить про метод __invoke, вроде тогда можно будет вызывать класс как функцию
  public function run(Request $request, Response $response, callable $next) {
    $is_valid = $request->validateBody($this->args);
    
    if (!$is_valid)
      $response->send(self::buildMsg($this->args), Status::$BAD_REQUEST);
    
    $next($request);
  }
  
  /**
   * @param string[] $args
   * @return string
   */
  public static function buildMsg(array $args): string {
    return self::$msg . implode(", ", $args);
  }
  
  private static string $msg = "В теле запроса не указаны все обязательные параметры: ";
}
