<?php

namespace Aolr\ProductionBundle\Entity;

class Journal
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $abbrevTitle;

    /**
     * @var string|null
     */
    private $publisherName;

    /**
     * @var string|null
     */
    private $eIssn;

    /**
     * @var string|null
     */
    private $issn;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     *
     * @return Journal
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAbbrevTitle()
    {
        return $this->abbrevTitle;
    }

    /**
     * @param mixed $abbrevTitle
     *
     * @return Journal
     */
    public function setAbbrevTitle($abbrevTitle)
    {
        $this->abbrevTitle = $abbrevTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPublisherName()
    {
        return $this->publisherName;
    }

    /**
     * @param mixed $publisherName
     *
     * @return Journal
     */
    public function setPublisherName($publisherName)
    {
        $this->publisherName = $publisherName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEIssn()
    {
        return $this->eIssn;
    }

    /**
     * @param mixed $eIssn
     *
     * @return Journal
     */
    public function setEIssn($eIssn)
    {
        $this->eIssn = $eIssn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIssn()
    {
        return $this->issn;
    }

    /**
     * @param mixed $issn
     *
     * @return Journal
     */
    public function setIssn($issn)
    {
        $this->issn = $issn;
        return $this;
    }

}
