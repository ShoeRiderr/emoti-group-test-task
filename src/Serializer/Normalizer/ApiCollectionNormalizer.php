<?php

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\ResourceList;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ApiCollectionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    /**
     * @var NormalizerInterface|NormalizerAwareInterface
     */
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        if (!$decorated instanceof NormalizerAwareInterface) {
            throw new \InvalidArgumentException(
                sprintf('The decorated normalizer must implement the %s.', NormalizerAwareInterface::class)
            );
        }

        $this->decorated = $decorated;
    }

    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if ($context['operation'] instanceof GetCollection && $context['resources'] instanceof ResourceList) {
            if ($data['@id'] === '/api/vacancies') {
                $totalPrice = 0;

                foreach ($data['hydra:member'] as $credit) {
                    $totalPrice += $credit['price'];
                }

                $data['totalPrice'] = $totalPrice;
                $data['totalPriceFormatted'] = $totalPrice / 100;
            }

        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    /**
     * @inheritdoc
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->decorated->setNormalizer($normalizer);
    }

}
