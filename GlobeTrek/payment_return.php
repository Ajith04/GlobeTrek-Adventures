<?php require_once 'config.php';
require_role('customer', 'login.php');
$id = (int) ($_GET['id'] ?? 0);
if (isset($_GET['cancel'])) {
    flash('warning', 'Payment was cancelled. You can try again from My Journeys.');
} else {
    flash('info', 'Payment processing has been submitted. Your booking status will update after gateway confirmation.');
}
go(app_url('bookings.php'));
