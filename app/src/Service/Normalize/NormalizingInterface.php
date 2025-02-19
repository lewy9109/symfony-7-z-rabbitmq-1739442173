<?php

declare(strict_types=1);

namespace App\Service\Normalize;

interface NormalizingInterface
{
    /**
     * @return array<string, mixed>
     */
    public function process(string $sourceFeed): array;
}
