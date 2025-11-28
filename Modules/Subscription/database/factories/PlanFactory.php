<?php

namespace Modules\Subscription\database\factories;

use Modules\Subscription\src\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 0, 500),
            'duration_days' => 30,
        ];
    }
}
