<?php

namespace Aolr\ProductionBundle\Entity;

class Content
{
    const TYPE_TEXT = 'text';
    const TYPE_FIG = 'fig';
    const TYPE_TABLE = 'table';
    const TYPE_FORMULA = 'formula';
    const TYPE_CODE = 'code';
    const TYPE_ARRAY = 'array';
    const TYPE_MEDIA = 'media';

    public static $types = [
        self::TYPE_TEXT, self::TYPE_FIG, self::TYPE_TABLE, self::TYPE_FORMULA,
        self::TYPE_CODE, self::TYPE_ARRAY, self::TYPE_MEDIA
    ];

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $info;

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
     * @return Content
     */
    public function setType(?string $type): Content
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfo(): ?string
    {
        return $this->info;
    }

    /**
     * @param string|null $info
     *
     * @return Content
     */
    public function setInfo(?string $info): Content
    {
        $this->info = $info;
        return $this;
    }

    public function getComputedId(): string
    {
        return '';
    }

    public function __toString()
    {
        return $this->info;
    }
}
