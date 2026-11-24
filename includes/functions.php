<?php
// Helper functions used by rooms.php, booking.php and my_bookings.php
date_default_timezone_set('Asia/Colombo');

const DISCOUNT_MIN_NIGHTS = 3;     // 3 or more nights...
const DISCOUNT_RATE       = 0.10;  // ...get 10% off

// Check that a string is a real date in YYYY-MM-DD format
function valid_date($d) {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

// Number of nights between two dates
function count_nights($check_in, $check_out) {
    return (int)(new DateTime($check_in))->diff(new DateTime($check_out))->days;
}

// Total price calculated on the SERVER (with discount for long stays)
function calc_total($nights, $price_per_night) {
    $total = $nights * $price_per_night;
    if ($nights >= DISCOUNT_MIN_NIGHTS) {
        $total = $total * (1 - DISCOUNT_RATE);
    }
    return round($total, 2);
}

// Room is available if NO confirmed booking overlaps the requested dates
function is_room_available($pdo, $room_id, $check_in, $check_out) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM bookings
         WHERE room_id = ? AND booking_status = 'Confirmed'
           AND check_in < ? AND check_out > ?"
    );
    $stmt->execute([$room_id, $check_out, $check_in]);
    return $stmt->fetchColumn() == 0;
}
