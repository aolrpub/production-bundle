<?php

namespace Aolr\ProductionBundle\Entity;

class Affiliation
{
    const TYPE_AFF = 'aff';
    const TYPE_CORRESP = 'corresp';
    const TYPE_FN = 'fn';

    /**
     *
     * @var int|null
     */
    private $orderNumber;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string|null
     */
    private $rorId;

    /**
     * @return int|null
     */
    public function getOrderNumber(): ?int
    {
        return $this->orderNumber;
    }

    /**
     * @param int|null $orderNumber
     *
     * @return Affiliation
     */
    public function setOrderNumber(?int $orderNumber): Affiliation
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return Affiliation
     */
    public function setLabel(string $label): Affiliation
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return Affiliation
     */
    public function setContent(string $content): Affiliation
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRorId(): ?string
    {
        return $this->rorId;
    }

    /**
     * @param string|null $rorId
     *
     * @return Affiliation
     */
    public function setRorId(?string $rorId): Affiliation
    {
        $this->rorId = $rorId;
        return $this;
    }

    public function getType(): string
    {
        if (is_numeric($this->label)) {
            return self::TYPE_AFF;
        }

        if ($this->label == '*') {
            return self::TYPE_CORRESP;
        }

        return self::TYPE_FN;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->getType() . ($this->orderNumber ?: $this->label);
    }
}
