<?php
declare(strict_types=1);

namespace Tests\Unit;

use Tag;
use Tests\DatabaseTestCase;

require_once __DIR__ . '/../../src/Models/Tag.php';

class TagsTest extends DatabaseTestCase
{
    public function testCreateTag(): void
    {
        $id = Tag::create(['name' => 'Material']);
        $this->assertGreaterThan(0, $id);

        $found = Tag::find($id);
        $this->assertNotNull($found);
        $this->assertEquals('Material', $found['name']);
    }

    public function testUpdateTag(): void
    {
        $id = Tag::create(['name' => 'Old Tag']);

        $ok = Tag::update($id, ['name' => 'New Tag']);
        $this->assertTrue($ok);

        $found = Tag::find($id);
        $this->assertEquals('New Tag', $found['name']);
    }

    public function testFindByName(): void
    {
        Tag::create(['name' => 'UniqueName']);

        $found = Tag::findByName('UniqueName');
        $this->assertNotNull($found);
        $this->assertEquals('UniqueName', $found['name']);
    }

    public function testDeleteTag(): void
    {
        $id = Tag::create(['name' => 'Delete Me']);

        $ok = Tag::delete($id);
        $this->assertTrue($ok);

        $found = Tag::find($id);
        $this->assertNull($found);
    }
}
