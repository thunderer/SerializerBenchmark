<?php
namespace Thunder\SerializerBenchmark\Entity;

final class User
{
    private $id;
    private $email;
    private $password;
    private $tags;
    private $createdAt;

    public function __construct($id, $email, $password)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->tags = [];
        $this->createdAt = new \DateTime();
    }

    public function getId() { return $this->id; }
    public function getEmail() { return $this->email; }
    public function getCreatedAt() { return $this->createdAt; }

    public function addTag(Tag $tag) { $this->tags[] = $tag; }
    public function getTags() { return $this->tags; }
}
