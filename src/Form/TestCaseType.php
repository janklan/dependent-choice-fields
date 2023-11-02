<?php

namespace App\Form;

use App\Entity\Owner;
use App\Entity\Pet;
use App\Model\FormData;
use App\Repository\OwnerRepository;
use App\Repository\PetRepository;
use Cognetiq\CoreBundle\Model\IndustryDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Uid\Uuid;

class TestCaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('petRequired', CheckboxType::class, options: [
                'required' => false,
                'label' => 'The Pet is required (tick to trigger the scenario under test)'
            ])
            ->add('owner', EntityType::class, [
                'class' => Owner::class,
                'query_builder' => fn (OwnerRepository $repository) => $repository->createQueryBuilder('owner')
                    ->setMaxResults(2)
            ])
        ;

//        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
//            $data = $event->getData();
//
//            if (!empty($data['owner'])) {
//                $form = $event->getForm();
//                $this->addPet($form, $data['owner']);
//            }
//        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $dto = $event->getData();
            assert($dto instanceof FormData);
            $form = $event->getForm();

            dump($dto); // this holds the initial value

            if ($dto->owner) {
                $this->addPet($form, $dto->owner->getId());
            }

//            if ($dto->owner && !$form->has('pet')) {
//                $this->addPet($form, $dto->owner->getId());
//            }
        });
    }

    private function addPet(FormInterface $form, int $ownerId): void {
        $form->add('pet', EntityType::class, [
            'placeholder' => '---> Please select a pet',
            'class' => Pet::class,
            'query_builder' => fn (PetRepository $repository) => $repository->createQueryBuilder('pet')
                ->where('pet.owner = :owner')
                ->setParameter('owner', $ownerId)
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'get',
            'data_class' => FormData::class,
        ]);
    }
}
