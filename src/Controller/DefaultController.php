<?php

namespace App\Controller;

use App\Entity\Owner;
use App\Entity\Pet;
use App\Form\TestCaseType;
use App\Model\FormData;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Test\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/')]
    public function index(Request $request, EntityManagerInterface $entityManager, FormFactoryInterface $ff): Response
    {
        // Fixtures.
        if (!$entityManager->getRepository(Owner::class)->count([])) {
            for($i = 1; $i <= 5; $i++) {
                $owner = new Owner();
                $owner->setName('Owner '.$i);
                $entityManager->persist($owner);

                for ($j = 1; $j <= 5; $j++) {
                    $pet = new Pet();
                    $pet->setName($owner->getName().'\'s Pet '.$j);
                    $pet->setOwner($owner);
                    $owner->addPet($pet);
                    $entityManager->persist($pet);
                }
            }

            $entityManager->flush();
        }


        $data = new FormData();
        $data->someValue = 'default - ignore';
        $form = $ff->createNamed('', TestCaseType::class, $data, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $success = true;
        }

        return $this->render('default/index.html.twig', [
            'form' => $form,
            'errors' => $form->getErrors(),
            'success' => $success ?? false,
        ]);
    }
}
