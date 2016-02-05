<?php
namespace Thunder\SerializerBenchmark\Entity;

final class Tag
{
    private $id;
    private $name;
    private $user;

    public function __construct($id, $name, User $user)
    {
        $this->id = $id;
        $this->name = $name;
//        $this->user = $user;
    }

    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getUser() { return $this->user; }
}
