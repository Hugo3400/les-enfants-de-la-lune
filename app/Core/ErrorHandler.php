<?php
declare(strict_types=1);

namespace App\Core;

final class ErrorHandler
{
    public static function notFound(string $message = 'Page introuvable.'): void
    {
        http_response_code(404);

        try {
            View::render('errors/404', [
                'title' => '404 - Page introuvable',
                'message' => $message,
            ]);
            return;
        } catch (\Throwable $exception) {
            self::log('error', 'Erreur rendu page 404', [
                'exception' => $exception->getMessage(),
            ]);
        }

        echo '404 - Page introuvable';
    }

    public static function internal(\Throwable|string $error, string $message = 'Une erreur interne est survenue.'): void
    {
        http_response_code(500);

        $details = [
            'error' => $error instanceof \Throwable ? $error->getMessage() : (string) $error,
        ];

        if ($error instanceof \Throwable) {
            $details['file'] = $error->getFile();
            $details['line'] = (string) $error->getLine();
        }

        self::log('error', 'Erreur interne', $details);

        try {
            View::render('errors/500', [
                'title' => '500 - Erreur interne',
                'message' => $message,
            ]);
            return;
        } catch (\Throwable $exception) {
            self::log('error', 'Erreur rendu page 500', [
                'exception' => $exception->getMessage(),
            ]);
        }

        echo '500 - Erreur interne';
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context !== [] ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : ''
        );

        @file_put_contents($logDir . '/app.log', $line, FILE_APPEND);
    }
}
