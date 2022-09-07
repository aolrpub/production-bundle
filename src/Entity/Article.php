<?php

namespace Aolr\ProductionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Article
{
    const DEFAULT_ID = 'aolr-0000';

    /**
     * @var string|null
     */
    private $id = self::DEFAULT_ID;

    /**
     * @var Journal|null
     */
    private $journal;

    /**
     * @var string|null
     */
    private $doi;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $categories = [];

    /**
     * @var ArrayCollection<Author>|null
     */
    private $authors;

    /**
     * @var ArrayCollection<Editor>|null
     */
    private $editors;

    /**
     * @var ArrayCollection<Affiliation>|null
     */
    private $affiliations;

    /**
     * @var \DateTime|null
     */
    private $publishedDate;

    /**
     * @var \DateTime|null
     */
    private $printDate;

    /**
     * @var \DateTime|null
     */
    private $receivedDate;

    /**
     * @var \DateTime|null
     */
    private $acceptedDate;

    /**
     * @var string|null
     */
    private $volume;

    /**
     * @var string|null
     */
    private $issue;

    /**
     * @var string|null
     */
    private $number;

    /**
     * @var Permission|null
     */
    private $permission;

    /**
     * @var string|null
     */
    private $abstract;

    /**
     * @var array
     */
    private $keywords = [];

    /**
     * @var ArrayCollection<Section>
     */
    private $sections;

    /**
     * @var ArrayCollection|null
     */
    private $backItems;

    /**
     * @var ArrayCollection<Reference>|null
     */
    private $references;

    /**
     * @var ArrayCollection|null
     */
    private $displayObjects;

    /**
     * @var array
     */
    private $footnotes = [];

    /**
     * @var ArrayCollection<Formula>
     */
    private $formulas;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->editors = new ArrayCollection();
        $this->affiliations = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->backItems = new ArrayCollection();
        $this->references = new ArrayCollection();
        $this->displayObjects = new ArrayCollection();
        $this->formulas = new ArrayCollection();
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
     * @return Article
     */
    public function setId(?string $id): Article
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Journal|null
     */
    public function getJournal(): ?Journal
    {
        return $this->journal;
    }

