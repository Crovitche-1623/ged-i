<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ContentTypeNotFoundException;
use App\Exception\FieldCodeNotFoundException;
use App\Form\EcmFormType;
use App\Service\HttpService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/', name: 'app__')]
class IndexController extends AbstractController
{
    public const SEARCHED_CONTENT_TYPE = 'Dossier collaborateur';
    public const SEARCHED_FIELD = 'Collaborateur';

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
        $collaboratorFolderId = $this->getCollaboratorFolderContentTypeId();

        $structureData = $this->httpClient->request('GET', "document/structure/$collaboratorFolderId")->toArray();

        $form = $this->createForm(EcmFormType::class, null, [
            'collaborators' => $this->getCollaborators($structureData),
            'document_types' => $this->getDocumentTypes($structureData)
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            /** @var  UploadedFile|null  $file */
            $file = $formData['file'];

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = (string) (new AsciiSlugger)->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid((string) time(), true) . '.' . $file->guessExtension();

                $uploadDirectory = $this->getParameter('uploads_directory');
                try {
                    $file->move(
                        $uploadDirectory,
                        $newFilename
                    );
                } catch (FileException $exception) {
                    dd($exception);
                }

                $path = $uploadDirectory . $newFilename;

                $base64 = base64_encode(file_get_contents($path));

                $this->addDocument(
                    $collaboratorFolderId,
                    $this->getCollaboratorFieldCode($structureData, $collaboratorFolderId),
                    $formData[EcmFormType::COLLABORATOR_FORM_KEY],
                    $formData[EcmFormType::FILE_TYPE_FORM_KEY],
                    $newFilename,
                    $base64
                );
            }
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
            if (self::SEARCHED_FIELD === $field['Title']) {
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
                        $documentTypes[$documentType['DisplayValue']] = $documentType['Id'];
                    }
                }
            }
        }

        return $documentTypes;
    }

    private function addDocument(
        int $contentTypeId,
        string $collaboratorFieldCode,
        string $collaboratorFieldValue,
        string $fileTypeValue,
        string $safeFileName,
        string $base64EncodedFile,
    ): void
    {
       $body = [
               'ContentTypeID' => $contentTypeId,
               'ObjectId' => uniqid((string) time(), true),
               'IpAddress' => gethostbyname(gethostname()),
               'IsLastVersion' => false,
               'IsDigitallySigned' => false,
               'Fields' => [
                   [
                       'Code' => $collaboratorFieldCode,
                       'Value' => $collaboratorFieldValue
                   ],
                   [
                       'Code' => 'type_de_document',
                       'Value' => $fileTypeValue,
                   ],     
                   [
                       'Code' => 'isSelected',
                       'Value' => 0
                   ],
                   [
                       'Code' => 'isMatchingExpectation',
                       'Value' => 0
                   ],
                   [
                       'Code' => 'isDF',
                       'Value' => ''
                   ],
                   [
                       'Code' => 'isRH',
                       'Value' => ''
                   ],
                   [
                       'Code' => 'isDRH',
                       'Value' => ''
                   ],
                   [
                       'Code' => 'grp4_etat',
                       'Value' => 'A traiter (RH)'
                   ],
                   [
                       'Code' => 'date',
                       'Value' => (new \DateTimeImmutable())->format('d.m.Y')
                   ]
               ],
               'Attachment' => [
                   'FileName' => $safeFileName,
                   'Base64File' => $base64EncodedFile
               ]
           ];

       $r = $this->httpClient->request('POST', 'document/save', [
           'json' => $body,
       ])->getContent(false);
       dd($r);
    }

    private function getCollaboratorFolderContentTypeId(): int
    {
        $response = $this->httpClient->request('GET', 'content-type/list');

        $collaboratorFolderId = null;
        foreach ($response->toArray() as $contentType) {
            if (self::SEARCHED_CONTENT_TYPE === $contentType['text']) {
                $collaboratorFolderId = (int) $contentType['id'];
                break;
            }
        }

        if (!$collaboratorFolderId) {
            throw new ContentTypeNotFoundException();
        }

        return $collaboratorFolderId;
    }

    private function getCollaboratorFieldCode(
        array $structureData,
        int $collaboratorFolderId
    ): string
    {
        $collaboratorFieldCode = null;
        foreach ($structureData['Fields'] as $field) {
            if (self::SEARCHED_FIELD === $field['Title']) {
                $collaboratorFieldCode = $field['Code'];
                break;
            }
        }

        if (!$collaboratorFieldCode) {
            throw new FieldCodeNotFoundException($collaboratorFolderId);
        }

        return $collaboratorFieldCode;
    }
}
