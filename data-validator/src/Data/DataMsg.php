<?php

namespace Awenn2015\DataValidator\Data;

class DataMsg {
  public const DiffKeys = "Не соответствие количества ключей в Map";
  public const NoMapErr = "Сущность Map проверена, все в порядке";
  public const NoListErr = "Сущность list проверена, все в порядке";
  
  public static function NotArray(string $prm): string {
    return "Параметр '$prm' должен быть массивом";
  }
  
  public static function MissingKey(string $key): string {
    return "Отсутствует обязательный ключ '$key' в Map";
  }
  
  public static function NotEqualsTypes(string $types): string {
    return "Не соответствие типов данных, тип должен быть $types";
  }
}