<?php

namespace Aolr\ProductionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Section
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var ArrayCollection
     */
    private $contents;

    /**
     * @var Section|null
     */
    private $parent;

    /**
     * @var ArrayCollection
     */
    private $children;

    /**
     * @var Section|null
     */
    private $prev;

    /**
     * @var Section|null
     */
    private $next;

    /**
     * @var Article|null
     */
    private $article;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->contents = new ArrayCollection();
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
     */
    public function setId(?string $id): Section
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
     * @return Section
     */
    public function setType(string $type): Section
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
     * @return Section
     */
    public function setTitle(string $title): Section
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

    public function addContent(Content $content): Section
    {
        $this->contents->add($content);
        return $this;
    }

    /**
     * @return Section|null
     */
    public function getParent(): ?Section
    {
        return $this->parent;
    }

    /**
     * @param Section $parent
     *
     * @return Section
     */
    public function setParent(Section $parent): Section
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

    public function addChild(Section $child): Section
    {
        $this->children->add($child);
        return $this;
    }

    /**
     * @return Section|null
     */
    public function getPrev(): ?Section
    {
        return $this->prev;
    }

    /**
     * @param Section $prev
     *
     * @return Section
     */
    public function setPrev(Section $prev): Section
    {
        $this->prev = $prev;
        return $this;
    }

    /**
     * @return Section|null
     */
    public function getNext(): ?Section
    {
        return $this->next;
    }

    /**
     * @param Section $next
     *
     * @return Section
     */
    public function setNext(Section $next): Section
    {
        $this->next = $next;
        return $this;
    }

    public function getTitleText()
    {
        if (preg_match('/\d\.\s+(.*)$/', $this->title, $matches)) {
            return $matches[1];
        }
        return $this->title;
    }

    public function getComputedType(): string
    {
        $typeArr = [
            'intro' => 'introduction',
            'materials' => 'materials',
            'methods' => 'methods',
            'results' => 'results',
            'discussion' => 'discussion'
        ];

        $textArr = explode(' and ', strtolower($this->getTitleText()));;
        $types = array_map(function ($text) use ($typeArr) {
            $key = array_search($text, $typeArr);
            return $key === false ? '' : $key;
        }, $textArr);
        $res = trim(implode('|', $types));

        return !empty($res) ? $res : '';
    }

    public function getComputedId(): ?string
    {
        if (preg_match('/^([\d\.]+)\./', $this->title, $matches)) {
            return 'sec' . str_replace('.', 'dot', $matches[1]);
        }

        return null;
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
     *
     * @return Section
     */
    public function setArticle(?Article $article): Section
    {
        $this->article = $article;
        return $this;
    }

}
