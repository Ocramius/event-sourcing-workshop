<?php

declare(strict_types=1);

namespace EventSourcingWorkshop\EventSourcing\Infrastructure\Serialization;

use CuyZ\Valinor\Mapper\TreeMapper;
use EventSourcingWorkshop\EventSourcing\Domain\DomainEvent;

/**
 * This implementation of {@see DeSerializeEvent} assumes that the serialized version of the domain events
 * is compatible with their defined constructors.
 *
 * If that is not the case, then the given {@see TreeMapper} must be configured to account for that.
 *
 * @see https://github.com/CuyZ/Valinor/blob/444747ab0a1e6e1e05a08c5d402b5e3313205774/docs/pages/source.md
 * @see https://github.com/CuyZ/Valinor/blob/444747ab0a1e6e1e05a08c5d402b5e3313205774/docs/pages/message-customization.md
 */
final class DeSerializeEventWithValinorMapper implements DeSerializeEvent
{
    public function __construct(private readonly TreeMapper $treeMapper)
    {
    }

    /** {@inheritDoc} */
    public function __invoke(string $type, array $payload): DomainEvent
    {
        return $this->treeMapper->map($type, $payload);
    }
}
