<?php

namespace App\Services\Translator;

interface TranslatorInterface
{
    public function translate(array $texts): array;

    public function setTargetLanguage(string $targetLanguage): void;

    public function setSourceLanguage(string $sourceLanguage): void;
}
