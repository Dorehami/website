<?php

// src/Entity/Comment.php

namespace App\Entity;

use App\Repository\CommentRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment implements ReportableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private ?string $content = null;
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Post $post = null;
    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $visible = true;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $moderationReason = null;
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $moderatedAt = null;
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $moderatedBy = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?self $parent = null;

    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $replies;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->replies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;
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

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function getModerationReason(): ?string
    {
        return $this->moderationReason;
    }

    public function setModerationReason(?string $moderationReason): static
    {
        $this->moderationReason = $moderationReason;
        return $this;
    }

    public function getModeratedAt(): ?DateTimeImmutable
    {
        return $this->moderatedAt;
    }

    public function setModeratedAt(?DateTimeImmutable $moderatedAt): static
    {
        $this->moderatedAt = $moderatedAt;
        return $this;
    }

    public function getModeratedBy(): ?User
    {
        return $this->moderatedBy;
    }

    public function setModeratedBy(?User $moderatedBy): static
    {
        $this->moderatedBy = $moderatedBy;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(self $reply): static
    {
        if (!$this->replies->contains($reply)) {
            $this->replies->add($reply);
            $reply->setParent($this);
        }

        return $this;
    }

    public function removeReply(self $reply): static
    {
        if ($this->replies->removeElement($reply)) {
            if ($reply->getParent() === $this) {
                $reply->setParent(null);
            }
        }

        return $this;
    }

    /**
     * Get visible replies
     *
     * @return Collection<int, self>
     */
    public function getVisibleReplies(): Collection
    {
        return $this->replies->filter(fn(self $reply) => $reply->isVisible() ?? true);
    }

    /**
     * Check if this comment is a reply
     */
    public function isReply(): bool
    {
        return $this->parent !== null;
    }

    /**
     * Check if this comment has replies
     */
    public function hasReplies(): bool
    {
        return !$this->replies->isEmpty();
    }

    /**
     * Get the number of visible replies
     */
    public function getVisibleRepliesCount(): int
    {
        return $this->getVisibleReplies()->count();
    }
    
    public function __toString(): string
    {
        return sprintf("%s-%s", $this->id, $this->createdAt->format("YmdHis"));
    }
}
