<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use FactorioItemBrowser\Api\Client\Entity\Item as ClientItem;
use FactorioItemBrowser\Api\Client\Entity\Recipe as ClientRecipe;
use FactorioItemBrowser\Api\Server\Database\Entity\Recipe as DatabaseRecipe;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /recipe/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsHandler implements RequestHandlerInterface
{
    /**
     * The database recipe service.
     * @var RecipeService
     */
    protected $recipeService;

    /**
     * The database translation service.
     * @var TranslationService
     */
    protected $translationService;

    /**
     * Initializes the auth handler.
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(RecipeService $recipeService, TranslationService $translationService)
    {
        $this->recipeService = $recipeService;
        $this->translationService = $translationService;
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = $this->createInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            throw new ValidationException($inputFilter->getMessages());
        }

        $recipeName = $inputFilter->getValue('name');
        $recipeIds = $this->recipeService->getIdsByNames([$recipeName]);
        $recipes = $this->recipeService->getDetailsByIds($recipeIds);

        foreach ($recipes as $index => $recipe) {
            $recipes[$index] = $this->mapDatabaseRecipeToClientRecipe($recipe);
        }
        $this->translationService->translateEntities(true);

        /* @var ClientRecipe[] $recipes */
        foreach ($recipes as $index => $recipe) {
            $recipes[$index] = $recipe->writeData();
        }
        return new JsonResponse([
            'recipes' => $recipes
        ]);
    }

    /**
     * Creates the input filter to use for the request.
     * @return InputFilter
     */
    protected function createInputFilter()
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'name' => 'name',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ]);
        return $inputFilter;
    }

    /**
     * Maps the specified database recipe to a client recipe instance.
     * @param DatabaseRecipe $databaseRecipe
     * @return ClientRecipe
     */
    protected function mapDatabaseRecipeToClientRecipe(DatabaseRecipe $databaseRecipe): ClientRecipe
    {
        $clientRecipe = new ClientRecipe();
        $clientRecipe->setName($databaseRecipe->getName())
                     ->setMode($databaseRecipe->getMode())
                     ->setCraftingTime($databaseRecipe->getCraftingTime());

        foreach ($databaseRecipe->getIngredients() as $databaseIngredient) {
            $clientItem = new ClientItem();
            $clientItem->setName($databaseIngredient->getItem()->getName())
                       ->setType($databaseIngredient->getItem()->getType())
                       ->setAmount($databaseIngredient->getAmount());
            $clientRecipe->addIngredient($clientItem);
            $this->translationService->addEntityToTranslate($clientItem);
        }

        foreach ($databaseRecipe->getProducts() as $databaseProduct) {
            $clientItem = new ClientItem();
            $clientItem->setName($databaseProduct->getItem()->getName())
                       ->setType($databaseProduct->getItem()->getType())
                       ->setAmount($databaseProduct->getAmount());
            $clientRecipe->addProduct($clientItem);
            $this->translationService->addEntityToTranslate($clientItem);
        }

        $this->translationService->addEntityToTranslate($clientRecipe);
        return $clientRecipe;
    }
}