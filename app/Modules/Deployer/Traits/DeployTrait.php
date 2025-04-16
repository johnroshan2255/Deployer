<?php

namespace App\Modules\Deployer\Traits;

trait DeployTrait
{
    /**
     * Get an available port within a given range.
     *
     * @param int $start Starting port number.
     * @param int $end Ending port number.
     * @return int|null Available port or null if none found.
     */
    public function getAvailablePort(int $start = 8000, int $end = 9000): ?int
    {
        foreach (range($start, $end) as $port) {
            if (!$this->isPortInUse($port)) {
                return $port;
            }
        }

        return null;
    }

    /**
     * Check if a given port is already in use.
     *
     * @param int $port
     * @return bool
     */
    protected function isPortInUse(int $port): bool
    {
        $output = [];
        exec("ss -tuln | grep ':{$port} '", $output);
        return !empty($output);
    }

}
