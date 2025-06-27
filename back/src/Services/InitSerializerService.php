<?php

namespace App\Services;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class InitSerializerService
{
    public Serializer $serializer;
    public function __construct()
    {
        $normalizers = [new ObjectNormalizer()];
        $encoder = [new JsonEncode(), new YamlEncoder(), new CsvEncoder(), new XmlEncoder()];
        $this->serializer = new Serializer($normalizers, $encoder);
    }
}
