<?php

declare(strict_types=1);

namespace Awenn2015\RestOnActions;

use Awenn2015\RestOnActions\Data\Route;
use Awenn2015\RestOnActions\Enums\Methods;
use Awenn2015\RestOnActions\Http\Method;
use Awenn2015\RestOnActions\Http\Request;
use Awenn2015\RestOnActions\Http\Response;
use Awenn2015\RestOnActions\Interfaces\Middleware;
use JetBrains\PhpStorm\NoReturn;

/* ======== AUTOLOAD ======== */

// TODO: Раскомментировать если в проекте не используется array-ext
// const __AUTOLOAD__ = __DIR__ . "/../vendor/autoload.php";

// if (file_exists(__AUTOLOAD__)) require __AUTOLOAD__;
// else throw new \Error("Не собран файл авто загрузчика для пакета!");

/*
 * История обновлений
 *
 * 1.5.0
 * - Добавлена возможность создания вложенных роутов (пока что с ограничением на 1 уровень)
 *
 * 1.5.1
 * - Добавил поддержку корневых middlewares для вложенных роутов
 * - Поменял константы в классе Method на статические свойства
 *
 * 1.5.2
 * - Добавил включение vendor/autoload.php и поменял minimum-stability с dev на stable
 *
 * 1.5.3
 * - Добавил общий метод receive, совместимая php версия повышена до 8.1
 */

/**
 * @version v1.5.3
 * @author awenn2015@gmail.com
 */
class RestOnActions {
  protected Request $request;
  protected readonly Response $response;
  protected readonly string $method;
  protected ?string $action;
  /** @var Middleware[] $middlewares */
  protected array $middlewares = [];
  /** @var Route[] $listOfActions */
  protected array $listOfActions = [];
  protected ?string $route = null;
  /** @var RestOnActions[] $listOfInstances */
  protected array $listOfInstances = [];

  public function __construct() {
    $this->request = new Request([
      'GET' => $_GET, 'POST' => $_POST,
      'FILES' => $_FILES, 'SERVER' => $_SERVER
    ]);

    $this->response = new Response();
    $this->method = $this->request->server("REQUEST_METHOD");;
    $this->action = $this->request->query("action");
  }

  public function route(string $path): RestOnActions {
    // TODO: Доработать к версии v2.0
    if ($this->route !== null)
      throw new \Error("Тю, чего это мы делаем?");

    $instance = new RestOnActions();
    $instance->withRoute($path);

    $this->listOfInstances[$path] = $instance;

    return $instance;
  }

  protected function withRoute(string $route): void {
    $this->route = $route;
  }

  /**
   * @param Middleware $middleware
   * @return void
   */
  public function use(Middleware $middleware): void {
    $this->middlewares[] = $middleware;
  }

  /**
   * @param string $action
   * @param callable $callback
   * @param Middleware[] $middlewares
   * @param string|null $description
   * @return void
   */
  public function get(string $action, callable $callback, array $middlewares = [], ?string $description = null): void {
    $this->addAction(Method::$GET, $action, $callback, $middlewares, $description);
  }

  /**
   * @param string $action
   * @param callable $callback
   * @param Middleware[] $middlewares
   * @param string|null $description
   * @return void
   */
  public function post(string $action, callable $callback, array $middlewares = [], ?string $description = null): void {
    $this->addAction(Method::$POST, $action, $callback, $middlewares, $description);
  }

  /**
   * @param Methods $method
   * @param callable|\Closure $callback
   * @param null|Middleware[] $middlewares
   */
  #[NoReturn] public function accept(Methods $method, callable|\Closure $callback, ?array $middlewares = null): void {
    exit("Not Implemented!");
  }

  /**
   * @param string $method
   * @param string $action
   * @param callable $callback
   * @param Middleware[] $middlewares
   * @param string|null $description
   * @return void
   */
  protected function addAction(
    string   $method,
    string   $action,
    callable $callback,
    array    $middlewares,
    ?string  $description = null
  ): void {
    $this->listOfActions[] = new Route($method, $action, $callback, $middlewares, $description);
  }

  /* =============== */

  /**
   * @param Middleware[] $middlewares
   * @param Request $request
   * @param Response $response
   * @param int $index
   * @return Request
   */
  protected function realizeNeedForCallNext(array $middlewares, Request $request, Response $response, int $index = 0): Request {
    $lRequest = $request;

    $next = function (Request $request) use (&$response, &$middlewares, &$index, &$lRequest) {
      $index++;

      if ($index === count($middlewares))
        $lRequest = $request;
      else
        $this->realizeNeedForCallNext($middlewares, $request, $response, $index);
    };

    $middlewares[$index]->run($request, $response, $next);

    return $lRequest;
  }

  public function run() {
    /** @var ?string $route Роут запроса */
    $route = $this->request->query("route");

    // Если внутри корневого instance
    if ($this->route === null) {
      if (empty($route))
        $this->start();
      else if (in_array($route, array_keys($this->listOfInstances)))
        $this->listOfInstances[$route]->start($this->middlewares);
      else
        $this->sendUnusedRoutes($route);
    } // Если внутри вложенного route instance
    else {
      if ($route === $this->route)
        $this->start();
      else return;
    }
  }

  /**
   * @param Middleware[]|null $rootMiddlewares
   * @return void
   */
  protected function start(?array $rootMiddlewares = null) {
    $allMiddlewares = array_merge(
      $rootMiddlewares ?: [],
      $this->middlewares
    );

    $this->useMiddlewares($allMiddlewares);

    foreach ($this->listOfActions as $route) {
      if ($this->method !== $route->method) continue;
      if ($this->action !== $route->action) continue;

      $this->useMiddlewares($route->middlewares);
      $route->callback($this->request, $this->response);

      $this->response->end();
    }

    $listOfActions = $this->collectUnusedActions();

    $res = [
      "msg" => "Ни одно из существующих действий не было выполнено, список доступных действий:",
      "data" => [
        "actions" => $listOfActions,
        "warning" => "unused-route-actions",
        "route" => $this->route ? "/" . $this->route : "/"
      ]
    ];

    $this->response->header("Content-Type", "application/json; charset=UTF-8");
    $this->response->send($res["msg"], 400, $res["data"]);
  }

  /**
   * @param Middleware[] $middlewares
   * @return void
   */
  protected function useMiddlewares(array $middlewares): void {
    if (!count($middlewares)) return;
    $this->request = $this->realizeNeedForCallNext($middlewares, $this->request, $this->response);
  }

  /**
   * @return array
   */
  protected function collectUnusedActions(): array {
    $collect = function (Route $route) {
      $info = [
        "method" => $route->method,
        "action" => $route->action,
      ];

      if (!empty($route->description)) {
        $info["description"] = $route->description;
      }

      return $info;
    };

    return array_map($collect, $this->listOfActions);
  }

  /* utils */

  protected function sendUnusedRoutes(string $route) {
    $res = [
      "msg" => "Данный маршрут не зарегистрирован в приложении, доступные маршруты:",
      "data" => function () use ($route) {
        $hasRoot = !empty($this->listOfActions);
        $routes = [];

        if ($hasRoot) $routes[] = "/";

        foreach ($this->listOfInstances as $instance)
          $routes[] = "/" . $instance->route;

        return [
          "used" => $route,
          "routes" => $routes,
          "warning" => "non-existing-route",
        ];
      }
    ];

    $this->response->header("Content-Type", "application/json; charset=UTF-8");
    $this->response->send($res["msg"], 400, $res["data"]());
  }
}
