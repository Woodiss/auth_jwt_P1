<?php

namespace App\Security;

class JWT
{
  private string $secret;
  private int $expire;

  public function __construct(string $secret, int $expire = 3600)
  {
    $this->secret = $secret;
    $this->expire = $expire;
  }

  private function base64UrlEncode(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  private function base64UrlDecode(string $data): string
  {
    $pad = 4 - (strlen($data) % 4);
    if ($pad < 4) $data .= str_repeat('=', $pad);
    return base64_decode(strtr($data, '-_', '+/'));
  }

  public function generate(array $payload): string
  {
    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $this->expire;

    $base64Header = $this->base64UrlEncode(json_encode($header));
    $base64Payload = $this->base64UrlEncode(json_encode($payload));

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $this->secret, true);
    $base64Signature = $this->base64UrlEncode($signature);

    return "$base64Header.$base64Payload.$base64Signature";
  }

  public function verify(string $token): ?array
  {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $payload, $sig] = $parts;
    $expected = $this->base64UrlEncode(hash_hmac('sha256', "$header.$payload", $this->secret, true));

    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode($this->base64UrlDecode($payload), true);
    if ($data['exp'] < time()) return null;

    return $data;
  }
}
