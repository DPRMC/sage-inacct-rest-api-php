<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Resources;

use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;

final class SageIntacctResources
{
    public function __construct(private readonly SageIntacctClient $client)
    {
    }

    public function accountsPayable(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('accounts-payable', $allowWrites);
    }

    public function accountsReceivable(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('accounts-receivable', $allowWrites);
    }

    public function cashManagement(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('cash-management', $allowWrites);
    }

    public function companyConfiguration(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('company-config', $allowWrites);
    }

    public function contractsAndRevenueManagement(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('contracts-revenue-management', $allowWrites);
    }

    public function construction(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('construction', $allowWrites);
    }

    public function employeeExpenses(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('employee-expenses', $allowWrites);
    }

    public function fixedAssets(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('fixed-assets', $allowWrites);
    }

    public function generalLedger(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('general-ledger', $allowWrites);
    }

    public function inventoryControl(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('inventory-control', $allowWrites);
    }

    public function orderEntry(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('order-entry', $allowWrites);
    }

    public function projects(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('projects', $allowWrites);
    }

    public function purchasing(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('purchasing', $allowWrites);
    }

    public function tax(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('tax', $allowWrites);
    }

    public function reports(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('reports', $allowWrites);
    }

    public function commonResources(?bool $allowWrites = null): SageDomainClient
    {
        return $this->client->domain('common-resources', $allowWrites);
    }
}
