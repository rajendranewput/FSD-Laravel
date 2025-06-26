<?php

namespace Tests\Feature\CafeManager;

use Tests\TestCase;
use Mockery;
use App\Services\CafeManager\CafeService;

class DayPartControllerTest extends TestCase
{
    protected $cafeServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cafeServiceMock = Mockery::mock(CafeService::class);
        $this->app->instance(CafeService::class, $this->cafeServiceMock);
    }

    public function it_returns_day_parts_from_index()
    {
        $cafeId = 1234;
        $expectedData = [['meal_type' => 'Breakfast', 'abbreviation' => 'B']];

        $this->cafeServiceMock
            ->shouldReceive('getCustomDayParts')
            ->once()
            ->with($cafeId)
            ->andReturn($expectedData);

        $response = $this->getJson("/cafemanager/cafes/{$cafeId}/day-parts");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'payload' => $expectedData,
                 ]);
    }

    public function it_returns_single_day_part_on_edit()
    {
        $cafeId = 1234;
        $expected = ['meal_type' => 'Lunch', 'abbreviation' => 'L'];

        $this->cafeServiceMock
            ->shouldReceive('getCustomDayPart')
            ->once()
            ->with($cafeId)
            ->andReturn($expected);

        $response = $this->getJson("/cafemanager/cafes/{$cafeId}/day-part/edit");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'payload' => $expected,
                 ]);
    }

    public function it_checks_if_day_part_is_in_use_before_delete()
    {
        $cafeId = 1234;
        $mealTypeId = 1;

        $this->cafeServiceMock
            ->shouldReceive('daypartInUse')
            ->once()
            ->with($mealTypeId, $cafeId)
            ->andReturn(true);

        $response = $this->deleteJson("/cafemanager/cafes/{$cafeId}/day-part/delete", [
            'meal_type_id' => $mealTypeId,
        ]);

        $response->assertStatus(400)
                 ->assertJson([
                     'success' => false,
                 ]);
    }

    public function it_can_store_day_part()
    {
        $data = [
            'meal_type' => 'Dinner',
            'abbreviation' => 'D',
            'cafe_id' => 1234,
        ];

        $this->cafeServiceMock
            ->shouldReceive('addUpdateDaypart')
            ->once()
            ->with($data)
            ->andReturn(true);

        $response = $this->postJson("/cafemanager/cafes/{$data['cafe_id']}/day-part", $data);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => __('general.day_part.create'),
                 ]);
    }

    public function it_returns_update_message_when_day_part_exists()
    {
        $data = [
            'meal_type' => 'Dinner',
            'abbreviation' => 'D',
            'cafe_id' => 1234,
        ];

        $this->cafeServiceMock
            ->shouldReceive('addUpdateDaypart')
            ->once()
            ->with($data)
            ->andReturn(false);

        $response = $this->postJson("/cafemanager/cafes/{$data['cafe_id']}/day-part", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => __('general.day_part.update'),
                 ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
