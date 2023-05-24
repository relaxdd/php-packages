<?php

namespace Awenn2015\RestOnActions\Http;

use JetBrains\PhpStorm\NoReturn;

class Response {
  /**
   * Устанавливает статус ответа
   *
   * @param int $code
   * @return $this
   */
  public function status(int $code = 200): static {
    http_response_code($code);
    return $this;
  }

  /**
   * @param \Throwable $err
   * @param int|null $code
   * @param bool $trace
   * @return void
   */
  public function defaultError(\Throwable $err, ?int $code = null, bool $trace = false): void {
    if (!is_null($code) && $code < 400)
      throw new \Error("Статус ошибки не может быть меньше 400!");

    if (is_null($code)) {
      if ($err instanceof \Exception) $code = 500;
      else if ($err instanceof \Error) $code = 400;
      else $code = 500;
    }

    $resp = [
      "error" => $err->getMessage(),
      "status" => false,
      "code" => $code
    ];

    if ($trace) $resp["trace"] = $err->getTraceAsString();
    $this->status($code)->json($resp);
  }

  /**
   * Устанавливает http заголовок
   *
   * @param string $name
   * @param string $value
   * @return $this
   */
  public function header(string $name, string $value): Response {
    header("$name: $value");
    return $this;
  }

  /* Methods completing the request */

  /**
   * @param array|string $data
   * @return void
   */
  #[NoReturn] public function json(array|string $data): void {
    $this->header("Content-Type", "application/json; charset=UTF-8");
    $data = is_array($data) ? $data : json_decode($data, true);

    die(json_encode($data, JSON_UNESCAPED_UNICODE));
  }

  #[NoReturn] public function text(string $text): void {
    $this->header("Content-Type", "text/html; charset=utf-8");
    die($text);
  }

  /**
   * Отправить ответ клиенту с указанием статуса и дополнительных данных
   *
   * @param string|null $message
   * @param int $code
   * @param array $concat
   * @return void
   */
  #[NoReturn] public function send(
    ?string $message = null,
    int     $code = 200,
    array   $concat = []
  ): void {
    $res = [
      'status' => $code >= 200 && $code < 300,
      'code' => $code
    ];

    if (!empty($message))
      $res['message'] = $message;

    $merge = array_merge($res, $concat);

    $this->header("Content-Type", "application/json; charset=UTF-8");

    http_response_code($code);
    die(json_encode($merge, JSON_UNESCAPED_UNICODE));
  }

  /**
   * Возвращает пустой ответ на клиент
   * @return void
   */
  #[NoReturn] public function end(): void {
    die();
  }
}
