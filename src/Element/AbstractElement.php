<?php

namespace Aolr\ProductionBundle\Element;

use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractElement
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var AbstractElement|null
     */
    protected $prev;

    /**
     * @var AbstractElement|null
     */
    protected $next;

    /**
     * @var AbstractElement|null
     */
    protected $parent;

    /**
     * @var ArrayCollection
     */
    protected $children;


    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @return AbstractElement|null
     */
    public function getPrev(): ?AbstractElement
    {
        return $this->prev;
    }

    /**
     * @param AbstractElement|null $prev
     *
     * @return AbstractElement
     */
    public function setPrev(?AbstractElement $prev): AbstractElement
    {
        $this->prev = $prev;
        return $this;
    }

    /**
     * @return AbstractElement|null
     */
    public function getNext(): ?AbstractElement
    {
        return $this->next;
    }

    /**
     * @param AbstractElement|null $next
     *
     * @return AbstractElement
     */
    public function setNext(?AbstractElement $next): AbstractElement
    {
        $this->next = $next;
        return $this;
    }

    /**
     * @return AbstractElement|null
     */
    public function getParent(): ?AbstractElement
    {
        return $this->parent;
    }

    /**
     * @param AbstractElement|null $parent
     *
     * @return AbstractElement
     */
    public function setParent(?AbstractElement $parent): AbstractElement
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren(): ArrayCollection
    {
        return $this->children;
    }

    /**
     * @param AbstractElement $child
     *
     * @return $this
     */
    public function addChild(AbstractElement $child): AbstractElement
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
        }
        return $this;
    }

    /**
     * @param AbstractElement $child
     *
     * @return $this
     */
    public function removeChild(AbstractElement $child): AbstractElement
    {
        $this->children->remove($child);
        return $this;
    }
}
