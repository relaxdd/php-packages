<?php

namespace ZereCargo\RestApi\Data;

class MiddlewareInfo {
  public string $class;
  public array $args;

  public function __construct(string $class, array $args) {
    $this->class = $class;
    $this->args = $args;
  }
}