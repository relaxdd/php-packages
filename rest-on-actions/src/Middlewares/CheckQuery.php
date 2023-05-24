<?php

namespace Awenn2015\RestOnActions\Middlewares;

use Awenn2015\RestOnActions\Http\Request;
use Awenn2015\RestOnActions\Http\Response;
use Awenn2015\RestOnActions\Http\Status;
use Awenn2015\RestOnActions\Interfaces\Middleware;

class CheckQuery implements Middleware {
  public array $args = [];
  
  public function __construct(...$args) {
    $this->args = array_merge($this->args, $args);
  }
  
  public function run(Request $request, Response $response, callable $next): void {
    $is_valid = $request->validateQuery($this->args);
    
    if (!$is_valid)
      $response->send(self::buildMsg($this->args), Status::$BAD_REQUEST);
    
    $next($request);
  }
  
  /**
   * @param string[] $args
   * @return string
   */
  private static function buildMsg(array $args): string {
    return self::$msg . implode(", ", $args);
  }
  
  private static string $msg = "В строке запроса не указаны все обязательные параметры: ";
}