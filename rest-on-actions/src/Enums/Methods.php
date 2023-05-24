<?php

namespace Awenn2015\RestOnActions\Enums;

enum Methods: string {
  case Get = "GET";
  case Post = "POST";
  case Delete = "DELETE";
  case Patch = "PATCH";
  case Put = "PUT";
}
