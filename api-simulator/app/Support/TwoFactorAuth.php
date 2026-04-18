<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Twilio\Http\CurlClient as TwilioCurlClient;
use Twilio\Rest\Client as TwilioClient;

class TwoFactorAuth
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    public const METHOD_TOTP = 'totp';
    public const METHOD_SMS = 'sms';
    public const METHOD_EMAIL = 'email_otp';
    public const METHOD_BACKUP = 'backup_code';
    public const VERIFICATION_UNKNOWN_IP = 'unknown_ips';
    public const VERIFICATION_UNKNOWN_SUBNET = 'unknown_ips_subnet';
    public const VERIFICATION_UNKNOWN_BROWSER = 'unknown_browser';
    public const VERIFICATION_USER_AGENT = 'user_agent';
    public const VERIFICATION_OS = 'os';

    public function generateSecret(int $length = 32): string
    {
        $secret = '';

        while (strlen($secret) < $length) {
            $secret .= self::BASE32_ALPHABET[random_int(0, 31)];
        }

        return substr($secret, 0, $length);
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(?string $encryptedSecret): ?string
    {
        if (blank($encryptedSecret)) {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedSecret);
        } catch (\Throwable) {
            return null;
        }
    }

    public function provisioningUri(string $issuer, string $accountName, string $secret): string
    {
        $label = rawurlencode($issuer . ':' . $accountName);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            $secret,
            rawurlencode($issuer)
        );
    }

    public function verifyCode(string $secret, string $code, int $window = 1): bool
    {
        $normalizedCode = preg_replace('/\D+/', '', $code);

        if ($normalizedCode === null || strlen($normalizedCode) !== 6) {
            return false;
        }

        $timeSlice = (int) floor(time() / 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->getCode($secret, $timeSlice + $offset), $normalizedCode)) {
                return true;
            }
        }

        return false;
    }

    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $raw = strtoupper(Str::random(8));
            $codes[] = substr($raw, 0, 4) . '-' . substr($raw, 4, 4);
        }

        return $codes;
    }

    public function normalizeBackupCode(string $code): string
    {
        return strtoupper((string) preg_replace('/[^A-Z0-9]/i', '', $code));
    }

    public function verificationOptionLabels(): array
    {
        return [
            self::VERIFICATION_UNKNOWN_IP => "Unknown IP's",
            self::VERIFICATION_UNKNOWN_SUBNET => "Unknown IP's subnet",
            self::VERIFICATION_UNKNOWN_BROWSER => 'Unknown Browser',
            self::VERIFICATION_USER_AGENT => 'UserAgent',
            self::VERIFICATION_OS => 'OS',
        ];
    }

    public function tokenTypeLabels(): array
    {
        return [
            self::METHOD_SMS => 'SMS',
            self::METHOD_EMAIL => 'Email',
            self::METHOD_BACKUP => 'Backup code',
        ];
    }

    public function enabledVerificationOptions(User $user): array
    {
        $options = $user->two_factor_verification_options;

        if (! is_array($options)) {
            $options = [];
        }

        $valid = array_keys($this->verificationOptionLabels());

        return array_values(array_intersect($valid, array_filter($options)));
    }

    public function enabledTokenTypes(User $user): array
    {
        $types = $user->two_factor_token_types;

        if (! is_array($types)) {
            $types = [];
        }

        $valid = array_keys($this->tokenTypeLabels());

        return array_values(array_intersect($valid, array_filter($types)));
    }

    public function availableMethods(User $user): array
    {
        $methods = [];

        foreach ($this->enabledTokenTypes($user) as $type) {
            if ($type === self::METHOD_SMS && filled($user->two_factor_phone)) {
                $methods[] = self::METHOD_SMS;
            }

            if ($type === self::METHOD_EMAIL && filled($user->two_factor_email)) {
                $methods[] = self::METHOD_EMAIL;
            }

            if (
                $type === self::METHOD_BACKUP &&
                (filled($user->two_factor_backup_answer_hash) || $user->security_answer_hash || $user->twoFactorBackupCodes()->where('used', false)->exists())
            ) {
                $methods[] = self::METHOD_BACKUP;
            }
        }

        if ($this->decryptSecret($user->two_factor_secret)) {
            $methods[] = self::METHOD_TOTP;
        }

        return array_values(array_unique($methods));
    }

    public function shouldRequireChallenge(User $user, Request $request): bool
    {
        if ($user->two_factor_enabled) {
            return true;
        }

        $options = $this->enabledVerificationOptions($user);

        if ($options === []) {
            return true;
        }

        $current = $this->trustedContext($request);

        foreach ($options as $option) {
            if ($option === self::VERIFICATION_UNKNOWN_IP && blank($user->two_factor_trusted_ip)) {
                return true;
            }

            if ($option === self::VERIFICATION_UNKNOWN_IP && $user->two_factor_trusted_ip !== $current['ip']) {
                return true;
            }

            if ($option === self::VERIFICATION_UNKNOWN_SUBNET && blank($user->two_factor_trusted_subnet)) {
                return true;
            }

            if ($option === self::VERIFICATION_UNKNOWN_SUBNET && $user->two_factor_trusted_subnet !== $current['subnet']) {
                return true;
            }

            if ($option === self::VERIFICATION_UNKNOWN_BROWSER && blank($user->two_factor_trusted_browser)) {
                return true;
            }

            if ($option === self::VERIFICATION_UNKNOWN_BROWSER && $user->two_factor_trusted_browser !== $current['browser']) {
                return true;
            }

            if ($option === self::VERIFICATION_USER_AGENT && blank($user->two_factor_trusted_user_agent_hash)) {
                return true;
            }

            if ($option === self::VERIFICATION_USER_AGENT && $user->two_factor_trusted_user_agent_hash !== $current['user_agent_hash']) {
                return true;
            }

            if ($option === self::VERIFICATION_OS && blank($user->two_factor_trusted_os)) {
                return true;
            }

            if ($option === self::VERIFICATION_OS && $user->two_factor_trusted_os !== $current['os']) {
                return true;
            }
        }

        return false;
    }

    public function rememberTrustedContext(User $user, Request $request): void
    {
        $current = $this->trustedContext($request);

        $user->forceFill([
            'two_factor_trusted_ip' => $current['ip'],
            'two_factor_trusted_subnet' => $current['subnet'],
            'two_factor_trusted_browser' => $current['browser'],
            'two_factor_trusted_os' => $current['os'],
            'two_factor_trusted_user_agent_hash' => $current['user_agent_hash'],
        ])->save();
    }

    public function generateOtpCode(int $digits = 6): string
    {
        $max = (10 ** $digits) - 1;

        return str_pad((string) random_int(0, $max), $digits, '0', STR_PAD_LEFT);
    }

    public function issueOtpPayload(string $code, int $minutes = 10): array
    {
        $payload = [
            'hash' => password_hash($code, PASSWORD_BCRYPT),
            'expires_at' => now()->addMinutes($minutes)->toDateTimeString(),
        ];

        if (app()->isLocal()) {
            $payload['debug_code'] = $code;
        }

        return $payload;
    }

    public function verifyOtpPayload(array $payload, string $code): bool
    {
        if (($payload['expires_at'] ?? null) === null || now()->greaterThan($payload['expires_at'])) {
            return false;
        }

        $normalizedCode = preg_replace('/\D+/', '', $code);

        return is_string($normalizedCode) && password_verify($normalizedCode, (string) ($payload['hash'] ?? ''));
    }

    public function deliverChannelCode(User $user, string $method, string $code): void
    {
        if ($method === self::METHOD_EMAIL) {
            Mail::raw(
                "Your Adshqip two-factor verification code is: {$code}",
                function ($mail) use ($user) {
                    $mail->to($user->two_factor_email)->subject('Adshqip Two Factor Verification Code');
                }
            );

            return;
        }

        if ($method === self::METHOD_SMS) {
            if (blank($user->two_factor_phone)) {
                return;
            }

            $sid = env('TWILIO_ACCOUNT_SID');
            $token = env('TWILIO_AUTH_TOKEN');
            $from = env('TWILIO_FROM');
            $caFile = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');

            if (filled($sid) && filled($token) && filled($from)) {
                $httpClient = null;

                if (is_string($caFile) && $caFile !== '' && is_file($caFile)) {
                    $httpClient = new TwilioCurlClient([
                        CURLOPT_CAINFO => $caFile,
                    ]);
                }

                $twilio = new TwilioClient($sid, $token, null, null, $httpClient);
                $twilio->messages->create($user->two_factor_phone, [
                    'from' => $from,
                    'body' => "Your Adshqip two-factor verification code is: {$code}",
                ]);

                if (app()->isLocal()) {
                    Log::info('Adshqip two-factor SMS sent via Twilio', [
                        'user_id' => $user->id,
                        'phone' => $user->two_factor_phone,
                    ]);
                }

                return;
            }

            Log::info('Adshqip two-factor SMS code', [
                'user_id' => $user->id,
                'phone' => $user->two_factor_phone,
                'code' => $code,
                'twilio_configured' => false,
            ]);
        }
    }

    public function trustedContext(Request $request): array
    {
        $agent = UserAgentDetails::parse($request->userAgent());
        $userAgent = (string) $request->userAgent();

        return [
            'ip' => $request->ip(),
            'subnet' => $this->subnetForIp($request->ip()),
            'browser' => $agent['browser'],
            'os' => $agent['os'],
            'user_agent_hash' => hash('sha256', $userAgent),
        ];
    }

    public function subnetForIp(?string $ip): ?string
    {
        if (blank($ip)) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);

            return count($parts) === 4 ? implode('.', array_slice($parts, 0, 3)) . '.0/24' : null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);

            return implode(':', array_slice($parts, 0, 4)) . '::/64';
        }

        return null;
    }

    private function getCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);
        $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $binaryTime, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $value = unpack('N', $truncatedHash)[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper($secret);
        $buffer = '';
        $bitsLeft = 0;
        $result = '';

        foreach (str_split($secret) as $char) {
            if ($char === '=') {
                continue;
            }

            $value = strpos(self::BASE32_ALPHABET, $char);

            if ($value === false) {
                continue;
            }

            $buffer .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);

            while (strlen($buffer) >= 8) {
                $result .= chr(bindec(substr($buffer, 0, 8)));
                $buffer = substr($buffer, 8);
            }

            $bitsLeft += 5;
        }

        return $result;
    }
}
