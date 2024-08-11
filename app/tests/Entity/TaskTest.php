<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\Checklist;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testGetId()
    {
        $task = new Task();
        $this->assertNull($task->getId());
    }

    public function testSetTitle()
    {
        $task = new Task();
        $title = "Test Title";
        $task->setTitle($title);
        $this->assertEquals($title, $task->getTitle());
    }
}