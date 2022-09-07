<?php

namespace Aolr\ProductionBundle\Entity;

use Aolr\ProductionBundle\Service\Helper;

class Author
{
    /**
     * @var string|null
     */
    private $orcid;

    /**
     * @var string|null
     */
    private $surname;

    /**
     * @var string|null
     */
    private $givenName;

    /**
     * @var string|null
     */
    private $affs;

    /**
     * @var Article
     */
    private $article;

    /**
     * @return string|null
     */
    public function getOrcid(): ?string
    {
        return $this->orcid;
    }

    /**
     * @param string|null $orcid
     *
     * @return Author
     */
    public function setOrcid(?string $orcid): Author
    {
        $this->orcid = $orcid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    /**
     * @param string|null $givenName
     *
     * @return Author
     */
    public function setGivenName(?string $givenName): Author
    {
        $this->givenName = $givenName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string|null $surname
     *
     * @return Author
     */
    public function setSurname(?string $surname): Author
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAffs(): ?string
    {
        return $this->affs;
    }

    /**
     * @param string|null $affs
     *
     * @return Author
     */
    public function setAffs(?string $affs): Author
    {
        $this->affs = $affs;
        return $this;
    }

    public function getXRefs(): array
    {
        $res = [];

        $count = [
            'aff' => 0,
            'c' => 0,
            'fn' => 0
        ];
        foreach(explode(',', $this->affs) as $aff) {
            $item = [
                'id' => 'aff' . $aff,
                'value' => $aff,
                'type' => 'aff'
            ];
            if (is_numeric($aff)) {
                $item['type'] = Affiliation::TYPE_AFF;
                $count['aff']++;
                $item['id'] = $item['type'] . $aff;
            } else if ($aff == '*') {
                $item['type'] = Affiliation::TYPE_CORRESP;
                $count['c']++;
                $item['id'] = $item['type'] . $count['c'];
            } else {
                $item['type'] = Affiliation::TYPE_FN;
                $count['fn']++;
                $item['id'] = $item['type'] . $count['fn'];
            }

            $res[] = $item;
        }

        return $res;

    }

    /**
     * @return Article
     */
    public function getArticle(): Article
    {
        return $this->article;
    }

    /**
     * @param Article $article
     *
     * @return Author
     */
    public function setArticle(Article $article): Author
    {
        $this->article = $article;
        return $this;
    }

    public function getAffiliations()
    {
        return $this->article->getAffiliations()->filter(function (Affiliation $affiliation) {
            $affsArr = array_filter(explode(',', $this->affs), 'is_numeric');
            return in_array($affiliation->getLabel(), $affsArr);
        });
    }

    public function getName(): string
    {
        return $this->surname . ' ' . $this->givenName;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
