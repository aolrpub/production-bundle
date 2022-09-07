<?php

namespace Aolr\ProductionBundle\Entity;

class DisplayObject extends Content
{
    const TYPE_FIG = 'fig';
    const TYPE_TABLE = 'table';


    public static $types = [
        self::TYPE_FIG, self::TYPE_TABLE
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
    private $caption;

    /**
     * @var string|null
     */
    private $footer;

    /**
     * @var string|null
     */
    private $graphicHref;

    /**
     * @var Article|null
     */
    private $article;

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
     * @return DisplayObject
     */
    public function setId(?string $id): DisplayObject
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
     * @return DisplayObject
     */
    public function setLabel(?string $label): DisplayObject
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCaption(): ?string
    {
        return $this->caption;
    }

    /**
     * @param string|null $caption
     *
     * @return DisplayObject
     */
    public function setCaption(?string $caption): DisplayObject
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFooter(): ?string
    {
        return $this->footer;
    }

    /**
     * @param string|null $footer
     */
    public function setFooter(?string $footer): void
    {
        $this->footer = $footer;
    }

    /**
     * @return string|null
     */
    public function getGraphicHref(): ?string
    {
        return $this->graphicHref;
    }

    /**
     * @param string|null $graphicHref
     */
    public function setGraphicHref(?string $graphicHref): DisplayObject
    {
        $this->graphicHref = $graphicHref;
        return $this;
    }

    public function getComputedId(): string
    {
        if (preg_match('/(Table|Figure)\s+(\S+)\.?/i', $this->label, $matches)) {
            return $this->getType() . substr('000' . $matches[2], -3);
        }

        return $this->getType() . '000';
    }

    public function getGraphicExtension()
    {
        return pathinfo($this->graphicHref, PATHINFO_EXTENSION);
    }

    /**
     * @return Article|null
     */
    public function getArticle(): ?Article
    {
        return $this->article;
    }

    /**
     * @param Article|null $article
     */
    public function setArticle(?Article $article): void
    {
        $this->article = $article;
    }

}
