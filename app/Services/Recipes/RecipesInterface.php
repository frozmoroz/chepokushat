<?php

namespace App\Services\Recipes;

use App\Services\Dto\RecipeDto;
use Illuminate\Support\Collection;

interface RecipesInterface
{
    /**
     * Поиск рецептов по строке
     * @param string $string
     * @return Collection
     */
    public function search(string $string): Collection;

    /**
     * Получить рецепт по айди
     * @param string $id
     * @return RecipeDto
     */
    public function getRecipeById(string $id): RecipeDto;
}
