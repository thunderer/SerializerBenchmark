<?php
namespace Thunder\SerializerBenchmark\Benchmark;

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\VisitorInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Thunder\Serializard\Format\JsonFormat;
use Thunder\Serializard\FormatContainer\FormatContainer;
use Thunder\Serializard\HydratorContainer\FallbackHydratorContainer;
use Thunder\Serializard\Normalizer\ReflectionNormalizer;
use Thunder\Serializard\NormalizerContainer\FallbackNormalizerContainer;
use Thunder\Serializard\Serializard;
use Thunder\SerializerBenchmark\Entity\Tag;
use Thunder\SerializerBenchmark\Entity\Team;
use Thunder\SerializerBenchmark\Entity\User;

/**
 * @Iterations(5)
 * @Revs(10)
 */
final class SerializerBench
{
    private $data;

    public function __construct()
    {
        $this->data = $this->createBenchmark();
    }

    public function verify()
    {
        var_dump('JMS');
        var_dump($this->benchJmsCustom());
        var_dump($this->benchJmsDefault());
        var_dump('Symfony');
        var_dump($this->benchSymfonyGetSetNormalizer());
        var_dump($this->benchSymfonyObjectNormalizer());
        var_dump($this->benchSymfonyPropertyNormalizer());
        var_dump('Serializard');
        var_dump($this->benchSerializardClosure());
        var_dump($this->benchSerializardReflection());
    }

    private function createBenchmark()
    {
        $users = 100;
        $tags = 100;

        $team = new Team();

        for($i = 0; $i < $users; $i++) {
            $user = new User(1, 'em@ail.com', 'password');

            array_map(function($name) use($user) {
                $user->addTag(new Tag(rand(0, 100), $name, $user));
            }, range(1, $tags));

            $team->addUser($user);
        }

        return $team;
    }

    /**
     * Can't make it handle Team users, DateTime and User tags properly
     */
    public function benchJmsCustom()
    {
        $jms = SerializerBuilder::create()
            ->configureHandlers(function(HandlerRegistry $registry) {
                $teamHandler = function(JsonSerializationVisitor $visitor, Team $team, array $type, SerializationContext $context) {
                    $visitor->setRoot(['users' => $visitor->visitArray($team->getUsers(), [], $context)]);
                };
                $userHandler = function(JsonSerializationVisitor $visitor, User $user, array $type, SerializationContext $context) {
                    $visitor->setRoot([
                        'user' => $user->getId(),
                        'email' => $user->getEmail(),
                        'tags' => $visitor->visitArray($user->getTags(), [], $context),
                        'createdAt' => $user->getCreatedAt(),
                    ]);
                };
                $tagHandler = function(JsonSerializationVisitor $visitor, Tag $tag, array $type) {
                    $visitor->setRoot([
                        'id' => $tag->getId(),
                        'name' => $tag->getName(),
                    ]);
                };
                $dateHandler = function(JsonSerializationVisitor $visitor, \DateTime $date, array $type) {
                    $visitor->setRoot($date->format(\DateTime::RFC3339));
                };

                $registry->registerHandler('serialization', Team::class, 'json', $teamHandler);
                $registry->registerHandler('serialization', User::class, 'json', $userHandler);
                $registry->registerHandler('serialization', Tag::class, 'json', $tagHandler);
                $registry->registerHandler('serialization', \DateTime::class, 'json', $dateHandler);
            })
            ->build();

        return $jms->serialize($this->data, 'json');
    }

    public function benchJmsDefault()
    {
        $jms = SerializerBuilder::create()->build();

        return $jms->serialize($this->data, 'json');
    }

    public function benchSymfonyObjectNormalizer()
    {
        $normalizer = new ObjectNormalizer();
        $normalizer->setCallbacks(array(
            'createdAt' => function(\DateTime $date) { return $date->format(\DateTime::RFC3339); },
        ));
        $normalizers = array($normalizer);
        $encoders = array(new JsonEncoder());
        $symfony = new Serializer($normalizers, $encoders);

        return $symfony->serialize($this->data, 'json');
    }

    public function benchSymfonyPropertyNormalizer()
    {
        $normalizer = new PropertyNormalizer();
        $normalizer->setCallbacks(array(
            'createdAt' => function(\DateTime $date) { return $date->format(\DateTime::RFC3339); },
        ));
        $normalizer->setIgnoredAttributes(['user', 'password']);
        $normalizers = array($normalizer);
        $encoders = array(new JsonEncoder());
        $symfony = new Serializer($normalizers, $encoders);

        return $symfony->serialize($this->data, 'json');
    }

    public function benchSymfonyGetSetNormalizer()
    {
        $normalizer = new GetSetMethodNormalizer();
        $normalizer->setCallbacks(array(
            'createdAt' => function(\DateTime $date) { return $date->format(\DateTime::RFC3339); },
        ));
        $normalizers = array($normalizer);
        $encoders = array(new JsonEncoder());
        $symfony = new Serializer($normalizers, $encoders);

        return $symfony->serialize($this->data, 'json');
    }

    public function benchSerializardReflection()
    {
        $formats = new FormatContainer();
        $formats->add('json', new JsonFormat());

        $normalizers = new FallbackNormalizerContainer();
        $normalizers->add(Team::class, 'team', new ReflectionNormalizer());
        $normalizers->add(User::class, 'user', new ReflectionNormalizer(['password']));
        $normalizers->add(Tag::class, 'tag', new ReflectionNormalizer(['user']));
        $normalizers->add(\DateTime::class, 'date', function(\DateTime $date) {
            return $date->format(\DateTime::RFC3339);
        });

        $hydrators = new FallbackHydratorContainer();

        $serializard = new Serializard($formats, $normalizers, $hydrators);

        return $serializard->serialize($this->data, 'json');
    }

    public function benchSerializardClosure()
    {
        $formats = new FormatContainer();
        $formats->add('json', new JsonFormat());

        $normalizers = new FallbackNormalizerContainer();
        $normalizers->add(Team::class, 'team', function(Team $team) {
            return ['users' => $team->getUsers()];
        });
        $normalizers->add(User::class, 'user', function(User $user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'tags' => $user->getTags(),
                'createdAt' => $user->getCreatedAt(),
            ];
        });
        $normalizers->add(Tag::class, 'tag', function(Tag $tag) {
            return [
                'id' => $tag->getId(),
                'name' => $tag->getName(),
            ];
        });
        $normalizers->add(\DateTime::class, 'date', function(\DateTime $date) {
            return $date->format(\DateTime::RFC3339);
        });

        $hydrators = new FallbackHydratorContainer();

        $serializard = new Serializard($formats, $normalizers, $hydrators);

        return $serializard->serialize($this->data, 'json');
    }
}
