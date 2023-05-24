<?php

namespace Awenn2015\RestOnActions\Http;

use Awenn2015\ArrayExt\ArrayExt;

/**
 * @version 1.0.1
 *
 * v1.0.1
 * - Добавил метод json
 */
class Request {
  public array $query;
  private array $body;
  private array $files;
  private array $server;

  public function __construct(array $array) {
    $this->query = $array['GET'];
    $this->body = $array['POST'];
    $this->files = $array['FILES'];
    $this->server = $array['SERVER'];
  }

  public function isEmpty(string $value): bool {
    $data = $this->getRequestBody();
    return empty($data[$value]);
  }

  /**
   * TODO: Добавить проверку заголовков
   * @return string|null
   */
  public function json(): ?string {
    $json = file_get_contents('php://input');
    return $json !== false ? $json : null;
  }

  public function query($value) {
    return $this->query[$value] ?? null;
  }

  public function body($value) {
    return $this->body[$value] ?? null;
  }

  public function server($value) {
    return $this->server[$value] ?? null;
  }

  public function files($value) {
    return $this->files[$value] ?? null;
  }

  /*  */

  public function validateBody(array $required): bool {
    $cb = fn($key) => (array_key_exists($key, $this->body));
    return ArrayExt::isEvery($cb, $required);
  }

  public function validateQuery(array $required): bool {
    $cb = function ($key) {
      return array_key_exists($key, $this->query) && !empty($this->query[$key]);
    };

    return ArrayExt::isEvery($cb, $required);
  }

  public function getRequestBody(): array {
    $method = $this->server("REQUEST_METHOD");

    if ($method === 'GET') {
      return $this->query;
    }

    if ($method === 'POST') {
      return $this->body;
    }

    $data = [];
    $exploded = explode('&', file_get_contents('php://input'));

    foreach ($exploded as $pair) {
      $item = explode('=', $pair);
      if (count($item) === 2) {
        $data[urldecode($item[0])] = urldecode($item[1]);
      }
    }

    return $data;
  }
}
