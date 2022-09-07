<?php

namespace Aolr\ProductionBundle\Entity;

class License
{
    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var array
     */
    private $contents = [];

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
     * @return License
     */
    public function setType(?string $type): License
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     *
     * @return License
     */
    public function setContent(?string $content): License
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return array
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @param array $contents
     *
     * @return License
     */
    public function setContents(array $contents): License
    {
        $this->contents = $contents;
        return $this;
    }
}
