<?php

namespace Aolr\ProductionBundle\Entity;

class Formula extends Content
{
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
    private $display;

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
    public function setId(?string $id): void
    {
        $this->id = $id;
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
     */
    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getDisplay(): ?string
    {
        return $this->display;
    }

    /**
     * @param string|null $display
     */
    public function setDisplay(?string $display): void
    {
        $this->display = $display;
    }

}
