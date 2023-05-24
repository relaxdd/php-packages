<?php

namespace Awenn2015\DataValidator\Data;

class ListStruct {
  public
  string $type;
  /** @var DataStruct[]|ListStruct|null $nested */
  public $nested;
  public ?string $scalar;
  
  /**
   * @param string $type
   * @param DataStruct[]|ListStruct|null $nested
   * @param string|null $scalar
   */
  public function __construct(string $type, $nested, ?string $scalar = null) {
    self::testAll($type, $nested, $scalar);
    
    $this->type = $type;
    $this->nested = $nested;
    $this->scalar = $scalar;
  }
  
  /* private */
  
  /**
   * @param string $type
   * @param DataStruct[]|ListStruct|null $nested
   * @param string|null $scalar
   * @return void
   */
  private static function testAll(string $type, $nested, ?string $scalar) {
    if ($type === "base" && $nested !== null) throw new \TypeError("Ошибка 1!");
    if ($type !== "base" && $scalar !== null) throw new \TypeError("Ошибка 2!");
    if ($type === "base" && $scalar === null) throw new \TypeError("Ошибка 3!");
    if ($type === "list" && !($nested instanceof ListStruct)) throw new \TypeError("Ошибка 4!");
    if ($type === "map" && (gettype($nested) !== "array" || !($nested[0] instanceof DataStruct))) throw new \TypeError("Ошибка 5!");
  }
}