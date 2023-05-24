<?php

namespace Awenn2015\RestOnActions\Http;

class Status {
  public static int $SUCCESS = 200;
  public static int $CREATED = 201;
  public static int $ACCEPTED = 202;
  
  public static int $BAD_REQUEST = 400;
  public static int $UNAUTHORIZED = 401;
  public static int $FORBIDDEN = 403;
  public static int $NOT_FOUND = 404;
  
  public static int $SERVER_ERROR = 500;
  public static int $NOT_IMPLEMENTED = 501;
}

