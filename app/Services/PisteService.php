<?php

class PisteService
{
    private string $clientId;
    private string $clientSecret;
    private string $authUrl = 'https://piste.gouv.fr/cas/oauth2.0/token';

    public function __construct(string $envPath)
    {
        $this->loadEnv($envPath);
        $this->clientId = isset($_ENV['CLIENT_ID']) ? trim($_ENV['CLIENT_ID']) : '';
        $this->clientSecret = isset($_ENV['CLIENT_SECRET']) ? trim($_ENV['CLIENT_SECRET']) : '';
    }

    private function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $cleanValue = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[trim($name)] = $cleanValue;
        }
    }

    public function getToken(): string
    {
        $ch = curl_init();

        $postFields = http_build_query([
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'openid',
        ]);

        curl_setopt($ch, CURLOPT_URL, $this->authUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return $result['access_token'] ?? 'Erreur : Token absent de la réponse JSON';
        }

        return "Erreur HTTP $httpCode : (Vérifiez votre Client Secret sur PISTE)";
    }
}
