<?php

namespace App\Libraries;

use App\Models\ConfigModel;

class ConfigService
{
    protected ?ConfigModel $configModel = null;

    public function getDailyHoursLimit(): float
    {
        return (float) ($this->getConfig('daily_hours_limit') ?? 24);
    }

    public function getDPlusNDays(): int
    {
        return (int) ($this->getConfig('d_plus_n_days') ?? 3);
    }

    public function isEditable(string $workDate): bool
    {
        $n = $this->getDPlusNDays();
        $cutoff = (new \DateTime())->modify("-{$n} days")->format('Y-m-d');
        return $workDate >= $cutoff;
    }

    public function getWorkingDays(): float
    {
        return (float) ($this->getConfig('working_days') ?? 22);
    }

    public function getStandardHours(): float
    {
        return (float) ($this->getConfig('standard_hours') ?? 8);
    }

    /**
     * Session expiration seconds (idle timeout); default 24h (FR-000a1).
     */
    public function getSessionExpiration(): int
    {
        return (int) ($this->getConfig('session_expiration') ?? 86400);
    }

    /**
     * BR-5: Hourly Cost = Monthly Cost / (Working Days × Standard Hours)
     */
    public function calculateHourlyCost(float $monthlyCost): float
    {
        $wd = $this->getWorkingDays();
        $sh = $this->getStandardHours();
        if ($wd <= 0 || $sh <= 0) {
            return 0.0;
        }
        return round($monthlyCost / ($wd * $sh), 2);
    }

    protected function getConfig(string $key): ?string
    {
        if ($this->configModel === null) {
            $this->configModel = new ConfigModel();
        }
        $row = $this->configModel->where('key', $key)->first();
        return $row['value'] ?? null;
    }
}
