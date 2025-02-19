<?php

namespace App\Controller;

use App\Service\Raport\RaportDto;
use App\Service\RedisStorage\ReportStorage;
use App\Service\RedisStorage\UserStorage;
use App\Service\Uploader;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;


class HomePageController extends AbstractController
{
    public function __construct(
        private readonly Uploader $uploader,
        private readonly UserStorage $userStorage,
        private readonly ReportStorage $reportStorage
    ) {}

    #[Route("/", name: "app_home_page")]
    public function index(Request $request): Response
    {
        return $this->render("home_page/index.html.twig", [
            "controller_name" => "HomePageController",
        ]);
    }

    #[Route("/csv/upload", name: "csv_upload", methods: ["POST"])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get("chunk");
        if (!$file instanceof UploadedFile) {
            return new JsonResponse(["error" => "Invalid file"], Response::HTTP_BAD_REQUEST);
        }
        $fileName = $request->request->get("fileName");
        if (!is_string($fileName) || empty($fileName)) {
            return new JsonResponse(["error" => "Invalid file name"], Response::HTTP_BAD_REQUEST);
        }
        $chunkIndex = $request->request->get("chunkIndex");
        if(!is_numeric($chunkIndex)) {
            return new JsonResponse(["error" => "Nieprawidłowy numer fragmentu"], Response::HTTP_BAD_REQUEST);
        }
        $totalChunks = $request->request->get("totalChunks");
        if(!is_numeric($totalChunks)) {
            return new JsonResponse(["error" => "Nieprawidłowy numer fragmentu"], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->uploader->saveChunk(
            $file,
            $fileName,
            (int)$chunkIndex,
            (int)$totalChunks
        );

        if ($result["status"] === "completed") {

            return new JsonResponse(
                [
                    "message" => "file merged",
                    "file" => $result["file"],
                ],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            ["message" => "Chunk saved"],
            Response::HTTP_OK
        );
    }

    #[Route('/users', name: 'user_list')]
    public function listUsers(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $perPage = 10;

        $pagination = $this->userStorage->getAllUsersPaginated($page, $perPage);

        return $this->render('user/list.html.twig', [
            'users' => $pagination['users'],
            'totalPages' => $pagination['totalPages'],
            'currentPage' => $pagination['currentPage'],
        ]);
    }

    #[Route('/report/latest', name: 'latest_report')]
    public function latestReport(): Response
    {
        try {
            $report = $this->reportStorage->getLastReport();
            $decodedErrors = [];

            foreach ($report->getErrors() as $errorGroup) {
                if (is_string($errorGroup)) {
                    $errorGroup = json_decode($errorGroup, true);
                }

                if (is_array($errorGroup)) {
                    foreach ($errorGroup as $error) {
                        $decodedErrors[] = $error;
                    }
                }
            }
        } catch (\Exception $e) {
            return $this->render('report/latest.html.twig', [
                'error' => $e->getMessage(),
                'report' => null,
            ]);
        }

        return $this->render('report/latest.html.twig', [
            'report' => $report,
            'error' => null,
            'raportErrors' => $decodedErrors
        ]);
    }

}
