<?php

namespace App\Util;

class MessageIndex
{
    public const VEHICLE_NOT_FOUND_ID = 'No vehicle found with that Id';
    public const VEHICLE_NOT_FOUND_NICKNAME = 'No vehicle found with that nickname';
    public const VEHICLE_DELETED = 'Vehicle deleted';
    public const VEHICLE_ALREADY_REGISTERED = 'Vehicle with the given license plate already registered';
    public const USER_NOT_FOUND = 'No user found with that email';
    public const USER_ALREADY_REGISTERED = 'User with the given email already registered';
    public const WRONG_CREDENTIALS = 'Wrong credentials given';
    public const STAY_NOT_FOUND = 'No stay found with that Id';
    public const STAY_ALREADY_ACTIVE = 'You already have an active stay in this parking';
    public const PAYMENT_COMPLETED = 'Payment completed';
    public const PAYMENT_FAILED = 'Payment failed';
    public const PARKING_NOT_FOUND = 'No parking found with that Id';
    public const SPOT_ALREADY_REGISTERED = 'Spot with the given code already registered for the given parking';
    public const NO_FREE_SPOTS = 'No free spots in the given parking';
    public const BOOKING_ALREADY_ACTIVE = 'You already have an active booking in this parking';

    private function __construct()
    {
        // empty for framework
    }
}
