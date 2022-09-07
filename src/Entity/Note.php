<?php

namespace Aolr\ProductionBundle\Entity;

class Note
{
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
    private $type;

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
     * @return Note
     */
    public function setLabel(string $label): Note
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
     * @return Note
     */
    public function setContent(string $content): Note
    {
        $this->content = $content;
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
     * @return Note
     */
    public function setType(?string $type): Note
    {
        $this->type = $type;
        return $this;
    }


}
