<?php

namespace App\Models;

/**
 * Class Item
 * @author Grant Lucas
 */
class Item
{
    /**
     * @var string $id
     */
    private $id = '';

    /**
     * @var string $name
     */
    private $name = '';

    /**
     * @param string $id
     * @param string $name
     */
    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Getter for id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id ;
    }

    /**
     * Getter for name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
