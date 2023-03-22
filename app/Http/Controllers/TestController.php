<?php

namespace App\Http\Controllers;

use App\Services\Recipes\RecipesInterface;
use App\Services\Translator\TranslatorInterface;
use App\Services\UserRepository;
use Carbon\Carbon;
use Google_Client;
use Google_Service_YouTube;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenFoodFacts\Api;

class TestController extends Controller
{

    public function index(RecipesInterface $recipes)
    {
//        $data = $recipes->search('члены бабки');
        $data = $recipes->getRecipeById(638420);
        dd($data);
        return view('welcome', $data);
        $trans = ['user'];
        dd($translator->translate($trans));

        $guzzle = new Client(['headers' => ['x-api-key' => '4264d4bdfe6e4f19948dff0ae7624ebd']]);

//        $a = $guzzle->get('https://api.spoonacular.com/recipes/complexSearch?apiKey=4264d4bdfe6e4f19948dff0ae7624ebd',
//            [
//                'query' => [
//                    'query' => 'pasta'
//                ],
//            ]);
//        dd($a->getBody()->getContents());
        $id = 654959;
        $a = $guzzle->get("https://api.spoonacular.com/recipes/$id/ingredientWidget.json",);
        dd($a->getBody()->getContents());
        dd($userRepository->all(), $userRepository->getById(), $userRepository->random());
    }
}
