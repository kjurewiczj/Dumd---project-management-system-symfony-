<?php

namespace App\Validator\UserProject;

use App\Repository\User\UserProjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CreateUserProjectValidator extends ConstraintValidator
{
    public function __construct(private UserProjectRepository $userProjectRepository){}

    public function validate($value, Constraint $constraint)
    {
        $root = $this->context->getRoot();

        if (!$this->userProjectRepository->findOneBy(['user' => $root->getData()->getUser(), 'project' => $root->getData()->getProject()])) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
