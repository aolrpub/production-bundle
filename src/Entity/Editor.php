<?php

namespace Aolr\ProductionBundle\Entity;

class Editor
{
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
    private $role;

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
     * @return Editor
     */
    public function setSurname(?string $surname): Editor
    {
        $this->surname = $surname;
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
     * @return Editor
     */
    public function setGivenName(?string $givenName): Editor
    {
        $this->givenName = $givenName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param string|null $role
     *
     * @return Editor
     */
    public function setRole(?string $role): Editor
    {
        $this->role = $role;
        return $this;
    }

}
