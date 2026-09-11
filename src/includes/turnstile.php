<?php
require_once __DIR__ . '/../config.php';

function turnstile_field(string $action, string $size = 'flexible'): void
{
    ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <div class="cf-turnstile mb-3"
         data-sitekey="<?php echo htmlspecialchars(TURNSTILE_SITEKEY); ?>"
         data-action="<?php echo htmlspecialchars($action); ?>"
         data-size="<?php echo htmlspecialchars($size); ?>"></div>
    <?php
}

function turnstile_verify(?string $token, string $expectedAction, ?string $remoteip = null): bool
{
    if (!is_string($token) || $token === '' || strlen($token) > 2048) {
        return false;
    }

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_POSTFIELDS     => http_build_query(array_filter([
            'secret'   => TURNSTILE_SECRET,
            'response' => $token,
            'remoteip' => $remoteip,
        ], fn($v) => $v !== null && $v !== '')),
    ]);
    $body     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $httpCode !== 200) {
        return false;
    }

    $result = json_decode($body, true);
    if (!is_array($result) || empty($result['success'])) {
        return false;
    }

    if (($result['action'] ?? null) !== $expectedAction) {
        return false;
    }

    $allowed = array_filter(array_map('trim', explode(',', TURNSTILE_HOSTNAMES)));
    if (!empty($allowed) && !in_array($result['hostname'] ?? '', $allowed, true)) {
        return false;
    }

    return true;
}
