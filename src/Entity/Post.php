<?php

namespace App\Entity;

use App\Enum\PostType;
use App\Repository\PostRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $domain = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'post', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\OneToMany(targetEntity: PostVote::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $votes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $normalizedUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $domainIsPersonal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalAuthorName = null;

    #[ORM\Column(enumType: PostType::class, options: ['default' => PostType::ARTICLE])]
    private ?PostType $type = PostType::ARTICLE;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        $this->setDomainFromUrl($url);

        return $this;
    }

    private function setDomainFromUrl(string $url): void
    {
        $parsedUrl = parse_url($url);
        $this->domain = $parsedUrl['host'] ?? null;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PostVote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(PostVote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setPost($this);
        }

        return $this;
    }

    public function removeVote(PostVote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getPost() === $this) {
                $vote->setPost(null);
            }
        }

        return $this;
    }

    /**
     * Get the number of votes (points) for this post
     */
    public function getPoints(): int
    {
        return $this->votes->count();
    }

    public function getVisibleComments(): Collection
    {
        return $this->comments->filter(fn(Comment $comment) => $comment->isVisible() ?? true);
    }

    /**
     * Check if a user has already voted for this post
     */
    public function hasVotedBy(User $user): bool
    {
        foreach ($this->votes as $vote) {
            if ($vote->getUser() === $user) {
                return true;
            }
        }

        return false;
    }

    public function getNormalizedUrl(): ?string
    {
        return $this->normalizedUrl;
    }

    public function setNormalizedUrl(?string $normalizedUrl): static
    {
        $this->normalizedUrl = $normalizedUrl;
        return $this;
    }

    public function __toString(): string
    {
        return $this->title . ' - ' . $this->author->getDisplayName() . ' - ' . $this->url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isDomainIsPersonal(): ?bool
    {
        return $this->domainIsPersonal;
    }

    public function setDomainIsPersonal(?bool $domainIsPersonal): static
    {
        $this->domainIsPersonal = $domainIsPersonal;

        return $this;
    }

    public function getOriginalAuthorName(): ?string
    {
        return $this->originalAuthorName;
    }

    public function setOriginalAuthorName(?string $originalAuthorName): static
    {
        $this->originalAuthorName = $originalAuthorName;

        return $this;
    }

    public function getType(): ?PostType
    {
        return $this->type;
    }

    public function setType(PostType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
