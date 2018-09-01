<?php

namespace App\Repository;

use App\Entity\Timeclock;
use App\Service\HumanityApi;

class TimeclockRepository
{
    /**
     * @var HumanityApi
     */
    private $api;

    /**
     * @param HumanityApi $api
     */
    public function __construct(HumanityApi $api)
    {
        $this->api = $api;
    }

    /**
     * @param \DateTime $date
     * @return array|Timeclock[]
     * @throws \App\Exception\BadApiResponseException
     * @throws \App\Exception\FailedAuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTimeclocksForDate(\DateTime $date)
    {
        $data = $this->api->getJsonResponse('GET', '/timeclocks', [
            'query' => [
                'start_date' => $date->format('Y-m-d'),
                'end_date' => $date->format('Y-m-d'),
            ],
        ]);

        // TODO: Error handling for different status codes?
        if($data['status'] !== 1) {
            return [];
        }

        if(empty($data['data'])) {
            return [];
        }

        $timeclocks = [];

        foreach($data['data'] as $timeclockArray) {

            // Get id
            $id = $timeclockArray['id'];

            // Get schedule id
            $scheduleId = $timeclockArray['schedule']['id'];

            // Get employee
            $employee = $timeclockArray['employee'];

            // Get in time
            $inTime = $timeclockArray['in_time']['time'];

            // Get out time (can be empty?)
            $outTime = $timeclockArray['out_time']['time'] ?? '';

            // Get shift id (if exists)
            $shiftId = $timeclockArray['shift'] ?? null;

            $timeclocks[] = new Timeclock($id, $scheduleId, $inTime, $outTime, $employee, $shiftId);
        }

        return $timeclocks;
    }
}