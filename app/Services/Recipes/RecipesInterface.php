<?php

namespace App\Services\Recipes;

use App\Services\Dto\RecipeDto;
use Illuminate\Support\Collection;
use Nette\Utils\Paginator;

interface RecipesInterface
{
    /**
     * Поиск рецептов по строке
     * @param string $string
     * @param Paginator $paginator
     * @return Collection
     */
    public function search(string $string, Paginator $paginator): Collection;

    /**
     * Получить рецепт по айди
     * @param string $id
     * @return RecipeDto
     */
    public function getRecipeById(string $id): RecipeDto;
}
