<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class JobOfferCrawler
{
    /**
     * @return array<int, array<string, string|null>>
     */
    public function parseJobOffers(string $html, array $selectors): array
    {
        $crawler = new Crawler($html);
        $items = $crawler->filter($selectors['item_selector'] ?? 'article');

        $offers = [];

        foreach ($items as $item) {
            $node = new Crawler($item);
            $offer = [
                'title' => $this->extractText($node, $selectors['title_selector'] ?? 'h2'),
                'company' => $this->extractText($node, $selectors['company_selector'] ?? '.company'),
                'description' => $this->extractText($node, $selectors['description_selector'] ?? '.description'),
                'location' => $this->extractText($node, $selectors['location_selector'] ?? '.location'),
                'salary' => $this->extractText($node, $selectors['salary_selector'] ?? '.salary'),
                'url' => $this->extractUrl($node, $selectors['url_selector'] ?? 'a', $selectors['url_attribute'] ?? 'href', $selectors['base_url'] ?? null),
            ];

            $offers[] = $offer;
        }

        return $offers;
    }

    private function extractText(Crawler $node, string $selector): ?string
    {
        $value = trim($node->filter($selector)->text(''));

        return $value !== '' ? $value : null;
    }

    private function extractUrl(Crawler $node, string $selector, string $attribute, ?string $baseUrl = null): ?string
    {
        $href = $node->filter($selector)->attr($attribute);
        if ($href === null) {
            return null;
        }

        $href = trim($href);
        if ($href === '') {
            return null;
        }

        if ($baseUrl !== null && str_starts_with($href, '/')) {
            return rtrim($baseUrl, '/') . $href;
        }

        return $href;
    }
}
