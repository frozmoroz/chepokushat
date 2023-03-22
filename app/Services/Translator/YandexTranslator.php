<?php

namespace App\Services\Translator;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class YandexTranslator implements TranslatorInterface
{
    /** id директории в яндекс облаке */
    private string $folderId;

    /** язык, на который переводится текст */
    private string $targetLanguage = 'ru';

    /** язык, с которого переводится текст */
    private string $sourceLanguage = 'en';

    /**
     * Токен яндекса для перевода (iamToken and expiresAt)
     * @var object
     */
    private $yandexTranslateToken;

    public function __construct()
    {
        $this->folderId = env("YANDEX_TRANSLATE_CATALOG");
    }

    /**
     * Задать язык, на который переводится текст
     * @param string $targetLanguage
     * @return void
     */
    public function setTargetLanguage(string $targetLanguage): void
    {
        $this->targetLanguage = $targetLanguage;
    }

    /**
     * Задать язык, с которого переводится текст
     * @param string $sourceLanguage
     * @return void
     */
    public function setSourceLanguage(string $sourceLanguage): void
    {
        $this->sourceLanguage = $sourceLanguage;
    }

    /**
     * Получить перевод
     * @param array $texts
     * @return array
     * @throws GuzzleException
     */
    public function translate(array $texts): array
    {
        $IAM_TOKEN = $this->getIamToken();

        $url = 'https://translate.api.cloud.yandex.net/translate/v2/translate';

        $headers = [
            'Content-Type: application/json',
            "Authorization: Bearer $IAM_TOKEN"
        ];

        $post_data = [
            "targetLanguageCode" => $this->targetLanguage,
            "sourceLanguageCode" => $this->sourceLanguage,
            "texts" => $texts,
            "folderId" => $this->folderId,
        ];

        $data_json = json_encode($post_data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);

        $result = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($result);

        if (isset($result->translations)) {
            return collect($result->translations)->pluck('text')->toArray();
        }

        Log::error('Translation failed');
        Log::error('data' . json_encode($result));

        return [];
    }

    /**
     * Получить токен для работы яндекс переводчика
     * @return string
     * @throws GuzzleException
     */
    private function getIamToken(): string
    {
        // Если токена нет, то создаем
        if (!$this->yandexTranslateToken) {
            $this->yandexTranslateToken = self::createIamToken();
            return $this->yandexTranslateToken->iamToken;
        }

        // Если есть, проверяем не протух ли он
        $now = Carbon::now();
        $tokenTime = strstr($this->yandexTranslateToken->expiresAt, '.', true);
        $tokenTime = Carbon::parse($tokenTime)->subHours(11);
        if ($now < $tokenTime) {
            $this->yandexTranslateToken = self::createIamToken();
        }

        return $this->yandexTranslateToken->iamToken;
    }


    /**
     * Создать токен для работы яндекс переводчика
     * @return null
     * @throws GuzzleException
     */
    private static function createIamToken()
    {
        $client = new Client();
        $url = 'https://iam.api.cloud.yandex.net/iam/v1/tokens';
        $headers = ['query' => [
            'yandexPassportOauthToken' => env("YANDEX_TRANSLATE_TOKEN"),
        ]];
        $response = $client->post($url, $headers);

        return json_decode($response->getBody());
    }
}
