<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class IngredientControllerTest extends TestCase
{

    use DatabaseMigrations;
    
    public $ingredientData = [
        'title' => 'Thyme',
        'desc' => 'This is a really great ingredient'
    ];
    
    /**
     * Attempts to create an ingredient
     */
    public function createIngredient($ingredient = [], $createUser = false)
    {
        if ( count($ingredient) == 0 ) $ingredient = $this->ingredientData;
        $response = $this->post('/api/ingredient', $ingredient, $this->headers($createUser));
        return $response;
    }
    
    /**
     * Creating an ingredient should be protected
     */
    public function testCreateIngredientNoAuth()
    {
        $this->createIngredient();
        $this->assertResponseStatus(400);
    }

    /**
     * Create an ingredient after being logged in
     */
    public function testCreateIngredientWithAuth()
    {
        $this->createIngredient([], true);
        $this->assertResponseStatus(200);

        $this->seeJson([
            'title' => $this->ingredientData['title'],
            'desc' => $this->ingredientData['desc'],
            'type' => 'ingredient'
        ]);
    }

    /**
     * See if we can retrieve a list of ingredients. No auth is needed and it should retrieve all ingredients
     */
    public function testAllIngredients()
    {
        $this->get('/api/ingredients', [], $this->headers());
        $this->assertResponseStatus(200);

        $this->createIngredient([], true);
        $this->createIngredient([
            'title' => 'This is a title',
            'type' => 'title'
        ], true);

        $this->get('/api/ingredients', [], $this->headers());
        $this->seeJson([
            'title' => $this->ingredientData['title'],
            'desc' => $this->ingredientData['desc'],
            'type' => 'ingredient'                
        ]);

        $this->seeJson([
            'title' => 'This is a title',
            'type' => 'title'
        ]);
    }
    
}
