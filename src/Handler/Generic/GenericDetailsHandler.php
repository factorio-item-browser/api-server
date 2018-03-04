<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Client\Constant\EntityType;
use FactorioItemBrowser\Api\Client\Entity\GenericEntity;
use FactorioItemBrowser\Api\Server\Database\Service\RecipeService;
use FactorioItemBrowser\Api\Server\Database\Service\TranslationService;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The handler of the /generic/details request.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class GenericDetailsHandler extends AbstractRequestHandler
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
     * Creates the input filter to use to verify the request.
     * @return InputFilter
     */
    protected function createInputFilter(): InputFilter
    {
        $entityFilter = new InputFilter();
        $entityFilter
            ->add([
                'name' => 'type',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ])
            ->add([
                'name' => 'name',
                'required' => true,
                'validators' => [
                    new NotEmpty()
                ]
            ]);

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'type' => CollectionInputFilter::class,
            'name' => 'entities',
            'input_filter' => $entityFilter,
            'required' => true,
        ], 'entities');
        return $inputFilter;
    }

    /**
     * Creates the response data from the validated request data.
     * @param DataContainer $requestData
     * @return array
     */
    protected function handleRequest(DataContainer $requestData): array
    {
        $namesByTypes = [];
        foreach ($requestData->getObjectArray('entities') as $entityData) {
            $namesByTypes[$entityData->getString('type')][] = $entityData->getString('name');
        }

        $entities = [];
        $recipeNames = $namesByTypes[EntityType::RECIPE] ?? [];
        foreach ($this->recipeService->filterAvailableNames($recipeNames) as $recipeName) {
            $entities[] = $this->createGenericEntity(EntityType::RECIPE, $recipeName);
        }
        unset($namesByTypes[EntityType::RECIPE]);

        $this->translationService->translateEntities(true);
        return [
            'entities' => $entities
        ];
    }

    /**
     * Creates a generic entity with the specified type and name.
     * @param string $type
     * @param string $name
     * @return GenericEntity
     */
    protected function createGenericEntity(string $type, string $name): GenericEntity
    {
        $entity = new GenericEntity();
        $entity->setType($type)
            ->setName($name);

        $this->translationService->addEntityToTranslate($entity);
        return $entity;
    }
}