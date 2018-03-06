<?php

declare(strict_types=1);

namespace FactorioItemBrowser\Api\Server\Handler\Generic;

use BluePsyduck\Common\Data\DataContainer;
use FactorioItemBrowser\Api\Server\Handler\AbstractRequestHandler;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

/**
 * The abstract class of the generic handlers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractGenericHandler extends AbstractRequestHandler
{
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
     * Returns the entity names grouped by the entity types.
     * @param DataContainer $requestData
     * @return array|string[][]
     */
    protected function getEntityNamesByType(DataContainer $requestData): array
    {
        $namesByTypes = [];
        foreach ($requestData->getObjectArray('entities') as $entityData) {
            $namesByTypes[$entityData->getString('type')][] = $entityData->getString('name');
        }
        return $namesByTypes;
    }
}