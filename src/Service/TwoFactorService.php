<?php

namespace App\Service;

use OTPHP\TOTP;
use Twilio\Rest\Client;
use SendGrid;
use SendGrid\Mail\Mail;

class TwoFactorService
{
  public function generateSecret(string $method = 'otp'): string
  {
    if ($method === 'otp') {
      $totp = TOTP::create();
      return $totp->getSecret();
    }

    // Pour email ou SMS → on génère un code à 6 chiffres
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  }

  public function getQrCodeUrl(string $email, string $secret, string $issuer = 'MonApp'): string
  {
    $totp = TOTP::create($secret);
    $totp->setLabel($email);
    $totp->setIssuer($issuer);

    return $totp->getProvisioningUri();
  }

  public function verifyCode(string $method, string $secret, string $code): bool
  {
    switch ($method) {
      case 'otp':
        $totp = TOTP::create($secret);
        return $totp->verify($code);

      case 'email':
      case 'sms':
        // Simple comparaison du code saisi et du code stocké
        return hash_equals($secret, $code);

      default:
        return false;
    }
  }

  public function sendEmailCode(string $email, string $code): void
  {
    $mail = new Mail();
    $mail->setFrom($_ENV["SENGRID_SENDER"], "My2FAapp");
    $mail->setSubject("Votre code de vérification");
    $mail->addTo($email);
    $mail->addContent("text/plain", "Votre code de vérification est : $code");

    $sendgrid = new SendGrid($_ENV['SENGRID_API_KEY']);
    $sendgrid->send($mail);
  }

  public function sendSmsCode(string $phone, string $code): void
  {
    /* $sid = getenv('TWILIO_SID'); */
    $sid = $_ENV['TWILIO_SID'];
    /* $token = getenv('TWILIO_AUTH_TOKEN'); */
    $token = $_ENV['TWILIO_AUTH_TOKEN'];
    /* $from = getenv('TWILIO_PHONE'); */
    $from = $_ENV['TWILIO_PHONE'];

    $client = new Client($sid, $token);
    $client->messages->create($phone, [
      'from' => $from,
      'body' => "Votre code de vérification est : $code"
    ]);
  }
}
