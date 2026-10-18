<?php
use PHPUnit\Framework\TestCase;

/** Checks the website pages (runs only when BASE_URL is set). */
final class WebsiteTest extends TestCase
{
    private static string $base;

    public static function setUpBeforeClass(): void
    {
        $base = getenv('BASE_URL');
        if (!$base) self::markTestSkipped('BASE_URL is not set - skipping website tests.');
        self::$base = rtrim($base, '/');
    }

    private function get(string $path, ?string $jar = null): array
    {
        $ch = curl_init(self::$base . '/' . $path);
        $o = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 15];
        if ($jar) { $o[CURLOPT_COOKIEFILE] = $jar; $o[CURLOPT_COOKIEJAR] = $jar; }
        curl_setopt_array($ch, $o);
        $res  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return [$code, substr($res, 0, $size), substr($res, $size)];
    }

    public function testHomePageLoads(): void
    {
        [$code, , $body] = $this->get('index.php');
        $this->assertSame(200, $code);
        $this->assertStringContainsString('Bright', $body);
    }

    public function testLoginPageHasGoogleButtonAndForm(): void
    {
        [$code, , $body] = $this->get('login.php');
        $this->assertSame(200, $code);
        $this->assertStringContainsString('Continue with Google', $body);
        $this->assertStringContainsString('name="password"', $body);
    }

    public function testHelpPageIsPublic(): void
    {
        [$code, , $body] = $this->get('help.php');
        $this->assertSame(200, $code);
        $this->assertStringContainsString('Help', $body);
    }

    /** @dataProvider protectedPages */
    public function testProtectedPagesRedirectToLoginWhenSignedOut(string $page): void
    {
        [$code, $headers] = $this->get($page);
        $this->assertSame(302, $code, "$page should redirect");
        $this->assertStringContainsString('login.php', $headers);
    }
    public static function protectedPages(): array
    {
        return [['dashboard.php'], ['students.php'], ['settings.php'], ['donations.php'], ['reports.php'], ['admin_users.php']];
    }

    public function testSignedInUserCanOpenSettingsPage(): void
    {
        $jar = tempnam(sys_get_temp_dir(), 'bsjar');
        $ch = curl_init(self::$base . '/login.php');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['email' => 'admin@brightstart.org', 'password' => 'Admin@123']),
            CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_RETURNTRANSFER => true]);
        curl_exec($ch);
        unset($ch);
        [$code, , $body] = $this->get('settings.php', $jar);
        @unlink($jar);
        $this->assertSame(200, $code);
        $this->assertStringContainsString('Appearance', $body);
    }

    public function testRegistrationRejectsWeakPassword(): void
    {
        $ch = curl_init(self::$base . '/register.php');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => http_build_query([
            'full_name' => 'Weak Pass', 'email' => 'weak@example.com', 'username' => 'weakpass', 'role' => 'Volunteer',
            'password' => 'abc', 'confirm_password' => 'abc'])]);
        $body = curl_exec($ch); curl_close($ch);
        $this->assertStringContainsString('at least 8 characters', $body);
    }
}