<?php

declare(strict_types=1);

namespace EventSourcingWorkshopTest\EventSourcing\Unit\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\SQLiteProjectionTable;
use EventSourcingWorkshopTest\EventSourcing\Asset\DummyDbTableProjectionDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @covers \EventSourcingWorkshop\EventSourcing\Infrastructure\Projection\SQLiteProjectionTable */
final class SQLiteProjectionTableTest extends TestCase
{
    /** @var Connection&MockObject */
    private Connection $db;
    private SQLiteProjectionTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db    = $this->createMock(Connection::class);
        $this->table = new SQLiteProjectionTable($this->db, new DummyDbTableProjectionDefinition());
    }

    public function testInsertIgnore(): void
    {
        $this->db->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT OR IGNORE INTO dummy_table (foo,bar,baz) VALUES (?,?,?)',
                ['a', 'b', 3]
            );

        $this->table->insertIgnore([
            'foo' => 'a',
            'bar' => 'b',
            'baz' => 3,
        ]);
    }

    public function testUpsert(): void
    {
        $this->db->expects(self::once())
            ->method('executeStatement')
            ->with(
                'INSERT INTO dummy_table (foo,bar,baz) VALUES (?,?,?) ON CONFLICT DO UPDATE SET foo=excluded.foo,bar=excluded.bar,baz=excluded.baz',
                ['a', 'b', 3]
            );

        $this->table->upsert([
            'foo' => 'a',
            'bar' => 'b',
            'baz' => 3,
        ]);
    }

    public function testUpdate(): void
    {
        $this->db->expects(self::once())
            ->method('update')
            ->with(
                'dummy_table',
                [
                    'foo' => 'a',
                    'bar' => 'b',
                    'baz' => 3,
                ],
                ['my_id' => 'the_id']
            );

        $this->table->update(
            ['my_id' => 'the_id'],
            [
                'foo' => 'a',
                'bar' => 'b',
                'baz' => 3,
            ]
        );
    }

    public function testDelete(): void
    {
        $this->db->expects(self::once())
            ->method('delete')
            ->with(
                'dummy_table',
                ['my_id' => 'the_id']
            );

        $this->table->delete(['my_id' => 'the_id']);
    }

    public function testTruncate(): void
    {
        $this->db->expects(self::once())
            ->method('executeStatement')
            ->with('TRUNCATE TABLE dummy_table');

        $this->table->truncate();
    }
}
