<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Repository\JobOfferRepository::class)]
class JobOffer
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	private ?int $id = null;

	#[ORM\Column(type: 'string', length: 255)]
	private string $title;

	#[ORM\Column(type: 'string', length: 255)]
	private string $company;

	#[ORM\Column(type: 'text')]
	private string $description;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $location = null;

	#[ORM\Column(type: 'string', length: 100, nullable: true)]
	private ?string $salary = null;

	#[ORM\Column(type: 'boolean')]
	private bool $isRemote = false;

	#[ORM\Column(type: 'datetime_immutable')]
	private \DateTimeImmutable $postedAt;

	#[ORM\Column(type: 'datetime_immutable', nullable: true)]
	private ?\DateTimeImmutable $expiresAt = null;

	#[ORM\Column(type: 'string', length: 255, unique: true)]
	private string $slug;

	#[ORM\Column(type: 'string', length: 255, nullable: true)]
	private ?string $url = null;

	public function __construct(string $title = '', string $company = '', string $description = '')
	{
		$this->title = $title;
		$this->company = $company;
		$this->description = $description;
		$this->postedAt = new \DateTimeImmutable();
		$this->slug = '';
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTitle(): string
	{
		return $this->title;
	}

	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getCompany(): string
	{
		return $this->company;
	}

	public function setCompany(string $company): self
	{
		$this->company = $company;

		return $this;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getLocation(): ?string
	{
		return $this->location;
	}

	public function setLocation(?string $location): self
	{
		$this->location = $location;

		return $this;
	}

	public function getSalary(): ?string
	{
		return $this->salary;
	}

	public function setSalary(?string $salary): self
	{
		$this->salary = $salary;

		return $this;
	}

	public function isRemote(): bool
	{
		return $this->isRemote;
	}

	public function setIsRemote(bool $isRemote): self
	{
		$this->isRemote = $isRemote;

		return $this;
	}

	public function getPostedAt(): \DateTimeImmutable
	{
		return $this->postedAt;
	}

	public function setPostedAt(\DateTimeImmutable $postedAt): self
	{
		$this->postedAt = $postedAt;

		return $this;
	}

	public function getExpiresAt(): ?\DateTimeImmutable
	{
		return $this->expiresAt;
	}

	public function setExpiresAt(?\DateTimeImmutable $expiresAt): self
	{
		$this->expiresAt = $expiresAt;

		return $this;
	}

	public function getSlug(): string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;

		return $this;
	}

	public function getUrl(): ?string
	{
		return $this->url;
	}

	public function setUrl(?string $url): self
	{
		$this->url = $url;

		return $this;
	}
}
