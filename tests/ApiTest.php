<?php
use PHPUnit\Framework\TestCase;

/** Calls the real API over HTTP. Runs only when BASE_URL is set (GitHub Actions sets it). */
final class ApiTest extends TestCase
{
    private static string $base;
    private static string $jar;

    public static function setUpBeforeClass(): void
    {
        $base = getenv('BASE_URL');
        if (!$base) self::markTestSkipped('BASE_URL is not set - skipping API tests.');
        self::$base = rtrim($base, '/');
        self::$jar  = tempnam(sys_get_temp_dir(), 'bsjar');
    }

    public static function tearDownAfterClass(): void { if (isset(self::$jar)) @unlink(self::$jar); }

    /** Returns [httpCode, decodedJson|null, rawBody]. $form=true sends an HTML form instead of JSON. */
    private function call(string $method, string $path, ?array $body = null, bool $form = false, bool $withCookie = true): array
    {
        $ch = curl_init(self::$base . '/' . ltrim($path, '/'));
        $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 15];
        if ($withCookie) { $opts[CURLOPT_COOKIEJAR] = self::$jar; $opts[CURLOPT_COOKIEFILE] = self::$jar; }
        if ($body !== null) {
            if ($form) { $opts[CURLOPT_POSTFIELDS] = http_build_query($body); }
            else { $opts[CURLOPT_POSTFIELDS] = json_encode($body); $opts[CURLOPT_HTTPHEADER] = ['Content-Type: application/json']; }
        }
        curl_setopt_array($ch, $opts);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$code, json_decode((string) $raw, true), (string) $raw];
    }

    private function loginAsAdmin(): void
    {
        $this->call('GET', 'logout.php');
        $this->call('POST', 'login.php', ['email' => 'admin@brightstart.org', 'password' => 'Admin@123'], true);
        [$code, $json] = $this->call('GET', 'api/get_settings.php');
        $this->assertSame(200, $code, 'Admin should be signed in');
        $this->assertSame('Admin', $json['profile']['Role']);
    }

    // ---------- Read endpoints ----------
    public function testGetStudentsReturnsAtLeastTenRecords(): void
    {
        [$code, $json] = $this->call('GET', 'api/get_students.php');
        $this->assertSame(200, $code);
        $this->assertTrue($json['success']);
        $this->assertGreaterThanOrEqual(10, count($json['data']));
        $this->assertArrayHasKey('FirstName', $json['data'][0]);
    }

    public function testStudentSearchFiltersResults(): void
    {
        [, $json] = $this->call('GET', 'api/get_students.php?search=Soweto');
        $this->assertNotEmpty($json['data']);
        foreach ($json['data'] as $s) $this->assertSame('Soweto', $s['CommunityArea']);
    }

    /** @dataProvider listEndpoints */
    public function testEveryListEndpointHasAtLeastTenRecords(string $endpoint): void
    {
        [$code, $json] = $this->call('GET', "api/$endpoint.php");
        $this->assertSame(200, $code);
        $this->assertTrue($json['success']);
        $this->assertGreaterThanOrEqual(10, count($json['data']), "$endpoint should return 10+ records");
    }
    public static function listEndpoints(): array
    {
        return [['get_programs'], ['get_volunteers'], ['get_sessions'], ['get_enrolments'],
                ['get_attendance'], ['get_performance'], ['get_donations'], ['get_admin_users']];
    }

    public function testDashboardStatsContainsTotals(): void
    {
        [, $json] = $this->call('GET', 'api/dashboard_stats.php');
        foreach (['students', 'volunteers', 'programs', 'sessions', 'enrolments', 'donations', 'total_donations_amount'] as $key) {
            $this->assertArrayHasKey($key, $json);
        }
        $this->assertGreaterThanOrEqual(10, $json['students']);
    }

    // ---------- Email + password sign-in ----------
    public function testLoginWithCorrectPasswordSucceedsAndHidesTheHash(): void
    {
        [, $json] = $this->call('POST', 'api/login.php', ['email' => 'admin@brightstart.org', 'password' => 'Admin@123']);
        $this->assertTrue($json['success']);
        $this->assertSame('Admin', $json['user']['Role']);
        $this->assertArrayNotHasKey('PasswordHash', $json['user']);
    }

    public function testLoginWithWrongPasswordFails(): void
    {
        [, $json] = $this->call('POST', 'api/login.php', ['email' => 'admin@brightstart.org', 'password' => 'wrong-password']);
        $this->assertFalse($json['success']);
    }

    public function testLoginForUnapprovedAccountIsBlocked(): void
    {
        [, $json] = $this->call('POST', 'api/login.php', ['email' => 'ayesha.patel@brightstart.org', 'password' => 'Pass@1234']);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('pending', $json['message']);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        [, $json] = $this->call('POST', 'api/register.php', ['full_name' => 'Dup User', 'email' => 'admin@brightstart.org', 'username' => 'dupuser1', 'password' => 'Pass@1234']);
        $this->assertFalse($json['success']);
    }

    public function testPasswordHashesAreNeverSentToTheBrowser(): void
    {
        [, $json] = $this->call('GET', 'api/get_admin_users.php');
        $this->assertArrayNotHasKey('PasswordHash', $json['data'][0]);
    }

    // ---------- Single sign-on (uses the fake Google server) ----------
    public function testSsoRejectsAnInvalidToken(): void
    {
        if (!getenv('FIREBASE_LOOKUP_URL')) $this->markTestSkipped('FIREBASE_LOOKUP_URL not set');
        [$code, $json] = $this->call('POST', 'api/sso_login.php', ['idToken' => 'garbage'], false, false);
        $this->assertSame(401, $code);
        $this->assertFalse($json['success']);
    }

    public function testSsoCreatesAPendingAccountForNewGoogleUsers(): void
    {
        if (!getenv('FIREBASE_LOOKUP_URL')) $this->markTestSkipped('FIREBASE_LOOKUP_URL not set');
        $email = 'newuser' . time() . '@gmail.com';
        [$code, $json] = $this->call('POST', 'api/sso_login.php', ['idToken' => "valid:$email"], false, false);
        $this->assertSame(403, $code);
        $this->assertTrue($json['pending']);
        [, $users] = $this->call('GET', 'api/get_admin_users.php');
        $found = array_values(array_filter($users['data'], fn($u) => $u['Email'] === $email));
        $this->assertCount(1, $found);
        $this->assertSame('0', (string) $found[0]['Verified']);
    }

    public function testSsoSignsInAnApprovedUser(): void
    {
        if (!getenv('FIREBASE_LOOKUP_URL')) $this->markTestSkipped('FIREBASE_LOOKUP_URL not set');
        [$code, $json] = $this->call('POST', 'api/sso_login.php', ['idToken' => 'valid:admin@brightstart.org'], false, false);
        $this->assertSame(200, $code);
        $this->assertSame('dashboard.php', $json['redirect']);
    }

    // ---------- Settings (needs a signed-in user) ----------
    public function testSettingsRequireLogin(): void
    {
        [$code, $json] = $this->call('GET', 'api/get_settings.php', null, false, false);
        $this->assertSame(401, $code);
        $this->assertFalse($json['success']);
    }

    public function testSettingsCanBeSavedAndReadBack(): void
    {
        $this->loginAsAdmin();
        [, $save] = $this->call('POST', 'api/update_settings.php', ['theme' => 'light', 'text_size' => 'large', 'show_slider' => 0]);
        $this->assertTrue($save['success']);
        [, $get] = $this->call('GET', 'api/get_settings.php');
        $this->assertSame('light', $get['settings']['theme']);
        $this->assertSame('large', $get['settings']['text_size']);
        $this->assertSame(0, $get['settings']['show_slider']);
        $this->call('POST', 'api/update_settings.php', ['theme' => 'dark', 'text_size' => 'normal', 'show_slider' => 1]);
    }

    public function testInvalidSettingValueIsRejected(): void
    {
        $this->loginAsAdmin();
        [$code, $json] = $this->call('POST', 'api/update_settings.php', ['theme' => 'neon', 'text_size' => 'normal', 'show_slider' => 1]);
        $this->assertSame(400, $code);
        $this->assertFalse($json['success']);
    }

    public function testChangePasswordRejectsWrongCurrentPassword(): void
    {
        $this->call('GET', 'logout.php');
        $this->call('POST', 'login.php', ['email' => 'coordinator@brightstart.org', 'password' => 'Pass@1234'], true);
        [$code, $json] = $this->call('POST', 'api/change_password.php', ['current_password' => 'nope', 'new_password' => 'NewPass123', 'confirm_password' => 'NewPass123']);
        $this->assertSame(400, $code);
        $this->assertStringContainsString('incorrect', $json['message']);
    }

    // ---------- Create / delete round trip ----------
    public function testStudentCanBeAddedFoundAndDeleted(): void
    {
        $unique = 'Tester' . time();
        [, $add] = $this->call('POST', 'api/add_student.php', [
            'first_name' => $unique, 'last_name' => 'Case', 'date_of_birth' => '2010-01-01', 'gender' => 'Other',
            'community_area' => 'TestTown', 'contact_number' => '0000000000', 'enrolment_date' => '2026-10-01']);
        $this->assertTrue($add['success']);

        [, $found] = $this->call('GET', 'api/get_students.php?search=' . $unique);
        $this->assertCount(1, $found['data']);

        [, $del] = $this->call('DELETE', 'api/delete_student.php?id=' . $found['data'][0]['StudentID']);
        $this->assertTrue($del['success']);
        [, $after] = $this->call('GET', 'api/get_students.php?search=' . $unique);
        $this->assertCount(0, $after['data']);
    }
}