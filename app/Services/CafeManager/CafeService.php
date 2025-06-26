<?php

namespace App\Services\CafeManager;

use App\Repositories\CafeManager\CafeRepository;
use ErrorException;
use Exception;

class CafeService
{
    protected $repo;

    public function __construct(CafeRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getCafe($cafeId)
    {
        try {
            return $this->repo->findCafe($cafeId);
        } catch (Exception $ex) {
            throw new ErrorException(handleExceptionError($ex));
        }
    }

    public function getCustomDayParts($cafeId)
    {
        try {
            return $this->repo->getCustomDayParts($cafeId);
        } catch (Exception $ex) {
            throw new ErrorException(handleExceptionError($ex));
        }
    }

    public function getCustomDayPart($cafeId)
    {
        try {
            return $this->repo->getCustomDayPart($cafeId);
        } catch (Exception $ex) {
            throw new ErrorException(handleExceptionError($ex));
        }
    }

    public function addUpdateDaypart(array $request)
    {
        try {
            $record = $this->repo->saveOrUpdateDayPart($request);
            return $record->wasRecentlyCreated;
        } catch (Exception $ex) {
            throw new ErrorException(handleExceptionError($ex));
        }
    }

    public function daypartInUse($mealTypeId, $cafeId)
    {
        try {
            return $this->repo->isDayPartInUse($mealTypeId, $cafeId);
        } catch (Exception $ex) {
            throw new ErrorException(handleExceptionError($ex));
        }
    }
}
