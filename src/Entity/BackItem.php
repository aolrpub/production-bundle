<?php

namespace Aolr\ProductionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class BackItem
{
    const TYPE_APP      = 'app';
    const TYPE_NOTES    = 'notes';
    const TYPE_ACK      = 'ack';
    const TYPE_GLOSSARY = 'glossary';

    public static $types = [
        self::TYPE_APP, self::TYPE_NOTES, self::TYPE_ACK, self::TYPE_GLOSSARY
    ];

    private $id;

    /**
     * @var int|null
     */
    private $orderNumber;

    /**
     * notes|app|ack
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $attrType;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var ArrayCollection<Content>|null
     */
    private $contents;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): BackItem
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return BackItem
     */
    public function setType(string $type): BackItem
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAttrType(): ?string
    {
        return $this->attrType ?: $this->getComputedAttrType();
    }

    /**
     * @param string|null $attrType
     *
     * @return BackItem
     */
    public function setAttrType(?string $attrType): BackItem
    {
        $this->attrType = $attrType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     *
     * @return BackItem
     */
    public function setTitle(?string $title): BackItem
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getContents(): ?ArrayCollection
    {
        return $this->contents;
    }

    public function addContent(Content $content): BackItem
    {
        $this->contents->add($content);
        return $this;
    }

    public function getComputedAttrType()
    {
        if (strtolower($this->title == 'conflicts of interest')) {
            return 'COI-statement';
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    /**
     * @param mixed $orderNumber
     */
    public function setOrderNumber($orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function isSupplementary(): bool
    {
        return strtolower($this->title) == 'supplementary materials';
    }

}
