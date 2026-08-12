<?php

namespace App\Command;

use App\Entity\JobOffer;
use App\Service\JobOfferCrawler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'app:import-job-offer')]
class ImportJobOfferCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JobOfferCrawler $crawler,
        private HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'The job board page URL to crawl')
            ->addOption('item-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for each job card', 'article.job-card')
            ->addOption('title-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for the job title', '.title')
            ->addOption('company-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for the company name', '.company')
            ->addOption('description-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for the description', '.description')
            ->addOption('location-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for the location', '.location')
            ->addOption('salary-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for the salary', '.salary')
            ->addOption('url-selector', null, InputOption::VALUE_REQUIRED, 'CSS selector for the job URL', '.job-link')
            ->addOption('url-attribute', null, InputOption::VALUE_REQUIRED, 'HTML attribute to read for the job URL', 'href')
            ->addOption('base-url', null, InputOption::VALUE_REQUIRED, 'Base URL used to resolve relative links', '')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of offers to import', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = (string) $input->getArgument('url');
        $baseUrl = (string) $input->getOption('base-url');
        if ($baseUrl === '') {
            $baseUrl = $this->guessBaseUrl($url);
        }

        $output->writeln(sprintf('Fetching %s', $url));

        try {
            $response = $this->httpClient->request('GET', $url);
            $html = $response->getContent();
        } catch (\Throwable $exception) {
            $output->writeln(sprintf('<error>Unable to fetch the page: %s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        $selectors = [
            'item_selector' => (string) $input->getOption('item-selector'),
            'title_selector' => (string) $input->getOption('title-selector'),
            'company_selector' => (string) $input->getOption('company-selector'),
            'description_selector' => (string) $input->getOption('description-selector'),
            'location_selector' => (string) $input->getOption('location-selector'),
            'salary_selector' => (string) $input->getOption('salary-selector'),
            'url_selector' => (string) $input->getOption('url-selector'),
            'url_attribute' => (string) $input->getOption('url-attribute'),
            'base_url' => $baseUrl,
        ];

        $offers = $this->crawler->parseJobOffers($html, $selectors);
        $limit = $input->getOption('limit');
        if ($limit !== null && $limit !== '') {
            $offers = array_slice($offers, 0, (int) $limit);
        }

        if ($offers === []) {
            $output->writeln('No job offers were found.');

            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($offers as $offerData) {
            $this->upsertOffer($offerData);
            ++$count;
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('Imported %d job offer(s) from %s.', $count, $url));

        return Command::SUCCESS;
    }

    private function upsertOffer(array $offerData): void
    {
        $title = (string) ($offerData['title'] ?? 'Untitled position');
        $company = (string) ($offerData['company'] ?? 'Unknown company');
        $description = (string) ($offerData['description'] ?? 'No description provided.');
        $location = $offerData['location'] ?? null;
        $salary = $offerData['salary'] ?? null;
        $url = $offerData['url'] ?? null;
        $slug = $this->generateUniqueSlug($title, $company);

        $jobOffer = $this->entityManager->getRepository(JobOffer::class)->findOneBy(['slug' => $slug]);
        if ($jobOffer === null && $url !== null) {
            $jobOffer = $this->entityManager->getRepository(JobOffer::class)->findOneBy(['url' => $url]);
        }

        if ($jobOffer === null) {
            $jobOffer = new JobOffer();
        }

        $jobOffer
            ->setTitle($title)
            ->setCompany($company)
            ->setDescription($description)
            ->setLocation($location)
            ->setSalary($salary)
            ->setIsRemote($this->isRemoteLocation($location))
            ->setPostedAt(new \DateTimeImmutable())
            ->setUrl($url)
            ->setSlug($slug);

        $this->entityManager->persist($jobOffer);
    }

    private function generateUniqueSlug(string $title, string $company): string
    {
        $base = sprintf('%s %s', $title, $company);
        $base = mb_strtolower($base);
        $base = preg_replace('/[^a-z0-9]+/', '-', $base) ?? '';
        $base = trim($base, '-');

        $slug = $base !== '' ? $base : 'job-offer';
        $originalSlug = $slug;
        $counter = 1;

        while ($this->entityManager->getRepository(JobOffer::class)->findOneBy(['slug' => $slug]) !== null) {
            $slug = $originalSlug . '-' . $counter;
            ++$counter;
        }

        return $slug;
    }

    private function isRemoteLocation(?string $location): bool
    {
        if ($location === null) {
            return false;
        }

        return str_contains(mb_strtolower($location), 'remote') || str_contains(mb_strtolower($location), 'home office');
    }

    private function guessBaseUrl(string $url): string
    {
        $parts = parse_url($url);
        if (!isset($parts['scheme'], $parts['host'])) {
            return '';
        }

        return $parts['scheme'] . '://' . $parts['host'];
    }
}
