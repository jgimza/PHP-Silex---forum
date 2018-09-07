<?php
/**
 * Unique Login constraint.
 */
namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueLogin.
 */

class UniqueLogin extends Constraint
{

    /**
     * Message.
     *
     * @var string $message
     */

    public $message = 'Login nie jest unikalny.';

    /**
     * Element id.
     *
     * @var int|string|null $elementId
     */

    public $elementId = null;

    /**
     * User repository.
     *
     * @var null|\Repository\UserRepository $repository
     */

    public $repository = null;

}