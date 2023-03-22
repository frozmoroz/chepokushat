<?php

namespace App\Services\Recipes;

use App\Services\Dto\RecipeDto;
use App\Services\Translator\TranslatorInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

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
     * @param string $method GET, POST
     * @param string $path запрос
     * @param array $params параметры запроса
     * @return mixed
     */
    private function builder(string $method, string $path, array $params = []): mixed
    {
        try {
            $client = new Client($this->headers);
            $response = $client->request(
                $method,
                self::URI . $path,
                [
                    'query' => $params,
                ]
            );

            return json_decode($response->getBody()->getContents());
        } catch (Throwable $e) {
            Log::channel('sponacular')->error($e->getMessage());
            return false;
        }

    }

    /**
     * Поиск рецептов по строке
     * @param string $string поисковая строка
     */
    public function search(string $string): Collection
    {
        $params = [
            'query' => self::translateSearchString($string),
            'addRecipeInformation' => true,
            'addRecipeNutrition' => true,
        ];

        $recipesList = $this->builder('GET', 'complexSearch', $params);

        if (!$recipesList) {
            throw new NotFoundHttpException('По данному запросу рецепты не найдены');
        }

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

        if (!$recipeInfo) {
            throw new NotFoundHttpException('Рецепт не найдены');
        }

        $recipe = app(RecipeDto::class);
        $recipe->title = $recipeInfo->title;
        $recipe->image = $recipeInfo->image;
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
