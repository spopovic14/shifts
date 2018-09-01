<?php

namespace App\Controller;

use App\Exception\BadApiResponseException;
use App\Exception\FailedAuthenticationException;
use App\Service\Shifts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @var Shifts
     */
    private $shiftsService;

    /**
     * @param Shifts $shiftsService
     */
    public function __construct(Shifts $shiftsService)
    {
        $this->shiftsService = $shiftsService;
    }

    /**
     * Display shifts for current day.
     *
     * @throws BadApiResponseException
     * @throws FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @Route("/")
     */
    public function index()
    {
        $data = $this->shiftsService->getShiftsWithTimeclocksForDate(new \DateTime());

        return $this->render('shifts_display.html.twig', [
            'shifts' => $data,
        ]);
    }

    /**
     * Display shifts for given day (Y-m-d).
     *
     * @param \DateTime $date
     * @return Response
     * @throws BadApiResponseException
     * @throws FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     *
     * @Route("/shifts/{date}")
     * @ParamConverter("date", options={"format": "Y-m-d"})
     */
    public function displayShiftsForDate(\DateTime $date)
    {
        $data = $this->shiftsService->getShiftsWithTimeclocksForDate($date);

        return $this->render('shifts_display.html.twig', [
            'shifts' => $data,
        ]);
    }
}