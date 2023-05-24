<?php

namespace Awenn2015\ArrayExt;

/*
 * История обновлений
 *
 * 1.0.1
 * - Переименовал метод getOrNull в getItem и типизировал его для php 8.1
 */

/**
 * @version 1.0.1
 * @author awenn2015@gmail.com
 */
class ArrayExt {
  /**
   * @param array $array
   * @param callable $callback
   * @return bool
   */
  public static function isEvery(callable $callback, array $array): bool {
    $i = 0;

    foreach ($array as $k => $v) {
      $c = call_user_func_array($callback, [$v, $k, $i, $array]);
      if ($c === false) return false;

      $i++;
    }

    return true;
  }

  /**
   * @param callable $callback
   * @param array $array
   * @return mixed|null
   */
  public static function findOne(callable $callback, array $array): mixed {
    foreach ($array as $k => $v) {
      if ($callback($v, $k, $array)) {
        return $v;
      }
    }

    return null;
  }

  /**
   * @param callable $callback
   * @param array $array
   * @return int
   */
  public static function indexOf(callable $callback, array $array): int {
    foreach ($array as $i => $v) {
      if ($callback($v, $i) === true)
        return $i;
    }

    return -1;
  }

  /**
   * @param callable $callback
   * @param array $array
   * @return bool
   */
  public static function isSome(callable $callback, array $array): bool {
    $i = 0;

    foreach ($array as $k => $v) {
      $c = call_user_func_array($callback, [$v, $k, $i, $array]);
      if ($c === true) return true;

      $i++;
    }

    return false;
  }

  /**
   * @param int|string $key
   * @param array $array
   * @return mixed|null
   */
  public static function getItem(int|string $key, array $array): mixed {
    return $array[$key] ?? null;
  }
}
