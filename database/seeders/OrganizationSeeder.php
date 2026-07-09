<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $organizations = [
            [
                'name'                 => 'Green Earth Foundation',
                'registration_number'  => 'GEF-2019-001',
                'legal_status'         => 'Registered Charity',
                'type'                 => 'NGO',
                'founded_year'         => 2015,
                'registered_country'   => 'United Kingdom',
                'registered_city'      => 'London',
                'contact_email'        => 'info@greenearth.org',
                'contact_phone'        => '+44 20 7946 0000',
                'website_url'          => 'https://greenearth.org',
                'currency'             => 'GBP',
                'annual_income'        => 1250000.00,
                'annual_expenditure'   => 980000.00,
                'status'               => 'verified',
            ],
            [
                'name'                 => 'Sahara Water Initiative',
                'registration_number'  => 'SWI-2020-114',
                'legal_status'         => 'Non-Profit',
                'type'                 => 'Charity',
                'founded_year'         => 2018,
                'registered_country'   => 'Nigeria',
                'registered_city'      => 'Abuja',
                'contact_email'        => 'contact@saharawater.org',
                'currency'             => 'USD',
                'status'               => 'pending',
            ],
            [
                'name'                 => 'Bright Future Trust',
                'registration_number'  => 'BFT-2021-077',
                'legal_status'         => 'Foundation',
                'type'                 => 'Foundation',
                'founded_year'         => 2021,
                'registered_country'   => 'United States',
                'registered_state_province' => 'California',
                'registered_city'      => 'San Francisco',
                'contact_email'        => 'hello@brightfuture.org',
                'website_url'          => 'https://brightfuture.org',
                'currency'             => 'USD',
                'status'               => 'verified',
            ],
        ];

        foreach ($organizations as $org) {
            Organization::firstOrCreate(['name' => $org['name']], $org);
        }
    }
}
