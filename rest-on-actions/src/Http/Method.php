<?php

namespace Awenn2015\RestOnActions\Http;

class Method {
  public string $method;
  
  public function __construct(Request $request) {
    $this->method = $request->server("REQUEST_METHOD");
  }
  
  public function toString(): string {
    return $this->method;
  }
  
  public function isEquals(string $method): bool {
    return $this->method === $method;
  }
  
  public static string $GET = "GET";
  public static string $POST = "POST";
  public static string $DELETE = "DELETE";
  public static string $PUT = "PUT";
  public static string $PATCH = "PATCH";
}
