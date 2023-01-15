<?php

namespace App\Validator\UserTask;

use App\Repository\UserTaskRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CreateUserTaskValidator extends ConstraintValidator
{
    public function __construct(private UserTaskRepository $userTaskRepository){}

    public function validate($value, Constraint $constraint)
    {
        $root = $this->context->getRoot();

        if ($root->getData()->getTask()->getUserAssigned()->getId() != $root->getData()->getUser()->getId()) {
            if (!$this->userTaskRepository->findOneBy(['user' => $root->getData()->getUser(), 'task' => $root->getData()->getTask()])) {
                return;
            }
        }



        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
