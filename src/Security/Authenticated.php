<?php

namespace App\Security;

#[\Attribute]
class Authenticated
{
  public function __construct(public array $roles = []) {}
}
