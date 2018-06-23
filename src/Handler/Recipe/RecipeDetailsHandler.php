<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Recipe;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Entity\RecipeWithExpensiveVersion;
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
     * The recipe mapper.
     * @var RecipeMapper
     */
    protected $recipeMapper;

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
     * @param RecipeMapper $recipeMapper
     * @param RecipeService $recipeService
     * @param TranslationService $translationService
     */
    public function __construct(
        RecipeMapper $recipeMapper,
        RecipeService $recipeService,
        TranslationService $translationService
    ) {
        $this->recipeMapper = $recipeMapper;
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
        $clientRecipes = [];
        $recipeNames = $requestData->getArray('names');
        $groupedRecipeIds = $this->recipeService->getGroupedIdsByNames($recipeNames);
        if (count($groupedRecipeIds) > 0) {
            $allRecipeIds = call_user_func_array('array_merge', $groupedRecipeIds);
            $databaseRecipes = $this->recipeService->getDetailsByIds($allRecipeIds);

            foreach ($groupedRecipeIds as $recipeIds) {
                $currentRecipe = null;
                foreach ($recipeIds as $recipeId) {
                    if (isset($databaseRecipes[$recipeId])) {
                        $mappedRecipe = new RecipeWithExpensiveVersion();
                        $this->recipeMapper->mapRecipe($databaseRecipes[$recipeId], $mappedRecipe);

                        if (is_null($currentRecipe)) {
                            $currentRecipe = $mappedRecipe;
                        } else {
                            $this->recipeMapper->combineRecipes($currentRecipe, $mappedRecipe);
                        }
                    }
                }
                $clientRecipes[] = $currentRecipe;
            }
        }

        $this->translationService->translateEntities();
        return [
            'recipes' => $clientRecipes
        ];
    }
}