<?php

namespace App\Infrastructure\Slim\Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JsonSerializable;

class Token implements JsonSerializable
{
    public string $algorithm = ALGORITHM;
    public string $type = TYPE;
    public string $issuer = ISSUER;
    public array $audience = AUDIENCE;
    public int $expirationTime;
    public int $notBefore;
    public int $issuedAt;

    /**
     * @param string|null $subject
     * @param string|null $jwtId
     * @param string|null $token
     */
    public function __construct(
        public ?string $subject = null,
        public ?string $jwtId = null,
        public ?string $token = null
    )
    {
    }

    /**
     * @return $this
     */
    public function encode(): self
    {
        $this->issuedAt = time();
        $this->expirationTime = $this->issuedAt + EXPIRATION_TIME_SECONDS;
        $this->notBefore = $this->issuedAt + NOT_BEFORE_SECONDS;

        $payload = $this->jsonSerialize();

        $this->token = JWT::encode($payload, SECRET, $this->algorithm);

        return $this;
    }

    /**
     * @return Token
     */
    public function decode(): static
    {
        $decode = JWT::decode($this->token, new Key(SECRET, 'HS256'), $headers);
        $this->subject = $decode->sub;
        return $this;
    }

    /**
     * @return string
     */
    public function toResponseToken(): string
    {
        $token = [
            'bearer' => $this->token
        ];

        $encoded = json_encode($token);

        return $encoded;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'exp' => $this->expirationTime,
            'nbf' => $this->notBefore,
            'iat' => $this->issuedAt
        ];

        if ($this->subject !== null)
        {
            $payload['sub'] = $this->subject;
        }

        if ($this->jwtId !== null)
        {
            $payload['jti'] = $this->jwtId;
        }

        return $payload;
    }
}
