<?php

namespace App\Tests\Service;

use App\Service\JobOfferCrawler;
use PHPUnit\Framework\TestCase;

class JobOfferCrawlerTest extends TestCase
{
    public function testItParsesJobOffersFromHtml(): void
    {
        $crawler = new JobOfferCrawler();
        $html = <<<'HTML'
<!DOCTYPE html>
<html>
<body>
  <section>
    <article class="job-card">
      <h2 class="title">Senior PHP Developer</h2>
      <div class="company">Acme</div>
      <p class="description">Build APIs for a modern SaaS platform.</p>
      <div class="location">Remote · Paris</div>
      <div class="salary">€80k - €100k</div>
      <a class="job-link" href="/jobs/senior-php-developer">View</a>
    </article>
    <article class="job-card">
      <h2 class="title">Frontend Engineer</h2>
      <div class="company">Globex</div>
      <p class="description">Create delightful interfaces for our customers.</p>
      <div class="location">Berlin</div>
      <div class="salary">€70k</div>
      <a class="job-link" href="/jobs/frontend-engineer">View</a>
    </article>
  </section>
</body>
</html>
HTML;

        $offers = $crawler->parseJobOffers($html, [
            'item_selector' => 'article.job-card',
            'title_selector' => '.title',
            'company_selector' => '.company',
            'description_selector' => '.description',
            'location_selector' => '.location',
            'salary_selector' => '.salary',
            'url_selector' => '.job-link',
            'url_attribute' => 'href',
            'base_url' => 'https://example.com',
        ]);

        $this->assertCount(2, $offers);
        $this->assertSame('Senior PHP Developer', $offers[0]['title']);
        $this->assertSame('Acme', $offers[0]['company']);
        $this->assertSame('Build APIs for a modern SaaS platform.', $offers[0]['description']);
        $this->assertSame('Remote · Paris', $offers[0]['location']);
        $this->assertSame('€80k - €100k', $offers[0]['salary']);
        $this->assertSame('https://example.com/jobs/senior-php-developer', $offers[0]['url']);
    }
}
