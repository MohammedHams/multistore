<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreStaff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StoreStaffFactory extends Factory
{
    protected $model = StoreStaff::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'store_id' => Store::factory(),
        ];
    }
}
