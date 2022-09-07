<?php

namespace Aolr\ProductionBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;

class Reference
{
    const PUBLICATION_TYPE_JOURNAL = 'journal';
    const PUBLICATION_TYPE_BOOK = 'book';

    public static $publicationTypes = [
        self::PUBLICATION_TYPE_JOURNAL,
        self::PUBLICATION_TYPE_BOOK
    ];

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var string|null
     */
    private $publicationType;

    /**
     * @var ArrayCollection<Author>|null
     */
    private $persons;

    /**
     * @var ArrayCollection<Author>|null
     */
    private $editors;

    /**
     * @var string|null
     */
    private $articleTitle;

    /**
     * @var string|null
     */
    private $source;

    /**
     * @var int|null
     */
    private $year;

    /**
     * @var string|null
     */
    private $volume;

    /**
     * @var string|null
     */
    private $fPage;

    /**
     * @var string|null
     */
    private $lPage;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var string|null
     */
    private $publisher;

    /**
     * @var string|null
     */
    private $edition;

    /**
     * @var string|null
     */
    private $doi;

    /**
     * @var int|null
     */
    private $pmid;

    /**
     * @var string|null
     */
    private $rawText;

    public function __construct()
    {
        $this->persons = new ArrayCollection();
        $this->editors = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return Reference
     */
    public function setId(?string $id): Reference
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     *
     * @return Reference
     */
    public function setLabel(?string $label): Reference
    {
        $this->label = $label;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getPublicationType(): ?string
    {
        return $this->publicationType;
    }

    /**
     * @param string|null $publicationType
     *
     * @return Reference
     */
    public function setPublicationType(?string $publicationType): Reference
    {
        $this->publicationType = $publicationType;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPersons(): ArrayCollection
    {
        return $this->persons;
    }

    public function addPerson(Author $author): Reference
    {
        $this->persons->add($author);
        return $this;
    }

    public function removePerson(Author $author): Reference
    {
        $this->persons->removeElement($author);
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getEditors(): ?ArrayCollection
    {
        return $this->editors;
    }

    public function addEditor(Author $editor): Reference
    {
        $this->editors->add($editor);
        return $this;
    }

    public function removeEditor(Author $editor): Reference
    {
        $this->editors->removeElement($editor);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getArticleTitle(): ?string
    {
        return $this->articleTitle;
    }

    /**
     * @param string|null $articleTitle
     *
     * @return Reference
     */
    public function setArticleTitle(?string $articleTitle): Reference
    {
        $this->articleTitle = $articleTitle;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @param string|null $source
     *
     * @return Reference
     */
    public function setSource(?string $source): Reference
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->year;
    }

    /**
     * @param int|null $year
     *
     * @return Reference
     */
    public function setYear(?int $year): Reference
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVolume(): ?string
    {
        return $this->volume;
    }

    /**
     * @param string|null $volume
     *
     * @return Reference
     */
    public function setVolume(?string $volume): Reference
    {
        $this->volume = $volume;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFPage(): ?string
    {
        return $this->fPage;
    }

    /**
     * @param string|null $fPage
     *
     * @return Reference
     */
    public function setFPage(?string $fPage): Reference
    {
        $this->fPage = $fPage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLPage(): ?string
    {
        return $this->lPage;
    }

    /**
     * @param string|null $lPage
     *
     * @return Reference
     */
    public function setLPage(?string $lPage): Reference
    {
        $this->lPage = $lPage;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     */
    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string|null
     */
    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    /**
     * @param string|null $publisher
     */
    public function setPublisher(?string $publisher): void
    {
        $this->publisher = $publisher;
    }

    /**
     * @return string|null
     */
    public function getEdition(): ?string
    {
        return $this->edition;
    }

    /**
     * @param string|null $edition
     */
    public function setEdition(?string $edition): void
    {
        $this->edition = $edition;
    }

    /**
     * @return string|null
     */
    public function getDoi(): ?string
    {
        return $this->doi;
    }

    /**
     * @param string|null $doi
     *
     * @return Reference
     */
    public function setDoi(?string $doi): Reference
    {
        $this->doi = $doi;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPmid(): ?int
    {
        return $this->pmid;
    }

    /**
     * @param int|null $pmid
     *
     * @return Reference
     */
    public function setPmid(?int $pmid): Reference
    {
        $this->pmid = $pmid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRawText(): ?string
    {
        return $this->rawText;
    }

    /**
     * @param string|null $rawText
     *
     * @return Reference
     */
    public function setRawText(?string $rawText): Reference
    {
        $this->rawText = $rawText;
        return $this;
    }

    public function isEtal()
    {
        return strpos($this->rawText, 'et al. ');
    }
}
