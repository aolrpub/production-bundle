<?php

namespace Aolr\ProductionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class App
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $contents;

    public function __construct()
    {
        $this->contents = new ArrayCollection();
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
     * @return App
     */
    public function setTitle(?string $title): App
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getContents(): ArrayCollection
    {
        return $this->contents;
    }

    /**
     * @param Content $content
     *
     * @return $this
     */
    public function addContent(Content $content): App
    {
        $this->contents->add($content);
        return $this;
    }

}
