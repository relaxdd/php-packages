<?php

namespace Awenn2015\DataValidator;

use Awenn2015\DataValidator\Data\DataMsg;
use Awenn2015\DataValidator\Data\DataReport;
use Awenn2015\DataValidator\Data\DataStruct;
use Awenn2015\DataValidator\Data\ListStruct;

/* ======== AUTOLOAD ======== */

// TODO: Раскомментировать если в проекте не используется array-ext
// const __AUTOLOAD__ = __DIR__ . "/../vendor/autoload.php";

// if (file_exists(__AUTOLOAD__)) require __AUTOLOAD__;
// else throw new \Error("Не собран файл авто загрузчика для пакета!");

/* ======== AUTOLOAD ======== */

/**
 * @version 1.1.0
 * @author awenn2015@gmail.com
 */
class ValidateMapStruct {
  /**
   * @param mixed $data
   * @param DataStruct[] $struct
   * @return DataReport
   */
  public static function init($data, array $struct): DataReport {
    $test = self::testArray($data, ["Global"]);
    if (!$test->status) return $test;

    return self::testByMap($struct, $data, ['Global']);
  }

  /**
   * @param DataStruct[] $listOfStruct
   * @param array|null $data
   * @param string[]|string|null $keyOrKeys
   * @return DataReport
   */
  public static function testByMap(array $listOfStruct, ?array $data, $keyOrKeys = null): DataReport {
    self::testKeyOrKeys($keyOrKeys);
    $keys = (array)$keyOrKeys;

    if (!self::compareByCountKeys($listOfStruct, $data))
      return self::returnResult(false, DataMsg::DiffKeys, $keys);

    foreach ($listOfStruct as $struct) {
      $keyExist = array_key_exists($struct->key, $data);
      $testKey = $struct->nullable || $keyExist;

      if (!$testKey) {
        $msg = DataMsg::MissingKey($struct->key);
        return self::returnResult(false, $msg, array_merge($keys, [$struct->key]));
      }

      if ($struct->nullable && !$keyExist) continue;

      $keys = array_merge($keys, [$struct->key]);

      // $result = null;
      // switch ($struct->type) {
      //   case DataStruct::MAP:
      //     $result = self::testByMap($struct->nested, $data[$struct->key], $keys);
      //     break;
      //   case DataStruct::LIST:
      //     $result = self::testByList($struct->nested, $data[$struct->key], $keys);
      //     break;
      //   case DataStruct::BASE:
      //     $result = self::testByBase($struct->scalar, $data[$struct->key], $keys, $struct->nullable);
      //     break;
      // }

      $result = self::detectDataType(
        $struct->type,
        $struct->nested,
        $data[$struct->key],
        $keys,
        $struct->scalar,
        $struct->nullable
      );

      if (!$result->status) return $result;

      array_pop($keys);
    }

    return self::returnResult(true, DataMsg::NoMapErr, $keyOrKeys);
  }

  /**
   * @param ListStruct $struct
   * @param array|null $list
   * @param string[]|string|null $keyOrKeys
   * @param bool $nullable
   * @return DataReport
   */
  public static function testByList(
    ListStruct $struct,
    ?array     $list,
               $keyOrKeys = null,
    bool       $nullable = false
  ): DataReport {
    self::testKeyOrKeys($keyOrKeys);
    $keys = (array)$keyOrKeys;

    foreach ($list as $i => $item) {
      $keys = array_merge($keys, [(string)$i]);
      // $result = null;

      $result = self::detectDataType(
        $struct->type,
        $struct->nested,
        $item,
        $keys,
        $struct->scalar,
        $nullable
      );

      // switch ($struct->type) {
      //   case DataStruct::MAP:
      //     $result = self::testByMap($struct->nested, $item, $keys);
      //     break;
      //   case DataStruct::LIST:
      //     $result = self::testByList($struct->nested, $item, $keys);
      //     break;
      //   case DataStruct::BASE:
      //     $result = self::testByBase($struct->scalar, $item, $keys);
      //     break;
      // }

      if (!$result->status) return $result;

      array_pop($keys);
    }

    return self::returnResult(true, DataMsg::NoListErr, $keyOrKeys);
  }

