<?php

namespace App\Services\Translator;

interface TranslatorInterface
{
    /**
     * Получить перевод
     * @param array $texts
     * @return array
     */
    public function translate(array $texts): array;

    /**
     * Задать язык, на который переводится текст
     * @param string $targetLanguage
     * @return void
     */
    public function setTargetLanguage(string $targetLanguage): void;

    /**
     * Задать язык, с которого переводится текст
     * @param string $sourceLanguage
     * @return void
     */
    public function setSourceLanguage(string $sourceLanguage): void;
}
