param([string]$BaseUrl = 'http://127.0.0.1:8097')
$ErrorActionPreference = 'Stop'
if ($BaseUrl -ne 'http://127.0.0.1:8097') { throw 'Use the isolated verification server on port 8097.' }
$passed = 0
function Assert-Result([bool]$Condition, [string]$Label) {
    if (!$Condition) { throw "FAIL: $Label" }
    $script:passed++
    Write-Output "PASS: $Label"
}
function Get-Token($Response) {
    $match = [regex]::Match($Response.Content, '<input\b[^>]*name="csrf_token"[^>]*value="([^"]+)"', 'Singleline')
    if (!$match.Success) { throw 'Missing CSRF token.' }
    return $match.Groups[1].Value
}
$student = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$admin = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$anon = [Microsoft.PowerShell.Commands.WebRequestSession]::new()
$registerUrl = "$BaseUrl/auth/register/register.php"
$loginUrl = "$BaseUrl/auth/login/login.php"
$dashboardUrl = "$BaseUrl/dashboard/dashboard.php"
$adminUrl = "$BaseUrl/admindashboard/admindashboard.php"
$r = Invoke-WebRequest "$dashboardUrl" -WebSession $anon
Assert-Result ($r.Content.Contains('id="loginForm"')) 'Student page requires login'
$r = Invoke-WebRequest $adminUrl -WebSession $anon
Assert-Result ($r.Content.Contains('id="loginForm"')) 'Admin page requires login'
$r = Invoke-WebRequest $registerUrl -WebSession $student
$body = @{csrf_token=(Get-Token $r); studentid='2000000099'; firstname='<script>alert(1)</script>'; lastname="O'Neil"; course='BSIS'; yearlevel='2nd Year'; password='HttpStudent!2026'; confirm_password='HttpStudent!2026'}
$badBody = $body.Clone(); $badBody.csrf_token='wrong'
$r = Invoke-WebRequest $registerUrl -WebSession $student -Method Post -Body $badBody
Assert-Result ($r.Content.Contains('Your session expired')) 'Registration rejects invalid CSRF'
$badBody = $body.Clone(); $badBody.studentid='0'
$r = Invoke-WebRequest $registerUrl -WebSession $student -Method Post -Body $badBody
Assert-Result ($r.Content.Contains('Student ID must be')) 'Registration rejects zero ID'
$r = Invoke-WebRequest $registerUrl -WebSession $student -Method Post -Body $body
Assert-Result ($r.Content.Contains('Account created successfully')) 'Registration HTTP flow'
$token = Get-Token $r
$r = Invoke-WebRequest $loginUrl -WebSession $student -Method Post -Body @{csrf_token=$token;studentid='2000000099';password='wrong'}
Assert-Result ($r.Content.Contains('Invalid Student ID or password')) 'Invalid login rejected'
$oldSession = $student.Cookies.GetCookies($BaseUrl)['PHPSESSID'].Value
$r = Invoke-WebRequest $loginUrl -WebSession $student -Method Post -Body @{csrf_token=(Get-Token $r);studentid='2000000099';password='HttpStudent!2026'}
Assert-Result ($r.Content.Contains('id="lab-panel-4"')) 'Valid student login and ComLab 4 render'
Assert-Result ($student.Cookies.GetCookies($BaseUrl)['PHPSESSID'].Value -ne $oldSession) 'Successful login rotates session ID'
Assert-Result ($r.Content.Contains('&lt;script&gt;alert(1)&lt;/script&gt;') -and !$r.Content.Contains('<script>alert(1)</script>')) 'Stored HTML is escaped'
$r = Invoke-WebRequest $adminUrl -WebSession $student
Assert-Result ($r.Content.Contains('id="lab-panel-4"') -and !$r.Content.Contains('<title>Admin Dashboard')) 'Student cannot access admin dashboard'
$r = Invoke-WebRequest $dashboardUrl -WebSession $student
$token = Get-Token $r
$r = Invoke-WebRequest $dashboardUrl -WebSession $student -Method Post -Body @{csrf_token='wrong';monitoring_action='time_in';computer_id='136'}
Assert-Result ($r.Content.Contains('Invalid security token')) 'Time In rejects invalid CSRF'
$r = Invoke-WebRequest $dashboardUrl -WebSession $student -Method Post -Body @{csrf_token=$token;monitoring_action='time_in';computer_id='136'}
Assert-Result ($r.Content.Contains('monitor-active')) 'Student Time In HTTP flow'
$r = Invoke-WebRequest $loginUrl -WebSession $admin
$r = Invoke-WebRequest $loginUrl -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token $r);studentid='admin';password='RubricAdmin!2026'}
Assert-Result ($r.Content.Contains('<title>Admin Dashboard')) 'Admin login HTTP flow'
$token = Get-Token $r
$create = @{csrf_token='wrong';action='create';studentid='2000000098';firstname='Admin';lastname='Fixture';course='ACT';yearlevel='1st Year';password='HttpAdminFixture!2026'}
$r = Invoke-WebRequest $adminUrl -WebSession $admin -Method Post -Body $create
Assert-Result ($r.Content.Contains('Your session expired')) 'Admin create rejects invalid CSRF'
$create.csrf_token=$token
$r = Invoke-WebRequest $adminUrl -WebSession $admin -Method Post -Body $create
Assert-Result ($r.Content.Contains('Account 2000000098 has been created')) 'Admin create HTTP flow'
$r = Invoke-WebRequest "$BaseUrl/admindashboard/editaccount.php?id=2000000098" -WebSession $admin
$r = Invoke-WebRequest "$BaseUrl/admindashboard/editaccount.php?id=2000000098" -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token $r);studentid='2000000098';firstname='Edited';lastname='Fixture';course='BSIS';yearlevel='4th Year';new_password=''}
Assert-Result ($r.Content.Contains('Account 2000000098 has been updated') -and $r.Content.Contains('Edited Fixture')) 'Admin edit HTTP flow'
$r = Invoke-WebRequest $adminUrl -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token $r);action='delete';studentid='2000000099'}
Assert-Result ($r.Content.Contains('Time out this student before deleting')) 'Admin deletion protects active session'
$sessionMatch = [regex]::Match($r.Content, 'name="session_id" value="(\d+)"')
if (!$sessionMatch.Success) { throw 'Missing admin Time Out form.' }
$r = Invoke-WebRequest $adminUrl -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token $r);action='end_session';session_id=$sessionMatch.Groups[1].Value}
Assert-Result ($r.Content.Contains('Session ended. The computer is available again.')) 'Admin Time Out HTTP flow'
$r = Invoke-WebRequest $adminUrl -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token $r);action='delete';studentid='2000000099'}
Assert-Result ($r.Content.Contains('Account 2000000099 has been deleted') -and $r.Content.Contains('Deleted account')) 'Admin delete preserves anonymous history on screen'
$r = Invoke-WebRequest $dashboardUrl -WebSession $student
Assert-Result ($r.Content.Contains('id="loginForm"')) 'Deleted account loses dashboard access'
$r = Invoke-WebRequest $adminUrl -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token (Invoke-WebRequest $adminUrl -WebSession $admin));action='delete';studentid='2000000098'}
Assert-Result ($r.Content.Contains('Account 2000000098 has been deleted')) 'Admin fixture deleted'
$r = Invoke-WebRequest "$BaseUrl/auth/logout/logout.php" -WebSession $admin -Method Post -Body @{csrf_token=(Get-Token $r)}
$r = Invoke-WebRequest $adminUrl -WebSession $admin
Assert-Result ($r.Content.Contains('id="loginForm"')) 'Logout removes administrator access'
$r = Invoke-WebRequest $registerUrl -WebSession $anon -Method Post -Body 'csrf_token[]=bad&studentid[]=bad&firstname[]=bad'
Assert-Result ($r.StatusCode -eq 200 -and !$r.Content.Contains('Fatal error') -and !$r.Content.Contains('Warning:')) 'Malformed array inputs handled without PHP errors'
Write-Output "SUCCESS: $passed HTTP checks passed."
