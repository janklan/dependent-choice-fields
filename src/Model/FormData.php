<?php

namespace App\Model;

use App\Entity\Owner;
use App\Entity\Pet;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FormData {
    public bool $petRequired = true;

    #[Assert\NotBlank]
    public ?Owner $owner = null;


    public ?Pet $pet = null;


    #[Assert\Callback]
    public function assertUserGroupSelected(ExecutionContextInterface $context): void
    {
        if (!$this->petRequired) {
            return;
        }

        if (!$this->owner || $this->pet instanceof Pet) {
            return;
        }

        $context->buildViolation('Pet must be selected when the Owner is selected.')
            ->atPath('pet')
            ->addViolation()
        ;
    }
}
