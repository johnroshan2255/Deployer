<?php

namespace App\Modules\Git\Repositories;

use App\Modules\Git\Interfaces\GitInterface;

class GitRepository implements GitInterface
{
    /**
     * Execute a command and return the output and status
     * 
     * @param string $command Command to execute
     * @return array Command result array with output and status
     */
    private function executeCommand(string $command): array
    {
        $output = [];
        $returnCode = 0;
        
        exec($command . " 2>&1", $output, $returnCode);
        
        $status = $returnCode === 0 ? 'success' : 'error';
        $message = implode("\n", $output);
        
        return [
            'status' => $status,
            'message' => $message,
            'code' => $returnCode
        ];
    }

    public function init(string $path): array
    {
        // Create directory if it doesn't exist
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $command = "cd " . escapeshellarg($path) . " && git init";
        return $this->executeCommand($command);
    }

    public function cloneRepository(string $repositoryUrl, string $path, string $branch = 'main'): array
    {
        // Create parent directory if it doesn't exist
        $parentDir = dirname($path);
        if (!is_dir($parentDir)) {
            mkdir($parentDir, 0755, true);
        }
        
        // Clone the repository with the specified branch
        $command = "git clone -b " . escapeshellarg($branch) . " " . 
                   escapeshellarg($repositoryUrl) . " " . 
                   escapeshellarg($path);
        
        return $this->executeCommand($command);
    }

    public function pullChanges(string $path, string $branch = 'main'): array
    {
        // Pull the latest changes from the specified branch
        $command = "cd " . escapeshellarg($path) . 
                   " && git checkout " . escapeshellarg($branch) . 
                   " && git pull origin " . escapeshellarg($branch);
        
        return $this->executeCommand($command);
    }

    public function checkoutBranch(string $path, string $branch): array
    {
        // Checkout the specified branch
        $command = "cd " . escapeshellarg($path) . 
                   " && git checkout " . escapeshellarg($branch);
        
        return $this->executeCommand($command);
    }

    public function commitChanges(string $path, string $message): array
    {
        // Add all changes and commit with the specified message
        $command = "cd " . escapeshellarg($path) . 
                   " && git add . && git commit -m " . escapeshellarg($message);
        
        return $this->executeCommand($command);
    }

    public function pushChanges(string $path, string $branch = 'main'): array
    {
        // Push changes to the specified branch
        $command = "cd " . escapeshellarg($path) . 
                   " && git push origin " . escapeshellarg($branch);
        
        return $this->executeCommand($command);
    }
}