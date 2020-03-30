<?php
declare(strict_types=1);

namespace App\Controller\Api\Task;

use Cake\ORM\Locator\LocatorAwareTrait;
use Cas\Domain\Model\Task;
use Cas\Domain\Model\TaskId;
use Cas\UseCase\Task\UpdateTaskCommandPort;

class UpdateTaskAdapter implements UpdateTaskCommandPort
{
    use LocatorAwareTrait;

    /**
     * @param \Cas\Domain\Model\TaskId $id id
     * @param string $description description
     * @return \Cas\Domain\Model\Task|null
     */
    public function update(TaskId $id, string $description): ?Task
    {
        $Tasks = $this->getTableLocator()->get('Tasks');

        /** @var \App\Model\Entity\Task|null $old */
        $old = $Tasks->find()->where(['id' => $id->asString()])->first();
        if (is_null($old)) {
            return null;
        }

        /** @var \App\Model\Entity\Task $task */
        $task = $Tasks->patchEntity($old, [
            'description' => $description,
        ]);
        if ($task->hasErrors()) {
            return null;
        }

        if (!$Tasks->save($task)) {
            return null;
        }

        return $task->toModel();
    }
}
