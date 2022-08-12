<?php
namespace App\Controller;

use App\Entity\Report;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @throws Exception
     * @Route("/", name="homepage")
     * @Route("/add", name="editpage")
     */
    public function index(): Response
    {

        return $this->render('index.html.twig');
    }

    /**
     * @param ReportRepository $reportRepository
     * @return JsonResponse
     * @Route ("/reports")
     */
    public function getReports(ReportRepository $reportRepository): JsonResponse
    {
        $reports = [];

        $qb = $reportRepository->createQueryBuilder('r')
            ->join('r.user', 'u')
            ->orderBy('u.sort', 'ASC');

        $reportsQuery = $qb->getQuery()->execute();

        foreach ($reportsQuery as $report)
        {
            if ($report->getIsPublished()) {
                $prevReport = $reportRepository->findOneBy([
                    'week' => $report->getWeek() - 1,
                    'user' => $report->getUser()
                ]);

                $reports[$report->getWeek()][] = [
                    'user_id'   => $report->getUser()->getId(),
                    'team_id'   => $report->getUser()->getTeamId(),
                    'rate'      => $report->getRate(),
                    'name'      => $report->getUser()->getName(),
                    'plan'      => $prevReport ? $prevReport->getNextWeek() : '',
                    'result'    => $report->getResult(),
                    'next_week' => $report->getNextWeek()
                ];
            }
        }

        return new JsonResponse($reports);
    }

    /**
     * @param Request $request
     * @param UserRepository $userRepository
     * @param ReportRepository $reportRepository
     * @return JsonResponse
     * @Route ("/report")
     */
    public function getReportByUser(Request $request, UserRepository $userRepository, ReportRepository $reportRepository): JsonResponse
    {
        $key = $request->query->get('key');
        $user = $userRepository->findOneBy(['password' => $key]);

        if (!$user) {
            return new JsonResponse();
        }

        $report = $reportRepository->findOneBy([
            'user' => $user,
            'week' => date('W')
        ]);

        $reportData = [
            'id'            => null,
            'user_id'       => $user->getId(),
            'rate'          => 0,
            'result'        => '',
            'next_week'     => '',
            'is_published'  => false,
            'published_at'  => null
        ];

        if ($report) {
            $reportData = [
                'id'            => $report->getId(),
                'user_id'       => $user->getId(),
                'rate'          => $report->getRate(),
                'result'        => $report->getResult(),
                'next_week'     => $report->getNextWeek(),
                'is_published'  => $report->getIsPublished(),
                'published_at'  => $report->getPublishedAt()
            ];
        }

        return new JsonResponse($reportData);
    }

    /**
     * @Route ("/saveReport", methods={"POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function saveReport(Request $request, UserRepository $userRepository): JsonResponse
    {
        $currentDate = new DateTimeImmutable();
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);

        if ($data['id']) {
            $report = $entityManager->getRepository(Report::class)->find($data['id']);

            if ($report && $report->getPublishedAt()) {
                $publishedAt = $report->getPublishedAt();

                if ($publishedAt->add(new DateInterval('PT1H')) < $currentDate) {
                    return new JsonResponse(['success' => false, 'error' => 'Опубликованный отчёт нельзя редактировать по истечению часа.']);
                }
            }
        } else {
            $report = new Report();
            $report->setUser($userRepository->find($data['user_id']));

            $entityManager->persist($report);
        }

        $report
            ->setRate($data['rate'])
            ->setWeek(date('W'))
            ->setResult($data['result'])
            ->setNextWeek($data['next_week'])
            ->setIsPublished($data['is_published']);

        if ($data['is_published'] && !$report->getPublishedAt()) {
            $report->setPublishedAt($currentDate);
        } elseif (!$data['is_published']) {
            $report->setPublishedAt(null);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route ("/averageRates")
     */
    public function getAverageRates(): JsonResponse
    {
        $teamId = 1;
        $rates = [];
        $current = null;

        for ($i = date('W') - 7; $i <= date('W'); $i++) {
            $rate = $this->getDoctrine()
                ->getRepository(Report::class)
                ->findAverageRateForWeek($teamId, $i);

            $rates[] = $rate ?? 0;

            if ($i == date('W')) {
                $current = $rate ?? 0;
            }
        }

        return new JsonResponse([
            'rates' => $rates,
            'current' => $current
        ]);
    }

    /**
     * @Route ("/usersRates/{userId}/{week}")
     */
    public function getUsersRates($userId, $week): JsonResponse
    {
        $rates = [];

        for ($i = $week - 7; $i <= $week; $i++) {
            $rate = $this->getDoctrine()
                ->getRepository(Report::class)
                ->findUserRateForWeek($userId, $i);

            $rates[] = empty($rate) ? 0 : $rate[0]['rate'];
        }

        return new JsonResponse($rates);
    }
}