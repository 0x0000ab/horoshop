<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate($entity): array
    {
        $entityErrors = $this->validator->validate($entity);
        $errors = [];
        if (count($entityErrors) > 0) {
            foreach ($entityErrors as $error) {
                $errors[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
        }

        return $errors;
    }

}
