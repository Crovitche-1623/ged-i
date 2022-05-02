<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/', name: 'app__')]
class IndexController extends AbstractController
{
    #[Route(path: '/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        HttpClientInterface $httpClient
    ): Response
    {
        $form = $this->createFormBuilder()
            ->add('firstName', TextType::class, [
                'attr' => [
                    'autofocus' => true
                ],
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('isMale', ChoiceType::class, [
                'choices' => [
                    'Monsieur' => true,
                    'Madame' => false
                ],
                'expanded' => true,
                'label' => 'Sexe',
                'multiple' => false
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance'
            ])
            ->add('cv', FileType::class, [
                'label' => 'Curriculum Vitae'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer votre candidature'
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du TOKEN
            $response = $httpClient->request('POST', 'http://157.26.82.44:2240/token', [
                'headers' => [
                    'Accept' => 'application/json'
                ],
                'body' => [
                    'grant_type' => 'password',
                    'username' => 'fanny.roulin',
                    'password' => 'fanny.roulin'
                ],
            ]);

            $token = $response->toArray()['access_token'];

            // Sauvegarde du document
            /*
            $httpClient->request('POST', 'http://157.26.82.44:2240/api/document/save', [
                'headers' => [
                    'Content-Type' =>
                    'Accept' => 'application/json',
                    'Authorization' => 'BEARER '. $token
                ]
            ]);

            */
            dd($form->getData(), $token);
        }

        return $this->renderForm('base.html.twig', [
            'form' => $form
        ]);
    }
}
