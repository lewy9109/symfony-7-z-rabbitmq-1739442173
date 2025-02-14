<?php

namespace App\Controller;

use App\Message\ImportCsvMessage;
use App\Service\Uploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomePageController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private readonly Uploader $uploader
    )
    {
    }

    #[Route('/', name: 'app_home_page')]
    public function index(Request $request): Response
    {
        return $this->render('home_page/index.html.twig', [
            'controller_name' => 'HomePageController',
        ]);
    }

    #[Route('/csv/upload', name: 'csv_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('chunk');
        $fileName = $request->request->get('fileName');
        $chunkIndex = (int) $request->request->get('chunkIndex');
        $totalChunks = (int) $request->request->get('totalChunks');

        if (!$file || !$fileName || $chunkIndex === null || $totalChunks === null) {
            return new JsonResponse(['error' => 'Brak wymaganych danych'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->uploader->saveChunk($file, $fileName, $chunkIndex, $totalChunks);

        if ($result['status'] === 'completed') {
            return new JsonResponse([
                'message' => 'Plik scalony',
                'file' => $result['file']
            ], Response::HTTP_OK);
        }

        return new JsonResponse(['message' => 'Fragment zapisany'], Response::HTTP_OK);
    }
}
