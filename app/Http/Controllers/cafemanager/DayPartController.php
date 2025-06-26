<?php

namespace App\Http\Controllers\CafeManager;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CafeManager\CafeService;
use App\Exception\CafemanagerApiException;
use App\Http\Requests\CafeManager\DayPartAddRequest;

class DayPartController extends Controller
{
    protected CafeService $cafeService;

    /**
     * Inject the CafeService using constructor.
     */
    public function __construct(CafeService $cafeService)
    {
        $this->cafeService = $cafeService;
    }

    /**
     * Get all custom day parts for the cafe.
     */
    public function index(Request $request,$cafeId)
    {
        try {
            $cafeId = $request->input('cafe_id', auth()->user()->cafe_id ?? $cafeId);
            $dayParts = $this->cafeService->getCustomDayParts($cafeId);
            return sendResponse(true, '', $dayParts);
        } catch (Exception $e) {
            $error_msg = 'Error caught on line '.$e->getLine().': '.$e->getMessage();
            throw new CafemanagerApiException($error_msg);
        }
    }

    /**
     * Store or update a day part.
     */
    public function store(DayPartAddRequest $request)
    {
        try {
            $data = $request->validated();
            $created = $this->cafeService->addUpdateDaypart($data);

            if ($created) {
                return sendResponse(true, __('general.day_part.create'), $created, config('constants.HTTP_CREATED'));
            }

            return sendResponse(true, __('general.day_part.update'));
        } catch (Exception $e) {
            $error_msg = 'Error caught on line '.$e->getLine().': '.$e->getMessage();
            throw new CafemanagerApiException($error_msg);
        }
    }

    /**
     * Get a single day part record for a given cafe.
     */
    public function edit(Request $request, int $cafeId)
    {
        try {
            $dayPart = $this->cafeService->getCustomDayPart($cafeId);

            if ($dayPart) {
                return sendResponse(true, '', $dayPart);
            }

            return sendResponse(false, __('general.day_part.not_found'), '', config('constants.HTTP_NOT_FOUND'));
        } catch (Exception $e) {
            $error_msg = 'Error caught on line '.$e->getLine().': '.$e->getMessage();
            throw new CafemanagerApiException($error_msg);
        }
    }

    /**
     * Check if a day part is in use before deletion.
     */
    public function destroy(int $cafeId, Request $request)
    {
        try {
            $mealTypeId = $request->input('meal_type_id');
            $inUse = $this->cafeService->daypartInUse($mealTypeId, $cafeId);
            if ($inUse) {
                return sendResponse(false, __('general.day_part.day_part_in_use'), '', config('constants.HTTP_BAD_REQUEST'));
            }

            return sendResponse(true, __('general.day_part.deletable'));
        } catch (Exception $e) {
            $error_msg = 'Error caught on line '.$e->getLine().': '.$e->getMessage();
            throw new CafemanagerApiException($error_msg);
        }
    }
}
