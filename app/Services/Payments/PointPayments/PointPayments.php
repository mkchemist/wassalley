<?php

namespace App\Services\Payments\PointPayments;

use App\User;
use Illuminate\Support\Facades\DB;

class PointPayments
{

  /**
   * Check if user have sufficient points to pay order amount
   *
   * @param float $point [current total user points]
   * @param float $rate [current point per currency value from business settings]
   * @param float $amount [total order amount]
   * @return bool
   */
  static public function hasSufficientPoints(float $points, float $rate, float $amount):bool
  {
    $totalPointsValue = $points / $rate;

    return $totalPointsValue >= $amount;
  }

  /**
   * Get equal points for the given order amount
   *
   * @param float $amount [order amount]
   * @param float $rate [current point per currency value from business settings]
   * @return float
   */
  static public function getOrderAmountEqualPoints(float $amount, float $rate):float
  {
    return $amount * $rate;
  }


  static public function handlePointWithdrawal(User $user, float $amount, float $rate, int $order)
  {
    $totalOrderAmountPoints = self::getOrderAmountEqualPoints($amount, $rate);

    $user->point = $user->point - $totalOrderAmountPoints;

    $user->save();

    DB::table('point_transitions')->insert([
      'user_id' => $user->id,
      'description' => "payment for order #$order",
      'type' => 'point_out',
      'amount' => $totalOrderAmountPoints,
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }
}
