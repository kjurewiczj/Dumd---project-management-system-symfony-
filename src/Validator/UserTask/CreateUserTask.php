<?php

namespace App\Validator\UserTask;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class CreateUserTask extends Constraint
{
    public $message = 'Użytkownik jest już dodany do tego zadania.';
}