    /**
     * @param Journal|null $journal
     *
     * @return Article
     */
    public function setJournal(?Journal $journal): Article
    {
        $this->journal = $journal;
        return $this;
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
     * @return Article
     */
    public function setDoi(?string $doi): Article
    {
        $this->doi = $doi;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return Article
     */
    public function setType(?string $type): Article
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Article
     */
    public function setTitle(string $title): Article
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     *
     * @return Article
     */
    public function setCategories(array $categories): Article
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return ArrayCollection<Author>|null
     */
    public function getAuthors(): ?ArrayCollection
    {
        return $this->authors;
    }

    /**
     * @param Author $author
     *
     * @return $this|null
     */
    public function addAuthor(Author $author): ?Article
    {
        $this->authors->add($author);
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getEditors(): ?ArrayCollection
    {
        return $this->editors;
    }

    public function addEditor(Editor $editor): Article
    {
        $this->editors->add($editor);
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getAffiliations(): ?ArrayCollection
    {
        return $this->affiliations;
    }

    /**
     * @param Affiliation $affiliation
     *
     * @return $this
     */
    public function addAffiliation(Affiliation $affiliation): Article
    {
        $this->affiliations->add($affiliation);
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishedDate(): ?\DateTime
    {
        return $this->publishedDate;
    }

    /**
     * @param \DateTime|null $publishedDate
     *
     * @return Article
     */
    public function setPublishedDate(?\DateTime $publishedDate): Article
    {
        $this->publishedDate = $publishedDate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPrintDate(): ?\DateTime
    {
        return $this->printDate;
    }

    /**
     * @param \DateTime|null $printDate
     *
     * @return Article
     */
    public function setPrintDate(?\DateTime $printDate): Article
    {
        $this->printDate = $printDate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getReceivedDate(): ?\DateTime
    {
        return $this->receivedDate;
    }

    /**
     * @param \DateTime|null $receivedDate
     *
     * @return Article
     */
    public function setReceivedDate(?\DateTime $receivedDate): Article
    {
        $this->receivedDate = $receivedDate;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getAcceptedDate(): ?\DateTime
    {
        return $this->acceptedDate;
    }

    /**
     * @param \DateTime|null $acceptedDate
     *
     * @return Article
     */
    public function setAcceptedDate(?\DateTime $acceptedDate): Article
    {
        $this->acceptedDate = $acceptedDate;
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
     * @return Article
     */
    public function setVolume(?string $volume): Article
    {
        $this->volume = $volume;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIssue(): ?string
    {
        return $this->issue;
    }

    /**
     * @param string|null $issue
     *
     * @return Article
     */
    public function setIssue(?string $issue): Article
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string|null $number
     *
     * @return Article
     */
    public function setNumber(?string $number): Article
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return Permission|null
     */
    public function getPermission(): ?Permission
    {
        return $this->permission;
    }

    /**
     * @param Permission|null $permission
     *
     * @return Article
     */
    public function setPermission(?Permission $permission): Article
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbstract(): ?string
    {
        return $this->abstract;
    }

    /**
     * @param string|null $abstract
     *
     * @return Article
     */
    public function setAbstract(?string $abstract): Article
    {
        $this->abstract = $abstract;
        return $this;
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @param array $keywords
     *
     * @return Article
     */
    public function setKeywords(array $keywords): Article
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * @return array
     */
    public function getSections(): ArrayCollection
    {
        return $this->sections;
    }

    public function addSection(Section $section): Article
    {
        $this->sections->add($section);
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getBackItems(): ?ArrayCollection
    {
        return $this->backItems;
    }

    public function addBackItem($item): Article
    {
        $this->backItems->add($item);
        return $this;
    }

    public function getAppBackItems(): ?ArrayCollection
    {
        return $this->backItems->filter(function(BackItem $item) {
            return $item->getType() == BackItem::TYPE_APP;
        });
    }

    /**
     * @return ArrayCollection|null
     */
    public function getReferences(): ?ArrayCollection
    {
        return $this->references;
    }

    public function setReferences(ArrayCollection $references): Article
    {
        $this->references = $references;
        return $this;
    }

    public function addReference(Reference $reference): Article
    {
        $this->references->add($reference);
        return $this;
    }


    /**
     * @return ArrayCollection|null
     */
    public function getDisplayObjects(): ?ArrayCollection
    {
        return $this->displayObjects;
    }

    /**
     * @param DisplayObject $displayObject
     *
     * @return $this
     */
    public function addDisplayObject(DisplayObject $displayObject): Article
    {
        $this->displayObjects->add($displayObject);
        return $this;
    }

    /**
     * @return array
     */
    public function getFootnotes(): array
    {
        return $this->footnotes;
    }

    /**
     * @param array $footnotes
     *
     * @return Article
     */
    public function setFootnotes(array $footnotes): Article
    {
        $this->footnotes = $footnotes;
        return $this;
    }

    public function addFootNote(?string $footnote): Article
    {
        $this->footnotes[] = $footnote;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFormulas(): ArrayCollection
    {
        return $this->formulas;
    }

    /**
     * @param Formula $formula
     *
     * @return Article
     */
    public function addFormulas(Formula $formula): Article
    {
        $this->formulas->add($formula);
        return $this;
    }


    public function getNumberedAffs(): ArrayCollection
    {
        $items = $this->affiliations->filter(function (Affiliation $aff) {
            return is_numeric($aff->getLabel());
        });


        return $items->map(function(Affiliation $aff) {
            $aff->setOrderNumber($aff->getLabel());
            return $aff;
        });
    }

    public function getAuthorNotes(): ArrayCollection
    {
        $items = $this->affiliations->filter(function (Affiliation $affiliation) {
            return !is_numeric($affiliation->getLabel());
        });

        $count = [
            'corresp' => 0,
            'fn' => 0
        ];

        foreach ($items as $item) {
            if ($item->getType() == Affiliation::TYPE_CORRESP) {
                $count['corresp']++;
                $item->setOrderNumber($count['corresp']);
            }

            if ($item->getType() == Affiliation::TYPE_FN) {
                $count['fn']++;
                $item->setOrderNumber($count['fn']);
            }
        }

        return $items;
    }

    public function getCrossrefAbstract()
    {
        return preg_replace_callback('/<\/([^>]+)>/', function ($matches) {
            return str_replace($matches[1], 'jats:' . $matches[1], $matches[0]);
        }, $this->abstract);
    }
}
