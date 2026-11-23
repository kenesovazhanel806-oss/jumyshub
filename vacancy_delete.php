<?php
require __DIR__ . '/includes/init.php';

// Delete — тек POST сұранысымен және тек вакансия иесі жоя алады
$user = require_role('employer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('employer.php');
}
verify_csrf();

$id = int_param($_POST, 'id');
$vacancy = $id !== null ? find_vacancy($id) : null;

if (!$vacancy || (int) $vacancy['owner_id'] !== (int) $user['id']) {
    set_flash('error', 'Вакансия табылмады немесе оны жоюға рұқсатыңыз жоқ.');
    redirect('employer.php');
}

// Prepared statement: id мәні SQL сұранысына тікелей қосылмайды
$stmt = db()->prepare('DELETE FROM vacancies WHERE id = ?');
$stmt->execute([$vacancy['id']]);

set_flash('success', '«' . $vacancy['title'] . '» вакансиясы жойылды.');
redirect('employer.php');
