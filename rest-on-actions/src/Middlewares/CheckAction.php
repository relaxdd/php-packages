<?php

namespace Awenn2015\RestOnActions\Middlewares;

use Awenn2015\RestOnActions\Http\Request;
use Awenn2015\RestOnActions\Http\Response;
use Awenn2015\RestOnActions\Http\Status;
use Awenn2015\RestOnActions\Interfaces\Middleware;

class CheckAction implements Middleware {
  public function run(Request $request, Response $response, callable $next) {
    if ($request->query("action") === null) {
      $response->send("Не указан query параметр `action`", Status::$BAD_REQUEST);
    }
    
    $next($request);
  }
}
