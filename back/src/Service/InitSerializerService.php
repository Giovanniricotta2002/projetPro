<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class InitSerializerService
{
    public Serializer $serializer;
    private array $normalizers;
    private array $encoders;

    public function __construct()
    {
        $this->normalizers = [new ObjectNormalizer()];
        $this->encoders = [new JsonEncoder(), new YamlEncoder(), new CsvEncoder(), new XmlEncoder()];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
    }

    public function serializerAndDate(): Serializer
    {
        $normalizer = [
            new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']),
            ...$this->normalizers
        ];

        $this->normalizers = $normalizer;

        return new Serializer($this->normalizers, $this->encoders);
    }
}
