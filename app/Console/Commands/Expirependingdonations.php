<?php

namespace App\Console\Commands;

use App\Models\Donation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Marca como 'expired' doações pendentes cujo PIX já venceu.
 *
 * Não deleta nenhum registro — dados históricos são preservados
 * para relatórios, auditoria e reenvio de webhook caso necessário.
 *
 * Agendado a cada 5 minutos via bootstrap/app.php.
 */
class ExpirePendingDonations extends Command
{
    protected $signature   = 'donations:expire';
    protected $description = 'Marca como expiradas doações pendentes com PIX vencido';

    public function handle(): int
    {
        $count = Donation::pixExpired()->update(['status' => Donation::STATUS_EXPIRED]);

        if ($count > 0) {
            Log::info("donations:expire — {$count} doação(ões) marcada(s) como expirada(s).");
        }

        $this->info("Marcadas {$count} doação(ões) como expirada(s).");

        return self::SUCCESS;
    }
}
