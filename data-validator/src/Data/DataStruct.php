<?php

namespace Awenn2015\DataValidator\Data;

class DataStruct {
  public string $key;
  public string $type;
  public ?bool $nullable;
  /** @var DataStruct[]|ListStruct|null $nested */
  public $nested;
  public ?string $scalar;
  
  /**
   * @param string $key
   * @param string $type
   * @param bool|null $nullable
   * @param DataStruct[]|ListStruct|null $nested
   * @param string|null $scalar
   */
  public function __construct(
    string $key,
    string $type,
    ?bool $nullable = null,
    $nested = null,
    ?string $scalar = null
  ) {
    self::testAll($key, $type, $nested, $scalar);
    
    if ($type !== self::BASE) $scalar = null;
    
    $this->key = $key;
    $this->type = $type;
    $this->nullable = $nullable;
    $this->nested = $nested;
    $this->scalar = $scalar;
  }
  
  public const BASE = "base";
  public const LIST = "list";
  public const MAP = "map";
  
  /* private */
  
  /**
   * @param string $key
   * @param string $type
   * @param DataStruct|ListStruct|null $nested
   * @param string|null $scalar
   * @return void
   */
  private static function testAll(string $key, string $type, $nested, ?string $scalar) {
    if (!self::testType($type)) throw new \TypeError(self::errs[0]);
    if (!self::testScalar($scalar)) throw new \TypeError(self::errs[1] . ", 1");
    if ($type === self::BASE && $scalar === null) throw new \TypeError(self::errs[1] . ", 2, ключ = $key");
    if ($type === self::BASE && $nested !== null) throw new \TypeError(self::errs[2]);
    if ($type === self::LIST && !($nested instanceof ListStruct))
      throw new \TypeError(self::errs[3] . ", ключ = $key");
  }
  
  private static function testType(string $type): bool {
    return in_array($type, ["base", "list", "map"]);
  }
  
  private static function testScalar(?string $scalar): bool {
    if ($scalar === null) return true;
    $types = ["boolean", "integer", "double", "string", "array", "object"];
    return in_array($scalar, $types);
  }
  
  private const errs = [
    "Параметр type должен быть перечислением из: base, list или map!",
    "Параметр scalar должен быть перечислением из gettype когда параметр type равен 'base'",
    "Компонент не должен содержать внутреннюю структуру когда параметр type равен 'base'",
    "Параметр nested должен быть экземпляром класса DataStruct когда параметр type равен 'list'"
  ];
}