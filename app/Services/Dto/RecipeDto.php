<?php

namespace App\Services\Dto;

use App\Services\Translator\TranslatorInterface;
use JetBrains\PhpStorm\ArrayShape;

/** DTO для работы с рецептом */
class RecipeDto
{
    /** Тайтл */
    public string $title = '';

    /** Превью картинка */
    public string $image = '';

    /** Описание */
    public string $description = '';

    /** Ингредиенты */
    public array $ingredients = [];

    /** Инструкции по преготовлению */
    public string $instructions = '';

    /** Переводчик */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Переводит нужные поля
     * @return void
     */
    public function translate(string $targetLanguage = 'ru')
    {
        $this->translator->setTargetLanguage($targetLanguage);

        // Переводим нужные поля
        $data = [&$this->title, &$this->description, &$this->instructions];
        $translatedData = $this->translator->translate($data);

        foreach ($data as $key => &$item) {
            $item = $translatedData[$key];
        }

        // Переводим ингредиенты
        $names = array_column($this->ingredients, 'name');
        $units = array_column($this->ingredients, 'unit');

        $translatedData = $this->translator->translate([...$names, ...$units]);

        foreach ($this->ingredients as $key => &$ingredient) {
            $ingredient['name'] = $translatedData[$key];
            $ingredient['unit'] = $translatedData[$key + count($names)];
        }

        unset($this->translator);
    }

    /**
     * Перевести в массив
     * @return array
     */
    #[ArrayShape([
        'title' => "string",
        'image' => "string",
        'description' => "string",
        'ingredients' => "array",
        'instructions' => "string"
    ])] public function toArray(): array
    {
        return [
            'title' => $this->title,
            'image' => $this->image,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'instructions' => $this->instructions,
        ];
    }
}