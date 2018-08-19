<?php
/**
 * Unique Tag constraint.
 */
namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueTag.
 */
class UniqueLogin extends Constraint
{
    /**
     * Message.
     *
     * @var string $message
     */
    public $message = '{{ tag }} is not unique Login.';

    /**
     * Element id.
     *
     * @var int|string|null $elementId
     */
    public $elementId = null;

    /**
     * Tag repository.
     *
     * @var null|\Repository\TagsRepository $repository
     */
    public $repository = null;

}