<?php

namespace App\Validator\UserProject;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class CreateUserProject extends Constraint
{
    public $message = 'Użytkownik jest już dodany do tego projektu.';
}
