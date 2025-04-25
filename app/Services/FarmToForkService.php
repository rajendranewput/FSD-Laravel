<?php

namespace App\Services;

use App\Repositories\FarmToForkRepository;

class FarmToForkService
{
    protected $repository;

    public function __construct(FarmToForkRepository $repository)
    {
        $this->repository = $repository;
    }

    public function calculateFarmToForkData($costCenters, $expOne, $expTwo, $endDate, $campusFlag, $type, $team)
    {
        $firstItems = $this->repository->getFarmToForkAccountData($costCenters, $expOne, $endDate, $campusFlag, $type, $team);
        $secondItems = $this->repository->getFarmToForkAccountData($costCenters, $expTwo, $endDate, $campusFlag, $type, $team);

        $f2f = [];
        foreach ($firstItems as $firstItem) {
            $accountId = $firstItem['account_id'];
            $firstSpend = $firstItem['amount'] ?? 0;
            $secondSpend = $secondItems[$accountId]['amount'] ?? 0;

            $spend = $secondSpend > 0 ? round(abs($firstSpend / $secondSpend * 100), 1) : 0;
            $f2f[$accountId][] = $spend;
        }

        return $f2f;
    }
}
