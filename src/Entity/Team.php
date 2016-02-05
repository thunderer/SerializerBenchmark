<?php
namespace Thunder\SerializerBenchmark\Entity;

final class Team
{
    private $users;

    public function __construct()
    {
        $this->users = [];
    }

    public function addUser(User $user) { $this->users[] = $user; }
    public function getUsers() { return $this->users; }
}
