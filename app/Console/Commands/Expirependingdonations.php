<?php

namespace App\Console\Commands;

use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Marca como "expired" doações pendentes cujo PIX já venceu.
 *
 * Nenhum registro é removido. Os dados históricos são preservados
 * para relatórios, auditoria e processamento de webhooks.
 *
 * Agendado a cada 5 minutos.
 */
class ExpirePendingDonations extends Command
{
    protected $signature = 'donations:expire';

    protected $description =
        'Marca como expiradas doações pendentes com PIX vencido';

    public function handle(): int
    {
        try {
            $count = Donation::query()
                ->pixExpired()
                ->update([
                    'status' => Donation::STATUS_EXPIRED,
                    'updated_at' => now(),
                ]);

            Log::info('Comando donations:expire concluído.', [
                'expired_donations' => $count,
                'executed_at' => now()->toIso8601String(),
            ]);

            $this->info(
                "Marcadas {$count} doação(ões) como expirada(s)."
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Falha ao executar donations:expire.', [
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'exception' => $exception,
            ]);

            $this->error(
                'Erro ao expirar doações: '.$exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
