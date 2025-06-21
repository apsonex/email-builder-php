<?php

namespace Apsonex\EmailBuilderPhp\Support\AiPayload;

use Apsonex\EmailBuilderPhp\Concerns\Makebale;
use Apsonex\EmailBuilderPhp\Contracts\StringableValueContract;

class BusinessInfo implements StringableValueContract
{
    use Makebale;

    protected ?string $industry = null;
    protected ?string $businessType = null;
    protected ?string $logoUrl = null;

    protected ?string $primaryBrandColor = null;
    protected ?string $primaryAltBrandColor = null;
    protected ?string $secondaryBrandColor = null;
    protected ?string $secondaryAltBrandColor = null;

    protected ?string $name = null;
    protected ?string $address = null;
    protected ?string $phone = null;
    protected ?string $email = null;
    protected ?string $website = null;
    protected ?string $googleMapLink = null;

    protected ?string $privacyPolicyUrl = null;
    protected ?string $termsOfUseUrl = null;

    protected array $socialLinks = [];

    public function industry(string $industry): static
    {
        $this->industry = $industry;
        return $this;
    }

    public function businessType(string $type): static
    {
        $this->businessType = $type;
        return $this;
    }

    public function logo(string $url): static
    {
        $this->logoUrl = $url;
        return $this;
    }

    public function primaryBrandColor(string $hex): static
    {
        $this->primaryBrandColor = $hex;
        return $this;
    }

    public function primaryAltBrandColor(string $hex): static
    {
        $this->primaryAltBrandColor = $hex;
        return $this;
    }

    public function secondaryBrandColor(string $hex): static
    {
        $this->secondaryBrandColor = $hex;
        return $this;
    }

    public function secondaryAltBrandColor(string $hex): static
    {
        $this->secondaryAltBrandColor = $hex;
        return $this;
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function address(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function phone(string $phone): static
    {
        $this->phone = $phone;
        $this->cleanPhone();
        return $this;
    }

    protected function cleanPhone(): static
    {
        if ($this->phone) {
            // Remove everything except digits and plus sign
            $cleaned = preg_replace('/[^\d+]/', '', $this->phone);

            // Ensure it starts with +1 and is followed by 10 digits
            if (preg_match('/^\+?1?(\d{10})$/', $cleaned, $matches)) {
                $this->phone = '+1' . $matches[1];
            } else {
                // Optionally, you can nullify or log invalid phone format
                $this->phone = null;
            }
        }

        return $this;
    }

    public function email(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function website(string $url): static
    {
        $this->website = $url;
        return $this;
    }

    public function googleMapLink(string $link): static
    {
        $this->googleMapLink = $link;
        return $this;
    }

    public function privacyPolicy(string $url): static
    {
        $this->privacyPolicyUrl = $url;
        return $this;
    }

    public function termsOfUse(string $url): static
    {
        $this->termsOfUseUrl = $url;
        return $this;
    }

    public function addSocialLink(string $platform, string $url): static
    {
        $this->socialLinks[$platform] = $url;
        return $this;
    }

    public function value(): string
    {
        $values = array_filter([
            'Industry'                => $this->industry,
            'Business Type'           => $this->businessType,
            'Logo URL'                => $this->logoUrl,
            'Primary Brand Color'     => $this->primaryBrandColor,
            'Primary Alt Brand Color' => $this->primaryAltBrandColor,
            'Secondary Brand Color'   => $this->secondaryBrandColor,
            'Secondary Alt Brand Color' => $this->secondaryAltBrandColor,
            'Business Name'           => $this->name,
            'Business Address'        => $this->address,
            'Phone'                   => $this->phone,
            'Email'                   => $this->email,
            'Website'                 => $this->website,
            'Google Map Link'         => $this->googleMapLink,
            'Privacy Policy'          => $this->privacyPolicyUrl,
            'Terms of Use'            => $this->termsOfUseUrl,
            'Social Links'            => $this->socialLinks,
        ]);

        $lines = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $lines[] = "{$key}:";
                foreach ($value as $platform => $url) {
                    $lines[] = "  - {$platform}: {$url}";
                }
            } else {
                $lines[] = "{$key}: {$value}";
            }
        }

        return implode("\n", $lines);
    }
}
