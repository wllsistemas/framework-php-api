<?php

define('CHAVE_PRIVADA', 'chave_privada');

class Jwt
{
    public static function gerarJWT()
    {
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        $payload = json_encode([
            'id_usuario' => 23,
            'usuario' => 'ADMIN',
            'iat' => time(),
            'exp' => time() + 3600,
        ]);

        $base64Header = base64_encode($header);
        $base64Payload = base64_encode($payload);

        $base64Header = Jwt::replaceCaracteresParaGerarJwt($base64Header);
        $base64Payload = Jwt::replaceCaracteresParaGerarJwt($base64Payload);

        $assinatura = hash_hmac('SHA256', "$base64Header.$base64Payload", CHAVE_PRIVADA, true);
        $base64Assinatura = base64_encode($assinatura);
        $base64Assinatura = Jwt::replaceCaracteresParaGerarJwt($base64Assinatura);

        return "$base64Header.$base64Payload.$base64Assinatura";
    }

    private static function replaceCaracteresParaGerarJwt($dados)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], $dados);
    }

    private static function replaceCaracteresParaValidarJwt($dados)
    {
        return str_replace(['-', '_'], ['+', '/'], $dados);
    }

    public static function tokenValido($token)
    {
        $token = str_replace('Bearer ', '', $token);
        $blocos = explode('.', $token);
        if (count($blocos) != 3) {
            return 'Token não possui os requisitos (header, payload e assinatura)';
        }

        list($header, $payload, $assinatura) = $blocos;

        $decodeHeader = Jwt::replaceCaracteresParaValidarJwt($header);
        $decodePayload = Jwt::replaceCaracteresParaValidarJwt($payload);

        $decodeHeader = Jwt::trataComprimentoBase64($decodeHeader);
        $decodePayload = Jwt::trataComprimentoBase64($decodePayload);

        if (Jwt::tokenEstaExpirado($decodePayload)) {
            return 'Token está expirado';
        }

        $assinaturaCalculada = hash_hmac('SHA256', "$header.$payload", CHAVE_PRIVADA, true);
        $base64Assinatura = Jwt::replaceCaracteresParaGerarJwt(base64_encode($assinaturaCalculada));

        if ($base64Assinatura != $assinatura) {
            return 'Assinatura do token inválida';
        }

        return '';
    }

    private static function trataComprimentoBase64($dados)
    {
        $padding = 4 - (strlen($dados) % 4);
        $dados .= str_repeat('=', $padding);
        return base64_decode($dados);
    }

    private static function tokenEstaExpirado($payload)
    {
        $payload = json_decode($payload, true);
        return (isset($payload['exp']) && $payload['exp'] < time());
    }
}
