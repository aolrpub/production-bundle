<?php

namespace Aolr\ProductionBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Permission
{
    /**
     * @var string|null
     */
    private $statement;

    /**
     * @var string|null
     */
    private $year;

    /**
     * @var string|null
     */
    private $licenseType;

    /**
     * @var array
     */
    private $licenseContents = [];

    /**
     * @return mixed
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param mixed $statement
     *
     * @return Permission
     */
    public function setStatement($statement)
    {
        $this->statement = $statement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     *
     * @return Permission
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLicenseType(): ?string
    {
        return $this->licenseType;
    }

    /**
     * @param string|null $licenseType
     *
     * @return Permission
     */
    public function setLicenseType(?string $licenseType): Permission
    {
        $this->licenseType = $licenseType;
        return $this;
    }

    /**
     * @return array
     */
    public function getLicenseContents(): array
    {
        return $this->licenseContents;
    }

    /**
     * @param array $licenseContents
     *
     * @return Permission
     */
    public function setLicenseContents(array $licenseContents): Permission
    {
        $this->licenseContents = $licenseContents;
        return $this;
    }
}
