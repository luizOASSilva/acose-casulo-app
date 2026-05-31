<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use App\Mail\ContactMessageMail;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request): JsonResponse
    {
        $this->blockBots($request);

        $data = $request->validated();

        $ip = (string) $request->ip();
        $email = Str::lower((string) $data['email']);

        $this->blockByIp($ip);
        $this->blockByEmail($email);
        $this->blockDuplicateMessage($ip, $email, $data);
        $this->blockDailyLimits($ip, $email);

        Mail::to($this->contactEmail())->send(
            new ContactMessageMail([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'subject' => $data['subject'],
                'message' => $data['message'],
                'ip' => $ip,
                'user_agent' => $request->userAgent(),
            ])
        );

        $this->registerSuccessfulAttempt($ip, $email, $data);

        return response()->json([
            'message' => 'Mensagem enviada com sucesso.',
        ]);
    }

    private function blockBots(StoreContactRequest $request): void
    {
        $honeypot = (string) $request->input('website', '');

        if (filled($honeypot)) {
            abort(422, 'Não foi possível enviar a mensagem.');
        }

        $startedAt = (int) $request->input('started_at', 0);

        if ($startedAt <= 0) {
            abort(422, 'Não foi possível validar o envio.');
        }

        $elapsedSeconds = now()->timestamp - $startedAt;

        if ($elapsedSeconds < 3) {
            abort(429, 'Aguarde alguns segundos antes de enviar novamente.');
        }

        if ($elapsedSeconds > 60 * 60 * 3) {
            abort(422, 'Sessão expirada. Atualize a página e tente novamente.');
        }
    }

    private function blockByIp(string $ip): void
    {
        if (Cache::has($this->key('contact:lock:ip', $ip))) {
            abort(429, 'Aguarde alguns minutos antes de enviar outra mensagem.');
        }
    }

    private function blockByEmail(string $email): void
    {
        if (Cache::has($this->key('contact:lock:email', $email))) {
            abort(429, 'Aguarde alguns minutos antes de enviar outra mensagem com este e-mail.');
        }
    }

    private function blockDuplicateMessage(string $ip, string $email, array $data): void
    {
        if (Cache::has($this->messageFingerprint($ip, $email, $data))) {
            abort(429, 'Esta mensagem já foi enviada recentemente.');
        }
    }

    private function blockDailyLimits(string $ip, string $email): void
    {
        $ipDailyKey = $this->key('contact:daily:ip', $ip);
        $emailDailyKey = $this->key('contact:daily:email', $email);

        $ipCount = (int) Cache::get($ipDailyKey, 0);
        $emailCount = (int) Cache::get($emailDailyKey, 0);

        if ($ipCount >= 10) {
            abort(429, 'Limite diário de mensagens atingido. Tente novamente amanhã.');
        }

        if ($emailCount >= 5) {
            abort(429, 'Limite diário de mensagens para este e-mail atingido. Tente novamente amanhã.');
        }
    }

    private function registerSuccessfulAttempt(string $ip, string $email, array $data): void
    {
        Cache::put(
            $this->key('contact:lock:ip', $ip),
            true,
            now()->addMinutes(3)
        );

        Cache::put(
            $this->key('contact:lock:email', $email),
            true,
            now()->addMinutes(5)
        );

        Cache::put(
            $this->messageFingerprint($ip, $email, $data),
            true,
            now()->addHours(12)
        );

        $this->incrementDailyCounter(
            $this->key('contact:daily:ip', $ip)
        );

        $this->incrementDailyCounter(
            $this->key('contact:daily:email', $email)
        );
    }

    private function incrementDailyCounter(string $key): void
    {
        if (! Cache::has($key)) {
            Cache::put($key, 0, now()->endOfDay());
        }

        Cache::increment($key);
    }

    private function messageFingerprint(string $ip, string $email, array $data): string
    {
        $payload = [
            'ip' => $ip,
            'email' => $email,
            'subject' => Str::lower(trim((string) $data['subject'])),
            'message' => Str::lower(trim((string) $data['message'])),
        ];

        return 'contact:duplicate:' . hash('sha256', json_encode($payload));
    }

    private function contactEmail(): string
    {
        $settingEmail = Setting::query()
            ->where('key', 'contact_email')
            ->value('value');

        if (filled($settingEmail)) {
            return (string) $settingEmail;
        }

        return (string) config('mail.from.address');
    }

    private function key(string $prefix, string $value): string
    {
        return $prefix . ':' . hash('sha256', $value);
    }
}
