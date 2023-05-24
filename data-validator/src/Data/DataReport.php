<?php

namespace Awenn2015\DataValidator\Data;

class DataReport {
  public bool $status;
  public string $message;
  public ?string $path;
  
  public function __construct(
    bool $status,
    string $message,
    ?string $path = null
  ) {
    $this->status = $status;
    $this->message = $message;
    $this->path = $path;
  }
}