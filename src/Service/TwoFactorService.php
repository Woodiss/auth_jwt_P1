<?php

namespace App\Service;

use OTPHP\TOTP;

class TwoFactorService
{
  public function generateSecret(): string
  {
    $totp = TOTP::create();
    return $totp->getSecret();
  }

  public function getQrCodeUrl(string $email, string $secret, string $issuer = 'MonApp'): string
  {
    $totp = TOTP::create($secret);
    $totp->setLabel($email);
    $totp->setIssuer($issuer);

    return $totp->getProvisioningUri();
  }

  public function verifyCode(string $secret, string $code): bool
  {
    $totp = TOTP::create($secret);
    return $totp->verify($code);
  }
}
