<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Dto;
use App\Filter\ArchivedFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @see https://schema.org/Book Documentation on Schema.org
 */
#[ORM\Entity]
#[ApiResource(
    input: Dto\Book::class,
    output: Dto\Book::class,
    normalizationContext: ['groups' => ['book:read']],
    denormalizationContext: ['groups' => ['book:write']],
    collectionOperations: ['post'],
    itemOperations: ['get']
)]
#[ApiFilter(ArchivedFilter::class)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'title', 'author', 'isbn', 'publicationDate'])]
#[ApiFilter(PropertyFilter::class)]
class Book implements ArchivableInterface
{
    use ArchivableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    #[ApiProperty(identifier: true)]
    private UuidInterface $uuid;

    /**
     * The ISBN of the book.
     */
    #[ORM\Column(nullable: true)]
    public ?string $isbn = null;

    /**
     * The title of the book.
     */
    #[ORM\Column]
    public ?string $title = null;

    /**
     * A description of the item.
     */
    #[ORM\Column(type: 'text')]
    public ?string $description = null;

    /**
     * The author of this content or rating. Please note that author is special in that HTML 5 provides a special mechanism for indicating authorship via the rel tag. That is equivalent to this and may be used interchangeably.
     */
    #[ORM\Column]
    public ?string $author = null;

    /**
     * The date on which the CreativeWork was created or the item was added to a DataFeed.
     */
    #[ORM\Column(type: 'date')]
    public ?\DateTimeInterface $publicationDate = null;

    /**
     * The book's reviews.
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: Review::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $reviews;

    /**
     * The book's cover base64 encoded.
     */
    #[Groups(groups: ['book:cover'])]
    public ?string $cover = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::uuid4();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return (string) $this->uuid;
    }

    public function addReview(Review $review, bool $updateRelation = true): void
    {
        if ($this->reviews->contains($review)) {
            return;
        }

        $this->reviews->add($review);
        if ($updateRelation) {
            $review->setBook($this, false);
        }
    }

    public function removeReview(Review $review, bool $updateRelation = true): void
    {
        $this->reviews->removeElement($review);
        if ($updateRelation) {
            $review->setBook(null, false);
        }
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): iterable
    {
        return $this->reviews;
    }
}
