<?php

namespace App\Tests\Entity;

use App\Entity\Checklist;
use App\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChecklistTest extends KernelTestCase
{
    private function getValidator(): ValidatorInterface
    {
        self::bootKernel();
        $container = self::getContainer();
        return $container->get('validator');
    }

    public function testGetId()
    {
        $checklist = new Checklist();
        $this->assertNull($checklist->getId());
    }

    public function testSetTitle()
    {
        $checklist = new Checklist();
        $title = "Test Title";
        $checklist->setTitle($title);
        $this->assertEquals($title, $checklist->getTitle());
    }

    public function testAddTask()
    {
        $checklist = new Checklist();
        $task = new Task();
        $checklist->addTask($task);
        $this->assertTrue($checklist->getTasks()->contains($task));
    }

    public function testRemoveTask()
    {
        $checklist = new Checklist();
        $task = new Task();
        $checklist->addTask($task);
        $checklist->removeTask($task);
        $this->assertFalse($checklist->getTasks()->contains($task));
    }

    public function testGetDuration()
    {
        $checklist = new Checklist();

        $task1 = $this->createMock(Task::class);
        $task1->method('isArchived')->willReturn(false);
        $task1->method('getDuration')->willReturn(new \DateInterval('PT1H'));

        $task2 = $this->createMock(Task::class);
        $task2->method('isArchived')->willReturn(false);
        $task2->method('getDuration')->willReturn(new \DateInterval('PT2H'));

        $checklist->addTask($task1);
        $checklist->addTask($task2);

        $duration = $checklist->getDuration();

        $this->assertEquals(3, $duration->h);
        $this->assertEquals(0, $duration->i);
    }

    public function testTitleNotBlank()
    {
        $validator = $this->getValidator();
        $checklist = new Checklist();
        $checklist->setTitle('');

        $errors = $validator->validate($checklist);

        $this->assertGreaterThan(0, count($errors), "Expected validation errors for blank title.");
    }

    public function testTitleMinLength()
    {
        $validator = $this->getValidator();
        $checklist = new Checklist();
        $checklist->setTitle('a');

        $errors = $validator->validate($checklist);

        $this->assertGreaterThan(0, count($errors), "Expected validation errors for short title.");
    }

    public function testValidTitle()
    {
        $validator = $this->getValidator();
        $checklist = new Checklist();
        $checklist->setTitle('Valid Title');

        $errors = $validator->validate($checklist);

        $this->assertCount(0, $errors, "Expected no validation errors for a valid title.");
    }
}
