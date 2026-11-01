<?php
declare(strict_types=1);

// Run against an isolated restore, never against the user's working database.
if (PHP_SAPI !== 'cli' || getenv('DB_NAME') !== 'techpulse_rubric_verify') {
    exit("Set DB_NAME=techpulse_rubric_verify and use the PHP CLI.\n");
}
require __DIR__ . '/../config/techpulse.php';
require __DIR__ . '/../auth/register/registerdb.php';
require __DIR__ . '/../auth/login/logindb.php';
require __DIR__ . '/../dashboard/dashboarddb.php';
require __DIR__ . '/../admindashboard/admindashboarddb.php';
require __DIR__ . '/../config/monitoring.php';

$passed = 0;
function check(bool $condition, string $name): void
{
    global $passed;
    if (!$condition) {
        throw new RuntimeException('FAIL: ' . $name);
    }
    $passed++;
    echo "PASS: $name\n";
}
function rejected(callable $operation, string $name): void
{
    $caught = false;
    try {
        $operation();
    } catch (RuntimeException $e) {
        $caught = true;
    }
    check($caught, $name);
}

$ids = ['2000000001', '2000000002', '2000000003'];
$maxSession = (int) $conn->query('SELECT COALESCE(MAX(session_id),0) AS n FROM computer_sessions')->fetch_assoc()['n'];
$pcRows = $conn->query("SELECT computer_id,status FROM computers WHERE lab_id=4 ORDER BY pc_number LIMIT 2")->fetch_all(MYSQLI_ASSOC);
$pc1 = (int) $pcRows[0]['computer_id'];
$pc2 = (int) $pcRows[1]['computer_id'];
$password = 'RubricStudent!2026';
$createdIds = [];
try {
    check(count(monitoringLaboratories($conn)) === 4 && count(monitoringComputers($conn)) === 180, 'Four labs and 180 computers');
    foreach (['0','-1','01','2147483648','1 OR 1=1',str_repeat('9',30)] as $invalid) {
        check(!validStudentId($invalid), 'Reject invalid ID: ' . $invalid);
    }
    check(validStudentId('2147483647'), 'Accept maximum signed integer ID');
    check(inputString(['studentid' => ['1']], 'studentid') === '', 'Reject array input safely');
    foreach ($ids as $id) {
        check(registerUser($conn, $id, "O'Neil", 'Demo', 'BSIS', '2nd Year', $password)['success'], 'Create synthetic account ' . $id);
        $createdIds[] = $id;
    }
    check(!registerUser($conn, $ids[0], 'Duplicate', 'Demo', 'BSIS', '2nd Year', $password)['success'], 'Duplicate ID returns validation error');
    check(!registerUser($conn, '2000000010', ' ', 'Demo', 'BSIS', '2nd Year', $password)['success'], 'Blank name rejected');
    check(!registerUser($conn, '2000000010', 'Demo', 'User', 'INVALID', '2nd Year', $password)['success'], 'Unknown course rejected');
    check(!registerUser($conn, '2000000010', 'Demo', 'User', 'BSIS', '9th Year', $password)['success'], 'Unknown year rejected');
    check(!registerUser($conn, '2000000010', 'Demo', 'User', 'BSIS', '2nd Year', str_repeat('a',73))['success'], 'Overlong bcrypt password rejected');
    $user = findUserByStudentId($conn, $ids[0]);
    check($user['firstname'] === "O'Neil", 'Prepared insert preserves apostrophe');
    check(password_verify($password, $user['password']) && !password_verify('wrong', $user['password']), 'Correct and incorrect password verification');
    check(!array_key_exists('password', findUserProfile($conn, $ids[0])), 'Profile read excludes password hash');
    check(adminUpdateUser($conn, $ids[0], 'Updated', 'Demo', 'ACT', '3rd Year')['success'], 'Update account with unchanged password');
    check(password_verify($password, findUserByStudentId($conn, $ids[0])['password']), 'Blank update password preserves hash');
    check(adminUpdateUser($conn, $ids[0], 'Updated', 'Demo', 'ACT', '3rd Year', 'ChangedPassword!2026')['success'], 'Update password');
    check(password_verify('ChangedPassword!2026', findUserByStudentId($conn, $ids[0])['password']), 'Updated password verifies');
    check(!adminUpdateUser($conn, '1999999999', 'Missing', 'User', 'ACT', '3rd Year')['success'], 'Missing update target reports failure');
    $conn->query("UPDATE computers SET status='available' WHERE computer_id IN ($pc1,$pc2)");
    monitoringTimeIn($conn, (int) $ids[0], $pc1);
    check(monitoringActive($conn, (int) $ids[0]) !== null, 'Time In creates active session');
    rejected(fn() => monitoringTimeIn($conn, (int) $ids[0], $pc2), 'One active computer per student');
    rejected(fn() => monitoringTimeIn($conn, (int) $ids[1], $pc1), 'One active student per computer');
    check(!deleteUser($conn, $ids[0])['success'], 'Active student deletion refused');
    monitoringTimeOut($conn, (int) $ids[0]);
    check(monitoringActive($conn, (int) $ids[0]) === null, 'Time Out releases computer');
    rejected(fn() => monitoringTimeOut($conn, (int) $ids[0]), 'Repeated Time Out rejected');
    $historyId = (int) $conn->query("SELECT session_id FROM computer_sessions WHERE StudentID={$ids[0]} ORDER BY session_id DESC LIMIT 1")->fetch_assoc()['session_id'];
    check(deleteUser($conn, $ids[0])['success'], 'Delete student with completed history');
    check(findUserProfile($conn, $ids[0]) === null, 'Deleted student cannot be read');
    $history = $conn->query("SELECT StudentID,time_out FROM computer_sessions WHERE session_id=$historyId")->fetch_assoc();
    check($history !== null && $history['StudentID'] === null && $history['time_out'] !== null, 'Completed usage history preserved anonymously');
    check(!deleteUser($conn, $ids[0])['success'], 'Repeated delete reports missing account');
    $conn->query("UPDATE computers SET status='maintenance' WHERE computer_id=$pc2");
    rejected(fn() => monitoringTimeIn($conn, (int) $ids[1], $pc2), 'Maintenance PC cannot be occupied');
    $conn->query("UPDATE computers SET status='available' WHERE computer_id=$pc2");
    monitoringTimeIn($conn, (int) $ids[1], $pc2);
    $active = monitoringActive($conn, (int) $ids[1]);
    monitoringAdminTimeOut($conn, (int) $active['session_id']);
    check(monitoringActive($conn, (int) $ids[1]) === null, 'Administrator can close abandoned session');
    rejected(fn() => monitoringAdminTimeOut($conn, (int) $active['session_id']), 'Administrator repeated close rejected');
    rejected(fn() => $conn->query("UPDATE users SET StudentID=0 WHERE StudentID={$ids[2]}"), 'Database CHECK rejects zero ID');
    rejected(fn() => $conn->query("UPDATE users SET course='MISSING' WHERE StudentID={$ids[2]}"), 'Database FK rejects unknown course');
    rejected(fn() => $conn->query("UPDATE users SET firstname='' WHERE StudentID={$ids[2]}"), 'Database CHECK rejects empty name');
    rejected(fn() => $conn->query("INSERT INTO computer_sessions (StudentID,computer_id,time_in,time_out) VALUES ({$ids[2]},$pc1,'2026-01-02','2026-01-01')"), 'Database CHECK rejects negative session duration');
    rejected(fn() => $conn->query("INSERT INTO computer_sessions (StudentID,computer_id) VALUES (NULL,$pc1)"), 'Database rejects anonymous active session');
    rejected(fn() => $conn->query("INSERT INTO computers (lab_id,pc_number) VALUES (4,46)"), 'Database CHECK rejects invalid PC number');
    rejected(fn() => $conn->query("INSERT INTO computers (lab_id,pc_number) VALUES (4,1)"), 'Database UNIQUE rejects duplicate lab PC');
    for ($i=20; $i<48; $i++) {
        $id = (string) (2000000000 + $i);
        check(registerUser($conn, $id, 'Page', 'Fixture', 'ACT', '1st Year', $password)['success'], 'Create pagination fixture ' . $i);
        $createdIds[] = $id;
    }
    $page1 = array_column(getAllUsers($conn, 1), 'StudentID');
    $page2 = array_column(getAllUsers($conn, 2), 'StudentID');
    check(count($page1) === 25 && count($page2) > 0 && !array_intersect($page1,$page2), 'Pagination has bounded nonoverlapping pages');
    echo "SUCCESS: $passed checks passed.\n";
} finally {
    $conn->query("DELETE FROM computer_sessions WHERE session_id>$maxSession");
    foreach ($createdIds as $id) {
        $q = $conn->prepare('DELETE FROM users WHERE StudentID=?');
        $q->bind_param('i',$id);
        $q->execute();
        $q->close();
    }
    foreach ($pcRows as $pc) {
        $q = $conn->prepare('UPDATE computers SET status=? WHERE computer_id=?');
        $q->bind_param('si',$pc['status'],$pc['computer_id']);
        $q->execute();
        $q->close();
    }
}
