<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use FactorioItemBrowser\Api\Server\Mapper\RecipeMapper;
use Zend\InputFilter\ArrayInput;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /recipe/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class RecipeDetailsHandler extends AbstractRequestHandler
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
     * Creates the input filter to use for the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        $inputFilter = new InputFilter();
        $inputFilter
            ->add([
                'type' => ArrayInput::class,
                'name' => 'names',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ]);
        return $inputFilter;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $recipeNames = $requestData->getArray('names');
        $recipeIds = $this->recipeService->getIdsByNames($recipeNames);
        $databaseRecipes = $this->recipeService->getDetailsByIds($recipeIds);

        $clientRecipes = [];
        foreach ($databaseRecipes as $databaseRecipe) {
            $clientRecipes[] = RecipeMapper::mapDatabaseRecipeToClientRecipe(
                $databaseRecipe,
                $this->translationService
            );
        }

        $this->translationService->translateEntities();
        return [
            'recipes' => $clientRecipes
        ];
    }
}