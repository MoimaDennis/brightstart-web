<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/validation.php';

final class ValidationTest extends TestCase
{
    public function testValidEmailIsAccepted(): void   { $this->assertSame('', validate_email('thandi@brightstart.org')); }
    public function testInvalidEmailIsRejected(): void { $this->assertNotSame('', validate_email('not-an-email')); }
    public function testEmptyEmailIsRejected(): void   { $this->assertNotSame('', validate_email('')); }

    public function testGoodUsernameIsAccepted(): void { $this->assertSame('', validate_username('sipho.dlamini')); }
    public function testShortUsernameIsRejected(): void { $this->assertStringContainsString('at least 4', validate_username('abc')); }
    public function testUsernameWithSpacesIsRejected(): void { $this->assertNotSame('', validate_username('bad name!')); }

    public function testStrongPasswordIsAccepted(): void { $this->assertSame('', validate_password('Pass@1234')); }
    public function testShortPasswordIsRejected(): void  { $this->assertStringContainsString('at least 8', validate_password('Ab1')); }
    public function testPasswordWithoutNumberIsRejected(): void { $this->assertStringContainsString('number', validate_password('OnlyLetters')); }
    public function testPasswordWithoutLetterIsRejected(): void { $this->assertStringContainsString('letter', validate_password('12345678')); }

    public function testFullNameIsAccepted(): void  { $this->assertSame('', validate_full_name('Thandi Mokoena')); }
    public function testBlankFullNameIsRejected(): void { $this->assertNotSame('', validate_full_name('   ')); }

    public function testSettingValuesAreChecked(): void
    {
        $this->assertTrue(validate_setting('theme', 'light'));
        $this->assertFalse(validate_setting('theme', 'purple'));
        $this->assertTrue(validate_setting('text_size', 'large'));
        $this->assertFalse(validate_setting('text_size', 'huge'));
        $this->assertTrue(validate_setting('show_slider', 0));
        $this->assertFalse(validate_setting('show_slider', 'maybe'));
    }

    public function testPasswordsAreStoredAsBcryptHashes(): void
    {
        $hash = password_hash('Pass@1234', PASSWORD_BCRYPT);
        $this->assertNotSame('Pass@1234', $hash);
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(password_verify('Pass@1234', $hash));
        $this->assertFalse(password_verify('wrong', $hash));
    }
}