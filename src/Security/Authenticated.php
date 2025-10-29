<?php

namespace App\Security;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Authenticated
{
  public array $roles;

  public function __construct(array $roles = [])
  {
    $this->roles = $roles;
  }
}
