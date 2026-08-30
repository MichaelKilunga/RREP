<?php

namespace App\Services\AI;

interface AIAdapterInterface
{
    /**
     * Send a prompt and get text completion.
     */
    public function generateText(string $prompt, array $options = []): string;

    /**
     * Generate structured real estate property marketing description.
     */
    public function generatePropertyDescription(array $propertyData): string;

    /**
     * Provide an automated estimate/valuation analysis.
     */
    public function estimatePropertyValuation(array $propertyData): array;
}
