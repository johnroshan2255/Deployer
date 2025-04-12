<?php

namespace App\Modules\Git\Interfaces;

interface GitInterface
{
    public function init(string $path): array;

    public function cloneRepository(string $repositoryUrl, string $path, string $branch = 'main'): array;

    public function pullChanges(string $path, string $branch = 'main'): array;

    public function checkoutBranch(string $path, string $branch): array;

    public function commitChanges(string $path, string $message): array;

    public function pushChanges(string $path, string $branch = 'main'): array;
}
