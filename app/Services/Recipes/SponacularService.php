<?php

namespace App\Services\Recipes;

use App\Services\Dto\RecipeDto;
use App\Services\Translator\TranslatorInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

class SponacularService implements RecipesInterface
{
    /** переводчик */
    private TranslatorInterface $translator;

    /** заголовки запроса */
    private array $headers;

    const URI = 'https://api.spoonacular.com/recipes/';

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->headers = ['headers' => ['x-api-key' => env('SPONACULAR_API_KEY')]];
    }

    /**
     * Построитель запроса к сервису
     * @throws GuzzleException
     */
    private function builder(string $method, string $path, array $params = [])
    {
        $client = new Client($this->headers);
        $response = $client->request(
            $method,
            self::URI . $path,
            [
                'query' => $params,
            ]
        );
        $response = json_decode($response->getBody()->getContents());

        return $response;
    }

    /**
     * Поиск рецептов по строке
     * @throws GuzzleException
     */
    public function search(string $string): Collection
    {
        $params = [
            'query' => self::translateSearchString($string),
            'addRecipeInformation' => true,
            'addRecipeNutrition' => true,
        ];

        $recipesList = $this->builder('GET', 'complexSearch', $params);

        $recipesListTitles = collect($recipesList->results)->pluck('title')->toArray();
        $translatedTitles = $this->translator->translate($recipesListTitles);

        return collect($recipesList->results)->map(function ($recipe, $key) use ($translatedTitles) {
            $recipe->title = $translatedTitles[$key];
            return $recipe;
        });
    }

    /**
     * Получить рецепт по айди
     * @param string $id
     * @return RecipeDto
     * @throws GuzzleException
     */
    public function getRecipeById(string $id): RecipeDto
    {
        $recipeInfo = $this->builder('GET', "$id/information");

        $recipe = app(RecipeDto::class);
        $recipe->title = $recipeInfo->title;
//        $recipe->image = $recipeInfo->image;
        $recipe->description = $recipeInfo->summary;
        $recipe->instructions = $recipeInfo->instructions;

        foreach ($recipeInfo->extendedIngredients as $ingredient) {
            $ingredientInfo['name'] = $ingredient->name;
            $ingredientInfo['unit'] = "$ingredient->amount $ingredient->unit";
            $recipe->ingredients[] = $ingredientInfo;
        }

        $recipe->translate();

        return $recipe;
    }

    /**
     * Перевести исходный текст с русского на английский
     * @param string $string
     * @return mixed
     */
    private static function translateSearchString(string $string): mixed
    {
        $translator = app(TranslatorInterface::class);

        $translator->setTargetLanguage('en');
        $translator->setSourceLanguage('ru');

        $string = $translator->translate([$string]);

        return array_shift($string);
    }
}
