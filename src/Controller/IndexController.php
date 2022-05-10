<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\EcmFormType;
use App\Service\HttpService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/', name: 'app__')]
class IndexController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpService $httpService)
    {
        $this->httpClient = $httpService->getHttpClient();
    }

    #[Route(path: '/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
    ): Response
    {
        $response = $this->httpClient->request('GET', 'content-type/list');

        $response = $this->httpClient->request(
            'GET',
            sprintf(
                'document/structure/%d',
                (int) $response->toArray()[0]['id']
            )
        );

        $structureData = $response->toArray();

        $form = $this->createForm(EcmFormType::class, null, [
            'collaborators' => $this->getCollaborators($structureData),
            'document_types' => $this->getDocumentTypes($structureData)
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData(), $token);
        }

        return $this->renderForm('base.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @param  array  $structureData
     *
     * @return  array<string, string>
     *
     * @throws  Exception
     */
    private function getCollaborators(array $structureData): array
    {
        $fields = $structureData['Fields'];

        $collaborators = [];
        foreach ($fields as $field) {
            // La liste est récupérée en vérifiant la clef en dur...
            // Ici, c'est 'Collaborateur' !
            if ('Collaborateur' === $field['Title']) {
                foreach ($field['ListElements'] as $collaborator) {
                    if ('' !== $collaborator['Id']) {
                        $collaborators[$collaborator['DisplayValue']] = $collaborator['Id'];
                    }
                }

                return $collaborators;
            }
        }

        throw new Exception('Collaborators not found !');
    }

    /**
     * @param  array  $structureData
     *
     * @return  array<string, string>
     */
    private function getDocumentTypes(array $structureData): array
    {
        $fields = $structureData['Fields'];

        $documentTypes = [];
        foreach ($fields as $field) {
            if ('Type de document' === $field['Title']) {
                foreach ($field['ListElements'] as $documentType) {
                    if ('' !== $documentType['Id']) {
                        $documentTypes[$documentType['DisplayValue']] = (int) $documentType['Id'];
                    }
                }
            }
        }

        return $documentTypes;
    }

    private function addDocument(int $contentTypeId)
    {
       $this->httpClient->request('POST', 'document/save', [
           'ContentTypeID' => $contentTypeId,
           'ObjectId' => uniqid((string) time(), true),
           'IpAddress' => Request::createFromGlobals()->getClientIp(),
           'IsLastVersion' => false,
           'IsDigitallySigned' => false,
           'Fields' => [

           ]
       ]);
    }
}
