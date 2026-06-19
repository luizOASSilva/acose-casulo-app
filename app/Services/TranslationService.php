<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TranslationService
{
    public function translatePlainTextToEnglish(?string $text): ?string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        return $this->translate(
            instruction: implode("\n", [
                'Translate the following Brazilian Portuguese text to natural English.',
                'Return only the translated text.',
                'Preserve names of people, organizations, addresses, URLs, emails and phone numbers.',
                'Do not add explanations.',
            ]),
            text: $text
        );
    }

    public function translateHtmlToEnglish(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        return $this->translate(
            instruction: implode("\n", [
                'Translate the following HTML content from Brazilian Portuguese to natural English.',
                'Return only valid HTML.',
                'Preserve all HTML tags, nesting, attributes, href values, target, rel, classes and inline structure.',
                'Translate only visible text nodes.',
                'Do not translate URLs, emails, phone numbers, names of people, institution names or addresses.',
                'Do not wrap the result in markdown.',
                'Do not add explanations.',
            ]),
            text: $html
        );
    }

    private function translate(string $instruction, string $text): string
    {
        $apiKey = config('services.openai.api_key');
        $model = config('services.openai.translation_model');

        if (! $apiKey) {
            throw new RuntimeException('OPENAI_API_KEY não configurada.');
        }

        if (! $model) {
            throw new RuntimeException('OPENAI_TRANSLATION_MODEL não configurado.');
        }

        $response = Http::withToken($apiKey)
            ->timeout(90)
            ->retry(2, 1000)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $model,
                'input' => implode("\n\n", [
                    $instruction,
                    'CONTENT:',
                    $text,
                ]),
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                'Erro ao traduzir conteúdo: ' . $response->body()
            );
        }

        $translated = $this->extractOutputText($response->json());

        if (trim($translated) === '') {
            throw new RuntimeException('A API retornou tradução vazia.');
        }

        return trim($translated);
    }

    private function extractOutputText(array $payload): string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text'])) {
            return $payload['output_text'];
        }

        $parts = [];

        foreach (($payload['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                if (isset($content['text']) && is_string($content['text'])) {
                    $parts[] = $content['text'];
                }
            }
        }

        return trim(implode("\n", $parts));
    }
}
