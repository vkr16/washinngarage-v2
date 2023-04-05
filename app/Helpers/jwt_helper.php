<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getJWT($headerAuthentication)
{
    if (is_null($headerAuthentication)) {
        return false;
    }

    return explode(" ", $headerAuthentication)[1];
}

function decodeJWT($encodedToken)
{
    $key = getenv('JWT_SECRET_KEY');
    $decodedToken = JWT::decode($encodedToken, new Key($key, 'HS256'));

    return $decodedToken;
}

function encodeJWT($email)
{
    $payload = [
        'email' => $email,
        'iat' => time(),
        'exp' => time() + getenv('JWT_TTL')
    ];

    $jwt = JWT::encode($payload, getenv('JWT_SECRET_KEY'), 'HS256');
    return $jwt;
}

function renewJWT($decodedToken)
{
    $email = $decodedToken->email;
    $newJWT = encodeJWT($email);

    return $newJWT;
}