  /**
   * @param string $scalar
   * @param int|float|string|bool $data
   * @param string[]|string $keyOrKeys
   * @param bool $nullable
   * @return DataReport
   */
  public static function testByBase(
    string $scalar,
           $data,
           $keyOrKeys,
    bool   $nullable = false
  ): DataReport {
    self::testKeyOrKeys($keyOrKeys);

    $test = gettype($data) === $scalar || ($nullable && $data === null);

    return $test
      ? self::returnResult(true, "Все в порядке")
      : (function () use ($data, $scalar, $keyOrKeys) {
        $msg = DataMsg::NotEqualsTypes("$scalar вместо " . gettype($data));
        return self::returnResult(false, $msg, $keyOrKeys);
      })();
  }

  /* utilities */

  /**
   * @param DataStruct[] $listOfStruct
   * @param mixed $data
   * @return bool
   */
  public static function compareByCountKeys(array $listOfStruct, $data): bool {
    $calcNullable = fn(int $acc, DataStruct $item) => !$item->nullable ? $acc : ++$acc;
    $qtyNullable = array_reduce($listOfStruct, $calcNullable, 0);
    $cData = count($data);
    $cStruct = count($listOfStruct);
    $cRange = ["min" => $cStruct - $qtyNullable, "max" => $cStruct];

    return $cData >= $cRange["min"] && $cData <= $cRange["max"];
  }

  /* private */

  /**
   * @param string $type
   * @param DataStruct[]|ListStruct|null $nested
   * @param mixed $data
   * @param null|string|string[] $keys
   * @param string|null $scalar
   * @param bool $nullable
   * @return DataReport
   */
  private static function detectDataType(
    string  $type,
            $nested,
            $data,
            $keys,
    ?string $scalar = null,
    bool    $nullable = false
  ): DataReport {
    self::testNested($nested, $scalar);
    self::testKeyOrKeys($keys);

    if (in_array($type, [DataStruct::MAP, DataStruct::LIST])) {
      $test = self::testArray($data, $keys, $nullable);
      if (!$test->status) return $test;
    }

    $result = null;

    switch ($type) {
      case DataStruct::MAP:
        $result = self::testByMap($nested, $data, $keys);
        break;
      case DataStruct::LIST:
        $result = self::testByList($nested, $data, $keys, $nullable);
        break;
      case DataStruct::BASE:
        $result = self::testByBase($scalar, $data, $keys, $nullable);
        break;
    }

    return $result;
  }

  /**
   * @param mixed $data
   * @param string[]|string|null $keys
   * @param bool $nullable
   * @return DataReport|null
   */
  private static function testArray($data, $keys, bool $nullable = false): ?DataReport {
    if (is_array($data) || ($nullable && is_null($data)))
      return self::returnResult(true, "Все в порядке");

    $key = $keys[count($keys) - 1];

    $key = !is_numeric($key) ? $key : (function () use ($keys) {
      $last = array_slice($keys, -2);
      return "$last[0][$last[1]]";
    })();

    return self::returnResult(false, DataMsg::NotArray($key), $keys);
  }

  /**
   * @param DataStruct|ListStruct|null $nested
   * @param string|null $scalar
   * @return void
   */
  private static function testNested($nested, ?string $scalar) {
    $check = [
      is_array($nested) && (!(count($nested) > 0) || $nested[0] instanceof DataStruct),
      !($nested instanceof ListStruct),
      !is_null($nested),
      is_null($scalar)
    ];

    if (!$check[0] && $check[1] && $check[2] && $check[3]) {
      $err = "Параметр nested должен быть экземпляром класса DataStruct или ListStruct когда scalar = null";
      throw new \TypeError($err);
    }
  }

  /**
   * @param string[]|string|null $keyOrKeys
   * @return void
   */
  private static function testKeyOrKeys($keyOrKeys) {
    if (!is_array($keyOrKeys) && !is_string($keyOrKeys) && !is_null($keyOrKeys))
      throw new \TypeError("Параметр key должен быть строкой, массивом строк или NULL");
  }

  /**
   * @param bool $status
   * @param string|null $msg
   * @param string[]|string|null $keyOrKeys
   * @return DataReport
   */
  private static function returnResult(bool $status, ?string $msg, $keyOrKeys = null): DataReport {
    self::testKeyOrKeys($keyOrKeys);
    $keys = is_array($keyOrKeys) ? implode(" => ", $keyOrKeys) : $keyOrKeys;
    return new DataReport($status, $msg, $keys);
  }
}