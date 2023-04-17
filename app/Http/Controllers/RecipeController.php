<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRecipeRequest;
use App\Services\Recipes\RecipesInterface;
use Illuminate\Http\JsonResponse;
use Nette\Utils\Paginator;

class RecipeController extends Controller
{
    /**
     * Получить рецепт по айди
     * @param int $id
     * @param RecipesInterface $recipes
     * @return JsonResponse
     */
    public function find(int $id, RecipesInterface $recipes): JsonResponse
    {
        return response()->json($recipes->getRecipeById($id));
    }

    /**
     * Поиск рецептов по поисковой строке
     * @param string $string
     * @param SearchRecipeRequest $request
     * @param RecipesInterface $recipes
     * @return JsonResponse
     */
    public function search(string $string, SearchRecipeRequest $request, RecipesInterface $recipes): JsonResponse
    {
        // Получаем пагинацию
        $paginator = self::getPaginator($request->validated());

        // Ищем рецепты
        $recipes = $recipes->search($string, $paginator);

        return response()->json($recipes);
    }

    /**
     * Пагинатор
     * @param array $data
     * @return Paginator
     */
    private static function getPaginator(array $data): Paginator
    {
        $paginator = new Paginator();
        $paginator->setPage($data['page'] ?? 1);
        $paginator->setItemsPerPage($data['perPage'] ?? 10);

        return $paginator;
    }
}
